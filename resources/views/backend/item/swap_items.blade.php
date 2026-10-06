@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="{{ asset('frontend/css/item-inventory.css') }}">
<div class="container card border-top border-0 border-4 border-primary">
    <div class="row justify-content-center">
        <div class="col-lg-12">
            <div class="row mb-3">
                <div class="col-lg-6 d-flex align-items-center">
                    <h4 class="mb-0">Swap Items (Same Branch / Division)</h4>
                </div>
                <div class="col-lg-6 text-end">
                    <a href="{{ route('receive-swap-items') }}" class="btn btn-outline-success me-2">
                        <i class="bx bx-check-circle me-1"></i>Receive Swap
                    </a>
                    <a href="{{ route('transfer-items') }}" class="btn btn-outline-secondary">
                        <i class="bx bx-arrow-back me-1"></i>Back to Transfer
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
                    <h5 class="mb-0"><i class="bx bx-search me-2"></i>Search Source Employee</h5>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('swap-items') }}">
                        <div class="row">
                            <div class="col-md-8 mb-3">
                                <label class="form-label">From Employee ID <span class="text-danger">*</span></label>
                                <input type="text" name="employee_id" class="form-control" placeholder="Enter Employee ID"
                                       value="{{ $searchEmployeeId ?? '' }}" required>
                            </div>
                            <div class="col-md-4 mb-3 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary w-100"><i class="bx bx-search me-1"></i>Search</button>
                            </div>
                        </div>
                    </form>
                    @if(!empty($employeeInfo))
                        <div class="alert alert-info mb-0">
                            <strong>Name:</strong> {{ $employeeInfo->emp_name ?? 'N/A' }} |
                            <strong>Branch:</strong> {{ $employeeInfo->branch_name ?? 'N/A' }}
                        </div>
                    @endif
                </div>
            </div>

            @if(isset($swappableItems) && $swappableItems->count() > 0)
                <div class="card mb-3">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="bx bx-transfer-alt me-2"></i>Swap Items to Another Employee</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('swap-items-save') }}" id="swapForm">
                            @csrf
                            <input type="hidden" name="from_employee_id" value="{{ $searchEmployeeId }}">

                            <div class="row mb-3">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">To Employee ID <span class="text-danger">*</span></label>
                                    <input type="text" id="to_employee_id" name="to_employee_id" class="form-control"
                                           placeholder="Enter Receiver Employee ID" onchange="fetchEmployeeInfo(this.value)" required>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Employee Name</label>
                                    <input type="text" id="employee_name" class="form-control" readonly>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">To Branch <span class="text-danger">*</span></label>
                                    <select id="to_branch_id" name="to_branch_id" class="form-select single-select-4" required>
                                        <option value="">Select Branch</option>
                                        @foreach($branchData as $branch)
                                            <option value="{{ $branch->hr_branch_id }}">{{ $branch->branch_name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-12">
                                    <label class="form-label">Remark</label>
                                    <textarea name="remarks" class="form-control" rows="2" placeholder="Swap remark (optional)">{{ old('remarks') }}</textarea>
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-bordered table-striped table-hover">
                                    <thead class="table-primary">
                                    <tr>
                                        <th width="50"><input type="checkbox" id="selectAll" onchange="toggleAll()"></th>
                                        <th>SL</th>
                                        <th>Product</th>
                                        <th>Category</th>
                                        <th>Serial / Code</th>
                                        <th>Condition</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($swappableItems as $index => $item)
                                        <tr>
                                            <td>
                                                <input type="checkbox" name="inventory_ids[]" value="{{ $item->id }}" class="item-checkbox">
                                            </td>
                                            <td>{{ $index + 1 }}</td>
                                            <td>{{ $item->item_title ?? 'N/A' }}</td>
                                            <td>{{ $item->category_title ?? 'N/A' }}</td>
                                            <td>{{ $item->uniq_serial_no ?: ($item->set_uniq_no ?? 'N/A') }}</td>
                                            <td>{{ $item->condition ?? 'Good' }}</td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <div class="text-end mt-3">
                                <button type="submit" class="btn btn-success btn-lg">
                                    <i class="bx bx-transfer-alt me-2"></i>Initiate Swap
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            @elseif(!empty($searchEmployeeId))
                <div class="alert alert-warning">No swappable items found for Employee ID: <strong>{{ $searchEmployeeId }}</strong></div>
            @else
                <div class="alert alert-info">Enter a source employee ID to list items available for swapping within the same branch or division.</div>
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
    function fetchEmployeeInfo(employeeId) {
        if (!employeeId) return;
        fetch("{{ route('get-employee-by-id') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ employee_id: employeeId })
        })
            .then(r => r.json())
            .then(res => {
                if (res.success && res.data) {
                    document.getElementById('employee_name').value = res.data.name || '';
                    const branchId = res.data.branch_id || '';
                    const branchSelect = document.getElementById('to_branch_id');
                    if (branchId && branchSelect) {
                        branchSelect.value = branchId;
                        if (window.jQuery && jQuery(branchSelect).hasClass('select2-hidden-accessible')) {
                            jQuery(branchSelect).val(branchId).trigger('change');
                        }
                    }
                }
            });
    }
</script>
@endsection
