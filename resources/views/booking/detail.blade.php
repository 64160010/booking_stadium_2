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
                            <p><strong>วันที่:</strong> {{ $date }}</p>
                            @if ($user)
                                <p><strong>ชื่อผู้ใช้:</strong> {{ $user->fname }}</p>
                                <p><strong>เบอร์โทรศัพท์:</strong> {{ $user->phone }}</p>
                            @else
                                <p>ไม่พบข้อมูลผู้ใช้</p>
                            @endif

                            <!-- Display Booked Stadiums and Time Slots -->
                            @if (!empty($stadiums) && !empty($timeSlots) && !empty($stadiumPrices))
                                @foreach ($stadiums as $index => $stadiumName)
                                    <div class="mb-4">
                                        <h6>สนาม: {{ $stadiumName }}</h6>
                                        <p><strong>เวลา:</strong> {{ $timeSlots[$index] ?? 'N/A' }}</p>
                                        <p><strong>ราคา:</strong> {{ number_format($stadiumPrices[$index] ?? 0) }} บาท</p>
                                    </div>
                                @endforeach
                                <div>
                                    <p><strong>ชั่วโมงรวม:</strong> {{ $totalHours }} ชั่วโมง</p>
                                    <p><strong>ราคาทั้งหมด:</strong> {{ number_format($totalPrice) }} บาท</p>
                                </div>
                            @else
                                {{-- <p>ข้อมูลการจองไม่ครบถ้วน</p> --}}
                            @endif

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
                date: '{{ $date }}',
                timeSlots: @json($timeSlots),
                stadiums: @json($stadiums),
                stadiumPrices: @json($stadiumPrices),
                totalHours: '{{ $totalHours }}',
                totalPrice: '{{ $totalPrice }}',
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                alert('การจองสำเร็จ!');
                window.location.href = '/booking/history'; // ตัวอย่างการ redirect
            },
            error: function(error) {
                alert('เกิดข้อผิดพลาดในการจอง กรุณาลองใหม่');
            }
        });
    }
}

        </script>
    @endpush

@endsection
