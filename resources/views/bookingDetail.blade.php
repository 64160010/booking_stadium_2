@extends('layouts.app')

@section('content')
<main class="py-4">
    <div class="container">
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
        // เมื่อคลิกปุ่มลบ
        $('.delete-booking').on('click', function(e) {
            e.preventDefault();

            var bookingId = $(this).data('id');
            var row = $('#booking-row-' + bookingId);

            if (confirm('คุณแน่ใจที่จะลบรายการนี้?')) {
                $.ajax({
                    url: '/booking/' + bookingId, // เส้นทางสำหรับลบ
                    type: 'DELETE',
                    data: {
                        _token: '{{ csrf_token() }}' // รวม token CSRF
                    },
                    success: function(response) {
                        // ลบแถวออกจากตารางเมื่อสำเร็จ
                        row.remove();
                        alert('ลบรายการจองสำเร็จ');
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
