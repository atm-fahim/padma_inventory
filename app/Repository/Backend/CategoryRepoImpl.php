<?php

namespace App\Repository\Backend;

use App\Interfaces\Repo\Backend\CategoryRepo;
use App\Models\CategoryModel;
use App\Interfaces\EloquentBaseRepository;

class CategoryRepoImpl extends EloquentBaseRepository implements CategoryRepo
{
    protected $model;

    public function __construct(CategoryModel $model)
    {
        $this->model = $model;
    }

    public function getCategory()
    {
        return $this->model->all();
    }

    public function getCategoriesByOrgId(int $orgId)
    {
        return $this->model->where('org_id', $orgId)->get();
    }

    public function findByNameAndOrgId(string $categoryName, int $orgId)
    {
        return $this->model->where('org_id', $orgId)
            ->where(function($query) use ($categoryName) {
                $query->where('category_title', $categoryName)
                      ->orWhere('category', $categoryName);
            })
            ->first();
    }

}
