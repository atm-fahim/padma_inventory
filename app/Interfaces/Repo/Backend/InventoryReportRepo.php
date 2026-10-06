<?php

namespace App\Interfaces\Repo\Backend;

interface InventoryReportRepo
{
    /**
     * Get aggregated report: stock in, stock out, total qty by item or by set_uniq_no.
     *
     * @param int|null $orgId
     * @param int|null $branchId
     * @param array $filters [warehouse, condition, item_id, category_id, date_from, date_to, group_by]
     * @return \Illuminate\Support\Collection
     */
    public function getReportData($orgId, $branchId, array $filters);

    /**
     * Get detail rows for a set_uniq_no or item_id (for row click / modal).
     *
     * @param string $type 'set'|'item'
     * @param string|int $id set_uniq_no or item_id
     * @param int|null $orgId
     * @param int|null $branchId
     * @return \Illuminate\Support\Collection
     */
    public function getDetails($type, $id, $orgId = null, $branchId = null);

    /**
     * Get full detail report: all inventory rows with item name, quantity, status, serial no, set_uniq_no,
     * warranty, supplier name, employee ids, branch names, brand name, condition, remarks. User-wise filter optional.
     *
     * @param int|null $orgId
     * @param int|null $branchId
     * @param array $filters [user_id, to_employee_id, branch_id, division_id, date_from, date_to, warehouse, condition, item_id, category_id]
     * @return \Illuminate\Support\Collection
     */
    public function getDetailReport($orgId, $branchId, array $filters = []);

    /**
     * Get division + branch wise device summary.
     *
     * @param int|null $orgId
     * @param int|null $branchId
     * @param array $filters [location => 'b:{hr_branch_id}'|'d:{division_id}'|'', division_id, category_id, item_id, date_from, date_to]
     * @return \Illuminate\Support\Collection
     */
    public function getDivisionBranchSummary($orgId, $branchId, array $filters = []);
}
