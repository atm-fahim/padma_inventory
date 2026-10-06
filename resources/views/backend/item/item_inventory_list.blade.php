@extends('layouts.app')

@section('content')
<div class="container card border-top border-0 border-4 border-primary">
    <div class="row justify-content-center">
        <div class="col-lg-12 py-3">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4 class="mb-0">Item Inventory List</h4>
                <a href="{{ route('items-inventory') }}" class="btn btn-primary btn-sm">
                    <i class="bx bx-left-arrow-alt me-1"></i> Back To Inventory Entry
                </a>
            </div>

            <div class="table-responsive">
                <table id="inventoryListTable" class="table table-striped table-bordered" style="width:100%">
                    <thead>
                        <tr>
                            <th>SL</th>
                            <th>Item</th>
                            <th>Code</th>
                            <th>Brand Name</th>
                            <th>Category</th>
                            <th>Supplier Name</th>
                            <th>Invoice Attachment</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($itemInventoryData as $value)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $value->item_title }}</td>
                                <td>{{ $value->set_uniq_no }}</td>
                                <td>{{ $value->brand_name }}</td>
                                <td>{{ $value->category_title }}</td>
                                <td>{{ $value->supplier_name }}</td>
                                <td>
                                    @if(!empty($value->invoice_attachment))
                                        @php
                                            $attachmentPath = asset($value->invoice_attachment);
                                            $ext = strtolower(pathinfo($value->invoice_attachment, PATHINFO_EXTENSION));
                                        @endphp
                                        @if(in_array($ext, ['jpg', 'jpeg', 'png']))
                                            <a href="{{ $attachmentPath }}" target="_blank">
                                                <img src="{{ $attachmentPath }}" alt="Invoice" style="width: 60px; height: 45px; object-fit: cover; border: 1px solid #ddd; border-radius: 4px;">
                                            </a>
                                        @else
                                            <a href="{{ $attachmentPath }}" target="_blank" class="btn btn-outline-secondary btn-sm">
                                                <i class="bx bxs-file-pdf me-1"></i> View
                                            </a>
                                        @endif
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </td>
                                <td class="text-nowrap">
                                    @if(data_get($user_access, 'is_edit', 0) == 1)
                                        <a href="{{ route('items-inventory-edit', $value->id) }}" class="btn btn-sm btn-outline-primary" title="Edit">
                                            <i class="bx bx-edit-alt"></i>
                                        </a>
                                    @endif
                                    @if(data_get($user_access, 'is_delete', 0) == 1)
                                        <a href="{{ route('items-inventory-delete', $value->id) }}"
                                           class="btn btn-sm btn-outline-danger"
                                           title="Delete"
                                           onclick="return confirm('Are you sure you want to delete this inventory item?');">
                                            <i class="bx bx-trash-alt"></i>
                                        </a>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
