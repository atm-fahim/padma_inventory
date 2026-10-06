<?php

namespace App\Http\Controllers\Backend;

use App\Exports\InventoryDetailReportExport;
use App\Exports\InventorySummaryReportExport;
use App\Exports\InventoryEmployeeReportExport;
use App\Interfaces\Repo\Backend\BranchRepo;
use App\Interfaces\Repo\Backend\CategoryRepo;
use App\Interfaces\Repo\Backend\ItemsRepo;
use App\Interfaces\Repo\Backend\InventoryReportRepo;
use App\Traits\UserAccessTraits;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf as PDF;

class InventoryReportController extends BaseController
{
    use UserAccessTraits;

    protected $inventoryReportRepo;
    protected $categoryRepo;
    protected $itemsRepo;
    protected $branchRepo;

    public function __construct(
        InventoryReportRepo $inventoryReportRepo,
        CategoryRepo $categoryRepo,
        ItemsRepo $itemsRepo,
        BranchRepo $branchRepo
    ) {
        $this->middleware('auth');
        $this->inventoryReportRepo = $inventoryReportRepo;
        $this->categoryRepo = $categoryRepo;
        $this->itemsRepo = $itemsRepo;
        $this->branchRepo = $branchRepo;
    }

    /**
     * Inventory report: Stock In (+), Stock Out (-), Total Qty with search filters.
     * Grouping is always by package (SET_UNIQ_NO).
     */
    public function index(Request $request)
    {
        $userAccessData = $this->userAccessFunction();
        $categoryData = $this->categoryRepo->getCategory();
        $itemsData = $this->itemsRepo->withoutDeletingData();
        $orgId = auth()->user()->org_id ?? null;
        $branchId = auth()->user()->branch_id ?? null;

        $filters = [
            'warehouse'   => $request->get('warehouse', ''),
            'condition'   => $request->get('condition', ''),
            'item_id'     => $request->get('item_id', ''),
            'category_id' => $request->get('category_id', ''),
            'date_from'   => $request->get('date_from', ''),
            'date_to'     => $request->get('date_to', ''),
            // 'group_by'    => 'set',
        ];

        try {
            $report = $this->inventoryReportRepo->getReportData($orgId, $branchId, $filters);
        } catch (\Throwable $e) {
            $report = collect([]);
            Log::warning('InventoryReportController::index getReportData failed: ' . $e->getMessage());
        }

        return view('backend.item.inventory_report')
            ->with('report', $report)
            ->with('categoryData', $categoryData)
            ->with('itemsData', $itemsData)
            ->with('user_access', $userAccessData)
            ->with('filters', $filters);
    }

    /**
     * Get details for a row: by set_uniq_no or by item_id (JSON for AJAX).
     */
    public function getDetails(Request $request)
    {
        $type = $request->get('type', 'item');
        $id = $request->get('id');
        $orgId = auth()->user()->org_id ?? null;
        $branchId = auth()->user()->branch_id ?? null;

        if (empty($id)) {
            return response()->json(['success' => false, 'message' => 'ID required'], 400);
        }

        $rows = $this->inventoryReportRepo->getDetails($type, $id, $orgId, $branchId);

        return response()->json(['success' => true, 'data' => $rows]);
    }

    /**
     * Full detail inventory report (all columns, user-wise filter, export Excel/PDF).
     */
    public function detailReport(Request $request)
    {
        $userAccessData = $this->userAccessFunction();
        $categoryData = $this->categoryRepo->getCategory();
        $itemsData = $this->itemsRepo->withoutDeletingData();
        $orgId = auth()->user()->org_id ?? null;
        $filters = $this->buildDetailFilters($request);

        $branchData = DB::table('branch')
            ->select('hr_branch_id', 'branch_name')
            ->where('status', '<>', 9)
            ->orderBy('branch_name')
            ->get();

        try {
            // Do not auto-limit by auth branch; optional Branch filter controls scope.
            $report = $this->inventoryReportRepo->getDetailReport($orgId, null, $filters);
        } catch (\Throwable $e) {
            $report = collect([]);
            Log::warning('InventoryReportController::detailReport failed: ' . $e->getMessage());
            if (config('app.debug')) {
                session()->flash('detail_report_error', $e->getMessage());
            }
        }

        return view('backend.item.inventory_detail_report')
            ->with('report', $report)
            ->with('branchData', $branchData)
            ->with('categoryData', $categoryData)
            ->with('itemsData', $itemsData)
            ->with('user_access', $userAccessData)
            ->with('filters', $filters);
    }

