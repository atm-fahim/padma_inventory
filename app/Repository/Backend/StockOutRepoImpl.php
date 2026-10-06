<?php

namespace App\Repository\Backend;

use App\Interfaces\EloquentBaseRepository;
use App\Interfaces\Repo\Backend\StockOutRepo;
use App\Models\ItemsInventoryModel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

/**
 * Repository implementation of stock out for the items inventory model.
 * @develop by fahim
 * @author Kohinurit limited
 * @since 2024-01-20
 */
class StockOutRepoImpl extends EloquentBaseRepository implements StockOutRepo
{
    protected $model;

    public function __construct(ItemsInventoryModel $model)
    {
        $this->model = $model;
    }

    public function checkRequestValidity($request): array
    {
        $validStatus = Validator::make($request->all(), [
            'from_branch_id'   => 'required|integer',
            'employee_id'      => 'nullable|string',
            'branch_id'        => 'nullable|integer',
            'item_id.*'        => 'required|integer',
            'inventory_id.*'   => 'required|integer',
            'quantity.*'       => 'required|numeric|min:1',
            'to_employee_id.*' => 'nullable|string',
            'to_branch_id.*'   => 'required|integer',
        ]);

        return $validStatus->fails()
            ? ['isValidationSuccess' => false, 'error' => $validStatus->errors()]
            : ['isValidationSuccess' => true];
    }

    public function getEmployeeInfoByEmployeeId($employeeId)
    {
        $employee = DB::table('users')
            ->select('users.id', 'users.name', 'users.employee_id', 'users.branch_id', 'branch.branch_name')
            ->leftJoin('branch', 'users.branch_id', '=', 'branch.id')
            ->where('users.employee_id', $employeeId)
            ->where('users.status', 1)
            ->first();

        return $employee;
    }

    public function getItemInventoryByItemId($itemId, $orgId = null, $branchId = null)
    {
        $query = $this->model::select(
            "item_inventory.*",
            "item_info.item_title",
            "category.category_title",
            "brand.brand_name",
            "supplier.supplier_name",
            "brand.id as brand_id",
            "category.id as category_id",
            "supplier.id as supplier_id",
            "item_info.id as item_info_id",
            "item_inventory.id as item_inventory_id",
            "item_inventory.set_uniq_no as set_uniq_no"
        )
            ->leftJoin('item_info', 'item_inventory.item_id', '=', 'item_info.id')
            ->leftJoin('category', 'item_inventory.category_id', '=', 'category.id')
            ->leftJoin('brand', 'item_inventory.brand_id', '=', 'brand.id')
            ->leftJoin('supplier', 'item_inventory.supplier_id', '=', 'supplier.id')
            ->where('item_inventory.item_id', $itemId)
            ->where('item_inventory.status', 1)
            ->where('item_inventory.quantity', '>', 0);

        if ($orgId !== null && $orgId !== '') {
            $query->where(function ($q) use ($orgId) {
                $q->where('item_inventory.org_id', $orgId)
                    ->orWhereNull('item_inventory.org_id')
                    ->orWhere('item_inventory.org_id', '');
            });
        }
        if ($branchId !== null && $branchId !== '') {
            $query->where('item_inventory.branch_id', $branchId);
        }

        return $query->get();
    }

    public function getItemsBySetUniqNo($setUniqNo, $orgId, $branchId)
    {
        $query = $this->model::select(
            "item_inventory.*",
            "item_info.item_title",
            "category.category_title",
            "brand.brand_name",
            "supplier.supplier_name"
        )
            ->leftJoin('item_info', 'item_inventory.item_id', '=', 'item_info.id')
            ->leftJoin('category', 'item_inventory.category_id', '=', 'category.id')
            ->leftJoin('brand', 'item_inventory.brand_id', '=', 'brand.id')
            ->leftJoin('supplier', 'item_inventory.supplier_id', '=', 'supplier.id')
            ->where('item_inventory.set_uniq_no', $setUniqNo)
            ->where('item_inventory.status', 1)
            ->where('item_inventory.quantity', '>', 0);

        if ($orgId !== null && $orgId !== '') {
            $query->where(function ($q) use ($orgId) {
                $q->where('item_inventory.org_id', $orgId)
                    ->orWhereNull('item_inventory.org_id')
                    ->orWhere('item_inventory.org_id', '');
            });
        }
        if ($branchId !== null && $branchId !== '') {
            $query->where('item_inventory.branch_id', $branchId);
        }

        return $query->get();
    }

