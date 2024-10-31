@extends('layouts.app')

@section('content')
<div class="container">
    <div class="card-body">
        <div class="row">
            <!-- Left Column: Bank Details -->
            <div class="col-md-6">
                <h2 class="text-center mb-4">รายละเอียดบัญชีธนาคารสำหรับโอนเงิน</h2>
                <div class="card shadow-sm"> <!-- เพิ่มกรอบเงาให้กับรายละเอียดบัญชีธนาคาร -->
                    <div class="card-body">
                        <ul class="list-group">
                            <li class="list-group-item">
                                <img src="{{ asset('https://th.bing.com/th/id/OIP.oDz143tLHQs3atBKazngswHaHW?rs=1&pid=ImgDetMain') }}" alt="ธนาคารกสิกรไทย" class="rounded-circle" style="width: 50px; height: 50px; margin-right: 10px;">
                                <strong>ธนาคารกสิกรไทย</strong><br>
                                หมายเลขบัญชี: 061-3358676<br>
                                ชื่อบัญชี: นางสาว ณัฎฐณิชา มีมาก
                            </li>
                            <li class="list-group-item">
                                <img src="{{ asset('https://www.thaitoptour.com/wp-content/uploads/2019/03/afscb-300x298.png') }}" alt="ธนาคารไทยพาณิชย์" class="rounded-circle" style="width: 50px; height: 50px; margin-right: 10px;">
                                <strong>ธนาคารไทยพาณิชย์</strong><br>
                                หมายเลขบัญชี: 044-4315064<br>
                                ชื่อบัญชี: นางสาว ณัฎฐณิชา มีมาก
                            </li>
                            <li class="list-group-item">
                                <img src="{{ asset('https://th.bing.com/th/id/OIP.m9nWyPNKXWgJAT_104PGHgHaHX?rs=1&pid=ImgDetMain') }}" alt="ธนาคารกรุงไทย" class="rounded-circle" style="width: 50px; height: 50px; margin-right: 10px;">
                                <strong>ธนาคารกรุงไทย</strong><br>
                                หมายเลขบัญชี: 033-5432065<br>
                                ชื่อบัญชี: นางสาว ณัฎฐณิชา มีมาก
                            </li>
                        </ul>
                    </div>
                </div> <!-- ปิดกรอบเงา -->
            </div>

            <!-- Right Column: Payment Form -->
            <div class="col-md-6">
                <h2 class="text-center mb-4">แจ้งการชำระเงิน</h2>
                
                <form action="{{ route('processPayment') }}" method="POST" enctype="multipart/form-data" class="shadow-lg p-4 rounded">
                    @csrf

                    <div class="form-group mb-3">
                        <label for="booking_code">รหัสการจอง*</label>
                        <select id="booking_code" name="booking_code" class="form-control" required>
                            <option value="" disabled selected>กรุณาเลือกรหัสการจอง</option>
                            @foreach($bookings as $booking)
                                <option value="{{ $booking->id }}">{{ $booking->id }} ({{ $booking->booking_status }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group mb-3">
                        <label for="payer_name">ชื่อผู้โอน*</label>
                        <input type="text" id="payer_name" name="payer_name" class="form-control" required>
                    </div>

                    <div class="form-group mb-3">
                        <label for="phone_number">เบอร์โทรศัพท์*</label>
                        <input type="tel" id="phone_number" name="phone_number" class="form-control" required>
                    </div>

                    <div class="form-group mb-3">
                        <label for="select_bank">ธนาคารที่โอน*</label>
                        <select id="select_bank" name="select_bank" class="form-control" required>
                            <option value="" disabled selected>กรุณาเลือกธนาคาร</option>
                            <option value="กสิกรไทย">ธนาคารกสิกรไทย</option>
                            <option value="ไทยพาณิชย์">ธนาคารไทยพาณิชย์</option>
                            <option value="กรุงไทย">ธนาคารกรุงไทย</option>
                        </select>
                    </div>
                    

                    <div class="form-group mb-3">
                        <label for="transfer_datetime">วันที่และเวลาโอน*</label>
                        <input type="datetime-local" id="transfer_datetime" name="transfer_datetime" class="form-control" required>
                    </div>

                    <div class="form-group mb-3">
                        <label for="transfer_amount">จำนวนเงินที่โอน*</label>
                        <input type="number" id="transfer_amount" name="transfer_amount" class="form-control" required>
                    </div>

                    <div class="form-group mb-3">
                        <label for="transfer_slip">อัปโหลดสลิปการโอนเงิน*</label>
                        <input type="file" id="transfer_slip" name="transfer_slip" class="form-control" accept="image/*" required>
                    </div>

                    <div class="text-center">
                        <button type="submit" class="btn btn-primary">ยืนยันการชำระเงิน</button>
                        <a href="{{ route('home') }}" class="btn btn-secondary">ยกเลิก</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
