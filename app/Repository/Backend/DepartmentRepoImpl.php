<?php

namespace App\Repository\Backend;

use App\Interfaces\EloquentBaseRepository;
use App\Interfaces\Repo\Backend\DepartmentRepo;
use App\Models\DepartmentModel;
use App\Models\DivisionModel;
use Illuminate\Support\Facades\Validator;

/**
 * Repository implementation of department for the department model.
 * they are department info
 * @develop by fahim
 * @author Padma Bank limited
 * @since 2023-11-26
 */
class DepartmentRepoImpl extends EloquentBaseRepository implements DepartmentRepo
{


    protected $model;

    public function __construct(DepartmentModel $model)
    {
        $this->model = $model;
    }

    public function checkRequestValidity($request): array
    {
        $validStatus = Validator::make($request->all(), [
            'department_name' => 'required|string',
            'short_name' => 'required|string',
        ]);
        return $validStatus->fails() ? array('isValidationSuccess' => false, 'error' => $validStatus->errors())
            : array('isValidationSuccess' => true);
    }

    public function departmentInfo()
    {
        return $this->model::select('department.*','division.division_name')
            ->leftJoin('division','division.id','department.division_id')
            ->where('department.status','<>',9)
            ->get();
    }

    public function getDepartmentInfoById($id)
    {
        return $this->model::where('id', $id)->first();
    }

    public function departmentInfoByDivisionId($id)
    {
        return $this->model::where('division_id', $id)->get();
    }
    public function getDepartmentId($department)
    {
        return $this->model::where('slug', $department)->first();
    }
}
