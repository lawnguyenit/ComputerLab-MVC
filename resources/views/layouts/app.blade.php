<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta http-equiv="Cache-Control" content="no-store" />
    <meta http-equiv="Pragma" content="no-cache" />
    <meta http-equiv="Expires" content="0" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="Quản lý phòng máy">
    <!-- Favicon -->
    <link rel="icon" href="{{ asset('images/logo.png') }}" type="image/png">
    <title>@yield('title', 'Trang chủ')</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="font-awesome/css/font-awesome.css" rel="stylesheet">
    <link href="css/plugins/toastr/toastr.min.css" rel="stylesheet">
    <link href="js/plugins/jquery-ui/jquery-ui.min.css" rel="stylesheet">
    <link href="css/plugins/dataTables/datatables.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link href="css/animate.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
    <link href="css/style2.css" rel="stylesheet">
    <script src="https://unpkg.com/mqtt/dist/mqtt.min.js"></script> 
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://unpkg.com/html5-qrcode"></script>
    
    @yield('css')
    <style>
        /* Đảm bảo z-index cho các dropdown và datepicker */
        .flatpickr-calendar {
            z-index: 9999 !important;
        }
        
        .dropdown-menu {
            z-index: 1060 !important;
        }

    
        
        /* Đảm bảo DataTables hiển thị đúng */
        .dataTables_wrapper {
            width: 100%;
            overflow-x: auto;
        }
        
        /* Giữ nguyên các biến CSS gốc */
        :root {
            --sidebar-bg: #0a6e6e;
            --sidebar-hover: #0c8080;
            --sidebar-active: #0c8080;
            --sidebar-text: #ffffff;
            --content-bg: #e9ecef;
            --header-bg: #ffffff;
            --sidebar-width: 250px;
            --sidebar-width-collapsed: 70px;
            --topbar-height: 50px;
            --footer-height: 35px;
        }
    </style>
    
    @yield('css')
</head>

