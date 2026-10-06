<?php

namespace App\Repository\Backend;

use App\Interfaces\Repo\Backend\ItemsReceiveRepo;
use App\Models\ItemsReceiveModel;
use App\Interfaces\EloquentBaseRepository;

class ItemsReceiveRepoImpl extends EloquentBaseRepository implements ItemsReceiveRepo
{
    private $model;

    public function __construct(ItemsReceiveModel $model)
    {
        $this->model = $model;
    }

    public function save(array $data)
    {
        return $this->model->create($data);
    }

    public function getReceivedItemsByOrgId(int $orgId)
    {
        return $this->model->where('org_id', $orgId)->get();
    }

    public function findById(int $id)
    {
        return $this->model->find($id);
    }
}
