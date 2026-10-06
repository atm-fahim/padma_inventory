@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="{{ asset('frontend/css/item-inventory.css') }}">
<div class="container-fluid card border-top border-0 border-4 border-success">
    <div class="row justify-content-center">
        <div class="col-12">
            <div class="row mb-3 align-items-center">
                <div class="col-md-7">
                    <h4 class="mb-0 text-success"><i class="bx bx-id-card me-2"></i>Employee Inventory Details Report</h4>
                    <p class="text-muted small mb-0 mt-1">Employee ID wise inventory details with item, branch, serial, stock refs, and status.</p>
                </div>
                <div class="col-md-5 text-end">
                    @php $exportParams = http_build_query($filters ?? []); @endphp
                    <a href="{{ route('inventory-summary') }}" class="btn btn-outline-info btn-sm me-1"><i class="bx bx-buildings me-1"></i>Item/Branch/Division Summary</a>
                    <a href="{{ route('inventory-detail-report') }}" class="btn btn-outline-primary btn-sm me-1"><i class="bx bx-detail me-1"></i>Detail Report</a>
                    <a href="{{ route('inventory-employee-report-export-excel') }}?{{ $exportParams }}" class="btn btn-success btn-sm me-1"><i class="bx bx-file me-1"></i>Export Excel</a>
                    <a href="{{ route('inventory-employee-report-export-pdf') }}?{{ $exportParams }}" class="btn btn-danger btn-sm" target="_blank"><i class="bx bx-file-blank me-1"></i>Export PDF</a>
                </div>
            </div>

            <div class="card mb-3 shadow-sm">
                <div class="card-header bg-light py-2">
                    <h6 class="mb-0"><i class="bx bx-filter-alt me-2"></i>Filters</h6>
                </div>
                <div class="card-body py-3">
                    <form method="GET" action="{{ route('inventory-employee-report') }}" class="row g-2 align-items-end">
                        <div class="col-md-2">
                            <label class="form-label small mb-0">Employee ID</label>
                            <input type="text" name="employee_id" class="form-control form-control-sm" placeholder="Employee ID" value="{{ $filters['employee_id'] ?? '' }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small mb-0">Warehouse</label>
                            <select name="warehouse" class="form-select form-select-sm">
                                <option value="">All</option>
                                <option value="IT" {{ ($filters['warehouse'] ?? '') == 'IT' ? 'selected' : '' }}>IT</option>
                                <option value="IT-OUT" {{ ($filters['warehouse'] ?? '') == 'IT-OUT' ? 'selected' : '' }}>IT-OUT</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small mb-0">Condition</label>
                            <select name="condition" class="form-select form-select-sm">
                                <option value="">All</option>
                                <option value="Good" {{ ($filters['condition'] ?? '') == 'Good' ? 'selected' : '' }}>Good</option>
                                <option value="Slow" {{ ($filters['condition'] ?? '') == 'Slow' ? 'selected' : '' }}>Slow</option>
                                <option value="Bad" {{ ($filters['condition'] ?? '') == 'Bad' ? 'selected' : '' }}>Bad</option>
                                <option value="Damage" {{ ($filters['condition'] ?? '') == 'Damage' ? 'selected' : '' }}>Damage</option>
                                <option value="Unused" {{ ($filters['condition'] ?? '') == 'Unused' ? 'selected' : '' }}>Unused</option>
                                <option value="Normal" {{ ($filters['condition'] ?? '') == 'Normal' ? 'selected' : '' }}>Normal</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small mb-0">Category</label>
                            <select name="category_id" class="form-select form-select-sm">
                                <option value="">All</option>
                                @foreach($categoryData as $cat)
                                    <option value="{{ $cat->id }}" {{ ($filters['category_id'] ?? '') == $cat->id ? 'selected' : '' }}>{{ $cat->category_title }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small mb-0">Item</label>
                            <select name="item_id" class="form-select form-select-sm">
                                <option value="">All</option>
                                @foreach($itemsData as $item)
                                    <option value="{{ $item->id }}" {{ ($filters['item_id'] ?? '') == $item->id ? 'selected' : '' }}>{{ $item->item_title ?? $item->item }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-1">
                            <label class="form-label small mb-0">Date From</label>
                            <input type="date" name="date_from" class="form-control form-control-sm" value="{{ $filters['date_from'] ?? '' }}">
                        </div>
                        <div class="col-md-1">
                            <label class="form-label small mb-0">Date To</label>
                            <input type="date" name="date_to" class="form-control form-control-sm" value="{{ $filters['date_to'] ?? '' }}">
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-success btn-sm w-100"><i class="bx bx-search me-1"></i>Search</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table id="employeeReportTable" class="table table-bordered table-hover table-sm mb-0">
                            <thead class="table-success">
                                <tr>
                                    <th>SL</th>
                                    <th>Item</th>
                                    <th>Employee IDs (Store/From/To)</th>
                                    <th>From Branch</th>
                                    <th>To Branch</th>
                                    <th>Received Branch</th>
                                    <th>Qty</th>
                                    <th>Status</th>
                                    <th>Serial</th>
                                    <th>Set No</th>
                                    <th>Stock In</th>
                                    <th>Stock Out</th>
                                    <th>Warehouse</th>
                                    <th>Condition</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($report as $index => $row)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $row->item_name ?? '—' }}</td>
                                        <td>
                                            S: {{ $row->store_employee_id ?? '—' }}<br>
                                            F: {{ $row->from_employee_id ?? '—' }}<br>
                                            T: {{ $row->to_employee_id ?? '—' }}
                                        </td>
                                        <td>{{ $row->from_branch_name ?? '—' }}</td>
                                        <td>{{ $row->to_branch_name ?? '—' }}</td>
                                        <td>{{ $row->received_branch_name ?? '—' }}</td>
                                        <td>{{ (int)($row->quantity ?? 0) }}</td>
                                        <td>{{ $row->status_label ?? '—' }}</td>
                                        <td>{{ $row->uniq_serial_no ?? '—' }}</td>
                                        <td>{{ $row->set_uniq_no ?? '—' }}</td>
                                        <td>{{ $row->stock_in_no ?? '—' }}</td>
                                        <td>{{ $row->stock_out_no ?? '—' }}</td>
                                        <td>{{ $row->warehouse ?? '—' }}</td>
                                        <td>{{ $row->condition ?? '—' }}</td>
                                        <td>{{ $row->created_date_formatted ?? '—' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="15" class="text-center py-4 text-muted">No records found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    @if(count($report) > 0)
    $('#employeeReportTable').DataTable({
        order: [[0, 'asc']],
        pageLength: 25,
        lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
        scrollX: true
    });
    @endif
});
</script>
@endsection
