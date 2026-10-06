<?php

namespace App\Repository\Backend;

use App\Interfaces\EloquentBaseRepository;
use App\Interfaces\Repo\Backend\DeliveryChallanRepo;
use App\Models\DeliveryChallanItemModel;
use App\Models\DeliveryChallanModel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class DeliveryChallanRepoImpl extends EloquentBaseRepository implements DeliveryChallanRepo
{
    protected $model;

    public function __construct(DeliveryChallanModel $model)
    {
        $this->model = $model;
    }

    public function checkRequestValidity($request): array
    {
        $validStatus = Validator::make($request->all(), [
            'buyer_name'         => 'required|string',
            'delivery_location'  => 'required|string',
            'challan_date'       => 'required|date',
            'item_name.*'        => 'required|string',
            'quantity.*'         => 'required|numeric|min:1',
        ]);

        return $validStatus->fails()
            ? ['isValidationSuccess' => false, 'error' => $validStatus->errors()]
            : ['isValidationSuccess' => true];
    }

    public function generateChallanNo(): string
    {
        $prefix = 'DC-';
        $latest = $this->model->orderBy('id', 'DESC')->first();
        $nextId = $latest ? $latest->id + 1 : 1;
        return $prefix . date('ymd') . '-' . str_pad($nextId, 4, '0', STR_PAD_LEFT);
    }

    public function createWithItems(array $challanData, array $itemsData)
    {
        return DB::transaction(function () use ($challanData, $itemsData) {
            /** @var DeliveryChallanModel $challan */
            $challan = $this->model->create($challanData);

            foreach ($itemsData as $item) {
                // Normalize numeric fields for Oracle (avoid ORA-01722 for text like "1 Year")
                if (isset($item['quantity'])) {
                    $item['quantity'] = is_numeric($item['quantity']) ? (float)$item['quantity'] : 0;
                }

                if (isset($item['warranty']) && $item['warranty'] !== null && $item['warranty'] !== '') {
                    // If warranty column is numeric in Oracle, extract leading number (e.g. "1 Year" -> 1)
                    if (preg_match('/\d+(\.\d+)?/', (string)$item['warranty'], $matches)) {
                        $item['warranty'] = $matches[0];
                    }
                }

                $item['delivery_challan_id'] = $challan->id;
                DeliveryChallanItemModel::create($item);
            }

            return $challan->load('items');
        });
    }

    public function findWithItemsById($id)
    {
        return $this->model->with('items')->findOrFail($id);
    }
}

