@extends('layouts.app')
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

@section('content')
<div class="container">
    @auth
        @if (Auth::user()->is_admin)
            <button class="btn btn-warning" onclick="filterBookings('รอการตรวจสอบ')">รอการตรวจสอบ</button>
            <button class="btn btn-success" onclick="filterBookings('ชำระเงินแล้ว')">ชำระเงินแล้ว</button>
            <button class="btn btn-danger" onclick="filterBookings('การชำระเงินถูกปฏิเสธ')">การชำระเงินถูกปฏิเสธ</button>
            <button class="btn btn-secondary" onclick="resetFilters()">แสดงทั้งหมด</button>
        @endif
    @endauth

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
                <th>สถานะการชำระเงิน</th>
                <th>รายละเอียด</th>
                <th>รูปหลักฐานการโอนเงิน</th>
                
                @auth
                    @if (!Auth::user()->is_admin)
                        
                            <th>หมายเหตุ</th> <!-- เปลี่ยนชื่อคอลัมน์เป็น "หมายเหตุ" สำหรับสถานะถูกปฏิเสธ -->
                    @endif
                @endauth
                @auth
                    @if (Auth::user()->is_admin)
                        @if (request('status') == 'การชำระเงินถูกปฏิเสธ')
                            <th>หมายเหตุ</th> <!-- เปลี่ยนชื่อคอลัมน์เป็น "หมายเหตุ" สำหรับสถานะถูกปฏิเสธ -->
                        @else
                            <th>ตรวจสอบ</th>
                        @endif
                    @endif
                @endauth
            </tr>
        </thead>
        <tbody>
            @foreach($bookings as $booking)
            <tr class="{{ $booking->booking_status }}">
                <td>{{ $booking->id }}</td>
                <td>{{ $booking->payment ? $booking->payment->payer_name : 'N/A' }}</td>
                <td>{{ $booking->payment ? number_format($booking->payment->amount, 2) : 'N/A' }}</td>
                <td>{{ $booking->payment->transfer_datetime ?? 'N/A' }}</td>
                <td>{{ $booking->booking_status }}
                <td>
                    <a href="{{ route('historyDetail', $booking->id) }}" class="btn btn-primary">รายการ</a>
                </td>
                <td>
                    @if($booking->payment && $booking->payment->confirmation_pic)
                        <button type="button" class="btn btn-info" data-bs-toggle="modal" data-bs-target="#paymentSlipModal{{ $booking->id }}">
                            ดูหลักฐาน
                        </button>
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
                @auth
                    @if (Auth::user()->is_admin && $booking->booking_status != 'การชำระเงินถูกปฏิเสธ')
                        <form action="{{ route('booking.confirm', $booking->id) }}" method="POST" style="display: inline;">
                            @csrf
                            <button type="submit" class="btn btn-success">ยืนยัน</button>
                        </form>
                        <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#rejectModal{{ $booking->id }}">ปฏิเสธ</button>
                        <div class="modal fade" id="rejectModal{{ $booking->id }}" tabindex="-1" aria-labelledby="rejectModalLabel{{ $booking->id }}" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="rejectModalLabel{{ $booking->id }}">เหตุผลที่ปฏิเสธการชำระเงิน</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    
                                    </div>
                                    <div class="modal-body">
                                        <form action="{{ route('booking.reject', $booking->id) }}" method="POST">
                                            @csrf
                                            <div class="form-group">
                                                <label for="reject_reason">หมายเหตุ (ไม่เกิน 20 ตัวอักษร):</label>
                                                <input type="text" name="reject_reason" class="form-control" maxlength="20" required>
                                            </div>
                                            <button type="submit" class="btn btn-danger mt-3">ปฏิเสธ</button>
                                        </form>
                                    </td>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @elseif ($booking->booking_status == 'การชำระเงินถูกปฏิเสธ')
                        <small> {{ $booking->reject_reason ?? 'ไม่มีเหตุผล' }}</small>
                    @endif
                @endauth
            </tr>
        @endforeach
    </tbody>
</table>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    function filterBookings(status) {
        window.location.href = `?status=${status}`; // ส่งคำขอไปยังเซิร์ฟเวอร์
    }

    function resetFilters() {
        window.location.href = '?'; // รีเซ็ตฟิลเตอร์โดยกลับไปที่หน้าเริ่มต้น
    }
</script>
@endsection
