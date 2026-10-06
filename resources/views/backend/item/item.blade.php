@extends('layouts.app')

@section('content')
    <div class="container card border-top border-0 border-4 border-primary">
        <div class="row justify-content-center">
            <div class="col-lg-12">
                <a href="{{route('items')}}">
                    <button type="button" class="btn btn-outline-info">
                        <i class="bx bx-refresh me-0"></i>
                    </button>
                </a>
                <div class="col-lg-12">
                    @if($user_access->is_create==1)
                        <button type="button" class="btn btn-sm btn-outline-success px-5" data-bs-toggle="modal" data-bs-target="#exampleModal">
                            Add Items +
                        </button>
                    @endif
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
                        <th>Category</th>
                        <th>Slug</th>
{{--                        <th>Image</th>--}}
                        <th>Action</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($item as $value)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $value->item_title }}</td>
                            <td>{{ $value->category_title }}</td>
                            <td>{{ $value->slug }}</td>
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
            <!-- Button trigger modal -->

            <!-- Modal -->
            <div class="modal fade" id="exampleModal" data-bs-keyboard="false" data-bs-backdrop="static" tabindex="-1" aria-labelledby="exampleModalLabel"
                 aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h1 class="modal-title fs-5" id="exampleModalLabel">{{ __('Item') }}</h1>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <form method="POST" action="{{ route('item-save-update') }}" enctype="multipart/form-data">
                                @csrf
                                <div class="row mb-3">
                                    <label class="col-md-4 col-form-label text-md-end">category</label>
                                    <select id="category_id" name="category_id"
                                            class="single-select form-select" style="width: 61% !important" required>
                                        <option value="">---select Any One---</option>
                                        @foreach($catData as $category)
                                            <option value="{{$category->id}}">{{$category->category_title}}</option>
                                            @if ($category->children)
                                                @foreach ($category->children as $child)
                                                    <option value="{{$child->id}}">--- {{$child->category_title}}</option>
                                                @endforeach
                                            @endif
                                        @endforeach
                                    </select>
                                        <strong id="ecategory_id" style="text-align: center;color: red;"></strong>
                                </div>
{{--                                <div class="row mb-3">--}}
{{--                                    <label for="size_of" class="col-md-4 col-form-label text-md-end">Size Of</label>--}}
{{--                                    <select name="size_of" class="single-select form-select" id="size_of" style="width: 61% !important">--}}
{{--                                        <option value="">---select Any One---</option>--}}
{{--                                        @foreach($size as $v_value)--}}
{{--                                            <option value="{{$v_value->slug}}">{{$v_value->size}}</option>--}}
{{--                                        @endforeach--}}
{{--                                    </select>--}}
{{--                                    <strong id="esize_id" style="text-align: center;color: red;"></strong>--}}
{{--                                </div>--}}

                                <div class="row mb-3">
                                    <label for="item"
                                           class="col-md-4 col-form-label text-md-end">{{ __('Item Name') }}</label>

                                    <div class="col-md-6">
                                        <input name="id" type="hidden" class="id" value="">
                                        <input id="item_title" type="text"
                                               class="form-control"
                                               name="item_title" value="{{ old('item_title') }}" required
                                               autocomplete="item_title" autofocus>

                                       <strong id="eitem" style="text-align: center;color: red;"></strong>

                                    </div>
                                </div>
{{--                                <div class="row mb-3">--}}
{{--                                    <label for="item code"--}}
{{--                                           class="col-md-4 col-form-label text-md-end">{{ __('Item Code') }}</label>--}}

{{--                                    <div class="col-md-6">--}}
{{--                                        <h6 class="exItem" style="color:red;"></h6>--}}
{{--                                        <input id="item_code" onkeyup="checkItemCode(this.value)" type="text"--}}
{{--                                               class="form-control"--}}
{{--                                               name="item_code" value="{{ old('item_code') }}"--}}
{{--                                               autocomplete="item_code" autofocus>--}}

{{--                                    </div>--}}
{{--                                </div>--}}
{{--                                <div class="row mb-3">--}}
{{--                                    <label for="item code"--}}
{{--                                           class="col-md-4 col-form-label text-md-end">{{ __('Item image') }}</label>--}}

{{--                                    <div class="col-md-6">--}}
{{--                                        <input type="file" class="form-control" name="item_img" >--}}
{{--                                        <input id="item_img1" type="hidden" class="form-control" name="item_img1" value="{{ old('item_img') }}">--}}
{{--                                    </div>--}}
{{--                                </div>--}}

                                <div class="row mb-0">
                                    <div class="col-md-6 offset-md-4">
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                                Close
                                            </button>
                                            <button type="submit"
                                                    class="btn btn-primary submit"> {{ __('Save') }}</button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>

                    </div>
                </div>
            </div>


        </div>
    </div>

    <!--Modal JS Script -->
    <script type="text/javascript">
        window.onload = () => {
             var data = '<?php echo session()->get('error'); ?>';
             var j = JSON.parse(data);
             if(data !=null){
                 $('#exampleModal').modal('show');
             }
            // Handler for `load` called.

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
                    $("#item_title").val(response.item_title)
                    $("#size_of").val(response.size);
                    // $("#item_img1").val(response.image);
                    $("#category_id").val(response.category_id).trigger('change');
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
