@extends('layouts.app')

@section('content')
    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
        <div class="breadcrumb-title pe-3">Inventory</div>
        <div class="ps-3">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 p-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
                    <li class="breadcrumb-item"><a href="{{ url('items-inventory') }}">Items Inventory</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('items-inventory-csv-upload') }}">CSV Upload</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Preview</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="card radius-10 shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Preview CSV Data</h5>
            <div>
                <span class="badge bg-primary me-2">Total Items: {{ count($csvData) }}</span>
            </div>
        </div>
        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            @if(session('eMessage'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('eMessage') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if(!empty($errors) && count($errors) > 0)
                <div class="alert alert-warning alert-dismissible fade show" role="alert">
                    <h6 class="alert-heading"><i class="bx bx-error"></i> Some rows have errors:</h6>
                    <ul class="mb-0">
                        @foreach($errors as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if(!empty($selectedBranch))
                <div class="alert alert-secondary py-2">
                    <strong>Selected Branch:</strong> {{ $selectedBranch->branch_name ?? 'N/A' }}
                    <span class="text-muted">(ID: {{ $selectedBranchId ?? '' }})</span>
                </div>
            @endif

            @if(!empty($importMeta))
                <div class="alert alert-primary">
                    <strong>Auto-detected format:</strong> {{ $importMeta['detected_format'] ?? 'unknown' }}
                    @if(!empty($importMeta['header_row_index']))
                        | <strong>Header row:</strong> {{ $importMeta['header_row_index'] }}
                    @endif
                    @if(!empty($importMeta['warnings']))
                        <div class="mt-2 mb-0"><small>{{ implode(' ', $importMeta['warnings']) }}</small></div>
                    @endif
                </div>
            @endif

            <div class="alert alert-info">
                <i class="bx bx-info-circle"></i> <strong>Preview Information:</strong>
                <ul class="mb-0 mt-2">
                    <li><span class="badge bg-success">Auto-matched</span> items indicate that item, brand, supplier, or category was found in your system.</li>
                    <li><span class="badge bg-warning text-dark">Not Found</span> items will create new item, brand, supplier, or category automatically.</li>
                    <li>Wrong data হলে নিচের table-এ edit করে তারপর <strong>Confirm & Import</strong> চাপুন।</li>
                </ul>
            </div>

            <form action="{{ route('items-inventory-csv-store') }}" method="POST" id="csvStoreForm">
                @csrf
                <input type="hidden" name="branch_id" value="{{ $selectedBranchId ?? '' }}">

                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle" id="previewTable" style="font-size: 12px;">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 2%;">#</th>
                                <th style="width: 8%;">SET_UNIQ_NO</th>
                                <th style="width: 10%;">Item</th>
                                <th style="width: 10%;">Category</th>
                                <th style="width: 8%;">Brand</th>
                                <th style="width: 8%;">Supplier</th>
                                <th style="width: 6%;">Price</th>
                                <th style="width: 4%;">Qty</th>
                                <th style="width: 6%;">Unit</th>
                                <th style="width: 8%;">Serial No</th>
                                <th style="width: 6%;">Condition</th>
                                <th style="width: 6%;">Warranty</th>
                                <th style="width: 8%;">Specs</th>
                                <th style="width: 10%;">Description</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($csvData as $index => $row)
                                <tr>
                                    <td class="text-center">{{ $index + 1 }}</td>
                                    <td>
                                        <input
                                            type="text"
                                            name="set_uniq_no[{{ $index }}]"
                                            class="form-control form-control-sm"
                                            value="{{ $row['set_uniq_no'] ?? '' }}"
                                            placeholder="Auto generated"
                                            readonly
                                        >
                                        <small class="text-success">Auto generated (PBL format)</small>
                                    </td>
                                    <td>
                                        <input type="text" name="item_name[{{ $index }}]" class="form-control form-control-sm mb-1" value="{{ $row['item_name'] ?? '' }}">
                                        <select name="item_id[{{ $index }}]" class="form-select form-select-sm">
                                            <option value="">-- New Item --</option>
                                            @foreach($items as $item)
                                                <option value="{{ $item->id }}"
                                                    data-category-id="{{ $item->category_id ?? '' }}"
                                                    {{ $row['item_id'] == $item->id ? 'selected' : '' }}>
                                                    {{ $item->item_title }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @if($row['auto_matched']['item'])
                                            <small class="text-success"><i class="bx bx-check-circle"></i> Matched</small>
                                        @else
                                            <small class="text-warning"><i class="bx bx-error-circle"></i> New item name editable</small>
                                        @endif
                                    </td>
                                    <td>
                                        <input type="text" name="category_name[{{ $index }}]" class="form-control form-control-sm mb-1" value="{{ $row['category_name'] ?? '' }}">
                                        <select name="category_id[{{ $index }}]" class="form-select form-select-sm">
                                            <option value="">-- New Category --</option>
                                            @foreach($categories as $category)
                                                <option value="{{ $category->id }}" 
                                                    {{ $row['category_id'] == $category->id ? 'selected' : '' }}>
                                                    {{ $category->category_title ?? $category->category }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @if($row['auto_matched']['category'])
                                            <small class="text-success"><i class="bx bx-check-circle"></i> Matched</small>
                                        @else
                                            <small class="text-warning"><i class="bx bx-error-circle"></i> New: {{ $row['category_name'] }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        <input type="text" name="brand_name[{{ $index }}]" class="form-control form-control-sm mb-1" value="{{ $row['brand_name'] ?? '' }}">
                                        <select name="brand_id[{{ $index }}]" class="form-select form-select-sm">
                                            <option value="">-- New Brand --</option>
                                            @foreach($brands as $brand)
                                                <option value="{{ $brand->id }}" 
                                                    {{ $row['brand_id'] == $brand->id ? 'selected' : '' }}>
                                                    {{ $brand->brand_name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @if($row['auto_matched']['brand'])
                                            <small class="text-success"><i class="bx bx-check-circle"></i> Matched</small>
                                        @elseif(!empty($row['brand_name']))
                                            <small class="text-warning"><i class="bx bx-error-circle"></i> New: {{ $row['brand_name'] }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        <select name="supplier_id[{{ $index }}]" class="form-select form-select-sm">
                                            <option value="">-- New Supplier --</option>
                                            @foreach($suppliers as $supplier)
                                                <option value="{{ $supplier->id }}" 
                                                    {{ $row['supplier_id'] == $supplier->id ? 'selected' : '' }}>
                                                    {{ $supplier->supplier_name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @if($row['auto_matched']['supplier'])
                                            <small class="text-success"><i class="bx bx-check-circle"></i> Matched</small>
                                        @elseif(!empty($row['supplier_name']))
                                            <small class="text-warning"><i class="bx bx-error-circle"></i> New: {{ $row['supplier_name'] }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        <input type="number" step="0.01" min="0" name="price[{{ $index }}]" class="form-control form-control-sm" value="{{ $row['price'] ?? 0 }}">
                                    </td>
                                    <td>
                                        <input type="number" step="1" min="0" name="quantity[{{ $index }}]" class="form-control form-control-sm" value="{{ $row['quantity'] ?? 1 }}">
                                    </td>
                                    <td>
                                        <input type="text" name="unit_size[{{ $index }}]" class="form-control form-control-sm" value="{{ $row['unit_size'] ?? '' }}">
                                    </td>
                                    <td>
                                        <input type="text" name="serial_no[{{ $index }}]" class="form-control form-control-sm" value="{{ $row['serial_no'] ?? '' }}">
                                    </td>
                                    <td>
                                        <input type="text" name="condition[{{ $index }}]" class="form-control form-control-sm" value="{{ $row['condition'] ?? '' }}">
                                    </td>
                                    <td><small>{{ $row['warranty'] ?? '-' }}</small></td>
                                    <td>
                                        <input type="text" name="ram[{{ $index }}]" class="form-control form-control-sm mb-1" placeholder="RAM" value="{{ $row['ram'] ?? '' }}">
                                        <input type="text" name="storage[{{ $index }}]" class="form-control form-control-sm mb-1" placeholder="Storage" value="{{ $row['storage'] ?? '' }}">
                                        <input type="text" name="processor[{{ $index }}]" class="form-control form-control-sm" placeholder="Processor" value="{{ $row['processor'] ?? '' }}">
                                    </td>
                                    <td>
                                        <textarea name="description[{{ $index }}]" class="form-control form-control-sm" rows="2">{{ $row['description'] ?? '' }}</textarea>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-4 d-flex justify-content-between align-items-center">
                    <div>
                        <a href="{{ route('items-inventory-csv-cancel') }}" class="btn btn-outline-danger px-4">
                            <i class="bx bx-x"></i> Cancel
                        </a>
                    </div>
                    <div>
                        <button type="submit" class="btn btn-success px-5">
                            <i class="bx bx-check-circle"></i> Confirm & Import to Database
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
        $(function() {
            $('#csvStoreForm').on('submit', function(e) {
                const confirmed = confirm('Are you sure you want to import {{ count($csvData) }} items to the database?');
                if (!confirmed) {
                    e.preventDefault();
                }
            });
        });
    </script>
@endsection
