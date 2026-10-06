@extends('layouts.app')

@section('content')
    <link rel="stylesheet" href="{{ asset('frontend/css/item-inventory.css') }}">
    <div class="container card border-top border-0 border-4" style="border-top-color:#df238e !important;">
        <div class="row justify-content-center">
            <div class="col-lg-12">
                <!-- <a href="{{route('items')}}">
                    <button type="button" class="btn btn-outline-info">
                        <i class="bx bx-refresh me-0"></i>
                    </button>
                </a> -->
                <!-- <div class="col-lg-12">
                    @if($user_access->is_create==1)
                        <button type="button" class="btn btn-sm btn-outline-success px-5" data-bs-toggle="modal" data-bs-target="#exampleModal">
                            Add Items +
                        </button>
                    @endif
                </div> -->
                <div class="col-lg-12">
    <div class="row mb-3">
        <div class="col-lg-6 d-flex align-items-center">
            <h4 class="mb-0">Item Inventory</h4>
        </div>
        <div class="col-lg-6 d-flex justify-content-end align-items-center">
            <a href="{{ route('items-inventory-list') }}" class="btn btn-info btn-sm me-2">
                <i class="bx bx-list-ul me-1"></i> Inventory List
            </a>
            <a href="{{ route('items-inventory-csv-upload') }}" class="btn btn-success btn-sm">
                <i class="bx bx-upload me-1"></i> Excel Upload File CSV
            </a>
        </div>
    </div>

    {{-- Inventory entry form --}}
    <form id="inventoryForm" method="POST" action="{{ route('items-inventory-save-update') }}" enctype="multipart/form-data">
        @csrf

        <div id="inventoryRowsContainer">
            <div class="inv-row-container inv-row" data-row-index="0">
                <div class="row">
                    {{-- Row 1: 6 fields --}}
                    <div class="col-md-2 mb-2">
                        <label class="form-label">
                            <i class="bx bx-category me-1"></i>Category <span class="text-danger">*</span>
                        </label>
                        <select name="category_id[]" class="form-select inv-category inv-main-select" required>
                            <option value="">Select Category</option>
                            @foreach($catData as $category)
                                <option value="{{$category->id}}">{{$category->category_title}}</option>
                                @if ($category->children)
                                    @foreach ($category->children as $child)
                                        <option value="{{$child->id}}">--- {{$child->category_title}}</option>
                                    @endforeach
                                @endif
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-2 mb-2">
                        <label class="form-label">
                            <i class="bx bx-box me-1"></i>Item <span class="text-danger">*</span>
                        </label>
                        <select name="item_id[]" class="form-select inv-item inv-main-select" required>
                            <option value="">Select Item</option>
                            @foreach($itemsData as $item)
                                <option value="{{$item->id}}"
                                        data-category-id="{{$item->category_id ?? ''}}">
                                    {{$item->item_title ?? $item->item}}
                                </option>
                            @endforeach
                        </select>
                        <div class="item-specs-container mt-2" style="display: none;"></div>
                    </div>

                    <div class="col-md-2 mb-2">
                        <label class="form-label">
                            <i class="bx bx-label me-1"></i>Brand
                        </label>
                        <select name="brand_id[]" class="form-select inv-brand inv-main-select">
                            <option value="">Select Brand</option>
                            @foreach($brandData as $brand)
                                <option value="{{$brand->id}}">{{$brand->brand_name}}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-2 mb-2">
                        <label class="form-label">
                            <i class="bx bx-store me-1"></i>Supplier
                        </label>
                        <select name="supplier_id[]" class="form-select inv-supplier inv-main-select">
                            <option value="">Select Supplier</option>
                            @foreach($supplierData as $supplier)
                                <option value="{{$supplier->id}}">{{$supplier->supplier_name}}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-2 mb-2">
                        <label class="form-label small mb-1">
                            <i class="bx bx-wrench me-1"></i>Service Warranty
                        </label>
                        <div class="warranty-ym-group" title="Year (left), Month (right) — e.g. 1 Year, 5 Months">
                            <div class="warranty-ym-field">
                                <span class="warranty-ym-lbl">Year</span>
                                <input type="number" min="0" step="1" name="service_warranty_years[]" class="form-control warranty-ym-input inv-sw-y" placeholder="0" aria-label="Service warranty years">
                            </div>
                            <div class="warranty-ym-field">
                                <span class="warranty-ym-lbl">Month</span>
                                <input type="number" min="0" step="1" name="service_warranty_months[]" class="form-control warranty-ym-input inv-sw-m" placeholder="0" aria-label="Service warranty months">
                            </div>
                        </div>
                    </div>

                    <div class="col-md-2 mb-2">
                        <label class="form-label small mb-1">
                            <i class="bx bx-shield me-1"></i>Warranty
                        </label>
                        <div class="warranty-ym-group">
                            <div class="warranty-ym-field">
                                <span class="warranty-ym-lbl">Year</span>
                                <input type="number" min="0" step="1" name="warranty_years[]" class="form-control warranty-ym-input inv-wr-y" placeholder="0" aria-label="Warranty years">
                            </div>
                            <div class="warranty-ym-field">
                                <span class="warranty-ym-lbl">Month</span>
                                <input type="number" min="0" step="1" name="warranty_months[]" class="form-control warranty-ym-input inv-wr-m" placeholder="0" aria-label="Warranty months">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    {{-- Row 2: 6 fields --}}
                    <div class="col-md-2 mb-2">
                        <label class="form-label">
                            <i class="bx bx-barcode me-1"></i>Serial No
                        </label>
                        <input type="text" name="serial_no[]" class="form-control" placeholder="e.g. SN-001">
                    </div>

                    <div class="col-md-2 mb-2">
                        <label class="form-label">
                            <i class="bx bx-check-circle me-1"></i>Condition
                        </label>
                        <select name="condition[]" class="form-select inv-condition">
                            <option value="">Select Condition</option>
                            <option value="slow">Slow</option>
                            <option value="good">Good</option>
                            <option value="bad">Bad</option>
                            <option value="damage">Damage</option>
                            <option value="normal">Normal</option>
                            <option value="unused">Unused</option>
                        </select>
                    </div>

                    <div class="col-md-2 mb-2">
                        <label class="form-label">
                            <i class="bx bx-hash me-1"></i>Quantity
                        </label>
                        <input type="number" name="quantity[]" class="form-control inv-qty"
                               min="0" step="1" value="1" placeholder="Enter quantity" readonly>
                    </div>

                    <div class="col-md-2 mb-2">
                        <label class="form-label">
                            <i class="bx bx-ruler me-1"></i>Unit Size
                        </label>
                        <input type="text" name="unit_size[]" class="form-control" placeholder="e.g. 1 PC">
                    </div>

                    <div class="col-md-2 mb-2">
                        <label class="form-label">
                            <i class="bx bx-dollar me-1"></i>Price
                        </label>
                        <input type="number" name="price[]" class="form-control inv-price"
                               min="0" step="0.01" value="0.00" placeholder="0.00">
                    </div>

                    <div class="col-md-2 mb-2">
                        <label class="form-label">
                            <i class="bx bx-percent me-1"></i>VAT (%)
                        </label>
                        <input type="number" name="vat[]" class="form-control"
                               min="0" step="0.01" value="0" placeholder="0">
                    </div>

                    <div class="col-md-2 mb-2">
                        <label class="form-label">
                            <i class="bx bx-calculator me-1"></i>Tax (%)
                        </label>
                        <div class="d-flex align-items-end">
                            <input type="number" name="tax[]" class="form-control"
                                   min="0" step="0.01" value="0" placeholder="0">
                            <button type="button" class="btn btn-outline-danger btn-sm ms-2 inv-remove-row" title="Remove Row">
                                <i class="bx bx-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="row">
                    {{-- Row 3: Description --}}
                    <div class="col-md-8 mb-2">
                        <label class="form-label">
                            <i class="bx bx-file me-1"></i>Description
                        </label>
                        <textarea name="description[]" class="form-control" rows="2"
                                  placeholder="Enter item description"></textarea>
                    </div>
                    <div class="col-md-4 mb-2">
                        <label class="form-label">
                            <i class="bx bx-paperclip me-1"></i>Invoice Attachment
                        </label>
                        <input type="file" name="invoice_attachment[]" class="form-control invoice-attachment-input"
                               accept=".jpg,.jpeg,.png,.pdf">
                        <small class="text-muted d-block mt-1">JPG, PNG, PDF (Max 5MB)</small>
                        <div class="invoice-attachment-preview mt-2" style="display:none;"></div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Action Buttons --}}
        <div class="d-flex justify-content-between align-items-center mt-3 mb-4">
            <button type="button" class="btn btn-outline-primary btn-sm px-4" id="invAddRow">
                <i class="bx bx-plus-circle me-1"></i> Add New Row
            </button>
            <button type="submit" class="btn btn-primary btn-sm px-4">
                <i class="bx bx-save me-1"></i> Save Inventory
            </button>
        </div>
    </form>
