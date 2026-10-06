@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="{{ asset('frontend/css/item-inventory.css') }}">
<div class="container card border-top border-0 border-4 border-info">
    <div class="row justify-content-center">
        <div class="col-lg-12">
            <div class="row mb-3">
                <div class="col-lg-6 d-flex align-items-center">
                    <h4 class="mb-0"><i class="bx bx-pie-chart-alt me-2"></i>Inventory Report (Stock In / Stock Out / Total)</h4>
                </div>
                <div class="col-lg-6 text-end">
                    <a href="{{ route('stock-in') }}" class="btn btn-outline-success btn-sm me-1"><i class="bx bx-down-arrow-circle me-1"></i>Stock In</a>
                    <a href="{{ route('stock-out') }}" class="btn btn-outline-primary btn-sm me-1"><i class="bx bx-up-arrow-circle me-1"></i>Stock Out</a>
                    <a href="{{ route('stock-in-list') }}" class="btn btn-outline-info btn-sm me-1"><i class="bx bx-list-ul me-1"></i>Stock In List</a>
                    <a href="{{ route('inventory-division-branch-summary') }}" class="btn btn-outline-dark btn-sm me-1"><i class="bx bx-buildings me-1"></i>Item/Branch/Division Summary</a>
                    <a href="{{ route('inventory-detail-report') }}" class="btn btn-outline-primary btn-sm"><i class="bx bx-detail me-1"></i>Detail Report</a>
                </div>
            </div>

            {{-- Search / Filter --}}
            <div class="card mb-3">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="bx bx-search me-2"></i>Search Inventory Report</h5>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('inventory-report') }}" class="row g-3">
                        <!-- <div class="col-md-2">
                            <label class="form-label">Warehouse</label>
                            <select name="warehouse" class="form-select form-select-sm">
                                <option value="">All</option>
                                <option value="IT" {{ ($filters['warehouse'] ?? '') == 'IT' ? 'selected' : '' }}>IT</option>
                                <option value="IT-OUT" {{ ($filters['warehouse'] ?? '') == 'IT-OUT' ? 'selected' : '' }}>IT-OUT</option>
                            </select>
                        </div> -->
                        <div class="col-md-2">
                            <label class="form-label">Condition</label>
                            <select name="condition" class="form-select form-select-sm">
                                <option value="">All</option>
                                <option value="good" {{ ($filters['condition'] ?? '') == 'good' ? 'selected' : '' }}>Good</option>
                                <option value="slow" {{ ($filters['condition'] ?? '') == 'slow' ? 'selected' : '' }}>Slow</option>
                                <option value="bad" {{ ($filters['condition'] ?? '') == 'bad' ? 'selected' : '' }}>Bad</option>
                                <option value="damage" {{ ($filters['condition'] ?? '') == 'damage' ? 'selected' : '' }}>Damage</option>
                                <option value="unused" {{ ($filters['condition'] ?? '') == 'unused' ? 'selected' : '' }}>Unused</option>
                                <option value="normal" {{ ($filters['condition'] ?? '') == 'normal' ? 'selected' : '' }}>Normal</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Category</label>
                            <select name="category_id" class="form-select form-select-sm">
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
                            <select name="item_id" class="form-select form-select-sm">
                                <option value="">All</option>
                                @foreach($itemsData as $item)
                                    <option value="{{ $item->id }}" {{ ($filters['item_id'] ?? '') == $item->id ? 'selected' : '' }}>{{ $item->item_title ?? $item->item }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Date From</label>
                            <input type="date" name="date_from" class="form-control form-control-sm" value="{{ $filters['date_from'] ?? '' }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Date To</label>
                            <input type="date" name="date_to" class="form-control form-control-sm" value="{{ $filters['date_to'] ?? '' }}">
                        </div>
                        <div class="col-md-12">
                            <button type="submit" class="btn btn-info btn-sm"><i class="bx bx-search me-1"></i>Search</button>
                            <a href="{{ route('inventory-report') }}" class="btn btn-outline-secondary btn-sm">Clear</a>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Report Table --}}
            <div class="card">
                <div class="card-body">
                    <p class="text-muted small mb-2"><i class="bx bx-info-circle me-1"></i>Row এ ক্লিক করলে বিস্তারিত দেখাবে (প্রতি SET_UNIQ_NO / প্যাকেজ অনুযায়ী)।</p>
                    <div class="table-responsive">
                        <table id="inventoryReportTable" class="table table-bordered table-striped table-hover">
                            <thead class="table-info">
                                <tr>
                                    <th>SL</th>
                                    <th>SET_UNIQ_NO</th>
                                    <th>Item</th>
                                    <th>Category</th>
                                    <th class="text-end">Stock In</th>
                                    <th class="text-end">Stock Out (−)</th>
                                    <th class="text-end">Total Qty</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($report as $index => $row)
                                    <tr class="report-row cursor-pointer"
                                        data-group-type="item"
                                        data-group-id="{{ $row->item_id ?? $row->group_id ?? '' }}"
                                        title="Details দেখতে ক্লিক করুন">
                                        <td>{{ $index + 1 }}</td>
                                        <td><strong>{{ $row->set_uniq_no ?? '—' }}</strong></td>
                                        <td>{{ $row->item_title ?? '—' }}</td>
                                        <td>{{ $row->category_title ?? '—' }}</td>
                                        <td class="text-end text-success fw-bold">{{ $row->stock_in_qty }}</td>
                                        <td class="text-end text-danger fw-bold">{{ $row->stock_out_qty }}</td>
                                        <td class="text-end fw-bold {{ $row->total_qty >= 0 ? 'text-primary' : 'text-danger' }}">{{ $row->total_qty }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-4">
                                            <i class="bx bx-info-circle fs-1 text-muted"></i>
                                            <p class="text-muted mt-2 mb-0">No data found. Adjust filters or add stock in/out.</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                            @if(count($report) > 0)
                            <tfoot class="table-light">
                                <tr>
                                    <th colspan="4" class="text-end">Total</th>
                                    <th class="text-end text-success">{{ $report->sum('stock_in_qty') }}</th>
                                    <th class="text-end text-danger">{{ $report->sum('stock_out_qty') }}</th>
                                    <th class="text-end fw-bold">{{ $report->sum('total_qty') }}</th>
                                </tr>
                            </tfoot>
                            @endif
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Details Modal --}}
<div class="modal fade inventory-details-modal" id="detailsModal" tabindex="-1" aria-labelledby="detailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="detailsModalLabel"><i class="bx bx-detail me-2"></i>Inventory Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="detailsLoading" class="text-center py-4" style="display: none;">
                    <div class="spinner-border text-primary" role="status"></div>
                    <p class="mt-2 mb-0">Loading...</p>
                </div>
                <div id="detailsContent" style="display: none;"></div>
            </div>
        </div>
    </div>
