<?php

namespace App\Interfaces\Repo\Backend;

use App\Interfaces\EloquentRepositoryInterface;

interface PaymentMethodRepo extends EloquentRepositoryInterface{
    public function checkRequestValidity($request);
}
