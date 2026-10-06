<?php

namespace App\Repository\Backend;

use App\Interfaces\Repo\Backend\InventoryBarcodeRepo;
use App\Models\ItemsInventoryModel;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class InventoryBarcodeRepoImpl implements InventoryBarcodeRepo
{
    /** @var array<int, int> Spec component item_ids excluded from main label list */
    private array $excludeSpecItemIds = [17, 41, 12, 14];

    public function searchBarcodeSources(
        int $orgId,
        ?int $branchId = null,
        ?int $divisionId = null,
        ?int $itemId = null
    ): Collection {
        $mainRowIds = DB::table('item_inventory')
            ->select(DB::raw('MAX(item_inventory.id) as id'))
            ->where('item_inventory.status', '<>', 9)
            ->whereNotIn('item_inventory.item_id', $this->excludeSpecItemIds)
            ->where(function ($q) use ($orgId) {
                $q->where('item_inventory.org_id', $orgId)
                    ->orWhereNull('item_inventory.org_id')
                    ->orWhere('item_inventory.org_id', '');
            });
        $this->applyNonEmptySetUniqNo($mainRowIds, 'item_inventory.set_uniq_no');
        $mainRowIds->groupBy('item_inventory.set_uniq_no');

        $query = ItemsInventoryModel::query()
            ->select(
                'item_inventory.set_uniq_no',
                'item_inventory.id as inventory_id',
                'item_inventory.item_id',
                'item_inventory.branch_id',
                'item_info.item_title',
                'brand.brand_name',
                'category.category_title',
                'item_inventory.price'
            )
            ->joinSub($mainRowIds, 'main_rows', function ($join) {
                $join->on('item_inventory.id', '=', 'main_rows.id');
            })
            ->leftJoin('item_info', 'item_inventory.item_id', '=', 'item_info.id')
            ->leftJoin('brand', 'item_inventory.brand_id', '=', 'brand.id')
            ->leftJoin('category', 'item_inventory.category_id', '=', 'category.id');

        if ($itemId) {
            $query->where('item_inventory.item_id', $itemId);
        }

        if ($branchId) {
            $this->applyBranchFilter($query, $branchId);
        } elseif ($divisionId) {
            $this->applyDivisionFilter($query, $divisionId);
        }

        return $query
            ->orderBy('item_inventory.set_uniq_no')
            ->get();
    }

    public function getItemsWithInventory(int $orgId): Collection
    {
        $query = DB::table('item_inventory')
            ->join('item_info', 'item_inventory.item_id', '=', 'item_info.id')
            ->where('item_inventory.status', '<>', 9)
            ->whereNotIn('item_inventory.item_id', $this->excludeSpecItemIds)
            ->where(function ($q) use ($orgId) {
                $q->where('item_inventory.org_id', $orgId)
                    ->orWhereNull('item_inventory.org_id');
            });

        $this->applyNonEmptySetUniqNo($query, 'item_inventory.set_uniq_no');

        return $query
            ->select('item_info.id', 'item_info.item_title')
            ->distinct()
            ->orderBy('item_info.item_title')
            ->get();
    }

    /**
     * Oracle treats CLOB / empty-string comparisons differently; use LENGTH(TRIM(...)).
     */
    private function applyNonEmptySetUniqNo(Builder $query, string $column): void
    {
        $query->whereNotNull($column)
            ->whereRaw('length(trim(' . $column . ')) > 0');
    }

    private function applyBranchFilter($query, int $branchKey): void
    {
        $keys = $this->branchKeysForDropdownValue($branchKey);
        if ($keys === []) {
            $query->whereRaw('1 = 0');

            return;
        }

        $query->whereIn('item_inventory.branch_id', $this->branchIdInList($keys));
    }

    private function applyDivisionFilter($query, int $divisionId): void
    {
        if (!Schema::hasColumn('branch', 'division_id')) {
            return;
        }

        $branchRows = DB::table('branch')
            ->where('division_id', $divisionId)
            ->where(function ($q) {
                $q->whereNull('status')->orWhere('status', '<>', 9);
            })
            ->get(['id', 'hr_branch_id']);

        $keys = [];
        foreach ($branchRows as $row) {
            if (!empty($row->id)) {
                $keys[] = (int) $row->id;
            }
            if (!empty($row->hr_branch_id)) {
                $keys[] = (int) $row->hr_branch_id;
            }
        }
        $keys = array_values(array_unique($keys));

        if ($keys === []) {
            $query->whereRaw('1 = 0');

            return;
        }

        $query->whereIn('item_inventory.branch_id', $this->branchIdInList($keys));
    }

    /**
     * Dropdown uses hr_branch_id when present; avoid matching unrelated branch.id = same number.
     *
     * @return array<int, int>
     */
    private function branchKeysForDropdownValue(int $branchKey): array
    {
        $byHr = DB::table('branch')->where('hr_branch_id', $branchKey)->get(['id', 'hr_branch_id']);
        if ($byHr->isNotEmpty()) {
            $keys = [];
            foreach ($byHr as $row) {
                if (!empty($row->id)) {
                    $keys[] = (int) $row->id;
                }
                if (!empty($row->hr_branch_id)) {
                    $keys[] = (int) $row->hr_branch_id;
                }
            }

            return array_values(array_unique($keys));
        }

        $byId = DB::table('branch')->where('id', $branchKey)->first(['id', 'hr_branch_id']);
        if ($byId) {
            $keys = [(int) $byId->id];
            if (!empty($byId->hr_branch_id)) {
                $keys[] = (int) $byId->hr_branch_id;
            }

            return array_values(array_unique($keys));
        }

        return [$branchKey];
    }

    /**
     * @param array<int, int> $keys
     * @return array<int, int|string>
     */
    private function branchIdInList(array $keys): array
    {
        $in = [];
        foreach ($keys as $key) {
            if ($key === '' || $key === null) {
                continue;
            }
            $in[] = (int) $key;
            $in[] = (string) (int) $key;
        }

        return array_values(array_unique($in, SORT_REGULAR));
    }
}
