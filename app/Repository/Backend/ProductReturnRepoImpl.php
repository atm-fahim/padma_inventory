<?php

namespace App\Repository\Backend;

use App\Interfaces\EloquentBaseRepository;
use App\Interfaces\Repo\Backend\ProductReturnRepo;
use App\Models\ProductReturnItemModel;
use App\Models\ProductReturnModel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ProductReturnRepoImpl extends EloquentBaseRepository implements ProductReturnRepo
{
    protected $model;

    public function __construct(ProductReturnModel $model)
    {
        $this->model = $model;
    }

    public function checkRequestValidity($request): array
    {
        $validStatus = Validator::make($request->all(), [
            'supplier_id'        => 'required|integer',
            'return_date'        => 'required|date',
            'return_type'        => 'required|in:damage,repair,replacement',
            'item_name.*'        => 'required|string',
            'quantity.*'         => 'required|numeric|min:1',
        ]);

        return $validStatus->fails()
            ? ['isValidationSuccess' => false, 'error' => $validStatus->errors()]
            : ['isValidationSuccess' => true];
    }

    public function generateReturnNo(): string
    {
        $prefix = 'PR-';
        $latest = $this->model->orderBy('id', 'DESC')->first();
        $nextId = $latest ? $latest->id + 1 : 1;
        return $prefix . date('ymd') . '-' . str_pad($nextId, 4, '0', STR_PAD_LEFT);
    }

    public function createWithItems(array $returnData, array $itemsData)
    {
        return DB::transaction(function () use ($returnData, $itemsData) {
            /** @var ProductReturnModel $return */
            $return = $this->model->create($returnData);

            foreach ($itemsData as $item) {
                // Normalize numeric fields for Oracle
                if (isset($item['quantity'])) {
                    $item['quantity'] = is_numeric($item['quantity']) ? (float)$item['quantity'] : 0;
                }

                $item['product_return_id'] = $return->id;
                ProductReturnItemModel::create($item);
            }

            return $return->load('items');
        });
    }

    public function findWithItemsById($id)
    {
        return $this->model->with(['items', 'supplier'])->findOrFail($id);
    }

    public function getReturnList($orgId = null, $branchId = null)
    {
        $query = $this->model->with('supplier')
            ->where('status', '<>', 9)
            ->orderBy('id', 'DESC');

        if ($orgId) {
            $query->where('org_id', $orgId);
        }

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        return $query->get();
    }
}
