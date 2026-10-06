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
            @if(Session::has('success'))
                <div class="alert alert-success">
                    {{Session::get('success')}}
                </div>
            @endif
            <div class="col-lg-12">
                <table id="example" class="table table-striped" style="width:100%">
                    <thead>
                    <tr>
                        <th>SL</th>
                        <th>Name</th>
                        <th>Employee Id</th>
                        <th>Email Address</th>
                        <th>User Type</th>
                        <th>Branch</th>
                        <th>contact No.</th>
                        <th>Action</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($users as $value)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $value->name }}</td>
                            <td>{{ $value->employee_id }}</td>
                            <td>{{ $value->email }}</td>
                            <td>{{ $value->user_types }}</td>
                            <td>{{ $value->user_id }}</td>
                            <td>{{ $value->contact_no }}</td>
                            {{--                            <td><img style="width: 30px;" src="{{ asset($value->banner) }}"/></td>--}}
                            <td>
                                @if(isset($user_access->is_edit) && !empty($user_access->is_edit) && $user_access->is_edit==1)
                                    <button onclick="edit({{$value->id}})">
                                        <i class="fadeIn animated bx bx-edit-alt" data-feather="edit"></i>
                                    </button>
                                @endif
                                @if(isset($user_access->is_delete) && !empty($user_access->is_delete) && $user_access->is_delete==1)
                                    <button>
                                        <a href="{{route('user-delete',$value->id)}}">
                                            <i class="fadeIn animated bx bx-trash-alt" data-feather="trash-2"></i></a>
                                    </button>
                                @endif
                                @if(isset($user_access->is_approved) && !empty($user_access->is_approved) && $value->status==1 && $user_access->is_approved==1)
                                    <button>
                                        <a href="{{route('user-in-active',$value->id)}}">
                                            <i class="fadeIn animated bx bx-comment-check"
                                               data-feather="check-square"></i>
                                        </a>
                                    </button>
                                @elseif(isset($user_access->is_approved) && ($user_access->is_approved) && $user_access->is_approved==1)
                                    <button>
                                        <a href="{{route('user-active',$value->id)}}">
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
            <div class="modal fade hide  in" id="exampleModal" data-bs-keyboard="false" data-bs-backdrop="static" tabindex="-1" aria-labelledby="exampleModalLabel"
                 aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h1 class="modal-title fs-5" id="exampleModalLabel">{{ __('User Registration') }}</h1>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <form class="row g-3" method="POST" action="{{ route('user-save-update') }}">
                                @csrf
                                <input type="hidden" name="id" class="id" value=""/>
                                <div class="col-12">
                                    <label for="username" class="form-label">User Name</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-transparent">
                                            <i class="bx bxs-user"></i>
                                        </span>
                                        <input required name="name" type="text" class="form-control" id="user_name" placeholder="User Name">
                                    </div>
                                </div>
                                <div class="col-12">
                                    <label for="username" class="form-label">Employee Id</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-transparent">
                                            <i class="bx bxs-user"></i>
                                        </span>
                                        <input required name="employee_id" type="text" class="form-control" id="user_name" placeholder="Employee Id">
                                    </div>
                                </div>

                                <div class="col-12">
                                    <label class="form-label">Email Address</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-transparent">
                                            <i class="bx bxs-message"></i>
                                        </span>
                                        <input required name="email" type="email"  class="form-control" id="email" placeholder="Email Address">
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Choose Password</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-transparent">
                                            <i class="bx bxs-lock-open"></i>
                                        </span>
                                        <input name="password" required type="password" class="form-control" id="password" placeholder="Choose Password">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Confirm Password</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-transparent">
                                            <i class="bx bxs-lock"></i>
                                        </span>
                                        <input class="form-control" required id="confPassword" name="password_confirmation" type="password"  placeholder="Confirm Password">

                                    </div>
                                </div>
                                <div class="col-6">
                                    <label class="form-label">Phone No</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-transparent">
                                            <i class="bx bxs-microphone"></i>
                                        </span>
                                        <input required name="phone_no" type="text" class="form-control" id="phoneNo" placeholder="Phone No">

                                    </div>
                                </div>
                                <div class="col-6">
                                    <label class="form-label">{{ __('User Type') }}</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-transparent">
                                            <i class="bx bxs-key"></i>
                                        </span>
                                        <select id="user_type" class="form-control" name="userType">
                                            <option value="0">---Select Any One ---</option>
                                            @foreach($user_type as $v_type)
                                                <option value="{{$v_type->slug}}">{{$v_type->user_type}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <label for="image" class="form-label text-md-end">{{ __('Photo') }}</label>
                                    <div class="input-group">
                                        <input id="user_img1" type="hidden" name="user_img1"/>
                                        <input id="user_img" type="file" class="form-control" name="user_img">
                                    </div>
                                </div>
                                <div class="col-6">
                                    <label for="branch" class="form-label">{{ __('Branch') }}</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-transparent">
                                            <i class="bx bxs-home"></i></span>
                                        <select class="form-control" name="branch_id" id="branchId">
                                            <option value="0">---Select Any One ---</option>
                                            @foreach($branch as $v_branch)
                                                <option value="{{$v_branch->id}}">{{$v_branch->branch_name}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="gridCheck2">
                                        <label class="form-check-label" for="gridCheck2">Check me out</label>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <button type="submit" class="btn btn-info px-5">Register</button>
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
                url: "<?= URL::to('/user-edit'); ?>/" + id,
                type: 'get',
                data: {},
                dataType: 'JSON',
                success: function (response) {
                    $("#exampleModal").modal("show");
                    $(".id").val(response.id);
                    $("#user_name").val(response.name);
                    $("#phoneNo").val(response.contact_no);
                    $("#email").val(response.email);
                    $("#user_type").val(response.user_types);
                    $("#branchID").val(response.branch_id);
                    $("#user_img1").val(response.image);

                    $("#banner_img1").attr('value', '' + response.banner);
                    if (response.id) {
                        $('.submit').text("Update");
                        $('#exampleModalLabel').text("Update user");
                    }
                }
            });
        }
    </script>
@endsection
