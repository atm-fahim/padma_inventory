@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="{{ asset('frontend/css/item-inventory.css') }}">
<div class="container card border-top border-0 border-4 border-primary">
    <div class="row justify-content-center">
        <div class="col-lg-12">
            <div class="row mb-3">
                <div class="col-lg-6 d-flex align-items-center">
                    <h4 class="mb-0">Receive Swapped Items</h4>
                </div>
                <div class="col-lg-6 text-end">
                    <a href="{{ route('swap-items') }}" class="btn btn-outline-primary me-2">
                        <i class="bx bx-transfer-alt me-1"></i>Swap Items
                    </a>
                    <a href="{{ route('receive-stock') }}" class="btn btn-outline-secondary">
                        <i class="bx bx-arrow-back me-1"></i>Receive Stock
                    </a>
                </div>
            </div>

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ is_string(session('error')) ? session('error') : json_encode(session('error')) }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <div class="card mb-3">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bx bx-search me-2"></i>Search Pending Swap</h5>
                </div>
                <div class="card-body">
                    <ul class="nav nav-tabs mb-3">
                        <li class="nav-item">
                            <button class="nav-link {{ empty($searchEmployeeId) ? 'active' : '' }}" data-bs-toggle="tab" data-bs-target="#swap-no-tab" type="button">By Swap No</button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link {{ !empty($searchEmployeeId) ? 'active' : '' }}" data-bs-toggle="tab" data-bs-target="#employee-tab" type="button">By Employee ID</button>
                        </li>
                    </ul>
                    <div class="tab-content">
                        <div class="tab-pane fade {{ empty($searchEmployeeId) ? 'show active' : '' }}" id="swap-no-tab">
                            <form method="GET" action="{{ route('receive-swap-items') }}">
                                <div class="row">
                                    <div class="col-md-8 mb-3">
                                        <label class="form-label">Swap Number</label>
                                        <input type="text" name="swap_no" class="form-control" placeholder="e.g. SW-PBL741-20260321-741-0001"
                                               value="{{ $swapNo ?? '' }}">
                                    </div>
                                    <div class="col-md-4 mb-3 d-flex align-items-end">
                                        <button type="submit" class="btn btn-primary w-100">Search</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                        <div class="tab-pane fade {{ !empty($searchEmployeeId) ? 'show active' : '' }}" id="employee-tab">
                            <form method="GET" action="{{ route('receive-swap-items') }}">
                                <div class="row">
                                    <div class="col-md-8 mb-3">
                                        <label class="form-label">Receiver Employee ID</label>
                                        <input type="text" name="employee_id" class="form-control" placeholder="Enter Employee ID"
                                               value="{{ $searchEmployeeId ?? '' }}">
                                    </div>
                                    <div class="col-md-4 mb-3 d-flex align-items-end">
                                        <button type="submit" class="btn btn-primary w-100">Search</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            @if(isset($pendingSwaps) && $pendingSwaps->count() > 0)
                <div class="card mb-3">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0"><i class="bx bx-list-check me-2"></i>Pending Swap: {{ $swapNo }}</h5>
                    </div>
                    <div class="card-body">
                        @if(!empty($fromEmployeeInfo) || !empty($toEmployeeInfo))
                            <div class="alert alert-info">
                                @if(!empty($fromEmployeeInfo))
                                    <div><strong>From:</strong> {{ $fromEmployeeInfo->emp_name ?? 'N/A' }} ({{ $pendingSwaps->first()->from_employee_id ?? '' }})</div>
                                @endif
                                @if(!empty($toEmployeeInfo))
                                    <div><strong>To:</strong> {{ $toEmployeeInfo->emp_name ?? 'N/A' }} ({{ $pendingSwaps->first()->to_employee_id ?? '' }})</div>
                                @endif
                            </div>
                        @endif

                        <form method="POST" action="{{ route('receive-swap-items-save') }}">
                            @csrf
                            <input type="hidden" name="swap_no" value="{{ $swapNo }}">

                            <div class="table-responsive">
                                <table class="table table-bordered table-striped">
                                    <thead class="table-success">
                                    <tr>
                                        <th width="50"><input type="checkbox" id="selectAll" onchange="toggleAll()" checked></th>
                                        <th>SL</th>
                                        <th>Product</th>
                                        <th>Category</th>
                                        <th>Serial / Code</th>
                                        <th>Swap Remark</th>
                                        <th>Receive Remark</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($pendingSwaps as $index => $row)
                                        <tr>
                                            <td>
                                                <input type="checkbox" name="swap_log_ids[]" value="{{ $row->id }}" class="item-checkbox" checked>
                                            </td>
                                            <td>{{ $index + 1 }}</td>
                                            <td>{{ $row->item_title ?? 'N/A' }}</td>
                                            <td>{{ $row->category_title ?? 'N/A' }}</td>
                                            <td>{{ $row->uniq_serial_no ?: ($row->set_uniq_no ?? 'N/A') }}</td>
                                            <td>{{ $row->remarks ?? '—' }}</td>
                                            <td>
                                                <input type="text" name="remarks[{{ $row->id }}]" class="form-control form-control-sm" placeholder="Receive remark">
                                            </td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <div class="text-end mt-3">
                                <button type="submit" class="btn btn-success btn-lg">
                                    <i class="bx bx-check-circle me-2"></i>Confirm Receive
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            @elseif(!empty($swapNo) || !empty($searchEmployeeId))
                <div class="alert alert-warning">No pending swap items found.</div>
            @else
                <div class="alert alert-info">Search by swap number or receiver employee ID to confirm swapped devices.</div>
            @endif
        </div>
    </div>
</div>

<script>
    function getItemCheckboxes() { return document.querySelectorAll('input.item-checkbox[type="checkbox"]'); }
    function toggleAll() {
        const checked = document.getElementById('selectAll').checked;
        getItemCheckboxes().forEach(cb => cb.checked = checked);
    }
</script>
@endsection
