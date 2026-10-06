<?php

namespace App\Repository\Backend;

use App\Interfaces\Repo\Backend\InventoryReportRepo;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Repository implementation for Inventory Report (Stock In / Stock Out / Total).
 * All DB queries for inventory report live here.
 * Component item_ids (RAM=17, HDD=12, SSD=14, Processor=41) - main item (e.g. Laptop) used as package name.
 */
class InventoryReportRepoImpl implements InventoryReportRepo
{
    /** Item IDs that are components (RAM, HDD, SSD, Processor) - not used as package label when grouping by set */
    protected const COMPONENT_ITEM_IDS = [12, 14, 17, 41];

    /**
     * Get aggregated report: stock in, stock out, total qty by item or by set_uniq_no (package).
     */
    public function getReportData($orgId, $branchId, array $filters)
    {

        // Stock In = WAREHOUSE 'IT', Stock Out = WAREHOUSE 'IT-OUT' (table column)
        $componentIds = implode(',', array_map('intval', self::COMPONENT_ITEM_IDS));
        // $isSet
        //         ? DB::raw("COALESCE(MAX(CASE WHEN item_inventory.item_id NOT IN ({$componentIds}) THEN item_info.item_title END), MAX(item_info.item_title)) as item_title")
        //         : DB::raw('MAX(item_info.item_title) as item_title'),
        $selects = [
            DB::raw('MAX(item_info.item_title) as item_title'),
            DB::raw('MAX(item_inventory.set_uniq_no) as set_uniq_no'),
            DB::raw('MAX(category.category_title) as category_title'),
            DB::raw("SUM(CASE WHEN UPPER(TRIM(COALESCE(item_inventory.warehouse,''))) = 'IT' THEN item_inventory.quantity ELSE 0 END) as stock_in_qty"),
            DB::raw("SUM(CASE WHEN UPPER(TRIM(COALESCE(item_inventory.warehouse,''))) = 'IT-OUT' THEN item_inventory.quantity ELSE 0 END) as stock_out_qty")
        ];

       array_unshift($selects, 'item_inventory.item_id');
        
        $query = DB::table('item_inventory')
            ->select($selects)
            ->leftJoin('item_info', 'item_inventory.item_id', '=', 'item_info.id')
            ->leftJoin('category', 'item_inventory.category_id', '=', 'category.id')
            ->where('item_inventory.status', '<>', 9);

        $this->applyReportFilters($query, $orgId, $branchId, $filters);

       
        $query->groupBy('item_inventory.item_id');
        $query->orderBy('item_inventory.item_id');

        $rows = $query->get();

        foreach ($rows as $row) {
            $row->stock_in_qty = (int) $row->stock_in_qty;
            $row->stock_out_qty = (int) $row->stock_out_qty;
            $row->total_qty = $row->stock_in_qty - $row->stock_out_qty;
            // Row click details are currently item-based.
            $row->group_type = 'item';
            $row->group_id = (string) ($row->item_id ?? '');
        }
        return $rows;
    }

