@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="{{ asset('frontend/css/item-inventory.css') }}">
<div class="container card border-top border-0 border-4 border-primary">
    <div class="row justify-content-center">
        <div class="col-lg-12">
            <div class="row mb-3">
                <div class="col-lg-6 d-flex align-items-center">
                    <h4 class="mb-0">Receive Items</h4>
                </div>
                <div class="col-lg-6 text-end">
                    <a href="{{ route('transfer-items') }}" class="btn btn-outline-warning me-2">
                        <i class="bx bx-transfer me-1"></i>Transfer Items
                    </a>
                    <a href="{{ route('receive-swap-items') }}" class="btn btn-outline-success me-2">
                        <i class="bx bx-check-circle me-1"></i>Receive Swap
                    </a>
                    <a href="{{ route('stock-out-list') }}" class="btn btn-outline-secondary">
                        <i class="bx bx-arrow-back me-1"></i>Back to Stock Out List
                    </a>
                </div>
            </div>

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            {{-- Stock Out Search Form --}}
            <div class="card mb-3">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bx bx-search me-2"></i>Search Stock Out Invoice</h5>
                </div>
                <div class="card-body">
                    <ul class="nav nav-tabs mb-3" id="searchTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link {{ !request('employee_id') ? 'active' : '' }}" id="invoice-tab" data-bs-toggle="tab" data-bs-target="#invoice-search" type="button" role="tab">
                                <i class="bx bx-barcode me-1"></i>By Invoice Number
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link {{ request('employee_id') ? 'active' : '' }}" id="employee-tab" data-bs-toggle="tab" data-bs-target="#employee-search" type="button" role="tab">
                                <i class="bx bx-user me-1"></i>By Employee ID
                            </button>
                        </li>
                    </ul>
                    
                    <div class="tab-content" id="searchTabContent">
                        {{-- Search by Invoice Number --}}
                        <div class="tab-pane fade {{ !request('employee_id') ? 'show active' : '' }}" id="invoice-search" role="tabpanel">
                            <form method="GET" action="{{ route('receive-stock') }}" id="searchFormInvoice">
                                <input type="hidden" name="search_type" value="invoice">
                                <div class="row">
                                    <div class="col-md-8 mb-3">
                                        <label class="form-label">
                                            <i class="bx bx-barcode me-1"></i>Stock Out Invoice Number <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" name="stock_out_no" id="stock_out_no" class="form-control" 
                                               placeholder="Enter Stock Out Invoice Number (e.g., SO-PBL741-5001)" 
                                               value="{{ request('stock_out_no') ?? ($invoiceData['stock_out_no'] ?? '') }}" required>
                                    </div>
                                    <div class="col-md-4 mb-3 d-flex align-items-end">
                                        <button type="submit" class="btn btn-primary w-100">
                                            <i class="bx bx-search me-1"></i>Search & Show Details
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                        
                        {{-- Search by Employee ID --}}
                        <div class="tab-pane fade {{ request('employee_id') ? 'show active' : '' }}" id="employee-search" role="tabpanel">
                            <form method="GET" action="{{ route('receive-stock') }}" id="searchFormEmployee">
                                <input type="hidden" name="search_type" value="employee">
                                <div class="row">
                                    <div class="col-md-8 mb-3">
                                        <label class="form-label">
                                            <i class="bx bx-id-card me-1"></i>Employee ID <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" name="employee_id" id="search_employee_id" class="form-control" 
                                               placeholder="Enter Employee ID" 
                                               value="{{ request('employee_id') ?? '' }}" required>
                                    </div>
                                    <div class="col-md-4 mb-3 d-flex align-items-end">
                                        <button type="submit" class="btn btn-primary w-100">
                                            <i class="bx bx-search me-1"></i>Search & Show Details
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            @if(isset($invoiceData) && !empty($invoiceData['items']))
            {{-- Invoice Details --}}
            <div class="card mb-3">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="bx bx-receipt me-2"></i>Invoice Details</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <strong>Stock Out No:</strong><br>
                            <span class="badge bg-primary">{{ $invoiceData['stock_out_no'] }}</span>
                        </div>
                        <div class="col-md-3">
                            <strong>From Branch:</strong><br>
                            {{ $invoiceData['from_branch']->branch_name ?? 'N/A' }}
                        </div>
                        <div class="col-md-3">
                            <strong>To Branch:</strong><br>
                            {{ $invoiceData['to_branch']->branch_name ?? 'N/A' }}
                        </div>
                        <div class="col-md-3">
                            <strong>Date:</strong><br>
                            {{ date('Y-m-d H:i', strtotime($invoiceData['created_at'])) }}
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <strong>From Employee:</strong><br>
                            {{ $invoiceData['from_employee']->emp_name ?? 'N/A' }} 
                            ({{ $invoiceData['from_employee']->employee_id ?? 'N/A' }})
                        </div>
                        <div class="col-md-6">
                            <strong>To Employee:</strong><br>
                            {{ $invoiceData['to_employee']->emp_name ?? 'N/A' }} 
                            ({{ $invoiceData['to_employee']->employee_id ?? 'N/A' }})
                        </div>
                    </div>
                </div>
            </div>

            {{-- Product List with Checkboxes --}}
            <div class="card mb-3">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="bx bx-package me-2"></i>Products List</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('receive-stock-save') }}" id="receiveForm">
                        @csrf
                        <input type="hidden" name="stock_out_no" value="{{ $invoiceData['stock_out_no'] }}">
                        
                        <div class="mb-3">
                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="selectAllItems()">
                                <i class="bx bx-check-square me-1"></i>Select All
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="deselectAllItems()">
                                <i class="bx bx-square me-1"></i>Deselect All
                            </button>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-bordered table-striped table-hover">
                                <thead class="table-primary">
                                    <tr>
                                        <th width="50">
                                            <input type="checkbox" id="selectAll" onchange="toggleAll()">
                                        </th>
                                        <th>SL</th>
                                        <th>Product Name</th>
                                        <th>Category</th>
                                        <th>Brand</th>
                                        <th>Quantity</th>
                                        <th>Serial No</th>
                                        <th>Employee ID</th>
                                        <th>Product Details</th>
                                        <th>Condition</th>
                                        <th>Remark</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $deviceKeywords = ['laptop','desktop','server','pc','computer'];
                                        $orderedItems = [];
                                        foreach($invoiceData['items'] as $idx => $row) {
                                            $title = strtolower($row['item']->item_title ?? $row['item']->item ?? '');
                                            $isParent = false;
                                            foreach($deviceKeywords as $kw) {
                                                if (strpos($title, $kw) !== false) { $isParent = true; break; }
                                            }
                                            $orderedItems[] = ['data' => $row, 'is_parent' => $isParent];
                                        }
                                        $hasParent = collect($orderedItems)->contains(function($r){ return $r['is_parent']; });
                                        if(!$hasParent && count($orderedItems) > 0) {
                                            $orderedItems[0]['is_parent'] = true;
                                        }
                                        $parentRow = collect($orderedItems)->firstWhere('is_parent', true);
                                        $parentTitle = $parentRow['data']['item']->item_title
                                            ?? $parentRow['data']['item']->item
                                            ?? 'Main Device';
                                        $setNo = $parentRow['data']['set_uniq_no'] ?? ($orderedItems[0]['data']['set_uniq_no'] ?? '');
                                        $setCount = count($orderedItems);
                                    @endphp
                                    @if($setNo)
                                    <tr class="set-header-row">
                                        <td></td>
                                        <td colspan="10">
                                            <strong>Device Set:</strong> {{ $setNo }} —
                                            <strong>{{ $parentTitle }}</strong>
                                            <span class="text-muted">({{ $setCount }} item{{ $setCount > 1 ? 's' : '' }} in this set)</span>
                                        </td>
                                    </tr>
                                    @endif
                                    @foreach($orderedItems as $index => $row)
                                    @php $item = $row['data']; $isParentRow = $row['is_parent']; @endphp
                                    <tr class="{{ ($item['is_received'] ? 'table-success ' : '') . ($isParentRow ? 'parent-device-row' : 'sub-device-row') }}">
                                        <td>
                                            @if(!$item['is_received'])
                                                <input type="checkbox" name="inventory_ids[]" 
                                                       value="{{ $item['id'] }}" 
                                                       class="item-checkbox">
                                            @else
                                                <i class="bx bx-check-circle text-success"></i>
                                            @endif
                                        </td>
                                        <td>{{ $index + 1 }}</td>
                                        <td>
                                            @php
                                                $title = $item['item']->item_title ?? $item['item']->item ?? 'N/A';
                                            @endphp
                                            @if($isParentRow)
                                                <strong>{{ $title }}</strong>
                                                <div class="text-muted small">Main device (complete Laptop/Desktop/Server)</div>
                                            @else
                                                <span class="text-muted small">&#x21B3; </span>
                                                <strong>{{ $title }}</strong>
                                                <div class="text-muted small">Part of: {{ $parentTitle }}</div>
                                            @endif
                                        </td>
                                        <td>{{ $item['category']->category_title ?? 'N/A' }}</td>
                                        <td>{{ $item['brand']->brand_name ?? 'N/A' }}</td>
                                        <td>
                                            <span class="badge bg-info">{{ $item['quantity'] ?? 'N/A' }}</span>
                                        </td>
                                        <td>{{ $item['serial_no'] ?? 'N/A' }}</td>
                                        <td>
                                            @if(!empty($item['to_employee_id']))
                                                <span class="badge bg-secondary">
                                                    {{ $item['to_employee_id'] }}
                                                </span>
                                                @if(isset($item['to_employee']) && !empty($item['to_employee']->emp_name))
                                                    <br><small class="text-muted">{{ $item['to_employee']->emp_name }}</small>
                                                @endif
                                            @else
                                                <span class="text-muted">N/A</span>
                                            @endif
                                        </td>
                                        <td class="product-details">
                                            @php
                                                $details = [];
                                                if(!empty($item['processor'])) {
                                                    $details[] = 'Processor: ' . $item['processor'];
                                                }
                                                if(!empty($item['storage'])) {
                                                    $details[] = 'Storage: ' . $item['storage'];
                                                }
                                                if(!empty($item['ram'])) {
                                                    $details[] = 'RAM: ' . $item['ram'];
                                                }
                                                echo !empty($details) ? implode(', ', $details) : 'N/A';
                                            @endphp
                                        </td>
                                        <td>{{ $item['condition'] ?? 'Good' }}</td>
                                        <td>
                                            @if(!$item['is_received'])
                                                <input type="text" name="remarks[{{ $item['id'] }}]" class="form-control form-control-sm" placeholder="Remark (optional)">
                                            @else
                                                <span class="text-muted small">{{ $item['inventory_item']->remarks ?? '' }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($item['is_received'])
                                                <span class="badge bg-success">
                                                    <i class="bx bx-check-circle me-1"></i>Received
                                                </span>
                                            @else
                                                <span class="badge bg-warning">
                                                    <i class="bx bx-time-five me-1"></i>Pending
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-3 text-end">
                            <button type="submit" class="btn btn-success btn-lg" id="receiveBtn">
                                <i class="bx bx-check-circle me-2"></i>Mark Selected Items as Received
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            @elseif(isset($invoiceData))
                <div class="alert alert-warning">
                    <i class="bx bx-info-circle me-2"></i>
                    No items found for this Stock Out Invoice Number.
                </div>
            @endif
        </div>
    </div>
</div>

<style>
    .product-details {
        font-size: 11px;
        white-space: nowrap;
    }
    .table-success {
        background-color: #d1e7dd !important;
    }
    /* Hierarchy: main device vs components */
    tr.parent-device-row {
        background-color: #e8f4fd;
    }
    tr.sub-device-row {
        background-color: #f8f9fa;
    }
.set-header-row {
    background-color: #d1e7ff;
    font-weight: 600;
}
</style>

<script>
    // Get all checkboxes that are not disabled (only pending items)
    function getItemCheckboxes() {
        return document.querySelectorAll('input.item-checkbox[type="checkbox"]');
    }
    
    // Toggle all checkboxes based on select all checkbox
    function toggleAll() {
        const selectAll = document.getElementById('selectAll');
        if (!selectAll) {
            console.log('Select All checkbox not found');
            return;
        }
        
        const checkboxes = getItemCheckboxes();
        console.log('Found checkboxes:', checkboxes.length);
        checkboxes.forEach(checkbox => {
            checkbox.checked = selectAll.checked;
        });
    }
    
    // Select all function
    function selectAllItems() {
        const checkboxes = getItemCheckboxes();
        console.log('Select All - Found checkboxes:', checkboxes.length);
        
        if (checkboxes.length === 0) {
            console.log('No checkboxes found');
            return;
        }
        
        checkboxes.forEach(checkbox => {
            checkbox.checked = true;
        });
        
        const selectAllCheckbox = document.getElementById('selectAll');
        if (selectAllCheckbox) {
            selectAllCheckbox.checked = true;
        }
        
        console.log('All items selected');
    }
    
    // Deselect all function
    function deselectAllItems() {
        const checkboxes = getItemCheckboxes();
        console.log('Deselect All - Found checkboxes:', checkboxes.length);
        
        checkboxes.forEach(checkbox => {
            checkbox.checked = false;
        });
        
        const selectAllCheckbox = document.getElementById('selectAll');
        if (selectAllCheckbox) {
            selectAllCheckbox.checked = false;
        }
        
        console.log('All items deselected');
    }
    
    // Wait for DOM to be fully loaded
    document.addEventListener('DOMContentLoaded', function() {
        console.log('DOM loaded');
        
        const selectAllCheckbox = document.getElementById('selectAll');
        const receiveForm = document.getElementById('receiveForm');
        
        // Update select all checkbox when individual checkboxes change
        const checkboxes = getItemCheckboxes();
        console.log('Total checkboxes found:', checkboxes.length);
        
        if (selectAllCheckbox && checkboxes.length > 0) {
            checkboxes.forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    const allChecked = Array.from(checkboxes).every(cb => cb.checked);
                    selectAllCheckbox.checked = allChecked;
                });
            });
        }

        // Form submission validation
        if (receiveForm) {
            receiveForm.addEventListener('submit', function(e) {
                const checkedBoxes = getItemCheckboxes();
                const checkedCount = Array.from(checkedBoxes).filter(cb => cb.checked).length;
                
                if (checkedCount === 0) {
                    e.preventDefault();
                    alert('Please select at least one item to receive.');
                    return false;
                }
                return confirm('Are you sure you want to mark the selected items as received?');
            });
        }
        
        // Handle search form submissions
        const searchFormInvoice = document.getElementById('searchFormInvoice');
        const searchFormEmployee = document.getElementById('searchFormEmployee');
        
        if (searchFormInvoice) {
            searchFormInvoice.addEventListener('submit', function(e) {
                const stockOutNo = document.getElementById('stock_out_no').value.trim();
                console.log('Invoice search form submitted, stock_out_no:', stockOutNo);
                if (!stockOutNo) {
                    e.preventDefault();
                    alert('Please enter a Stock Out Invoice Number.');
                    return false;
                }
                // Allow form to submit normally
            });
        }
        
        if (searchFormEmployee) {
            searchFormEmployee.addEventListener('submit', function(e) {
                const employeeId = document.getElementById('search_employee_id').value.trim();
                console.log('Employee search form submitted, employee_id:', employeeId);
                if (!employeeId) {
                    e.preventDefault();
                    alert('Please enter an Employee ID.');
                    return false;
                }
                // Allow form to submit normally
            });
        }
    });
</script>

@endsection
