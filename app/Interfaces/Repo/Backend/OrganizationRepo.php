<?php

namespace App\Interfaces\Repo\Backend;

use App\Interfaces\EloquentRepositoryInterface;

interface OrganizationRepo extends EloquentRepositoryInterface{
    public function checkRequestValidity($request);
}