    public function getStockOutList($orgId = null, $branchId = null)
    {
        $query = $this->model::select(
            'item_inventory.stock_out_no',
            DB::raw('MAX(item_inventory.id) as id'),
            DB::raw('MAX(item_inventory.from_branch_id) as from_branch_id'),
            DB::raw('MAX(item_inventory.to_branch_id) as to_branch_id'),
            DB::raw('MAX(item_inventory.created_date) as created_date'),
            DB::raw('COUNT(*) as item_count'),
            DB::raw('MAX(from_branch.branch_name) as from_branch_name'),
            DB::raw('MAX(to_branch.branch_name) as to_branch_name'),
            DB::raw("(SELECT COUNT(*) FROM stock_out_gate_pass_logs g WHERE g.stock_out_no = item_inventory.stock_out_no AND g.event_type = 'view') as gate_pass_view_count"),
            DB::raw("(SELECT COUNT(*) FROM stock_out_gate_pass_logs g WHERE g.stock_out_no = item_inventory.stock_out_no AND g.event_type = 'print') as gate_pass_print_count")
        )
            ->leftJoin('branch as from_branch', 'item_inventory.from_branch_id', '=', 'from_branch.hr_branch_id')
            ->leftJoin('branch as to_branch', 'item_inventory.to_branch_id', '=', 'to_branch.hr_branch_id')
            ->whereNotNull('item_inventory.stock_out_no')
            ->where('item_inventory.warehouse', 'IT-OUT')
            ->where('item_inventory.store_type', 'SO')
            ->groupBy('item_inventory.stock_out_no')
            ->orderBy(DB::raw('MAX(item_inventory.id)'), 'DESC');


        return $query->get();
    }

