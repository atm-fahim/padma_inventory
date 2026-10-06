<?php

namespace App\Interfaces\Repo\Backend;

use App\Interfaces\EloquentRepositoryInterface;

interface ItemsInventoryRepo extends EloquentRepositoryInterface
{
    public function checkRequestValidity($request);

    public function getItemInventoryInfo();

    public function getSetUniqNoList($orgId = null, $branchId = null);

}
