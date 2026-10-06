<?php

namespace App\Interfaces\Repo\Backend;

use Illuminate\Support\Collection;

interface InventoryBarcodeRepo
{
    /**
     * Inventory rows eligible for barcode labels (grouped by SET_UNIQ_NO).
     *
     * @return Collection<int, object>
     */
    public function searchBarcodeSources(
        int $orgId,
        ?int $branchId = null,
        ?int $divisionId = null,
        ?int $itemId = null
    ): Collection;

    /**
     * @return Collection<int, object>
     */
    public function getItemsWithInventory(int $orgId): Collection;
}
