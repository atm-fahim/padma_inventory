@extends('layouts.app')

@section('content')
    <div class="container card border-top border-0 border-4 border-primary">
        <div class="col-xl-9 mx-auto">
            <div class="card">
                <div class="card-body">
                    @if($user_access->is_approved==1)
                        <div class="input-group mb-3">
                            <select class="form-select type_value" required="" aria-describedby="button-addon2">
                                <option selected="" disabled="" value="">Choose...</option>
                                @foreach($access_type as $access_value)
                                    <option value="{{$access_value->slug}}">{{$access_value->user_type}}</option>
                                @endforeach
                            </select>
                            <button class="btn btn-outline-secondary" onclick="search()" type="button"
                                    id="button-addon2">
                                Search
                            </button>
                        </div>
                    @endif
                </div>
                <div class="row justify-content-center">
                    <div class="col-lg-12">
                        <form method="POST" action="{{ route('give-permission') }}" enctype="multipart/form-data">
                            @csrf
                            <input class="user_types" type='hidden' name='user_role_types[]' value=''/>
                            <table  class="table table-bordered mb-0">
                                <thead>
                                <tr>
                                    <th>SL</th>
                                    <th><input type="checkbox" id="select-all"/> Menu</th>
                                    <th>Create</th>
                                    <th>Edit</th>
                                    <th>Delete</th>
                                    <th>View</th>
                                    <th>Cancel</th>
                                    <th>Request</th>
                                    <th>Approved</th>
                                </tr>
                                </thead>
                                <tbody class="show_permission_menu">
                                </tbody>
                                <tfoot>

                                </tfoot>
                            </table>
                            <div class="col" style="float: right; padding: 10px;">
                                <button type="submit"
                                        class="btn btn-outline-success px-5 radius-30"> {{ __('Save') }}</button>
                            </div>
                        </form>
                    </div>
                    <!-- Button trigger modal -->
                </div>
            </div>
        </div>
        <script type="text/javascript">
            function search() {
                let selectValue = $('.type_value').val();
                $('.user_types').val(selectValue);
                $.ajax({
                    url: "<?= URL::to('/show-permission-menu'); ?>/" + selectValue,
                    type: 'get',
                    data: {},
                    success: function (response) {
                        $('.show_permission_menu').html(response);
                    }
                });
            }
        </script>

@endsection

