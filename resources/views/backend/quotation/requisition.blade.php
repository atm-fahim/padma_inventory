@extends('layouts.app')

@section('content')
<div class="container card border-top border-0 border-4 border-primary">
    <div class="row justify-content-center">
        <div class="col-lg-12">
            <div class="row mb-3">
                <div class="col-lg-6 d-flex align-items-center">
                    <h4 class="mb-0">Create Requisition</h4>
                </div>
            </div>

            @if($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach($errors->all() as $e)
                            <li>{{ $e }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('requisition-store') }}" id="requisitionForm">
                @csrf

                <div class="card mb-3">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Employee Info</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Employee ID<span class="text-danger">*</span></label>
                                <input type="text" id="employee_id" name="employee_id" class="form-control"
                                       placeholder="Enter Employee ID" onchange="fetchEmployeeInfo(this.value)" value="{{ old('employee_id') }}" required>
                                <div id="employee_id_error" class="text-danger small mt-1" style="display:none;"></div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Employee Name</label>
                                <input type="text" id="employee_name" class="form-control" placeholder="Employee Name" readonly>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Requisition Date<span class="text-danger">*</span></label>
                                <input type="date" name="requisition_date" class="form-control" value="{{ old('requisition_date', date('Y-m-d')) }}" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Notes</label>
                                <textarea name="notes" class="form-control" rows="2" placeholder="Requisition note (optional)">{{ old('notes') }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card mb-3">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0">Items</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered" id="itemsTable">
                                <thead class="table-primary">
                                    <tr>
                                        <th style="width: 60px;">SL</th>
                                        <th>Item</th>
                                        <th style="width: 120px;">Qty</th>
                                        <th style="width: 120px;">UoM</th>
                                        <th style="width: 90px;">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td class="sl">1</td>
                                        <td>
                                            <select name="item_name[]" class="form-select" required>
                                                <option value="">Select Item</option>
                                                @foreach($itemsData as $item)
                                                    <option value="{{ $item->item_title ?? $item->item }}">{{ $item->item_title ?? $item->item }}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td><input type="number" name="quantity[]" class="form-control" min="0.01" step="0.01" value="1" required></td>
                                        <td><input type="text" name="uom[]" class="form-control" value="PCS"></td>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-danger btn-sm remove-row">&times;</button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <button type="button" class="btn btn-primary" id="addRowBtn">Add Item +</button>
                    </div>
                </div>

                <div class="text-end mb-3">
                    <button type="submit" class="btn btn-success btn-lg">Submit Requisition</button>
                    <a href="{{ route('requisition-list') }}" class="btn btn-secondary btn-lg">View List</a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
(function() {
    'use strict';
    window.fetchEmployeeInfo = function(employeeId) {
        if (typeof jQuery === 'undefined') return;
        var $ = jQuery;
        employeeId = employeeId ? employeeId.trim() : '';
        if (!employeeId) return;

        $.ajax({
            url: '{{ route("get-employee-by-id") }}',
            method: 'POST',
            data: { _token: '{{ csrf_token() }}', employee_id: employeeId },
            success: function(response) {
                if (response.success) {
                    $('#employee_name').val(response.data.name);
                    $('#employee_id_error').hide();
                } else {
                    $('#employee_id_error').text(response.message || 'Employee not found').show();
                    $('#employee_name').val('');
                }
            },
            error: function(xhr) {
                const msg = xhr.responseJSON?.message || 'Error fetching employee information';
                $('#employee_id_error').text(msg).show();
                $('#employee_name').val('');
            }
        });
    };

    function updateSl() {
        document.querySelectorAll('#itemsTable tbody tr').forEach((row, idx) => {
            const sl = row.querySelector('.sl');
            if (sl) sl.textContent = idx + 1;
        });
    }

    function addRow() {
        const tbody = document.querySelector('#itemsTable tbody');
        const firstRow = tbody.querySelector('tr');
        const newRow = firstRow.cloneNode(true);
        newRow.querySelectorAll('input, select').forEach(el => {
            if (el.name === 'quantity[]') el.value = 1;
            else if (el.name === 'uom[]') el.value = 'PCS';
            else if (el.tagName === 'SELECT') el.selectedIndex = 0;
        });
        tbody.appendChild(newRow);
        updateSl();
    }

    document.addEventListener('click', function(e) {
        if (e.target && e.target.id === 'addRowBtn') {
            e.preventDefault();
            addRow();
        }
        if (e.target && e.target.classList.contains('remove-row')) {
            const tbody = document.querySelector('#itemsTable tbody');
            if (tbody.querySelectorAll('tr').length > 1) {
                e.target.closest('tr').remove();
                updateSl();
            }
        }
    });
})();
</script>
@endsection

