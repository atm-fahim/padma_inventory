<?php

namespace App\Interfaces\Repo\Backend;

use App\Interfaces\EloquentRepositoryInterface;

interface RequisitionRepo extends EloquentRepositoryInterface
{
    public function checkRequestValidity($request);

    public function generateRequisitionNo(): string;

    public function createWithItems(array $requisitionData, array $itemsData);

    public function findWithItemsById($id);

    public function getRequisitionList($orgId = null, $branchId = null);
}
