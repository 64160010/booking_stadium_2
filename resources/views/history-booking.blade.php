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
                        <!-- ปุ่มแสดง Modal -->
                        <button type="button" class="btn btn-primary" data-toggle="modal" 
                                data-target="#detailsModal" 
                                data-booking="{{ json_encode($booking) }}">รายการ</button>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

<!-- Modal สำหรับแสดงรายละเอียด -->
<div class="modal fade" id="detailsModal" tabindex="-1" role="dialog" aria-labelledby="detailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="detailsModalLabel">รายละเอียดการจองและการยืม</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <!-- รายละเอียดการจอง -->
                <h5>รายละเอียดการจอง</h5>
                <p><strong>รหัสการจอง:</strong> <span id="modal-booking-id"></span></p>
                <p><strong>สนามที่จอง:</strong> <span id="modal-stadium-name"></span></p>
                <p><strong>เวลาที่จอง:</strong> <span id="modal-booking-date"></span></p>
                <p><strong>ช่วงเวลาที่จอง:</strong> <span id="modal-time-slot"></span></p>
                <p><strong>ราคา:</strong> <span id="modal-booking-price"></span></p>
                <p><strong>ชั่วโมงรวม:</strong> <span id="modal-total-hours"></span></p>

                <!-- รายละเอียดการยืมอุปกรณ์ -->
                <h5>รายละเอียดการยืมอุปกรณ์</h5>
                <table class="table">
                    <thead>
                        <tr>
                            <th>รหัสอุปกรณ์</th>
                            <th>อุปกรณ์ที่ยืม</th>
                            <th>ประเภทอุปกรณ์</th>
                            <th>ราคา</th>
                            <th>จำนวน</th>
                            <th>ราคารวม</th>
                            <th>วันที่ยืม</th>
                            <th>ช่วงเวลาที่ยืม</th>
                        </tr>
                    </thead>
                    <tbody id="modal-borrow-details">
                        <!-- ข้อมูลการยืมจะถูกเติมที่นี่ด้วย JavaScript -->
                    </tbody>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">ปิด</button>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        $('#detailsModal').on('show.bs.modal', function (event) {
            var button = $(event.relatedTarget); // ปุ่มที่กดเพื่อแสดง modal
            var booking = button.data('booking'); // ดึงข้อมูล booking จากปุ่มที่กด

            // แสดงรายละเอียดการจอง
            $('#modal-booking-id').text(booking.id);
            $('#modal-stadium-name').text(booking.stadium_name);
            $('#modal-booking-date').text(booking.booking_date);
            $('#modal-time-slot').text(booking.time_slot);
            $('#modal-booking-price').text(booking.price);
            $('#modal-total-hours').text(booking.total_hours);

            // ล้างข้อมูลการยืมอุปกรณ์ก่อนเติมข้อมูลใหม่
            $('#modal-borrow-details').empty();

            // แสดงรายละเอียดการยืมอุปกรณ์ (ถ้ามี)
            if (booking.borrow && booking.borrow.length > 0) {
                booking.borrow.forEach(function(borrow) {
                    var row = '<tr>' +
                        '<td>' + borrow.item_id + '</td>' +
                        '<td>' + borrow.item_name + '</td>' +
                        '<td>' + borrow.item_type + '</td>' +
                        '<td>' + borrow.item_price + '</td>' +
                        '<td>' + borrow.item_quantity + '</td>' +
                        '<td>' + borrow.item_total_price + '</td>' +
                        '<td>' + borrow.borrow_date + '</td>' +
                        '<td>' + borrow.time_slot + '</td>' +
                        '</tr>';
                    $('#modal-borrow-details').append(row);
                });
            } else {
                $('#modal-borrow-details').append('<tr><td colspan="8">ไม่มีการยืม</td></tr>');
            }
        });
    });
</script>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>

@endsection
