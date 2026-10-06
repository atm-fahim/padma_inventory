@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="{{ asset('frontend/css/item-inventory.css') }}">
<style>
    .summary-hero {
        background: linear-gradient(135deg, #0f4c5c 0%, #1b6ca8 55%, #28a745 100%);
        border-radius: 14px;
        color: #fff;
        padding: 1.25rem 1.5rem;
        margin-bottom: 1.25rem;
        box-shadow: 0 10px 24px rgba(15, 76, 92, 0.18);
    }
    .summary-hero h4 { margin: 0; font-weight: 700; letter-spacing: .2px; }
    .summary-hero p { margin: .35rem 0 0; opacity: .9; font-size: .9rem; }
    .kpi-card {
        border: 0;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 6px 16px rgba(0,0,0,.06);
        height: 100%;
    }
    .kpi-card .kpi-label { font-size: .78rem; text-transform: uppercase; letter-spacing: .04em; opacity: .85; }
    .kpi-card .kpi-value { font-size: 1.75rem; font-weight: 700; line-height: 1.2; }
    .kpi-total { background: linear-gradient(135deg, #0d6efd, #0a58ca); color: #fff; }
    .kpi-item { background: linear-gradient(135deg, #198754, #146c43); color: #fff; }
    .kpi-branch { background: linear-gradient(135deg, #fd7e14, #e8590c); color: #fff; }
    .kpi-division { background: linear-gradient(135deg, #6f42c1, #5a32a3); color: #fff; }
    .report-section-title {
        font-size: 1rem;
        font-weight: 700;
        margin-bottom: .75rem;
        display: flex;
        align-items: center;
        gap: .4rem;
    }
    .table thead.table-deep th {
        background: #0f4c5c;
        color: #fff;
        white-space: nowrap;
        font-size: .85rem;
    }
    .qty-badge {
        display: inline-block;
        min-width: 2.5rem;
        padding: .2rem .55rem;
        border-radius: 999px;
        background: #e7f1ff;
        color: #0a58ca;
        font-weight: 700;
    }
    .sub-summary-card { border-radius: 12px; border: 1px solid #e9ecef; }
</style>

<div class="container card border-top border-0 border-4 border-info">
    <div class="row justify-content-center">
        <div class="col-lg-12">
            <div class="summary-hero">
                <div class="row align-items-center">
                    <div class="col-lg-7">
                        <h4><i class="bx bx-bar-chart-alt-2 me-2"></i>Item / Branch / Division Stock Summary</h4>
                        <p>Item-wise current stock by Branch and Division. Use filters to narrow, then export Excel or PDF.</p>
                    </div>
                    <div class="col-lg-5 text-lg-end mt-3 mt-lg-0">
                        @php $exportParams = http_build_query($filters ?? []); @endphp
                        <a href="{{ route('inventory-report') }}" class="btn btn-light btn-sm me-1 mb-1">
                            <i class="bx bx-pie-chart-alt me-1"></i>Inventory Report
                        </a>
                        <a href="{{ route('inventory-detail-report') }}" class="btn btn-light btn-sm me-1 mb-1">
                            <i class="bx bx-detail me-1"></i>Detail
                        </a>
                        <a href="{{ route('inventory-summary-export-excel') }}?{{ $exportParams }}" class="btn btn-success btn-sm me-1 mb-1">
                            <i class="bx bx-file me-1"></i>Excel
                        </a>
                        <a href="{{ route('inventory-summary-export-pdf') }}?{{ $exportParams }}" class="btn btn-danger btn-sm mb-1" target="_blank">
                            <i class="bx bx-file-blank me-1"></i>PDF
                        </a>
                    </div>
                </div>
            </div>

            @if(session('division_branch_report_error'))
                <div class="alert alert-danger py-2 small mb-3" role="alert">
                    <strong>Report query error (debug):</strong> {{ session('division_branch_report_error') }}
                </div>
            @endif

            @php
                $summary = $summary ?? [
                    'total_qty' => 0,
                    'item_count' => 0,
                    'branch_count' => 0,
                    'division_count' => 0,
                    'item_wise' => collect(),
                    'branch_wise' => collect(),
                    'division_wise' => collect(),
                ];
            @endphp

            <div class="row g-3 mb-3">
                <div class="col-md-3 col-6">
                    <div class="card kpi-card kpi-total">
                        <div class="card-body">
                            <div class="kpi-label"><i class="bx bx-package me-1"></i>Total Quantity</div>
                            <div class="kpi-value">{{ number_format($summary['total_qty'] ?? 0) }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="card kpi-card kpi-item">
                        <div class="card-body">
                            <div class="kpi-label"><i class="bx bx-cube me-1"></i>Unique Items</div>
                            <div class="kpi-value">{{ number_format($summary['item_count'] ?? 0) }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="card kpi-card kpi-branch">
                        <div class="card-body">
                            <div class="kpi-label"><i class="bx bx-buildings me-1"></i>Branches</div>
                            <div class="kpi-value">{{ number_format($summary['branch_count'] ?? 0) }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="card kpi-card kpi-division">
                        <div class="card-body">
                            <div class="kpi-label"><i class="bx bx-sitemap me-1"></i>Divisions</div>
                            <div class="kpi-value">{{ number_format($summary['division_count'] ?? 0) }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="bx bx-filter-alt me-2"></i>Filters</h5>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('inventory-summary') }}" class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Division</label>
                            <select name="division_id" class="form-select form-select-sm single-select-4">
                                <option value="">— All Divisions —</option>
                                @foreach(($divisionData ?? []) as $division)
                                    <option value="{{ $division->id }}" {{ (string)($filters['division_id'] ?? '') === (string)$division->id ? 'selected' : '' }}>
                                        {{ $division->division_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Branch</label>
                            <select name="location" class="form-select form-select-sm single-select-4">
                                <option value="">— All Branches —</option>
                                @foreach($branchData as $branch)
                                    @php $locVal = 'b:' . $branch->hr_branch_id; @endphp
                                    <option value="{{ $locVal }}" {{ ($filters['location'] ?? '') === $locVal ? 'selected' : '' }}>
                                        {{ $branch->branch_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Category</label>
                            <select name="category_id" class="form-select form-select-sm single-select-4">
                                <option value="">All</option>
                                @foreach($categoryData as $cat)
                                    <option value="{{ $cat->id }}" {{ ($filters['category_id'] ?? '') == $cat->id ? 'selected' : '' }}>{{ $cat->category_title }}</option>
                                    @if(!empty($cat->children))
                                        @foreach($cat->children as $child)
                                            <option value="{{ $child->id }}" {{ ($filters['category_id'] ?? '') == $child->id ? 'selected' : '' }}>— {{ $child->category_title }}</option>
                                        @endforeach
                                    @endif
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Item</label>
                            <select name="item_id" class="form-select form-select-sm single-select-4">
                                <option value="">All</option>
                                @foreach($itemsData as $item)
                                    <option value="{{ $item->id }}" {{ ($filters['item_id'] ?? '') == $item->id ? 'selected' : '' }}>{{ $item->item_title ?? $item->item }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-1">
                            <label class="form-label">From</label>
                            <input type="date" name="date_from" class="form-control form-control-sm" value="{{ $filters['date_from'] ?? '' }}">
                        </div>
                        <div class="col-md-1">
                            <label class="form-label">To</label>
                            <input type="date" name="date_to" class="form-control form-control-sm" value="{{ $filters['date_to'] ?? '' }}">
                        </div>
                        <div class="col-md-12 d-flex align-items-end">
                            <button type="submit" class="btn btn-info btn-sm me-2"><i class="bx bx-search me-1"></i>Search</button>
                            <a href="{{ route('inventory-summary') }}" class="btn btn-outline-secondary btn-sm">Clear</a>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-body">
                    <div class="report-section-title"><i class="bx bx-table"></i> Detail Report (Item × Branch × Division)</div>
                    <div class="table-responsive">
                        <table id="divisionBranchSummaryTable" class="table table-bordered table-striped table-hover align-middle">
                            <thead class="table-deep">
                                <tr>
                                    <th>SL</th>
                                    <th>Division</th>
                                    <th>Branch</th>
                                    <th>Item</th>
                                    <th class="text-end">Stock In</th>
                                    <th class="text-end">Stock Out</th>
                                    <th class="text-end">Current Qty</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($report as $index => $row)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>
                                            <span class="badge bg-light text-dark border">{{ $row->division_name ?? 'Unassigned' }}</span>
                                        </td>
                                        <td class="fw-semibold">{{ $row->branch_name ?? $row->location_name ?? '—' }}</td>
                                        <td>{{ $row->item_name ?? 'Unknown Item' }}</td>
                                        <td class="text-end text-success">{{ (int)($row->stock_in_qty ?? 0) }}</td>
                                        <td class="text-end text-danger">{{ (int)($row->stock_out_qty ?? 0) }}</td>
                                        <td class="text-end"><span class="qty-badge">{{ (int)($row->total_qty ?? 0) }}</span></td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-4 text-muted">
                                            <i class="bx bx-info-circle me-1"></i>No summary data found.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                            @if(count($report) > 0)
                            <tfoot class="table-light">
                                <tr>
                                    <th colspan="4" class="text-end">Grand Total</th>
                                    <th class="text-end text-success">{{ $report->sum('stock_in_qty') }}</th>
                                    <th class="text-end text-danger">{{ $report->sum('stock_out_qty') }}</th>
                                    <th class="text-end"><span class="qty-badge">{{ $summary['total_qty'] ?? $report->sum('total_qty') }}</span></th>
                                </tr>
                            </tfoot>
                            @endif
                        </table>
                    </div>
                </div>
            </div>

            @if(count($report) > 0)
            <div class="row g-3 mb-3">
                <div class="col-lg-4">
                    <div class="card sub-summary-card h-100">
                        <div class="card-body">
                            <div class="report-section-title text-primary"><i class="bx bx-cube"></i> Item Wise Summary</div>
                            <div class="table-responsive" style="max-height: 320px; overflow-y: auto;">
                                <table class="table table-sm table-bordered mb-0">
                                    <thead class="table-light sticky-top">
                                        <tr>
                                            <th>Item</th>
                                            <th class="text-center">Branches</th>
                                            <th class="text-end">Qty</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($summary['item_wise'] as $row)
                                            <tr>
                                                <td>{{ $row->item_name }}</td>
                                                <td class="text-center">{{ $row->branch_count }}</td>
                                                <td class="text-end fw-bold">{{ $row->total_qty }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="card sub-summary-card h-100">
                        <div class="card-body">
                            <div class="report-section-title text-warning"><i class="bx bx-buildings"></i> Branch Wise Summary</div>
                            <div class="table-responsive" style="max-height: 320px; overflow-y: auto;">
                                <table class="table table-sm table-bordered mb-0">
                                    <thead class="table-light sticky-top">
                                        <tr>
                                            <th>Branch</th>
                                            <th class="text-center">Items</th>
                                            <th class="text-end">Qty</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($summary['branch_wise'] as $row)
                                            <tr>
                                                <td>
                                                    <div class="fw-semibold">{{ $row->branch_name }}</div>
                                                    <div class="small text-muted">{{ $row->division_name }}</div>
                                                </td>
                                                <td class="text-center">{{ $row->item_count }}</td>
                                                <td class="text-end fw-bold">{{ $row->total_qty }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="card sub-summary-card h-100">
                        <div class="card-body">
                            <div class="report-section-title text-purple" style="color:#6f42c1;"><i class="bx bx-sitemap"></i> Division Wise Summary</div>
                            <div class="table-responsive" style="max-height: 320px; overflow-y: auto;">
                                <table class="table table-sm table-bordered mb-0">
                                    <thead class="table-light sticky-top">
                                        <tr>
                                            <th>Division</th>
                                            <th class="text-center">Branches</th>
                                            <th class="text-center">Items</th>
                                            <th class="text-end">Qty</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($summary['division_wise'] as $row)
                                            <tr>
                                                <td>{{ $row->division_name }}</td>
                                                <td class="text-center">{{ $row->branch_count }}</td>
                                                <td class="text-center">{{ $row->item_count }}</td>
                                                <td class="text-end fw-bold">{{ $row->total_qty }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    if (window.jQuery && jQuery.fn && jQuery.fn.select2) {
        jQuery('select.single-select-4').each(function () {
            var $el = jQuery(this);
            if ($el.hasClass('select2-hidden-accessible')) {
                $el.select2('destroy');
            }
            $el.select2({
                theme: 'bootstrap4',
                width: '100%'
            });
        });
    }

    @if(count($report) > 0)
    if (window.jQuery && jQuery.fn && jQuery.fn.DataTable) {
        jQuery('#divisionBranchSummaryTable').DataTable({
            order: [[1, 'asc'], [2, 'asc'], [3, 'asc']],
            pageLength: 25,
            lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]]
        });
    }
    @endif
});
</script>
@endsection
