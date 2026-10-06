<?php

namespace App\Repository\Backend;

use App\Interfaces\EloquentBaseRepository;
use App\Interfaces\Repo\Backend\BackendMenuRepo;
use App\Models\BackendMenuModel;
use Illuminate\Support\Facades\Validator;

/**
 * Repository implementation of menu for the backend model.
 * they are backend menu info
 * @develop by fahim
 * @author Padma Bank limited
 * @since 2023-08-20-08-20
 */
class BackendMenuRepoImpl extends EloquentBaseRepository implements BackendMenuRepo
{
    protected $model;

    public function __construct(BackendMenuModel $model)
    {
        $this->model = $model;
    }

    public function checkRequestValidity($request): array
    {
        $validStatus = Validator::make($request->all(), [
            'menu_name' => 'required|string',
        ]);
        return $validStatus->fails() ? array('isValidationSuccess' => false, 'error' => $validStatus->errors())
            : array('isValidationSuccess' => true);
    }

    public function getParentMenu()
    {
       // if (empty($orderId)) return null;
        return BackendMenuModel::where(['parent_id' => 0])->get();
    }


}
