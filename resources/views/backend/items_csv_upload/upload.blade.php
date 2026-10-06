@extends('layouts.app')

@section('content')
    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
        <div class="breadcrumb-title pe-3">Inventory</div>
        <div class="ps-3">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 p-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
                    <li class="breadcrumb-item"><a href="{{ url('items-inventory') }}">Items Inventory</a></li>
                    <li class="breadcrumb-item active" aria-current="page">CSV Upload</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="card radius-10 shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Upload Items via CSV/XLSX File</h5>
            <div class="d-flex gap-2">
                <a href="{{ route('items-inventory-csv-receive') }}" class="btn btn-sm btn-warning">
                    <i class="bx bx-check-circle"></i> Receive Uploaded Items
                </a>
                <a href="{{ url('items-inventory') }}" class="btn btn-sm btn-secondary">
                    <i class="bx bx-arrow-back"></i> Back to Inventory
                </a>
            </div>
        </div>
        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <div class="mt-2">
                        <a href="{{ route('items-inventory-csv-export-result') }}" class="btn btn-sm btn-success">
                            <i class="bx bx-download"></i> Download Upload Result Excel
                        </a>
                        <a href="{{ route('items-inventory-csv-receive') }}" class="btn btn-sm btn-outline-success ms-2">
                            <i class="bx bx-right-arrow-alt"></i> Go to Receive Page
                        </a>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            @if(session('eMessage'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('eMessage') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <div class="row">
                <div class="col-md-12">
                    <div class="alert alert-info">
                        <h6 class="alert-heading"><i class="bx bx-info-circle"></i> CSV/XLSX File Format Instructions</h6>
                        <p class="mb-2"><strong>Auto-detect enabled:</strong> Branch inventory Excel files (Product Name, Serial No, Product Description, Device Status ইত্যাদি) সিস্টেম নিজে থেকে map করবে। Preview-এ ভুল থাকলে edit করে upload করুন।</p>
                        <p class="mb-2">Standard template ব্যবহার করলে নিচের columns (columns in <strong>bold</strong> are required):</p>
                        <div class="row">
                            <div class="col-md-6">
                                <ol class="mb-2">
                                    <li>set_uniq_no (Optional) - খালি থাকলে preview-এ auto PBL code generate হবে</li>
                                    <li><strong>item_name</strong> (Required) - Name of the item</li>
                                    <li><strong>category</strong> (Required) - Category name</li>
                                    <li>brand (Optional) - Brand name</li>
                                    <li>supplier (Optional) - Supplier name</li>
                                    <li><strong>price</strong> (Required) - Price per unit</li>
                                    <li><strong>quantity</strong> (Required) - Available quantity</li>
                                    <li>unit_size (Optional) - Unit size/measurement</li>
                                    <li>serial_no (Optional) - Serial number</li>
                                    <li>condition (Optional) - Item condition (New/Used/Refurbished)</li>
                                    <li>warranty (Optional) - Warranty period</li>
                                    <li>service_warranty (Optional) - Service warranty</li>
                                </ol>
                            </div>
                            <div class="col-md-6">
                                <ol start="12" class="mb-2">
                                    <li>vat (Optional) - VAT amount</li>
                                    <li>tax (Optional) - Tax amount</li>
                                    <li>description (Optional) - Item description</li>
                                    <li>ram (Optional) - RAM specification (for computers)</li>
                                    <li>storage (Optional) - Storage specification (for computers)</li>
                                    <li>processor (Optional) - Processor specification</li>
                                    <li>graphics (Optional) - Graphics card</li>
                                    <li>display (Optional) - Display specification</li>
                                    <li>battery (Optional) - Battery specification</li>
                                    <li>color (Optional) - Item color</li>
                                </ol>
                            </div>
                        </div>
                        <p class="mb-2"><small><i class="bx bx-bulb"></i> <strong>Tip:</strong> If item, brand, category, or supplier already exists in the system, it will be automatically matched. New ones will be created if not found.</small></p>
                        <div class="alert alert-success mt-2 mb-0">
                            <strong>🖥️ Desktop/Laptop/Server Package Feature:</strong><br>
                            When you upload Desktop/Laptop/Server with RAM, Processor, and Storage, the system automatically creates separate inventory entries for each component with the same SET_UNIQ_NO. For example, a laptop with 8GB RAM, 512GB SSD, and Intel i5 will create 4 entries (Laptop + RAM + SSD + Processor) all grouped together.
                        </div>
                    </div>

                    <div class="card border">
                        <div class="card-body">
                            <h6 class="mb-3">Sample Format (showing required + some optional fields):</h6>
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered" style="font-size: 11px;">
                                    <thead class="table-light">
                                        <tr>
                                            <th>set_uniq_no</th>
                                            <th>item_name</th>
                                            <th>category</th>
                                            <th>brand</th>
                                            <th>supplier</th>
                                            <th>price</th>
                                            <th>quantity</th>
                                            <th>unit_size</th>
                                            <th>serial_no</th>
                                            <th>condition</th>
                                            <th>warranty</th>
                                            <th>description</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td></td>
                                            <td>Dell Laptop</td>
                                            <td>Laptop</td>
                                            <td>Dell</td>
                                            <td>Tech Supplier</td>
                                            <td>55000</td>
                                            <td>1</td>
                                            <td>PCS</td>
                                            <td>DL123456</td>
                                            <td>New</td>
                                            <td>1 Year</td>
                                            <td>Dell Latitude 5420</td>
                                        </tr>
                                        <tr>
                                            <td>PBL-MN-5001</td>
                                            <td>HP Monitor</td>
                                            <td>Monitor</td>
                                            <td>HP</td>
                                            <td>Tech Supplier</td>
                                            <td>12000</td>
                                            <td>1</td>
                                            <td>PCS</td>
                                            <td>HP789012</td>
                                            <td>New</td>
                                            <td>2 Years</td>
                                            <td>HP 24 inch LED</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div class="mt-2">
                                <a href="{{ route('items-inventory-csv-sample', ['format' => 'csv']) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="bx bx-download"></i> Download Sample CSV
                                </a>
                                <a href="{{ route('items-inventory-csv-sample', ['format' => 'xlsx']) }}" class="btn btn-sm btn-outline-success ms-2">
                                    <i class="bx bx-file"></i> Download Sample XLSX
                                </a>
                            </div>
                        </div>
                    </div>

                    <form action="{{ route('items-inventory-csv-upload-process') }}" method="POST" enctype="multipart/form-data" class="mt-4">
                        @csrf

                        <div class="mb-4">
                            <label class="form-label fw-semibold">Branch <span class="text-danger">*</span></label>
                            <select name="branch_id" class="form-select single-select-4" required>
                                <option value="">Select Branch</option>
                                @foreach($branchData as $branch)
                                    <option value="{{ $branch->hr_branch_id }}"
                                        {{ (string) old('branch_id', $defaultBranchId ?? '') === (string) $branch->hr_branch_id ? 'selected' : '' }}>
                                        {{ $branch->branch_name }}
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted">Uploaded inventory এই branch-এ save হবে।</small>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-semibold">Select CSV/XLSX File <span class="text-danger">*</span></label>
                            <input type="file" name="csv_file" id="csv_file" class="form-control" accept=".csv,.txt,.xlsx,.xls" required>
                            <small class="text-muted">Maximum file size: 10MB</small>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary px-4">
                                <i class="bx bx-upload"></i> Upload & Preview
                            </button>
                            <a href="{{ url('items-inventory') }}" class="btn btn-outline-secondary px-4">
                                <i class="bx bx-x"></i> Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
@if(session('auto_download_csv_result'))
<script>
    window.addEventListener('load', function () {
        window.location.href = @json(route('items-inventory-csv-export-result'));
    });
</script>
@endif
@endpush
