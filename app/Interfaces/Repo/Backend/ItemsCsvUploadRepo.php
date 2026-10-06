<?php

namespace App\Interfaces\Repo\Backend;
use App\Interfaces\EloquentRepositoryInterface;

interface ItemsCsvUploadRepo extends EloquentRepositoryInterface
{
   

    /**
     * Get all items by organization ID
     *
     * @param int $orgId
     * @return mixed
     */
    public function getItemsByOrgId(int $orgId);


   
    /**
     * Check if item exists by name and org_id
     *
     * @param string $itemName
     * @param int $orgId
     * @return mixed
     */
    public function findByNameAndOrgId(string $itemName, int $orgId);
}
