@extends('layouts.app')

@section('content')
<div class="container card border-top border-0 border-4 border-primary">
    <div class="row justify-content-center">
        <div class="col-lg-12 p-4">
            <div class="d-flex justify-content-between mb-3">
                <div>
                    <h5 class="mb-1">PRODUCT REPAIR</h5>
                    <small>Return Details</small>
                </div>
                <div class="text-end">
                    <p class="mb-1"><strong>Return No:</strong> {{ $return->return_no }}</p>
                    <p class="mb-1"><strong>Date:</strong> {{ \Carbon\Carbon::parse($return->return_date)->format('d-m-Y') }}</p>
                    @if($return->reference_no)
                        <p class="mb-1"><strong>Reference No:</strong> {{ $return->reference_no }}</p>
                    @endif
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <p class="mb-1"><strong>Supplier/Vendor:</strong> {{ $return->supplier->supplier_name ?? 'N/A' }}</p>
                    <p class="mb-1"><strong>Return Type:</strong> 
                        <span class="badge bg-{{ $return->return_type == 'damage' ? 'danger' : ($return->return_type == 'repair' ? 'warning' : 'info') }}">
                            {{ ucfirst($return->return_type) }}
                        </span>
                    </p>
                </div>
                <div class="col-md-6">
                    <p class="mb-1"><strong>Status:</strong> 
                        @if($return->status == 1)
                            <span class="badge bg-warning">Pending</span>
                        @elseif($return->status == 2)
                            <span class="badge bg-info">Received</span>
                        @elseif($return->status == 3)
                            <span class="badge bg-success">Completed</span>
                        @endif
                    </p>
                </div>
            </div>

            @if($return->reason)
                <div class="alert alert-info mb-3">
                    <strong>Reason:</strong> {{ $return->reason }}
                </div>
            @endif

            @if($return->remarks)
                <div class="alert alert-secondary mb-3">
                    <strong>Remarks:</strong> {{ $return->remarks }}
                </div>
            @endif

            <div class="table-responsive mb-4">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th style="width: 60px;">SL</th>
                            <th>Item Description</th>
                            <th style="width: 120px;">Qty</th>
                            <th style="width: 120px;">UoM</th>
                            <th>Damage Description</th>
                            <th>Repair Notes</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($return->items as $index => $item)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $item->item_name }}</td>
                                <td>{{ number_format($item->quantity, 2) }}</td>
                                <td>{{ $item->uom }}</td>
                                <td>{{ $item->damage_description ?? '-' }}</td>
                                <td>{{ $item->repair_notes ?? '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-4 text-end no-print">
                <button onclick="window.print();" class="btn btn-primary">Print</button>
                <a href="{{ route('product-repair-list') }}" class="btn btn-secondary">Back to List</a>
                <a href="{{ route('product-repair') }}" class="btn btn-success">New Repair</a>
            </div>
        </div>
    </div>
</div>
@endsection
