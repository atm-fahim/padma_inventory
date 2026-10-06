<?php

namespace App\Interfaces\Repo\Backend;

use App\Interfaces\EloquentRepositoryInterface;
use Illuminate\Database\Eloquent\Model;

interface BackendMenuRepo extends EloquentRepositoryInterface
{

    public function checkRequestValidity($request);

    public function getParentMenu();
}
