<?php

namespace App\Repository\Backend;

use App\Interfaces\EloquentBaseRepository;
use App\Interfaces\Repo\Backend\RequisitionRepo;
use App\Models\RequisitionItemModel;
use App\Models\RequisitionModel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class RequisitionRepoImpl extends EloquentBaseRepository implements RequisitionRepo
{
    protected $model;

    public function __construct(RequisitionModel $model)
    {
        $this->model = $model;
    }

    public function checkRequestValidity($request): array
    {
        $validStatus = Validator::make($request->all(), [
            'employee_id'   => 'required|string',
            'requisition_date' => 'required|date',
            'item_name.*'  => 'required|string',
            'quantity.*'   => 'required|numeric|min:0.01',
        ]);

        return $validStatus->fails()
            ? ['isValidationSuccess' => false, 'error' => $validStatus->errors()]
            : ['isValidationSuccess' => true];
    }

    public function generateRequisitionNo(): string
    {
        $prefix = 'REQ-';
        $latest = $this->model->orderBy('id', 'DESC')->first();
        $nextId = $latest ? $latest->id + 1 : 1;
        return $prefix . date('ymd') . '-' . str_pad($nextId, 4, '0', STR_PAD_LEFT);
    }

    public function createWithItems(array $requisitionData, array $itemsData)
    {
        return DB::transaction(function () use ($requisitionData, $itemsData) {
            $requisition = $this->model->create($requisitionData);
            foreach ($itemsData as $item) {
                $item['requisition_id'] = $requisition->id;
                RequisitionItemModel::create($item);
            }
            return $requisition->load('items');
        });
    }

    public function findWithItemsById($id)
    {
        return $this->model->with('items')->findOrFail($id);
    }

    public function getRequisitionList($orgId = null, $branchId = null)
    {
        $query = $this->model->with('items')->where('status', '<>', 9)->orderBy('id', 'DESC');
        if ($orgId) {
            $query->where('org_id', $orgId);
        }
        if ($branchId) {
            $query->where('branch_id', $branchId);
        }
        return $query->get();
    }
}
