@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="{{ asset('frontend/css/item-inventory.css') }}">
<div class="container card border-top border-0 border-4 border-primary">
    <div class="row justify-content-center">
        <div class="col-lg-12 py-3">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4 class="mb-0">Edit Item Inventory</h4>
                <a href="{{ route('items-inventory-list') }}" class="btn btn-primary btn-sm">
                    <i class="bx bx-left-arrow-alt me-1"></i> Back To Inventory List
                </a>
            </div>

            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('items-inventory-update', $itemData->id) }}" enctype="multipart/form-data">
                @csrf

                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Category <span class="text-danger">*</span></label>
                        <select name="category_id" class="form-select" required>
                            <option value="">Select Category</option>
                            @foreach($catData as $category)
                                <option value="{{ $category->id }}" {{ (int) old('category_id', $itemData->category_id) === (int) $category->id ? 'selected' : '' }}>
                                    {{ $category->category_title }}
                                </option>
                                @if ($category->children)
                                    @foreach ($category->children as $child)
                                        <option value="{{ $child->id }}" {{ (int) old('category_id', $itemData->category_id) === (int) $child->id ? 'selected' : '' }}>
                                            --- {{ $child->category_title }}
                                        </option>
                                    @endforeach
                                @endif
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="form-label">Item <span class="text-danger">*</span></label>
                        <select name="item_id" class="form-select" id="editItemSelect" required>
                            <option value="">Select Item</option>
                            @foreach($itemsData as $item)
                                <option value="{{ $item->id }}"
                                        data-category-id="{{ $item->category_id ?? '' }}"
                                    {{ (int) old('item_id', $itemData->item_id) === (int) $item->id ? 'selected' : '' }}>
                                    {{ $item->item_title ?? $item->item }}
                                </option>
                            @endforeach
                        </select>
                        @php
                            $oldSpecs = old('specifications');
                            $specRows = is_array($oldSpecs) ? $oldSpecs : ($specifications ?? []);
                        @endphp
                        @if(!empty($showComputerSpecs))
                            <div class="card mt-2">
                                <div class="card-body p-2">
                                    <h6 class="card-title mb-2"><i class="bx bx-chip me-1"></i>Item Specifications:</h6>
                                    <div class="table-responsive">
                                        <table class="table table-sm table-bordered mb-2" id="specificationsPreviewTable">
                                            <thead class="table-light">
                                            <tr>
                                                <th style="width: 35%;">Type</th>
                                                <th>Value</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            @forelse($specRows as $spec)
                                                <tr>
                                                    <td>{{ $spec['type'] ?? '' }}</td>
                                                    <td>{{ $spec['value'] ?? '' }}</td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="2" class="text-muted">No specifications found.</td>
                                                </tr>
                                            @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                    <button type="button" class="btn btn-sm btn-outline-primary" id="openSpecificationsModal" data-bs-toggle="modal" data-bs-target="#specificationsModal">
                                        <i class="bx bx-edit-alt me-1"></i>Modify Specifications
                                    </button>

                                    <div id="specificationsInputs">
                                        @foreach($specRows as $index => $spec)
                                            <input type="hidden" name="specifications[{{ $index }}][type]" value="{{ $spec['type'] ?? '' }}">
                                            <input type="hidden" name="specifications[{{ $index }}][value]" value="{{ $spec['value'] ?? '' }}">
                                            <input type="hidden" name="specifications[{{ $index }}][brand_id]" value="{{ $spec['brand_id'] ?? '' }}">
                                            <input type="hidden" name="specifications[{{ $index }}][condition]" value="{{ $spec['condition'] ?? '' }}">
                                        @endforeach
                                    </div>
                                    <script type="application/json" id="initialSpecificationsJson">@json(array_values($specRows))</script>
                                </div>
                            </div>
                        @endif
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="form-label">Brand</label>
                        <select name="brand_id" class="form-select">
                            <option value="">Select Brand</option>
                            @foreach($brandData as $brand)
                                <option value="{{ $brand->id }}" {{ (int) old('brand_id', $itemData->brand_id) === (int) $brand->id ? 'selected' : '' }}>
                                    {{ $brand->brand_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="form-label">Supplier</label>
                        <select name="supplier_id" class="form-select">
                            <option value="">Select Supplier</option>
                            @foreach($supplierData as $supplier)
                                <option value="{{ $supplier->id }}" {{ (int) old('supplier_id', $itemData->supplier_id) === (int) $supplier->id ? 'selected' : '' }}>
                                    {{ $supplier->supplier_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="row">
                    @php
                        $swYm = $serviceWarrantyYm ?? ['years' => '', 'months' => ''];
                        $wrYm = $warrantyYm ?? ['years' => '', 'months' => ''];
                    @endphp
                    <div class="col-auto mb-3">
                        <label class="form-label small mb-1">Service Warranty</label>
                        <div class="warranty-ym-group" title="Year (left), Month (right)">
                            <div class="warranty-ym-field">
                                <span class="warranty-ym-lbl">Year</span>
                                <input type="number" min="0" step="1" name="service_warranty_years" class="form-control warranty-ym-input"
                                       value="{{ old('service_warranty_years', $swYm['years']) }}" placeholder="0">
                            </div>
                            <div class="warranty-ym-field">
                                <span class="warranty-ym-lbl">Month</span>
                                <input type="number" min="0" step="1" name="service_warranty_months" class="form-control warranty-ym-input"
                                       value="{{ old('service_warranty_months', $swYm['months']) }}" placeholder="0">
                            </div>
                        </div>
                    </div>
                    <div class="col-auto mb-3">
                        <label class="form-label small mb-1">Warranty</label>
                        <div class="warranty-ym-group">
                            <div class="warranty-ym-field">
                                <span class="warranty-ym-lbl">Year</span>
                                <input type="number" min="0" step="1" name="warranty_years" class="form-control warranty-ym-input"
                                       value="{{ old('warranty_years', $wrYm['years']) }}" placeholder="0">
                            </div>
                            <div class="warranty-ym-field">
                                <span class="warranty-ym-lbl">Month</span>
                                <input type="number" min="0" step="1" name="warranty_months" class="form-control warranty-ym-input"
                                       value="{{ old('warranty_months', $wrYm['months']) }}" placeholder="0">
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Serial No</label>
                        <input type="text" name="serial_no" class="form-control" value="{{ old('serial_no', $itemData->uniq_serial_no) }}">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Condition</label>
                        <select name="condition" class="form-select">
                            <option value="">Select Condition</option>
                            @foreach(['slow' => 'Slow', 'good' => 'Good', 'bad' => 'Bad', 'damage' => 'Damage', 'normal' => 'Normal', 'unused' => 'Unused'] as $key => $label)
                                <option value="{{ $key }}" {{ old('condition', $itemData->condition) === $key ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-2 mb-3">
                        <label class="form-label">Quantity</label>
                        <input type="number" min="0" step="1" name="quantity" class="form-control" value="{{ old('quantity', $itemData->quantity) }}">
                    </div>
                    <div class="col-md-2 mb-3">
                        <label class="form-label">Unit Size</label>
                        <input type="text" name="unit_size" class="form-control" value="{{ old('unit_size', $itemData->unit_size) }}">
                    </div>
                    <div class="col-md-2 mb-3">
                        <label class="form-label">Price</label>
                        <input type="number" min="0" step="0.01" name="price" class="form-control" value="{{ old('price', $itemData->price) }}">
                    </div>
                    <div class="col-md-2 mb-3">
                        <label class="form-label">VAT (%)</label>
                        <input type="number" min="0" step="0.01" name="vat" class="form-control" value="{{ old('vat', $itemData->vat) }}">
                    </div>
                    <div class="col-md-2 mb-3">
                        <label class="form-label">Tax (%)</label>
                        <input type="number" min="0" step="0.01" name="tax" class="form-control" value="{{ old('tax', $itemData->tax) }}">
                    </div>
                    <div class="col-md-2 mb-3">
                        <label class="form-label">Code</label>
                        <input type="text" class="form-control" value="{{ $itemData->set_uniq_no }}" readonly>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-8 mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="3">{{ old('description', $itemData->description) }}</textarea>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Invoice Attachment</label>
                        <input type="file" name="invoice_attachment" class="form-control" accept=".jpg,.jpeg,.png,.pdf">
                        @if(!empty($itemData->invoice_attachment))
                            <div class="mt-2">
                                <a href="{{ asset($itemData->invoice_attachment) }}" target="_blank" class="btn btn-outline-secondary btn-sm">
                                    <i class="bx bx-paperclip me-1"></i> Current Attachment
                                </a>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="mt-2">
                    <button type="submit" class="btn btn-success btn-sm px-4">
                        <i class="bx bx-save me-1"></i> Update Inventory
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<div class="modal fade" id="specificationsModal" tabindex="-1" aria-labelledby="specificationsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="specificationsModalLabel"><i class="bx bx-chip me-1"></i>Item Specifications</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <h6>RAM</h6>
                    <div id="ramRowsContainer">
                        <div class="card p-2 mb-2 spec-row ram-row">
                            <div class="row g-2">
                                <div class="col-md-3"><label class="form-label mb-1">RAM</label><select class="form-select form-select-sm spec-value"><option value="">Select RAM</option><option>2 GB</option><option>4 GB</option><option>8 GB</option><option>16 GB</option><option>32 GB</option><option>64 GB</option><option>128 GB</option></select></div>
                                <div class="col-md-3">
                                    <label class="form-label mb-1">Brand</label>
                                    <select class="form-select form-select-sm spec-brand">
                                        <option value="">Select Brand</option>
                                        @foreach($brandData as $brand)
                                            <option value="{{ $brand->id }}">{{ $brand->brand_name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3"><label class="form-label mb-1">Service</label><input type="text" class="form-control form-control-sm" placeholder="e.g. 1 Year"></div>
                                <div class="col-md-3"><label class="form-label mb-1">Warranty</label><input type="text" class="form-control form-control-sm" placeholder="e.g. 6 Month"></div>
                                <div class="col-md-3">
                                    <label class="form-label mb-1">Condition</label>
                                    <select class="form-select form-select-sm spec-condition">
                                        <option value="">Select Condition</option>
                                        <option value="slow">Slow</option>
                                        <option value="good">Good</option>
                                        <option value="bad">Bad</option>
                                        <option value="damage">Damage</option>
                                        <option value="normal">Normal</option>
                                        <option value="unused">Unused</option>
                                    </select>
                                </div>
                                <div class="col-md-3"><label class="form-label mb-1">Serial No</label><input type="text" class="form-control form-control-sm" placeholder="e.g. SN-001"></div>
                                <div class="col-md-2"><label class="form-label mb-1">Quantity</label><input type="number" class="form-control form-control-sm" value="1"></div>
                                <div class="col-md-2"><label class="form-label mb-1">Price</label><input type="number" class="form-control form-control-sm" value="0"></div>
                                <div class="col-md-2"><label class="form-label mb-1">Tax (%)</label><input type="number" class="form-control form-control-sm" value="0"></div>
                                <div class="col-md-2 d-flex align-items-end"><button type="button" class="btn btn-sm btn-outline-danger spec-remove-btn w-100">Remove</button></div>
                            </div>
                        </div>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-primary" id="addRamRow"><i class="bx bx-plus-circle me-1"></i>Add RAM Row</button>
                </div>
                <div class="mb-3">
                    <h6>Processor</h6>
                    <div id="processorRowsContainer">
                        <div class="card p-2 mb-2 spec-row processor-row">
                            <div class="row g-2">
                                <div class="col-md-3"><label class="form-label mb-1">Processor</label><select class="form-select form-select-sm spec-value"><option value="">Select Processor</option><option>Intel Core i3</option><option>Intel Core i5</option><option>Intel Core i7</option><option>Intel Core i9</option><option>AMD Ryzen 5</option><option>AMD Ryzen 7</option></select></div>
                                <div class="col-md-3">
                                    <label class="form-label mb-1">Brand</label>
                                    <select class="form-select form-select-sm spec-brand">
                                        <option value="">Select Brand</option>
                                        @foreach($brandData as $brand)
                                            <option value="{{ $brand->id }}">{{ $brand->brand_name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3"><label class="form-label mb-1">Service</label><input type="text" class="form-control form-control-sm" placeholder="e.g. 1 Year"></div>
                                <div class="col-md-3"><label class="form-label mb-1">Warranty</label><input type="text" class="form-control form-control-sm" placeholder="e.g. 6 Month"></div>
                                <div class="col-md-3">
                                    <label class="form-label mb-1">Condition</label>
                                    <select class="form-select form-select-sm spec-condition">
                                        <option value="">Select Condition</option>
                                        <option value="slow">Slow</option>
                                        <option value="good">Good</option>
                                        <option value="bad">Bad</option>
                                        <option value="damage">Damage</option>
                                        <option value="normal">Normal</option>
                                        <option value="unused">Unused</option>
                                    </select>
                                </div>
                                <div class="col-md-3"><label class="form-label mb-1">Serial No</label><input type="text" class="form-control form-control-sm" placeholder="e.g. SN-001"></div>
                                <div class="col-md-2"><label class="form-label mb-1">Quantity</label><input type="number" class="form-control form-control-sm" value="1"></div>
                                <div class="col-md-2"><label class="form-label mb-1">Price</label><input type="number" class="form-control form-control-sm" value="0"></div>
                                <div class="col-md-2"><label class="form-label mb-1">Tax (%)</label><input type="number" class="form-control form-control-sm" value="0"></div>
                                <div class="col-md-2 d-flex align-items-end"><button type="button" class="btn btn-sm btn-outline-danger spec-remove-btn w-100">Remove</button></div>
                            </div>
                        </div>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-primary" id="addProcessorRow"><i class="bx bx-plus-circle me-1"></i>Add Processor Row</button>
                </div>
                <div class="mb-1">
                    <h6>HDD/SSD</h6>
                    <div id="storageRowsContainer">
                        <div class="card p-2 mb-2 spec-row storage-row">
                            <div class="row g-2">
                                <div class="col-md-3"><label class="form-label mb-1">Storage</label><select class="form-select form-select-sm spec-value"><option value="">Select Storage</option><option>128 GB SSD</option><option>256 GB SSD</option><option>512 GB SSD</option><option>1 TB SSD</option><option>500 GB HDD</option><option>1 TB HDD</option><option>2 TB HDD</option></select></div>
                                <div class="col-md-3">
                                    <label class="form-label mb-1">Brand</label>
                                    <select class="form-select form-select-sm spec-brand">
                                        <option value="">Select Brand</option>
                                        @foreach($brandData as $brand)
                                            <option value="{{ $brand->id }}">{{ $brand->brand_name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3"><label class="form-label mb-1">Service</label><input type="text" class="form-control form-control-sm" placeholder="e.g. 1 Year"></div>
                                <div class="col-md-3"><label class="form-label mb-1">Warranty</label><input type="text" class="form-control form-control-sm" placeholder="e.g. 6 Month"></div>
                                <div class="col-md-3">
                                    <label class="form-label mb-1">Condition</label>
                                    <select class="form-select form-select-sm spec-condition">
                                        <option value="">Select Condition</option>
                                        <option value="slow">Slow</option>
                                        <option value="good">Good</option>
                                        <option value="bad">Bad</option>
                                        <option value="damage">Damage</option>
                                        <option value="normal">Normal</option>
                                        <option value="unused">Unused</option>
                                    </select>
                                </div>
                                <div class="col-md-3"><label class="form-label mb-1">Serial No</label><input type="text" class="form-control form-control-sm" placeholder="e.g. SN-001"></div>
                                <div class="col-md-2"><label class="form-label mb-1">Quantity</label><input type="number" class="form-control form-control-sm" value="1"></div>
                                <div class="col-md-2"><label class="form-label mb-1">Price</label><input type="number" class="form-control form-control-sm" value="0"></div>
                                <div class="col-md-2"><label class="form-label mb-1">Tax (%)</label><input type="number" class="form-control form-control-sm" value="0"></div>
                                <div class="col-md-2 d-flex align-items-end"><button type="button" class="btn btn-sm btn-outline-danger spec-remove-btn w-100">Remove</button></div>
                            </div>
                        </div>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-primary" id="addStorageRow"><i class="bx bx-plus-circle me-1"></i>Add HDD Row</button>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary btn-sm" id="saveSpecificationsFromModal">Save</button>
            </div>
        </div>
    </div>
</div>
<script>
    (function () {
        const itemSelect = document.getElementById('editItemSelect') || document.querySelector('select[name="item_id"]');
        const categorySelect = document.querySelector('select[name="category_id"]');
        const conditionSelect = document.querySelector('select[name="condition"]');
        if (!itemSelect) return;

        itemSelect.addEventListener('change', function () {
            const opt = itemSelect.options[itemSelect.selectedIndex];
            const categoryId = opt ? String(opt.getAttribute('data-category-id') || '').trim() : '';
            if (categoryId && categorySelect) {
                categorySelect.value = categoryId;
            }
            if (conditionSelect) {
                conditionSelect.value = itemSelect.value ? 'good' : '';
            }
        });
    })();
</script>
<script>
    (function () {
        const previewTableBody = document.querySelector('#specificationsPreviewTable tbody');
        const inputsContainer = document.getElementById('specificationsInputs');
        const openModalBtn = document.getElementById('openSpecificationsModal');
        const ramRowsContainer = document.getElementById('ramRowsContainer');
        const processorRowsContainer = document.getElementById('processorRowsContainer');
        const storageRowsContainer = document.getElementById('storageRowsContainer');
        const addRamRowBtn = document.getElementById('addRamRow');
        const addProcessorRowBtn = document.getElementById('addProcessorRow');
        const addStorageRowBtn = document.getElementById('addStorageRow');
        const saveModalBtn = document.getElementById('saveSpecificationsFromModal');
        const modalEl = document.getElementById('specificationsModal');
        const initialJsonEl = document.getElementById('initialSpecificationsJson');

        if (!previewTableBody || !inputsContainer || !openModalBtn || !ramRowsContainer || !processorRowsContainer || !storageRowsContainer || !addRamRowBtn || !addProcessorRowBtn || !addStorageRowBtn || !saveModalBtn || !modalEl) return;

        function openModalFallback() { if (typeof window.jQuery !== 'undefined') window.jQuery('#specificationsModal').modal('show'); }
        function closeModal() {
            if (typeof bootstrap !== 'undefined' && bootstrap.Modal) bootstrap.Modal.getOrCreateInstance(modalEl).hide();
            else if (typeof window.jQuery !== 'undefined') window.jQuery('#specificationsModal').modal('hide');
        }

        function setSelectValue(select, value) {
            if (!select) return;
            const v = (value == null ? '' : String(value)).trim();
            if (!v) {
                select.value = '';
                return;
            }
            select.value = v;
            if (select.value === v) return;
            // case-insensitive match
            const opts = Array.from(select.options);
            const found = opts.find((o) => (o.value || o.textContent || '').trim().toLowerCase() === v.toLowerCase());
            if (found) {
                select.value = found.value;
                return;
            }
            const opt = document.createElement('option');
            opt.value = v;
            opt.textContent = v;
            select.appendChild(opt);
            select.value = v;
        }

        function appendRow(container, rowClass, value, brandId, condition) {
            const template = container.querySelector('.' + rowClass);
            const clone = template.cloneNode(true);
            clone.querySelectorAll('input').forEach((i) => i.value = (i.type === 'number' ? i.defaultValue || '0' : ''));
            setSelectValue(clone.querySelector('.spec-value'), value);
            setSelectValue(clone.querySelector('.spec-brand'), brandId);
            setSelectValue(clone.querySelector('.spec-condition'), condition);
            container.appendChild(clone);
        }

        function resetContainer(container) {
            const rows = container.querySelectorAll('.spec-row');
            rows.forEach((r, i) => { if (i > 0) r.remove(); });
            const first = container.querySelector('.spec-row');
            if (!first) return;
            first.querySelectorAll('input').forEach((i) => i.value = (i.type === 'number' ? i.defaultValue || '0' : ''));
            setSelectValue(first.querySelector('.spec-value'), '');
            setSelectValue(first.querySelector('.spec-brand'), '');
            setSelectValue(first.querySelector('.spec-condition'), '');
        }

        function getSpecsFromHiddenInputs() {
            const types = inputsContainer.querySelectorAll('input[name*="[type]"]');
            const values = inputsContainer.querySelectorAll('input[name*="[value]"]');
            const brands = inputsContainer.querySelectorAll('input[name*="[brand_id]"]');
            const conditions = inputsContainer.querySelectorAll('input[name*="[condition]"]');
            const out = [];
            for (let i = 0; i < Math.max(types.length, values.length); i++) {
                const t = types[i] ? String(types[i].value || '').trim() : '';
                const v = values[i] ? String(values[i].value || '').trim() : '';
                const b = brands[i] ? String(brands[i].value || '').trim() : '';
                const c = conditions[i] ? String(conditions[i].value || '').trim() : '';
                if (t && v) out.push({ type: t, value: v, brand_id: b, condition: c });
            }
            return out;
        }

        function getInitialSpecs() {
            const fromHidden = getSpecsFromHiddenInputs();
            if (fromHidden.length) return fromHidden;
            if (!initialJsonEl) return [];
            try {
                const parsed = JSON.parse(initialJsonEl.textContent || '[]');
                return Array.isArray(parsed) ? parsed.filter((s) => s && s.type && s.value) : [];
            } catch (e) {
                return [];
            }
        }

        function fillFirstOrAppend(container, rowClass, value, brandId, condition) {
            const first = container.querySelector('.spec-row');
            const firstValue = first ? first.querySelector('.spec-value') : null;
            if (firstValue && !String(firstValue.value || '').trim()) {
                setSelectValue(firstValue, value);
                setSelectValue(first.querySelector('.spec-brand'), brandId);
                setSelectValue(first.querySelector('.spec-condition'), condition);
            } else {
                appendRow(container, rowClass, value, brandId, condition);
            }
        }

        function buildEditRowsFromSpecs(specs) {
            resetContainer(ramRowsContainer);
            resetContainer(processorRowsContainer);
            resetContainer(storageRowsContainer);
            (specs || []).forEach((s) => {
                const t = String(s.type || '').trim();
                const v = String(s.value || '').trim();
                const b = s.brand_id != null ? String(s.brand_id) : '';
                const c = s.condition != null ? String(s.condition) : '';
                if (!t || !v) return;
                if (t === 'RAM') fillFirstOrAppend(ramRowsContainer, 'ram-row', v, b, c);
                else if (t === 'Processor') fillFirstOrAppend(processorRowsContainer, 'processor-row', v, b, c);
                else if (t === 'SSD' || t === 'HDD') fillFirstOrAppend(storageRowsContainer, 'storage-row', v, b, c);
            });
        }

        function collectSpecData() {
            const out = [];
            ramRowsContainer.querySelectorAll('.spec-row').forEach((r) => {
                const v = (r.querySelector('.spec-value')?.value || '').trim();
                const b = (r.querySelector('.spec-brand')?.value || '').trim();
                const c = (r.querySelector('.spec-condition')?.value || '').trim();
                if (v) out.push({ type: 'RAM', value: v, brand_id: b, condition: c });
            });
            processorRowsContainer.querySelectorAll('.spec-row').forEach((r) => {
                const v = (r.querySelector('.spec-value')?.value || '').trim();
                const b = (r.querySelector('.spec-brand')?.value || '').trim();
                const c = (r.querySelector('.spec-condition')?.value || '').trim();
                if (v) out.push({ type: 'Processor', value: v, brand_id: b, condition: c });
            });
            storageRowsContainer.querySelectorAll('.spec-row').forEach((r) => {
                const v = (r.querySelector('.spec-value')?.value || '').trim();
                const b = (r.querySelector('.spec-brand')?.value || '').trim();
                const c = (r.querySelector('.spec-condition')?.value || '').trim();
                if (v) out.push({ type: v.toUpperCase().includes('SSD') ? 'SSD' : 'HDD', value: v, brand_id: b, condition: c });
            });
            return out;
        }

        function renderPreviewAndHidden(specs) {
            previewTableBody.innerHTML = '';
            inputsContainer.innerHTML = '';
            if (!specs.length) {
                previewTableBody.innerHTML = '<tr><td colspan="2" class="text-muted">No specifications found.</td></tr>';
                return;
            }
            specs.forEach((s, i) => {
                const tr = document.createElement('tr');
                tr.innerHTML = '<td>' + s.type + '</td><td>' + s.value + '</td>';
                previewTableBody.appendChild(tr);
                const t = document.createElement('input'); t.type = 'hidden'; t.name = 'specifications[' + i + '][type]'; t.value = s.type;
                const v = document.createElement('input'); v.type = 'hidden'; v.name = 'specifications[' + i + '][value]'; v.value = s.value;
                const b = document.createElement('input'); b.type = 'hidden'; b.name = 'specifications[' + i + '][brand_id]'; b.value = s.brand_id || '';
                const c = document.createElement('input'); c.type = 'hidden'; c.name = 'specifications[' + i + '][condition]'; c.value = s.condition || '';
                inputsContainer.appendChild(t); inputsContainer.appendChild(v); inputsContainer.appendChild(b); inputsContainer.appendChild(c);
            });
            if (initialJsonEl) initialJsonEl.textContent = JSON.stringify(specs);
        }

        openModalBtn.addEventListener('click', function () {
            buildEditRowsFromSpecs(getInitialSpecs());
            if (typeof bootstrap === 'undefined' || !bootstrap.Modal) openModalFallback();
        });
        modalEl.addEventListener('show.bs.modal', function () {
            buildEditRowsFromSpecs(getInitialSpecs());
        });

        addRamRowBtn.addEventListener('click', function () { appendRow(ramRowsContainer, 'ram-row', '', '', ''); });
        addProcessorRowBtn.addEventListener('click', function () { appendRow(processorRowsContainer, 'processor-row', '', '', ''); });
        addStorageRowBtn.addEventListener('click', function () { appendRow(storageRowsContainer, 'storage-row', '', '', ''); });
        [ramRowsContainer, processorRowsContainer, storageRowsContainer].forEach((c) => c.addEventListener('click', function (e) {
            const btn = e.target.closest('.spec-remove-btn'); if (!btn) return;
            const rows = c.querySelectorAll('.spec-row');
            if (rows.length <= 1) {
                setSelectValue(rows[0]?.querySelector('.spec-value'), '');
                setSelectValue(rows[0]?.querySelector('.spec-brand'), '');
                setSelectValue(rows[0]?.querySelector('.spec-condition'), '');
                return;
            }
            btn.closest('.spec-row').remove();
        }));
        saveModalBtn.addEventListener('click', function () { renderPreviewAndHidden(collectSpecData()); closeModal(); });
    })();
</script>
@endsection
