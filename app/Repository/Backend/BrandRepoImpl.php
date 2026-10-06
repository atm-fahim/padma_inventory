<?php

namespace App\Repository\Backend;

use App\Interfaces\EloquentBaseRepository;
use App\Interfaces\Repo\Backend\BrandRepo;
use App\Models\BrandModel;
use Illuminate\Support\Facades\Validator;

class BrandRepoImpl extends EloquentBaseRepository implements BrandRepo
{
    protected $model;

    public function __construct(BrandModel $model)
    {
        $this->model = $model;
    }
    public function checkRequestValidity($request): array
    {
        $validStatus = Validator::make($request->all(), [
            'brand_name' => 'required|string',
        ]);
        return $validStatus->fails() ? array('isValidationSuccess' => false, 'error' => $validStatus->errors())
            : array('isValidationSuccess' => true);
    }
    public function getAllBrands()
    {
        return $this->model->all();
    }

    public function getBrandsByOrgId(int $orgId)
    {
        return $this->model->where('org_id', $orgId)->get();
    }

    public function findByNameAndOrgId(string $brandName, int $orgId)
    {
        return $this->model->where('brand_name', $brandName)
            ->where('org_id', $orgId)
            ->first();
    }


}
