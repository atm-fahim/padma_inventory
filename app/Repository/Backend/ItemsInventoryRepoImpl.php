<?php

namespace App\Repository\Backend;

use App\Interfaces\EloquentBaseRepository;
use App\Interfaces\Repo\Backend\ItemsInventoryRepo;

use App\Models\ItemsInventoryModel;
use App\Models\SupplierModel;
use App\Models\BrandModel;
use App\Models\ItemInfoModel;
use App\Models\CategoryModel;
use App\Models\ItemModel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

/**
 * Repository implementation of items for the items model.
 * they are items info
 * @develop by fahim
 * @author Kohinurit limited
 * @since 2023-01-20
 */
class ItemsInventoryRepoImpl extends EloquentBaseRepository implements ItemsInventoryRepo
{
    protected $model;

    public function __construct(ItemsInventoryModel $model)
    {
        $this->model = $model;
    }

    public function checkRequestValidity($request): array
    {
        $validStatus = Validator::make($request->all(), [
            'category_id.*'      => 'required|integer',
            'item_id.*'          => 'required|integer',
            'supplier_id.*'      => 'nullable|integer',
            'brand_id.*'         => 'nullable|integer',
            'serial_no.*'        => 'nullable|string',
            'quantity.*'         => 'required|numeric',
            'invoice_attachment'   => 'nullable|array',
            'invoice_attachment.*' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);

        return $validStatus->fails()
            ? ['isValidationSuccess' => false, 'error' => $validStatus->errors()]
            : ['isValidationSuccess' => true];
    }

    public function getItemInventoryInfo(){
        // Exclude spec items (RAM=17, Processor=41, HDD=12, SSD=14) to show only main items
        $excludeItemIds = [17, 41, 12, 14];

        return $this->model::select("item_inventory.*","users.name",
        "supplier.supplier_name","brand.brand_name","item_info.item_title",
        "category.category_title")
            ->leftJoin('supplier','item_inventory.supplier_id', '=', 'supplier.id')
            ->leftJoin('brand','item_inventory.brand_id', '=', 'brand.id')
            ->leftJoin('item_info','item_inventory.item_id', '=', 'item_info.id')
            ->leftJoin('category','item_inventory.category_id', '=', 'category.id')
            ->leftJoin('users','users.id','=','item_inventory.created_by')
            ->where(['item_inventory.status' => 1])
            ->whereNotIn('item_inventory.item_id', $excludeItemIds)
            ->whereIn('item_inventory.id', function($query) use ($excludeItemIds) {
                $query->select(DB::raw('MAX(item_inventory.id)'))
                    ->from('item_inventory')
                    ->where('item_inventory.status', 1)
                    ->where('item_inventory.created_by', auth()->user()->id)
                    ->whereNotIn('item_inventory.item_id', $excludeItemIds)
                    ->groupBy('item_inventory.set_uniq_no');
            })
            ->orderBy('item_inventory.id', 'desc')
            ->get();
    }

    /**
     * Get SET_UNIQ_NO list grouped by set_uniq_no with item names
     * Only shows main items (excludes RAM, Processor, HDD, SSD)
     */
    public function getSetUniqNoList($orgId = null, $branchId = null){
        // Exclude spec items (RAM=17, Processor=41, HDD=12, SSD=14) to show only main items
        $excludeItemIds = [17, 41, 12, 14];

        // Use aggregate functions for all columns to satisfy Oracle GROUP BY requirements
        $query = $this->model::select(
            "item_inventory.set_uniq_no",
            DB::raw("MAX(item_info.item_title) as item_title"),
            DB::raw("MAX(category.category_title) as category_title"),
            DB::raw("MAX(brand.brand_name) as brand_name"),
            DB::raw("MAX(item_inventory.id) as inventory_id")
        )
            ->leftJoin('item_info','item_inventory.item_id', '=', 'item_info.id')
            ->leftJoin('category','item_inventory.category_id', '=', 'category.id')
            ->leftJoin('brand','item_inventory.brand_id', '=', 'brand.id')
            ->where('item_inventory.status', 1)
            ->whereNotNull('item_inventory.set_uniq_no')
            ->where('item_inventory.set_uniq_no', '!=', '')
            ->whereNotIn('item_inventory.item_id', $excludeItemIds);

        if ($orgId) {
            $query->where(function ($q) use ($orgId) {
                $q->where('item_inventory.org_id', $orgId)
                    ->orWhereNull('item_inventory.org_id')
                    ->orWhere('item_inventory.org_id', '');
            });
        }

        if ($branchId) {
            $query->where('item_inventory.branch_id', $branchId);
        }

        return $query->groupBy('item_inventory.set_uniq_no')
            ->orderBy('item_inventory.set_uniq_no', 'desc')
            ->get();
    }

}