    public function logGatePassEvent(string $stockOutNo, string $eventType, ?int $userId = null, ?int $orgId = null): void
    {
        if (! in_array($eventType, ['view', 'print'], true)) {
            return;
        }

        DB::table('stock_out_gate_pass_logs')->insert([
            'stock_out_no' => $stockOutNo,
            'event_type' => $eventType,
            'user_id' => $userId,
            'org_id' => $orgId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function countGatePassEvents(string $stockOutNo, string $eventType): int
    {
        return (int) DB::table('stock_out_gate_pass_logs')
            ->where('stock_out_no', $stockOutNo)
            ->where('event_type', $eventType)
            ->count();
    }

    public function getStockOutByStockOutNo($stockOutNo)
    {
        $query = $this->model::select(
            "item_inventory.*",
            "from_branch.branch_name as from_branch_name",
            "to_branch.branch_name as to_branch_name"
        )
        ->leftJoin('branch as from_branch', 'item_inventory.from_branch_id', '=', 'from_branch.hr_branch_id')
        ->leftJoin('branch as to_branch', 'item_inventory.to_branch_id', '=', 'to_branch.hr_branch_id')
        ->where('item_inventory.stock_out_no', $stockOutNo)
        ->where('item_inventory.store_type', 'SO')
        ->whereIn('item_inventory.status', [2,3]); // Status 2 = Stock out but not received yet
        return $query->get();
    }
    
    public function getStockOutByEmployeeId($employeeId)
    {
        $orgId = $orgId ?? (auth()->user()->org_id ?? null);
        $branchId = $branchId ?? (auth()->user()->branch_id ?? null);
        
        $query = $this->model::select(
            "item_inventory.*",
            "from_branch.branch_name as from_branch_name",
            "to_branch.branch_name as to_branch_name"
        )
        ->leftJoin('branch as from_branch', 'item_inventory.from_branch_id', '=', 'from_branch.hr_branch_id')
        ->leftJoin('branch as to_branch', 'item_inventory.to_branch_id', '=', 'to_branch.hr_branch_id')
        ->where('item_inventory.to_employee_id', $employeeId)
        ->where('item_inventory.store_type', 'SO')
        ->whereIn('item_inventory.status', [2, 4]); // Status 2 = Stock out but not received yet
        return $query->orderBy('item_inventory.created_date', 'DESC')->get();
    }
    
    public function receiveStockOutItems($stockOutNo, $inventoryIds, array $remarks = [])
    {
        //dd($remarks);
        $updatedCount = 0;
        
        foreach ($inventoryIds as $inventoryId) {
            $inventoryItem = $this->model::where('id', $inventoryId)
                ->where('stock_out_no', $stockOutNo)
                ->where('store_type', 'SO')
                ->first();
            
            if ($inventoryItem) {
                $remarkValue = $remarks[$inventoryId] ?? 'RECEIVED';
                // Update status to received (status = 3 for received)
                $inventoryItem->update([
                    'status' => 3, // Received status
                    'received_branch_id' => auth()->user()->branch_id,
                    'received_employee_id' => auth()->user()->employee_id,
                    'updated_at' => now(),
                    'receive_date_time' => now(),
                    'RECEIVE_REMARKS' => $remarkValue,
                ]);
                $updatedCount++;
            }
        }
        
        return $updatedCount;
    }
    
    public function checkRequestValidityForReceive($request): array
    {
        $validStatus = Validator::make($request->all(), [
            'stock_out_no' => 'required|string',
            'inventory_ids' => 'required|array|min:1',
            'inventory_ids.*' => 'required|integer|exists:item_inventory,id',
        ]);

        return $validStatus->fails()
            ? ['isValidationSuccess' => false, 'error' => $validStatus->errors()]
            : ['isValidationSuccess' => true];
    }
    
    public function getReceivedItemsByEmployee($branchId = null, $employeeId = null)
    {
        $branchId = $branchId ?? (auth()->user()->branch_id ?? null);
        $employeeId = $employeeId ?? (auth()->user()->employee_id ?? null);

        $query = $this->model::select(
            "item_inventory.*",
            "item_info.item_title",
            "category.category_title",
            "brand.brand_name",
            "supplier.supplier_name",
            "from_branch.branch_name as from_branch_name",
            "to_branch.branch_name as to_branch_name"
        )
        ->leftJoin('item_info', 'item_inventory.item_id', '=', 'item_info.id')
        ->leftJoin('category', 'item_inventory.category_id', '=', 'category.id')
        ->leftJoin('brand', 'item_inventory.brand_id', '=', 'brand.id')
        ->leftJoin('supplier', 'item_inventory.supplier_id', '=', 'supplier.id')
        ->leftJoin('branch as from_branch', 'item_inventory.from_branch_id', '=', 'from_branch.hr_branch_id')
        ->leftJoin('branch as to_branch', 'item_inventory.to_branch_id', '=', 'to_branch.hr_branch_id')
        ->where('item_inventory.status', 3); // Received status

        if (!empty($employeeId)) {
            $query->where('item_inventory.to_employee_id', $employeeId);
        }

        if (!empty($branchId)) {
            $query->where('item_inventory.to_branch_id', $branchId);
        }

        return $query->orderBy('item_inventory.id', 'DESC')->get();
    }
    
    public function transferItemsToEmployee($inventoryIds, $toEmployeeId, $toBranchId, $fromEmployeeId, $fromBranchId, $orgId, $fromDeviceSerialNo = null, $toDeviceSerialNo = null, $remarks = null)
    {
        $transferredCount = 0;
        $remarkText = trim((string) $remarks) !== '' ? trim((string) $remarks) : 'TRANSFERRED';
        
        foreach ($inventoryIds as $inventoryId) {
            $inventoryItem = $this->model::where('id', $inventoryId)
                ->where('status', 3) // Only transfer received items
                ->where('org_id', $orgId)
                ->first();
            
            if ($inventoryItem) {
                // Keep log of previous holder (A -> B)
                $previousEmployeeId = $inventoryItem->to_employee_id ?? $inventoryItem->received_employee_id;
                $previousBranchId = $inventoryItem->to_branch_id ?? $inventoryItem->received_branch_id;

                DB::table('item_transfer_logs')->insert([
                    'item_inventory_id' => $inventoryItem->id,
                    'org_id'            => $inventoryItem->org_id,
                    'stock_out_no'      => $inventoryItem->stock_out_no ?? null,
                    'set_uniq_no'       => $inventoryItem->set_uniq_no ?? null,
                    'uniq_serial_no'    => $inventoryItem->uniq_serial_no ?? null,
                    'condition'         => $inventoryItem->condition ?? null,
                    'from_employee_id'  => $previousEmployeeId,
                    'to_employee_id'    => $toEmployeeId,
                    'from_branch_id'    => $previousBranchId,
                    'to_branch_id'      => $toBranchId,
                    'from_device_serial_no' => $fromDeviceSerialNo,
                    'to_device_serial_no'   => $toDeviceSerialNo,
                    'transferred_by'    => auth()->user()->employee_id ?? null,
                    'transferred_at'    => now(),
                    'remarks'           => $remarkText,
                    'created_at'        => now(),
                    'updated_at'        => now(),
                ]);

                // Update inventory item with new employee and branch
                $inventoryItem->update([
                    'to_employee_id' => $toEmployeeId,
                    'to_branch_id' => $toBranchId,
                    'received_employee_id' => $toEmployeeId,
                    'received_branch_id' => $toBranchId,
                    'from_employee_id' => $fromEmployeeId,
                    'from_branch_id' => $fromBranchId,
                    'from_device_serial_no' => $fromDeviceSerialNo,
                    'to_device_serial_no'   => $toDeviceSerialNo,
                    'updated_by' => auth()->user()->employee_id,
                    'status' => 4, // Transferred status
                    // Only store date (no time) in updated_date for Oracle compatibility
                    // 'updated_date' => date('Y-m-d'),
                    // 'transfer_date_time' => now(),
                    'remarks' => $remarkText,
                    'updated_at' => now(),
                ]);
                $transferredCount++;
            }
        }
        
        return $transferredCount;
    }
    
    public function checkRequestValidityForTransfer($request): array
    {
        $validStatus = Validator::make($request->all(), [
            'to_employee_id' => 'required|string',
            'to_branch_id' => 'required|integer',
            'inventory_ids' => 'required|array|min:1',
            'inventory_ids.*' => 'required|integer|exists:item_inventory,id',
            'remarks' => 'nullable|string|max:500',
        ]);

        return $validStatus->fails()
            ? ['isValidationSuccess' => false, 'error' => $validStatus->errors()]
            : ['isValidationSuccess' => true];
    }
    
    public function getItemsForReturnToDivision($employeeId = null)
    {
        $employeeId = $employeeId ?? (auth()->user()->employee_id ?? null);

        $query = $this->model::select(
            "item_inventory.*",
            "item_info.item_title",
            "category.category_title",
            "brand.brand_name",
            "supplier.supplier_name",
            "from_branch.branch_name as from_branch_name",
            "to_branch.branch_name as to_branch_name"
        )
        ->leftJoin('item_info', 'item_inventory.item_id', '=', 'item_info.id')
        ->leftJoin('category', 'item_inventory.category_id', '=', 'category.id')
        ->leftJoin('brand', 'item_inventory.brand_id', '=', 'brand.id')
        ->leftJoin('supplier', 'item_inventory.supplier_id', '=', 'supplier.id')
        ->leftJoin('branch as from_branch', 'item_inventory.from_branch_id', '=', 'from_branch.hr_branch_id')
        ->leftJoin('branch as to_branch', 'item_inventory.to_branch_id', '=', 'to_branch.hr_branch_id')
        ->whereIn('item_inventory.status', [3, 4]); // Received or Transferred status

        if (!empty($employeeId)) {
            $query->where('item_inventory.to_employee_id', $employeeId);
        }

        return $query->orderBy('item_inventory.id', 'DESC')->get();
    }
    
    public function returnItemsToDivision($inventoryIds, $divisionId, $fromEmployeeId, $fromBranchId, $orgId, $fromDeviceSerialNo = null, $toBranchId = null, $condition = null, $remarks = null)
    {
        $returnedCount = 0;
        
        foreach ($inventoryIds as $inventoryId) {
            $inventoryItem = $this->model::where('id', $inventoryId)
                ->whereIn('status', [3, 4]) // Only return received or transferred items
                ->where('org_id', $orgId)
                ->first();
            
            if ($inventoryItem) {
                // Keep log of previous holder
                $previousEmployeeId = $inventoryItem->to_employee_id ?? $inventoryItem->received_employee_id;
                $previousBranchId = $inventoryItem->to_branch_id ?? $inventoryItem->received_branch_id;

                // Insert log in item_transfer_logs
                DB::table('item_transfer_logs')->insert([
                    'item_inventory_id' => $inventoryItem->id,
                    'org_id'            => $inventoryItem->org_id,
                    'stock_out_no'      => $inventoryItem->stock_out_no ?? null,
                    'set_uniq_no'       => $inventoryItem->set_uniq_no ?? null,
                    'uniq_serial_no'    => $inventoryItem->uniq_serial_no ?? null,
                    'condition'         => $condition ?? $inventoryItem->condition ?? null,
                    'from_employee_id'  => $previousEmployeeId,
                    'to_employee_id'    => null, // Division return, no employee
                    'from_branch_id'    => $previousBranchId,
                    'to_branch_id'      => $toBranchId,
                    'division_id'       => $divisionId,
                    'from_device_serial_no' => $fromDeviceSerialNo,
                    'to_device_serial_no'   => null,
                    'transferred_by'    => auth()->user()->employee_id ?? null,
                    'transferred_at'    => now(),
                    'remarks'           => $remarks ?? 'RETURNED_TO_DIVISION',
                    'created_at'        => now(),
                    'updated_at'        => now(),
                ]);

                // Update inventory item - return to division
                $inventoryItem->update([
                    'to_employee_id'      => null,
                    'to_branch_id'        => $toBranchId,
                    'division_id'         => $divisionId,
                    'from_employee_id'    => $fromEmployeeId,
                    'from_branch_id'      => $fromBranchId,
                    'from_device_serial_no' => $fromDeviceSerialNo,
                    'updated_by'          => auth()->user()->employee_id,
                    'status'              => 5, // Returned to division status
                    'condition'           => $condition ?? $inventoryItem->condition,
                    'remarks'             => $remarks ?? 'RETURNED_TO_DIVISION',
                    'updated_at'          => now(),
                ]);
                $returnedCount++;
            }
        }
        
        return $returnedCount;
    }
    
    public function checkRequestValidityForReturnToDivision($request): array
    {
        $validStatus = Validator::make($request->all(), [
            'division_id' => 'required|integer|exists:division,id',
            'branch_id'   => 'required|integer',
            'inventory_ids' => 'required|array|min:1',
            'inventory_ids.*' => 'required|integer|exists:item_inventory,id',
        ]);

        return $validStatus->fails()
            ? ['isValidationSuccess' => false, 'error' => $validStatus->errors()]
            : ['isValidationSuccess' => true];
    }
    
}