    /**
     * Get detail rows for a set_uniq_no or item_id.
     */
    public function getDetails($type, $id, $orgId = null, $branchId = null)
    {
        $query = DB::table('item_inventory')
            ->select(
                'item_inventory.id',
                'item_inventory.item_id',
                'item_inventory.set_uniq_no',
                'item_inventory.store_type',
                'item_inventory.warehouse',
                'item_inventory.quantity',
                'item_inventory.condition',
                'item_inventory.storage',
                'item_inventory.ram',
                'item_inventory.processor',
                'item_inventory.uniq_serial_no',
                'item_inventory.remarks',
                'item_inventory.stock_in_no',
                'item_inventory.stock_out_no',
                'item_inventory.created_date',
                'item_info.item_title',
                'category.category_title',
                'brand.brand_name'
            )
            ->leftJoin('item_info', 'item_inventory.item_id', '=', 'item_info.id')
            ->leftJoin('category', 'item_inventory.category_id', '=', 'category.id')
            ->leftJoin('brand', 'item_inventory.brand_id', '=', 'brand.id');

        if ($orgId) {
            $query->where('item_inventory.org_id', $orgId);
        }
        if ($branchId) {
            $query->where(function ($q) use ($branchId) {
                $q->where('item_inventory.branch_id', $branchId)
                    ->orWhere('item_inventory.to_branch_id', $branchId)
                    ->orWhere('item_inventory.from_branch_id', $branchId);
            });
        }

        if ($type === 'set') {
            $query->where('item_inventory.set_uniq_no', $id);
        } else {
            $query->where('item_inventory.item_id', (int) $id);
        }

        $rows = $query->orderBy('item_inventory.created_date', 'DESC')->get();

        foreach ($rows as $row) {
            $row->created_date_formatted = $row->created_date ? date('Y-m-d H:i', strtotime($row->created_date)) : '—';
            $row->type_label = (strtoupper(trim($row->warehouse ?? '')) === 'IT-OUT') ? 'Stock Out' : 'Stock In';
        }

        return $rows;
    }

    /**
     * Apply common filters to report query.
     */
    protected function applyReportFilters($query, $orgId, $branchId, array $filters): void
    {
        if ($orgId) {
            $query->where('item_inventory.org_id', $orgId);
        }
        if ($branchId) {
            $query->where(function ($q) use ($branchId) {
                $q->where('item_inventory.branch_id', $branchId)
                    ->orWhere('item_inventory.to_branch_id', $branchId)
                    ->orWhere('item_inventory.from_branch_id', $branchId);
            });
        }
        // dd($filters);
        if (!empty($filters['warehouse'])) {
            $query->where('item_inventory.warehouse', $filters['warehouse']);
        }
        if (!empty($filters['condition'])) {
            $query->where('item_inventory.condition', $filters['condition']);
        }
        if (!empty($filters['item_id'])) {
            $query->where('item_inventory.item_id', $filters['item_id']);
        }
        if (!empty($filters['category_id'])) {
            $query->where('item_inventory.category_id', $filters['category_id']);
        }
        if (!empty($filters['date_from'])) {
            $query->whereDate('item_inventory.created_date', '>=', $filters['date_from']);
        }
        if (!empty($filters['date_to'])) {
            $query->whereDate('item_inventory.created_date', '<=', $filters['date_to']);
        }
    }

