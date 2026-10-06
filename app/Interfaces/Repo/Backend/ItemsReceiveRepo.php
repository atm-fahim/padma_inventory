<?php

namespace App\Interfaces\Repo\Backend;
use App\Interfaces\EloquentRepositoryInterface;

interface ItemsReceiveRepo extends EloquentRepositoryInterface
{
    /**
     * Save item receive data
     *
     * @param array $data
     * @return mixed
     */
    public function save(array $data);

    /**
     * Get all received items by organization ID
     *
     * @param int $orgId
     * @return mixed
     */
    public function getReceivedItemsByOrgId(int $orgId);

    /**
     * Find received item by ID
     *
     * @param int $id
     * @return mixed
     */
    public function findById(int $id);
}
