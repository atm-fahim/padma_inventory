<?php

namespace App\Interfaces\Repo\Backend;

use App\Interfaces\EloquentRepositoryInterface;

interface DeliveryChallanRepo extends EloquentRepositoryInterface
{
    public function checkRequestValidity($request);

    public function generateChallanNo(): string;

    public function createWithItems(array $challanData, array $itemsData);

    public function findWithItemsById($id);
}

