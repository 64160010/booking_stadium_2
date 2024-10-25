@extends('layouts.app')

@section('content')
<div class="container-fluid px-0">
    <!-- Hero Section -->
    <header class="hero" style="background: url('https://th.bing.com/th/id/R.88b715f42f65ec255eeeb6a890286246?rik=3MK8Zc81oXE6Dw&riu=http%3a%2f%2fwallpapercave.com%2fwp%2fV0VMLUp.jpg&ehk=cSbpTcNpQCURyiEdPRYANWvn%2f7ke%2fiQ0Ji44QnI%2bY7Q%3d&risl=&pid=ImgRaw&r=0') no-repeat center center; background-size: cover; color: white; height: 400px; width: 100%; position: relative; overflow: hidden;">
        <div class="hero-content" style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); text-align: center; width: 100%; padding: 0 15px; background: rgba(0, 0, 0, 0.5); border-radius: 10px;">
            <h1 class="display-4">ยินดีต้อนรับเข้าสู่เว็บจองสนามฟุตบอล</h1>
            <p class="lead">สามารถจองได้เลยเวลานี้!</p>
            <button class="custom-btn">
                <a href="{{ route('booking') }}" style="color: inherit; text-decoration: none;">
                    {{ __('จองสนาม') }}
                 </a>
 
            </button>
            </div>
    </header>

    <!-- Success Message -->
    @if(session('success'))
        <div id="success-message" class="alert alert-success mt-3">
            {{ session('success') }}
        </div>
    @endif

   <!-- Image Grid Section -->
<!-- Image Grid Section -->
<div class="container mt-5">
    <h2 class="text-center mb-4">ภาพเกี่ยวกับสนามฟุตบอลและอุปกรณ์</h2>
    <div class="row text-center">
        <div class="col-md-3 mb-4">
            <div class="image-wrapper">
                <img src="https://wallpapercave.com/wp/wp8255803.jpg" class="img-fluid rounded" alt="Football Field 1">
                <div class="caption">การจองสนาม</div> <!-- Caption for first image -->
            </div>
        </div>
        <div class="col-md-3 mb-4">
            <div class="image-wrapper">
                <img src="https://www.soccerbible.com/media/106268/8-adidas-nemeziz-mutator-boots-min.jpg" class="img-fluid rounded" alt="Football Equipment 4">
                <div class="caption">การใช้บริการจองสนาม</div> <!-- Caption for third image -->
            </div>
        </div>
        <div class="col-md-3 mb-4">
            <div class="image-wrapper">
                <img src="https://th.bing.com/th/id/OIP.T3qZJGezbdntqNn0BNpqmwHaEK?w=768&h=432&rs=1&pid=ImgDetMain" class="img-fluid rounded" alt="Football Equipment 1">
                <div class="caption">การยืมอุปกรณ์</div> <!-- Caption for third image -->
            </div>
        </div>
        <div class="col-md-3 mb-4">
            <div class="image-wrapper">
                <img src="https://th.bing.com/th/id/R.e3972e544a7621cb56371d83d56f3bfc?rik=IRo6uKwulLKpPw&pid=ImgRaw&r=0" class="img-fluid rounded" alt="Football Equipment 2">
                <div class="caption">การใช้บริการอุปกรณ์</div> <!-- Caption for fourth image -->
            </div>
        </div>
    </div>
