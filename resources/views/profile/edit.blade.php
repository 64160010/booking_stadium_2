@extends('layouts.app')

@section('content')
    <div class="container mt-4">
        <h1>แก้ไขข้อมูลส่วนตัว</h1>

        @if (Auth::check())
            <!-- ตรวจสอบว่าผู้ใช้ล็อกอินอยู่หรือไม่ -->

            @if ($errors->any())
    <div class="alert alert-danger">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

            <form action="{{ route('profile.update') }}" method="POST">
                @csrf
                @method('PUT')

                <div class="card shadow-sm">
                    <div class="card-body">

                        <div class="row g-3">
                            <div class="col-md-6">
                                <div>
                                    <label class="form-label">ชื่อจริง/ชื่อผู้ใช้</label>
                                    <input type="text" class="form-control shadow-sm" id="fname" name="fname"
                                        value="{{ old('fname', Auth::user()->fname) }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div>
                                    <label class="form-label">นามสกุล</label>
                                    <input type="text" class="form-control shadow-sm" id="lname" name="lname"
                                        value="{{ old('lname', Auth::user()->lname) }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div>
                                    <label class="form-label">อีเมลล์</label>
                                    <input type="email" class="form-control shadow-sm" id="email" name="email"
                                        value="{{ old('email', Auth::user()->email) }}" readonly>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div>
                                    <label class="form-label">เบอร์โทร</label>
                                    <input type="tel" class="form-control shadow-sm" id="phone" name="phone"
                                        value="{{ old('phone', Auth::user()->phone) }}">
                                </div>
                            </div> 
                            <div class="col-12 text-end">
                                <a href="{{ route('home') }}" class="btn btn-sm btn-neutral me-2">ยกเลิก</a>
                                <button type="submit" class="btn btn-sm btn-primary">บันทึก</button>
                            </div>
                        </div>
            </form>
    </div>
    </div>
@else
    <p>กรุณาเข้าสู่ระบบเพื่อแก้ไขข้อมูลส่วนตัวของคุณ</p>
    @endif
    </div>
@endsection