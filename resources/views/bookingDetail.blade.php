@extends('layouts.app')

@section('content')
<main class="py-4">
    <div class="container">
        <h1>รหัสการจอง 001</h1>
        <h1 class="mb-4">รายละเอียดการจอง</h1>

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
        @elseif ($bookingDetails->isNotEmpty())
        <!-- ถ้ามีข้อมูลการจอง -->
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>รหัสการจอง</th>
                    <th>ชื่อจริง</th>
                    <th>เบอร์โทรศัพท์</th>
                    <th>รายการ</th>
                    <th>วันที่</th>
                    <th>เวลา</th>
                    <th>ราคา</th>
                    <th>ชั่วโมง</th>
                    <th>สถานะ</th>
                    <th>ลบ</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($bookingDetails as $detail)
                <tr id="booking-row-{{ $detail->id }}">
                    <td>{{ $detail->id }}</td>
                    <td>{{ $detail->user->fname }}</td>
                    <td>{{ $detail->user->phone }}</td>
                    <td>{{ $detail->stadium->stadium_name }}</td>
                    <td>{{ $detail->booking_date }}</td>
                    <td>{{ $detail->timeSlot->time_slot }}</td>
                    <td>{{ number_format($detail->booking_total_price) }} บาท</td>
                    <td>{{ $detail->booking_total_hour }}</td>
                    <td>{{ $detail->booking_status }}</td>
                    <td>
                        <button class="btn btn-outline-secondary delete-booking" data-id="{{ $detail->id }}">ลบ</button>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="d-flex justify-content-between mt-3">
            <button class="btn btn-outline-secondary" onclick="window.location='{{ route('booking') }}'">ย้อนกลับ</button>
            <div>
                <button class="btn btn-outline-secondary me-2" onclick="window.location='{{ route('lending.index') }}'">ยืมอุปกรณ์</button>
                <button class="btn btn-success">ยืนยันการจอง</button>
            </div>
        </div>
        @else
            <p>ไม่พบข้อมูลการจอง</p>
        @endif

        <!-- แสดงรายละเอียดการยืมด้านล่าง -->
        @if ($borrowingDetails->isNotEmpty())
            <h2 class="mt-5">รายละเอียดการยืมอุปกรณ์</h2>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>รหัสการยืม</th>
                        <th>ชื่ออุปกรณ์</th>
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
                    <tr id="borrow-row-{{ $borrow->id }}">
                        <td>{{ $borrow->id }}</td>
                        <td>{{ $borrow->item_name }}</td>
                        <td>{{ $borrow->borrow_date }}</td>
                        <td>{{ $borrow->time_slot }}</td>
                        <td>{{ $borrow->quantity }}</td>
                        <td>{{ number_format($borrow->price) }} บาท</td>
                        <td>{{ $borrow->borrow_status }}</td>
                        <td>
                            <button class="btn btn-outline-secondary delete-borrow" data-id="{{ $borrow->id }}">ลบ</button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p>ยังไม่มีการยืมอุปกรณ์</p>
        @endif

        <!-- เงื่อนไขสำหรับปุ่มยืมอุปกรณ์ -->
       @if ($bookingDetails->isNotEmpty())
        <button class="btn btn-outline-secondary me-2" onclick="window.location='{{ route('lending.index', ['booking_stadium_id' => $bookingDetails[0]->booking_stadium_id]) }}'">ยืมอุปกรณ์</button>
        @else
        <p>คุณต้องจองสนามก่อนนะ ถึงจะสามารถยืมอุปกรณ์ได้</p>
       @endif

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
