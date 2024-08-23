@extends('layouts.app')

@section('content')
<div class="container-fluid px-0">
    <!-- Hero Section -->
    <header class="hero" style="background: url('https://png.pngtree.com/thumb_back/fw800/background/20230906/pngtree-a-soccer-ball-next-to-a-soccer-field-image_13313617.jpg') no-repeat center center; background-size: cover; color: white; height: 400px; width: 100%; position: relative; overflow: hidden;">
        <div class="hero-content" style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); text-align: center; width: 100%; padding: 0 15px; background: rgba(0, 0, 0, 0.5); border-radius: 10px;">
            <h1 class="display-4">ยินดีต้อนรับเข้าสู่เว็บจองสนามฟุตบอล</h1>
            <p class="lead">สามารถจองได้เลยเวลานี้!</p>
        </div>
    </header>

    <!-- Success Message -->
    @if(session('success'))
        <div id="success-message" class="alert alert-success mt-3">
            {{ session('success') }}
        </div>
    @endif
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        var successMessage = document.getElementById('success-message');
        if (successMessage) {
            setTimeout(function() {
                successMessage.style.display = 'none';
            }, 5000);
        }
    });
</script>
@endsection