    /**
     * Export detail report to Excel.
     */
    public function exportExcel(Request $request)
    {
        $orgId = auth()->user()->org_id ?? null;
        $filters = $this->buildDetailFilters($request);
        $report = $this->inventoryReportRepo->getDetailReport($orgId, null, $filters);
        $meta = $this->buildDetailExportMeta($filters, $report->count());
        $filename = 'inventory_detail_report_' . date('Y-m-d_His') . '.xlsx';
        return Excel::download(new InventoryDetailReportExport($report, $meta), $filename);
    }

    /**
     * Export detail report to PDF.
     */
    public function exportPdf(Request $request)
    {
        $orgId = auth()->user()->org_id ?? null;
        $filters = $this->buildDetailFilters($request);
        $report = $this->inventoryReportRepo->getDetailReport($orgId, null, $filters);
        $meta = $this->buildDetailExportMeta($filters, $report->count());
        $pdf = PDF::loadView('backend.item.inventory_detail_report_pdf', [
            'report' => $report,
            'filters' => $filters,
            'organizationName' => $meta['organization_name'],
            'branchLabel' => $meta['branch_label'],
            'generatedAt' => $meta['generated_at'],
        ])->setPaper('a4', 'landscape');
        return $pdf->stream('inventory_detail_report_' . date('Y-m-d') . '.pdf');
    }

    /**
     * Heading meta for detail report Excel/PDF downloads.
     */
    protected function buildDetailExportMeta(array $filters, int $total = 0): array
    {
        $orgId = auth()->user()->org_id ?? null;
        $organizationName = null;

        if ($orgId) {
            $organizationName = DB::table('organization')
                ->where('id', $orgId)
                ->where('status', '<>', 9)
                ->value('org_name');
        }

        if (!$organizationName) {
            $organizationName = auth()->user()->org_name
                ?? config('app.name', 'Organization');
        }

        $branchId = trim((string) ($filters['branch_id'] ?? ''));
        if ($branchId !== '') {
            $branchName = DB::table('branch')
                ->where('hr_branch_id', $branchId)
                ->where('status', '<>', 9)
                ->value('branch_name');
            $branchLabel = $branchName ?: $branchId;
        } else {
            $branchLabel = 'All Branch';
        }

        return [
            'organization_name' => $organizationName,
            'branch_label' => $branchLabel,
            'generated_at' => date('Y-m-d H:i'),
            'total' => $total,
        ];
    }

    protected function buildDetailFilters(Request $request): array
    {
        return [
            'user_id' => $request->get('user_id', ''),
            'to_employee_id' => $request->get('to_employee_id', ''),
            'branch_id' => $request->get('branch_id', ''),
            'warehouse' => $request->get('warehouse', ''),
            'condition' => $request->get('condition', ''),
            'item_id' => $request->get('item_id', ''),
            'category_id' => $request->get('category_id', ''),
            'date_from' => $request->get('date_from', ''),
            'date_to' => $request->get('date_to', ''),
        ];
    }

    /**
     * Item / Branch / Division wise stock summary report.
     */
    public function summary(Request $request)
    {
        $userAccessData = $this->userAccessFunction();
        $categoryData = $this->categoryRepo->getCategory();
        $itemsData = $this->itemsRepo->withoutDeletingData();
        $orgId = auth()->user()->org_id ?? null;
        $filters = $this->buildSummaryFilters($request);

        $branchQuery = DB::table('branch')
            ->where('status', '<>', 9)
            ->orderBy('branch_name');

        if (\Illuminate\Support\Facades\Schema::hasColumn('branch', 'division_id')) {
            $branchData = $branchQuery->select('hr_branch_id', 'branch_name', 'division_id')->get();
        } else {
            $branchData = $branchQuery->select('hr_branch_id', 'branch_name')->get();
        }

        $divisionData = DB::table('division')
            ->select('id', 'division_name')
            ->where('status', '<>', 9)
            ->orderBy('division_name')
            ->get();

        try {
            $report = $this->inventoryReportRepo->getDivisionBranchSummary($orgId, null, $filters);
        } catch (\Throwable $e) {
            $report = collect([]);
            Log::warning('InventoryReportController::summary failed: ' . $e->getMessage(), [
                'exception' => $e,
            ]);
            if (config('app.debug')) {
                session()->flash('division_branch_report_error', $e->getMessage());
            }
        }

        $summary = $this->buildSummaryTotals($report);

        return view('backend.item.inventory_division_branch_summary')
            ->with('report', $report)
            ->with('summary', $summary)
            ->with('branchData', $branchData)
            ->with('divisionData', $divisionData)
            ->with('categoryData', $categoryData)
            ->with('itemsData', $itemsData)
            ->with('filters', $filters)
            ->with('user_access', $userAccessData);
    }

