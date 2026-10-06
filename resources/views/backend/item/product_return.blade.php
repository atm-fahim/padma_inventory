@extends('layouts.app')

@section('content')
<div class="container card border-top border-0 border-4 border-primary">
    <div class="row justify-content-center">
        <div class="col-lg-12">
            <div class="row mb-3">
                <div class="col-lg-6 d-flex align-items-center">
                    <h4 class="mb-0">Product Repair</h4>
                </div>
            </div>

            <form method="POST" action="{{ route('product-repair-store') }}">
                @csrf

                <div class="card mb-3">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Supplier & Return Info</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
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
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Return Type<span class="text-danger">*</span></label>
                                <select name="return_type" class="form-select" required>
                                    <option value="repair" {{ old('return_type') == 'repair' ? 'selected' : '' }}>Repair</option>
                                    <option value="damage" {{ old('return_type') == 'damage' ? 'selected' : '' }}>Damage</option>
                                    <option value="replacement" {{ old('return_type') == 'replacement' ? 'selected' : '' }}>Replacement</option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Return Date<span class="text-danger">*</span></label>
                                <input type="date" name="return_date" class="form-control" value="{{ old('return_date', date('Y-m-d')) }}" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Reason</label>
                                <textarea name="reason" class="form-control" rows="2" placeholder="Reason for return">{{ old('reason') }}</textarea>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Reference No</label>
                                <input type="text" name="reference_no" class="form-control" value="{{ old('reference_no') }}">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Remarks</label>
                                <textarea name="remarks" class="form-control" rows="2" placeholder="Additional remarks">{{ old('remarks') }}</textarea>
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
                                        <th>Description</th>
                                        <th style="width: 120px;">Qty</th>
                                        <th style="width: 120px;">UoM</th>
                                        <th>Damage Description</th>
                                        <th>Repair Notes</th>
                                        <th style="width: 80px;">Action</th>
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
                                            <input type="hidden" name="description[]" value="">
                                        </td>
                                        <td>
                                            <input type="number" name="quantity[]" class="form-control" min="1" step="1" value="1" required>
                                        </td>
                                        <td>
                                            <input type="text" name="uom[]" class="form-control" value="PCS">
                                        </td>
                                        <td>
                                            <textarea name="damage_description[]" class="form-control" rows="2" placeholder="Describe damage"></textarea>
                                        </td>
                                        <td>
                                            <textarea name="repair_notes[]" class="form-control" rows="2" placeholder="Repair notes"></textarea>
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

                <div class="row mb-3">
                    <div class="col-md-12 text-end">
                        <button type="submit" class="btn btn-success btn-lg">Submit Return</button>
                        <a href="{{ route('product-repair-list') }}" class="btn btn-secondary btn-lg">View List</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    (function () {
        function updateSl() {
            const rows = document.querySelectorAll('#itemsTable tbody tr');
            rows.forEach((row, index) => {
                const slCell = row.querySelector('.sl');
                if (slCell) {
                    slCell.textContent = index + 1;
                }
            });
        }

        function addRow() {
            const tbody = document.querySelector('#itemsTable tbody');
            const firstRow = tbody.querySelector('tr');
            const newRow = firstRow.cloneNode(true);

            // reset select and inputs
            const select = newRow.querySelector('select[name="item_name[]"]');
            if (select) {
                select.selectedIndex = 0;
            }

            newRow.querySelectorAll('input').forEach(input => {
                if (input.name === 'quantity[]') {
                    input.value = 1;
                } else if (input.name === 'uom[]') {
                    input.value = 'PCS';
                } else {
                    input.value = '';
                }
            });

            newRow.querySelectorAll('textarea').forEach(textarea => {
                textarea.value = '';
            });

            tbody.appendChild(newRow);
            updateSl();
        }

        document.addEventListener('click', function (e) {
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
