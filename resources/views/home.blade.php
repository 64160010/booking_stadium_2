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

    <!-- Rules Section -->
    <div class="container mt-5">
        <h2 class="text-center mb-4">ระเบียบและข้อปฏิบัติการใช้สนามฟุตบอล</h2>

        <div class="rules-section bg-light p-4 rounded shadow">
            <h4>การจองสนาม</h4>
            <ul class="pl-3">
                <li>ลูกค้าแจ้งเจ้าหน้าที่ทุกครั้งก่อนเข้าใช้สนาม</li>
                <li>ลูกค้าที่จองสนาม ต้องมาถึงสนามก่อน 10 นาที</li>
                <li>หากมาช้ากว่าเวลากำหนด ถือว่าสละสิทธิการจองครั้งนั้น</li>
                <li>ในกรณีต้องการยกเลิกการจอง กรุณาโทรแจ้งก่อนล่วงหน้าอย่างน้อย 1 วัน</li>
            </ul>

            <h4>การใช้สนาม</h4>
            <ul class="pl-3">
                <li>กรุณารักษาเวลาการจองใช้สนาม</li>
                <li>กรุณาสวมเครื่องแต่งกายชุดกีฬา และสวมรองเท้ากีฬาเท่านั้น</li>
                <li>ไม่อนุญาตให้นำสัตว์เลี้ยงเข้าภายในสนามและบริเวณคลับเฮาส์</li>
                <li>ห้ามสูบบุหรี่ภายในสนามโดยเด็ดขาด สูบบุหรี่ในสนามปรับ 2,000 บาท สามารถสูบบุหรี่ในบริเวณที่จัดไว้ให้เท่านั้น</li>
                <li>ห้ามเล่นการพนัน</li>
                <li>ห้ามพกพาอาวุธเข้ามาในบริเวณสนามโดยเด็ดขาด</li>
                <li>กรณีที่มีการทะเลาะวิวาทในระหว่างการเล่น ทางสนามขอสงวนสิทธิการพิจารณายกเลิกการเช่าสนามนั้นในทันที โดยผู้เช่าจะต้องชำระเงินเต็มจำนวนการเช่า</li>
                <li>ห้ามนำอาหาร และขนมขบเคี้ยว เข้าภายในสนาม</li>
                <li>ห้ามนำเครื่องดื่มแอลกอฮอล์และสารเสพติดทุกชนิดเข้าภายในสนาม</li>
                <li>กรุณางดใช้กลอง แตรทุกชนิด และงดส่งเสียงดังหลังเวลา 21.00 น.</li>
                <li>กรุณาดูแลทรัพย์สินส่วนตัว ทางสนามไม่รับผิดชอบในกรณีสูญหาย</li>
                <li>กรุณารักษาความสะอาดภายในสนาม</li>
                <li>หากพื้นสนาม, สถานที่ และอุปกรณ์ เกิดความเสียหาย ผู้ใช้บริการจะต้องชำระค่าเสียหายตามมูลค่าจริงที่เกิดขึ้น</li>
            </ul>
        </div>
    </div>
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