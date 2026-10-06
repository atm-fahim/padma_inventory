@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="{{ asset('frontend/css/item-inventory.css') }}">
<div class="container-fluid stock-out-page">
    <div class="stock-out-hero mb-3">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div>
                <h4 class="mb-1"><i class="bx bx-export me-2"></i>Stock Out</h4>
                <p class="mb-0 small opacity-90">Source branch থেকে item আউট করুন এবং destination branch / receiver-এ পাঠান</p>
            </div>
            <div class="stock-out-issuer">
                <span class="issuer-label">Issued by</span>
                <strong>{{ auth()->user()->name ?? 'N/A' }}</strong>
                @if(!empty(auth()->user()->employee_id))
                    <span class="issuer-id">({{ auth()->user()->employee_id }})</span>
                @endif
            </div>
        </div>
    </div>

    <form id="stockOutForm" method="POST" action="{{ route('stock-out-save-update') }}">
        @csrf

        <div class="row g-3">
            {{-- Source --}}
            <div class="col-lg-6">
                <div class="stock-out-card h-100">
                    <div class="stock-out-card-head head-source">
                        <i class="bx bx-git-branch me-1"></i> Source Branch <span class="badge-req">Step 1</span>
                    </div>
                    <div class="stock-out-card-body">
                        <p class="hint-text">কোন শাখার ইনভেন্টরি থেকে আউট করবেন। Item / Unique No এই শাখার উপর ভিত্তি করে load হবে।</p>
                        <label class="form-label">From branch <span class="text-danger">*</span></label>
                        <select id="from_branch_id" name="from_branch_id" class="form-select stock-out-select" required>
                            <option value="">Select source branch</option>
                            @foreach($branchData as $branch)
                                <option value="{{ $branch->hr_branch_id }}"
                                    {{ (string) old('from_branch_id', $defaultFromBranchId ?? '') === (string) $branch->hr_branch_id ? 'selected' : '' }}>
                                    {{ $branch->branch_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            {{-- Receiver --}}
            <div class="col-lg-6">
                <div class="stock-out-card h-100">
                    <div class="stock-out-card-head head-receiver">
                        <i class="bx bx-user me-1"></i> Receiver / Destination <span class="badge-req">Step 1</span>
                    </div>
                    <div class="stock-out-card-body">
                        <div class="row g-2">
                            <div class="col-md-6">
                                <label class="form-label">Employee ID <span class="text-muted">(optional)</span></label>
                                <input type="text" id="employee_id" name="employee_id" class="form-control"
                                       placeholder="Enter Employee ID" onchange="fetchEmployeeInfo(this.value)" value="{{ old('employee_id') }}">
                                <div id="employee_id_error" class="text-danger small mt-1" style="display: none;"></div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Employee Name</label>
                                <input type="text" id="employee_name" class="form-control" placeholder="Auto filled" readonly>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Destination branch (To) <span class="text-danger">*</span></label>
                                <select id="branch_id" name="branch_id" class="form-select stock-out-select">
                                    <option value="">Select destination branch</option>
                                    @foreach($branchData as $branch)
                                        <option value="{{$branch->hr_branch_id}}"
                                                {{$branch->hr_branch_id == old('branch_id') ? 'selected' : ''}}>
                                            {{$branch->branch_name}}
                                        </option>
                                    @endforeach
                                </select>
                                <small class="text-muted">Employee ID দিলে auto fill হতে পারে; না হলে manually select করুন।</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Add Items --}}
            <div class="col-12">
                <div class="stock-out-card">
                    <div class="stock-out-card-head head-items">
                        <i class="bx bx-box me-1"></i> Add Items
                        <span class="badge-req">Step 2</span>
                    </div>
                    <div class="stock-out-card-body">
                        <div class="stock-out-steps mb-3">
                            <span class="step-chip"><b>1</b> Item / Unique No</span>
                            <span class="step-chip"><b>2</b> Preview list</span>
                            <span class="step-chip"><b>3</b> Add Item</span>
                        </div>

                        <div class="stock-out-filters">
                            <div class="row g-3">
                                <div class="col-lg-5 col-md-6">
                                    <div class="so-field">
                                        <label class="form-label" for="item_select">Select Item <span class="text-danger">*</span></label>
                                        <select id="item_select" class="form-select stock-out-select">
                                            <option value="">Select Item</option>
                                            @foreach($itemsData as $item)
                                                <option value="{{ $item->id }}"
                                                    data-category-id="{{ $item->category_id ?? '' }}"
                                                    data-category="{{ $item->category_title ?? '' }}">
                                                    {{ $item->item_title ?? $item->item ?? ('Item #'.$item->id) }}
                                                    @if(!empty($item->category_title))
                                                        ({{ $item->category_title }})
                                                    @endif
                                                </option>
                                            @endforeach
                                        </select>
                                        <div class="so-field-meta">&nbsp;</div>
                                    </div>
                                </div>
                                <div class="col-lg-2 col-md-6">
                                    <div class="so-field">
                                        <label class="form-label" for="item_quantity">Quantity <span class="text-danger">*</span></label>
                                        <input type="number" id="item_quantity" class="form-control" placeholder="Qty" min="1" value="1" required>
                                        <div class="so-field-meta">&nbsp;</div>
                                    </div>
                                </div>
                                <div class="col-lg-5 col-md-12">
                                    <div class="so-field">
                                        <label class="form-label" for="set_uniq_no">
                                            Unique No
                                            <span id="uniqNoLoader" class="stock-out-inline-loader" style="display:none;" aria-hidden="true">
                                                <span class="spinner-border spinner-border-sm text-theme" role="status"></span>
                                            </span>
                                        </label>
                                        <div class="stock-out-uniq-wrap">
                                            <select id="set_uniq_no" class="form-select stock-out-select">
                                                <option value=""></option>
                                                @foreach($setUniqNoList as $setUniqNo)
                                                    <option value="{{ $setUniqNo->set_uniq_no }}"
                                                        data-item-title="{{ $setUniqNo->item_title ?? '' }}"
                                                        data-category="{{ $setUniqNo->category_title ?? '' }}"
                                                        data-brand="{{ $setUniqNo->brand_name ?? '' }}">
                                                        {{ $setUniqNo->set_uniq_no }}
                                                        @if(!empty($setUniqNo->item_title)) — {{ $setUniqNo->item_title }}@endif
                                                        @if(!empty($setUniqNo->category_title)) | {{ $setUniqNo->category_title }}@endif
                                                        @if(!empty($setUniqNo->brand_name)) | {{ $setUniqNo->brand_name }}@endif
                                                    </option>
                                                @endforeach
                                            </select>
                                            <div id="uniqNoSelectOverlay" class="stock-out-select-overlay" style="display:none;">
                                                <span class="spinner-border spinner-border-sm text-theme" role="status"></span>
                                                <span class="ms-2 stock-out-overlay-text">Loading…</span>
                                            </div>
                                        </div>
                                        <div class="so-field-meta">
                                            <small id="uniqNoStatus" class="stock-out-uniq-status text-muted">Item বা branch select করলে Unique No load হবে</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="so-preview-head d-flex flex-wrap justify-content-between align-items-center gap-2 mt-1 mb-2">
                            <div class="fw-semibold text-secondary"><i class="bx bx-list-ul me-1"></i>Preview list</div>
                            <small class="text-muted">Unique No select করলে এখানে items দেখাবে</small>
                        </div>

                        <div class="table-responsive stock-out-table-wrap position-relative">
                            <div id="uniqItemsTableLoader" class="stock-out-table-loader" style="display:none;">
                                <span class="spinner-border text-theme" role="status"></span>
                                <span class="ms-2 fw-semibold">Items loading…</span>
                            </div>
                            <table class="table table-bordered table-hover stock-out-table mb-0" id="itemsUniqueNoTable">
                                <thead>
                                    <tr>
                                        <th style="width:48px;">SL</th>
                                        <th>Item</th>
                                        <th>Category</th>
                                        <th>Brand</th>
                                        <th>Unique No</th>
                                        <th style="width:60px;">Qty</th>
                                        <th style="width:120px;">Condition</th>
                                        <th>Description</th>
                                        <th style="width:100px;">Action</th>
                                    </tr>
                                </thead>
                                <tbody id="itemsUniqueNoTableBody">
                                    <tr class="so-empty-row">
                                        <td colspan="9" class="text-center text-muted py-4">
                                            এখনো কোনো item নেই — Unique No select করুন
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="stock-out-add-bar">
                            <div class="stock-out-add-hint">
                                <i class="bx bx-info-circle me-1"></i>
                                Preview list চেক করে নিচের বাটনে <strong>Add Item</strong> চাপুন
                            </div>
                            <button type="button" id="addItemBtn" class="btn btn-theme px-4">
                                <i class="bx bx-plus-circle me-1"></i> Add Item
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Selected Items --}}
            <div class="col-12">
                <div class="stock-out-card">
                    <div class="stock-out-card-head head-selected">
                        <i class="bx bx-list-check me-1"></i> Selected Items
                        <span class="badge-req">Step 3</span>
                    </div>
                    <div class="stock-out-card-body">
                        <div class="table-responsive stock-out-table-wrap">
                            <table class="table table-bordered table-hover stock-out-table mb-0" id="itemsTable">
                                <thead>
                                    <tr>
                                        <th style="width:48px;">SL</th>
                                        <th>Item</th>
                                        <th>Category</th>
                                        <th>Brand</th>
                                        <th>Unique No</th>
                                        <th style="width:60px;">Qty</th>
                                        <th style="width:120px;">Condition</th>
                                        <th>Description</th>
                                        <th>Receiver ID</th>
                                        <th>Receiver name</th>
                                        <th style="width:100px;">Action</th>
                                    </tr>
                                </thead>
                                <tbody id="itemsTableBody">
                                    <tr class="so-empty-row">
                                        <td colspan="11" class="text-center text-muted py-4">
                                            Add Item করলে selected items এখানে দেখাবে
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Note + actions --}}
            <div class="col-12">
                <div class="stock-out-card">
                    <div class="stock-out-card-head head-source">
                        <i class="bx bx-check-shield me-1"></i> Submit
                        <span class="badge-req">Final</span>
                    </div>
                    <div class="stock-out-card-body">
                        <label class="form-label" for="global_note"><i class="bx bx-note me-1"></i>Note <span class="text-muted fw-normal">(optional)</span></label>
                        <textarea id="global_note" name="global_note" class="form-control" rows="2"
                                  placeholder="এই stock out invoice এর জন্য note লিখুন">{{ old('global_note') }}</textarea>
                        <small class="text-muted d-block mt-1">এই note invoice-এ show হবে।</small>

                        <div class="stock-out-submit-bar">
                            <a href="{{ route('stock-out') }}" class="btn btn-outline-secondary">
                                <i class="bx bx-refresh me-1"></i> Reset
                            </a>
                            <button type="submit" class="btn btn-theme btn-lg px-4">
                                <i class="bx bx-check-circle me-1"></i> Submit Stock Out
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
// Wait for jQuery to be loaded
(function() {
    'use strict';
    
    // Global functions for employee lookup - accessible from inline handlers
    window.fetchEmployeeInfo = function(employeeId) {
        // Ensure jQuery is available
        if (typeof jQuery === 'undefined') {
            console.error('jQuery is not loaded');
            return;
        }
        var $ = jQuery;
        
        // Trim the employee ID
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
                    // Set branch_id value and trigger select2 update
                    $('#branch_id').val(response.data.branch_id).trigger('change');
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
        $('#branch_id').val('');
        $('#branch_name').val('');
    };

    window.clearEmployeeInfo = function() {
        if (typeof jQuery === 'undefined') return;
        var $ = jQuery;
        $('#employee_name').val('');
        $('#branch_id').val('');
        $('#branch_name').val('');
        $('#employee_id_error').hide();
    };

    // Global function for item selection - accessible from inline handlers
    // window.fetchInventoryByItemId = function(itemId) {
    //     if (typeof jQuery === 'undefined') return;
    //     var $ = jQuery;
    //     // itemId is already passed as parameter from inline handler
    //     if (!itemId) {
    //         return;
    //     }
    //     // Clear set_uniq_no when item is selected
    //     $('#set_uniq_no').val(null).trigger('change');
    //     // You can add additional logic here if needed
    // };
})();

