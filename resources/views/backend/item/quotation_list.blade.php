@extends('layouts.app')

@section('content')
<div class="container card border-top border-0 border-4 border-primary">
    <div class="row justify-content-center">
        <div class="col-lg-12">
            <div class="row mb-3">
                <div class="col-lg-6 d-flex align-items-center">
                    <h4 class="mb-0">Quotation List</h4>
                </div>
                <div class="col-lg-6 text-end">
                    <a href="{{ route('create-quotation') }}" class="btn btn-primary">Create New Quotation</a>
                </div>
            </div>

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <div class="table-responsive">
                <table class="table table-striped table-bordered" id="quotationsTable">
                    <thead>
                        <tr>
                            <th>SL</th>
                            <th>Quotation No</th>
                            <th>Date</th>
                            <th>Employee</th>
                            <th>Supplier</th>
                            <th>Items</th>
                            <th>Total Amount</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($quotations as $index => $quotation)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td><strong>{{ $quotation->quotation_no }}</strong></td>
                                <td>{{ \Carbon\Carbon::parse($quotation->quotation_date)->format('d-m-Y') }}</td>
                                <td>{{ $quotation->employee_name }}<br><small class="text-muted">{{ $quotation->employee_id }}</small></td>
                                <td>{{ $quotation->supplier->supplier_name ?? 'N/A' }}</td>
                                <td>{{ $quotation->items->count() }} item(s)</td>
                                <td><strong>{{ number_format($quotation->total_amount, 2) }}</strong></td>
                                <td>
                                    @if($quotation->status == 'draft')
                                        <span class="badge bg-secondary">Draft</span>
                                    @elseif($quotation->status == 'pending')
                                        <span class="badge bg-warning">Pending</span>
                                    @elseif($quotation->status == 'admin_approved')
                                        <span class="badge bg-info">Admin Approved</span>
                                    @elseif($quotation->status == 'division_head_approved')
                                        <span class="badge bg-primary">Division Head Approved</span>
                                    @elseif($quotation->status == 'md_approved')
                                        <span class="badge bg-success">MD Approved</span>
                                    @elseif($quotation->status == 'board_approved')
                                        <span class="badge bg-success">Board Approved</span>
                                    @elseif($quotation->status == 'rejected')
                                        <span class="badge bg-danger">Rejected</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('quotation-show', $quotation->id) }}" class="btn btn-sm btn-info">
                                        View
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        $('#quotationsTable').DataTable({
            order: [[0, 'desc']],
            pageLength: 25
        });
    });
</script>
@endsection
