@extends('layouts.app')
@section('content')
    <div class="container card border-top border-0 border-4 border-primary">
        <div class="row justify-content-center">
            <div class="col-lg-12">
                @if($user_access->is_create==1)
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#exampleModal"
                            style="float: right;">
                        Add +
                    </button>
                @endif
            </div>
            <hr/>
            <div class="col-lg-12">
                <table id="example" class="table table-striped" style="width:100%">
                    <thead>
                    <tr>
                        <th>SL</th>
                        <th>Supplier Name</th>
                        <th>Address</th>
                        <th>TIN</th>
                        <th>VAT</th>
                        <th>Account Number</th>
                        <th>Trade License</th>
                        <th>Code</th>
                        <th>Slug</th>
                        <th>Phone Number</th>
                        <th>Action</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($supplier as $value)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $value->supplier_name }}</td>
                            <td>{{ $value->address }}</td>
                            <td>{{ $value->tin }}</td>
                            <td>{{ $value->vat }}</td>
                            <td>{{ $value->account_no }}</td>
                            <td>{{ $value->trade_license }}</td>
                            <td>{{ $value->code }}</td>
                            <td>{{ $value->slug }}</td>
                            <td>{{ $value->phone_number }}</td>
                            <td>
                                @if($user_access->is_edit==1)
                                    <button onclick="edit({{$value->id}})">
                                        <i class="fadeIn animated bx bx-edit-alt" data-feather="edit"></i>
                                    </button>
                                @endif
                                @if($user_access->is_delete==1)
                                    <button>
                                        <a href="{{route('supplier-delete',$value->id)}}">
                                            <i class="fadeIn animated bx bx-trash-alt" data-feather="trash-2"></i></a>
                                    </button>
                                @endif
                                @if($value->status==1 && $user_access->is_approved==1)
                                    <button>
                                        <a href="{{route('supplier-in-active',$value->id)}}">
                                            <i class="fadeIn animated bx bx-comment-check"
                                               data-feather="check-square"></i>
                                        </a>
                                    </button>
                                @elseif($user_access->is_approved==1)
                                    <button>
                                        <a href="{{route('supplier-active',$value->id)}}">
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
            <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel"
                 aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h1 class="modal-title fs-5" id="exampleModalLabel">{{ __('Supplier') }}</h1>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <form method="POST" action="{{ route('supplier-save-update') }}"
                                  enctype="multipart/form-data">
                                @csrf

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="supplier" class="form-label">{{ __('Supplier Name') }}</label>
                                            <input name="id" type="hidden" class="id" value="">
                                            <input name="code" type="hidden" class="code" value="">
                                            <input id="supplier" type="text"
                                                   class="form-control @error('supplier_name') is-invalid @enderror"
                                                   name="supplier_name" value="{{ old('supplier_name') }}" required
                                                   autocomplete="supplier" autofocus>

                                            @error('supplier_name')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                            @enderror
                                        </div>

                                        <div class="mb-3">
                                            <label for="phone_number" class="form-label">{{ __('Phone Number') }}</label>
                                            <input id="phone_number" type="text"
                                                   class="form-control @error('phone_number') is-invalid @enderror"
                                                   name="phone_number" value="{{ old('phone_number') }}" required
                                                   autocomplete="phone_number">

                                            @error('phone_number')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                            @enderror
                                        </div>

                                        <div class="mb-3">
                                            <label for="tin" class="form-label">{{ __('TIN') }}</label>
                                            <input id="tin" type="text"
                                                   class="form-control @error('tin') is-invalid @enderror"
                                                   name="tin"
                                                   value="{{ old('tin') }}"
                                                   autocomplete="tin"
                                                   maxlength="12"
                                                   minlength="12"
                                                   pattern="\d{12}"
                                                   inputmode="numeric"
                                                   placeholder="Enter 12 digit TIN number">

                                            @error('tin')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                            @enderror
                                        </div>

                                        <div class="mb-3">
                                            <label for="vat" class="form-label">{{ __('VAT') }}</label>
                                            <input id="vat" type="text"
                                                   class="form-control @error('vat') is-invalid @enderror"
                                                   name="vat"
                                                   value="{{ old('vat') }}"
                                                   autocomplete="vat"
                                                   maxlength="9"
                                                   minlength="9"
                                                   pattern="\d{9}"
                                                   inputmode="numeric"
                                                   placeholder="Enter 9 digit VAT number">

                                            @error('vat')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="address" class="form-label">{{ __('Address') }}</label>
                                            <textarea id="address"
                                                      class="form-control @error('address') is-invalid @enderror"
                                                      name="address" required
                                                      autocomplete="address" rows="4">{{ old('address') }}</textarea>

                                            @error('address')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                            @enderror
                                        </div>

                                        <div class="mb-3">
                                            <label for="account_no" class="form-label">{{ __('Account Number') }}</label>
                                            <input id="account_no" type="text"
                                                   class="form-control @error('account_no') is-invalid @enderror"
                                                   name="account_no" value="{{ old('account_no') }}"
                                                   autocomplete="account_no">

                                            @error('account_no')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                            @enderror
                                        </div>

                                        <div class="mb-3">
                                            <label for="trade_license" class="form-label">{{ __('Trade License') }}</label>
                                            <input id="trade_license" type="text"
                                                   class="form-control @error('trade_license') is-invalid @enderror"
                                                   name="trade_license" value="{{ old('trade_license') }}"
                                                   autocomplete="trade_license">

                                            @error('trade_license')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                        </div>

                        <div class="row mb-0">
                            <div class="col-md-6 offset-md-4">
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                        Close
                                    </button>
                                    <button type="submit" class="btn btn-primary submit"> {{ __('Save') }}</button>
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
    <script>
        function edit(id) {
            $.ajax({
                url: "<?= URL::to('/supplier-edit'); ?>/" + id,
                type: 'get',
                data: {},
                dataType: 'JSON',
                success: function (response) {
                    $("#exampleModal").modal("show");
                    $(".id").val(response.id);
                    $("#supplier").val(response.supplier_name);
                    $("#phone_number").val(response.phone_number);
                    $("#address").val(response.address);
                    $("#tin").val(response.tin);
                    $("#vat").val(response.vat);
                    $("#account_no").val(response.account_no);
                    $("#trade_license").val(response.trade_license);
                    $(".code").val(response.code);
                    // $("#thumb_img").attr('src',''+response.thumb_image);
                    if(response.id){
                        $('.submit').text("Update");
                        $('#exampleModalLabel').text("Update Supplier");
                    }
                }
            });
        }
    </script>
@endsection
