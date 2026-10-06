<?php

namespace App\Repository\Backend;

use App\Interfaces\EloquentBaseRepository;
use App\Interfaces\Repo\Backend\SupplierRepo;
use App\Models\SupplierModel;


class SupplierRepoImpl extends EloquentBaseRepository implements SupplierRepo
{
    protected $model;

    public function __construct(SupplierModel $model)
    {
        $this->model = $model;
    }

    public function supplierInfoByOrgId(int $orgId)
    {
        return $this->model->where('org_id', $orgId)->get();
    }

    public function findByNameAndOrgId(string $supplierName, int $orgId)
    {
        return $this->model->where('supplier_name', $supplierName)
            ->where('org_id', $orgId)
            ->first();
    }

}
