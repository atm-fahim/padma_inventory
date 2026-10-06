<?php

namespace App\Repository\Backend;

use App\Interfaces\EloquentBaseRepository;
use App\Interfaces\Repo\Backend\UnitRepo;
use App\Models\UnitModel;
use Illuminate\Support\Facades\Validator;

/**
 * Repository implementation of unit (size) for the size model.
 * @develop by fahim
 * @author Kohinurit limited
 * @since 2023-01-20
 */
class UnitRepoImpl extends EloquentBaseRepository implements UnitRepo
{
    protected $model;

    public function __construct(UnitModel $model)
    {
        $this->model = $model;
    }

    public function checkRequestValidity($request): array
    {
        $validStatus = Validator::make($request->all(), [
            'size_unit' => 'required|string',
        ]);
        return $validStatus->fails()
            ? ['isValidationSuccess' => false, 'error' => $validStatus->errors()]
            : ['isValidationSuccess' => true];
    }
}


