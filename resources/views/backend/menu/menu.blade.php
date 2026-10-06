@extends('layouts.app')

@section('content')
    <div class="container card border-top border-0 border-4 border-primary">
        <div class="row justify-content-center">
            <div class="col-lg-12">
                @if(isset($user_access->is_create) && !empty($user_access->is_create) && $user_access->is_create==1)
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                            data-bs-target="#exampleModal"
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
                        <th>Menu</th>
                        <th>Slug</th>
                        <th>Parent Menu</th>
                        <th>URL</th>
                        <th>Icon</th>
                        <th>Action</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($menu as $value)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $value->menu_name }}</td>
                            <td>{{ $value->slug }}</td>
                            <td></td>
                            <td>{{ $value->menu_url }}</td>
                            <td>{{ $value->icon }}</td>
                            <td>
                                @if(isset($user_access->is_edit) && !empty($user_access->is_edit) && $user_access->is_edit==1)
                                    <button onclick="edit({{$value->id}})">
                                        <i class="fadeIn animated bx bx-edit-alt" data-feather="edit"></i>
                                    </button>
                                @endif
                                @if(isset($user_access->is_delete) && !empty($user_access->is_delete) && $user_access->is_delete==1)
                                    <button>
                                        <a href="{{route('backend-menu-delete',$value->id)}}">
                                            <i class="fadeIn animated bx bx-trash-alt"
                                               data-feather="trash-2"></i></a>
                                    </button>
                                @endif
                                @if(isset($user_access->is_approved) && !empty($user_access->is_approved) && $value->status==1 && $user_access->is_approved==1)
                                    <button>
                                        <a href="{{route('backend-menu-in-active',$value->id)}}">
                                            <i class="fadeIn animated bx bx-comment-check"
                                               data-feather="check-square"></i>
                                        </a>
                                    </button>
                                @elseif(isset($user_access->is_approved) && ($user_access->is_approved) && $user_access->is_approved==1)
                                    <button>
                                        <a href="{{route('backend-menu-active',$value->id)}}">
                                            <i class="fadeIn animated bx bx-comment-x"
                                               data-feather="x-square"></i>
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
                            <h1 class="modal-title fs-5" id="exampleModalLabel">{{ __('Backend Menu') }}</h1>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <form method="POST" action="{{ route('backend-menu-save-update') }}"
                                  enctype="multipart/form-data">
                                @csrf
                                <div class="row mb-3">
                                    <label for="state"
                                           class="col-md-4 col-form-label text-md-end">{{ __('Select Parent Menu') }}</label>
                                    <div class="col-md-6">
                                        <select id="parent_menu" name="parent_menu" class="form-control">
                                            <option value="0">---select any one---</option>
                                            @foreach($parent_menu as $v_menu)
                                                <option
                                                    value="{{$v_menu->id}}"> {{$v_menu->menu_name}} </option>
                                            @endforeach
                                        </select>
                                        @error('parent_menu')
                                        <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <label for="menu"
                                           class="col-md-4 col-form-label text-md-end">{{ __('Menu') }}</label>

                                    <div class="col-md-6">
                                        <input name="id" type="hidden" class="id" value="">
                                        {{--                                        <input name="organization_id" type="hidden" class="organization_id" value="1">--}}
                                        <input id="menu_name" type="text"
                                               class="form-control @error('menu_name') is-invalid @enderror"
                                               name="menu_name" value="" required
                                               autocomplete="menu_name" autofocus>

                                        @error('menu_name')
                                        <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <label for="menu_icon"
                                           class="col-md-4 col-form-label text-md-end">{{ __('Menu Icon') }}</label>

                                    <div class="col-md-6">
                                        <input id="menu_icon" type="text"
                                               class="form-control @error('menu_icon') is-invalid @enderror"
                                               name="menu_icon" value="{{ old('menu_icon') }}" required
                                               autocomplete="menu_icon" autofocus>

                                        @error('menu_icon')
                                        <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <label for="url"
                                           class="col-md-4 col-form-label text-md-end">{{ __('URL') }}</label>
                                    <div class="col-md-6">
                                        <input id="menu_url" type="text"
                                               class="form-control @error('url') is-invalid @enderror"
                                               name="url" value="{{ old('url') }}" required
                                               autocomplete="url" autofocus>

                                        @error('url')
                                        <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                        @enderror
                                    </div>
                                </div>


                                <div class="row mb-0">
                                    <div class="col-md-6 offset-md-4">
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary"
                                                    data-bs-dismiss="modal">
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
                url: "<?= URL::to('/backend-menu-edit'); ?>/" + id,
                type: 'get',
                data: {},
                dataType: 'JSON',
                success: function (response) {
                    $("#exampleModal").modal("show");
                    $(".id").val(response.id);
                    $("#menu_name").val(response.menu_name);
                    $("#parent_menu").val(response.parent_id);
                    $("#menu_url").val(response.menu_url);
                    $("#menu_icon").val(response.icon);
                    // $("#banner_img1").attr('value',''+response.banner);
                    if (response.id) {
                        $('.submit').text("Update");
                        $('#exampleModalLabel').text("Update Menu");
                    }
                }
            });
        }
    </script>
@endsection