    /**
     * Get full detail report with all columns and joined names (supplier, brand, branches).
     * User-wise: filter by created_by (store user) or to_employee_id when user_id given.
     */
    public function getDetailReport($orgId, $branchId, array $filters = [])
    {
        $hasDivisionColumn = Schema::hasColumn('branch', 'division_id');

        $query = DB::table('item_inventory')
            ->select(
                'item_inventory.id',
                'item_inventory.item_id',
                'item_inventory.category_id',
                'item_inventory.quantity',
                'item_inventory.status',
                'item_inventory.uniq_serial_no',
                'item_inventory.from_device_serial_no',
                'item_inventory.to_device_serial_no',
                'item_inventory.ram',
                'item_inventory.storage',
                'item_inventory.processor',
                'item_inventory.set_uniq_no',
                'item_inventory.service_warranty',
                'item_inventory.warranty',
                'item_inventory.supplier_id',
                'item_inventory.created_by as store_employee_id',
                'item_inventory.from_employee_id',
                'item_inventory.to_employee_id',
                'item_inventory.branch_id',
                'item_inventory.from_branch_id',
                'item_inventory.to_branch_id',
                'item_inventory.brand_id',
                'item_inventory.condition',
                'item_inventory.receive_remarks',
                'item_inventory.received_branch_id',
                'item_inventory.warehouse',
                'item_inventory.remarks',
                'item_inventory.stock_in_no',
                'item_inventory.stock_out_no',
                'item_inventory.created_date',
                'item_info.item_title as item_name',
                'category.category_title as category_name',
                'supplier.supplier_name',
                'brand.brand_name',
                'store_branch.branch_name as store_branch_name',
                'from_branch.branch_name as from_branch_name',
                'to_branch.branch_name as to_branch_name',
                DB::raw('COALESCE(rec_branch_hr.branch_name, rec_branch_pk.branch_name) as received_branch_name')
            )
            ->leftJoin('item_info', 'item_inventory.item_id', '=', 'item_info.id')
            ->leftJoin('category', 'item_inventory.category_id', '=', 'category.id')
            ->leftJoin('supplier', 'item_inventory.supplier_id', '=', 'supplier.id')
            ->leftJoin('brand', 'item_inventory.brand_id', '=', 'brand.id')
            ->leftJoin('branch as store_branch', 'item_inventory.branch_id', '=', 'store_branch.hr_branch_id')
            ->leftJoin('branch as from_branch', 'item_inventory.from_branch_id', '=', 'from_branch.hr_branch_id')
            ->leftJoin('branch as to_branch', 'item_inventory.to_branch_id', '=', 'to_branch.hr_branch_id')
            // received_branch_id may be hr_branch_id (common) or branch PK
            ->leftJoin('branch as rec_branch_hr', 'item_inventory.received_branch_id', '=', 'rec_branch_hr.hr_branch_id')
            ->leftJoin('branch as rec_branch_pk', 'item_inventory.received_branch_id', '=', 'rec_branch_pk.id')
            ->where('item_inventory.status', '<>', 9);

        if ($hasDivisionColumn) {
            $query->addSelect(DB::raw("COALESCE(store_division.division_name, from_division.division_name, to_division.division_name, rec_division_hr.division_name, rec_division_pk.division_name, 'Unassigned') as division_name"))
                ->leftJoin('division as store_division', 'store_branch.division_id', '=', 'store_division.id')
                ->leftJoin('division as from_division', 'from_branch.division_id', '=', 'from_division.id')
                ->leftJoin('division as to_division', 'to_branch.division_id', '=', 'to_division.id')
                ->leftJoin('division as rec_division_hr', 'rec_branch_hr.division_id', '=', 'rec_division_hr.id')
                ->leftJoin('division as rec_division_pk', 'rec_branch_pk.division_id', '=', 'rec_division_pk.id');
        } else {
            $query->addSelect(DB::raw("'Unassigned' as division_name"));
        }

        if ($orgId) {
            $query->where(function ($q) use ($orgId) {
                $q->where('item_inventory.org_id', $orgId)
                    ->orWhereNull('item_inventory.org_id')
                    ->orWhere('item_inventory.org_id', '')
                    ->orWhere('item_inventory.org_id', 0);
            });
        }

        $filterBranchId = !empty($filters['branch_id']) ? (int) $filters['branch_id'] : null;
        if (!$filterBranchId && $branchId) {
            $filterBranchId = (int) $branchId;
        }

        if ($filterBranchId) {
            $this->applyInventoryHrBranchScope($query, $filterBranchId);
        }

        $userId = trim((string) ($filters['user_id'] ?? ''));
        $toEmployeeId = trim((string) ($filters['to_employee_id'] ?? ''));
        $warehouse = trim((string) ($filters['warehouse'] ?? ''));
        $condition = trim((string) ($filters['condition'] ?? ''));
        $itemId = $filters['item_id'] ?? null;
        $categoryId = $filters['category_id'] ?? null;
        $divisionId = $filters['division_id'] ?? null;
        $dateFrom = trim((string) ($filters['date_from'] ?? ''));
        $dateTo = trim((string) ($filters['date_to'] ?? ''));

        if ($userId !== '') {
            $query->where('item_inventory.created_by', $userId);
        }
        if ($toEmployeeId !== '') {
            $query->where('item_inventory.to_employee_id', $toEmployeeId);
        }
        if ($warehouse !== '') {
            $query->whereRaw('UPPER(TRIM(item_inventory.warehouse)) = UPPER(TRIM(?))', [$warehouse]);
        }
        if ($condition !== '') {
            $query->whereRaw('UPPER(TRIM(item_inventory.condition)) = UPPER(TRIM(?))', [$condition]);
        }
        if (!empty($itemId)) {
            $query->where('item_inventory.item_id', (int) $itemId);
        }
        if (!empty($categoryId)) {
            $query->where(function ($q) use ($categoryId) {
                $q->where('item_inventory.category_id', (int) $categoryId)
                    ->orWhere('item_info.category_id', (int) $categoryId);
            });
        }
        if (!empty($divisionId) && $hasDivisionColumn) {
            $divisionId = (int) $divisionId;
            $query->where(function ($q) use ($divisionId) {
                $q->where('store_branch.division_id', $divisionId)
                    ->orWhere('from_branch.division_id', $divisionId)
                    ->orWhere('to_branch.division_id', $divisionId)
                    ->orWhere('rec_branch_hr.division_id', $divisionId)
                    ->orWhere('rec_branch_pk.division_id', $divisionId);
            });
        }
        if ($dateFrom !== '') {
            $query->whereDate('item_inventory.created_date', '>=', $dateFrom);
        }
        if ($dateTo !== '') {
            $query->whereDate('item_inventory.created_date', '<=', $dateTo);
        }

        $rows = $query->orderBy('item_inventory.created_date', 'DESC')->get();
        $showMainItemSpecs = $this->shouldShowMainItemSpecs($itemId);
        $setSpecMap = $showMainItemSpecs ? $this->buildSetSpecMap($rows, $orgId) : [];

        foreach ($rows as $row) {
            $row->created_date_formatted = $row->created_date ? date('Y-m-d H:i', strtotime($row->created_date)) : '';
            $row->status_label = $this->statusLabel($row->status ?? 0);
            $row->division_name = $row->division_name ?: 'Unassigned';
            $row->store_branch_name = $row->store_branch_name ?: '—';
            $row->received_branch_name = $row->received_branch_name
                ?: ($row->to_branch_name ?: null);
            if ($showMainItemSpecs) {
                $this->applySetSpecsToMainItemRow($row, $setSpecMap);
            } else {
                $this->applyComponentSpecVisibility($row);
            }
        }

        return $rows;
    }