</div>

   <!-- Rules Section -->
    <div class="container mt-5">
        <h2 class="text-center mb-4">ระเบียบและข้อปฏิบัติการใช้สนามฟุตบอล</h2>

        <div class="rules-section bg-light p-4 rounded shadow">
            <h4 class="text-primary">การจองสนาม</h4>
            <ul class="pl-3" style="list-style-type: disc;">
                <li>ลูกค้าแจ้งเจ้าหน้าที่ทุกครั้งก่อนเข้าใช้สนาม</li>
                <li>ลูกค้าที่จองสนาม ต้องมาถึงสนามก่อน 10 นาที</li>
                <li>หากมาช้ากว่าเวลากำหนด ถือว่าสละสิทธิการจองครั้งนั้น</li>
                <li>ในกรณีต้องการยกเลิกการจอง กรุณาโทรแจ้งก่อนล่วงหน้าอย่างน้อย 1 วัน</li>
            </ul>
        </div>

        <div class="rules-section bg-light p-4 rounded shadow mt-4">
            <h4 class="text-primary">การใช้สนาม</h4>
            <ul class="pl-3" style="list-style-type: disc;">
                <li>กรุณารักษาเวลาการจองสนามและใช้สนาม</li>
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

        <div class="rules-section bg-light p-4 rounded shadow mt-4">
            <h4 class="text-primary">การยืมอุปกรณ์</h4>
            <ul class="pl-3" style="list-style-type: disc;">
                <li>ผู้ใช้สามารถยืมอุปกรณ์ได้ก็ต่อเมื่อได้ทำการจองสนามแล้วเท่านั้น</li>
                <li>สามารถยืมอุปกรณ์ได้ตามจำนวนที่กำหนด</li>
                <li>เมื่อยืมอุปกรณ์แล้ว ขอให้ดูแลรักษาอุปกรณ์ให้ดี</li>
                <li>ต้องนำอุปกรณ์ที่ยืมกลับมาคืนตามเวลาที่กำหนด</li>
            </ul>
        </div>
    </div>
</div>

<style>
    .custom-btn {
        background-color: #3f8cdf; /* สีเขียว */
        color: white;
        padding: 7px 24px;
        margin-bottom:10px;
        border-radius: 8px;
        border: none;
        font-size: 16px;
        cursor: pointer;
        text-decoration: none;
        display: inline-block;
        transition: background-color 0.3s ease;
    }

    .custom-btn:hover {
        background-color: #3375bb; /* เปลี่ยนสีเมื่อ hover */
    }

    .custom-btn i {
        margin-right: 8px; /* ระยะห่างระหว่างไอคอนกับข้อความ */
    }

    .image-wrapper {
    width: 100%; /* ทำให้กรอบมีความกว้าง 100% */
    height: 250px; /* ตั้งความสูงให้กรอบ */
    overflow: hidden; /* ซ่อนส่วนที่เกิน */
    display: flex;
    flex-direction: column; /* จัดเรียงในแนวตั้ง */
    justify-content: flex-start; /* จัดเรียงให้อยู่ด้านบน */
    background-color: #f8f9fa; /* เพิ่มสีพื้นหลังเพื่อมองเห็นกรอบ */
    border-radius: 8px; /* เพิ่มมุมโค้งให้กรอบ */
    transition: transform 0.3s ease; /* เพิ่ม transition สำหรับการขยาย */
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2); /* เพิ่มเงาให้กับกรอบ */
}

.image-wrapper img {
    width: 100%; /* ทำให้ภาพมีความกว้าง 100% */
    height: auto; /* ให้ความสูงปรับอัตโนมัติตามสัดส่วน */
    border-radius: 8px 8px 0 0; /* เพิ่มมุมโค้งให้กับภาพเฉพาะด้านบน */
    transition: transform 0.3s ease; /* เพิ่ม transition สำหรับการขยาย */
}

.image-wrapper:hover {
    transform: scale(1.05); /* ขยายกรอบเมื่อ hover */
}

.image-wrapper:hover img {
    transform: scale(1.1); /* ขยายภาพเมื่อ hover */
}

.caption {
    padding: 10px; /* เพิ่ม padding ให้ข้อความ */
    font-size: 1.1em; /* ขนาดข้อความ */
    font-weight: bold; /* ทำให้ข้อความหนา */
    text-align: center; /* จัดข้อความให้อยู่กลาง */
    color: #333; /* สีข้อความ */
    background-color: #f8f9fa; /* สีพื้นหลังของข้อความ */
}

    #success-message {
        margin-top: 20px;
        padding: 10px;
        border-radius: 5px;
        transition: opacity 0.5s ease; /* เพิ่ม transition สำหรับการแสดงข้อความ */
    }

    .rules-section {
        background-color: #f8f9fa; /* เพิ่มสีพื้นหลังให้ส่วนระเบียบ */
        padding: 20px; /* เพิ่ม padding */
        border-radius: 8px; /* เพิ่มมุมโค้งให้กับส่วนระเบียบ */
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); /* เพิ่มเงาให้กับส่วนระเบียบ */

        
    }
   
   

</style>
@endsection