</div>

<style>
.cursor-pointer { cursor: pointer; }
.report-row:hover { background-color: rgba(0,123,255,0.08); }
.inventory-details-modal .modal-content {
    border: 0;
    border-radius: 14px;
    overflow: hidden;
    box-shadow: 0 12px 30px rgba(15, 23, 42, 0.18);
}
.inventory-details-modal .modal-header {
    background: linear-gradient(135deg, #0d6efd 0%, #0aa2c0 100%);
    color: #fff;
    border-bottom: 0;
    padding: 12px 16px;
}
.inventory-details-modal .btn-close {
    filter: invert(1);
    opacity: 0.95;
}
.inventory-details-modal .modal-body {
    background: #f8fafc;
    padding: 14px;
}
.details-table-wrap {
    border: 1px solid #dbe7f3;
    border-radius: 10px;
    overflow: hidden;
    background: #fff;
}
.details-table-wrap .table {
    margin-bottom: 0;
    font-size: 13px;
}
.details-table-wrap thead th {
    background: #eef6ff;
    color: #0f172a;
    border-bottom: 1px solid #dbe7f3;
    white-space: nowrap;
}
.details-table-wrap tbody td {
    vertical-align: middle;
}
.details-table-wrap tbody tr:nth-child(even) {
    background: #fcfdff;
}
.details-badge {
    font-size: 11px;
    padding: 4px 8px;
    border-radius: 999px;
}
.details-empty,
.details-error {
    background: #fff;
    border: 1px solid #dbe7f3;
    border-radius: 10px;
    padding: 14px;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    @if(count($report) > 0)
    var detailsUrl = '{{ route("inventory-report-details") }}';
    var tableEl = document.getElementById('inventoryReportTable');
    var loadingEl = document.getElementById('detailsLoading');
    var contentEl = document.getElementById('detailsContent');
    var titleEl = document.getElementById('detailsModalLabel');
    var modalEl = document.getElementById('detailsModal');

    if (window.jQuery && jQuery.fn && jQuery.fn.DataTable) {
        jQuery('#inventoryReportTable').DataTable({
            order: [[1, 'asc']],
            pageLength: 25,
            lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
            language: {
                search: "Search:",
                lengthMenu: "Show _MENU_ entries",
                info: "Showing _START_ to _END_ of _TOTAL_ entries",
                infoEmpty: "Showing 0 to 0 of 0 entries",
                infoFiltered: "(filtered from _MAX_ total entries)",
                paginate: { first: "First", last: "Last", next: "Next", previous: "Previous" }
            }
        });
    }

    if (!tableEl) return;

    tableEl.addEventListener('click', function (event) {
        var rowEl = event.target.closest('tr.report-row');
        if (!rowEl) return;

        var type = rowEl.getAttribute('data-group-type') || 'item';
        var id = rowEl.getAttribute('data-group-id') || '';
        if (!id) return;

        var modal = new bootstrap.Modal(modalEl);
        titleEl.innerHTML = '<i class="bx bx-detail me-2"></i>Details: ' + (type === 'set' ? 'Set ' + id : 'Item #' + id);
        loadingEl.style.display = 'block';
        contentEl.style.display = 'none';
        contentEl.innerHTML = '';
        modal.show();

        fetch(detailsUrl + '?type=' + encodeURIComponent(type) + '&id=' + encodeURIComponent(id), {
            method: 'GET',
            headers: { 'Accept': 'application/json' }
        })
            .then(function (response) { return response.json(); })
            .then(function (res) {
                loadingEl.style.display = 'none';
                if (res.success && Array.isArray(res.data) && res.data.length > 0) {
                    var html = '<div class="details-table-wrap"><table class="table table-bordered table-sm"><thead><tr><th>SL</th><th>Type</th><th>Item</th><th>Category</th><th>Brand</th><th>Storage</th><th>Serial No</th><th>Set/Ref</th><th>Qty</th><th>Condition</th><th>Remarks</th><th>Ref No</th><th>Date</th></tr></thead><tbody>';
                    res.data.forEach(function (r, i) {
                        var badge = (r.type_label === 'Stock Out') ? 'bg-danger' : 'bg-success';
                        var storageText = r.storage || r.ram || r.processor || '—';
                        html += '<tr><td>' + (i + 1) + '</td><td><span class="badge details-badge ' + badge + '">' + (r.type_label || '—') + '</span></td><td>' + (r.item_title || '—') + '</td><td>' + (r.category_title || '—') + '</td><td>' + (r.brand_name || '—') + '</td><td>' + storageText + '</td><td>' + (r.uniq_serial_no || '—') + '</td><td>' + (r.set_uniq_no || '—') + '</td><td>' + (r.quantity || 0) + '</td><td>' + (r.condition || '—') + '</td><td>' + (r.remarks || '—') + '</td><td>' + (r.stock_in_no || r.stock_out_no || '—') + '</td><td>' + (r.created_date_formatted || '—') + '</td></tr>';
                    });
                    html += '</tbody></table></div>';
                    contentEl.innerHTML = html;
                } else {
                    contentEl.innerHTML = '<div class="details-empty text-muted mb-0"><i class="bx bx-info-circle me-1"></i>No detail records found.</div>';
                }
                contentEl.style.display = 'block';
            })
            .catch(function () {
                loadingEl.style.display = 'none';
                contentEl.innerHTML = '<div class="details-error text-danger mb-0"><i class="bx bx-error-circle me-1"></i>Failed to load details.</div>';
                contentEl.style.display = 'block';
            });
    });
    @endif
});
</script>
@endsection
