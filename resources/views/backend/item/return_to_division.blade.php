@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="{{ asset('frontend/css/item-inventory.css') }}">
<div class="container card border-top border-0 border-4 border-primary">
    <div class="row justify-content-center">
        <div class="col-lg-12">
            <div class="row mb-3">
                <div class="col-lg-6 d-flex align-items-center">
                    <h4 class="mb-0">Return Items to Division</h4>
                </div>
                <div class="col-lg-6 text-end">
                    <a href="{{ route('transfer-items') }}" class="btn btn-outline-secondary">
                        <i class="bx bx-arrow-back me-1"></i>Back to Transfer Items
                    </a>
                </div>
            </div>

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bx bx-error-circle me-2"></i>
                    <strong>Error!</strong> {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bx bx-check-circle me-2"></i>
                    <strong>Success!</strong> {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if(session('warning'))
                <div class="alert alert-warning alert-dismissible fade show" role="alert">
                    <i class="bx bx-error me-2"></i>
                    <strong>Warning!</strong> {{ session('warning') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if(session('info'))
                <div class="alert alert-info alert-dismissible fade show" role="alert">
                    <i class="bx bx-info-circle me-2"></i>
                    <strong>Info!</strong> {{ session('info') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            {{-- Search Form --}}
            <div class="card mb-3">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bx bx-search me-2"></i>Search by Employee ID</h5>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('return-to-division') }}" id="searchForm">
                        <div class="row">
                            <div class="col-md-8 mb-3">
                                <label class="form-label">
                                    <i class="bx bx-id-card me-1"></i>Employee ID <span class="text-danger">*</span>
                                </label>
                                <input type="text" name="employee_id" id="search_employee_id" class="form-control" 
                                       placeholder="Enter Employee ID" 
                                       value="{{ request('employee_id') ?? ($searchEmployeeId ?? '') }}" required>
                            </div>
                            <div class="col-md-4 mb-3 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary w-100 me-2">
                                    <i class="bx bx-search me-1"></i>Search
                                </button>
                                <a href="{{ route('return-to-division') }}" class="btn btn-outline-secondary">
                                    <i class="bx bx-refresh me-1"></i>Reset
                                </a>
                            </div>
                        </div>
                    </form>
                    
                    @if(isset($employeeInfo) && !empty($employeeInfo))
                    <div class="alert alert-info mb-0 mt-3">
                        <strong>Employee Information:</strong><br>
                        <strong>Name:</strong> {{ $employeeInfo->emp_name ?? 'N/A' }}<br>
                        <strong>Employee ID:</strong> {{ $employeeInfo->employee_id ?? 'N/A' }}<br>
                        <strong>Branch:</strong> {{ $employeeInfo->branch_name ?? 'N/A' }}
                    </div>
                    @endif
                </div>
            </div>

            @if(isset($itemsForReturn) && $itemsForReturn->count() > 0)
            {{-- Return Form --}}
            <div class="card mb-3">
                <div class="card-header bg-warning text-white">
                    <h5 class="mb-0"><i class="bx bx-undo me-2"></i>Return Items to Division</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('return-to-division-save') }}" id="returnForm">
                        @csrf
                        
                        <div class="row mb-3">
                            <div class="col-md-3 mb-3">
                                <label class="form-label">
                                    <i class="bx bx-building me-1"></i>Division <span class="text-danger">*</span>
                                </label>
                                <select id="division_id" name="division_id" class="form-select single-select-4" required>
                                    <option value="">Select Division</option>
                                    @foreach($divisionData as $division)
                                        <option value="{{ $division->id }}">
                                            {{ $division->division_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label">
                                    <i class="bx bx-building-house me-1"></i>Branch <span class="text-danger">*</span>
                                </label>
                                <select id="branch_id" name="branch_id" class="form-select single-select-4" required>
                                    <option value="">Select Branch</option>
                                    @foreach($branchData as $branch)
                                        <option value="{{ $branch->hr_branch_id }}">
                                            {{ $branch->branch_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label">
                                    <i class="bx bx-check-circle me-1"></i>Condition
                                </label>
                                <select id="condition" name="condition" class="form-select">
                                    <option value="">Select Condition</option>
                                    <option value="Good">Good</option>
                                    <option value="Slow">Slow</option>
                                    <option value="Bad">Bad</option>
                                    <option value="Damage">Damage</option>
                                    <option value="Normal">Normal</option>
                                    <option value="Unused">Unused</option>
                                </select>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label">
                                    <i class="bx bx-barcode me-1"></i>From Device Serial No
                                </label>
                                <input type="text" id="from_device_serial_no" name="from_device_serial_no" 
                                       class="form-control" placeholder="Enter From Device Serial No">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-12 mb-3">
                                <label class="form-label">
                                    <i class="bx bx-comment-detail me-1"></i>Remarks
                                </label>
                                <textarea id="remarks" name="remarks" class="form-control" rows="2" placeholder="Enter remarks (optional)"></textarea>
                            </div>
                        </div>

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
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($itemsForReturn as $index => $item)
                                    <tr>
                                        <td>
                                            <input type="checkbox" name="inventory_ids[]" 
                                                   value="{{ $item->id }}" 
                                                   class="item-checkbox">
                                        </td>
                                        <td>{{ $index + 1 }}</td>
                                        <td>
                                            <strong>{{ $item->item_title ?? 'N/A' }}</strong>
                                        </td>
                                        <td>{{ $item->category_title ?? 'N/A' }}</td>
                                        <td>{{ $item->brand_name ?? 'N/A' }}</td>
                                        <td>
                                            <span class="badge bg-info">{{ $item->quantity ?? 'N/A' }}</span>
                                        </td>
                                        <td>{{ $item->uniq_serial_no ?? 'N/A' }}</td>
                                        <td>
                                            @if(!empty($item->to_employee_id))
                                                <span class="badge bg-secondary">
                                                    {{ $item->to_employee_id }}
                                                </span>
                                            @else
                                                <span class="text-muted">N/A</span>
                                            @endif
                                        </td>
                                        <td class="product-details">
                                            @php
                                                $details = [];
                                                if(!empty($item->processor)) {
                                                    $details[] = 'Processor: ' . $item->processor;
                                                }
                                                if(!empty($item->storage)) {
                                                    $details[] = 'Storage: ' . $item->storage;
                                                }
                                                if(!empty($item->ram)) {
                                                    $details[] = 'RAM: ' . $item->ram;
                                                }
                                                echo !empty($details) ? implode(', ', $details) : 'N/A';
                                            @endphp
                                        </td>
                                        <td>{{ $item->condition ?? 'Good' }}</td>
                                        <td>
                                            @if($item->status == 3)
                                                <span class="badge bg-success">Received</span>
                                            @elseif($item->status == 4)
                                                <span class="badge bg-info">Transferred</span>
                                            @else
                                                <span class="badge bg-secondary">{{ $item->status }}</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-3 text-end">
                            <button type="submit" class="btn btn-warning btn-lg" id="returnBtn">
                                <i class="bx bx-undo me-2"></i>Return Selected Items to Division
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            @elseif(isset($searchEmployeeId) && !empty($searchEmployeeId))
                <div class="alert alert-warning">
                    <i class="bx bx-info-circle me-2"></i>
                    No items found for Employee ID: <strong>{{ $searchEmployeeId }}</strong>
                </div>
            @else
                <div class="alert alert-info">
                    <i class="bx bx-info-circle me-2"></i>
                    Please enter an Employee ID to search for items to return to division.
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
</style>

<script>
    // Get all checkboxes
    function getItemCheckboxes() {
        return document.querySelectorAll('input.item-checkbox[type="checkbox"]');
    }
    
    // Toggle all checkboxes based on select all checkbox
    function toggleAll() {
        const selectAll = document.getElementById('selectAll');
        if (!selectAll) return;
        
        const checkboxes = getItemCheckboxes();
        checkboxes.forEach(checkbox => {
            checkbox.checked = selectAll.checked;
        });
    }
    
    // Select all function
    function selectAllItems() {
        const checkboxes = getItemCheckboxes();
        checkboxes.forEach(checkbox => {
            checkbox.checked = true;
        });
        const selectAllCheckbox = document.getElementById('selectAll');
        if (selectAllCheckbox) {
            selectAllCheckbox.checked = true;
        }
    }
    
    // Deselect all function
    function deselectAllItems() {
        const checkboxes = getItemCheckboxes();
        checkboxes.forEach(checkbox => {
            checkbox.checked = false;
        });
        const selectAllCheckbox = document.getElementById('selectAll');
        if (selectAllCheckbox) {
            selectAllCheckbox.checked = false;
        }
    }
    
    // Auto-dismiss flash messages after 5 seconds
    document.addEventListener('DOMContentLoaded', function() {
        const alerts = document.querySelectorAll('.alert:not(.alert-permanent)');
        alerts.forEach(function(alert, index) {
            const alertText = alert.textContent.trim();
            const alertId = 'alert-' + btoa(alertText).substring(0, 20) + '-' + index;
            
            if (sessionStorage.getItem('alert-shown-' + alertId)) {
                alert.remove();
                return;
            }
            
            sessionStorage.setItem('alert-shown-' + alertId, 'true');
            
            setTimeout(function() {
                if (alert && alert.parentNode) {
                    try {
                        const bsAlert = bootstrap.Alert.getOrCreateInstance(alert);
                        bsAlert.close();
                    } catch(e) {
                        alert.remove();
                    }
                }
            }, 5000);
        });
        
        const selectAllCheckbox = document.getElementById('selectAll');
        const returnForm = document.getElementById('returnForm');
        
        // Update select all checkbox when individual checkboxes change
        const checkboxes = getItemCheckboxes();
        if (selectAllCheckbox && checkboxes.length > 0) {
            checkboxes.forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    const allChecked = Array.from(checkboxes).every(cb => cb.checked);
                    selectAllCheckbox.checked = allChecked;
                });
            });
        }

        // Form submission validation
        if (returnForm) {
            returnForm.addEventListener('submit', function(e) {
                const checkedBoxes = getItemCheckboxes();
                const checkedCount = Array.from(checkedBoxes).filter(cb => cb.checked).length;
                
                if (checkedCount === 0) {
                    e.preventDefault();
                    alert('Please select at least one item to return.');
                    return false;
                }
                
                const divisionId = document.getElementById('division_id').value;
                
                if (!divisionId) {
                    e.preventDefault();
                    alert('Please select a division.');
                    return false;
                }
                
                return confirm('Are you sure you want to return the selected items to division?');
            });
        }
        
        // Handle search form submission
        const searchForm = document.getElementById('searchForm');
        if (searchForm) {
            searchForm.addEventListener('submit', function(e) {
                const employeeId = document.getElementById('search_employee_id').value.trim();
                if (!employeeId) {
                    e.preventDefault();
                    alert('Please enter an Employee ID.');
                    return false;
                }
            });
        }
    });
</script>

@endsection
