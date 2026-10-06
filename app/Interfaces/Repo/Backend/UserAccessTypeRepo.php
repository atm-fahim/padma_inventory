<?php

namespace App\Interfaces\Repo\Backend;

use App\Interfaces\EloquentRepositoryInterface;

interface UserAccessTypeRepo extends EloquentRepositoryInterface{
    public function checkRequestValidity($request);
}
