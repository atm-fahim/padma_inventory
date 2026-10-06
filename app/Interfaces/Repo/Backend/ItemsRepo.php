<?php

namespace App\Interfaces\Repo\Backend;
use App\Interfaces\EloquentRepositoryInterface;

interface ItemsRepo extends EloquentRepositoryInterface
{
    /**
     * Get all items by organization ID
     *
     * @param int $orgId
     * @return mixed
     */
    public function itemAllInfoByOrgId(int $orgId);

    /**
     * Get all items by organization ID
     *
     * @param int $orgId
     * @return mixed
     */
    public function itemInfoByOrgId();

    public function getItemInfoByCategoryId(int $categoryId);
  
}
