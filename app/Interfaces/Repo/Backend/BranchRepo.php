<?php

namespace App\Interfaces\Repo\Backend;

use App\Interfaces\EloquentRepositoryInterface;

interface BranchRepo extends EloquentRepositoryInterface{
    public function checkRequestValidity($request);

    public function getBranchByName($branchName);
}
