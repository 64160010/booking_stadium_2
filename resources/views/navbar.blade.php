<div class="container-fluid">
  <a class="navbar-brand" href="#">Booking</a>
  <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
    <span class="navbar-toggler-icon"></span>
  </button>
  <div class="collapse navbar-collapse" id="navbarSupportedContent">
    <ul class="navbar-nav me-auto mb-2 mb-lg-0">
      <li class="nav-item">
        <a class="nav-link active" aria-current="page" href="/">หน้าแรก</a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="{{route('booking')}}">จองสนาม</a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="{{route('lending')}}">ยืมอุปกรณ์</a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="booking_status">สถานะการจอง</a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="lending_status">สถานะการยืม</a>
      </li>
     
    </ul>
    <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
      <li class="nav-item dropdown">
        <a class="nav-link dropdown-toggle" href="/" role="button" data-bs-toggle="dropdown">ผู้ใช้งาน</a>
        <ul class="dropdown-menu dropdown-menu-end">
          <li><a class="dropdown-item" href="register">สมัครสมาชิก</a></li>
          <li><a class="dropdown-item" href="login">เข้าสู่ระบบ</a></li>
          
        </ul>
      </li>
    </ul>
  </div>
</div>
