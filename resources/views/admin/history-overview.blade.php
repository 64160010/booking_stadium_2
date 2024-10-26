@extends('layouts.app')

@section('content')
<div class="container">
    <h1>ประวัติการจองและการยืมทั้งหมด</h1>

    <h2>รายการจอง</h2>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>สนาม</th>
                <th>วันที่จอง</th>
                <th>เวลา</th>
                <th>สถานะการชำระเงิน</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($allBookingDetails as $booking)
                <tr>
                    <td>{{ $booking->stadium->stadium_name ?? 'ไม่มีข้อมูลสนาม' }}</td>
                    <td>{{ $booking->booking_date }}</td>
                    <td>{{ $booking->timeSlot->time_slot ?? 'ไม่มีข้อมูลเวลา' }}</td>
                    <td>สถานะการชำระเงิน</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <h2>รายละเอียดการยืม</h2>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>สนาม</th>
                <th>วันที่ยืม</th>
                <th>ชื่ออุปกรณ์</th>
                <th>เวลา</th>
                <th>จำนวน</th>
                <th>ราคา</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($allBorrowingDetails as $borrow)
                <tr>
                    <td>{{ $borrow->stadium->stadium_name ?? 'ไม่มีข้อมูลสนาม' }}</td>
                    <td>{{ $borrow->borrow_date }}</td>
                    <td>{{ implode(', ', $borrow->details->pluck('item.item_name')->toArray()) }}</td>
                    <td>{{ implode(', ', $borrow->details->pluck('timeSlot.time_slot')->toArray()) }}</td>
                    <td>{{ $borrow->details->sum('borrow_quantity') }}</td>
                    <td>{{ number_format($borrow->details->sum('borrow_total_price')) }} บาท</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
