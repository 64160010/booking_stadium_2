@extends('layouts.app')
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

@section('content')
<div class="container">
    <h2 class="text-center mb-4">ประวัติการจองและการยืม</h2>
    <div class="mb-4">
        <form action="{{ url()->current() }}" method="GET" class="form-inline">
            <div class="form-group mr-2">
                <input style="padding-right:120px;" type="text" name="booking_stadium_id" class="form-control" placeholder="รหัสการจอง" value="{{ request('booking_stadium_id') }}">
            </div>
            <div class="form-group mr-2">
                <input style="padding-right:120px;" type="text" name="fname" class="form-control" placeholder="ชื่อผู้ใช้" value="{{ request('fname') }}">
            </div>
            <div class="form-group mr-2">
                <input style="padding-right:120px;" type="date" name="borrow_date" class="form-control" placeholder="วันที่" value="{{ request('borrow_date') }}">
            </div>
            <input type="hidden" name="status" value="{{ request('status') }}">
            <button type="submit" class="btn btn-primary">ค้นหา</button>
            <a href="{{ url()->current() }}" class="btn btn-secondary ml-2">รีเซ็ต</a>
        </form>
    </div>
    @auth
        @if (Auth::user()->is_admin)
        <div style="text-align: center;">
            <button class="btn btn-warning" onclick="filterBookings('รอการตรวจสอบ')">รอการตรวจสอบ</button>
            <button class="btn btn-success" onclick="filterBookings('ชำระเงินแล้ว')">ชำระเงินแล้ว</button>
            <button class="btn btn-danger" onclick="filterBookings('การชำระเงินถูกปฏิเสธ')">การชำระเงินถูกปฏิเสธ</button>
            <button class="btn btn-primary" onclick="filterBookings('หมดอายุการชำระเงิน')">หมดอายุการชำระเงิน</button>
            <button class="btn btn-secondary" onclick="resetFilters()">แสดงทั้งหมด</button>
        </div>
        @endif
    @endauth
    

    

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    

    <table class="table table-striped" style="margin-top:30px;">
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
                <td>{{ $booking->payment ? $booking->payment->payer_name : ($booking->user ? $booking->user->fname : 'N/A') }}</td>
                <td>{{ $booking->payment ? number_format($booking->payment->amount, 2) : '-' }}</td>
                <td>{{ $booking->payment->transfer_datetime ?? '-' }}</td>
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
                        -
                    @endif
                </td>
                <td>
                @auth
                @if (Auth::user()->is_admin && !in_array($booking->booking_status, ['การชำระเงินถูกปฏิเสธ', 'หมดอายุการชำระเงิน']))

                        <form action="{{ route('booking.confirm', $booking->id) }}" method="POST" style="display: inline;">
                            @csrf
                            <button type="submit" class="btn btn-success" >ยืนยัน</button>
                        </form>
                        <button type="button" class="btn btn-danger"" data-bs-toggle="modal" data-bs-target="#rejectModal{{ $booking->id }}">ปฏิเสธ</button>
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
