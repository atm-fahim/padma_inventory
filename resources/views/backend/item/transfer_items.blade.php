@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="{{ asset('frontend/css/item-inventory.css') }}">
<div class="container card border-top border-0 border-4 border-primary">
    <div class="row justify-content-center">
        <div class="col-lg-12">
            <div class="row mb-3">
                <div class="col-lg-6 d-flex align-items-center">
                    <h4 class="mb-0">Transfer Received Items</h4>
                </div>
                <div class="col-lg-6 text-end">
                    <a href="{{ route('swap-items') }}" class="btn btn-outline-warning me-2">
                        <i class="bx bx-transfer-alt me-1"></i>Swap Items
                    </a>
                    <a href="{{ route('receive-stock') }}" class="btn btn-outline-secondary">
                        <i class="bx bx-arrow-back me-1"></i>Back to Receive Items
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
                    <form method="GET" action="{{ route('transfer-items') }}" id="searchForm">
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
                                <a href="{{ route('transfer-items') }}" class="btn btn-outline-secondary">
                                    <i class="bx bx-refresh me-1"></i>Reset
                                </a>
                            </div>
                        </div>
                    </form>
                    
                    @if(isset($employeeInfo) && !empty($employeeInfo))
                    <div class="alert alert-info mb-0">
                        <strong>Employee Information:</strong><br>
                        <strong>Name:</strong> {{ $employeeInfo->emp_name ?? 'N/A' }}<br>
                        <strong>Employee ID:</strong> {{ $employeeInfo->employee_id ?? 'N/A' }}<br>
                        <strong>Branch:</strong> {{ $employeeInfo->branch_name ?? 'N/A' }}
                    </div>
                    @endif
                </div>
            </div>

            @if(isset($receivedItems) && $receivedItems->count() > 0)
            {{-- Transfer Form --}}
            <div class="card mb-3">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bx bx-transfer me-2"></i>Transfer Items to Another Employee</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('transfer-items-save') }}" id="transferForm">
                        @csrf
                        
                        <div class="row mb-3">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">
                                    <i class="bx bx-id-card me-1"></i>To Employee ID <span class="text-danger">*</span>
                                </label>
                                <input type="text" id="to_employee_id" name="to_employee_id" class="form-control" 
                                       placeholder="Enter Employee ID" onchange="fetchEmployeeInfo(this.value)" required>
                                <div id="employee_id_error" class="text-danger small mt-1" style="display: none;"></div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">
                                    <i class="bx bx-user me-1"></i>Employee Name
                                </label>
                                <input type="text" id="employee_name" class="form-control" 
                                       placeholder="Employee Name" readonly>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">
                                    <i class="bx bx-building me-1"></i>To Branch <span class="text-danger">*</span>
                                </label>
                                <select id="to_branch_id" name="to_branch_id" class="form-select single-select-4" required>
                                    <option value="">Select Branch</option>
                                    @foreach($branchData as $branch)
                                        <option value="{{$branch->hr_branch_id}}">
                                            {{$branch->branch_name}}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">
                                    <i class="bx bx-barcode me-1"></i>From Device Serial No
                                </label>
                                <input type="text" id="from_device_serial_no" name="from_device_serial_no" 
                                       class="form-control" placeholder="Enter From Device Serial No">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">
                                    <i class="bx bx-barcode me-1"></i>To Device Serial No
                                </label>
                                <input type="text" id="to_device_serial_no" name="to_device_serial_no" 
                                       class="form-control" placeholder="Enter To Device Serial No">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-12">
                                <label class="form-label">
                                    <i class="bx bx-note me-1"></i>Remark
                                </label>
                                <textarea name="remarks" class="form-control" rows="2" placeholder="Transfer remark (optional)">{{ old('remarks') }}</textarea>
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
                                        <th>Received Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($receivedItems as $index => $item)
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
                                            @if($item->receive_date_time)
                                                {{ date('Y-m-d H:i', strtotime($item->receive_date_time)) }}
                                            @else
                                                N/A
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-3 text-end">
                            <button type="submit" class="btn btn-success btn-lg" id="transferBtn">
                                <i class="bx bx-transfer me-2"></i>Transfer Selected Items
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            @elseif(isset($searchEmployeeId) && !empty($searchEmployeeId))
                <div class="alert alert-warning">
                    <i class="bx bx-info-circle me-2"></i>
                    No received items found for Employee ID: <strong>{{ $searchEmployeeId }}</strong>
                </div>
            @else
                <div class="alert alert-info">
                    <i class="bx bx-info-circle me-2"></i>
                    Please enter an Employee ID to search for received items to transfer.
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
    
    // Fetch employee info
    function fetchEmployeeInfo(employeeId) {
        if (!employeeId) {
            document.getElementById('employee_name').value = '';
            document.getElementById('to_branch_id').value = '';
            // Trigger select2 change if it exists
            if (typeof jQuery !== 'undefined' && jQuery('#to_branch_id').hasClass('select2-hidden-accessible')) {
                jQuery('#to_branch_id').val('').trigger('change');
            }
            return;
        }
        
        fetch('{{ route("get-employee-by-id") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ employee_id: employeeId })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('employee_name').value = data.data.name || '';
                
                // Set branch value
                const branchId = data.data.branch_id || '';
                const branchSelect = document.getElementById('to_branch_id');
                
                if (branchId) {
                    branchSelect.value = branchId;
                    
                    // Trigger select2 change event if select2 is initialized
                    if (typeof jQuery !== 'undefined' && jQuery(branchSelect).hasClass('select2-hidden-accessible')) {
                        jQuery(branchSelect).val(branchId).trigger('change');
                    } else {
                        // Trigger native change event
                        branchSelect.dispatchEvent(new Event('change', { bubbles: true }));
                    }
                }
                
                document.getElementById('employee_id_error').style.display = 'none';
            } else {
                document.getElementById('employee_name').value = '';
                document.getElementById('to_branch_id').value = '';
                // Trigger select2 change if it exists
                if (typeof jQuery !== 'undefined' && jQuery('#to_branch_id').hasClass('select2-hidden-accessible')) {
                    jQuery('#to_branch_id').val('').trigger('change');
                }
                document.getElementById('employee_id_error').textContent = data.message || 'Employee not found';
                document.getElementById('employee_id_error').style.display = 'block';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('employee_name').value = '';
            document.getElementById('to_branch_id').value = '';
            // Trigger select2 change if it exists
            if (typeof jQuery !== 'undefined' && jQuery('#to_branch_id').hasClass('select2-hidden-accessible')) {
                jQuery('#to_branch_id').val('').trigger('change');
            }
            document.getElementById('employee_id_error').textContent = 'Error fetching employee info';
            document.getElementById('employee_id_error').style.display = 'block';
        });
    }
    
    // Auto-dismiss flash messages after 5 seconds and prevent showing again
    document.addEventListener('DOMContentLoaded', function() {
        // Auto-dismiss alerts after 5 seconds and mark as shown
        const alerts = document.querySelectorAll('.alert:not(.alert-permanent)');
        alerts.forEach(function(alert, index) {
            // Create unique ID for each alert based on its content
            const alertText = alert.textContent.trim();
            const alertId = 'alert-' + btoa(alertText).substring(0, 20) + '-' + index;
            
            // Check if this alert was already shown in this session
            if (sessionStorage.getItem('alert-shown-' + alertId)) {
                alert.remove(); // Remove if already shown
                return;
            }
            
            // Mark as shown
            sessionStorage.setItem('alert-shown-' + alertId, 'true');
            
            // Auto-dismiss after 5 seconds
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
        const transferForm = document.getElementById('transferForm');
        
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
        if (transferForm) {
            transferForm.addEventListener('submit', function(e) {
                const checkedBoxes = getItemCheckboxes();
                const checkedCount = Array.from(checkedBoxes).filter(cb => cb.checked).length;
                
                if (checkedCount === 0) {
                    e.preventDefault();
                    alert('Please select at least one item to transfer.');
                    return false;
                }
                
                const toEmployeeId = document.getElementById('to_employee_id').value;
                const toBranchId = document.getElementById('to_branch_id').value;
                
                if (!toEmployeeId || !toBranchId) {
                    e.preventDefault();
                    alert('Please enter employee ID and select branch.');
                    return false;
                }
                
                return confirm('Are you sure you want to transfer the selected items?');
            });
        }
    });
</script>

@endsection
