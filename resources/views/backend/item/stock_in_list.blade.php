@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="{{ asset('frontend/css/item-inventory.css') }}">
<div class="container card border-top border-0 border-4 border-success">
    <div class="row justify-content-center">
        <div class="col-lg-12">
            <div class="row mb-3">
                <div class="col-lg-6 d-flex align-items-center">
                    <h4 class="mb-0"><i class="bx bx-down-arrow-circle me-2"></i>Stock In List</h4>
                </div>
                <div class="col-lg-6 text-end">
                    <a href="{{ route('inventory-report') }}" class="btn btn-outline-secondary me-1">
                        <i class="bx bx-pie-chart-alt me-1"></i>Inventory Report
                    </a>
                    <a href="{{ route('stock-in') }}" class="btn btn-outline-success">
                        <i class="bx bx-plus me-1"></i>New Stock In
                    </a>
                </div>
            </div>

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="stockInTable" class="table table-bordered table-striped table-hover">
                            <thead class="table-success">
                                <tr>
                                    <th>SL</th>
                                    <th>Stock In No</th>
                                    <th>Supplier</th>
                                    <th>To Branch</th>
                                    <th>Item Count</th>
                                    <th>Total Quantity</th>
                                    <th>Total Amount</th>
                                    <th>Date</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($stockInList as $index => $stockIn)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>
                                            <strong>{{ $stockIn->STOCK_IN_NO }}</strong>
                                        </td>
                                        <td>{{ $stockIn->supplier_name }}</td>
                                        <td>{{ $stockIn->to_branch_name }}</td>
                                        <td>
                                            <span class="badge bg-info">{{ $stockIn->item_count }}</span>
                                        </td>
                                        <td>
                                            <span class="badge bg-primary">{{ $stockIn->total_quantity }}</span>
                                        </td>
                                        <td>
                                            <strong>৳{{ number_format($stockIn->total_amount ?? 0, 2) }}</strong>
                                        </td>
                                        <td>{{ date('Y-m-d H:i', strtotime($stockIn->created_date)) }}</td>
                                        <td>
                                            <a href="{{ route('stock-in-invoice', ['stockInNo' => $stockIn->STOCK_IN_NO]) }}" 
                                               class="btn btn-sm btn-success" 
                                               title="View Invoice">
                                                <i class="bx bx-printer me-1"></i>View Invoice
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center">
                                            <div class="py-4">
                                                <i class="bx bx-info-circle fs-1 text-muted"></i>
                                                <p class="text-muted mt-2">No stock in records found</p>
                                            </div>
                                        </td>
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
        // Initialize DataTable if records exist
        @if(count($stockInList) > 0)
            $('#stockInTable').DataTable({
                "order": [[7, "desc"]], // Sort by date descending
                "pageLength": 25,
                "lengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
                "language": {
                    "search": "Search:",
                    "lengthMenu": "Show _MENU_ entries",
                    "info": "Showing _START_ to _END_ of _TOTAL_ entries",
                    "infoEmpty": "Showing 0 to 0 of 0 entries",
                    "infoFiltered": "(filtered from _MAX_ total entries)",
                    "paginate": {
                        "first": "First",
                        "last": "Last",
                        "next": "Next",
                        "previous": "Previous"
                    }
                }
            });
        @endif
    });
</script>
@endsection
