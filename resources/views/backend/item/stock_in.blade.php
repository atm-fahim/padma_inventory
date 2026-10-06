@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="{{ asset('frontend/css/item-inventory.css') }}">
<div class="container card border-top border-0 border-4 border-success">
    <div class="row justify-content-center">
        <div class="col-lg-12">
            <div class="row mb-3">
                <div class="col-lg-6 d-flex align-items-center">
                    <h4 class="mb-0"><i class="bx bx-down-arrow-circle me-2"></i>Stock In</h4>
                </div>
                <div class="col-lg-6 text-end">
                    <a href="{{ route('inventory-report') }}" class="btn btn-outline-secondary me-1">
                        <i class="bx bx-pie-chart-alt me-1"></i>Inventory Report
                    </a>
                    <a href="{{ route('stock-in-list') }}" class="btn btn-outline-info">
                        <i class="bx bx-list-ul me-1"></i>Stock In List
                    </a>
                </div>
            </div>

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            {{-- Stock In Form --}}
            <form id="stockInForm" method="POST" action="{{ route('stock-in-save-update') }}">
                @csrf
                
                <div id="stockInRowsContainer">
                    <div class="card mb-3 stock-in-row" data-row-index="0">
                        <div class="card-header bg-success text-white">
                            <h5 class="mb-0"><i class="bx bx-box me-2"></i>Item #1</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3 mb-3">
                                    <label class="form-label">
                                        <i class="bx bx-category me-1"></i>Category <span class="text-danger">*</span>
                                    </label>
                                    <select name="category_id[]" class="form-select stock-in-category" required>
                                        <option value="">Select Category</option>
                                        @foreach($categoryData as $category)
                                            <option value="{{$category->id}}">{{$category->category_title}}</option>
                                            @if ($category->children)
                                                @foreach ($category->children as $child)
                                                    <option value="{{$child->id}}">--- {{$child->category_title}}</option>
                                                @endforeach
                                            @endif
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-3 mb-3">
                                    <label class="form-label">
                                        <i class="bx bx-box me-1"></i>Item <span class="text-danger">*</span>
                                    </label>
                                    <select name="item_id[]" class="form-select stock-in-item single-select-4" required>
                                        <option value="">Select Item</option>
                                        @foreach($itemsData as $item)
                                            <option value="{{$item->id}}" 
                                                    data-category-id="{{$item->category_id ?? ''}}">
                                                {{$item->item_title ?? $item->item}}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-3 mb-3">
                                    <label class="form-label">
                                        <i class="bx bx-store me-1"></i>Supplier <span class="text-danger">*</span>
                                    </label>
                                    <select name="supplier_id[]" class="form-select" required>
                                        <option value="">Select Supplier</option>
                                        @foreach($supplierData as $supplier)
                                            <option value="{{$supplier->id}}">{{$supplier->supplier_name}}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-3 mb-3">
                                    <label class="form-label">
                                        <i class="bx bx-label me-1"></i>Brand
                                    </label>
                                    <select name="brand_id[]" class="form-select">
                                        <option value="">Select Brand</option>
                                        @foreach($brandData as $brand)
                                            <option value="{{$brand->id}}">{{$brand->brand_name}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-2 mb-3">
                                    <label class="form-label">
                                        <i class="bx bx-hash me-1"></i>Quantity <span class="text-danger">*</span>
                                    </label>
                                    <input type="number" name="quantity[]" class="form-control" 
                                           placeholder="Qty" min="1" value="1" required>
                                </div>

                                <div class="col-md-2 mb-3">
                                    <label class="form-label">
                                        <i class="bx bx-dollar me-1"></i>Price
                                    </label>
                                    <input type="number" name="price[]" class="form-control" 
                                           placeholder="0.00" min="0" step="0.01" value="0">
                                </div>

                                <div class="col-md-2 mb-3">
                                    <label class="form-label">
                                        <i class="bx bx-barcode me-1"></i>Serial No
                                    </label>
                                    <input type="text" name="serial_no[]" class="form-control" 
                                           placeholder="Serial Number">
                                </div>

                                <div class="col-md-2 mb-3">
                                    <label class="form-label">
                                        <i class="bx bx-store-alt me-1"></i>Warehouse
                                    </label>
                                    <input type="text" class="form-control bg-light" value="IT (Stock In)" readonly>
                                    <input type="hidden" name="warehouse[]" value="IT">
                                </div>

                                <div class="col-md-2 mb-3">
                                    <label class="form-label">
                                        <i class="bx bx-check-circle me-1"></i>Condition
                                    </label>
                                    <select name="condition[]" class="form-select">
                                        <option value="Good">Good</option>
                                        <option value="Slow">Slow</option>
                                        <option value="Bad">Bad</option>
                                        <option value="Damage">Damage</option>
                                        <option value="Unused">Unused</option>
                                        <option value="Normal">Normal</option>
                                    </select>
                                </div>

                                <div class="col-md-2 mb-3">
                                    <label class="form-label">
                                        <i class="bx bx-comment-detail me-1"></i>Remark
                                    </label>
                                    <input type="text" name="remark[]" class="form-control" placeholder="Remark">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-2 mb-3">
                                    <label class="form-label">
                                        <i class="bx bx-wrench me-1"></i>Warranty
                                    </label>
                                    <input type="text" name="warranty[]" class="form-control" 
                                           placeholder="e.g. 1 Year">
                                </div>
                                <div class="col-md-2 mb-3">
                                    <label class="form-label">
                                        <i class="bx bx-trash me-1"></i>Action
                                    </label>
                                    <button type="button" class="btn btn-outline-danger w-100 remove-stock-in-row">
                                        <i class="bx bx-trash"></i> Remove
                                    </button>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-3 mb-3">
                                    <label class="form-label">
                                        <i class="bx bx-chip me-1"></i>RAM
                                    </label>
                                    <input type="text" name="ram[]" class="form-control" 
                                           placeholder="e.g. 8 GB">
                                </div>

                                <div class="col-md-3 mb-3">
                                    <label class="form-label">
                                        <i class="bx bx-hdd me-1"></i>Storage
                                    </label>
                                    <input type="text" name="storage[]" class="form-control" 
                                           placeholder="e.g. 500 GB">
                                </div>

                                <div class="col-md-3 mb-3">
                                    <label class="form-label">
                                        <i class="bx bx-microchip me-1"></i>Processor
                                    </label>
                                    <input type="text" name="processor[]" class="form-control" 
                                           placeholder="e.g. Intel i5">
                                </div>

                                <div class="col-md-3 mb-3">
                                    <label class="form-label">
                                        <i class="bx bx-file me-1"></i>Description
                                    </label>
                                    <textarea name="description[]" class="form-control" rows="2" 
                                              placeholder="Item description"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center mt-3 mb-4">
                    <button type="button" class="btn btn-outline-success" id="addStockInRow">
                        <i class="bx bx-plus-circle me-1"></i> Add New Item
                    </button>
                    <button type="submit" class="btn btn-success px-5">
                        <i class="bx bx-save me-1"></i> Save Stock In
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    let rowIndex = 1;

    // Add new row
    $('#addStockInRow').click(function() {
        const newRow = $('.stock-in-row').first().clone();
        newRow.attr('data-row-index', rowIndex);
        newRow.find('.card-header h5').text('Item #' + (rowIndex + 1));
        newRow.find('input, select, textarea').val('');
        newRow.find('input[name="warehouse[]"]').val('IT');
        newRow.find('input[type="number"]').val(function() {
            if ($(this).attr('name') === 'quantity[]') return '1';
            if ($(this).attr('name') === 'price[]') return '0';
            return '';
        });
        newRow.find('select[name="condition[]"]').prop('selectedIndex', 0);
        $('#stockInRowsContainer').append(newRow);
        rowIndex++;
        
        // Reinitialize select2
        $('.single-select-4').select2();
    });

    // Remove row
    $(document).on('click', '.remove-stock-in-row', function() {
        if ($('.stock-in-row').length > 1) {
            $(this).closest('.stock-in-row').remove();
        } else {
            alert('At least one item is required');
        }
    });

    // Filter items by category
    $(document).on('change', '.stock-in-category', function() {
        const categoryId = $(this).val();
        const itemSelect = $(this).closest('.stock-in-row').find('.stock-in-item');
        itemSelect.find('option').each(function() {
            if ($(this).val() === '') return;
            const itemCategoryId = $(this).data('category-id');
            if (categoryId && itemCategoryId && itemCategoryId != categoryId) {
                $(this).hide();
            } else {
                $(this).show();
            }
        });
        itemSelect.val('').trigger('change');
    });
});
</script>
@endsection
