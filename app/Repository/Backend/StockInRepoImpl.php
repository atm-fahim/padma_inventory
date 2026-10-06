<?php

namespace App\Repository\Backend;

use App\Interfaces\EloquentBaseRepository;
use App\Interfaces\Repo\Backend\StockInRepo;
use App\Models\ItemsInventoryModel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

/**
 * Repository implementation of stock in for the items inventory model.
 * @develop by fahim
 * @author Padma Bank limited
 * @since 2026-01-04
 */
class StockInRepoImpl extends EloquentBaseRepository implements StockInRepo
{
    protected $model;

    public function __construct(ItemsInventoryModel $model)
    {
        $this->model = $model;
    }

    public function checkRequestValidity($request): array
    {
        $validStatus = Validator::make($request->all(), [
            'supplier_id.*'   => 'required|integer',
            'item_id.*'       => 'required|integer',
            'category_id.*'   => 'required|integer',
            'quantity.*'      => 'required|numeric|min:1',
            'price.*'         => 'nullable|numeric|min:0',
        ]);

        return $validStatus->fails()
            ? ['isValidationSuccess' => false, 'error' => $validStatus->errors()]
            : ['isValidationSuccess' => true];
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
        
        if ($orgId) {
            $query->where('item_inventory.org_id', $orgId);
        }
        
        if ($branchId) {
            $query->where('item_inventory.branch_id', $branchId);
        }
        
        return $query->get();
    }

    public function getItemsBySetUniqNo($setUniqNo, $orgId, $branchId)
    {
        return $this->model::select(
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
            ->where('item_inventory.quantity', '>', 0)
            ->get();
    }

    public function getStockInList($orgId = null, $branchId = null)
    {
        $query = DB::table('item_inventory')
            ->select(
                'STOCK_IN_NO',
                'FROM_BRANCH_ID',
                'TO_BRANCH_ID',
                'SUPPLIER_ID',
                'created_date',
                DB::raw('COUNT(*) as item_count'),
                DB::raw('SUM(QUANTITY) as total_quantity'),
                DB::raw('SUM(PRICE * QUANTITY) as total_amount')
            )
            ->whereNotNull('STOCK_IN_NO')
            ->where('STOCK_IN_NO', '!=', '')
            ->where('store_type', 'SI');
        
        if ($orgId) {
            $query->where('org_id', $orgId);
        }
        
        if ($branchId) {
            $query->where('TO_BRANCH_ID', $branchId);
        }
        
        $stockInList = $query->groupBy('STOCK_IN_NO', 'FROM_BRANCH_ID', 'TO_BRANCH_ID', 'SUPPLIER_ID', 'created_date')
            ->orderBy('created_date', 'DESC')
            ->get();
        
        // Get branch and supplier names for display
        foreach ($stockInList as $stockIn) {
            $stockIn->from_branch_name = DB::table('branch')
                ->where('id', $stockIn->FROM_BRANCH_ID)
                ->value('branch_name') ?? 'Supplier';
            
            $stockIn->to_branch_name = DB::table('branch')
                ->where('id', $stockIn->TO_BRANCH_ID)
                ->value('branch_name') ?? 'N/A';
            
            $stockIn->supplier_name = DB::table('supplier')
                ->where('id', $stockIn->SUPPLIER_ID)
                ->value('supplier_name') ?? 'N/A';
        }
        
        return $stockInList;
    }

    public function getStockInByStockInNo($stockInNo, $orgId = null)
    {
        $query = $this->model::where('STOCK_IN_NO', $stockInNo)
            ->where('store_type', 'SI');
        
        if ($orgId) {
            $query->where('org_id', $orgId);
        }
        
        return $query->get();
    }
}
