<?php

namespace App\Repository\Backend;

use App\Interfaces\EloquentBaseRepository;
use App\Interfaces\Repo\Backend\ItemSwapRepo;
use App\Models\ItemSwapLogModel;
use App\Models\ItemsInventoryModel;
use App\Support\StockReferencePrefixBuilder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ItemSwapRepoImpl extends EloquentBaseRepository implements ItemSwapRepo
{
    protected $model;

    protected ItemSwapLogModel $swapLogModel;

    public function __construct(ItemsInventoryModel $model, ItemSwapLogModel $swapLogModel)
    {
        $this->model = $model;
        $this->swapLogModel = $swapLogModel;
    }

    public function checkRequestValidityForSwap($request): array
    {
        $validStatus = Validator::make($request->all(), [
            'from_employee_id' => 'required|string',
            'to_employee_id' => 'required|string|different:from_employee_id',
            'to_branch_id' => 'required|integer',
            'inventory_ids' => 'required|array|min:1',
            'inventory_ids.*' => 'required|integer|exists:item_inventory,id',
            'remarks' => 'nullable|string|max:500',
        ]);

        return $validStatus->fails()
            ? ['isValidationSuccess' => false, 'error' => $validStatus->errors()]
            : ['isValidationSuccess' => true];
    }

    public function checkRequestValidityForReceiveSwap($request): array
    {
        $validStatus = Validator::make($request->all(), [
            'swap_no' => 'required|string',
            'swap_log_ids' => 'required|array|min:1',
            'swap_log_ids.*' => 'required|integer|exists:item_swap_logs,id',
        ]);

        return $validStatus->fails()
            ? ['isValidationSuccess' => false, 'error' => $validStatus->errors()]
            : ['isValidationSuccess' => true];
    }

    public function getSwappableItemsByEmployee(?string $employeeId, $branchId = null): Collection
    {
        if (empty($employeeId)) {
            return collect();
        }

        $query = $this->model::select(
            'item_inventory.*',
            'item_info.item_title',
            'category.category_title',
            'brand.brand_name',
            'to_branch.branch_name as to_branch_name'
        )
            ->leftJoin('item_info', 'item_inventory.item_id', '=', 'item_info.id')
            ->leftJoin('category', 'item_inventory.category_id', '=', 'category.id')
            ->leftJoin('brand', 'item_inventory.brand_id', '=', 'brand.id')
            ->leftJoin('branch as to_branch', 'item_inventory.to_branch_id', '=', 'to_branch.hr_branch_id')
            ->whereIn('item_inventory.status', [3, 4])
            ->where('item_inventory.to_employee_id', $employeeId);

        if (!empty($branchId)) {
            $query->where('item_inventory.to_branch_id', $branchId);
        }

        return $query->orderByDesc('item_inventory.id')->get();
    }

    public function getPendingSwapsBySwapNo(string $swapNo): Collection
    {
        return $this->buildPendingSwapQuery()
            ->where('item_swap_logs.swap_no', $swapNo)
            ->orderBy('item_swap_logs.id')
            ->get();
    }

    public function getPendingSwapsByEmployeeId(string $employeeId): Collection
    {
        return $this->buildPendingSwapQuery()
            ->where('item_swap_logs.to_employee_id', $employeeId)
            ->orderByDesc('item_swap_logs.id')
            ->get();
    }

    private function buildPendingSwapQuery()
    {
        return DB::table('item_swap_logs')
            ->select(
                'item_swap_logs.*',
                'item_info.item_title',
                'category.category_title',
                'brand.brand_name',
                'from_branch.branch_name as from_branch_name',
                'to_branch.branch_name as to_branch_name'
            )
            ->leftJoin('item_inventory', 'item_swap_logs.item_inventory_id', '=', 'item_inventory.id')
            ->leftJoin('item_info', 'item_inventory.item_id', '=', 'item_info.id')
            ->leftJoin('category', 'item_inventory.category_id', '=', 'category.id')
            ->leftJoin('brand', 'item_inventory.brand_id', '=', 'brand.id')
            ->leftJoin('branch as from_branch', 'item_swap_logs.from_branch_id', '=', 'from_branch.hr_branch_id')
            ->leftJoin('branch as to_branch', 'item_swap_logs.to_branch_id', '=', 'to_branch.hr_branch_id')
            ->where('item_swap_logs.status', ItemSwapLogModel::STATUS_PENDING)
            ->where('item_inventory.status', 6);
    }

    public function employeesInSameBranchOrDivision($fromBranchId, $toBranchId): bool
    {
        $fromBranchId = (int) $fromBranchId;
        $toBranchId = (int) $toBranchId;

        if ($fromBranchId <= 0 || $toBranchId <= 0) {
            return false;
        }

        if ($fromBranchId === $toBranchId) {
            return true;
        }

        $fromBranch = StockReferencePrefixBuilder::findBranchRow($fromBranchId);
        $toBranch = StockReferencePrefixBuilder::findBranchRow($toBranchId);

        if (!$fromBranch || !$toBranch) {
            return false;
        }

        $fromDivisionId = (int) data_get($fromBranch, 'division_id', 0);
        $toDivisionId = (int) data_get($toBranch, 'division_id', 0);

        return $fromDivisionId > 0 && $fromDivisionId === $toDivisionId;
    }

    public function generateSwapNo($branchId): string
    {
        $branchId = (int) $branchId;
        $two = StockReferencePrefixBuilder::branchTwoCharCode($branchId);
        $datePart = now()->format('Ymd');
        $prefix = 'SW-PBL' . $two . '-' . $datePart . '-' . $branchId . '-';

        $last = DB::table('item_swap_logs')
            ->where('swap_no', 'like', $prefix . '%')
            ->orderByDesc('id')
            ->value('swap_no');

        $next = 1;
        if ($last) {
            $parts = explode('-', (string) $last);
            $lastSerial = (int) end($parts);
            $next = $lastSerial + 1;
        }

        return $prefix . str_pad((string) $next, 4, '0', STR_PAD_LEFT);
    }

    public function initiateSwap(
        array $inventoryIds,
        string $fromEmployeeId,
        $fromBranchId,
        string $toEmployeeId,
        $toBranchId,
        $orgId,
        string $remarks = ''
    ): int {
        if (!$this->employeesInSameBranchOrDivision($fromBranchId, $toBranchId)) {
            throw new \RuntimeException('Swap is allowed only within the same branch or division.');
        }

        $fromBranch = StockReferencePrefixBuilder::findBranchRow((int) $fromBranchId);
        $toBranch = StockReferencePrefixBuilder::findBranchRow((int) $toBranchId);
        $fromDivisionId = (int) data_get($fromBranch, 'division_id', 0) ?: null;
        $toDivisionId = (int) data_get($toBranch, 'division_id', 0) ?: null;

        $swapNo = $this->generateSwapNo($fromBranchId);
        $swappedCount = 0;
        $remarkText = trim($remarks) !== '' ? trim($remarks) : 'SWAP_INITIATED';

        foreach ($inventoryIds as $inventoryId) {
            $inventoryItem = $this->model::where('id', $inventoryId)
                ->whereIn('status', [3, 4])
                ->where('to_employee_id', $fromEmployeeId)
                ->first();

            if (!$inventoryItem) {
                continue;
            }

            $hasPending = DB::table('item_swap_logs')
                ->where('item_inventory_id', $inventoryItem->id)
                ->where('status', ItemSwapLogModel::STATUS_PENDING)
                ->exists();

            if ($hasPending) {
                continue;
            }

            DB::table('item_swap_logs')->insert([
                'swap_no' => $swapNo,
                'item_inventory_id' => $inventoryItem->id,
                'org_id' => $orgId,
                'stock_out_no' => $inventoryItem->stock_out_no ?? null,
                'set_uniq_no' => $inventoryItem->set_uniq_no ?? null,
                'uniq_serial_no' => $inventoryItem->uniq_serial_no ?? null,
                'condition' => $inventoryItem->condition ?? null,
                'from_employee_id' => $fromEmployeeId,
                'to_employee_id' => $toEmployeeId,
                'from_branch_id' => $fromBranchId,
                'to_branch_id' => $toBranchId,
                'from_division_id' => $fromDivisionId,
                'to_division_id' => $toDivisionId,
                'swapped_by' => auth()->user()->employee_id ?? null,
                'swapped_at' => now(),
                'remarks' => $remarkText,
                'status' => ItemSwapLogModel::STATUS_PENDING,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $inventoryItem->update([
                'status' => 6,
                'remarks' => 'SWAP_PENDING',
                'updated_by' => auth()->user()->id ?? null,
                'updated_at' => now(),
            ]);

            $swappedCount++;
        }

        return $swappedCount;
    }

    public function receiveSwapItems(string $swapNo, array $swapLogIds, array $remarks = []): int
    {
        $receiverEmployeeId = auth()->user()->employee_id ?? null;
        $receiverBranchId = auth()->user()->branch_id ?? null;
        $updatedCount = 0;

        foreach ($swapLogIds as $swapLogId) {
            $swapLog = DB::table('item_swap_logs')
                ->where('id', $swapLogId)
                ->where('swap_no', $swapNo)
                ->where('status', ItemSwapLogModel::STATUS_PENDING)
                ->first();

            if (!$swapLog) {
                continue;
            }

            if ($receiverEmployeeId && (string) $swapLog->to_employee_id !== (string) $receiverEmployeeId) {
                continue;
            }

            $inventoryItem = $this->model::where('id', $swapLog->item_inventory_id)
                ->where('status', 6)
                ->first();

            if (!$inventoryItem) {
                continue;
            }

            $receiveRemark = trim((string) ($remarks[$swapLogId] ?? ''));
            if ($receiveRemark === '') {
                $receiveRemark = 'SWAP_RECEIVED';
            }

            $inventoryItem->update([
                'to_employee_id' => $swapLog->to_employee_id,
                'to_branch_id' => $swapLog->to_branch_id,
                'received_employee_id' => $swapLog->to_employee_id,
                'received_branch_id' => $swapLog->to_branch_id ?: $receiverBranchId,
                'from_employee_id' => $swapLog->from_employee_id,
                'from_branch_id' => $swapLog->from_branch_id,
                'receive_date_time' => now(),
                'RECEIVE_REMARKS' => $receiveRemark,
                'status' => 4,
                'remarks' => 'SWAPPED',
                'updated_by' => auth()->user()->id ?? null,
                'updated_at' => now(),
            ]);

            DB::table('item_swap_logs')
                ->where('id', $swapLogId)
                ->update([
                    'status' => ItemSwapLogModel::STATUS_RECEIVED,
                    'receive_remarks' => $receiveRemark,
                    'received_by' => $receiverEmployeeId,
                    'received_at' => now(),
                    'updated_at' => now(),
                ]);

            $updatedCount++;
        }

        return $updatedCount;
    }
}
