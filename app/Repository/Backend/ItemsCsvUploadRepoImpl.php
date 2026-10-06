<?php

namespace App\Repository\Backend;

use App\Interfaces\Repo\Backend\ItemsCsvUploadRepo;
use App\Models\ItemsInventoryModel;
use App\Interfaces\EloquentBaseRepository;
use Illuminate\Support\Facades\DB;

class ItemsCsvUploadRepoImpl extends EloquentBaseRepository implements ItemsCsvUploadRepo
{
    protected $model;

    public function __construct(ItemsInventoryModel $model)
    {
        $this->model = $model;
    }

    

    /**
     * Get all items by organization ID
     */
    public function getItemsByOrgId(int $orgId)
    {
        return $this->model->where('org_id', $orgId)->get();
    }

  

    /**
     * Check if item exists by name and org_id
     */
    public function findByNameAndOrgId(string $itemName, int $orgId)
    {
        return $this->model->where('item_title', $itemName)
            ->where('org_id', $orgId)
            ->first();
    }
}
