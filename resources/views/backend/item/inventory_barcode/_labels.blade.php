<div id="barcode-con" style="{{ $labelCssVars ?? '' }}">
    <div class="barcode-sheet barcode-sheet--single-strip">
        @foreach($labels as $label)
            <div class="barcode-label">
                <div class="barcode-label-item">{{ $label['item_name'] }}</div>
                @if(!empty($showBrand) && !empty($label['brand_name']))
                    <div class="barcode-label-brand">{{ $label['brand_name'] }}</div>
                @endif
                @if(!empty($showPrice) && !empty($label['price']))
                    <div class="barcode-label-price">Price: {{ $label['price'] }}</div>
                @endif
                <div class="barcode-label-image">{!! $label['barcode_html'] !!}</div>
                <div class="barcode-label-code">{{ $label['set_uniq_no'] }}</div>
            </div>
        @endforeach
    </div>
</div>