    /**
     * Export item/branch/division summary report to Excel.
     */
    public function summaryExportExcel(Request $request)
    {
        $orgId = auth()->user()->org_id ?? null;
        $filters = $this->buildSummaryFilters($request);
        $report = $this->inventoryReportRepo->getDivisionBranchSummary($orgId, null, $filters);
        $filename = 'inventory_item_branch_division_summary_' . date('Y-m-d_His') . '.xlsx';
        return Excel::download(new InventorySummaryReportExport($report), $filename);
    }

    /**
     * Export item/branch/division summary report to PDF.
     */
    public function summaryExportPdf(Request $request)
    {
        $orgId = auth()->user()->org_id ?? null;
        $filters = $this->buildSummaryFilters($request);
        $report = $this->inventoryReportRepo->getDivisionBranchSummary($orgId, null, $filters);
        $summary = $this->buildSummaryTotals($report);
        $pdf = PDF::loadView('backend.item.inventory_summary_report_pdf', compact('report', 'summary'))
            ->setPaper('a4', 'landscape');
        return $pdf->stream('inventory_item_branch_division_summary_' . date('Y-m-d') . '.pdf');
    }

    protected function buildSummaryFilters(Request $request): array
    {
        $location = trim((string) $request->get('location', ''));
        if ($location === '' && $request->filled('branch_id')) {
            $location = 'b:' . (int) $request->get('branch_id');
        }

        return [
            'location' => $location,
            'division_id' => $request->get('division_id', ''),
            'category_id' => $request->get('category_id', ''),
            'item_id' => $request->get('item_id', ''),
            'date_from' => $request->get('date_from', ''),
            'date_to' => $request->get('date_to', ''),
        ];
    }

    protected function buildSummaryTotals($report): array
    {
        $report = collect($report);
        $itemWise = $report->groupBy('item_id')->map(function ($rows) {
            $first = $rows->first();
            return (object) [
                'item_id' => $first->item_id ?? null,
                'item_name' => $first->item_name ?? 'Unknown Item',
                'branch_count' => $rows->pluck('branch_key')->unique()->count(),
                'total_qty' => (int) $rows->sum('total_qty'),
            ];
        })->sortByDesc('total_qty')->values();

        $branchWise = $report->groupBy('branch_key')->map(function ($rows) {
            $first = $rows->first();
            return (object) [
                'branch_key' => $first->branch_key ?? 0,
                'branch_name' => $first->branch_name ?? $first->location_name ?? 'Unassigned',
                'division_name' => $first->division_name ?? 'Unassigned',
                'item_count' => $rows->pluck('item_id')->unique()->count(),
                'total_qty' => (int) $rows->sum('total_qty'),
            ];
        })->sortByDesc('total_qty')->values();

        $divisionWise = $report->groupBy(function ($row) {
            return (string) ($row->division_id ?? 0);
        })->map(function ($rows) {
            $first = $rows->first();
            return (object) [
                'division_id' => $first->division_id ?? 0,
                'division_name' => $first->division_name ?? 'Unassigned',
                'branch_count' => $rows->pluck('branch_key')->unique()->count(),
                'item_count' => $rows->pluck('item_id')->unique()->count(),
                'total_qty' => (int) $rows->sum('total_qty'),
            ];
        })->sortByDesc('total_qty')->values();

        return [
            'total_qty' => (int) $report->sum('total_qty'),
            'item_count' => $report->pluck('item_id')->unique()->count(),
            'branch_count' => $report->pluck('branch_key')->unique()->count(),
            'division_count' => $report->pluck('division_id')->unique()->count(),
            'row_count' => $report->count(),
            'item_wise' => $itemWise,
            'branch_wise' => $branchWise,
            'division_wise' => $divisionWise,
        ];
    }

    /**
     * Employee-wise inventory detail report.
     */
    public function employeeReport(Request $request)
    {
        $userAccessData = $this->userAccessFunction();
        $categoryData = $this->categoryRepo->getCategory();
        $itemsData = $this->itemsRepo->withoutDeletingData();
        $orgId = auth()->user()->org_id ?? null;
        $branchId = auth()->user()->branch_id ?? null;

        $employeeId = trim((string) $request->get('employee_id', ''));
        $filters = [
            'user_id' => '',
            'to_employee_id' => '',
            'warehouse' => $request->get('warehouse', ''),
            'condition' => $request->get('condition', ''),
            'item_id' => $request->get('item_id', ''),
            'category_id' => $request->get('category_id', ''),
            'date_from' => $request->get('date_from', ''),
            'date_to' => $request->get('date_to', ''),
        ];

        try {
            $report = $this->buildEmployeeReportData($orgId, $branchId, $filters, $employeeId);
        } catch (\Throwable $e) {
            $report = collect([]);
            Log::warning('InventoryReportController::employeeReport failed: ' . $e->getMessage());
        }

        return view('backend.item.inventory_employee_report')
            ->with('report', $report)
            ->with('categoryData', $categoryData)
            ->with('itemsData', $itemsData)
            ->with('filters', [
                'employee_id' => $employeeId,
                'warehouse' => $filters['warehouse'],
                'condition' => $filters['condition'],
                'item_id' => $filters['item_id'],
                'category_id' => $filters['category_id'],
                'date_from' => $filters['date_from'],
                'date_to' => $filters['date_to'],
            ])
            ->with('user_access', $userAccessData);
    }

