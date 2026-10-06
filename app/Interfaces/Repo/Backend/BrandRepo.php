<?php

namespace App\Interfaces\Repo\Backend;

use App\Interfaces\EloquentRepositoryInterface;

interface BrandRepo extends EloquentRepositoryInterface
{
    /**
     * Get all brands
     *
     * @return mixed
     */
    public function getAllBrands();

    /**
     * Get brands by organization ID
     *
     * @param int $orgId
     * @return mixed
     */
    public function getBrandsByOrgId(int $orgId);

    /**
     * Find brand by name
     *
     * @param string $brandName
     * @param int $orgId
     * @return mixed
     */
    public function findByNameAndOrgId(string $brandName, int $orgId);

    
}
