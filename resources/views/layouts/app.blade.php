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

/* สไตล์สำหรับปุ่มเข้าสู่ระบบและสมัครสมาชิก */
.navbar-nav .nav-link {
    background-color: transparent; /* พื้นหลังโปร่งใส */
    color: #ffffff; /* ข้อความสีเขียว */
    border: 2px solid #ffffff; /* กรอบสีเขียว */
    border-radius: 15px; /* ขอบโค้งมน 15px */
    padding: 5px 10px; /* เพิ่ม padding เพื่อให้ปุ่มใหญ่ขึ้น */
    transition: background-color 0.3s ease, transform 0.3s ease, box-shadow 0.3s ease; /* เพิ่มการเปลี่ยนสี, การเคลื่อนที่ และเงา */
    display: flex; /* ใช้ flexbox */
    justify-content: center; /* จัดข้อความให้อยู่กลาง */
    align-items: center; /* จัดข้อความให้อยู่กลางในแนวตั้ง */
    min-width: 100px; /* กำหนดความกว้างขั้นต่ำ */
    height: 40px; /* กำหนดความสูงของปุ่ม */
    font-weight: 600; /* เพิ่มความหนาของข้อความ */
    text-align: center; /* จัดข้อความให้อยู่ตรงกลาง */
}

/* สีพื้นหลังและเอฟเฟกต์เมื่อ hover */
.navbar-nav .nav-link:hover {
    background-color: transparent; /* เปลี่ยนสีพื้นหลังเป็นเขียวเมื่อ hover */
    color: #ffffff; /* เปลี่ยนข้อความเป็นสีขาวเมื่อ hover */
    border-color: white;
    transform: translateY(-3px); /* การเคลื่อนที่ขึ้นเล็กน้อยเมื่อ hover */
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2); /* เพิ่มเงาเมื่อ hover */
}

/* เพิ่ม margin ระหว่างปุ่ม */
.navbar-nav .nav-item {
    margin-left: 10px; /* เพิ่มระยะห่างระหว่างปุ่ม */
    margin-bottom:5px;
}



        /* ส่วนของ navbar */
        .navbar-top {
            background-color: #3f8cdf;
            padding: 10px 0;
            
        }

        .navbar-bottom {
        background-color: #ffffff; /* เปลี่ยนเป็นพื้นหลังสีขาว */
        padding: 10px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); /* เพิ่มเงา */
    }

    .navbar-bottom a {
        color: #343a40; /* เปลี่ยนสีตัวอักษรเป็นสีเทาเข้ม */
        font-size: 16px;
        text-decoration: none;
        margin-right: 20px;
        padding: 8px 12px;
        border-radius: 5px;
        transition: background-color 0.3s ease, color 0.3s ease;
    }
        
    
    .navbar-bottom a:hover {
        background-color: #f8f9fa; /* เปลี่ยนสีพื้นหลังเมื่อ hover */
        color: #343a40; /* เปลี่ยนสีตัวอักษรเมื่อ hover */
    }

    .navbar-bottom i {
        margin-right: 8px; /* เว้นระยะระหว่างไอคอนกับข้อความ */
    }
    .navbar-brand {
        font-size: 36px; /* ขนาดตัวอักษรใหญ่ขึ้น */
        font-weight: bold; /* ตัวอักษรหนา */
        color: #ffffff; /* สีตัวอักษรเป็นสีขาว */
        text-decoration: none; /* เอาเส้นใต้ลิงก์ออก */
        font-family: 'Arial', sans-serif; /* ใช้ฟอนต์ที่ทันสมัย */
        transition: color 0.3s ease; /* เอฟเฟกต์การเปลี่ยนสีเมื่อ hover */
    }

    .navbar-brand img {
        width: 50px; /* ขนาดของโลโก้ใหญ่ขึ้น */
        height: auto;
        margin-right: 15px;
        transition: transform 0.3s ease;
    
    }
    .navbar-brand:hover {
        color: #ffc107; /* เปลี่ยนสีเป็นสีเหลืองเมื่อ hover */
    }


    .navbar-brand:hover img {
        transform: scale(1.1); /* ขยายขนาดโลโก้เล็กน้อยเมื่อ hover */
    }

    .navbar-brand span {
        color: #ffffff; /* สีของตัวอักษรเป็นสีขาว */
        text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5); /* เพิ่มเงาให้ตัวอักษรเพื่อให้มีมิติมากขึ้น */
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
                <a class="navbar-brand d-flex align-items-center" href="{{ route('admin.home') }}">
                    <img src="https://www.svgrepo.com/show/163314/football.svg" alt="Football Icon">
                    <span>ฟุตบอลคลับ</span>
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

        <!-- ส่วนล่างของ Navbar -->
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
</body>

</html>
