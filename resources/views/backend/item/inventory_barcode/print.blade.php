@extends('layouts.app')

@section('content')
    <link href="{{ asset('backend/assets/css/barcode.css') }}" rel="stylesheet">

    <div class="barcode-preview-toolbar d-print-none">
        <div class="d-flex flex-wrap align-items-center gap-3 justify-content-between">
            <div>
                <h5 class="mb-1"><i class="bx bx-barcode"></i> Barcode preview</h5>
                <p class="mb-0 text-muted small">
                    {{ $label_count }} label(s) · Sheet: <strong>{{ $sheetLabel }}</strong> · Bars: <strong>{{ $densityLabel }}</strong>
                </p>
                <p class="mb-0 text-primary small">নিচে preview দেখুন — ঠিক থাকলে <strong>Print</strong> চাপুন।</p>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <button type="button" class="btn btn-warning px-4" onclick="printBarcodes()">
                    <i class="bx bx-printer"></i> Print
                </button>
                <a href="{{ route('inventory-barcode') }}" class="btn btn-outline-secondary">Back</a>
            </div>
        </div>
    </div>

    <div class="barcode-preview-frame d-print-none">
        @include('backend.item.inventory_barcode._labels')
    </div>

    <script>
        function printBarcodes() {
            var contents = document.getElementById('barcode-con').innerHTML;
            var cssVars = @json($labelCssVars);
            var pageW = @json($sheetWidthMm ?? 38.1);
            var pageH = @json($sheetHeightMm ?? 25.4);
            var frame = document.createElement('iframe');
            frame.name = 'barcodePrintFrame';
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
    </script>
@endsection
