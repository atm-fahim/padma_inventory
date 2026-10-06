@extends('layouts.app')

@section('content')
    <link href="{{asset('backend/assets/css/barcode.css')}}" rel="stylesheet">
    <script type="text/javascript">
        function printContent() {
            var contents = document.getElementById("barcode-con").innerHTML;
            var frame1 = document.createElement('iframe');
            frame1.name = "frame1";
            frame1.style.position = "absolute";
            frame1.style.top = "-1000000px";
            document.body.appendChild(frame1);
            var frameDoc = (frame1.contentWindow) ? frame1.contentWindow : (frame1.contentDocument.document) ? frame1.contentDocument.document : frame1.contentDocument;
            frameDoc.document.open();
            frameDoc.document.write('<html><head><title>Lab reports</title>');
            frameDoc.document.write('</head><body>');
            frameDoc.document.write('<link href="{{asset('backend/assets/css/barcode.css')}}" rel="stylesheet" type="text/css" />');
            frameDoc.document.write(contents);
            frameDoc.document.write('</body></html>');
            frameDoc.document.close();
            setTimeout(function () {
                window.frames["frame1"].focus();
                window.frames["frame1"].print();
                document.body.removeChild(frame1);
            }, 500);
            return false;
        }
    </script>
    <div class="container card border-top border-0 border-4 border-primary">
        <div class="card col-xl-9 mx-auto">
            <div class="card-body">
                <form class="row g-3" method="POST" action="{{ route('barcode-generate-by-item-id') }}"
                      enctype="multipart/form-data">
                    @csrf
                    <div class="col-md-6">
                        <div class="input-group mb-3">
                            <span class="input-group-text" id="inputGroup-sizing-sm">Item</span>
                            <select name="item_id" class="form-select" id="inputGroupSelect03"
                                    aria-label="Example select with button addon">
                                <option selected="">Choose...</option>
                                @foreach($item_info as $v_item)
                                    <option value="{{$v_item->id}}">{{$v_item->item}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="input-group input-group-sm mb-3"><span class="input-group-text"
                                                                           id="inputGroup-sizing-sm">QTY</span>
                            <input name="qty" type="text" class="form-control" aria-label="Sizing example input"
                                   aria-describedby="inputGroup-sizing-sm">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="input-group mb-3">
                            <span class="input-group-text" id="inputGroup-sizing-sm">Page Layout </span>
                            <select name="barcode_size" class="form-select" id="inputGroupSelect03"
                                    aria-label="Example select with button addon">
                                <option selected="">Choose...</option>
                                <option selected="" value="30">30 per sheet (38mm x 25mm)</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="input-group input-group-sm mb-3">
                            <span class="input-group-text" id="inputGroup-sizing-sm">Customise Price</span>
                            <input name="custom_price" type="text" class="form-control">
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-check form-check-inline">
                            <input name="product_name" class="form-check-input" type="checkbox" id="inlineCheckbox1"
                                   checked>
                            <label class="form-check-label" for="inlineCheckbox1">Product name </label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input name="p_code" class="form-check-input" type="checkbox" id="inlineCheckbox1" value="1"
                                   checked>
                            <label class="form-check-label" for="inlineCheckbox1">Product code </label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input name="price" class="form-check-input" type="checkbox" id="inlineCheckbox1" value="1"
                                   checked>
                            <label class="form-check-label" for="inlineCheckbox1">Price </label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input name="brand" class="form-check-input" type="checkbox" id="inlineCheckbox1" value="1">
                            <label class="form-check-label" for="inlineCheckbox1">Brand </label>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="col">
                            <button type="submit" class="btn btn-info px-5"><i class="bx bx-cog mr-1"></i>Generate
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <?php
        if (isset($barcode) && !empty($barcode)) {
        ?>
        <div class="text-center">
            <div class="btn-group">
                <button class="btn btn-warning" onClick="window.printContent();"><span
                        class="fa fa-print"></span> Print
                </button>
            </div>
        </div>
        <?php
        }
        ?>
                <div id="barcode-con">
                    <div class="barcode">
                        <?php
                        //$barcodeQty;

                        if (isset($barcode) && !empty($barcode)) {

                        for ($i = 0; $i < $barcodeQty; $i++) {
                        ?>
                        <div class="item styleGP">
                            <div class="item-inner">
                                <div style="line-height:1.222;font-weight:700;">
                                    <span style="font-size:10px;" class="barcode_name">{{$product_info['name']}}</span>
                                </div>
                                <div style="font-size:15px;line-height:1.222;font-weight:700;">
                                    BDT {{@number_format($product_info['price'], 2)}}</div>
                                <span class="barcode_image">
											{!! $barcode !!}
                                    <div class="text-center" style="font-size:12px;">
                                        {{$product_info['code']}}
                                    </div>
							 	</span>
                            </div>
                        </div>
                        <?php
                        }
                        }
                        ?>
                    </div>
                </div>
            </div>


    <!--Modal JS Script -->
    <script type="text/javascript">
        window.onload = () => {
            var data = '<?php echo session()->get('error'); ?>';
            var j = JSON.parse(data);
            if (data != null) {
                $('#exampleModal').modal('show');
            }
            // Handler for `load` called.
            $("#esupplier_id").text(j.supplier_id);
            $("#ebrand_id").text(j.brand_id);
            $("#ecategory_id").text(j.category_id);
            $("#eitem").text(j.item);
        }
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
                    if (response.code == code) {
                        $(".exItem").text('already exists..!');
                    }
                }
            });
        }
    </script>
@endsection
