
<!doctype html>
<html lang="en">
<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!--favicon-->
    <link rel="icon" href="{{asset('backend/assets/images/favicon-32x32.png')}}" type="image/png')}}"/>
    <!--plugins-->


    <link rel="stylesheet" href="{{asset('backend/assets/css/select2.min.css')}}" />
    <link rel="stylesheet" href="{{asset('backend/assets/css/select2-bootstrap-5-theme.min.css')}}" />
    {{--    <link href="{{asset('backend/assets/plugins/vectormap/jquery-jvectormap-2.0.2.css')}}" rel="stylesheet"/>--}}
    <link href="{{asset('backend/assets/plugins/simplebar/css/simplebar.css')}}" rel="stylesheet"/>
    <link href="{{asset('backend/assets/plugins/perfect-scrollbar/css/perfect-scrollbar.css')}}" rel="stylesheet"/>
    <link href="{{asset('backend/assets/plugins/metismenu/css/metisMenu.min.css')}}" rel="stylesheet"/>
    <link href="{{asset('backend/assets/plugins/datatable/css/dataTables.bootstrap5.min.css')}}" rel="stylesheet"/>
    <!-- loader-->
    {{--    <link href="{{asset('backend/assets/css/pace.min.css')}}" rel="stylesheet" />--}}
    {{--    <script src="{{asset('backend/assets/js/pace.min.js')}}"></script>--}}
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
    <link href="{{asset('backend/assets/css/bootstrap.min.css')}}" rel="stylesheet">
    <link href="{{asset('backend/assets/css/bootstrap-extended.css')}}" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500&amp;display=swap" rel="stylesheet">
    <link href="{{asset('backend/assets/css/app.css')}}" rel="stylesheet">
    <link href="{{asset('backend/assets/css/icons.css')}}" rel="stylesheet">
    <link href="{{asset('css/custom.css')}}" rel="stylesheet">

    <!-- Theme Style CSS -->
    <link rel="stylesheet" href="{{asset('backend/assets/css/dark-theme.css')}}"/>
    <link rel="stylesheet" href="{{asset('backend/assets/css/semi-dark.css')}}"/>
    <link rel="stylesheet" href="{{asset('backend/assets/css/header-colors.css')}}"/>
    <link href="{{asset('backend/assets/plugins/datetimepicker/css/classic.css')}}" rel="stylesheet" />
    <link href="{{asset('backend/assets/plugins/datetimepicker/css/classic.time.css')}}" rel="stylesheet" />
    <link href="{{asset('backend/assets/plugins/datetimepicker/css/classic.date.css')}}" rel="stylesheet" />
    <link rel="stylesheet" href="{{asset('backend/assets/plugins/bootstrap-material-datetimepicker/css/bootstrap-material-datetimepicker.min.css')}}">
    <link rel="stylesheet" href="{{asset('backend/assets/plugins/smart-wizard/css/smart_wizard_all.min.css')}}"/>
    <title>{{env('APP_NAME')}}</title>
</head>

