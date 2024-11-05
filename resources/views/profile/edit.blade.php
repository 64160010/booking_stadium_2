@extends('layouts.app')

@section('content')
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="text-center mb-4">
                    <h1 class="display-5" style="padding-bottom: 10px; border-bottom: 4px solid #fd0d71; color: #fd0d71;">
                        แก้ไขข้อมูลส่วนตัว
                    </h1>
                </div>

                @if (Auth::check())
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="card shadow-sm border-0">
                        <div class="card-body p-4">
                            <form action="{{ route('profile.update') }}" method="POST">
                                @csrf
                                @method('PUT')

                                <div class="row g-4">
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">ชื่อจริง/ชื่อผู้ใช้</label>
                                        <input type="text" class="form-control shadow-sm" id="fname" name="fname"
                                            value="{{ old('fname', Auth::user()->fname) }}" placeholder="ชื่อจริงของคุณ">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">นามสกุล</label>
                                        <input type="text" class="form-control shadow-sm" id="lname" name="lname"
                                            value="{{ old('lname', Auth::user()->lname) }}" placeholder="นามสกุลของคุณ">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">อีเมลล์</label>
                                        <input type="email" class="form-control shadow-sm" id="email" name="email"
                                            value="{{ old('email', Auth::user()->email) }}" readonly>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">เบอร์โทร</label>
                                        <input type="tel" class="form-control shadow-sm" id="phone" name="phone"
                                            value="{{ old('phone', Auth::user()->phone) }}" placeholder="เบอร์โทรศัพท์">
                                    </div> 
                                </div>

                                <div class="text-end mt-4">
                                    <a href="{{ route('home') }}" class="btn btn-outline-secondary btn-lg me-2">ยกเลิก</a>
                                    <button type="submit" class="btn btn btn-lg" style="background-color:#fd0d71;">บันทึก</button>
                                </div>
                            </form>
                        </div>
                    </div>
                @else
                    <div class="alert alert-warning text-center mt-4">
                        <p>กรุณาเข้าสู่ระบบเพื่อแก้ไขข้อมูลส่วนตัวของคุณ</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