    /**
     * When user filters by Desktop/Laptop/Server item,
     * show RAM/Storage/Processor on main item row from its set components.
     */
    protected function shouldShowMainItemSpecs($itemId): bool
    {
        $itemId = (int) ($itemId ?? 0);
        if ($itemId <= 0 || in_array($itemId, self::COMPONENT_ITEM_IDS, true)) {
            return false;
        }

        $title = (string) DB::table('item_info')->where('id', $itemId)->value('item_title');
        if ($title === '') {
            return false;
        }

        return (bool) preg_match('/\b(desktop|laptop|server|pc|computer)\b/i', $title);
    }

    protected function buildSetSpecMap($rows, $orgId = null): array
    {
        $setUniqNos = collect($rows)
            ->pluck('set_uniq_no')
            ->filter(fn ($v) => trim((string) $v) !== '')
            ->unique()
            ->values()
            ->all();

        if (empty($setUniqNos)) {
            return [];
        }

        $specRows = DB::table('item_inventory as ii')
            ->select(
                'ii.set_uniq_no',
                DB::raw("MAX(CASE WHEN ii.item_id = 17 THEN ii.ram END) as ram_value"),
                DB::raw("MAX(CASE WHEN ii.item_id IN (12,14) THEN ii.storage END) as storage_value"),
                DB::raw("MAX(CASE WHEN ii.item_id = 41 THEN ii.processor END) as processor_value")
            )
            ->whereIn('ii.set_uniq_no', $setUniqNos)
            ->where('ii.status', '<>', 9)
            ->groupBy('ii.set_uniq_no');

        if ($orgId) {
            $specRows->where(function ($q) use ($orgId) {
                $q->where('ii.org_id', $orgId)
                    ->orWhereNull('ii.org_id')
                    ->orWhere('ii.org_id', '')
                    ->orWhere('ii.org_id', 0);
            });
        }

        $map = [];
        foreach ($specRows->get() as $r) {
            $map[(string) $r->set_uniq_no] = [
                'ram' => $r->ram_value,
                'storage' => $r->storage_value,
                'processor' => $r->processor_value,
            ];
        }

        return $map;
    }

