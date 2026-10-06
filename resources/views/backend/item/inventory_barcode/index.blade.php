@extends('layouts.app')

@section('content')
    <link href="{{ asset('backend/assets/css/barcode.css') }}" rel="stylesheet">

    <div class="container card border-top border-0 border-4 border-primary">
        <div class="card col-xl-9 mx-auto">
            <div class="card-body inventory-barcode-page">
                @if(session('eMessage'))
                    <div class="alert alert-danger">{{ session('eMessage') }}</div>
                @endif

                <form class="row g-3" method="POST" action="{{ route('inventory-barcode-generate') }}" id="barcodeGenerateForm">
                    @csrf

                    <div class="col-md-6">
                        <div class="input-group mb-3">
                            <span class="input-group-text">Division / Branch</span>
                            <select name="location_filter" id="location_filter"
                                    class="form-select barcode-select2"
                                    data-placeholder="Choose…" data-allow-clear="true">
                                <option value=""></option>
                                @foreach($locationGroups as $groupName => $entries)
                                    <optgroup label="{{ $groupName }}">
                                        @foreach($entries as $entry)
                                            <option value="{{ $entry['value'] }}"
                                                {{ (string) old('location_filter', $filters['location_filter'] ?? '') === (string) $entry['value'] ? 'selected' : '' }}>
                                                {{ $entry['label'] }}
                                            </option>
                                        @endforeach
                                    </optgroup>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="input-group mb-3">
                            <span class="input-group-text">Item</span>
                            <select name="item_id" id="item_id" class="form-select barcode-select2"
                                    data-placeholder="Choose…" data-allow-clear="true">
                                <option value=""></option>
                                @foreach($items as $item)
                                    <option value="{{ $item->id }}"
                                        {{ (string) old('item_id', $filters['item_id'] ?? '') === (string) $item->id ? 'selected' : '' }}>
                                        {{ $item->item_title }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="input-group input-group-sm mb-3">
                            <span class="input-group-text">QTY</span>
                            <input type="number" name="default_qty" class="form-control"
                                   value="{{ old('default_qty', 1) }}" min="1" max="500">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="input-group mb-3">
                            <span class="input-group-text">Page Layout</span>
                            <select name="label_sheet_size" class="form-select" required>
                                @foreach($sheetSizes as $key => $preset)
                                    <option value="{{ $key }}"
                                        {{ (string) old('label_sheet_size', \App\Support\BarcodeLabelSizeCatalog::DEFAULT_SHEET_KEY) === (string) $key ? 'selected' : '' }}>
                                        {{ $preset['per_sheet'] }} per sheet ({{ $preset['width_mm'] }}mm x {{ $preset['height_mm'] }}mm)
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="col-md-12">
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" id="cb_product_name" checked disabled>
                            <label class="form-check-label" for="cb_product_name">Product name</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" id="cb_set_code" checked disabled>
                            <label class="form-check-label" for="cb_set_code">SET_UNIQ_NO (barcode)</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" name="show_price" value="1" id="show_price"
                                {{ old('show_price') ? 'checked' : '' }}>
                            <label class="form-check-label" for="show_price">Price</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" name="show_brand" value="1" id="show_brand"
                                {{ old('show_brand') ? 'checked' : '' }}>
                            <label class="form-check-label" for="show_brand">Brand</label>
                        </div>
                    </div>

                    <input type="hidden" name="barcode_density" value="{{ \App\Support\BarcodeLabelSizeCatalog::DEFAULT_DENSITY_KEY }}">

                    <div class="col-md-6">
                        <button type="submit" class="btn btn-info px-5">
                            <i class="bx bx-cog"></i> Generate
                        </button>
                        <a href="{{ route('barcode-generate') }}" class="btn btn-outline-secondary ms-2">Reset</a>
                    </div>
                </form>
            </div>
        </div>

        @if(!empty($labels) && count($labels) > 0)
            <div class="card col-xl-11 mx-auto mt-3 d-print-none" id="barcodePreviewSection">
                <div class="card-body">
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
                        <div>
                            <h5 class="mb-1"><i class="bx bx-barcode"></i> Barcode preview</h5>
                            <p class="mb-0 text-muted small">
                                {{ $label_count }} label(s) · {{ $sheetLabel ?? '' }}
                            </p>
                            <p class="mb-0 text-primary small">প্রতিটি barcode একটার নিচে একটা (single label) — ঠিক থাকলে <strong>Print</strong> চাপুন।</p>
                        </div>
                        <button type="button" class="btn btn-warning px-4" onclick="printBarcodes()">
                            <i class="bx bx-printer"></i> Print
                        </button>
                    </div>
                    <div class="barcode-preview-frame">
                        @include('backend.item.inventory_barcode._labels')
                    </div>
                </div>
            </div>
        @endif
    </div>

    @if(!empty($labels) && count($labels) > 0)
        <script>
            function printBarcodes() {
                var contents = document.getElementById('barcode-con').innerHTML;
                var cssVars = @json($labelCssVars ?? '');
                var pageW = @json($sheetWidthMm ?? 38.1);
                var pageH = @json($sheetHeightMm ?? 25.4);
                var frame = document.createElement('iframe');
                frame.style.position = 'absolute';
                frame.style.top = '-1000000px';
                document.body.appendChild(frame);
                var win = frame.contentWindow;
                var doc = win.document;
                doc.open();
                doc.write('<html><head><title>Inventory Barcodes</title>');
                doc.write('<link href="{{ asset('backend/assets/css/barcode.css') }}" rel="stylesheet" type="text/css" />');
                doc.write('<style>@page{size:' + pageW + 'mm ' + pageH + 'mm;margin:0;}html,body{margin:0;padding:0;}#barcode-con{' + cssVars + '}</style>');
                doc.write('</head><body>');
                doc.write(contents);
                doc.write('</body></html>');
                doc.close();
                setTimeout(function () {
                    win.focus();
                    win.print();
                    document.body.removeChild(frame);
                }, 500);
            }
            document.addEventListener('DOMContentLoaded', function () {
                var el = document.getElementById('barcodePreviewSection');
                if (el) el.scrollIntoView({ behavior: 'smooth', block: 'start' });
            });
        </script>
    @endif

    <script>
        $(function () {
            if (!$.fn.select2) {
                return;
            }
            $('.barcode-select2').each(function () {
                var $el = $(this);
                if ($el.hasClass('select2-hidden-accessible')) {
                    $el.select2('destroy');
                }
                $el.select2({
                    theme: 'bootstrap4',
                    width: '100%',
                    placeholder: $el.data('placeholder') || 'Choose…',
                    allowClear: true,
                    dropdownAutoWidth: false
                });
            });
        });
    </script>
@endsection
