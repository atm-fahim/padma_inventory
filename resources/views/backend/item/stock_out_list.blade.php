@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="{{ asset('frontend/css/item-inventory.css') }}">
<div class="container card border-top border-0 border-4 border-primary">
    <div class="row justify-content-center">
        <div class="col-lg-12">
            <div class="row mb-3">
                <div class="col-lg-6 d-flex align-items-center">
                    <h4 class="mb-0">Stock Out List</h4>
                </div>
                <div class="col-lg-6 text-end">
                    <a href="{{ route('receive-stock') }}" class="btn btn-outline-warning me-2">
                        <i class="bx bx-package me-1"></i>Receive Stock
                    </a>
                    <a href="{{ route('stock-out') }}" class="btn btn-outline-primary">
                        <i class="bx bx-plus me-1"></i>New Stock Out
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
                        <table id="stockOutTable" class="table table-bordered table-striped table-hover">
                            <thead class="table-primary">
                                <tr>
                                    <th>SL</th>
                                    <th>Stock Out No</th>
                                    <th>From Branch</th>
                                    <th>To Branch</th>
                                    <th>Item Count</th>
                                    <th>Date</th>
                                    <th class="text-center" title="Gate Pass page views">GP Views</th>
                                    <th class="text-center" title="Gate Pass prints (browser afterprint)">GP Prints</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($stockOutList as $index => $stockOut)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>
                                            <strong>{{ $stockOut->stock_out_no }}</strong>
                                        </td>
                                        <td>{{ $stockOut->from_branch_name }}</td>
                                        <td>{{ $stockOut->to_branch_name }}</td>
                                        <td>
                                            <span class="badge bg-info">{{ $stockOut->item_count }}</span>
                                        </td>
                                        <td>{{ date('Y-m-d H:i', strtotime($stockOut->created_date)) }}</td>
                                        <td class="text-center">
                                            <span class="badge bg-secondary">{{ (int) ($stockOut->gate_pass_view_count ?? 0) }}</span>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-dark">{{ (int) ($stockOut->gate_pass_print_count ?? 0) }}</span>
                                        </td>
                                        <td>
                                            <a href="{{ route('stock-out-invoice', ['stockOutNo' => $stockOut->stock_out_no]) }}" 
                                               class="btn btn-sm btn-success" 
                                               title="Create Gate Pass">
                                                <i class="bx bx-printer me-1"></i>Gate Pass
                                            </a>
                                            <a href="{{ route('receive-stock', ['stock_out_no' => $stockOut->stock_out_no]) }}" 
                                               class="btn btn-sm btn-warning" 
                                               title="Receive Stock">
                                                <i class="bx bx-package me-1"></i>Receive
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center">
                                            <div class="py-4">
                                                <i class="bx bx-info-circle fs-1 text-muted"></i>
                                                <p class="text-muted mt-2">No stock out records found</p>
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
    // Wait for jQuery to be available before initializing
    (function() {
        function initStockOutList() {
            // Ensure jQuery is available
            if (typeof jQuery === 'undefined') {
                setTimeout(initStockOutList, 50);
                return;
            }
            var $ = jQuery;
            
            $(document).ready(function() {
                // Initialize DataTable if records exist
                @if(count($stockOutList) > 0)
                    $('#stockOutTable').DataTable({
                        "order": [[5, "desc"]], // Sort by date column (index 5) descending
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
        }
        
        // Start initialization
        initStockOutList();
    })();
</script>
@endsection
