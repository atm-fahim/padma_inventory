<?php

namespace App\Repository\Backend;

use App\Interfaces\EloquentBaseRepository;
use App\Interfaces\Repo\Backend\BackendMenuRepo;
use App\Interfaces\Repo\Backend\UserAccessTypeRepo;
use App\Models\BackendMenuModel;
use App\Models\UserAccessTypeModel;
use Illuminate\Support\Facades\Validator;

/**
 * Repository implementation of menu for the menu model.
 * they are menu access info
 * @develop by fahim
 * @author Padma Bank limited
 * @since 2023-08-20
 */
class UserAccessTypeRepoImpl extends EloquentBaseRepository implements UserAccessTypeRepo
{
    protected $model;

    public function __construct(UserAccessTypeModel $model)
    {
        $this->model = $model;
    }

    public function checkRequestValidity($request): array
    {
        $validStatus = Validator::make($request->all(), [
            'user_type' => 'required|string',
//            'file' => 'required|image|mimes:jpg,jpeg,png,gif,svg|max:200',
        ]);
        return $validStatus->fails() ? array('isValidationSuccess' => false, 'error' => $validStatus->errors())
            : array('isValidationSuccess' => true);
    }


}
