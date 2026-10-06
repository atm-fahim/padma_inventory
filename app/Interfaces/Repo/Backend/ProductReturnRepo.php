<?php

namespace App\Interfaces\Repo\Backend;

use App\Interfaces\EloquentRepositoryInterface;

interface ProductReturnRepo extends EloquentRepositoryInterface
{
    public function checkRequestValidity($request);

    public function generateReturnNo(): string;

    public function createWithItems(array $returnData, array $itemsData);

    public function findWithItemsById($id);

    public function getReturnList($orgId = null, $branchId = null);
}
