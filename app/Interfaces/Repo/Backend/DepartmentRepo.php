<?php

namespace App\Interfaces\Repo\Backend;

use App\Interfaces\EloquentRepositoryInterface;

interface DepartmentRepo extends EloquentRepositoryInterface
{
    public function checkRequestValidity($request);

    public function departmentInfo();

    public function getDepartmentInfoById($id);
    public function departmentInfoByDivisionId($id);
    public function getDepartmentId($department);
}
