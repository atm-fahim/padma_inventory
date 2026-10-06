<?php

namespace App\Repository\Backend;

use App\Interfaces\EloquentBaseRepository;
use App\Interfaces\Repo\Backend\OrganizationRepo;
use App\Models\OrganizationModel;
use Illuminate\Support\Facades\Validator;

/**
 * Repository implementation of organization for the Organization model.
 * they are organization info
 * @develop by fahim
 * @author Padma Bank limited
 * @since 2023-08-20-04-01
 */
class OrganizationRepoImpl extends EloquentBaseRepository implements OrganizationRepo
{


    protected $model;

    public function __construct(OrganizationModel $model)
    {
        $this->model = $model;
    }

    public function checkRequestValidity($request): array
    {
        $validStatus = Validator::make($request->all(), [
            'org_name' => 'required|string',
            'address' => 'required|string'
        ]);
        return $validStatus->fails() ? array('isValidationSuccess' => false, 'error' => $validStatus->errors())
            : array('isValidationSuccess' => true);
    }


}
