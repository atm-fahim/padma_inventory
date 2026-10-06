@extends('layouts.app')

@section('content')
<div class="container card border-top border-0 border-4 border-primary">
    <div class="row justify-content-center">
        <div class="col-lg-12 p-4">
            <div class="d-flex justify-content-between mb-3">
                <div>
                    <h5 class="mb-1">REQUISITION</h5>
                    <small class="text-muted">Details</small>
                </div>
                <div class="text-end">
                    <p class="mb-1"><strong>No:</strong> {{ $requisition->requisition_no }}</p>
                    <p class="mb-1"><strong>Date:</strong> {{ \Carbon\Carbon::parse($requisition->requisition_date)->format('d-m-Y') }}</p>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <p class="mb-1"><strong>Employee:</strong> {{ $requisition->employee_name }} <span class="text-muted">({{ $requisition->employee_id }})</span></p>
                </div>
                <div class="col-md-6 text-end">
                    <p class="mb-1"><strong>Status:</strong>
                        @if($requisition->status == 1)
                            <span class="badge bg-secondary">Draft</span>
                        @elseif($requisition->status == 2)
                            <span class="badge bg-warning">Submitted</span>
                        @else
                            <span class="badge bg-dark">N/A</span>
                        @endif
                    </p>
                </div>
            </div>

            @if($requisition->notes)
                <div class="alert alert-info">
                    <strong>Notes:</strong> {{ $requisition->notes }}
                </div>
            @endif

            <div class="table-responsive mb-4">
                <table class="table table-bordered">
                    <thead class="table-primary">
                        <tr>
                            <th>SL</th>
                            <th>Item</th>
                            <th class="text-end">Qty</th>
                            <th>UoM</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($requisition->items as $i => $it)
                            <tr>
                                <td>{{ $i + 1 }}</td>
                                <td><strong>{{ $it->item_name }}</strong></td>
                                <td class="text-end">{{ number_format($it->quantity, 2) }}</td>
                                <td>{{ $it->uom }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="text-end">
                <a href="{{ route('requisition-list') }}" class="btn btn-secondary">Back to List</a>
            </div>
        </div>
    </div>
</div>
@endsection

