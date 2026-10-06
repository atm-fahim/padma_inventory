<?php

namespace App\Interfaces\Repo\Backend;

use App\Interfaces\EloquentRepositoryInterface;

interface ExpanseRepo extends EloquentRepositoryInterface{
    public function checkRequestValidity($request);
}
