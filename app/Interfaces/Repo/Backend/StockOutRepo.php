<?php

namespace App\Interfaces\Repo\Backend;

use App\Interfaces\EloquentRepositoryInterface;

interface StockOutRepo extends EloquentRepositoryInterface
{
    public function checkRequestValidity($request);
    
    public function getEmployeeInfoByEmployeeId($employeeId);
    
    public function getItemInventoryByItemId($itemId, $orgId = null, $branchId = null);
    
    public function getItemsBySetUniqNo($setUniqNo, $orgId, $branchId);
    
    public function getStockOutList($orgId = null, $branchId = null);

    public function logGatePassEvent(string $stockOutNo, string $eventType, ?int $userId = null, ?int $orgId = null): void;

    public function countGatePassEvents(string $stockOutNo, string $eventType): int;
    
    public function getStockOutByStockOutNo($stockOutNo);
    
    public function getStockOutByEmployeeId($employeeId);
    
    public function receiveStockOutItems($stockOutNo, $inventoryIds, array $remarks = []);
    
    public function checkRequestValidityForReceive($request);
    
    public function getReceivedItemsByEmployee($branchId = null, $employeeId = null);
    
    public function transferItemsToEmployee($inventoryIds, $toEmployeeId, $toBranchId, $fromEmployeeId, $fromBranchId, $orgId, $fromDeviceSerialNo = null, $toDeviceSerialNo = null, $remarks = null);
    
    public function checkRequestValidityForTransfer($request);
    
    public function returnItemsToDivision($inventoryIds, $divisionId, $fromEmployeeId, $fromBranchId, $orgId, $fromDeviceSerialNo = null, $toBranchId = null, $condition = null, $remarks = null);
    
    public function checkRequestValidityForReturnToDivision($request);
    
    public function getItemsForReturnToDivision($employeeId = null);
}

