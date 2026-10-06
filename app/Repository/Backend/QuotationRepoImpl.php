<?php

namespace App\Repository\Backend;

use App\Interfaces\EloquentBaseRepository;
use App\Interfaces\Repo\Backend\QuotationRepo;
use App\Models\QuotationApprovalModel;
use App\Models\QuotationItemModel;
use App\Models\QuotationModel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class QuotationRepoImpl extends EloquentBaseRepository implements QuotationRepo
{
    protected $model;

    public function __construct(QuotationModel $model)
    {
        $this->model = $model;
    }

    public function checkRequestValidity($request): array
    {
        $validStatus = Validator::make($request->all(), [
            'supplier_id'        => 'required|integer',
            'employee_id'        => 'required|string',
            'quotation_date'     => 'required|date',
            'item_id.*'          => 'required|integer',
            'quantity.*'         => 'required|numeric|min:1',
            'unit_price.*'       => 'required|numeric|min:0',
        ]);

        return $validStatus->fails()
            ? ['isValidationSuccess' => false, 'error' => $validStatus->errors()]
            : ['isValidationSuccess' => true];
    }

    public function generateQuotationNo(): string
    {
        $prefix = 'QT-';
        $latest = $this->model->orderBy('id', 'DESC')->first();
        $nextId = $latest ? $latest->id + 1 : 1;
        return $prefix . date('ymd') . '-' . str_pad($nextId, 4, '0', STR_PAD_LEFT);
    }

    public function createWithItems(array $quotationData, array $itemsData)
    {
        return DB::transaction(function () use ($quotationData, $itemsData) {
            /** @var QuotationModel $quotation */
        
            $quotation = $this->model->create($quotationData);

            foreach ($itemsData as $item) {
                if (isset($item['quantity'])) {
                    $item['quantity'] = is_numeric($item['quantity']) ? (float)$item['quantity'] : 0;
                }
                if (isset($item['unit_price'])) {
                    $item['unit_price'] = is_numeric($item['unit_price']) ? (float)$item['unit_price'] : 0;
                }
                if (isset($item['discount'])) {
                    $item['discount'] = is_numeric($item['discount']) ? (float)$item['discount'] : 0;
                }
                if (isset($item['vat'])) {
                    $item['vat'] = is_numeric($item['vat']) ? (float)$item['vat'] : 0;
                }
                if (isset($item['tax'])) {
                    $item['tax'] = is_numeric($item['tax']) ? (float)$item['tax'] : 0;
                }

                $item['quotation_id'] = $quotation->id;
                QuotationItemModel::create($item);
            }

            return $quotation->load('items');
        });
    }

    public function findWithItemsById($id)
    {
        return $this->model->with(['items', 'supplier', 'approvals'])->findOrFail($id);
    }

    public function getQuotationList($orgId = null, $branchId = null)
    {
        $query = $this->model->with('supplier')
            ->where('active_status', '<>', 9)
            ->orderBy('id', 'DESC');

        if ($orgId) {
            $query->where('org_id', $orgId);
        }

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        return $query->get();
    }

    public function approveQuotation($quotationId, $approvalLevel, $status, $comments = null, $approverInfo = [])
    {
        return DB::transaction(function () use ($quotationId, $approvalLevel, $status, $comments, $approverInfo) {
            // Enforce sequential approval: admin -> division_head -> md -> board
            $order = ['admin', 'division_head', 'md', 'board'];
            $levelIndex = array_search($approvalLevel, $order, true);
            if ($levelIndex === false) {
                throw new \InvalidArgumentException('Invalid approval level');
            }

            if ($levelIndex > 0) {
                $requiredPrevious = array_slice($order, 0, $levelIndex);
                $approvedPrev = QuotationApprovalModel::where('quotation_id', $quotationId)
                    ->whereIn('approval_level', $requiredPrevious)
                    ->where('status', 'approved')
                    ->pluck('approval_level')
                    ->toArray();

                foreach ($requiredPrevious as $prevLevel) {
                    if (!in_array($prevLevel, $approvedPrev, true)) {
                        throw new \RuntimeException('Previous approval missing: ' . $prevLevel);
                    }
                }
            }

            // Create or update approval record
            $approval = QuotationApprovalModel::updateOrCreate(
                [
                    'quotation_id' => $quotationId,
                    'approval_level' => $approvalLevel,
                ],
                [
                    'approved_by' => $approverInfo['user_id'] ?? auth()->id(),
                    'approver_name' => $approverInfo['name'] ?? auth()->user()->name ?? '',
                    'approver_designation' => $approverInfo['designation'] ?? '',
                    'comments' => $comments,
                    'status' => $status,
                    'approved_at' => $status === 'approved' ? now() : null,
                ]
            );

            // Update quotation status based on approval level
            $quotation = $this->model->findOrFail($quotationId);
            $statusMap = [
                'admin' => 'admin_approved',
                'division_head' => 'division_head_approved',
                'md' => 'md_approved',
                'board' => 'board_approved',
            ];

            if ($status === 'approved' && isset($statusMap[$approvalLevel])) {
                $quotation->update(['status' => $statusMap[$approvalLevel]]);
            } elseif ($status === 'rejected') {
                $quotation->update(['status' => 'rejected']);
            }

            return $approval;
        });
    }

    public function getApprovalStatus($quotationId)
    {
        return QuotationApprovalModel::where('quotation_id', $quotationId)
            ->orderBy('id', 'ASC')
            ->get();
    }
}
