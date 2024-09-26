@extends('layouts.app')

@section('content')
<div class="container-fluid mt-4">
    <div class="row justify-content-center">
        <div class="col-12">
            <div class="card shadow-lg border-0">
                <div class="card-header bg-primary text-white text-center">
                    <h4>{{ __('เพิ่มสมาชิกใหม่') }}</h4>
                </div>
                <div class="card-body p-4">
                    <!-- Back Button -->
                    <a href="{{ route('users.index') }}" class="btn btn-secondary mb-3">ย้อนกลับ</a>

                    <!-- Form Start -->
                    <form action="{{ route('users.store') }}" method="POST">
                        @csrf

                        <!-- First Name -->
                        <div class="form-group mb-3">
                            <label for="fname">{{ __('ชื่อ') }}</label>
                            <input type="text" name="fname" id="fname" class="form-control" placeholder="กรอกชื่อ" value="{{ old('fname') }}" required>
                            @error('fname')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <!-- Last Name -->
                        <div class="form-group mb-3">
                            <label for="lname">{{ __('นามสกุล') }}</label>
                            <input type="text" name="lname" id="lname" class="form-control" placeholder="กรอกนามสกุล" value="{{ old('lname') }}" required>
                            @error('lname')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <!-- Duplicate Name & Last Name -->
                        @if($errors->has('duplicate'))
                            <div class="alert alert-danger">{{ $errors->first('duplicate') }}</div>
                        @endif

                        <!-- Email -->
                        <div class="form-group mb-3">
                            <label for="email">{{ __('อีเมล') }}</label>
                            <input type="email" name="email" id="email" class="form-control" placeholder="กรอกอีเมล" value="{{ old('email') }}" required>
                            @error('email')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <!-- Phone -->
                        <div class="form-group mb-3">
                            <label for="phone">{{ __('เบอร์โทร') }}</label>
                            <input type="text" name="phone" id="phone" class="form-control" placeholder="กรอกเบอร์โทร" value="{{ old('phone') }}" required>
                            @error('phone')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <!-- Password -->
                        <div class="form-group mb-3">
                            <label for="password">{{ __('รหัสผ่าน') }}</label>
                            <input type="password" name="password" id="password" class="form-control" placeholder="กรอกรหัสผ่าน" required>
                            @error('password')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <!-- Confirm Password -->
                        <div class="form-group mb-3">
                            <label for="password_confirmation">{{ __('ยืนยันรหัสผ่าน') }}</label>
                            <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" placeholder="ยืนยันรหัสผ่าน" required>
                        </div>

                        <!-- Is Admin -->
                        <div class="form-group mb-3">
                            <label for="is_admin">{{ __('สถานะ') }}</label>
                            <select name="is_admin" id="is_admin" class="form-control">
                                <option value="0">ผู้ใช้</option>
                                <option value="1">แอดมิน</option>
                            </select>
                        </div>

                        <!-- Submit Button -->
                        <div class="form-group text-center">
                            <button type="submit" class="btn btn-primary">{{ __('บันทึกข้อมูล') }}</button>
                        </div>
                    </form>
                    <!-- Form End -->

                </div>
            </div>
        </div>
    </div>
</div>
@endsection
