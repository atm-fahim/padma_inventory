@extends('layouts.app')

@section('content')
<div class="container card border-top border-0 border-4 border-primary">
    <div class="row justify-content-center">
        <div class="col-lg-12 p-4">
            <div class="d-flex justify-content-between mb-3">
                <div>
                    <h5 class="mb-1">QUOTATION</h5>
                    <small class="text-muted">Approval & Signature</small>
                </div>
                <div class="text-end">
                    <p class="mb-1"><strong>Quotation No:</strong> {{ $quotation->quotation_no }}</p>
                    <p class="mb-1"><strong>Date:</strong> {{ \Carbon\Carbon::parse($quotation->quotation_date)->format('d-m-Y') }}</p>
                    <p class="mb-1"><strong>Status:</strong> <span class="badge bg-secondary">{{ $quotation->status }}</span></p>
                </div>
            </div>

            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <div class="row mb-3">
                <div class="col-md-6">
                    <p class="mb-1"><strong>Employee:</strong> {{ $quotation->employee_name }} <span class="text-muted">({{ $quotation->employee_id }})</span></p>
                    <p class="mb-1"><strong>Supplier:</strong> {{ $quotation->supplier->supplier_name ?? 'N/A' }}</p>
                </div>
                <div class="col-md-6 text-end">
                    <p class="mb-1"><strong>Subtotal:</strong> {{ number_format($quotation->subtotal, 2) }}</p>
                    <p class="mb-1"><strong>Discount:</strong> {{ number_format($quotation->discount_amount, 2) }}</p>
                    <p class="mb-1"><strong>VAT:</strong> {{ number_format($quotation->vat_amount, 2) }}</p>
                    <p class="mb-1"><strong>Tax:</strong> {{ number_format($quotation->tax_amount, 2) }}</p>
                    <p class="mb-1"><strong>Total:</strong> {{ number_format($quotation->total_amount, 2) }}</p>
                </div>
            </div>

            @if($quotation->note)
                <div class="alert alert-info">
                    <strong>Note:</strong> {{ $quotation->note }}
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
                            <th class="text-end">Unit Price</th>
                            <th class="text-end">Discount</th>
                            <th class="text-end">VAT</th>
                            <th class="text-end">Tax</th>
                            <th class="text-end">Line Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($quotation->items as $i => $it)
                            <tr>
                                <td>{{ $i + 1 }}</td>
                                <td><strong>{{ $it->item_name }}</strong></td>
                                <td class="text-end">{{ number_format($it->quantity, 2) }}</td>
                                <td>{{ $it->uom }}</td>
                                <td class="text-end">{{ number_format($it->unit_price, 2) }}</td>
                                <td class="text-end">{{ number_format($it->discount, 2) }}</td>
                                <td class="text-end">{{ number_format($it->vat, 2) }}</td>
                                <td class="text-end">{{ number_format($it->tax, 2) }}</td>
                                <td class="text-end"><strong>{{ number_format($it->line_total, 2) }}</strong></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @php
                $levels = [
                    'admin' => 'System Admin',
                    'division_head' => 'Division Head',
                    'md' => 'MD',
                    'board' => 'Board',
                ];
                $approvalsByLevel = $quotation->approvals->keyBy('approval_level');
            @endphp

            <div class="card mb-3">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">Approval Signatures</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        @foreach($levels as $levelKey => $label)
                            @php $ap = $approvalsByLevel[$levelKey] ?? null; @endphp
                            <div class="col-md-3">
                                <div class="border rounded p-3 h-100">
                                    <div class="fw-bold">{{ $label }}</div>
                                    <div class="text-muted small mb-2">Level: {{ $levelKey }}</div>
                                    @if($ap && $ap->status === 'approved')
                                        <div><strong>{{ $ap->approver_name }}</strong></div>
                                        <div class="text-muted small">{{ $ap->approver_designation }}</div>
                                        <div class="text-muted small">Approved: {{ \Carbon\Carbon::parse($ap->approved_at)->format('d-m-Y H:i') }}</div>
                                    @elseif($ap && $ap->status === 'rejected')
                                        <div class="text-danger fw-bold">Rejected</div>
                                        <div class="text-muted small">{{ $ap->approver_name }}</div>
                                        <div class="text-muted small">{{ $ap->comments }}</div>
                                    @else
                                        <div class="text-muted">Pending</div>
                                    @endif
                                    <hr>
                                    <div class="small text-muted">Comments:</div>
                                    <div class="small">{{ $ap->comments ?? '-' }}</div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            @if($canApprove && $nextApprovalLevel)
                <div class="card mb-3">
                    <div class="card-header bg-warning">
                        <strong>Approve Quotation</strong>
                        <div class="small text-muted">Your level: {{ $currentUserApprovalLevel }} | Next required: {{ $nextApprovalLevel }}</div>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('quotation-approve', $quotation->id) }}">
                            @csrf
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Designation</label>
                                    <input type="text" name="designation" class="form-control" placeholder="e.g. Division Head / MD">
                                </div>
                                <div class="col-md-8 mb-3">
                                    <label class="form-label">Comments</label>
                                    <input type="text" name="comments" class="form-control" placeholder="Approval comments (optional)">
                                </div>
                            </div>
                            <div class="text-end">
                                <button type="submit" name="status" value="approved" class="btn btn-success">Approve</button>
                                <button type="submit" name="status" value="rejected" class="btn btn-danger">Reject</button>
                                <a href="{{ route('quotation-list') }}" class="btn btn-secondary">Back</a>
                            </div>
                        </form>
                    </div>
                </div>
            @else
                <div class="text-end">
                    <a href="{{ route('quotation-list') }}" class="btn btn-secondary">Back to List</a>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

