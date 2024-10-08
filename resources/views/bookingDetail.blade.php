@extends('layouts.app')

@section('content')
<main class="py-4">
    <div class="container">
        <h1>รายละเอียดการจอง</h1>
        

        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        <!-- ถ้ามีข้อความแจ้งเตือน ไม่มีข้อมูลการจอง -->
        @if(isset($message))
            <div class="alert alert-info">
                {{ $message }}
            </div>
            @elseif ($groupedBookingDetails->isNotEmpty())
            <!-- ถ้ามีข้อมูลการจอง -->
            <table class="table table-bordered table-striped">
                <thead class="table-light">
                    <tr>
                        <th>สนาม</th>
                        <th>วันที่จอง</th>
                        <th>เวลา</th>
                        <th>ราคา</th>
                        <th>ชั่วโมง</th>
                        <th>สถานะ</th>
                        <th>ลบ</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($groupedBookingDetails as $group)
                    <tr>
                        <td>{{ $group['stadium_name'] }}</td>
                        <td>{{ $group['booking_date'] }}</td>
                        <td>{{ $group['time_slots'] }}</td>
                        <td>{{ number_format($group['total_price']) }} บาท</td>
                        <td>{{ $group['total_hours'] }}</td>
                        <td><!-- สถานะที่คุณต้องการแสดง --></td>
                        <td>
                            {{-- <button class="btn btn-outline-danger delete-booking" data-id="{{ $detail->id }}">ลบ</button> --}}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        

        @else
            <p>ไม่พบข้อมูลการจอง</p>
        @endif

        <!-- แสดงรายละเอียดการยืมด้านล่าง -->
        @if ($borrowingDetails->isNotEmpty())
        <h2 class="mt-5">รายละเอียดการยืมอุปกรณ์</h2>
        <table class="table table-bordered table-striped">
            <thead class="table-light">
                <tr>
                    <th>รหัสการยืม</th>
                    <th>ชื่ออุปกรณ์</th>
                    <th>สนามที่ใช้</th>
                    <th>วันที่ยืม</th>
                    <th>เวลา</th>
                    <th>จำนวน</th>
                    <th>ราคา</th>
                    <th>สถานะ</th>
                    <th>ลบ</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($borrowingDetails as $borrow)
                    @foreach ($borrow->details as $detail) <!-- วนลูปเพื่อแสดงรายละเอียด -->
                    <tr id="borrow-row-{{ $borrow->id }}">
                        <td>{{ $borrow->id }}</td>
                        <td>{{ $detail->item->item_name }}</td>
                        <td>{{ $detail->stadium->stadium_name }}</td>
                        <td>{{ $borrow->borrow_date }}</td>
                        <td>
                            @if ($detail->timeSlot) <!-- ตรวจสอบว่ามีข้อมูลเวลา -->
                                {{ $detail->timeSlot->time_slot }} <!-- แสดงช่วงเวลาจาก time slot -->
                            @else
                                ไม่มีข้อมูลเวลา
                            @endif
                        </td>
                        <td>{{ $detail->borrow_quantity }}</td>
                        <td>{{ number_format($detail->borrow_total_price) }} บาท</td>
                        <td>{{ $borrow->borrow_status }}</td>
                        <td>
                            <button class="btn btn-outline-danger delete-borrow" data-id="{{ $borrow->id }}">ลบ</button>
                        </td>
                    </tr>
                    @endforeach
                @endforeach
            </tbody>
        </table>
    @else
        <p>ยังไม่มีการยืมอุปกรณ์</p>
    @endif
    
    
        <!-- เงื่อนไขสำหรับปุ่มยืมอุปกรณ์ -->
        @if ($bookingDetails->isNotEmpty())
            <h3 class="mt-3">สามารถยืมอุปกรณ์ได้</h3>
        @else
            <p>คุณต้องจองสนามก่อนนะ ถึงจะสามารถยืมอุปกรณ์ได้</p>
        @endif

        <div class="d-flex justify-content-between mt-4">
            <button class="btn btn-outline-secondary" onclick="window.location='{{ route('booking') }}'">ย้อนกลับ</button>
            <div>
                <button class="btn btn-outline-secondary me-2" onclick="window.location='{{ route('lending.index', ['booking_stadium_id' => $bookingDetails[0]->booking_stadium_id]) }}'">ยืมอุปกรณ์</button>
                <button class="btn btn-success">ยืนยันการจอง</button>
            </div>
        </div>

    </div>
</main>

<!-- JavaScript ส่วนจัดการลบข้อมูลแบบ AJAX -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function() {
        // เมื่อคลิกปุ่มลบการจอง
        $('.delete-booking').on('click', function(e) {
            e.preventDefault();
            var bookingId = $(this).data('id');
            var row = $('#booking-row-' + bookingId);

            if (confirm('คุณแน่ใจที่จะลบรายการนี้?')) {
                $.ajax({
                    url: '/booking/' + bookingId,
                    type: 'DELETE',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        row.remove();
                        alert('ลบรายการจองสำเร็จ');
                    },
                    error: function(xhr) {
                        alert('เกิดข้อผิดพลาดในการลบข้อมูล');
                    }
                });
            }
        });

        // เมื่อคลิกปุ่มลบการยืม
        $('.delete-borrow').on('click', function(e) {
            e.preventDefault();
            var borrowId = $(this).data('id');
            var row = $('#borrow-row-' + borrowId);

            if (confirm('คุณแน่ใจที่จะลบรายการนี้?')) {
                $.ajax({
                    url: '/lending/borrow/' + borrowId, // เส้นทางสำหรับลบการยืม
                    type: 'DELETE',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        row.remove();
                        alert('ลบรายการยืมสำเร็จ');
                    },
                    error: function(xhr) {
                        alert('เกิดข้อผิดพลาดในการลบข้อมูล');
                    }
                });
            }
        });
    });
</script>
@endsection