    protected function applySetSpecsToMainItemRow($row, array $setSpecMap): void
    {
        $itemId = (int) ($row->item_id ?? 0);
        $itemName = (string) ($row->item_name ?? '');
        $setNo = (string) ($row->set_uniq_no ?? '');
        $isMainDevice = !in_array($itemId, self::COMPONENT_ITEM_IDS, true)
            && (bool) preg_match('/\b(desktop|laptop|server|pc|computer)\b/i', $itemName);

        if (!$isMainDevice || $setNo === '' || !isset($setSpecMap[$setNo])) {
            return;
        }

        $row->ram = $row->ram ?: ($setSpecMap[$setNo]['ram'] ?? null);
        $row->storage = $row->storage ?: ($setSpecMap[$setNo]['storage'] ?? null);
        $row->processor = $row->processor ?: ($setSpecMap[$setNo]['processor'] ?? null);
    }

    /**
     * Show RAM / STORAGE / PROCESSOR only on matching component rows.
     * Main items (Desktop/Laptop/etc.) keep those columns empty.
     * RAM=17, HDD=12, SSD=14, Processor=41.
     */
    protected function applyComponentSpecVisibility($row): void
    {
        $itemId = (int) ($row->item_id ?? 0);
        $name = strtolower(trim((string) ($row->item_name ?? '')));

        $isRam = $itemId === 17 || (bool) preg_match('/\bram\b/i', $name);
        $isStorage = in_array($itemId, [12, 14], true)
            || (bool) preg_match('/\b(ssd|hdd|storage)\b/i', $name);
        $isProcessor = $itemId === 41
            || (bool) preg_match('/\b(processor|cpu)\b/i', $name);

        if ($isRam && !$isStorage && !$isProcessor) {
            $row->storage = null;
            $row->processor = null;
            return;
        }
        if ($isStorage && !$isRam && !$isProcessor) {
            $row->ram = null;
            $row->processor = null;
            return;
        }
        if ($isProcessor && !$isRam && !$isStorage) {
            $row->ram = null;
            $row->storage = null;
            return;
        }

        // Main device / other items: hide all three columns
        $row->ram = null;
        $row->storage = null;
        $row->processor = null;
    }

