<!doctype html>
<html lang="en">
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=Nunito" rel="stylesheet">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">

    <!-- Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="{{ asset('css/login.css') }}">

    <style>
        /* ส่วนของ navbar */
        .navbar-top {
            background-color: #279a3e;
            padding: 10px 0;
        }

        .navbar-bottom {
            background-color: #ffffff;
            padding: 20px 0; /* เพิ่ม padding เพื่อเพิ่มความสูง */
            font-size: 15px; /* เพิ่มขนาดตัวอักษร */
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
       
        }

        /* กำหนดสีให้ลิงก์ใน navbar-bottom */
        .navbar-bottom a {
            color: #4261c5;
            margin: 0 20px;
            text-transform: uppercase;
           
        }

        
    

        .navbar-bottom a:hover {
            color: #304998;
        }

        .navbar-brand img {
            width: 40px;
            height: 40px;
            margin-right: 8px;
        }
    </style>

    <!-- Scripts -->
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
</head>

<body>
    <div id="app">
        <!-- ส่วนบนของ Navbar -->
        <nav class="navbar-top">
            <div class="container d-flex justify-content-between align-items-center">
                <!-- Brand and Home link -->
                <a class="navbar-brand d-flex align-items-center " href="{{ route('admin.home') }}">
                    <img  src="https://www.svgrepo.com/show/163314/football.svg" alt="Football Icon">
                    ฟุตบอลคลับ
                </a>

                <!-- Authentication Links -->
                <ul class="navbar-nav">
                    @guest
                        @if (Route::has('login'))
                            <li class="nav-item">
                                <a class="nav-link text-white" href="{{ route('login') }}">
                                    <i class="fas fa-sign-in-alt me-1"></i> {{ __('เข้าสู่ระบบ') }}
                                </a>
                            </li>
                        @endif

                        @if (Route::has('register'))
                            <li class="nav-item">
                                <a class="nav-link text-white" href="{{ route('register') }}">
                                    <i class="fas fa-user-plus me-1"></i> {{ __('สมัครสมาชิก') }}
                                </a>
                            </li>
                        @endif
                    @else
                        <li class="nav-item dropdown">
                            <a id="navbarDropdown" class="nav-link dropdown-toggle text-white" href="#" role="button"
                                data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                                <i class="fas fa-user me-1"></i> {{ Auth::user()->fname }}
                            </a>
                            <div class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                                <a class="dropdown-item" href="{{ route('profile.edit') }}">
                                    <i class="fas fa-edit me-1"></i> {{ __('แก้ไขโปรไฟล์') }}
                                </a>
                                <a class="dropdown-item" href="{{ route('logout') }}"
                                    onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                    <i class="fas fa-sign-out-alt me-1"></i> {{ __('ออกจากระบบ') }}
                                </a>
                                <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                    @csrf
                                </form>
                            </div>
                        </li>
                    @endguest
                </ul>
            </div>
        </nav>

        <!-- ส่วนล่างของ Navbar -->
        <nav class="navbar-bottom">
            <div class="container d-flex justify-content-start">
                <a href="{{ route('booking') }}">
                    <i class="fas fa-calendar-alt me-1"></i> {{ __('จองสนาม') }}
                </a>
                <a href="{{ route('booking.detail', ['id' => $booking_stadium_id ?? 'null']) }}">
                    <i class="fas fa-calendar-alt me-1"></i> {{ __('รายละเอียดการจองสนาม') }}
                </a>
                <a href="{{ route('lending.index') }}">
                    <i class="fas fa-basketball-ball me-1"></i> {{ __('ยืมอุปกรณ์') }}
                </a>
                <a href="{{ route('lending.borrow-detail') }}">
                    <i class="fas fa-basketball-ball me-1"></i> {{ __('รายละเอียดการยืมอุปกรณ์') }}
                </a>
            </div>
        </nav>

        <!-- Main Content -->
        <main class="py-4">
            @yield('content')
        </main>
    </div>

    <!-- Scripts -->
    @stack('scripts')

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