<style>
    :root {
        --sidebar-bg: #0a6e6e;
        --sidebar-hover: #0c8080;
        --sidebar-active: #0c8080;
        --sidebar-text: #ffffff;
        --content-bg: #e9ecef;
        --header-bg: #ffffff;
        --sidebar-width: 250px;
        --sidebar-width-collapsed: 70px;
        --topbar-height: 50px;
        --footer-height: 35px;
    }


    .wsidebar-collapse {
        background-color:#0a6e6e;
        width: 100%;
        display: flex;
        justify-content: center;
        align-items: center;
        color: #ffffff;
        font-size: 30px;
        height: 60px;
    }

    .wnavbar {
        background-color: #ffffff;
        border-bottom: 1px solid #e7e7e7;
        height: 60px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0 15px;
        width: 100%;
    }
    
    /* Thêm style cho logo và brand */
    .navbar-brand {
        display: flex;
        align-items: center;
        font-weight: bold;
        font-size: 18px;
        color: #333;
        text-decoration: none;
    }
    
    .navbar-brand i {
        margin-right: 10px;
        font-size: 20px;
        color: #333;
    }
    
    /* Style cho user profile */
    .user-profile {
        display: flex;
        align-items: center;
        cursor: pointer;
    }
    
    .avatar {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        background-color: #17a2b8;
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        margin-right: 8px;
    }
    
    .user-email {
        margin-right: 5px;
        font-size: 14px;
        color: #555;
    }
    
    /* Dropdown menu styles */
    .dropdown-menu {
        min-width: 200px;
        padding: 5px 0;
        margin: 2px 0 0;
        background-color: #fff;
        border: 1px solid rgba(0,0,0,.15);
        border-radius: 4px;
        box-shadow: 0 6px 12px rgba(0,0,0,.175);
        position: absolute;
        right: 0;
        top: 100%;
        z-index: 1000;
        display: none;
    }
    
    .dropdown.show .dropdown-menu {
        display: block;
    }
    
    .dropdown-user li {
        margin-bottom: 0;
    }
    
    .dropdown-user a {
        padding: 10px 20px;
        display: block;
        color: #333;
        text-decoration: none;
    }
    
    .dropdown-user a:hover {
        background-color: #f5f5f5;
    }
    
    .dropdown-user .divider {
        height: 1px;
        margin: 9px 0;
        overflow: hidden;
        background-color: #e5e5e5;
    }
    
    /* Thêm style cho icon caret */
    .caret-icon {
        transition: transform 0.3s;
    }
    
    .dropdown.show .caret-icon {
        transform: rotate(180deg);
    }

    .wsidebar {
        background-color: #0a6e6e;
        width: 100%;
        height: calc(100vh - 60px);
        overflow-y: auto;
        padding: 15px 0;
        color: #ffffff;
    }

    .wsidebar ul {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .wsidebar li {
        margin-bottom: 10px;
        height: 35px;
    }

    .wsidebar a {
        display: flex;
        align-items: center;
        color: #ffffff !important; /* Đảm bảo chữ màu trắng */
        text-decoration: none;
        padding: 10px 15px;
        border-radius: 5px;
        transition: background-color 0.3s ease;
    }

    .wsidebar a:hover {
        background-color: #0c8080;
        color: #ffffff !important; /* Đảm bảo chữ màu trắng khi hover */
    }

    .wsidebar a.active {
        background-color: #0c8080;
        color: #ffffff !important; /* Đảm bảo chữ màu trắng khi active */
        border-left: 3px solid white;
    }

    .wsidebar a i {
        margin-right: 10px;
        color: #ffffff !important; /* Đảm bảo icon màu trắng */
    }

    .wsidebar a span {
        font-size: 14px;
        color: #ffffff !important; /* Đảm bảo chữ màu trắng */
    }

    .wsidebar a:hover .tooltip-text {
        visibility: visible;
        color: #ffffff !important; /* Đảm bảo chữ tooltip màu trắng */
    }

    .wsidebar a .tooltip-text {
        visibility: hidden;
        background-color: #0c8080;
        color: #ffffff !important; /* Đảm bảo chữ tooltip màu trắng */
        text-align: center;
        border-radius: 5px;
        padding: 5px 10px;
        position: absolute;
        z-index: 1;
        top: 50%;
        margin-left: 10px;
        transform: translateY(-50%);
        opacity: 0;
        transition: opacity 0.3s ease;
    }

    .wsidebar a:hover.tooltip-text {
        opacity: 1;
    }

    .wsidebar a.active.tooltip-text {
        visibility: visible;
        opacity: 1;
    }

    .dropdown-menu {
        min-width: 200px;
        padding: 5px 0;
        margin: 2px 0 0;
        background-color: #fff;
        border: 1px solid rgba(0,0,0,.15);
        border-radius: 4px;
        box-shadow: 0 6px 12px rgba(0,0,0,.175);
        position: absolute;
        right: 0;
        top: 100%;
        z-index: 1000;
        display: none;
        overflow: hidden;
    }
    
    .dropdown.show .dropdown-menu {
        display: block;
    }
    
    .dropdown-user li {
        margin-bottom: 0;
        list-style: none;
    }
    
    .dropdown-user a {
        padding: 12px 20px;
        display: block;
        color: #333;
        text-decoration: none;
        transition: all 0.3s ease;
    }
    
    .dropdown-user a:hover {
        background-color: #f5f5f5;
        padding-left: 25px;
    }
    
    .dropdown-user .divider {
        height: 1px;
        margin: 0;
        overflow: hidden;
        background-color: #e5e5e5;
    }
    
    /* Thêm header cho dropdown */
    .dropdown-header {
        padding: 15px 20px;
        background-color: #17a2b8;
        color: white;
        font-weight: bold;
        border-bottom: 1px solid rgba(0,0,0,0.1);
    }
    
    /* Cải thiện icon trong dropdown */
    .dropdown-user a i {
        margin-right: 10px;
        width: 20px;
        text-align: center;
    }
    
    /* Cải thiện animation cho dropdown */
    .dropdown-menu {
        transform: translateY(-10px);
        opacity: 0;
        transition: all 0.3s ease;
        display: block;
        visibility: hidden;
    }
    
    .dropdown.show .dropdown-menu {
        transform: translateY(0);
        opacity: 1;
        visibility: visible;
    }
    
    /* Thêm style cho icon caret */
    .caret-icon {
        transition: transform 0.3s;
    }
    
    .dropdown.show .caret-icon {
        transform: rotate(180deg);
    }
    
    /* Cải thiện user profile */
    .user-profile {
        display: flex;
        align-items: center;
        cursor: pointer;
        padding: 5px 10px;
        border-radius: 4px;
        transition: background-color 0.3s ease;
    }
    
    .user-profile:hover {
        background-color: rgba(0,0,0,0.05);
    }
    
    .avatar {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        background-color: #17a2b8;
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        margin-right: 8px;
    }
    
    .user-email {
        margin-right: 5px;
        font-size: 14px;
        color: #555;
    }

    .navbar-top-links .dropdown-toggle {
        display: flex;
        align-items: center;
        padding: 15px;
        color: #333;
        text-decoration: none;
    }

    .navbar-top-links .dropdown-toggle i.fa-user {
        margin-right: 5px;
        font-size: 16px;
    }

    .navbar-top-links .dropdown-toggle i.fa-caret-down {
        margin-left: 5px;
        font-size: 12px;
    }
</style>


<body>
    <div id="wrapper">
        <nav class="navbar-default navbar-static-side" role="navigation">
            <div class="wsidebar-collapse">
                <i class="fa fa-school"></i>
            </div>
            <div class="wsidebar">
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                            <i class="fas fa-home"></i>
                            <span>Trang chủ</span>
                            <div class="tooltip-text">Trang chủ</div>
                        </a>
                    </li>
                    @php
                        $user = Auth::user();
                        $permission = $user::getPermissionNames($user->id);
                    @endphp


                    @if ($permission == 'Admin' || $permission == 'Quản trị người dùng')            
                    <li class="nav-item">
                        <a href="{{ route('users.index') }}" class="nav-link {{ request()->routeIs('users.index') ? 'active' : '' }}">
                            <i class="fas fa-users"></i>
                            <span>Người dùng</span>
                            <div class="tooltip-text">Người dùng</div>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('room-managers.index') }}" class="nav-link {{ request()->routeIs('room-managers.index')? 'active' : '' }}">
                            <i class="fas fa-user-shield"></i>
                            <span>Người quản lý</span>
                            <div class="tooltip-text">Người quản lý</div>
                        </a>
                    </li>
                    @endif

                    @if( $permission == 'Admin' || $permission == 'Quản trị phòng máy')
                    <li class="nav-item">
                        <a href="{{ route('rooms.index') }}" class="nav-link {{ request()->routeIs('rooms.index') ? 'active' : '' }}">
                        <i class="fas fa-desktop"></i>
                            <span>Phòng máy</span>
                            <div class="tooltip-text">Phòng máy</div>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('room-restrictions.index') }}" class="nav-link {{ request()->routeIs('room-restrictions.index') ? 'active' : '' }}">
                            <i class="fas fa-door-closed"></i>
                            <span>Khóa phòng máy</span>
                            <div class="tooltip-text">Khóa phòng máy</div>
                        </a>
                    </li>
                    @endif
                    @if ($permission == 'Admin' || $permission == 'Quản trị thiết bị')
                    <li class="nav-item">
                        <a href="{{ route('devices.index') }}" class="nav-link {{ request()->routeIs('devices.index') ? 'active' : '' }}">
                            <i class="fas fa-laptop"></i>
                            <span>Thiết bị</span>
                            <div class="tooltip-text">Thiết bị</div>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('sensors.index') }}" class="nav-link {{ request()->routeIs('sensors.index') ? 'active' : '' }}">
                            <i class="fas fa-microchip"></i>
                            <span>Cảm biến</span>
                            <div class="tooltip-text">Cảm biến</div>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('device-restrictions.index') }}" class="nav-link {{ request()->routeIs('device-restrictions.index')? 'active' : '' }}">
                            <i class="fas fa-door-closed"></i>
                            <span>Khóa thiết bị</span>
                            <div class="tooltip-text">Khóa thiết bị</div>
                        </a>
                    </li>
                    @endif
                    <li class="nav-item">
                        <a href="{{ route('access-room.index') }}" class="nav-link {{ request()->routeIs('access-room.index')? 'active' : '' }}">
                            <i class="fas fa-key"></i>
                            <span>Truy cập phòng</span>
                            <div class="tooltip-text">Truy cập phòng</div>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('datalist.index') }}" class="nav-link {{ request()->routeIs('datalist.index') ? 'active' : '' }}">
                            <i class="fas fa-database"></i>
                            <span>Dữ liệu cảm biến</span>
                            <div class="tooltip-text">Dữ liệu cảm biến</div>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('admin-room.index') }}" class="nav-link {{ request()->routeIs('admin-room.index')? 'active' : '' }}">
                            <i class="fas fa-desktop"></i>
                            <span>Quản trị phòng máy</span>
                            <div class="tooltip-text">Quản trin phòng máy</div>
                        </a>
                    <li class="nav-item">
                        <a href="{{ route('weather.index') }}" class="nav-link {{ request()->routeIs('weather.index') ? 'active' : '' }}">
                            <i class="fa fa-cloud"></i>
                            <span>Dự báo thời tiết</span>
                            <div class="tooltip-text">Dự báo thời tiết</div>
                        </a>
                    </li>
                </ul>
            </div>
        </nav>

        <div id="page-wrapper" class="gray-bg dashbard-1">
            <div class="row border-bottom">
                <nav class="wnavbar navbar-static-top" role="navigation">
                    <a class="navbar-minimalize minimalize-styl-2 btn btn-primary" href="#"><i class="fa fa-bars"></i> </a>

                    <div class="navbar-brand">
                        <i class="fa fa-desktop"></i>
                        <span>COMPUTER LAB</span>
                    </div>
                    <div class="dropdown">
                        <div class="user-profile" onclick="toggleDropdown()">
                            <div class="avatar">
                              
                                <img src="https://ui-avatars.com/api/?name={{ Auth::user()->ho_ten }}&background=17a2b8&color=fff&size=32&font-size=0.5&bold=true" 
                                     alt="User Avatar" 
                                     class="user-avatar"
                                     style="width: 32px; height: 32px; border-radius: 50%;">
                            </div>
                            <span class="user-email">{{ Auth::user()->ho_ten ?? 'user@example.com' }}</span>
                            <i class="fa fa-chevron-down caret-icon"></i>
                        </div>
                        <ul class="dropdown-menu dropdown-user">
                            <div class="dropdown-header">
                                Tài khoản của tôi
                            </div>
                            <li>
                            <a href="{{ route('profile') }}">
                                    <i class="fa fa-user-circle"></i> Hồ sơ cá nhân
                                </a>
                            </li>
                            <li>
                                <a href="">
                                    <i class="fa fa-cog"></i> Cài đặt tài khoản
                                </a>
                            </li>
                            <li class="divider"></li>
                            <li>
                                <a href="#" onclick="event.preventDefault();sessionStorage.clear(); document.getElementById('logout-form').submit();">
                                    <i class="fa fa-sign-out"></i> Đăng xuất
                                </a>
                                <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                                    @csrf
                                </form>
                            </li>
                        </ul>
                    </div>
                </nav>
            </div>
            <div class="dashboard-header">

                @yield('content')
            </div>
            @include('layouts.footer')
        </div>
    </div>

    

    <!-- JQuery -->
    <script src="js/jquery-3.1.1.min.js"></script>
    <!-- Jquery UI -->
    <script src="js/plugins/jquery-ui/jquery-ui.min.js"></script>
    <!-- Bootstrap -->
    <script src="js/bootstrap.min.js"></script>
    <!-- Metis Menu -->
    <script src="js/plugins/metisMenu/jquery.metisMenu.js"></script>
    <!-- SlimScroll -->
    <script src="js/plugins/slimscroll/jquery.slimscroll.min.js"></script>
    <!-- Custom and plugin javascript -->
    <script src="js/inspinia.js"></script>
    <!-- Loading progress bar -->
    <script src="js/plugins/pace/pace.min.js"></script>
    <!-- jQuery UI -->
    <script src="js/plugins/toastr/toastr.min.js"></script>
    <!-- Data Tables -->
    <script src="js/plugins/dataTables/datatables.min.js"></script>
    <!-- Flat Picker -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/flatpickr/4.6.13/flatpickr.min.js" integrity="sha512-K/oyQtMXpxI4+K0W7H25UopjM8pzq0yrVdFdG21Fh5dBe91I40pDd9A4lzNlHPHBIP2cwZuoxaUSX0GJSObvGA==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    @yield('js')
    @include('layouts.toast')
</body>

</html>

<!-- Thêm script để xử lý dropdown -->
<script>
    function toggleDropdown() {
        document.querySelector('.dropdown').classList.toggle('show');
    }
    
    // Đóng dropdown khi click ra ngoài
    window.onclick = function(event) {
        if (!event.target.matches('.user-profile') && !event.target.matches('.user-profile *')) {
            var dropdowns = document.getElementsByClassName("dropdown");
            for (var i = 0; i < dropdowns.length; i++) {
                var openDropdown = dropdowns[i];
                if (openDropdown.classList.contains('show')) {
                    openDropdown.classList.remove('show');
                }
            }
        }
    }
</script>
