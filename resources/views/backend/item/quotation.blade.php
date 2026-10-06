@extends('layouts.app')

@section('content')
<div class="container card border-top border-0 border-4 border-primary">
    <div class="row justify-content-center">
        <div class="col-lg-12">
            <div class="row mb-3">
                <div class="col-lg-6 d-flex align-items-center">
                    <h4 class="mb-0">Create Quotation</h4>
                </div>
            </div>

            <form method="POST" action="{{ route('quotation-store') }}" id="quotationForm">
                @csrf

                <div class="card mb-3">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Employee & Supplier Info</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Employee ID<span class="text-danger">*</span></label>
                                <input type="text" id="employee_id" name="employee_id" class="form-control" 
                                       placeholder="Enter Employee ID" onchange="fetchEmployeeInfo(this.value)" value="{{ old('employee_id') }}" required>
                                <div id="employee_id_error" class="text-danger small mt-1" style="display: none;"></div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Employee Name</label>
                                <input type="text" id="employee_name" class="form-control" 
                                       placeholder="Employee Name" readonly>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Supplier/Vendor<span class="text-danger">*</span></label>
                                <select name="supplier_id" class="form-select" required>
                                    <option value="">Select Supplier</option>
                                    @foreach($suppliers as $supplier)
                                        <option value="{{ $supplier->id }}" {{ old('supplier_id') == $supplier->id ? 'selected' : '' }}>
                                            {{ $supplier->supplier_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Quotation Date<span class="text-danger">*</span></label>
                                <input type="date" name="quotation_date" class="form-control" value="{{ old('quotation_date', date('Y-m-d')) }}" required>
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
                                <thead>
                                    <tr>
                                        <th style="width: 50px;">SL</th>
                                        <th>Item</th>
                                        <th style="width: 100px;">Qty</th>
                                        <th style="width: 100px;">UoM</th>
                                        <th style="width: 120px;">Unit Price</th>
                                        <th style="width: 120px;">Discount</th>
                                        <th style="width: 100px;">VAT</th>
                                        <th style="width: 100px;">Tax</th>
                                        <th style="width: 120px;">Line Total</th>
                                        <th style="width: 80px;">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td class="sl">1</td>
                                        <td>
                                            <select name="item_id[]" class="form-select item-select" required>
                                                <option value="">Select Item</option>
                                                @foreach($itemsData as $item)
                                                    <option value="{{ $item->id }}" 
                                                            data-name="{{ $item->item_title ?? $item->item }}"
                                                            data-price="{{ $item->unit_price ?? 0 }}">
                                                        {{ $item->item_title ?? $item->item }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            <input type="hidden" name="item_name[]" class="item-name-input">
                                            <input type="hidden" name="description[]" value="">
                                        </td>
                                        <td>
                                            <input type="number" name="quantity[]" class="form-control qty-input" min="0.01" step="0.01" value="1" required>
                                        </td>
                                        <td>
                                            <input type="text" name="uom[]" class="form-control" value="PCS">
                                        </td>
                                        <td>
                                            <input type="number" name="unit_price[]" class="form-control price-input" min="0" step="0.01" value="0" required>
                                        </td>
                                        <td>
                                            <input type="number" name="discount[]" class="form-control discount-input" min="0" step="0.01" value="0">
                                        </td>
                                        <td>
                                            <input type="number" name="vat[]" class="form-control vat-input" min="0" step="0.01" value="0">
                                        </td>
                                        <td>
                                            <input type="number" name="tax[]" class="form-control tax-input" min="0" step="0.01" value="0">
                                        </td>
                                        <td>
                                            <input type="text" name="line_total[]" class="form-control line-total" readonly value="0.00">
                                        </td>
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

                <div class="card mb-3">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0">Summary</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Note</label>
                                <textarea name="note" class="form-control" rows="3" placeholder="Additional notes or comments">{{ old('note') }}</textarea>
                            </div>
                            <div class="col-md-6">
                                <div class="row">
                                    <div class="col-md-6 text-end"><strong>Subtotal:</strong></div>
                                    <div class="col-md-6 text-end"><span id="subtotal">0.00</span></div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 text-end"><strong>Discount:</strong></div>
                                    <div class="col-md-6 text-end"><span id="total-discount">0.00</span></div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 text-end"><strong>VAT:</strong></div>
                                    <div class="col-md-6 text-end"><span id="total-vat">0.00</span></div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 text-end"><strong>Tax:</strong></div>
                                    <div class="col-md-6 text-end"><span id="total-tax">0.00</span></div>
                                </div>
                                <hr>
                                <div class="row">
                                    <div class="col-md-6 text-end"><strong>Total:</strong></div>
                                    <div class="col-md-6 text-end"><strong><span id="grand-total">0.00</span></strong></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-12 text-end">
                        <button type="submit" class="btn btn-success btn-lg">Save Quotation</button>
                        <a href="{{ route('quotation-list') }}" class="btn btn-secondary btn-lg">View List</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
(function() {
    'use strict';
    
    window.fetchEmployeeInfo = function(employeeId) {
        if (typeof jQuery === 'undefined') {
            console.error('jQuery is not loaded');
            return;
        }
        var $ = jQuery;
        
        employeeId = employeeId ? employeeId.trim() : '';
        if (!employeeId) {
            window.clearEmployeeInfo();
            return;
        }

        $.ajax({
            url: '{{ route("get-employee-by-id") }}',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                employee_id: employeeId
            },
            success: function(response) {
                if (response.success) {
                    $('#employee_name').val(response.data.name);
                    $('#employee_id_error').hide();
                } else {
                    window.showEmployeeError(response.message || 'Employee not found');
                }
            },
            error: function(xhr) {
                const errorMsg = xhr.responseJSON?.message || 'Error fetching employee information';
                window.showEmployeeError(errorMsg);
            }
        });
    };

    window.showEmployeeError = function(message) {
        if (typeof jQuery === 'undefined') return;
        var $ = jQuery;
        $('#employee_id_error').text(message).show();
        $('#employee_name').val('');
    };

    window.clearEmployeeInfo = function() {
        if (typeof jQuery === 'undefined') return;
        var $ = jQuery;
        $('#employee_name').val('');
        $('#employee_id_error').hide();
    };

    function updateSl() {
        const rows = document.querySelectorAll('#itemsTable tbody tr');
        rows.forEach((row, index) => {
            const slCell = row.querySelector('.sl');
            if (slCell) {
                slCell.textContent = index + 1;
            }
        });
    }

    function calculateLineTotal(row) {
        const qty = parseFloat(row.querySelector('.qty-input').value) || 0;
        const price = parseFloat(row.querySelector('.price-input').value) || 0;
        const discount = parseFloat(row.querySelector('.discount-input').value) || 0;
        const vat = parseFloat(row.querySelector('.vat-input').value) || 0;
        const tax = parseFloat(row.querySelector('.tax-input').value) || 0;
        
        const lineTotal = (qty * price) - discount + vat + tax;
        row.querySelector('.line-total').value = lineTotal.toFixed(2);
        calculateTotals();
    }

    function calculateTotals() {
        let subtotal = 0;
        let totalDiscount = 0;
        let totalVat = 0;
        let totalTax = 0;

        document.querySelectorAll('#itemsTable tbody tr').forEach(row => {
            const qty = parseFloat(row.querySelector('.qty-input').value) || 0;
            const price = parseFloat(row.querySelector('.price-input').value) || 0;
            const discount = parseFloat(row.querySelector('.discount-input').value) || 0;
            const vat = parseFloat(row.querySelector('.vat-input').value) || 0;
            const tax = parseFloat(row.querySelector('.tax-input').value) || 0;

            subtotal += (qty * price);
            totalDiscount += discount;
            totalVat += vat;
            totalTax += tax;
        });

        const grandTotal = subtotal - totalDiscount + totalVat + totalTax;

        document.getElementById('subtotal').textContent = subtotal.toFixed(2);
        document.getElementById('total-discount').textContent = totalDiscount.toFixed(2);
        document.getElementById('total-vat').textContent = totalVat.toFixed(2);
        document.getElementById('total-tax').textContent = totalTax.toFixed(2);
        document.getElementById('grand-total').textContent = grandTotal.toFixed(2);
    }

    function addRow() {
        const tbody = document.querySelector('#itemsTable tbody');
        const firstRow = tbody.querySelector('tr');
        const newRow = firstRow.cloneNode(true);

        newRow.querySelectorAll('input, select').forEach(input => {
            if (input.name === 'quantity[]') {
                input.value = 1;
            } else if (input.name === 'uom[]') {
                input.value = 'PCS';
            } else if (input.name === 'unit_price[]' || input.name === 'discount[]' || input.name === 'vat[]' || input.name === 'tax[]') {
                input.value = 0;
            } else if (input.name === 'line_total[]') {
                input.value = '0.00';
            } else if (input.classList.contains('item-select')) {
                input.selectedIndex = 0;
            } else {
                input.value = '';
            }
        });

        tbody.appendChild(newRow);
        updateSl();
        attachRowEvents(newRow);
    }

    function attachRowEvents(row) {
        row.querySelector('.item-select')?.addEventListener('change', function() {
            const option = this.options[this.selectedIndex];
            const price = parseFloat(option.dataset.price) || 0;
            const name = option.dataset.name || '';
            row.querySelector('.price-input').value = price;
            row.querySelector('.item-name-input').value = name;
            calculateLineTotal(row);
        });

        ['qty-input', 'price-input', 'discount-input', 'vat-input', 'tax-input'].forEach(className => {
            row.querySelector('.' + className)?.addEventListener('input', function() {
                calculateLineTotal(row);
            });
        });
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
                calculateTotals();
            }
        }
    });

    // Attach events to initial row
    document.querySelectorAll('#itemsTable tbody tr').forEach(row => {
        attachRowEvents(row);
    });
})();
</script>
@endsection