</div>

    {{-- Item Specifications Modal --}}
    <div class="modal fade" id="itemSpecsModal" tabindex="-1" aria-labelledby="itemSpecsModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-xl modal-dialog-scrollable modal-fullscreen-lg-down">
            <div class="modal-content specs-modal-content">
                <div class="modal-header specs-modal-header">
                    <h5 class="modal-title" id="itemSpecsModalLabel">
                        <i class="bx bx-chip me-1"></i>Item Specifications
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body specs-modal-body">
                    {{-- RAM Section --}}
                    <div class="specs-section mb-3">
                        <div class="specs-section-title"><i class="bx bx-memory-card me-1"></i>RAM</div>
                        <div id="ramRowsContainer">
                            <div class="specs-row-container ram-row">
                                <div class="row g-2">
                                    <div class="col-6 col-md-3 col-xl-2">
                                        <label class="form-label">RAM <span class="text-danger">*</span></label>
                                        <select class="form-select specs-ram inv-modal-select" name="ram[]" required>
                                            <option value="">Select RAM</option>
                                            <option value="ram not found">RAM Not Found</option>
                                            <option value="2 GB">2 GB</option>
                                            <option value="4 GB">4 GB</option>
                                            <option value="8 GB">8 GB</option>
                                            <option value="16 GB">16 GB</option>
                                            <option value="32 GB">32 GB</option>
                                            <option value="64 GB">64 GB</option>
                                            <option value="128 GB">128 GB</option>
                                        </select>
                                    </div>
                                    <div class="col-6 col-md-3 col-xl-2">
                                        <label class="form-label">Brand</label>
                                        <select class="form-select specs-ram-brand inv-modal-select" name="brand_id[]" required>
                                            <option value="">Select Brand</option>
                                            @foreach($brandData as $brand)
                                                <option value="{{$brand->id}}">{{$brand->brand_name}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-6 col-md-3 col-xl-2">
                                        <label class="form-label">Service Warranty</label>
                                        <div class="warranty-ym-group">
                                            <div class="warranty-ym-field">
                                                <span class="warranty-ym-lbl">Year</span>
                                                <input type="number" min="0" step="1" class="form-control warranty-ym-input ram-sw-y" placeholder="0">
                                            </div>
                                            <div class="warranty-ym-field">
                                                <span class="warranty-ym-lbl">Month</span>
                                                <input type="number" min="0" step="1" class="form-control warranty-ym-input ram-sw-m" placeholder="0">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3 col-xl-2">
                                        <label class="form-label">Warranty</label>
                                        <div class="warranty-ym-group">
                                            <div class="warranty-ym-field">
                                                <span class="warranty-ym-lbl">Year</span>
                                                <input type="number" min="0" step="1" class="form-control warranty-ym-input ram-wr-y" placeholder="0">
                                            </div>
                                            <div class="warranty-ym-field">
                                                <span class="warranty-ym-lbl">Month</span>
                                                <input type="number" min="0" step="1" class="form-control warranty-ym-input ram-wr-m" placeholder="0">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3 col-xl-2">
                                        <label class="form-label">Condition</label>
                                        <select class="form-select specs-ram-condition inv-modal-select" name="condition[]">
                                            <option value="">Select Condition</option>
                                            <option value="slow">Slow</option>
                                            <option value="good">Good</option>
                                            <option value="bad">Bad</option>
                                            <option value="damage">Damage</option>
                                            <option value="normal">Normal</option>
                                            <option value="unused">Unused</option>
                                        </select>
                                    </div>
                                    <div class="col-6 col-md-3 col-xl-2">
                                        <label class="form-label">Serial No</label>
                                        <input type="text" class="form-control specs-serial-no" name="serial_no[]" placeholder="e.g. SN-001">
                                    </div>
                                    <div class="col-6 col-md-3 col-xl-2">
                                        <label class="form-label">Quantity</label>
                                        <input type="number" class="form-control specs-quantity" name="quantity[]" min="0" step="1" value="1" readonly>
                                    </div>
                                    <div class="col-6 col-md-3 col-xl-2">
                                        <label class="form-label">Unit Size</label>
                                        <input type="text" class="form-control specs-unit-size" name="unit_size[]" placeholder="e.g. 1 PC">
                                    </div>
                                    <div class="col-6 col-md-3 col-xl-2">
                                        <label class="form-label">Price</label>
                                        <input name="price[]" type="number" class="form-control specs-price" min="0" step="0.01" value="0.00">
                                    </div>
                                    <div class="col-6 col-md-3 col-xl-2">
                                        <label class="form-label">VAT (%)</label>
                                        <input name="vat[]" type="number" class="form-control specs-vat" min="0" step="0.01" value="0">
                                    </div>
                                    <div class="col-6 col-md-3 col-xl-2">
                                        <label class="form-label">Tax (%)</label>
                                        <div class="d-flex align-items-center gap-1">
                                            <input name="tax[]" type="number" class="form-control specs-tax" min="0" step="0.01" value="0">
                                            <button type="button" class="btn btn-outline-danger btn-sm remove-ram-row specs-remove-btn" style="display:none;" title="Remove">
                                                <i class="bx bx-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label">Description</label>
                                        <textarea class="form-control specs-ram-description" name="description[]" rows="2" placeholder="Enter RAM description"></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <button type="button" class="btn btn-outline-primary btn-sm mt-2" id="addRamRow">
                            <i class="bx bx-plus-circle me-1"></i> Add RAM Row
                        </button>
                    </div>

                    {{-- Processor Section --}}
                    <div class="specs-section mb-3">
                        <div class="specs-section-title"><i class="bx bx-cpu me-1"></i>Processor</div>
                        <div id="processorRowsContainer">
                            <div class="specs-row-container processor-row">
                                <div class="row g-2">
                                    <div class="col-6 col-md-3 col-xl-2">
                                        <label class="form-label">Processor <span class="text-danger">*</span></label>
                                        <select class="form-select specs-processor inv-modal-select" name="processor[]" required>
                                            <option value="">Select Processor</option>
                                            <option value="processor not found">Processor Not Found</option>
                                            <option value="Intel Core i3">Intel Core i3</option>
                                            <option value="Intel Core i5">Intel Core i5</option>
                                            <option value="Intel Core i7">Intel Core i7</option>
                                            <option value="Intel Core i9">Intel Core i9</option>
                                            <option value="AMD Ryzen 3">AMD Ryzen 3</option>
                                            <option value="AMD Ryzen 5">AMD Ryzen 5</option>
                                            <option value="AMD Ryzen 7">AMD Ryzen 7</option>
                                            <option value="AMD Ryzen 9">AMD Ryzen 9</option>
                                            <option value="Intel Pentium">Intel Pentium</option>
                                            <option value="Intel Celeron">Intel Celeron</option>
                                            <option value="Apple M1">Apple M1</option>
                                            <option value="Apple M2">Apple M2</option>
                                        </select>
                                    </div>
                                    <div class="col-6 col-md-3 col-xl-2">
                                        <label class="form-label">Brand</label>
                                        <select class="form-select specs-processor-brand inv-modal-select" name="brand_id[]" required>
                                            <option value="">Select Brand</option>
                                            @foreach($brandData as $brand)
                                                <option value="{{$brand->id}}">{{$brand->brand_name}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-6 col-md-3 col-xl-2">
                                        <label class="form-label">Service Warranty</label>
                                        <div class="warranty-ym-group">
                                            <div class="warranty-ym-field">
                                                <span class="warranty-ym-lbl">Year</span>
                                                <input type="number" min="0" step="1" class="form-control warranty-ym-input proc-sw-y" placeholder="0">
                                            </div>
                                            <div class="warranty-ym-field">
                                                <span class="warranty-ym-lbl">Month</span>
                                                <input type="number" min="0" step="1" class="form-control warranty-ym-input proc-sw-m" placeholder="0">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3 col-xl-2">
                                        <label class="form-label">Warranty</label>
                                        <div class="warranty-ym-group">
                                            <div class="warranty-ym-field">
                                                <span class="warranty-ym-lbl">Year</span>
                                                <input type="number" min="0" step="1" class="form-control warranty-ym-input proc-wr-y" placeholder="0">
                                            </div>
                                            <div class="warranty-ym-field">
                                                <span class="warranty-ym-lbl">Month</span>
                                                <input type="number" min="0" step="1" class="form-control warranty-ym-input proc-wr-m" placeholder="0">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3 col-xl-2">
                                        <label class="form-label">Condition</label>
                                        <select class="form-select specs-processor-condition inv-modal-select" name="condition[]">
                                            <option value="">Select Condition</option>
                                            <option value="slow">Slow</option>
                                            <option value="good">Good</option>
                                            <option value="bad">Bad</option>
                                            <option value="damage">Damage</option>
                                            <option value="normal">Normal</option>
                                            <option value="unused">Unused</option>
                                        </select>
                                    </div>
                                    <div class="col-6 col-md-3 col-xl-2">
                                        <label class="form-label">Serial No</label>
                                        <input type="text" class="form-control specs-processor-serial-no" name="serial_no[]" placeholder="e.g. SN-001">
                                    </div>
                                    <div class="col-6 col-md-3 col-xl-2">
                                        <label class="form-label">Quantity</label>
                                        <input type="number" class="form-control specs-processor-quantity" name="quantity[]" min="0" step="1" value="1" readonly>
                                    </div>
                                    <div class="col-6 col-md-3 col-xl-2">
                                        <label class="form-label">Unit Size</label>
                                        <input type="text" class="form-control specs-processor-unit-size" name="unit_size[]" placeholder="e.g. 1 PC">
                                    </div>
                                    <div class="col-6 col-md-3 col-xl-2">
                                        <label class="form-label">Price</label>
                                        <input name="price[]" type="number" class="form-control specs-processor-price" min="0" step="0.01" value="0.00">
                                    </div>
                                    <div class="col-6 col-md-3 col-xl-2">
                                        <label class="form-label">VAT (%)</label>
                                        <input name="vat[]" type="number" class="form-control specs-processor-vat" min="0" step="0.01" value="0">
                                    </div>
                                    <div class="col-6 col-md-3 col-xl-2">
                                        <label class="form-label">Tax (%)</label>
                                        <div class="d-flex align-items-center gap-1">
                                            <input name="tax[]" type="number" class="form-control specs-processor-tax" min="0" step="0.01" value="0">
                                            <button type="button" class="btn btn-outline-danger btn-sm remove-processor-row specs-remove-btn" style="display:none;" title="Remove">
                                                <i class="bx bx-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label">Description</label>
                                        <textarea class="form-control specs-processor-description" name="description[]" rows="2" placeholder="Enter Processor description"></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <button type="button" class="btn btn-outline-primary btn-sm mt-2" id="addProcessorRow">
                            <i class="bx bx-plus-circle me-1"></i> Add Processor Row
                        </button>
                    </div>

                    {{-- HDD Section --}}
                    <div class="specs-section mb-2">
                        <div class="specs-section-title"><i class="bx bx-hdd me-1"></i>HDD / SSD</div>
                        <div id="hddRowsContainer">
                            <div class="specs-row-container hdd-row">
                                <div class="row g-2">
                                    <div class="col-6 col-md-3 col-xl-2">
                                        <label class="form-label"><span class="storage-label-text">Storage</span> <span class="text-danger">*</span></label>
                                        <select class="form-select specs-hdd inv-modal-select" name="storage[]" required>
                                            <option value="">Select Storage</option>
                                            <option value="hard disk not found">Hard Disk Not Found</option>
                                            <option value="128 GB SSD">128 GB SSD</option>
                                            <option value="256 GB SSD">256 GB SSD</option>
                                            <option value="512 GB SSD">512 GB SSD</option>
                                            <option value="1 TB SSD">1 TB SSD</option>
                                            <option value="2 TB SSD">2 TB SSD</option>
                                            <option value="500 GB HDD">500 GB HDD</option>
                                            <option value="256 GB HDD">256 GB HDD</option>
                                            <option value="1 TB HDD">1 TB HDD</option>
                                            <option value="2 TB HDD">2 TB HDD</option>
                                            <option value="4 TB HDD">4 TB HDD</option>
                                            <option value="128 GB SSD + 1 TB HDD">128 GB SSD + 1 TB HDD</option>
                                            <option value="256 GB SSD + 1 TB HDD">256 GB SSD + 1 TB HDD</option>
                                            <option value="512 GB SSD + 1 TB HDD">512 GB SSD + 1 TB HDD</option>
                                        </select>
                                    </div>
                                    <div class="col-6 col-md-3 col-xl-2">
                                        <label class="form-label">Brand</label>
                                        <select class="form-select specs-hdd-brand inv-modal-select" name="brand_id[]" required>
                                            <option value="">Select Brand</option>
                                            @foreach($brandData as $brand)
                                                <option value="{{$brand->id}}">{{$brand->brand_name}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-6 col-md-3 col-xl-2">
                                        <label class="form-label">Service Warranty</label>
                                        <div class="warranty-ym-group">
                                            <div class="warranty-ym-field">
                                                <span class="warranty-ym-lbl">Year</span>
                                                <input type="number" min="0" step="1" class="form-control warranty-ym-input hdd-sw-y" placeholder="0">
                                            </div>
                                            <div class="warranty-ym-field">
                                                <span class="warranty-ym-lbl">Month</span>
                                                <input type="number" min="0" step="1" class="form-control warranty-ym-input hdd-sw-m" placeholder="0">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3 col-xl-2">
                                        <label class="form-label">Warranty</label>
                                        <div class="warranty-ym-group">
                                            <div class="warranty-ym-field">
                                                <span class="warranty-ym-lbl">Year</span>
                                                <input type="number" min="0" step="1" class="form-control warranty-ym-input hdd-wr-y" placeholder="0">
                                            </div>
                                            <div class="warranty-ym-field">
                                                <span class="warranty-ym-lbl">Month</span>
                                                <input type="number" min="0" step="1" class="form-control warranty-ym-input hdd-wr-m" placeholder="0">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3 col-xl-2">
                                        <label class="form-label">Condition</label>
                                        <select class="form-select specs-hdd-condition inv-modal-select" name="condition[]">
                                            <option value="">Select Condition</option>
                                            <option value="slow">Slow</option>
                                            <option value="good">Good</option>
                                            <option value="bad">Bad</option>
                                            <option value="damage">Damage</option>
                                            <option value="normal">Normal</option>
                                        </select>
                                    </div>
                                    <div class="col-6 col-md-3 col-xl-2">
                                        <label class="form-label">Usage Status</label>
                                        <select class="form-select specs-hdd-usage-status inv-modal-select" name="usage_status[]">
                                            <option value="">Select Usage Status</option>
                                            <option value="used">Used</option>
                                            <option value="unused">Unused</option>
                                        </select>
                                    </div>
                                    <div class="col-6 col-md-3 col-xl-2">
                                        <label class="form-label">Serial No</label>
                                        <input type="text" class="form-control specs-hdd-serial-no" name="serial_no[]" placeholder="e.g. SN-001">
                                    </div>
                                    <div class="col-6 col-md-3 col-xl-2">
                                        <label class="form-label">Quantity</label>
                                        <input type="number" class="form-control specs-hdd-quantity" name="quantity[]" min="0" step="1" value="1" readonly>
                                    </div>
                                    <div class="col-6 col-md-3 col-xl-2">
                                        <label class="form-label">Unit Size</label>
                                        <input type="text" class="form-control specs-hdd-unit-size" name="unit_size[]" placeholder="e.g. 1 PC">
                                    </div>
                                    <div class="col-6 col-md-3 col-xl-2">
                                        <label class="form-label">Price</label>
                                        <input name="price[]" type="number" class="form-control specs-hdd-price" min="0" step="0.01" value="0.00">
                                    </div>
                                    <div class="col-6 col-md-3 col-xl-2">
                                        <label class="form-label">VAT (%)</label>
                                        <input name="vat[]" type="number" class="form-control specs-hdd-vat" min="0" step="0.01" value="0">
                                    </div>
                                    <div class="col-6 col-md-3 col-xl-2">
                                        <label class="form-label">Tax (%)</label>
                                        <div class="d-flex align-items-center gap-1">
                                            <input type="number" class="form-control specs-hdd-tax" name="tax[]" min="0" step="0.01" value="0">
                                            <button type="button" class="btn btn-outline-danger btn-sm remove-hdd-row specs-remove-btn" style="display:none;" title="Remove">
                                                <i class="bx bx-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label">Description</label>
                                        <textarea class="form-control specs-hdd-description" name="description[]" rows="2" placeholder="Enter HDD/SSD description"></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <button type="button" class="btn btn-outline-primary btn-sm mt-2" id="addHddRow">
                            <i class="bx bx-plus-circle me-1"></i> Add HDD Row
                        </button>
                    </div>
                </div>
                <div class="modal-footer specs-modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary px-4" id="saveSpecsBtn">
                        <i class="bx bx-check me-1"></i> Add
                    </button>
                </div>
            </div>
        </div>
    </div>

                <hr/>
                <strong style="margin-left: 35%;">
                    <?php
                    echo session()->get('eMessage');
                    session()->forget('eMessage');
                    ?>
                </strong>
                <strong id="book"></strong>
                <table id="example" class="table table-striped" style="width:100%">
                    <thead>
                    <tr>
                        <th>SL</th>
                        <th>Item</th>
{{--                        <th>Size</th>--}}
                        <th>Entry SL</th>
                        <th>Serial No</th>
                        <th>Brand Name</th>

                        <th>Category</th>
                        <th>Supplier Name</th>
                        <th>Entry User Name</th>
                        <th>Action</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($itemInventoryData as $value)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $value->item_title }}</td>
                            <td>{{ $value->set_uniq_no }}</td>
                            <td>{{ $value->uniq_serial_no }}</td>
                            <td>{{ $value->brand_name }}</td>
                            <td>{{ $value->category_title }}</td>
                            <td>{{ $value->supplier_name }}</td>
                            <td>{{ $value->name }}</td>
{{--                            <td><img src="{{asset($value->image)}}" width="90"/> </td>--}}
                            <td>
                                @if($user_access->is_edit==1)
                                    <button onclick="edit({{$value->id}})">
                                        <i class="fadeIn animated bx bx-edit-alt" data-feather="edit"></i>
                                    </button>
                                @endif
                                @if($user_access->is_delete==1)
                                    <button>
                                        <a href="{{route('item-delete',$value->id)}}">
                                            <i class="fadeIn animated bx bx-trash-alt" data-feather="trash-2"></i></a>
                                    </button>
                                @endif
                                @if($value->status==1 && $user_access->is_approved==1)
                                    <button>
                                        <a href="{{route('item-in-active',$value->id)}}">
                                            <i class="fadeIn animated bx bx-comment-check"
                                               data-feather="check-square"></i>
                                        </a>
                                    </button>
                                @elseif($user_access->is_approved==1)
                                    <button>
                                        <a href="{{route('item-active',$value->id)}}">
                                            <i class="fadeIn animated bx bx-comment-x" data-feather="x-square"></i>
                                        </a>
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @endforeach

                    </tbody>
                    <tfoot>

                    </tfoot>
                </table>
            </div>
                </div>
            </div>


        </div>
    </div>
    <script>
    window.addEventListener('load', function () {
        if (typeof window.jQuery === 'undefined') {
            return;
        }
        var $ = window.jQuery;

        function getSelectPlaceholder($sel) {
            const emptyText = $.trim($sel.find('option[value=""]').first().text() || '');
            return emptyText || 'Select';
        }

        function initInvSelect2(scope) {
            if (typeof $.fn.select2 === 'undefined') {
                return;
            }

            const $scope = scope ? $(scope) : $('#inventoryRowsContainer');
            $scope.find('select.inv-main-select').each(function () {
                const $sel = $(this);
                if ($sel.hasClass('select2-hidden-accessible')) {
                    $sel.select2('destroy');
                }
                $sel.next('.select2').remove();
                $sel.removeClass('select2-hidden-accessible').removeAttr('data-select2-id').removeAttr('aria-hidden');
                $sel.find('option').removeAttr('data-select2-id');
                $sel.select2({
                    theme: 'bootstrap4',
                    placeholder: getSelectPlaceholder($sel),
                    allowClear: true,
                    width: '100%'
                });
            });
        }

        function cleanClonedSelect2($row) {
            $row.find('span.select2').remove();
            $row.find('select').each(function () {
                const $select = $(this);
                $select.removeClass('select2-hidden-accessible')
                    .removeAttr('data-select2-id')
                    .removeAttr('aria-hidden')
                    .show();
                $select.find('option').removeAttr('data-select2-id');
            });
        }

        function initModalSelect2(scope) {
            if (typeof $.fn.select2 === 'undefined') {
                return;
            }

            const $scope = scope ? $(scope) : $('#itemSpecsModal');
            $scope.find('select.inv-modal-select').each(function () {
                const $sel = $(this);
                if ($sel.hasClass('select2-hidden-accessible')) {
                    try { $sel.select2('destroy'); } catch (e) {}
                }
                $sel.next('.select2').remove();
                $sel.removeClass('select2-hidden-accessible')
                    .removeAttr('data-select2-id')
                    .removeAttr('aria-hidden')
                    .show();
                $sel.find('option').removeAttr('data-select2-id');

                $sel.select2({
                    theme: 'bootstrap4',
                    placeholder: getSelectPlaceholder($sel),
                    allowClear: true,
                    width: '100%',
                    dropdownParent: $('#itemSpecsModal .modal-content')
                });
            });
        }

        initInvSelect2();

        function isComputerItem(itemValue) {
            return itemValue === '4' || itemValue === '5' || itemValue === '6';
        }

        function formatWarrantyYearsMonthsFromInputs($y, $m) {
            const y = parseInt(String($y.val() == null ? '' : $y.val()).trim(), 10);
            const mo = parseInt(String($m.val() == null ? '' : $m.val()).trim(), 10);
            const parts = [];
            if (!isNaN(y) && y > 0) {
                parts.push(y + ' ' + (y === 1 ? 'Year' : 'Years'));
            }
            if (!isNaN(mo) && mo > 0) {
                parts.push(mo + ' ' + (mo === 1 ? 'Month' : 'Months'));
            }
            return parts.join(', ');
        }

        function warrantyYmFromRow($row, yClass, mClass) {
            return formatWarrantyYearsMonthsFromInputs($row.find(yClass), $row.find(mClass));
        }

        function getStorageTypeFromValue(storageValue) {
            const value = (storageValue || '').toUpperCase();
            if (value.indexOf('SSD') !== -1 && value.indexOf('HDD') === -1) {
                return 'SSD';
            }
            if (value.indexOf('HDD') !== -1 && value.indexOf('SSD') === -1) {
                return 'HDD';
            }
            if (value.indexOf('SSD') !== -1) {
                return 'SSD+HDD';
            }
            return 'Storage';
        }

        function updateStorageLabels(scope) {
            const $scope = scope ? $(scope) : $(document);
            $scope.find('.hdd-row').each(function () {
                const storageValue = $(this).find('.specs-hdd').val();
                const type = getStorageTypeFromValue(storageValue);
                let labelText = 'Storage (HDD/SSD)';
                if (type === 'SSD') {
                    labelText = 'SSD';
                } else if (type === 'HDD') {
                    labelText = 'HDD';
                } else if (type === 'SSD+HDD') {
                    labelText = 'SSD + HDD';
                }
                $(this).find('.storage-label-text').text(labelText);
            });
        }

        // Add new row
        $('#invAddRow').on('click', function () {
            const lastRow = $('#inventoryRowsContainer .inv-row-container:last');
            const newRow = lastRow.clone();

            // Update row index
            const rowCount = $('#inventoryRowsContainer .inv-row-container').length;
            newRow.attr('data-row-index', rowCount);

            // Remove any cloned select2 containers from the new row
            cleanClonedSelect2(newRow);

            // Reset all form values
            newRow.find('select').each(function () {
                $(this).val('');
            });

            newRow.find('input').val('');
            newRow.find('textarea').val('');
            newRow.find('.invoice-attachment-preview').hide().empty();
            newRow.find('.inv-qty').val('1');
            newRow.find('.inv-price').val('0.00');
            newRow.find('input[name="vat[]"]').val('0');
            newRow.find('input[name="tax[]"]').val('0');

            // Clear specs container in cloned row
            newRow.find('.item-specs-container').hide().empty();

            // Append new row to DOM
            $('#inventoryRowsContainer').append(newRow);

            // Re-initialize searchable selects in the new row
            initInvSelect2(newRow);
        });

        // Remove row (keep at least one)
        $(document).on('click', '.inv-remove-row', function () {
            const rows = $('#inventoryRowsContainer .inv-row-container').length;
            if (rows > 1) {
                $(this).closest('.inv-row-container').fadeOut(300, function() {
                    $(this).remove();
                });
            } else {
                alert('At least one row is required!');
            }
        });

        // Category -> Item (AJAX). Skipped when category was auto-set from item selection.
        $(document).on('change', '.inv-category', function () {
            const $category = $(this);
            if ($category.data('skip-item-reload')) {
                $category.data('skip-item-reload', false);
                return;
            }

            const categoryId = $category.val();
            const row = $category.closest('.inv-row-container');
            const itemSelect = row.find('.inv-item');

            itemSelect.empty().append('<option value="">Select Item</option>');

            if (!categoryId) {
                initInvSelect2(row);
                return;
            }

            $.ajax({
                url: "{{ url('/items-by-category') }}/" + categoryId,
                type: 'GET',
                dataType: 'json',
                success: function (items) {
                    $.each(items, function (i, item) {
                        const $opt = $('<option>', {
                            value: item.id,
                            text: item.item_title ?? item.item
                        });
                        $opt.attr('data-category-id', item.category_id ?? categoryId);
                        itemSelect.append($opt);
                    });
                    initInvSelect2(row);
                },
                error: function () {
                    console.error('Failed to load items by category');
                }
            });
        });

        // Item select: auto-select category, then open specs modal for Desktop/Laptop/Server
        $(document).on('change', '.inv-item', function () {
            const $item = $(this);
            const itemValue = $item.val();
            const row = $item.closest('.inv-row-container');
            const specsContainer = row.find('.item-specs-container');
            const $category = row.find('.inv-category');
            const $selected = $item.find('option:selected');
            const categoryId = String($selected.attr('data-category-id') || $selected.data('category-id') || '').trim();

            if (categoryId && String($category.val() || '') !== categoryId) {
                $category.data('skip-item-reload', true);
                $category.val(categoryId).trigger('change');
            }

            // Default condition to Good when an item is selected
            const $condition = row.find('.inv-condition');
            if (itemValue) {
                $condition.val('good');
            } else {
                $condition.val('');
            }

            // Clear previous specs display
            specsContainer.hide().empty();

            // Check if value is 4, 5, or 6
            if (isComputerItem(itemValue)) {
                // Open modal (backdrop static — dismissed only via header/footer buttons)
                const $modal = $('#itemSpecsModal');
                $modal.data('target-container', specsContainer);
                $modal.data('target-row', row);
                $modal.modal('show');
            } else {
                // If value is not 4, 5, or 6, ensure specs are cleared
                specsContainer.hide().empty();
            }
        });

        $('#itemSpecsModal').on('shown.bs.modal', function () {
            initModalSelect2();
        });

        $('#itemSpecsModal').on('show.bs.modal', function () {
            $(this).data('closed-with-save', false);
        });

        // Invoice attachment preview (image/pdf)
        $(document).on('change', '.invoice-attachment-input', function () {
            const file = this.files && this.files[0] ? this.files[0] : null;
            const previewContainer = $(this).closest('.col-md-4').find('.invoice-attachment-preview');
            previewContainer.hide().empty();

            if (!file) {
                return;
            }

            const fileName = file.name || 'Attachment';
            const fileType = (file.type || '').toLowerCase();
            const objectUrl = URL.createObjectURL(file);

            if (fileType.indexOf('image/') === 0) {
                const imgHtml =
                    '<div class="border rounded p-2 bg-light">' +
                        '<div class="small fw-bold mb-1 text-truncate" title="' + fileName + '">' + fileName + '</div>' +
                        '<img src="' + objectUrl + '" alt="Invoice Preview" style="width:100%; max-height:160px; object-fit:contain;">' +
                    '</div>';
                previewContainer.html(imgHtml).show();
            } else if (fileType === 'application/pdf' || fileName.toLowerCase().endsWith('.pdf')) {
                const pdfHtml =
                    '<div class="border rounded p-2 bg-light">' +
                        '<div class="small fw-bold mb-1 text-truncate" title="' + fileName + '">' + fileName + '</div>' +
                        '<a href="' + objectUrl + '" target="_blank" class="btn btn-outline-primary btn-sm">' +
                            '<i class="bx bx-show me-1"></i>Preview PDF' +
                        '</a>' +
                    '</div>';
                previewContainer.html(pdfHtml).show();
            } else {
                const fallbackHtml =
                    '<div class="border rounded p-2 bg-light small text-muted">' +
                        'Preview not available for this file type. Selected: ' + fileName +
                    '</div>';
                previewContainer.html(fallbackHtml).show();
            }
        });

        $(document).on('change', '.specs-hdd', function () {
            updateStorageLabels($(this).closest('.hdd-row'));
        });

        // Add RAM row
        $('#addRamRow').on('click', function () {
            const firstRow = $('#ramRowsContainer .ram-row:first');
            const newRow = firstRow.clone();
            cleanClonedSelect2(newRow);

            // Reset all values
            newRow.find('select').val('');
            newRow.find('input[type="text"]').val('');
            newRow.find('textarea').val('');
            newRow.find('.warranty-ym-input').val('');
            newRow.find('input[type="number"]').not('.warranty-ym-input').each(function() {
                if ($(this).hasClass('specs-quantity')) {
                    $(this).val('1');
                } else if ($(this).hasClass('specs-price')) {
                    $(this).val('0.00');
                } else {
                    $(this).val('0');
                }
            });

            // Show remove button on the new row
            newRow.find('.remove-ram-row').show();

            // Append new row
            $('#ramRowsContainer').append(newRow);
            initModalSelect2(newRow);

            // Show remove button on all rows (including first one if multiple rows exist)
            if ($('#ramRowsContainer .ram-row').length > 1) {
                $('#ramRowsContainer .ram-row .remove-ram-row').show();
            }
        });

        // Remove RAM row
        $(document).on('click', '.remove-ram-row', function () {
            const ramRows = $('#ramRowsContainer .ram-row').length;
            if (ramRows > 1) {
                $(this).closest('.ram-row').remove();
                // Show/hide remove buttons based on remaining rows
                if ($('#ramRowsContainer .ram-row').length === 1) {
                    $('#ramRowsContainer .ram-row .remove-ram-row').hide();
                } else {
                    $('#ramRowsContainer .ram-row .remove-ram-row').show();
                }
            } else {
                alert('At least one RAM row is required!');
            }
        });

        // Add Processor row
        $('#addProcessorRow').on('click', function () {
            const firstRow = $('#processorRowsContainer .processor-row:first');
            const newRow = firstRow.clone();
            cleanClonedSelect2(newRow);

            // Reset all values
            newRow.find('select').val('');
            newRow.find('input[type="text"]').val('');
            newRow.find('textarea').val('');
            newRow.find('.warranty-ym-input').val('');
            newRow.find('input[type="number"]').not('.warranty-ym-input').each(function() {
                if ($(this).hasClass('specs-processor-quantity')) {
                    $(this).val('1');
                } else if ($(this).hasClass('specs-processor-price')) {
                    $(this).val('0.00');
                } else {
                    $(this).val('0');
                }
            });

            // Show remove button on the new row
            newRow.find('.remove-processor-row').show();

            // Append new row
            $('#processorRowsContainer').append(newRow);
            initModalSelect2(newRow);

            // Show remove button on all rows (including first one if multiple rows exist)
            if ($('#processorRowsContainer .processor-row').length > 1) {
                $('#processorRowsContainer .processor-row .remove-processor-row').show();
            }
        });

        // Remove Processor row
        $(document).on('click', '.remove-processor-row', function () {
            const processorRows = $('#processorRowsContainer .processor-row').length;
            if (processorRows > 1) {
                $(this).closest('.processor-row').remove();
                // Show/hide remove buttons based on remaining rows
                if ($('#processorRowsContainer .processor-row').length === 1) {
                    $('#processorRowsContainer .processor-row .remove-processor-row').hide();
                } else {
                    $('#processorRowsContainer .processor-row .remove-processor-row').show();
                }
            } else {
                alert('At least one Processor row is required!');
            }
        });

        // Add HDD row
        $('#addHddRow').on('click', function () {
            const firstRow = $('#hddRowsContainer .hdd-row:first');
            const newRow = firstRow.clone();
            cleanClonedSelect2(newRow);

            // Reset all values
            newRow.find('select').val('');
            newRow.find('input[type="text"]').val('');
            newRow.find('textarea').val('');
            newRow.find('.warranty-ym-input').val('');
            newRow.find('input[type="number"]').not('.warranty-ym-input').each(function() {
                if ($(this).hasClass('specs-hdd-quantity')) {
                    $(this).val('1');
                } else if ($(this).hasClass('specs-hdd-price')) {
                    $(this).val('0.00');
                } else {
                    $(this).val('0');
                }
            });

            // Show remove button on the new row
            newRow.find('.remove-hdd-row').show();

            // Append new row
            $('#hddRowsContainer').append(newRow);
            updateStorageLabels(newRow);
            initModalSelect2(newRow);

            // Show remove button on all rows (including first one if multiple rows exist)
            if ($('#hddRowsContainer .hdd-row').length > 1) {
                $('#hddRowsContainer .hdd-row .remove-hdd-row').show();
            }
        });

        // Remove HDD row
        $(document).on('click', '.remove-hdd-row', function () {
            const hddRows = $('#hddRowsContainer .hdd-row').length;
            if (hddRows > 1) {
                $(this).closest('.hdd-row').remove();
                // Show/hide remove buttons based on remaining rows
                if ($('#hddRowsContainer .hdd-row').length === 1) {
                    $('#hddRowsContainer .hdd-row .remove-hdd-row').hide();
                } else {
                    $('#hddRowsContainer .hdd-row .remove-hdd-row').show();
                }
            } else {
                alert('At least one HDD row is required!');
            }
        });

        // Save specs and display below select box
        $('#saveSpecsBtn').on('click', function () {
            const specsContainer = $('#itemSpecsModal').data('target-container');
            const targetRow = $('#itemSpecsModal').data('target-row');
            const specsData = [];

            // Collect RAM data from all RAM rows
            $('#ramRowsContainer .ram-row').each(function () {
                const ram = $(this).find('.specs-ram').val();
                if (ram) {
                    const ramBrandSelect = $(this).find('.specs-ram-brand');
                    const ramBrand = ramBrandSelect.val();
                    const ramBrandName = ramBrandSelect.find('option:selected').text() || '';
                    const conditionValue = $(this).find('.specs-ram-condition').val() || '';

                    specsData.push({
                        type: 'RAM',
                        value: ram,
                        brand_id: ramBrand,
                        brand_name: ramBrandName,
                        service_warranty: warrantyYmFromRow($(this), '.ram-sw-y', '.ram-sw-m') || '',
                        warranty: warrantyYmFromRow($(this), '.ram-wr-y', '.ram-wr-m') || '',
                        serial_no: $(this).find('.specs-serial-no').val() || '',
                        quantity: $(this).find('.specs-quantity').val() || '1',
                        unit_size: $(this).find('.specs-unit-size').val() || '',
                        price: $(this).find('.specs-price').val() || '0.00',
                        vat: $(this).find('.specs-vat').val() || '0',
                        tax: $(this).find('.specs-tax').val() || '0',
                        condition: conditionValue,
                        description: $(this).find('.specs-ram-description').val() || ''
                    });
                }
            });

            // Collect Processor data from all Processor rows
            $('#processorRowsContainer .processor-row').each(function () {
                const processor = $(this).find('.specs-processor').val();
                if (processor) {
                    const processorBrandSelect = $(this).find('.specs-processor-brand');
                    const processorBrand = processorBrandSelect.val();
                    const processorBrandName = processorBrandSelect.find('option:selected').text() || '';
                    const conditionValue = $(this).find('.specs-processor-condition').val() || '';

                    specsData.push({
                        type: 'Processor',
                        value: processor,
                        brand_id: processorBrand,
                        brand_name: processorBrandName,
                        service_warranty: warrantyYmFromRow($(this), '.proc-sw-y', '.proc-sw-m') || '',
                        warranty: warrantyYmFromRow($(this), '.proc-wr-y', '.proc-wr-m') || '',
                        serial_no: $(this).find('.specs-processor-serial-no').val() || '',
                        quantity: $(this).find('.specs-processor-quantity').val() || '1',
                        unit_size: $(this).find('.specs-processor-unit-size').val() || '',
                        price: $(this).find('.specs-processor-price').val() || '0.00',
                        vat: $(this).find('.specs-processor-vat').val() || '0',
                        tax: $(this).find('.specs-processor-tax').val() || '0',
                        condition: conditionValue,
                        description: $(this).find('.specs-processor-description').val() || ''
                    });
                }
            });

            // Collect HDD data from all HDD rows
            $('#hddRowsContainer .hdd-row').each(function () {
                const hdd = $(this).find('.specs-hdd').val();
                if (hdd) {
                    const hddBrandSelect = $(this).find('.specs-hdd-brand');
                    const hddBrand = hddBrandSelect.val();
                    const hddBrandName = hddBrandSelect.find('option:selected').text() || '';
                    const conditionValue = $(this).find('.specs-hdd-condition').val() || '';

                    specsData.push({
                        type: 'HDD',
                        storage_type: getStorageTypeFromValue(hdd),
                        type_display: getStorageTypeFromValue(hdd) === 'Storage' ? 'Storage' : getStorageTypeFromValue(hdd),
                        value: hdd,
                        brand_id: hddBrand,
                        brand_name: hddBrandName,
                        service_warranty: warrantyYmFromRow($(this), '.hdd-sw-y', '.hdd-sw-m') || '',
                        warranty: warrantyYmFromRow($(this), '.hdd-wr-y', '.hdd-wr-m') || '',
                        serial_no: $(this).find('.specs-hdd-serial-no').val() || '',
                        quantity: $(this).find('.specs-hdd-quantity').val() || '1',
                        unit_size: $(this).find('.specs-hdd-unit-size').val() || '',
                        price: $(this).find('.specs-hdd-price').val() || '0.00',
                        vat: $(this).find('.specs-hdd-vat').val() || '0',
                        tax: $(this).find('.specs-hdd-tax').val() || '0',
                        condition: conditionValue,
                        description: $(this).find('.specs-hdd-description').val() || ''
                    });
                }
            });

            if (specsData.length === 0) {
                alert('Please add at least one specification (RAM, Processor, or HDD)!');
                return;
            }

            const targetItemValue = targetRow ? (targetRow.find('.inv-item').val() || '') : '';
            if (isComputerItem(targetItemValue)) {
                const hasRam = specsData.some(function (spec) { return spec.type === 'RAM' && spec.value; });
                const hasProcessor = specsData.some(function (spec) { return spec.type === 'Processor' && spec.value; });
                const hasStorage = specsData.some(function (spec) { return spec.type === 'HDD' && spec.value; });

                if (!hasRam || !hasProcessor || !hasStorage) {
                    alert('Desktop/Laptop/Server item এর জন্য RAM, Processor এবং HDD/SSD mandatory.');
                    return;
                }
            }

            // Display specs below select box
            let specsHtml = '<div class="card mt-2">' +
                '<div class="card-body p-2">' +
                '<h6 class="card-title mb-2"><i class="bx bx-chip me-1"></i>Item Specifications:</h6>' +
                '<div class="table-responsive">' +
                '<table class="table table-sm table-bordered mb-0">' +
                '<thead class="table-light">' +
                '<tr>' +
                '<th>Type</th>' +
                '<th>Value</th>' +
                '<th>Brand</th>' +
                '<th>Service Warranty</th>' +
                '<th>Warranty</th>' +
                '<th>Condition</th>' +
                '<th>Serial No</th>' +
                '<th>Quantity</th>' +
                '<th>Unit Size</th>' +
                '<th>Price</th>' +
                '<th>VAT (%)</th>' +
                '<th>Tax (%)</th>' +
                '<th>Description</th>' +
                '</tr>' +
                '</thead>' +
                '<tbody>';

            specsData.forEach(function(spec) {
                // Format condition value - capitalize first letter
                let conditionValue = spec.condition || '';
                if (conditionValue) {
                    conditionValue = conditionValue.charAt(0).toUpperCase() + conditionValue.slice(1);
                }

                specsHtml += '<tr>' +
                    '<td>' + (spec.type_display || spec.type || '-') + '</td>' +
                    '<td>' + (spec.value || '-') + '</td>' +
                    '<td>' + (spec.brand_name || '-') + '</td>' +
                    '<td>' + (spec.service_warranty || '-') + '</td>' +
                    '<td>' + (spec.warranty || '-') + '</td>' +
                    '<td>' + (conditionValue || '-') + '</td>' +
                    '<td>' + (spec.serial_no || '-') + '</td>' +
                    '<td>' + (spec.quantity || '-') + '</td>' +
                    '<td>' + (spec.unit_size || '-') + '</td>' +
                    '<td>' + (spec.price || '-') + '</td>' +
                    '<td>' + (spec.vat || '-') + '</td>' +
                    '<td>' + (spec.tax || '-') + '</td>' +
                    '<td>' + (spec.description || '-') + '</td>' +
                    '</tr>';
            });

            specsHtml += '</tbody></table></div></div></div>';

            // Store specs data as hidden input for form submission
            specsHtml += '<input type="hidden" name="item_specs[]" value=\'' + JSON.stringify(specsData) + '\'>';

            specsContainer.html(specsHtml).show();

            // Close modal and reset form (mark saved so hidden.bs.modal does not clear item)
            const $specModal = $('#itemSpecsModal');
            $specModal.data('closed-with-save', true);
            $specModal.modal('hide');
            resetModalForm();
        });

        $('#inventoryForm').on('submit', function (e) {
            let hasError = false;

            $('#inventoryRowsContainer .inv-row-container').each(function (rowIndex) {
                if (hasError) {
                    return false;
                }

                const itemValue = ($(this).find('.inv-item').val() || '').toString();
                if (!isComputerItem(itemValue)) {
                    return;
                }

                const specsInput = $(this).find('input[name="item_specs[]"]').first();
                if (!specsInput.length || !specsInput.val()) {
                    hasError = true;
                    alert('Row ' + (rowIndex + 1) + ': Desktop/Laptop/Server item এর জন্য RAM, Processor এবং HDD/SSD mandatory.');
                    return false;
                }

                let specsData = [];
                try {
                    specsData = JSON.parse(specsInput.val());
                } catch (err) {
                    hasError = true;
                    alert('Row ' + (rowIndex + 1) + ': Item specifications invalid. আবার Add করুন।');
                    return false;
                }

                const hasRam = specsData.some(function (spec) { return spec.type === 'RAM' && spec.value; });
                const hasProcessor = specsData.some(function (spec) { return spec.type === 'Processor' && spec.value; });
                const hasStorage = specsData.some(function (spec) {
                    return (spec.type === 'HDD' || spec.type === 'SSD' || spec.type === 'Storage') && spec.value;
                });

                if (!hasRam || !hasProcessor || !hasStorage) {
                    hasError = true;
                    alert('Row ' + (rowIndex + 1) + ': Desktop/Laptop/Server item এর জন্য RAM, Processor এবং HDD/SSD mandatory.');
                    return false;
                }
            });

            if (hasError) {
                e.preventDefault();
                return false;
            }
        });

        // Function to reset modal form
        function resetModalForm() {
            // Reset RAM rows - keep only first, remove others
            $('#ramRowsContainer .ram-row:not(:first)').remove();
            $('#ramRowsContainer .ram-row:first').find('select').val('');
            $('#ramRowsContainer .ram-row:first').find('input[type="text"]').val('');
            $('#ramRowsContainer .ram-row:first').find('textarea').val('');
            $('#ramRowsContainer .ram-row:first').find('.warranty-ym-input').val('');
            $('#ramRowsContainer .ram-row:first').find('input[type="number"]').not('.warranty-ym-input').each(function() {
                if ($(this).hasClass('specs-quantity')) {
                    $(this).val('1');
                } else if ($(this).hasClass('specs-price')) {
                    $(this).val('0.00');
                } else {
                    $(this).val('0');
                }
            });
            $('#ramRowsContainer .ram-row .remove-ram-row').hide();

            // Reset Processor rows - keep only first, remove others
            $('#processorRowsContainer .processor-row:not(:first)').remove();
            $('#processorRowsContainer .processor-row:first').find('select').val('');
            $('#processorRowsContainer .processor-row:first').find('input[type="text"]').val('');
            $('#processorRowsContainer .processor-row:first').find('textarea').val('');
            $('#processorRowsContainer .processor-row:first').find('.warranty-ym-input').val('');
            $('#processorRowsContainer .processor-row:first').find('input[type="number"]').not('.warranty-ym-input').each(function() {
                if ($(this).hasClass('specs-processor-quantity')) {
                    $(this).val('1');
                } else if ($(this).hasClass('specs-processor-price')) {
                    $(this).val('0.00');
                } else {
                    $(this).val('0');
                }
            });
            $('#processorRowsContainer .processor-row .remove-processor-row').hide();

            // Reset HDD rows - keep only first, remove others
            $('#hddRowsContainer .hdd-row:not(:first)').remove();
            $('#hddRowsContainer .hdd-row:first').find('select').val('');
            $('#hddRowsContainer .hdd-row:first').find('input[type="text"]').val('');
            $('#hddRowsContainer .hdd-row:first').find('textarea').val('');
            $('#hddRowsContainer .hdd-row:first').find('.warranty-ym-input').val('');
            $('#hddRowsContainer .hdd-row:first').find('input[type="number"]').not('.warranty-ym-input').each(function() {
                if ($(this).hasClass('specs-hdd-quantity')) {
                    $(this).val('1');
                } else if ($(this).hasClass('specs-hdd-price')) {
                    $(this).val('0.00');
                } else {
                    $(this).val('0');
                }
            });
            $('#hddRowsContainer .hdd-row .remove-hdd-row').hide();
            updateStorageLabels($('#hddRowsContainer'));
        }

        // When modal closes without saving specs, clear item so user can pick the same Desktop/Laptop/Server again (change event re-fires).
        $('#itemSpecsModal').on('hidden.bs.modal', function () {
            const $modal = $(this);
            const closedWithSave = Boolean($modal.data('closed-with-save'));
            $modal.data('closed-with-save', false);
            const targetRow = $modal.data('target-row');

            if (!closedWithSave && targetRow && targetRow.length) {
                const $item = targetRow.find('.inv-item');
                const v = String($item.val() || '');
                if (isComputerItem(v)) {
                    const hasSpecs = targetRow.find('input[name="item_specs[]"]').filter(function () {
                        return ($(this).val() || '').trim() !== '';
                    }).length > 0;
                    if (!hasSpecs) {
                        if ($item.hasClass('select2-hidden-accessible')) {
                            $item.val(null).trigger('change.select2');
                        } else {
                            $item.val('').trigger('change');
                        }
                    }
                }
            }

            $modal.removeData('target-row');
            $modal.removeData('target-container');
            resetModalForm();
            // Destroy any leftover select2 so next open starts clean
            $modal.find('select.inv-modal-select').each(function () {
                const $sel = $(this);
                if ($sel.hasClass('select2-hidden-accessible')) {
                    try { $sel.select2('destroy'); } catch (e) {}
                }
                $sel.next('.select2').remove();
                $sel.removeClass('select2-hidden-accessible').removeAttr('data-select2-id').show();
            });
        });

        updateStorageLabels($('#hddRowsContainer'));
    });
</script>
    <?php
    session()->forget('error');
    ?>
    <script>
        function edit(id) {
            $.ajax({
                url: "<?= URL::to('/item-edit'); ?>/" + id,
                type: 'get',
                data: {},
                dataType: 'JSON',
                success: function (response) {
                    $("#exampleModal").modal("show");
                    $(".id").val(response.id);
                    $("#item").val(response.item);
                    $("#size_of").val(response.size);
                    $("#item_img1").val(response.image);
                    $("#supplier_id").val(response.supplier_id).trigger('change');
                    $("#brand_id").val(response.brand_id).trigger('change');
                    $("#category_id").val(response.category_id).trigger('change');
                    $("#size_of").val(response.size).trigger('change');
                    $("#item_code").val(response.code);
                    if (response.id) {
                        $('.submit').text("Update");
                        $('#exampleModalLabel').text("Update Items");
                    }
                }
            });
        }

        function checkItemCode(code) {
            $.ajax({
                url: "<?= URL::to('/item-info-by-code'); ?>/" + code,
                type: 'get',
                data: {},
                dataType: 'JSON',
                success: function (response) {
                    if(response.code == code){
                        $(".exItem").text('already exists..!');
                    }
                }
            });
        }
    </script>
@endsection
