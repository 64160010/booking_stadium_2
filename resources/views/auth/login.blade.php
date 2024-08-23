@extends('layouts.app')

@section('styles')
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
    
    <!-- Animate.css -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="{{ asset('css/login.css') }}">
@endsection

@section('content')
<div class="row mx-0 auth-wrapper">
    <!-- Background Circles -->
    <ul class="circles">
        <li></li>
        <li></li>
        <li></li>
        <li></li>
        <li></li>
        <li></li>
        <li></li>
        <li></li>
        <li></li>
        <li></li>
    </ul>
    <!-- Custom Design Side -->
    <div class="d-none d-sm-flex col-sm-6 col-lg-8 align-items-center p-5">
        <div class="align-items-start d-lg-flex flex-column offset-lg-2 text-white">
            
            <h1 class="d-flex">ยินดีต้อนรับ </h1>
            <h1 class="d-flex">เว็บจองสนามฟุตบอลและยืมคืนอุปกรณ์</h1>
           
                 
        </div>
    </div>
    <!-- Login Form Side -->
    <div class="d-flex justify-content-center col-sm-6 col-lg-4 align-items-center px-5 bg-white mx-auto">
        <div class="form-wrapper">
            <div class="d-flex flex-column">
                <div class="mb-4">
                    <h3 class="font-medium mb-1">เข้าสู่ระบบ</h3>
                    <p class="mb-2">โปรดเข้าสู่ระบบของคุณเพื่อดำเนินการต่อไป</p>
                </div>
                <div class="mb-10">
                    <form method="POST" action="{{ route('login') }}">
                        @csrf
                        <div class="form-group">
                            <label for="email">อีเมลล์</label>
                            <input id="email" name="email" type="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" required autocomplete="email" autofocus>
                            @error('email')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label for="password">รหัสผ่าน</label>
                            <input id="password" name="password" type="password" class="form-control @error('password') is-invalid @enderror" required autocomplete="current-password">
                            @error('password')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                        <div class="text-right mb-3">
                            <a class="btn btn-link" href="{{ route('password.request') }}">
                                ลืมรหัสผ่าน?
                            </a>
                        </div>
                        <button type="submit" class="btn btn-primary btn-block mt-3 border-0">
                           เข้าสู่ระบบ
                        </button>
                    </form>
                </div>
                <div class="p-5 text-center text-xs">
                    
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
    <!-- ICON -->
    <script src="https://unpkg.com/lucide@latest"></script>
    <script>
        lucide.createIcons();
    </script>
@endsection
