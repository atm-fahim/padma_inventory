<?php

namespace App\Interfaces\Repo\Backend;

use App\Interfaces\EloquentRepositoryInterface;

interface UserRepo extends EloquentRepositoryInterface{
    public function checkRequestValidity($request);
    public function instructorInfo();
    public function existUserInfo($email);
}
