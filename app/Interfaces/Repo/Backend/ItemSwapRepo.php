<?php

namespace App\Interfaces\Repo\Backend;

use App\Interfaces\EloquentRepositoryInterface;
use Illuminate\Support\Collection;

interface ItemSwapRepo extends EloquentRepositoryInterface
{
    public function checkRequestValidityForSwap($request): array;

    public function checkRequestValidityForReceiveSwap($request): array;

    public function getSwappableItemsByEmployee(?string $employeeId, $branchId = null): Collection;

    public function getPendingSwapsBySwapNo(string $swapNo): Collection;

    public function getPendingSwapsByEmployeeId(string $employeeId): Collection;

    public function employeesInSameBranchOrDivision($fromBranchId, $toBranchId): bool;

    public function generateSwapNo($branchId): string;

    public function initiateSwap(
        array $inventoryIds,
        string $fromEmployeeId,
        $fromBranchId,
        string $toEmployeeId,
        $toBranchId,
        $orgId,
        string $remarks = ''
    ): int;

    public function receiveSwapItems(string $swapNo, array $swapLogIds, array $remarks = []): int;
}