    public function employeeReportExportExcel(Request $request)
    {
        $orgId = auth()->user()->org_id ?? null;
        $branchId = auth()->user()->branch_id ?? null;
        $employeeId = trim((string) $request->get('employee_id', ''));
        $filters = [
            'user_id' => '',
            'to_employee_id' => '',
            'warehouse' => $request->get('warehouse', ''),
            'condition' => $request->get('condition', ''),
            'item_id' => $request->get('item_id', ''),
            'category_id' => $request->get('category_id', ''),
            'date_from' => $request->get('date_from', ''),
            'date_to' => $request->get('date_to', ''),
        ];

        $report = $this->buildEmployeeReportData($orgId, $branchId, $filters, $employeeId);
        $filename = 'inventory_employee_report_' . date('Y-m-d_His') . '.xlsx';
        return Excel::download(new InventoryEmployeeReportExport($report), $filename);
    }

    public function employeeReportExportPdf(Request $request)
    {
        $orgId = auth()->user()->org_id ?? null;
        $branchId = auth()->user()->branch_id ?? null;
        $employeeId = trim((string) $request->get('employee_id', ''));
        $filters = [
            'user_id' => '',
            'to_employee_id' => '',
            'warehouse' => $request->get('warehouse', ''),
            'condition' => $request->get('condition', ''),
            'item_id' => $request->get('item_id', ''),
            'category_id' => $request->get('category_id', ''),
            'date_from' => $request->get('date_from', ''),
            'date_to' => $request->get('date_to', ''),
        ];

        $report = $this->buildEmployeeReportData($orgId, $branchId, $filters, $employeeId);
        $pdf = PDF::loadView('backend.item.inventory_employee_report_pdf', compact('report'))
            ->setPaper('a4', 'landscape');
        return $pdf->stream('inventory_employee_report_' . date('Y-m-d') . '.pdf');
    }

    protected function buildEmployeeReportData($orgId, $branchId, array $filters, string $employeeId = '')
    {
        $report = $this->inventoryReportRepo->getDetailReport($orgId, $branchId, $filters);

        $componentItemIds = [12, 14, 17, 41];
        $report = $report->filter(function ($row) use ($componentItemIds) {
            return !in_array((int) ($row->item_id ?? 0), $componentItemIds, true);
        })->values();

        if ($employeeId !== '') {
            $report = $report->filter(function ($row) use ($employeeId) {
                return (string) ($row->store_employee_id ?? '') === $employeeId
                    || (string) ($row->from_employee_id ?? '') === $employeeId
                    || (string) ($row->to_employee_id ?? '') === $employeeId;
            })->values();
        }

        return $report->groupBy(function ($row) {
            $setKey = trim((string) ($row->set_uniq_no ?? ''));
            if ($setKey === '') {
                $setKey = trim((string) ($row->stock_in_no ?? ''));
            }
            if ($setKey === '') {
                $setKey = trim((string) ($row->stock_out_no ?? ''));
            }
            if ($setKey === '') {
                $setKey = 'ROW-' . (string) ($row->id ?? uniqid('', true));
            }

            return implode('|', [
                (string) ($row->item_id ?? ''),
                $setKey,
                (string) ($row->store_employee_id ?? ''),
                (string) ($row->from_employee_id ?? ''),
                (string) ($row->to_employee_id ?? ''),
                (string) ($row->from_branch_name ?? ''),
                (string) ($row->to_branch_name ?? ''),
                (string) ($row->received_branch_name ?? ''),
                (string) ($row->warehouse ?? ''),
            ]);
        })->map(function ($group) {
            $first = $group->first();
            $first->quantity = (int) $group->sum(function ($r) {
                return (int) ($r->quantity ?? 0);
            });
            $first->uniq_serial_no = $group->count() > 1 ? 'Multiple' : ($first->uniq_serial_no ?? '');
            return $first;
        })->values();
    }
}
