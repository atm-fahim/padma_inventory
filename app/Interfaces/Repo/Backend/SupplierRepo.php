<?php

namespace App\Interfaces\Repo\Backend;
use App\Interfaces\EloquentRepositoryInterface; 

interface SupplierRepo extends EloquentRepositoryInterface
{
    /**
     * Find supplier by name and org_id
     *
     * @param string $supplierName
     * @param int $orgId
     * @return mixed
     */
    public function findByNameAndOrgId(string $supplierName, int $orgId);
    public function supplierInfoByOrgId(int $orgId);
}
