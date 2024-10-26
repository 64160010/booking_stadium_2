@extends('layouts.app')
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

@section('content')
<div class="container">
    <h2 class="text-center mb-4">ประวัติการจองและการยืม</h2>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <table class="table table-striped">
        <thead>
            <tr>
                <th>รหัสการจอง</th>
                <th>ชื่อผู้จอง</th>
                <th>จำนวนเงิน</th>
                <th>วันที่และเวลาโอน</th>
                <th>สถานะการจอง</th>
                <th>สถานะการยืม</th>
                <th>รายละเอียด</th>
                <th>รูปหลักฐานการโอนเงิน</th>
                <th>ตรวจสอบ</th>  <!-- เพิ่มหัวข้อ ตรวจสอบ -->
            </tr>
        </thead>
        <tbody>
            @foreach($bookings as $booking)
                <tr>
                    <td>{{ $booking->id }}</td>
                    <td>
                        @if($booking->payment)
                            {{ $booking->payment->payer_name }}
                        @else
                            N/A
                        @endif
                    </td>
                    
                    <td>
                        @if($booking->payment)
                            {{ number_format($booking->payment->amount, 2) }}
                        @else
                            N/A
                        @endif
                    </td>
                    
                    <td>{{ $booking->payment->transfer_datetime ?? 'N/A' }}</td>
                    <td>{{ $booking->booking_status }}</td>
                    <td>
                        @if($booking->borrow->isNotEmpty())
                            @foreach($booking->borrow as $borrow)
                                {{ $borrow->borrow_status }}<br>
                            @endforeach
                        @else
                            ไม่มีการยืม
                        @endif
                    </td>
                    <td>
                        <a href="{{ route('historyDetail', $booking->id) }}" class="btn btn-primary">รายการ</a>
                    </td>
                    
                    <td>
                        @if($booking->payment && $booking->payment->confirmation_pic)
                            <!-- ปุ่มสำหรับเปิด Modal -->
                            <button type="button" class="btn btn-info" data-bs-toggle="modal" data-bs-target="#paymentSlipModal{{ $booking->id }}">
                                ดูหลักฐาน
                            </button>
                    
                            <!-- Modal แสดงรูปหลักฐานการโอน -->
                            <div class="modal fade" id="paymentSlipModal{{ $booking->id }}" tabindex="-1" aria-labelledby="paymentSlipModalLabel{{ $booking->id }}" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="paymentSlipModalLabel{{ $booking->id }}">หลักฐานการโอนเงิน</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body text-center">
                                            <img src="{{ asset('storage/slips/' . $booking->payment->confirmation_pic) }}" alt="Confirmation Image" style="width: 100%; height: auto;">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @else
                            N/A
                        @endif
                    </td>
                    
                    <td>
                        <!-- ปุ่มยืนยันและปฏิเสธสำหรับแอดมิน -->
                        <form action="{{ route('booking', $booking->id) }}" method="POST" style="display: inline;">
                            @csrf
                            <button type="submit" class="btn btn-success">ยืนยัน</button>
                        </form>
                        
                        <form action="{{ route('booking', $booking->id) }}" method="POST" style="display: inline;">
                            @csrf
                            <button type="submit" class="btn btn-danger">ปฏิเสธ</button>
                        </form>
                    </td>
                    
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
@endsection
