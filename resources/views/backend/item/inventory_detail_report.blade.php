@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="{{ asset('frontend/css/item-inventory.css') }}">
@php
    $exportParams = http_build_query(array_filter($filters ?? [], fn ($v) => $v !== null && $v !== ''));
    $totalRows = count($report);
    $inStock = collect($report)->filter(fn ($r) => (int)($r->status ?? 0) === 1)->count();
    $stockOut = collect($report)->filter(fn ($r) => (int)($r->status ?? 0) === 2)->count();
    $received = collect($report)->filter(fn ($r) => (int)($r->status ?? 0) === 3)->count();
    $statusClass = function ($status) {
        return match ((int) $status) {
            1 => 'badge-in',
            2 => 'badge-out',
            3 => 'badge-recv',
            4 => 'badge-xfer',
            default => 'badge-muted',
        };
    };
@endphp

<div class="container-fluid detail-report-page">
    <div class="detail-hero mb-3">
        <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
            <div>
                <h4 class="mb-1"><i class="bx bx-detail me-2"></i>Inventory Detail Report</h4>
                <p class="mb-0 small opacity-90">Branch, Item, Category দিয়ে filter করুন — Excel / PDF export করুন</p>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <a href="{{ route('inventory-report') }}" class="btn btn-hero-light btn-sm"><i class="bx bx-pie-chart-alt me-1"></i>Summary</a>
                <a href="{{ route('inventory-summary') }}" class="btn btn-hero-light btn-sm"><i class="bx bx-buildings me-1"></i>Item/Branch/Division</a>
                <a href="{{ route('inventory-detail-export-excel') }}?{{ $exportParams }}" class="btn btn-hero-solid btn-sm"><i class="bx bx-file me-1"></i>Excel</a>
                <a href="{{ route('inventory-detail-export-pdf') }}?{{ $exportParams }}" class="btn btn-hero-solid btn-sm" target="_blank"><i class="bx bx-file-blank me-1"></i>PDF</a>
            </div>
        </div>
    </div>

    @if(session('detail_report_error'))
        <div class="alert alert-danger py-2 small mb-3">
            <strong>Report error:</strong> {{ session('detail_report_error') }}
        </div>
    @endif

    <div class="row g-3 mb-3">
        <div class="col-6 col-md-3">
            <div class="kpi-card kpi-total">
                <div class="kpi-label">Total Records</div>
                <div class="kpi-value">{{ number_format($totalRows) }}</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="kpi-card kpi-in">
                <div class="kpi-label">In Stock</div>
                <div class="kpi-value">{{ number_format($inStock) }}</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="kpi-card kpi-out">
                <div class="kpi-label">Stock Out</div>
                <div class="kpi-value">{{ number_format($stockOut) }}</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="kpi-card kpi-recv">
                <div class="kpi-label">Received</div>
                <div class="kpi-value">{{ number_format($received) }}</div>
            </div>
        </div>
    </div>

    <div class="detail-card mb-3">
        <div class="detail-card-head">
            <i class="bx bx-filter-alt me-1"></i> Filters
        </div>
        <div class="detail-card-body">
            <form method="GET" action="{{ route('inventory-detail-report') }}" id="detailFilterForm" class="row g-3 align-items-end">
                <div class="col-md-3 col-lg-2">
                    <label class="form-label">Branch</label>
                    <select name="branch_id" class="form-select form-select-sm detail-filter-select">
                        <option value="">All Branches</option>
                        @foreach(($branchData ?? []) as $branch)
                            <option value="{{ $branch->hr_branch_id }}" {{ (string)($filters['branch_id'] ?? '') === (string)$branch->hr_branch_id ? 'selected' : '' }}>
                                {{ $branch->branch_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 col-lg-2">
                    <label class="form-label">Category</label>
                    <select name="category_id" class="form-select form-select-sm detail-filter-select">
                        <option value="">All Categories</option>
                        @foreach($categoryData as $cat)
                            <option value="{{ $cat->id }}" {{ (string)($filters['category_id'] ?? '') === (string)$cat->id ? 'selected' : '' }}>{{ $cat->category_title }}</option>
                            @if(!empty($cat->children) && count($cat->children))
                                @foreach($cat->children as $child)
                                    <option value="{{ $child->id }}" {{ (string)($filters['category_id'] ?? '') === (string)$child->id ? 'selected' : '' }}>— {{ $child->category_title }}</option>
                                @endforeach
                            @endif
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 col-lg-2">
                    <label class="form-label">Item</label>
                    <select name="item_id" class="form-select form-select-sm detail-filter-select">
                        <option value="">All Items</option>
                        @foreach($itemsData as $item)
                            <option value="{{ $item->id }}" {{ (string)($filters['item_id'] ?? '') === (string)$item->id ? 'selected' : '' }}>{{ $item->item_title ?? $item->item }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 col-lg-2">
                    <label class="form-label">Warehouse</label>
                    <select name="warehouse" class="form-select form-select-sm">
                        <option value="">All</option>
                        <option value="IT" {{ ($filters['warehouse'] ?? '') == 'IT' ? 'selected' : '' }}>IT</option>
                        <option value="IT-OUT" {{ ($filters['warehouse'] ?? '') == 'IT-OUT' ? 'selected' : '' }}>IT-OUT</option>
                    </select>
                </div>
                <div class="col-md-3 col-lg-2">
                    <label class="form-label">Condition</label>
                    <select name="condition" class="form-select form-select-sm">
                        <option value="">All</option>
                        @foreach(['Good','Slow','Bad','Damage','Unused','Normal'] as $cond)
                            <option value="{{ $cond }}" {{ strcasecmp((string)($filters['condition'] ?? ''), $cond) === 0 ? 'selected' : '' }}>{{ $cond }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 col-lg-2">
                    <label class="form-label">Created By</label>
                    <input type="text" name="user_id" class="form-control form-control-sm" placeholder="User ID" value="{{ $filters['user_id'] ?? '' }}">
                </div>
                <div class="col-md-3 col-lg-2">
                    <label class="form-label">To Employee ID</label>
                    <input type="text" name="to_employee_id" class="form-control form-control-sm" placeholder="Employee ID" value="{{ $filters['to_employee_id'] ?? '' }}">
                </div>
                <div class="col-md-3 col-lg-2">
                    <label class="form-label">Date From</label>
                    <input type="date" name="date_from" class="form-control form-control-sm" value="{{ $filters['date_from'] ?? '' }}">
                </div>
                <div class="col-md-3 col-lg-2">
                    <label class="form-label">Date To</label>
                    <input type="date" name="date_to" class="form-control form-control-sm" value="{{ $filters['date_to'] ?? '' }}">
                </div>
                <div class="col-md-3 col-lg-2 d-flex gap-2">
                    <button type="submit" class="btn btn-theme btn-sm flex-fill"><i class="bx bx-search me-1"></i>Search</button>
                    <a href="{{ route('inventory-detail-report') }}" class="btn btn-outline-secondary btn-sm">Clear</a>
                </div>
            </form>
        </div>
    </div>

    <div class="detail-card">
        <div class="detail-card-head head-table">
            <span><i class="bx bx-table me-1"></i> Detail Records</span>
            <span class="head-count">{{ number_format($totalRows) }} rows</span>
        </div>
        <div class="detail-card-body p-0">
            <div class="table-responsive detail-table-wrap">
                <table id="detailReportTable" class="table table-hover table-sm mb-0 detail-table">
                    <thead>
                        <tr>
                            <th>SL</th>
                            <th>Item Name</th>
                            <th>Category</th>
                            <th>Branch</th>
                            <th>Qty</th>
                            <th>Status</th>
                            <th>UNIQ_SERIAL_NO</th>
                            <th>FROM_DEVICE_SERIAL_NO</th>
                            <th>TO_DEVICE_SERIAL_NO</th>
                            <th>RAM</th>
                            <th>STORAGE</th>
                            <th>PROCESSOR</th>
                            <th>SET_UNIQ_NO</th>
                            <th>SERVICE_WARRANTY</th>
                            <th>WARRANTY</th>
                            <th>Supplier</th>
                            <th>Store Emp ID</th>
                            <th>From Emp ID</th>
                            <th>To Emp ID</th>
                            <th>From Branch</th>
                            <th>To Branch</th>
                            <th>Brand</th>
                            <th>Condition</th>
                            <th>RECEIVE_REMARKS</th>
                            <th>Received Branch</th>
                            <th>Warehouse</th>
                            <th>Remarks</th>
                            <th>Stock In No</th>
                            <th>Stock Out No</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($report as $index => $row)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td class="fw-semibold">{{ $row->item_name ?? '—' }}</td>
                                <td>{{ $row->category_name ?? '—' }}</td>
                                <td class="fw-semibold">{{ $row->store_branch_name ?? '—' }}</td>
                                <td class="text-center">{{ $row->quantity ?? 0 }}</td>
                                <td><span class="status-badge {{ $statusClass($row->status ?? 0) }}">{{ $row->status_label ?? '—' }}</span></td>
                                <td>{{ $row->uniq_serial_no ?? '—' }}</td>
                                <td>{{ $row->from_device_serial_no ?? '—' }}</td>
                                <td>{{ $row->to_device_serial_no ?? '—' }}</td>
                                <td>{{ $row->ram ?? '—' }}</td>
                                <td>{{ $row->storage ?? '—' }}</td>
                                <td>{{ $row->processor ?? '—' }}</td>
                                <td><span class="set-chip">{{ $row->set_uniq_no ?? '—' }}</span></td>
                                <td>{{ $row->service_warranty ?? '—' }}</td>
                                <td>{{ $row->warranty ?? '—' }}</td>
                                <td>{{ $row->supplier_name ?? '—' }}</td>
                                <td>{{ $row->store_employee_id ?? '—' }}</td>
                                <td>{{ $row->from_employee_id ?? '—' }}</td>
                                <td>{{ $row->to_employee_id ?? '—' }}</td>
                                <td>{{ $row->from_branch_name ?? '—' }}</td>
                                <td>{{ $row->to_branch_name ?? '—' }}</td>
                                <td>{{ $row->brand_name ?? '—' }}</td>
                                <td>{{ $row->condition ?? '—' }}</td>
                                <td>{{ $row->receive_remarks ?? '—' }}</td>
                                <td class="fw-semibold text-recv">{{ $row->received_branch_name ?? '—' }}</td>
                                <td>{{ $row->warehouse ?? '—' }}</td>
                                <td>{{ $row->remarks ?? '—' }}</td>
                                <td>{{ $row->stock_in_no ?? '—' }}</td>
                                <td>{{ $row->stock_out_no ?? '—' }}</td>
                                <td>{{ $row->created_date_formatted ?? '—' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="30" class="text-center py-5 text-muted">
                                    <div class="mb-1"><i class="bx bx-search-alt-2" style="font-size:1.6rem;"></i></div>
                                    No records found. Filters adjust করে আবার Search করুন।
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<style>
.detail-report-page {
    --dr-theme: #df238e;
    --dr-theme-dark: #c01b78;
    --dr-soft: rgba(223, 35, 142, 0.12);
    --dr-border: #f0d0e3;
    padding-bottom: 1.5rem;
}
.detail-hero {
    background: linear-gradient(135deg, #df238e 0%, #c01b78 55%, #9b1560 100%);
    color: #fff;
    border-radius: 14px;
    padding: 1.15rem 1.35rem;
    box-shadow: 0 10px 24px rgba(223, 35, 142, 0.22);
}
.detail-hero h4 { font-weight: 700; margin: 0; }
.btn-hero-light {
    background: rgba(255,255,255,0.16);
    border: 1px solid rgba(255,255,255,0.35);
    color: #fff;
    border-radius: 8px;
}
.btn-hero-light:hover { background: rgba(255,255,255,0.28); color: #fff; }
.btn-hero-solid {
    background: #fff;
    border: 1px solid #fff;
    color: #c01b78;
    border-radius: 8px;
    font-weight: 600;
}
.btn-hero-solid:hover { background: #fff5fa; color: #9b1560; }

.kpi-card {
    background: #fff;
    border: 1px solid var(--dr-border);
    border-radius: 12px;
    padding: 0.85rem 1rem;
    box-shadow: 0 4px 14px rgba(15, 23, 42, 0.04);
    height: 100%;
}
.kpi-label { font-size: 0.75rem; color: #64748b; font-weight: 600; text-transform: uppercase; letter-spacing: 0.03em; }
.kpi-value { font-size: 1.45rem; font-weight: 700; color: #0f172a; margin-top: 0.15rem; }
.kpi-total { border-top: 3px solid #64748b; }
.kpi-in { border-top: 3px solid #16a34a; }
.kpi-out { border-top: 3px solid #df238e; }
.kpi-recv { border-top: 3px solid #0ea5e9; }

.detail-card {
    background: #fff;
    border: 1px solid var(--dr-border);
    border-radius: 14px;
    overflow: hidden;
    box-shadow: 0 6px 18px rgba(15, 23, 42, 0.05);
}
.detail-card-head {
    padding: 0.75rem 1rem;
    font-weight: 700;
    font-size: 0.95rem;
    color: #fff;
    background: linear-gradient(135deg, #64748b, #475569);
    display: flex;
    align-items: center;
    justify-content: space-between;
}
.detail-card-head.head-table {
    background: linear-gradient(135deg, #df238e, #c01b78);
}
.head-count {
    background: rgba(255,255,255,0.2);
    border-radius: 999px;
    font-size: 0.72rem;
    padding: 0.2rem 0.6rem;
}
.detail-card-body { padding: 1rem 1.1rem; }
.detail-card-body .form-label {
    font-size: 0.78rem;
    font-weight: 600;
    color: #475569;
    margin-bottom: 0.25rem;
}
.detail-card-body .form-control,
.detail-card-body .form-select {
    border-radius: 8px;
    border-color: #e2b7d1;
}
.detail-card-body .form-control:focus,
.detail-card-body .form-select:focus {
    border-color: var(--dr-theme);
    box-shadow: 0 0 0 0.2rem var(--dr-soft);
}
.btn-theme {
    background: var(--dr-theme);
    border-color: var(--dr-theme);
    color: #fff;
    border-radius: 8px;
    font-weight: 600;
}
.btn-theme:hover { background: var(--dr-theme-dark); border-color: var(--dr-theme-dark); color: #fff; }

.detail-table-wrap {
    max-height: 68vh;
    overflow: auto;
}
.detail-table {
    font-size: 0.8rem;
    margin-bottom: 0;
}
.detail-table thead th {
    background: #fff5fa;
    color: #9b1560;
    font-weight: 700;
    white-space: nowrap;
    border-color: var(--dr-border);
    position: sticky;
    top: 0;
    z-index: 2;
    padding: 0.55rem 0.65rem;
}
.detail-table td {
    white-space: nowrap;
    vertical-align: middle;
    border-color: #f3dceb;
    padding: 0.45rem 0.65rem;
}
.detail-table tbody tr:nth-child(even) { background: #fafbfd; }
.detail-table tbody tr:hover { background: #fff0f7; }
.set-chip {
    display: inline-block;
    background: #fff5fa;
    border: 1px solid var(--dr-border);
    color: #9b1560;
    border-radius: 6px;
    padding: 0.1rem 0.4rem;
    font-size: 0.75rem;
    font-weight: 600;
}
.text-recv { color: #0369a1; }
.status-badge {
    display: inline-block;
    border-radius: 999px;
    padding: 0.2rem 0.55rem;
    font-size: 0.7rem;
    font-weight: 700;
}
.badge-in { background: #dcfce7; color: #166534; }
.badge-out { background: #fce7f3; color: #9b1560; }
.badge-recv { background: #e0f2fe; color: #0369a1; }
.badge-xfer { background: #fef3c7; color: #92400e; }
.badge-muted { background: #e2e8f0; color: #475569; }

#detailFilterForm .detail-filter-select + .select2-container {
    width: 100% !important;
    max-width: 100%;
}
#detailFilterForm .select2-container .select2-selection--single {
    height: 31px !important;
    min-height: 31px !important;
    border: 1px solid #e2b7d1 !important;
    border-radius: 8px !important;
    display: flex;
    align-items: center;
}
#detailFilterForm .select2-container--bootstrap4 .select2-selection--single .select2-selection__rendered {
    line-height: 29px !important;
    padding-left: 0.5rem;
    padding-right: 1.75rem;
    font-size: 0.875rem;
}
#detailFilterForm .select2-container--bootstrap4 .select2-selection--single .select2-selection__arrow {
    height: 29px !important;
    top: 1px;
    right: 3px;
}
#detailFilterForm .select2-container--bootstrap4.select2-container--focus .select2-selection--single,
#detailFilterForm .select2-container--bootstrap4.select2-container--open .select2-selection--single {
    border-color: var(--dr-theme) !important;
    box-shadow: 0 0 0 0.15rem var(--dr-soft) !important;
}
#detailFilterForm > [class*="col-"] { min-width: 0; }

.dataTables_wrapper .dataTables_filter input,
.dataTables_wrapper .dataTables_length select {
    border-radius: 8px;
    border-color: #e2b7d1;
}
.dataTables_wrapper .dataTables_paginate .paginate_button.current {
    background: var(--dr-theme) !important;
    border-color: var(--dr-theme) !important;
    color: #fff !important;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    if (window.jQuery && jQuery.fn && jQuery.fn.select2) {
        jQuery('#detailFilterForm select.detail-filter-select').each(function () {
            var $el = jQuery(this);
            if ($el.hasClass('select2-hidden-accessible')) {
                $el.select2('destroy');
            }
            $el.select2({
                theme: 'bootstrap4',
                width: '100%',
                allowClear: true,
                placeholder: $el.find('option[value=""]').first().text() || 'All',
                dropdownParent: jQuery('#detailFilterForm')
            });
        });
    }

    @if($totalRows > 0)
    if (window.jQuery && jQuery.fn && jQuery.fn.DataTable) {
        jQuery('#detailReportTable').DataTable({
            order: [[0, 'asc']],
            pageLength: 25,
            lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
            scrollX: true,
            language: {
                search: "Search:",
                lengthMenu: "Show _MENU_",
                info: "Showing _START_ to _END_ of _TOTAL_",
                paginate: { first: "First", last: "Last", next: "Next", previous: "Prev" }
            }
        });
    }
    @endif
});
</script>
@endsection
