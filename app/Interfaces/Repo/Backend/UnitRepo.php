<?php

namespace App\Interfaces\Repo\Backend;

use App\Interfaces\EloquentRepositoryInterface;

interface UnitRepo extends EloquentRepositoryInterface
{
    public function checkRequestValidity($request);
}


