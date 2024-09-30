@extends('layouts.app')

@section('content')
    <main class="py-4">
        <div class="container-fluid mt-4">
            <div class="row justify-content-center">
                <div class="col-12">
                    <div class="card shadow-lg border-0">
                        <div class="card-header bg-primary text-white text-center">
                            <h4>{{ __('รายละเอียดการจอง') }}</h4>
                        </div>
                        <div class="card-body p-3">
                            <!-- User and Booking Details -->
                            <h5>รายละเอียดการจอง</h5>
                            <p><strong>วันที่:</strong> {{ $bookingDetail->booking_date }}</p>
                            @if ($bookingDetail->user)
                                <p><strong>ชื่อผู้ใช้:</strong> {{ $bookingDetail->user->fname }}</p>
                                <p><strong>เบอร์โทรศัพท์:</strong> {{ $bookingDetail->user->phone }}</p>
                            @else
                                <p>ไม่พบข้อมูลผู้ใช้</p>
                            @endif
                            

                            <!-- Display Booked Stadiums and Time Slots -->
                            @foreach ($bookingDetails as $booking)
                                @php
                                    $stadium = \App\Models\Stadium::find($booking->stadium_id); // ดึงข้อมูลสนาม
                                @endphp
                                <div class="mb-4">
                                    <h6>สนาม: {{ $stadium ? $stadium->stadium_name : 'ไม่พบสนาม' }}</h6>
                                    <p><strong>เวลา:</strong> {{ $booking->time_slot->time }}</p> <!-- ดึงเวลาได้จากความสัมพันธ์ -->
                                    <p><strong>ราคา:</strong> {{ number_format($booking->booking_total_price) }} บาท</p>
                                </div>
                            @endforeach
                            
                            <div>
                                <p><strong>ชั่วโมงรวม:</strong> {{ $totalHours }} ชั่วโมง</p>
                                <p><strong>ราคาทั้งหมด:</strong> {{ number_format($totalPrice) }} บาท</p>
                            </div>

                            <!-- Confirmation Button -->
                            <div class="text-center">
                                <button class="btn btn-success" onclick="confirmBooking()">ยืนยันการจอง</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    @push('scripts')
        <script>
            function confirmBooking() {
    if (confirm('คุณต้องการยืนยันการจองใช่หรือไม่?')) {
        $.ajax({
            url: '{{ route('booking.store') }}',
            method: 'POST',
            data: {
                date: '{{ $bookingDetail->booking_date }}',
                timeSlots: @json($timeSlots), // คุณต้องแน่ใจว่าตัวแปรนี้มีค่าถูกต้อง
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                alert('การจองสำเร็จ!');
                // เปลี่ยน redirect ไปยังหน้าแสดงรายละเอียดการจอง
                window.location.href = '{{ route("bookingdetail", ["id" => ""]) }}' + response.id; // ส่ง id ของการจอง
            },
            error: function(xhr) {
                let errorMessage = 'เกิดข้อผิดพลาดในการจอง กรุณาลองใหม่';
                if (xhr.responseJSON && xhr.responseJSON.error) {
                    errorMessage = xhr.responseJSON.error; // แสดงข้อความข้อผิดพลาดจากเซิร์ฟเวอร์
                }
                alert(errorMessage);
            }
        });
    }
}


        </script>
    @endpush
@endsection
