<?php

namespace App\Interfaces\Repo\Backend;

use App\Interfaces\EloquentRepositoryInterface;

interface StockInRepo extends EloquentRepositoryInterface
{
    public function checkRequestValidity($request);
    
    public function getItemInventoryByItemId($itemId, $orgId = null, $branchId = null);
    
    public function getItemsBySetUniqNo($setUniqNo, $orgId, $branchId);
    
    public function getStockInList($orgId = null, $branchId = null);
    
    public function getStockInByStockInNo($stockInNo, $orgId = null);
}