function escapeHtml(str) {
    return String(str == null ? '' : str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
}

function buildUniqNoOptionLabel(su, title, category, brand) {
    var parts = [su || ''];
    if (title) parts.push(title);
    var meta = [];
    if (category) meta.push(category);
    if (brand) meta.push(brand);
    var label = parts.filter(Boolean).join(' — ');
    if (meta.length) label += ' | ' + meta.join(' | ');
    return label;
}

function previewEmptyHtml() {
    return '<tr class="so-empty-row"><td colspan="9" class="text-center text-muted py-4">এখনো কোনো item নেই — Unique No select করুন</td></tr>';
}

function selectedEmptyHtml() {
    return '<tr class="so-empty-row"><td colspan="11" class="text-center text-muted py-4">Add Item করলে selected items এখানে দেখাবে</td></tr>';
}

function clearPreviewTable() {
    if (typeof jQuery === 'undefined') return;
    jQuery('#itemsUniqueNoTableBody').html(previewEmptyHtml());
}

function clearSelectedTableIfEmpty() {
    if (typeof jQuery === 'undefined') return;
    var $ = jQuery;
    if ($('#itemsTableBody tr').not('.so-empty-row').length === 0) {
        $('#itemsTableBody').html(selectedEmptyHtml());
    }
}

function getPreviewRows() {
    return jQuery('#itemsUniqueNoTableBody tr').not('.so-empty-row');
}

function getSelectedRows() {
    return jQuery('#itemsTableBody tr').not('.so-empty-row');
}

function setUniqNoLoading(isLoading, message) {
    if (typeof jQuery === 'undefined') return;
    var $ = jQuery;
    var $loader = $('#uniqNoLoader');
    var $overlay = $('#uniqNoSelectOverlay');
    var $status = $('#uniqNoStatus');
    var $select = $('#set_uniq_no');
    var msg = message || 'Unique No loading…';

    if (isLoading) {
        $loader.show();
        $overlay.find('span:not(.spinner-border)').first().text(msg);
        // Keep last word short on overlay
        $overlay.find('.stock-out-overlay-text').text(msg);
        $overlay.show();
        $status.removeClass('text-success text-danger text-muted').addClass('text-theme').text(msg);
        $select.prop('disabled', true);
        if ($select.hasClass('select2-hidden-accessible')) {
            $select.trigger('change.select2');
        }
    } else {
        $loader.hide();
        $overlay.hide();
        $select.prop('disabled', false);
        if ($select.hasClass('select2-hidden-accessible')) {
            $select.trigger('change.select2');
        }
        if (message) {
            var cls = (message.indexOf('Error') === 0 || message.indexOf('No ') === 0) ? 'text-danger' : 'text-success';
            $status.removeClass('text-success text-danger text-muted text-theme').addClass(cls).text(message);
        } else {
            $status.removeClass('text-success text-danger text-theme').addClass('text-muted').text('Item বা branch select করলে Unique No load হবে');
        }
    }
}

function setUniqItemsTableLoading(isLoading) {
    if (typeof jQuery === 'undefined') return;
    var $ = jQuery;
    if (isLoading) {
        $('#uniqItemsTableLoader').show();
    } else {
        $('#uniqItemsTableLoader').hide();
    }
}

/** Rebuild #set_uniq_no options; Select2 must be destroyed before .html() or the UI stays stuck on the placeholder. */
function setUniqNoSelectOptions(html, clearSelection) {
    if (typeof jQuery === 'undefined' || typeof jQuery.fn.select2 === 'undefined') {
        return;
    }
    var $el = jQuery('#set_uniq_no');
    if ($el.hasClass('select2-hidden-accessible')) {
        try { $el.select2('destroy'); } catch (e) {}
    }
    $el.next('.select2').remove();
    $el.removeClass('select2-hidden-accessible').removeAttr('data-select2-id').removeAttr('aria-hidden').show();
    $el.find('option').removeAttr('data-select2-id');
    $el.prop('disabled', false);
    $el.html(html);
    $el.select2({
        theme: 'bootstrap4',
        placeholder: 'Select Unique No',
        allowClear: true,
        width: '100%'
    });
    if (clearSelection !== false) {
        // Use select2-only change so stock-out handlers do not race/clear the preview table
        $el.val(null).trigger('change.select2');
    }
}

function reloadSetUniqNoForFromBranch() {
    if (typeof jQuery === 'undefined') {
        return;
    }
    var $ = jQuery;
    var bid = $('#from_branch_id').val();
    if (!bid) {
        setUniqNoSelectOptions('<option value=""></option>', true);
        clearPreviewTable();
        setUniqNoLoading(false, 'আগে Source branch select করুন');
        return;
    }
    setUniqNoLoading(true, 'Branch অনুযায়ী Unique No loading…');
    $.ajax({
        url: '{{ route("stock-out-branch-set-uniq-list") }}',
        method: 'POST',
        data: {
            _token: '{{ csrf_token() }}',
            branch_id: bid
        },
        success: function(resp) {
            if (!resp.success || !resp.data || !resp.data.length) {
                setUniqNoSelectOptions('<option value=""></option>', true);
                clearPreviewTable();
                setUniqNoLoading(false, 'No Unique No found for this branch');
                return;
            }
            var html = '<option value=""></option>';
            for (var i = 0; i < resp.data.length; i++) {
                var row = resp.data[i];
                var su = row.set_uniq_no || '';
                var title = row.item_title || '';
                var cat = row.category_title || '';
                var brand = row.brand_name || '';
                var label = buildUniqNoOptionLabel(su, title, cat, brand);
                html += '<option value="' + escapeHtml(su) + '"'
                    + ' data-item-title="' + escapeHtml(title) + '"'
                    + ' data-category="' + escapeHtml(cat) + '"'
                    + ' data-brand="' + escapeHtml(brand) + '">'
                    + escapeHtml(label) + '</option>';
            }
            setUniqNoSelectOptions(html, true);
            clearPreviewTable();
            $('#item_select').val(null).trigger('change.select2');
            setUniqNoLoading(false, resp.data.length + ' Unique No loaded — এখন select করুন');
        },
        error: function() {
            setUniqNoSelectOptions('<option value=""></option>', true);
            clearPreviewTable();
            setUniqNoLoading(false, 'Error loading Unique No');
        }
    });
}

function fetchInventoryByItemId(itemId) {
    if (typeof jQuery === 'undefined') {
        return;
    }
    var $ = jQuery;
    if (!itemId) {
        return;
    }
    if (!$('#from_branch_id').val()) {
        alert('Please select source branch (stock from) first.');
        return;
    }

    setUniqNoLoading(true, 'Item অনুযায়ী Unique No loading…');
    $.ajax({
        url: '{{ route("get-inventory-by-item-id") }}',
        method: 'POST',
        data: {
            _token: '{{ csrf_token() }}',
            item_id: itemId,
            from_branch_id: $('#from_branch_id').val() || ''
        },
        success: function(response) {
            if (response.success && response.data && response.data.length > 0) {
                var uniqueSetUniqNos = {};
                response.data.forEach(function(item) {
                    if (item.set_uniq_no && String(item.set_uniq_no).trim() !== '') {
                        if (!uniqueSetUniqNos[item.set_uniq_no]) {
                            uniqueSetUniqNos[item.set_uniq_no] = {
                                title: item.item_title || '',
                                category: item.category_title || '',
                                brand: item.brand_name || ''
                            };
                        }
                    }
                });

                var keys = Object.keys(uniqueSetUniqNos);
                if (keys.length > 0) {
                    var html = '<option value=""></option>';
                    keys.forEach(function(setUniqNo) {
                        var meta = uniqueSetUniqNos[setUniqNo];
                        var label = buildUniqNoOptionLabel(setUniqNo, meta.title, meta.category, meta.brand);
                        html += '<option value="' + escapeHtml(setUniqNo) + '"'
                            + ' data-item-title="' + escapeHtml(meta.title) + '"'
                            + ' data-category="' + escapeHtml(meta.category) + '"'
                            + ' data-brand="' + escapeHtml(meta.brand) + '">'
                            + escapeHtml(label) + '</option>';
                    });
                    setUniqNoSelectOptions(html, true);
                    clearPreviewTable();
                    setUniqNoLoading(false, keys.length + ' Unique No loaded — এখন select করুন');
                } else {
                    setUniqNoSelectOptions('<option value=""></option>', true);
                    clearPreviewTable();
                    setUniqNoLoading(false, 'No Unique No found for this item');
                    alert('No Unique No found for this item in the selected source branch.');
                }
            } else {
                setUniqNoSelectOptions('<option value=""></option>', true);
                clearPreviewTable();
                setUniqNoLoading(false, 'No Unique No found for this item');
                alert(response.message || 'No inventory found for this item');
            }
        },
        error: function(xhr) {
            setUniqNoLoading(false, 'Error loading Unique No');
            var errorMsg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Error fetching inventory';
            alert(errorMsg);
            console.log(xhr);
        }
    });
}

function getItemsBySetUniqNo(setUniqNo) {
    if (typeof jQuery === 'undefined') {
        return;
    }
    var $ = jQuery;
    if (!setUniqNo) {
        return;
    }
    if (!$('#from_branch_id').val()) {
        alert('Please select source branch (stock from) first.');
        return;
    }
    setUniqItemsTableLoading(true);
    setUniqNoLoading(true, 'Selected Unique No items loading…');
    $.ajax({
        url: '{{ route("get-items-by-set-uniq-no") }}',
        method: 'POST',
        data: {
            _token: '{{ csrf_token() }}',
            set_uniq_no: setUniqNo,
            from_branch_id: $('#from_branch_id').val() || ''
        },
        success: function(response) {
            setUniqItemsTableLoading(false);
            if (response.success && response.data && response.data.length > 0) {
                var data = response.data;
                // Parent = Laptop, Desktop, Server (main device); rest = sub-items (RAM, HDD, SSD, Processor, etc.)
                var parentKeywords = ['laptop', 'desktop', 'server', 'pc', 'computer'];
                var parents = [];
                var children = [];
                for (var i = 0; i < data.length; i++) {
                    var title = (data[i].item_title || '').toLowerCase();
                    var isParent = parentKeywords.some(function(k) { return title.indexOf(k) !== -1; });
                    if (isParent) {
                        parents.push(data[i]);
                    } else {
                        children.push(data[i]);
                    }
                }
                if (parents.length === 0 && data.length > 0) {
                    parents = [data[0]];
                    children = data.slice(1);
                }

                // Sync Item select to main/parent item when Unique No is chosen
                var mainItem = parents[0] || data[0];
                if (mainItem && mainItem.item_id) {
                    window._stockOutSyncingItem = true;
                    $('#item_select').val(String(mainItem.item_id)).trigger('change.select2');
                    window._stockOutSyncingItem = false;
                }

                function conditionOptions(selected) {
                    var opts = [
                        { v: '', t: 'Select Condition' },
                        { v: 'slow', t: 'Slow' },
                        { v: 'good', t: 'Good' },
                        { v: 'bad', t: 'Bad' },
                        { v: 'damage', t: 'Damage' },
                        { v: 'normal', t: 'Normal' },
                        { v: 'unused', t: 'Unused' }
                    ];
                    var sel = (selected || 'good').toLowerCase();
                    var html = '';
                    for (var i = 0; i < opts.length; i++) {
                        html += '<option value="' + opts[i].v + '"' + (opts[i].v === sel ? ' selected' : '') + '>' + opts[i].t + '</option>';
                    }
                    return html;
                }

                function buildRow(item, sl, isSubRow) {
                    var processor = item.processor || '';
                    var ram = item.ram || '';
                    var storage = item.storage || '';
                    var specsHtml = '';
                    if (processor || ram || storage) {
                        specsHtml = '<div class="small text-muted mt-1">';
                        if (processor) specsHtml += '<div><strong>Processor:</strong> ' + escapeHtml(processor) + '</div>';
                        if (ram) specsHtml += '<div><strong>RAM:</strong> ' + escapeHtml(ram) + '</div>';
                        if (storage) specsHtml += '<div><strong>Storage:</strong> ' + escapeHtml(storage) + '</div>';
                        specsHtml += '</div>';
                    }
                    var rowClass = isSubRow ? 'sub-item-row' : 'parent-item-row';
                    var indent = isSubRow ? ' style="padding-left: 2rem;" title="Under main device"' : '';
                    var label = isSubRow ? '<span class="text-muted small">↳ </span>' : '';
                    var invId = item.id || item.item_inventory_id || '';
                    var qty = item.quantity || 1;
                    var cond = item.condition || 'good';
                    var desc = item.description || item.remarks || '';

                    var tr = '<tr class="' + rowClass + '">';
                    tr += '<td' + indent + '>';
                    tr += '<input type="hidden" name="item_inventory_id[]" value="' + escapeHtml(invId) + '">';
                    tr += '<input type="hidden" name="item_id[]" value="' + escapeHtml(item.item_id || '') + '">';
                    tr += '<input type="hidden" name="category_id[]" value="' + escapeHtml(item.category_id || '') + '">';
                    tr += '<input type="hidden" name="brand_id[]" value="' + escapeHtml(item.brand_id || '') + '">';
                    tr += '<input type="hidden" name="inventory_id[]" value="' + escapeHtml(invId) + '">';
                    tr += '<input type="hidden" name="set_uniq_no[]" value="' + escapeHtml(item.set_uniq_no || '') + '">';
                    tr += '<input type="hidden" name="quantity[]" value="' + escapeHtml(qty) + '">';
                    tr += label + sl + '</td>';
                    tr += '<td><div><strong>' + escapeHtml(item.item_title || 'N/A') + '</strong></div>' + specsHtml + '</td>';
                    tr += '<td>' + escapeHtml(item.category_title || 'N/A') + '</td>';
                    tr += '<td>' + escapeHtml(item.brand_name || 'N/A') + '</td>';
                    tr += '<td>' + escapeHtml(item.set_uniq_no || '') + '</td>';
                    tr += '<td>' + escapeHtml(qty) + '</td>';
                    tr += '<td><select name="condition[]" class="form-select form-select-sm">' + conditionOptions(cond) + '</select></td>';
                    tr += '<td><input type="text" name="description[]" value="' + escapeHtml(desc) + '" class="form-control" placeholder="Enter Description"></td>';
                    tr += '<td><button type="button" class="btn btn-danger btn-sm remove-preview-item"><i class="bx bx-trash me-1"></i> Remove</button></td>';
                    tr += '</tr>';
                    return tr;
                }

                var html = '';
                var sl = 1;
                for (var p = 0; p < parents.length; p++) {
                    html += buildRow(parents[p], sl++, false);
                }
                for (var c = 0; c < children.length; c++) {
                    html += buildRow(children[c], sl++, true);
                }
                $('#itemsUniqueNoTableBody').html(html);
                setUniqNoLoading(false, data.length + ' item(s) ready — নিচে Add Item চাপুন');
            } else {
                clearPreviewTable();
                setUniqNoLoading(false, 'No items found for this Unique No');
                alert(response.message || 'No items found for this Unique No');
            }
        },
        error: function(xhr) {
            setUniqItemsTableLoading(false);
            clearPreviewTable();
            setUniqNoLoading(false, 'Error loading items');
            var errorMsg = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Error fetching items';
            alert(errorMsg);
            console.log(xhr);
        }
    });
}

 // Function to remove item from table
 function removeItem(button) {
     if (typeof jQuery === 'undefined') return;
     var $ = jQuery;
     $(button).closest('tr').remove();

     var rows = getPreviewRows();
     if (rows.length === 0) {
         clearPreviewTable();
         return;
     }
     rows.each(function(index) {
         var $td = $(this).find('td:first');
         var isSub = $(this).hasClass('sub-item-row');
         var label = isSub ? '\u21B3 ' : '';
         var inputs = $td.find('input').clone(true);
         $td.empty().append(inputs).append(document.createTextNode(label + (index + 1)));
     });
 }

 // Function to remove item from selected items table
 function removeSelectedItem(button) {
     if (typeof jQuery === 'undefined') return;
     var $ = jQuery;
     $(button).closest('tr').remove();

     var rows = getSelectedRows();
     if (rows.length === 0) {
         clearSelectedTableIfEmpty();
         return;
     }
     rows.each(function(index) {
         $(this).find('td:first').text(index + 1);
     });
 }

 // Function to handle Add Item button click
 function handleAddItemClick() {
     if (typeof jQuery === 'undefined') {
         console.error('jQuery is not loaded');
         alert('Error: jQuery is not loaded. Please refresh the page.');
         return false;
     }
     var $ = jQuery;

     if (handleAddItemClick._busy) {
         return false;
     }
     handleAddItemClick._busy = true;

     try {
         const employeeId = $('#employee_id').val() || '';
         const employeeName = $('#employee_name').val() || '';
         const branchId = $('#branch_id').val() || '';
         const fromBranchId = $('#from_branch_id').val() || '';
         const selectedUniq = $('#set_uniq_no').val() || '';

         if (!fromBranchId || String(fromBranchId).trim() === '') {
             alert('Please select source branch (stock from) first.');
             $('#from_branch_id').focus();
             return false;
         }

         if (!branchId || String(branchId).trim() === '') {
             alert('Please select Destination branch (To), or enter Employee ID so branch auto-fills.');
             $('#branch_id').focus();
             return false;
         }

         const rows = getPreviewRows();

         if (rows.length === 0) {
             if (selectedUniq) {
                 alert('Unique No selected, but item rows are not loaded yet. Wait a moment or re-select the Unique No.');
             } else {
                 alert('Please select a Unique No first so items load in the preview table.');
             }
             return false;
         }

         // Remove empty placeholder from selected table before appending
         $('#itemsTableBody tr.so-empty-row').remove();
         const currentRowCount = getSelectedRows().length;

         rows.each(function(index) {
             const clonedRow = $(this).clone(true, true);

             var $firstTd = clonedRow.find('td:first');
             var inputs = $firstTd.find('input[type="hidden"]').clone(true);
             var isSub = clonedRow.hasClass('sub-item-row');
             var label = isSub ? '\u21B3 ' : '';
             $firstTd.empty().append(inputs).append(document.createTextNode(label + (currentRowCount + index + 1)));

             let empIdInput = clonedRow.find('input[name="to_employee_id[]"]');
             if (empIdInput.length === 0) {
                 empIdInput = $('<input>').attr({ type: 'hidden', name: 'to_employee_id[]' });
                 $firstTd.append(empIdInput);
             }
             empIdInput.val(employeeId);

             let branchIdInput = clonedRow.find('input[name="to_branch_id[]"]');
             if (branchIdInput.length === 0) {
                 branchIdInput = $('<input>').attr({ type: 'hidden', name: 'to_branch_id[]' });
                 $firstTd.append(branchIdInput);
             }
             branchIdInput.val(branchId);

             const actionCell = clonedRow.find('td').last();
             clonedRow.find('td.receiver-id-display, td.receiver-name-display').remove();
             actionCell.before($('<td></td>').addClass('small receiver-id-display').text(employeeId || '—'));
             actionCell.before($('<td></td>').addClass('small receiver-name-display').text(employeeName || '—'));

             const removeBtn = clonedRow.find('button');
             if (removeBtn.length > 0) {
                 removeBtn
                     .removeClass('remove-preview-item')
                     .addClass('remove-selected-item')
                     .attr('onclick', null)
                     .off('click');
             }

             $('#itemsTableBody').append(clonedRow);
         });

         clearPreviewTable();
         $('#item_select').val(null).trigger('change.select2');
         $('#item_quantity').val(1);
         // Restore full Unique No list for current source branch
         if (fromBranchId) {
             reloadSetUniqNoForFromBranch();
         } else {
             $('#set_uniq_no').val(null).trigger('change.select2');
         }

         return false;
     } finally {
         setTimeout(function () {
             handleAddItemClick._busy = false;
         }, 300);
     }
 }

 // Wait until layout jQuery + Select2 are fully loaded (fixes first-visit item select not loading)
 window.addEventListener('load', function () {
     if (typeof jQuery === 'undefined' || typeof jQuery.fn.select2 === 'undefined') {
         return;
     }
     var $ = jQuery;

     function initStockOutSelect2($el, placeholder) {
         if ($el.hasClass('select2-hidden-accessible')) {
             try { $el.select2('destroy'); } catch (e) {}
         }
         $el.next('.select2').remove();
         $el.removeClass('select2-hidden-accessible')
             .removeAttr('data-select2-id')
             .removeAttr('aria-hidden')
             .show();
         $el.find('option').removeAttr('data-select2-id');
         $el.select2({
             theme: 'bootstrap4',
             placeholder: placeholder,
             allowClear: true,
             width: '100%'
         });
     }

     initStockOutSelect2($('#item_select'), 'Select Item');
     initStockOutSelect2($('#set_uniq_no'), 'Select Unique No');
     initStockOutSelect2($('#branch_id'), 'Select destination branch');
     initStockOutSelect2($('#from_branch_id'), 'Select source branch');

     // Delegated handlers — work even after Select2 destroy/reinit
     $(document).off('change.stockOutItem', '#item_select')
         .on('change.stockOutItem', '#item_select', function () {
             if (window._stockOutSyncingItem) {
                 return;
             }
             var itemId = $(this).val();
             if (itemId) {
                 fetchInventoryByItemId(itemId);
             } else {
                 // Keep branch-wise Unique No list; only clear preview
                 clearPreviewTable();
             }
         });

     $(document).off('change.setUniqStockOut', '#set_uniq_no')
         .on('change.setUniqStockOut', '#set_uniq_no', function () {
             var setUniqNo = $(this).val();
             if (setUniqNo) {
                 getItemsBySetUniqNo(setUniqNo);
             } else {
                 clearPreviewTable();
             }
         });

     $(document).off('change.stockOutFromBranch', '#from_branch_id')
         .on('change.stockOutFromBranch', '#from_branch_id', function () {
             reloadSetUniqNoForFromBranch();
         });

     $(document).off('click.stockOutRemovePreview', '.remove-preview-item')
         .on('click.stockOutRemovePreview', '.remove-preview-item', function () {
             removeItem(this);
         });

     $(document).off('click.stockOutRemoveSelected', '.remove-selected-item')
         .on('click.stockOutRemoveSelected', '.remove-selected-item', function () {
             removeSelectedItem(this);
         });

     $('#addItemBtn').off('click.stockOutAdd').on('click.stockOutAdd', function (e) {
         e.preventDefault();
         e.stopPropagation();
         handleAddItemClick();
         return false;
     });

     // Prefill Unique No list for default source branch
     if ($('#from_branch_id').val()) {
         reloadSetUniqNoForFromBranch();
     }
 });
 </script>

<style>
.stock-out-page {
    --so-theme: #df238e;
    --so-theme-dark: #c01b78;
    --so-soft: rgba(223, 35, 142, 0.12);
    --so-border: #f0d0e3;
    --so-input-h: 40px;
    padding-bottom: 1.5rem;
}
.stock-out-hero {
    background: linear-gradient(135deg, #df238e 0%, #c01b78 55%, #9b1560 100%);
    color: #fff;
    border-radius: 14px;
    padding: 1.1rem 1.35rem;
    box-shadow: 0 10px 24px rgba(223, 35, 142, 0.22);
}
.stock-out-hero h4 { font-weight: 700; margin: 0; }
.stock-out-issuer {
    background: rgba(255,255,255,0.16);
    border: 1px solid rgba(255,255,255,0.28);
    border-radius: 10px;
    padding: 0.55rem 0.85rem;
    font-size: 0.9rem;
}
.stock-out-issuer .issuer-label {
    display: block;
    font-size: 0.7rem;
    text-transform: uppercase;
    opacity: 0.85;
    letter-spacing: 0.04em;
}
.stock-out-issuer .issuer-id { opacity: 0.85; margin-left: 0.25rem; }

.stock-out-card {
    background: #fff;
    border: 1px solid var(--so-border);
    border-radius: 14px;
    overflow: hidden;
    box-shadow: 0 6px 18px rgba(15, 23, 42, 0.05);
}
.stock-out-card-head {
    padding: 0.75rem 1rem;
    font-weight: 700;
    font-size: 0.95rem;
    color: #fff;
    display: flex;
    align-items: center;
    gap: 0.35rem;
}
.stock-out-card-head .badge-req {
    margin-left: auto;
    background: rgba(255,255,255,0.22);
    border-radius: 999px;
    font-size: 0.68rem;
    font-weight: 600;
    padding: 0.2rem 0.55rem;
}
.head-source { background: linear-gradient(135deg, #64748b, #475569); }
.head-receiver { background: linear-gradient(135deg, #df238e, #c01b78); }
.head-items { background: linear-gradient(135deg, #0ea5e9, #0284c7); }
.head-selected { background: linear-gradient(135deg, #16a34a, #15803d); }
.stock-out-card-body { padding: 1.1rem 1.2rem; }
.stock-out-card-body .form-label {
    font-size: 0.8rem;
    font-weight: 600;
    color: #475569;
    margin-bottom: 0.35rem;
    display: flex;
    align-items: center;
    gap: 0.4rem;
    min-height: 1.15rem;
}
.stock-out-card-body .form-control,
.stock-out-card-body .form-select {
    height: var(--so-input-h);
    min-height: var(--so-input-h);
    border-radius: 8px;
    border-color: #e2b7d1;
}
.stock-out-card-body textarea.form-control {
    height: auto;
    min-height: 70px;
}
.stock-out-card-body .form-control:hover,
.stock-out-card-body .form-select:hover {
    border-color: var(--so-theme);
}
.stock-out-card-body .form-control:focus,
.stock-out-card-body .form-select:focus {
    border-color: var(--so-theme);
    box-shadow: 0 0 0 0.2rem var(--so-soft);
}
.stock-out-card-body .hint-text {
    font-size: 0.8rem;
    color: #64748b;
    margin-bottom: 0.75rem;
}

.stock-out-steps {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
}
.stock-out-steps .step-chip {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    background: #fff5fa;
    border: 1px solid var(--so-border);
    color: #9b1560;
    border-radius: 999px;
    padding: 0.28rem 0.7rem;
    font-size: 0.78rem;
    font-weight: 600;
}
.stock-out-steps .step-chip b {
    width: 1.15rem;
    height: 1.15rem;
    border-radius: 50%;
    background: var(--so-theme);
    color: #fff;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 0.7rem;
}

.stock-out-filters {
    background: #fcfbff;
    border: 1px solid #f3e2ec;
    border-radius: 12px;
    padding: 0.95rem 1rem 0.55rem;
    margin-bottom: 0.85rem;
}
.so-field { display: flex; flex-direction: column; height: 100%; }
.so-field-meta {
    min-height: 1.25rem;
    margin-top: 0.3rem;
    line-height: 1.2;
}
.stock-out-uniq-wrap {
    position: relative;
    min-height: var(--so-input-h);
}
.stock-out-uniq-status {
    display: block;
    font-size: 0.75rem;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.so-preview-head {
    padding: 0 0.15rem;
}

.btn-theme {
    background: var(--so-theme);
    border-color: var(--so-theme);
    color: #fff;
    border-radius: 8px;
    font-weight: 600;
}
.btn-theme:hover,
.btn-theme:focus {
    background: var(--so-theme-dark);
    border-color: var(--so-theme-dark);
    color: #fff;
    box-shadow: 0 0 0 0.2rem var(--so-soft);
}

.stock-out-table-wrap {
    border: 1px solid var(--so-border);
    border-radius: 10px;
    overflow: auto;
    background: #fff;
    min-height: 120px;
}
.stock-out-table {
    font-size: 0.85rem;
    margin-bottom: 0;
}
.stock-out-table thead th {
    background: #fff5fa;
    color: #9b1560;
    font-weight: 700;
    white-space: nowrap;
    border-color: var(--so-border);
    vertical-align: middle;
    position: sticky;
    top: 0;
    z-index: 1;
}
.stock-out-table td {
    vertical-align: middle;
    border-color: #f3dceb;
}
.stock-out-table .form-control,
.stock-out-table .form-select {
    min-height: 34px;
    height: 34px;
    font-size: 0.82rem;
}
tr.parent-item-row {
    background-color: #fff0f7;
    font-weight: 500;
}
tr.sub-item-row {
    background-color: #fafafa;
    border-left: 3px solid var(--so-theme);
}
tr.sub-item-row td:first-child {
    padding-left: 2rem;
}
tr.so-empty-row td {
    background: #fcfcfd;
}
#item_quantity { font-weight: 600; text-align: center; }

.stock-out-add-bar,
.stock-out-submit-bar {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: space-between;
    gap: 0.75rem;
    margin-top: 0.95rem;
    padding: 0.85rem 1rem;
    background: #fff5fa;
    border: 1px solid var(--so-border);
    border-radius: 10px;
}
.stock-out-submit-bar { justify-content: flex-end; }
.stock-out-add-hint {
    color: #64748b;
    font-size: 0.84rem;
}

/* Select2 theme for stock-out — keep equal height with inputs */
.stock-out-page .stock-out-select + .select2-container {
    width: 100% !important;
    display: block;
}
.stock-out-page .select2-container .select2-selection--single {
    height: var(--so-input-h) !important;
    min-height: var(--so-input-h) !important;
    border: 1px solid #e2b7d1 !important;
    border-radius: 8px !important;
    display: flex !important;
    align-items: center;
    background: #fff !important;
}
.stock-out-page .select2-container .select2-selection--single:hover {
    border-color: var(--so-theme) !important;
    background: #fffafc !important;
}
.stock-out-page .select2-container--bootstrap4 .select2-selection--single .select2-selection__rendered {
    line-height: 1.25 !important;
    padding-left: 10px;
    padding-right: 30px;
    font-size: 0.875rem;
    color: #0f172a;
    width: 100%;
}
.stock-out-page .select2-container--bootstrap4 .select2-selection--single .select2-selection__placeholder {
    color: #94a3b8;
    line-height: 1.25 !important;
}
.stock-out-page .select2-container--bootstrap4 .select2-selection--single .select2-selection__arrow {
    height: calc(var(--so-input-h) - 2px) !important;
    top: 1px;
    right: 4px;
}
.stock-out-page .select2-container--bootstrap4 .select2-selection--single .select2-selection__arrow b {
    border-color: var(--so-theme) transparent transparent transparent;
}
.stock-out-page .select2-container--bootstrap4.select2-container--focus .select2-selection--single,
.stock-out-page .select2-container--bootstrap4.select2-container--open .select2-selection--single {
    border-color: var(--so-theme) !important;
    box-shadow: 0 0 0 0.2rem var(--so-soft) !important;
}
.stock-out-page .select2-container--bootstrap4.select2-container--disabled .select2-selection--single {
    background: #f8fafc !important;
    cursor: wait;
}

/* Select2 dropdown (appended to body) */
body > .select2-container--bootstrap4 .select2-dropdown {
    border-color: #e2b7d1 !important;
    border-radius: 8px !important;
    overflow: hidden;
}
body > .select2-container--bootstrap4 .select2-results__option--highlighted,
body > .select2-container--bootstrap4 .select2-results__option--highlighted[aria-selected] {
    background-color: #df238e !important;
    color: #fff !important;
}
body > .select2-container--bootstrap4 .select2-search--dropdown .select2-search__field {
    border-radius: 6px !important;
    border-color: #e2b7d1 !important;
}
body > .select2-container--bootstrap4 .select2-search--dropdown .select2-search__field:focus {
    border-color: #df238e !important;
    outline: none;
    box-shadow: 0 0 0 0.15rem rgba(223, 35, 142, 0.2);
}

.text-theme { color: var(--so-theme) !important; }
.stock-out-inline-loader .spinner-border {
    width: 0.85rem;
    height: 0.85rem;
    border-width: 0.15em;
}
.stock-out-select-overlay {
    position: absolute;
    inset: 0;
    z-index: 5;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(255, 250, 252, 0.94);
    border: 1px solid #e2b7d1;
    border-radius: 8px;
    font-size: 0.78rem;
    color: #9b1560;
    font-weight: 600;
    pointer-events: all;
}
.stock-out-table-loader {
    position: absolute;
    inset: 0;
    z-index: 4;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(255, 255, 255, 0.82);
    color: #9b1560;
    border-radius: 10px;
}

@media (max-width: 767.98px) {
    .stock-out-add-bar,
    .stock-out-submit-bar {
        flex-direction: column;
        align-items: stretch;
    }
    .stock-out-add-bar .btn,
    .stock-out-submit-bar .btn {
        width: 100%;
    }
    .stock-out-uniq-status {
        white-space: normal;
    }
}
</style>
@endsection

