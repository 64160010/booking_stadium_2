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
        /* Navbar styling based on user role */
        .navbar-top {
            background-color: {{ Auth::check() && Auth::user()->is_admin ? '#8b0000' : '#3f8cdf' }};
            padding: 10px 0;
        }

        /* Login and register button styles */
        .navbar-nav .nav-link {
            background-color: transparent;
            color: #ffffff;
            border: 2px solid #ffffff;
            border-radius: 15px;
            padding: 5px 10px;
            transition: background-color 0.3s ease, transform 0.3s ease, box-shadow 0.3s ease;
            display: flex;
            justify-content: center;
            align-items: center;
            min-width: 100px;
            height: 40px;
            font-weight: 600;
            text-align: center;
        }

        /* Hover effect for buttons */
        .navbar-nav .nav-link:hover {
            background-color: transparent;
            color: #ffffff;
            border-color: white;
            transform: translateY(-3px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        /* Margin between buttons */
        .navbar-nav .nav-item {
            margin-left: 10px;
            margin-bottom: 5px;
        }

        /* Bottom navbar */
        .navbar-bottom {
            background-color: #ffffff;
            padding: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .navbar-bottom a {
            color: #343a40;
            font-size: 16px;
            text-decoration: none;
            margin-right: 20px;
            padding: 8px 12px;
            border-radius: 5px;
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        .navbar-bottom a:hover {
            background-color: #f8f9fa;
            color: #343a40;
        }

        .navbar-brand {
            font-size: 36px;
            font-weight: bold;
            color: #ffffff;
            text-decoration: none;
            font-family: 'Arial', sans-serif;
            transition: color 0.3s ease;
        }

        .navbar-brand img {
            width: 50px;
            height: auto;
            margin-right: 15px;
            transition: transform 0.3s ease;
        }

        .navbar-brand:hover {
            color: #ffc107;
        }

        .navbar-brand:hover img {
            transform: scale(1.1);
        }

        .navbar-brand span {
            color: #ffffff;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5);
        }
    </style>

    <!-- Scripts -->
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
</head>

<body>
    <div id="app">
        <!-- Navbar Top Section -->
        <nav class="navbar-top">
            <div class="container d-flex justify-content-between align-items-center">
                <a class="navbar-brand d-flex align-items-center" href="{{ route('admin.home') }}">
                    <img src="https://www.svgrepo.com/show/163314/football.svg" alt="Football Icon">
                    <span>{{ Auth::check() && Auth::user()->is_admin ? 'การจัดการ ฟุตบอลคลับ' : 'ฟุตบอลคลับ' }}</span>
                </a>

                <!-- Authentication Links -->
                <ul class="navbar-nav">
                    @guest
                        @if (Route::has('login'))
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('login') }}">
                                    <i class="fas fa-sign-in-alt me-1"></i> {{ __('เข้าสู่ระบบ') }}
                                </a>
                            </li>
                        @endif

                        @if (Route::has('register'))
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('register') }}">
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

        <!-- Navbar Bottom Section -->
        <nav class="navbar-bottom">
            <div class="container d-flex justify-content-start">
                <a href="{{ route('booking') }}">
                    <i class="fas fa-calendar-alt"></i> {{ __('จองสนาม') }}
                </a>
                <a href="{{ route('booking.detail', ['id' => $booking_stadium_id ?? 'null']) }}">
                    <i class="fas fa-info-circle"></i> {{ __('รายละเอียดการจองสนาม') }}
                </a>
                <a href="{{ route('history.booking', ['id' => $booking_stadium_id ?? 'null']) }}">
                    <i class="fas fa-info-circle"></i> {{ __('ประวัติการจอง') }}
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
    <!-- Bootstrap CSS -->
<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
<!-- jQuery and Bootstrap JS -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

</body>

</html>
