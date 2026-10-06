@extends('layouts.app')

@section('content')
    <div class="container card border-top border-0 border-4 border-primary">
        <div class="row justify-content-center">
            <div class="col-lg-12">
                @if(isset($user_access->is_create) && !empty($user_access->is_create) && $user_access->is_create==1)
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
                        <th>Branch</th>
                        <th>Division</th>
                        <th>Address</th>
                        <th>City</th>
                        <th>State</th>
                        <th>contact No.</th>
                        <th>Banner</th>
                        <th>Action</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($branch as $value)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $value->branch_name }}</td>
                            <td>{{ $value->division_name ?? '—' }}</td>
                            <td><?php echo  $value->address; ?></td>
                            <td>{{ $value->city }}</td>
                            <td>{{ $value->state }}</td>
                            <td>{{ $value->contact_number }}</td>
                            <td><img style="width: 30px;" src="{{ asset($value->banner) }}"/></td>
                            <td>
                                @if(isset($user_access->is_edit) && !empty($user_access->is_edit) && $user_access->is_edit==1)
                                    <button onclick="edit({{$value->id}})">
                                        <i class="fadeIn animated bx bx-edit-alt" data-feather="edit"></i>
                                    </button>
                                @endif
                                @if(isset($user_access->is_delete) && !empty($user_access->is_delete) && $user_access->is_delete==1)
                                    <button>
                                        <a href="{{route('branch-delete',$value->id)}}">
                                            <i class="fadeIn animated bx bx-trash-alt" data-feather="trash-2"></i></a>
                                    </button>
                                @endif
                                @if($value->status==1  && $user_access->is_approved==1)
                                    <button>
                                        <a href="{{route('branch-in-active',$value->id)}}">
                                            <i class="fadeIn animated bx bx-comment-check"
                                               data-feather="check-square"></i>
                                        </a>
                                    </button>
                                @elseif(isset($user_access->is_approved) && ($user_access->is_approved) && $user_access->is_approved==1)
                                    <button>
                                        <a href="{{route('branch-active',$value->id)}}">
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
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h1 class="modal-title fs-5" id="exampleModalLabel">{{ __('Branch') }}</h1>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <form method="POST" action="{{ route('branch-save-update') }}"
                                  enctype="multipart/form-data">
                                @csrf
                                <div class="row mb-3">
                                    <label for="branch"
                                           class="col-md-4 col-form-label text-md-end">{{ __('Branch') }}</label>
                                    <div class="col-md-6">
                                        <input name="id" type="hidden" class="id" value="">
                                        <input name="organization_id" type="hidden" class="organization_id" value="1">
                                        <input id="branch_name" type="text" class="form-control" name="branch_name"
                                               value="">
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <label for="division_id"
                                           class="col-md-4 col-form-label text-md-end">{{ __('Division') }}</label>
                                    <div class="col-md-6">
                                        <select name="division_id" class="form-control division_id">
                                            <option value="">— Select Division —</option>
                                            @foreach(($division_info ?? []) as $v_division)
                                                <option value="{{ $v_division->id }}">{{ $v_division->division_name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <label for="phone_number"
                                           class="col-md-4 col-form-label text-md-end">{{ __('Phone Number') }}</label>
                                    <div class="col-md-6">
                                        <input id="phone_number" type="text" class="form-control" name="phone_number"
                                               value="{{ old('phone_number') }}" required/>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <label for="city"
                                           class="col-md-4 col-form-label text-md-end">{{ __('City') }}</label>
                                    <div class="col-md-6">
                                        <input id="city" type="text" class="form-control" name="city" value="{{ old('city') }}" required/>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <label for="state"
                                           class="col-md-4 col-form-label text-md-end">{{ __('State') }}</label>
                                    <div class="col-md-6">
                                        <input id="state" type="text"
                                               class="form-control" name="state" value="{{ old('state') }}" required >
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <label for="zip_code"
                                           class="col-md-4 col-form-label text-md-end">{{ __('Zip code') }}</label>
                                    <div class="col-md-6">
                                        <input id="zip_code" type="text" class="form-control" name="zip_code" value="{{ old('zip_code') }}" required>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <label for="address"
                                           class="col-md-4 col-form-label text-md-end">{{ __('Address') }}</label>
                                    <div class="col-md-6">
                                        <textarea id="address" class="form-control" name="address">{{ old('address') }}</textarea>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <label for="banner"
                                           class="col-md-4 col-form-label text-md-end">{{ __('Banner') }}</label>

                                    <div class="col-md-6">
                                        <input id="banner_img1" type="hidden" name="banner_img1"/>
                                        <input id="banner_img" type="file" class="form-control" name="banner_img" >
                                    </div>
                                </div>

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
    <script>
        function edit(id) {
            $.ajax({
                url: "<?= URL::to('/branch-edit'); ?>/" + id,
                type: 'get',
                data: {},
                dataType: 'JSON',
                success: function (response) {
                    $("#exampleModal").modal("show");
                    $(".id").val(response.id);
                    $("#branch_name").val(response.branch_name);
                    $(".division_id").val(response.division_id || '');
                    $("#phone_number").val(response.contact_number);
                    $("#zip_code").val(response.zip_code);
                    $("#state").val(response.state);
                    tinyMCE.activeEditor.setContent(response.address);
                    $("#address").val(response.address);
                    $("#city").val(response.city);
                    $(".organization_id").val(response.organization_id);
                    $("#banner_img1").attr('value', '' + response.banner);
                    if (response.id) {
                        $('.submit').text("Update");
                        $('#exampleModalLabel').text("Update Branch");
                    }
                }
            });
        }
    </script>
@endsection
