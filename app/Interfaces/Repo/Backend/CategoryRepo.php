<?php

namespace App\Interfaces\Repo\Backend;
use App\Interfaces\EloquentRepositoryInterface;

interface CategoryRepo extends EloquentRepositoryInterface
{
    /**
     * Get all categories
     *
     * @return mixed
     */
    public function getCategory();

    /**
     * Get categories by organization ID
     *
     * @param int $orgId
     * @return mixed
     */
    public function getCategoriesByOrgId(int $orgId);

    /**
     * Find category by name and org_id
     *
     * @param string $categoryName
     * @param int $orgId
     * @return mixed
     */
    public function findByNameAndOrgId(string $categoryName, int $orgId);

}
