@extends('layouts.app')

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
                <th>สถานะการจอง</th>
                <th>จำนวนเงิน</th>
                <th>วันที่และเวลาโอน</th>
                <th>สถานะการยืม</th>
                <th>รายละเอียด</th>
            </tr>
        </thead>
        <tbody>
            @foreach($bookings as $booking)
                <tr>
                    <td>{{ $booking->id }}</td>
                    <td>{{ $booking->payer_name }}</td>
                    <td>{{ $booking->booking_status }}</td>
                    <td>
                        @if($booking->payment)
                            {{ number_format($booking->payment->amount, 2) }}
                        @else
                            N/A
                        @endif
                    </td>
                    
                    <td>{{ $booking->payment->transfer_datetime ?? 'N/A' }}</td>
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
                        <button>รายการ</button>
                    </td>
                    
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
