@extends('layouts.app')

@section('content')
<main class="py-4">
    <div class="container">
        <h3 class="mb-4">{{ __('สถานะการจอง') }}</h3>
        <div class="card shadow-sm p-4">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>รหัสการจอง</th>
                        <th>ชื่อผู้ใช้</th>
                        <th>เบอร์โทรศัพท์</th>
                        <th>ชื่อสนาม</th>
                        <th>วันที่</th>
                        <th>เวลา</th>
                        <th>ราคา</th>
                        <th>ชั่วโมง</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($stadiums as $stadium)
                        @foreach ($stadiumsData[$stadium->id] as $timeSlot)
                            <tr>
                                <td>12345</td> <!-- สมมุติรหัสการจอง -->
                                <td>{{ Auth::user()->fname }}</td>
                                <td>{{ Auth::user()->phone }}</td>
                                <td>{{ $stadium->stadium_name }}</td>
                                <td>{{ \Carbon\Carbon::parse($date)->format('d/m/Y') }}</td>
                                <td>{{ $timeSlot }}</td>
                                <td>{{ number_format($stadium->stadium_price) }} บาท</td>
                                <td>1 ชั่วโมง</td>
                            </tr>
                        @endforeach
                    @endforeach
                </tbody>
            </table>

            <div class="d-flex justify-content-between mt-4">
                <button class="btn btn-secondary" onclick="goBack()">{{ __('ย้อนกลับ') }}</button>
                <div>
                    <span>รวมยอด {{ number_format($totalPrice) }} บาท</span>
                    <button class="btn btn-success ms-3" onclick="confirmBooking()">{{ __('ยืนยันการจอง') }}</button>
                </div>
            </div>
        </div>
    </div>
</main>

<script>
    function goBack() {
        window.history.back();
    }

    function confirmBooking() {
        alert('ยืนยันการจอง');
        // เพิ่มฟังก์ชันสำหรับการยืนยันการจองที่นี่
        // เช่น การส่งคำขอ POST ไปยัง server เพื่อบันทึกการจอง
    }
</script>
@endsection