    /**
     * Branch + item wise summary using movement logic.
     * IT rows add quantity to base branch.
     * IT-OUT rows move quantity: subtract from from_branch and add to to_branch.
     */
    public function getDivisionBranchSummary($orgId, $branchId, array $filters = [])
    {
        $componentIds = implode(',', array_map('intval', self::COMPONENT_ITEM_IDS));
        $normalizedWarehouseExpr = "UPPER(TRIM(COALESCE(ii.warehouse,'')))";
        $filterLocation = trim((string) ($filters['location'] ?? ''));
        $filterDivisionId = $filters['division_id'] ?? '';
        $filterCategoryId = $filters['category_id'] ?? '';
        $filterItemId = $filters['item_id'] ?? '';
        $hasDivisionColumn = Schema::hasColumn('branch', 'division_id');

        $applyCommonFilters = function ($query) use ($orgId, $componentIds, $filterCategoryId, $filterItemId, $filters) {
            $query->where('ii.status', '<>', 9)
                ->whereRaw("ii.item_id NOT IN ({$componentIds})");
            if ($orgId) {
                $query->where('ii.org_id', $orgId);
            }
            if (!empty($filterCategoryId)) {
                $query->where('ii.category_id', $filterCategoryId);
            }
            if (!empty($filterItemId)) {
                $query->where('ii.item_id', $filterItemId);
            }
            if (!empty($filters['date_from'])) {
                $query->whereDate('ii.created_date', '>=', $filters['date_from']);
            }
            if (!empty($filters['date_to'])) {
                $query->whereDate('ii.created_date', '<=', $filters['date_to']);
            }
        };

        $divisionIdExpr = $hasDivisionColumn
            ? 'COALESCE(loc_division.id, 0)'
            : '0';
        $divisionNameExpr = $hasDivisionColumn
            ? "COALESCE(loc_division.division_name, 'Unassigned')"
            : "'Unassigned'";

        $joinLocationDivision = function ($query, string $branchAlias) use ($hasDivisionColumn) {
            if ($hasDivisionColumn) {
                $query->leftJoin('division as loc_division', $branchAlias . '.division_id', '=', 'loc_division.id');
            }
        };

        $stockInMovements = DB::table('item_inventory as ii')
            ->select(
                'ii.item_id',
                DB::raw('COALESCE(base_branch.hr_branch_id, 0) as branch_key'),
                DB::raw("COALESCE(base_branch.branch_name, 'Unassigned') as location_name"),
                DB::raw("{$divisionIdExpr} as division_id"),
                DB::raw("{$divisionNameExpr} as division_name"),
                DB::raw('CASE WHEN ii.quantity > 0 THEN ii.quantity ELSE 0 END as stock_in_qty'),
                DB::raw('0 as stock_out_qty'),
                DB::raw('ii.quantity as qty_delta')
            )
            ->leftJoin('branch as base_branch', 'ii.branch_id', '=', 'base_branch.hr_branch_id');
        $joinLocationDivision($stockInMovements, 'base_branch');
        $stockInMovements->whereRaw("{$normalizedWarehouseExpr} = 'IT'");
        $applyCommonFilters($stockInMovements);

        $stockOutFromMovements = DB::table('item_inventory as ii')
            ->select(
                'ii.item_id',
                DB::raw('COALESCE(from_branch.hr_branch_id, 0) as branch_key'),
                DB::raw("COALESCE(from_branch.branch_name, 'Unassigned') as location_name"),
                DB::raw("{$divisionIdExpr} as division_id"),
                DB::raw("{$divisionNameExpr} as division_name"),
                DB::raw('0 as stock_in_qty'),
                DB::raw('CASE WHEN ii.quantity > 0 THEN ii.quantity ELSE 0 END as stock_out_qty'),
                DB::raw('-ii.quantity as qty_delta')
            )
            ->leftJoin('branch as from_branch', 'ii.from_branch_id', '=', 'from_branch.hr_branch_id');
        $joinLocationDivision($stockOutFromMovements, 'from_branch');
        $stockOutFromMovements->whereRaw("{$normalizedWarehouseExpr} = 'IT-OUT'");
        $applyCommonFilters($stockOutFromMovements);

        $stockOutToMovements = DB::table('item_inventory as ii')
            ->select(
                'ii.item_id',
                DB::raw('COALESCE(to_branch.hr_branch_id, 0) as branch_key'),
                DB::raw("COALESCE(to_branch.branch_name, 'Unassigned') as location_name"),
                DB::raw("{$divisionIdExpr} as division_id"),
                DB::raw("{$divisionNameExpr} as division_name"),
                DB::raw('CASE WHEN ii.quantity > 0 THEN ii.quantity ELSE 0 END as stock_in_qty'),
                DB::raw('0 as stock_out_qty'),
                DB::raw('ii.quantity as qty_delta')
            )
            ->leftJoin('branch as to_branch', 'ii.to_branch_id', '=', 'to_branch.hr_branch_id');
        $joinLocationDivision($stockOutToMovements, 'to_branch');
        $stockOutToMovements->whereRaw("{$normalizedWarehouseExpr} = 'IT-OUT'");
        $applyCommonFilters($stockOutToMovements);

        $movements = $stockInMovements
            ->unionAll($stockOutFromMovements)
            ->unionAll($stockOutToMovements);

        $rows = DB::query()
            ->fromSub($movements, 'mv')
            ->leftJoin('item_info', 'mv.item_id', '=', 'item_info.id')
            ->select(
                'mv.item_id',
                'mv.branch_key',
                'mv.division_id',
                DB::raw('MAX(mv.division_name) as division_name'),
                DB::raw('MAX(mv.location_name) as location_name'),
                DB::raw('MAX(mv.location_name) as branch_name'),
                DB::raw('MAX(item_info.item_title) as item_name'),
                DB::raw('SUM(mv.stock_in_qty) as stock_in_qty'),
                DB::raw('SUM(mv.stock_out_qty) as stock_out_qty'),
                DB::raw('GREATEST(0, SUM(mv.qty_delta)) as total_qty')
            )
            ->groupBy('mv.division_id', 'mv.branch_key', 'mv.item_id')
            ->when($branchId, function ($q) use ($branchId) {
                $q->where('mv.branch_key', (int) $branchId);
            })
            ->when($filterLocation !== '' && strpos($filterLocation, 'b:') === 0, function ($q) use ($filterLocation) {
                $q->where('mv.branch_key', (int) substr($filterLocation, 2));
            })
            ->when($filterLocation !== '' && strpos($filterLocation, 'd:') === 0, function ($q) use ($filterLocation) {
                $q->where('mv.division_id', (int) substr($filterLocation, 2));
            })
            ->when(!empty($filterDivisionId), function ($q) use ($filterDivisionId) {
                $q->where('mv.division_id', (int) $filterDivisionId);
            })
            ->havingRaw('GREATEST(0, SUM(mv.qty_delta)) > 0')
            ->orderBy('division_name')
            ->orderBy('location_name')
            ->orderBy('item_name')
            ->get();

        foreach ($rows as $row) {
            $row->total_qty = (int) ($row->total_qty ?? 0);
            $row->stock_in_qty = (int) ($row->stock_in_qty ?? 0);
            $row->stock_out_qty = (int) ($row->stock_out_qty ?? 0);
            $row->division_id = (int) ($row->division_id ?? 0);
            $row->branch_key = (int) ($row->branch_key ?? 0);
            $row->division_name = $row->division_name ?: 'Unassigned';
            $row->location_name = $row->location_name ?: 'Unassigned';
            $row->branch_name = $row->branch_name ?: $row->location_name;
            $row->item_name = $row->item_name ?: 'Unknown Item';
        }

        return $rows;
    }

    /**
     * Match inventory rows by HR branch id (branch_id / to / from / received may be hr_branch_id or branch PK).
     */
    protected function applyInventoryHrBranchScope($query, $hrBranchId): void
    {
        $hrBranchId = (int) $hrBranchId;
        $query->where(function ($q) use ($hrBranchId) {
            $q->where('item_inventory.branch_id', $hrBranchId)
                ->orWhere('item_inventory.to_branch_id', $hrBranchId)
                ->orWhere('item_inventory.from_branch_id', $hrBranchId)
                ->orWhere('item_inventory.received_branch_id', $hrBranchId)
                ->orWhereExists(function ($sub) use ($hrBranchId) {
                    $sub->select(DB::raw(1))
                        ->from('branch as inv_recv_br')
                        ->whereColumn('inv_recv_br.id', 'item_inventory.received_branch_id')
                        ->where('inv_recv_br.hr_branch_id', $hrBranchId);
                });
        });
    }

    protected function statusLabel($status): string
    {
        $labels = [1 => 'In Stock', 2 => 'Stock Out', 3 => 'Received', 4 => 'Transferred'];
        return $labels[(int) $status] ?? 'Unknown';
    }
}