<body>
<!--wrapper-->
<div class="wrapper">
    <!--sidebar wrapper -->
    <div class="sidebar-wrapper" data-simplebar="true">
        <a href="{{route('dashboard')}}">
        <div class="sidebar-header">
            <div>
                <img src="{{asset('backend/assets/images/logo-icon.png')}}" class="logo-icon" alt="logo icon">
            </div>
            <div>
                <strong>PADMA BANK</strong>
            </div>
            <div class="toggle-icon ms-auto"><i class='bx bx-arrow-to-left'></i>
            </div>
        </div>
        </a>

        <!--navigation-->
        <ul class="metismenu" id="menu">

            <?php

            if(!empty(auth()->user()->user_types)){
                $user_type = auth()->user()->user_types;
                $parentMenu = DB::select("SELECT t1.id,t1.menu_name,t1.slug,t1.parent_id,t1.menu_url,t1.icon
                   FROM backend_menu t1 WHERE t1.status=1 AND t1.parent_id=0
    AND t1.id IN (SELECT t2.menu_id FROM access_menu t2 WHERE  t2.status=1 AND t2.user_type='$user_type') ORDER BY t1.menu_name");

            foreach ($parentMenu as $p_menu){
                ?>
            <li>

                <a href="javascript:;" class="has-arrow">
                    <div class="parent-icon"><i class='bx bx-{{$p_menu->icon}}'></i>
                    </div>
                    <div class="menu-title">{{$p_menu->menu_name}}</div>
                </a>
                <ul>
                        <?php
                        $childMenu = DB::select("SELECT t1.ID,t1.menu_name,t1.slug,t1.parent_id,t1.menu_url,t1.icon
                   FROM backend_menu t1 WHERE t1.status=1 AND t1.parent_id='$p_menu->id'
    AND t1.id IN (SELECT t2.menu_id FROM access_menu t2 WHERE  t2.status=1 AND t2.user_type='$user_type') ORDER BY t1.menu_name");

                    foreach ($childMenu as $v_child_menu){
                        ?>
                        <?php //echo "$v_child_menu->menu_url";?>
                    <li><a href="{{route($v_child_menu->menu_url)}}"><i class="bx bx-right-arrow-alt"></i>{{$v_child_menu->menu_name}}</a></li>
                        <?php
                    }
                        ?>
                </ul>
            </li>
                <?php
            }
            }
            ?>
        </ul>
        <!--end navigation-->
    </div>
    <!--end sidebar wrapper -->
    <!--start header -->
    <header>
        <div class="topbar d-flex align-items-center">
            <nav class="navbar navbar-expand">
                <div class="mobile-toggle-menu"><i class='bx bx-menu'></i>
                </div>
                <div class="search-bar flex-grow-1">
                    <div class="position-relative search-bar-box">
                                            <a target="_blank" class="btn btn-info" href="{{route('task-login')}}"><div>
                                                    <strong>Go to Task</strong>
                                                </div></a>
{{--                        <input type="text" class="form-control search-control" placeholder="Type to search..."> <span--}}
{{--                            class="position-absolute top-50 search-show translate-middle-y"><i class='bx bx-search'></i></span>--}}
{{--                        <span class="position-absolute top-50 search-close translate-middle-y"><i--}}
{{--                                class='bx bx-x'></i></span>--}}
                    </div>
                </div>
                <div class="top-menu ms-auto">
                    <ul class="navbar-nav align-items-center">
                        <li class="nav-item mobile-search-icon">
                            <a class="nav-link" href="#"> <i class='bx bx-search'></i>
                            </a>
                        </li>
                        <li class="nav-item dropdown dropdown-large">
                            <a class="nav-link dropdown-toggle dropdown-toggle-nocaret" href="#" role="button"
                               data-bs-toggle="dropdown" aria-expanded="false"> <i class='bx bx-category'></i>
                            </a>
                            <div class="dropdown-menu dropdown-menu-end">

                            </div>
                        </li>
                        <li class="nav-item dropdown dropdown-large">
                            <a class="nav-link dropdown-toggle dropdown-toggle-nocaret position-relative" href="#"
                               role="button" data-bs-toggle="dropdown" aria-expanded="false"> <span class="alert-count">0</span>
                                <i class='bx bx-bell'></i>
                            </a>
                            <div class="dropdown-menu dropdown-menu-end">
                                <a href="javascript:;">
                                    <div class="msg-header">
                                        <p class="msg-header-title">Notifications</p>
                                        <p class="msg-header-clear ms-auto">Marks all as read</p>
                                    </div>
                                </a>
                                <div class="header-notifications-list">
                                    <a class="dropdown-item" href="javascript:;">
                                        <div class="d-flex align-items-center">
                                            <div class="notify bg-light-primary text-primary"><i
                                                    class="bx bx-group"></i>
                                            </div>
                                            <div class="flex-grow-1">
                                                <h6 class="msg-name">New Customers<span class="msg-time float-end">14 Sec
												ago</span></h6>
                                                <p class="msg-info">5 new user registered</p>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                                <a href="javascript:;">
                                    <div class="text-center msg-footer">View All Notifications</div>
                                </a>
                            </div>
                        </li>
                        <li class="nav-item dropdown dropdown-large">
                            <a class="nav-link dropdown-toggle dropdown-toggle-nocaret position-relative" href="#"
                               role="button" data-bs-toggle="dropdown" aria-expanded="false"> <span class="alert-count">0</span>
                                <i class='bx bx-comment'></i>
                            </a>
                            <div class="dropdown-menu dropdown-menu-end">
                                <a href="javascript:;">
                                    <div class="msg-header">
                                        <p class="msg-header-title">Messages</p>
                                        <p class="msg-header-clear ms-auto">Marks all as read</p>
                                    </div>
                                </a>
                                <div class="header-message-list">
                                    <a class="dropdown-item" href="javascript:;">
                                        <div class="d-flex align-items-center">
                                            <div class="user-online">
                                                <img src="{{asset('backend/assets/images/avatars/avatar-1.png')}}"
                                                     class="msg-avatar" alt="user avatar">
                                            </div>
                                            <div class="flex-grow-1">
                                                <h6 class="msg-name">Daisy Anderson <span class="msg-time float-end">5 sec
												ago</span></h6>
                                                <p class="msg-info">The standard chunk of lorem</p>
                                            </div>
                                        </div>
                                    </a>

                                </div>
                                <a href="javascript:;">
                                    <div class="text-center msg-footer">View All Messages</div>
                                </a>
                            </div>
                        </li>
                    </ul>
                </div>
                <div class="user-box dropdown">
                    <a class="d-flex align-items-center nav-link dropdown-toggle dropdown-toggle-nocaret" href="#"
                       role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <img src="{{asset('backend/assets/images/avatars/avatar-2.png')}}" class="user-img"
                             alt="user avatar">
                        <div class="user-info ps-3">
                            <p class="user-name mb-0"><?php if(!empty(auth()->user()->name)){ echo strtoupper(auth()->user()->name); }else{ echo 'Padma Bank';}?></p>
                            <p class="designattion mb-0"><?php if(!empty(auth()->user()->user_types)){ echo strtoupper(auth()->user()->user_types); }else{ echo 'Padma Bank';}?></p>
                        </div>
                    </a>
{{--                    <a href="{{route('task-login')}}"><div>--}}
{{--                            <strong>Task</strong>--}}
{{--                        </div></a>--}}
                    <ul class="dropdown-menu dropdown-menu-end">
{{--                        <li><a class="dropdown-item" href="javascript:;"><i class="bx bx-user"></i><span>Profile</span></a>--}}
{{--                        </li>--}}
{{--                        <li><a class="dropdown-item" href="javascript:;"><i class="bx bx-cog"></i><span>Settings</span></a>--}}
{{--                        </li>--}}
{{--                        <li><a class="dropdown-item" href="javascript:;"><i class='bx bx-home-circle'></i><span>Dashboard</span></a>--}}
{{--                        </li>--}}
{{--                        <li><a class="dropdown-item" href="javascript:;"><i class='bx bx-dollar-circle'></i><span>Earnings</span></a>--}}
{{--                        </li>--}}
{{--                        <li><a class="dropdown-item" href="javascript:;"><i--}}
{{--                                    class='bx bx-download'></i><span>Downloads</span></a>--}}
{{--                        </li>--}}
{{--                        <li>--}}
{{--                            <div class="dropdown-divider mb-0"></div>--}}
{{--                        </li>--}}
                        <li>
                            <a class="dropdown-item" href="{{ route('logout') }}" onclick="event.preventDefault();
                               document.getElementById('logout-form').submit();"><i class='bx bx-log-out-circle'></i><span>Logout</span></a>
                            <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                @csrf
                            </form>
                        </li>
                    </ul>
                </div>
            </nav>
        </div>
    </header>
    <!--end header -->
    <!--start page wrapper -->
    <div class="page-wrapper">
        <div class="page-content">
            @yield('content')
        </div>
    </div>
    <!--end page wrapper -->
    <!--start overlay-->
    <div class="overlay toggle-icon"></div>
    <!--end overlay-->
    <!--Start Back To Top Button-->
    <a href="javaScript:;" class="back-to-top"><i class='bx bxs-up-arrow-alt'></i></a>
    <!--End Back To Top Button-->
    <footer class="page-footer">
        <p class="mb-0">Copyright © <?php echo  date("Y");?>. All right reserved.</p>
    </footer>
</div>
<!--end wrapper-->
<!--start switcher-->
<div class="switcher-wrapper">
    <div class="switcher-btn"><i class='bx bx-cog bx-spin'></i>
    </div>
    <div class="switcher-body">
        <div class="d-flex align-items-center">
            <h5 class="mb-0 text-uppercase">Theme Customizer</h5>
            <button type="button" class="btn-close ms-auto close-switcher" aria-label="Close"></button>
        </div>
        <hr/>
        <h6 class="mb-0">Theme Styles</h6>
        <hr/>
        <div class="d-flex align-items-center justify-content-between">
            <div class="form-check">
                <input class="form-check-input" type="radio" name="flexRadioDefault" id="lightmode" checked>
                <label class="form-check-label" for="lightmode">Light</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="radio" name="flexRadioDefault" id="darkmode">
                <label class="form-check-label" for="darkmode">Dark</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="radio" name="flexRadioDefault" id="semidark">
                <label class="form-check-label" for="semidark">Semi Dark</label>
            </div>
        </div>
        <hr/>
        <div class="form-check">
            <input class="form-check-input" type="radio" id="minimaltheme" name="flexRadioDefault">
            <label class="form-check-label" for="minimaltheme">Minimal Theme</label>
        </div>
        <hr/>
        <h6 class="mb-0">Header Colors</h6>
        <hr/>
        <div class="header-colors-indigators">
            <div class="row row-cols-auto g-3">
                <div class="col">
                    <div class="indigator headercolor1" id="headercolor1"></div>
                </div>
                <div class="col">
                    <div class="indigator headercolor2" id="headercolor2"></div>
                </div>
                <div class="col">
                    <div class="indigator headercolor3" id="headercolor3"></div>
                </div>
                <div class="col">
                    <div class="indigator headercolor4" id="headercolor4"></div>
                </div>
                <div class="col">
                    <div class="indigator headercolor5" id="headercolor5"></div>
                </div>
                <div class="col">
                    <div class="indigator headercolor6" id="headercolor6"></div>
                </div>
                <div class="col">
                    <div class="indigator headercolor7" id="headercolor7"></div>
                </div>
                <div class="col">
                    <div class="indigator headercolor8" id="headercolor8"></div>
                </div>
            </div>
        </div>
        <hr/>
        <h6 class="mb-0">Sidebar Colors</h6>
        <hr/>
        <div class="header-colors-indigators">
            <div class="row row-cols-auto g-3">
                <div class="col">
                    <div class="indigator sidebarcolor1" id="sidebarcolor1"></div>
                </div>
                <div class="col">
                    <div class="indigator sidebarcolor2" id="sidebarcolor2"></div>
                </div>
                <div class="col">
                    <div class="indigator sidebarcolor3" id="sidebarcolor3"></div>
                </div>
                <div class="col">
                    <div class="indigator sidebarcolor4" id="sidebarcolor4"></div>
                </div>
                <div class="col">
                    <div class="indigator sidebarcolor5" id="sidebarcolor5"></div>
                </div>
                <div class="col">
                    <div class="indigator sidebarcolor6" id="sidebarcolor6"></div>
                </div>
                <div class="col">
                    <div class="indigator sidebarcolor7" id="sidebarcolor7"></div>
                </div>
                <div class="col">
                    <div class="indigator sidebarcolor8" id="sidebarcolor8"></div>
                </div>
            </div>
        </div>
    </div>
</div>
<!--end switcher-->

<script src="{{asset('backend/assets/js/jquery-3.6.0.min.js')}}"></script>
<!-- Bootstrap JS -->
<script src="{{asset('backend/assets/js/bootstrap.bundle.min.js')}}"></script>
<!--plugins-->

<script src="{{asset('backend/assets/js/jquery.min.js')}}"></script>
<script src="{{asset('backend/assets/plugins/datetimepicker/js/picker.js')}}"></script>
<script src="{{asset('backend/assets/plugins/datetimepicker/js/picker.time.js')}}"></script>
<script src="{{asset('backend/assets/plugins/datetimepicker/js/picker.date.js')}}"></script>
<script src="{{asset('backend/assets/plugins/bootstrap-material-datetimepicker/js/moment.min.js')}}"></script>
<script src="{{asset('backend/assets/plugins/bootstrap-material-datetimepicker/js/bootstrap-material-datetimepicker.min.js')}}"></script>
<script src="{{asset('backend/assets/plugins/simplebar/js/simplebar.min.js')}}"></script>
<script src="{{asset('backend/assets/plugins/metismenu/js/metisMenu.min.js')}}"></script>
<script src="{{asset('backend/assets/plugins/perfect-scrollbar/js/perfect-scrollbar.js')}}"></script>
{{--<script src="{{asset('backend/assets/plugins/vectormap/jquery-jvectormap-2.0.2.min.js')}}"></script>--}}
{{--<script src="{{asset('backend/assets/plugins/vectormap/jquery-jvectormap-world-mill-en.js')}}"></script>--}}
<script src="{{asset('backend/assets/plugins/chartjs/js/Chart.min.js')}}"></script>
<script src="{{asset('backend/assets/plugins/chartjs/js/Chart.extension.js')}}"></script>
<script src="{{asset('backend/assets/plugins/smart-wizard/js/jquery.smartWizard.min.js')}}"></script>
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js" integrity="sha256-T0Vest3yCU7pafRw9r+settMBX6JkKN06dqBnpQ8d30=" crossorigin="anonymous"></script>

<script src="{{asset('backend/assets/plugins/select2/js/select2.min.js')}}"></script>
<script src="{{asset('assets/plugins/select2/js/select2-custom.js')}}"></script>
<script src="{{asset('backend/assets/plugins/datatable/js/jquery.dataTables.min.js')}}"></script>
<script src="{{asset('backend/assets/plugins/datatable/js/dataTables.bootstrap5.min.js')}}"></script>
{{--<script src="{{asset('backend/assets/js/index.js')}}"></script>--}}

<script src="{{asset('backend/assets/js/feather.min.js')}}"></script>
<!--app JS-->
{{--<script src="{{asset('editor/tiny_mce.js')}}"></script>--}}
<script src="{{asset('backend/assets/js/app.js')}}"></script>
<script src="{{ asset('tinymce/tinymce.min.js') }}" referrerpolicy="origin"></script>
<script>
    tinymce.init({
        selector: 'textarea.tynyDetails', // Replace this CSS selector to match the placeholder element for TinyMCE
        plugins: 'image styles advlist lists table',
        images_upload_url: 'postAcceptor.php',
        automatic_uploads: false,
        toolbar_sticky: true,
        autosave_ask_before_unload: true,
        autosave_interval: "30s",
        autosave_prefix: "{path}{query}-{id}-",
        autosave_restore_when_empty: false,
        autosave_retention: "2m",
        image_advtab: true,
        toolbar: 'undo redo | bold italic underline strikethrough | fontfamily fontsize blocks | alignleft aligncenter alignright alignjustify | outdent indent |  numlist bullist | forecolor backcolor removeformat | pagebreak | charmap emoticons | fullscreen  preview save print | insertfile image media template link anchor codesample | ltr rtl | table tabledelete'
    });
</script>
<script>
    feather.replace()
    $(document).ready(function() {
        $('#example').DataTable();
    } );
</script>

<style>
    .tox-promotion{
        display:none;
    }
    .tox-statusbar__branding{
        display:none;
    }
    .select2-results {
        max-height: 300px; /* Set desired height */
        overflow-y: auto; /* Enable vertical scrolling */
    }
</style>
<script>
    $(document).ready(function() {
        var table = $('#example2').DataTable( {
            lengthChange: false,
            title: 'Data export',
            buttons: [ 'copy', 'excel', 'pdf', 'print']
        } );

        table.buttons().container()
            .appendTo( '#example2_wrapper .col-md-6:eq(0)' );

    });
    $('.single-select').select2({
        dropdownParent: $('#exampleModal'),
        theme: 'bootstrap4',
        placeholder: $(this).data('placeholder'),
        allowClear: Boolean($(this).data('allow-clear')),

    });
    $( '#single-select-field' ).select2( {
        theme: 'bootstrap4',
        placeholder: $(this).data('placeholder'),
        allowClear: Boolean($(this).data('allow-clear')),
    } );


    $('.single-select-1').select2({
        dropdownParent: $('#exampleModal'),
        theme: 'bootstrap4',
        placeholder: $(this).data('placeholder'),
        allowClear: Boolean($(this).data('allow-clear')),
    });
    $('.single-select-2').select2({
        dropdownParent: $('#exampleModal'),
        theme: 'bootstrap4',
        placeholder: $(this).data('placeholder'),
        allowClear: Boolean($(this).data('allow-clear')),
    });
    $('.single-select-3').select2({
        dropdownParent: $('#exampleModal'),
        theme: 'bootstrap4',
        placeholder: $(this).data('placeholder'),
        allowClear: Boolean($(this).data('allow-clear')),
    });
    $('.single-select-4').select2({
        theme: 'bootstrap4',
        placeholder: $(this).data('placeholder'),
        allowClear: Boolean($(this).data('allow-clear')),
    });
    $('.single-select-5').select2({
        theme: 'bootstrap4',
        placeholder: $(this).data('placeholder'),
        allowClear: Boolean($(this).data('allow-clear')),
        dropdownAutoWidth: true,
        width: '100%'
    });
</script>
<script>
    $(document).ready(function() {
        const datepicker = $('.datepicker').pickadate({
            selectMonths: true,
            selectYears: true,
            format: 'yyyy-mm-dd',
            editable: true,
        });

        // Open the date picker when the button is clicked
        $('.open-datepicker').on('click', function() {
            datepicker.pickadate('open');
        });

        // Optional: Allow closing the picker on outside click
        $(document).on('click', function(e) {
            if (!$(e.target).closest('.datepicker, .open-datepicker').length) {
                datepicker.pickadate('close');
            }
        });

        // Optional: Validate manual input
        datepicker.on('close', function() {
            const selectedDate = datepicker.pickadate('get');
            if (!selectedDate) {
                // Clear the input if no date was selected
                $('.datepicker').val('');
            }
        });
    });

    $(function () {
        $('#date-time').bootstrapMaterialDatePicker({
            format: 'YYYY-MM-DD HH:mm'
        });
        $('#date-time2').bootstrapMaterialDatePicker({
            format: 'YYYY-MM-DD HH:mm'
        });
        $('#date').bootstrapMaterialDatePicker({
            time: false
        });
        $('#time').bootstrapMaterialDatePicker({
            date: false,
            format: 'HH:mm'
        });
    });

    $(function () {
        $('[data-bs-toggle="popover"]').popover();
        $('[data-bs-toggle="tooltip"]').tooltip();
    })
</script>
</body>
</html>
