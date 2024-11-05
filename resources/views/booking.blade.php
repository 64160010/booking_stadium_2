@extends('layouts.app')

@section('content')
<main class="py-4">
    <div class="container-fluid mt-4">
        <div class="row justify-content-center">
            <div class="col-7">
                <div class="card shadow-lg border-0">
                    <div style="background-color:#27299a;" class="card-header text-white text-center">
                        <h4>{{ __('การจองสนาม') }}</h4>
                    </div>
                    <div class="card-body p-3">
                        <!-- การเลือกวันที่ -->
                        <div class="mb-4">
                            <label for="booking-date" class="form-label">เลือกวันที่</label>
                            <input type="date" id="booking-date" class="form-control" 
                                onchange="updateBookings()" 
                                min="{{ \Carbon\Carbon::now()->format('Y-m-d') }}" 
                                max="{{ \Carbon\Carbon::now()->addDays(7)->format('Y-m-d') }}">
                        </div>

                        <!-- ปุ่มสถานะ -->
                        <div class="mb-4 text-start">
                            <button class="btn btn-md btn-success me-3">ว่าง</button>
                            <button class="btn btn-md btn-warning text-dark me-3">รอการตรวจสอบ</button>
                            <button class="btn btn-md btn-secondary">มีการจองแล้ว</button>
                        </div>

                        <!-- วนลูปผ่านสนาม -->
                        @foreach ($stadiums as $stadium)
                        <div class="mb-4">
                            <div class="card border-light">
                                <div class="card-body border stadium-card">
                                    <h5 class="card-title">{{ $stadium->stadium_name }}</h5>
                                    <p class="card-text">ราคา: {{ number_format($stadium->stadium_price) }} บาท</p>
                                    <p class="card-text">สถานะ: 
                                        <span class="badge @if ($stadium->stadium_status == 'พร้อมให้บริการ') bg-success @else bg-danger @endif">
                                            {{ $stadium->stadium_status }}
                                        </span>
                                    </p>
                                    
                                    <!-- การเลือกช่วงเวลา -->
                                    <div class="d-flex flex-wrap">
                                        @foreach ($stadium->timeSlots as $timeSlot)
                                            <button class="btn m-1 time-slot-button 
                                                @if ($stadium->stadium_status != 'พร้อมให้บริการ') btn-secondary disabled @else btn-outline-primary @endif" 
                                                data-stadium="{{ $stadium->id }}" 
                                                data-time="{{ $timeSlot->time_slot }}" 
                                                onclick="selectTimeSlot(this, {{ $stadium->id }})" 
                                                @if ($stadium->stadium_status != 'พร้อมให้บริการ') disabled @endif>
                                                {{ $timeSlot->time_slot }}
                                            </button>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach

                        <!-- ปุ่มจองตามสถานะการล็อกอิน -->
                        <div class="text-center">
                            @auth
                                <button class="btn btn-primary" onclick="submitBooking()">
                                    {{ __('จองสนาม') }}
                                </button>
                            @else
                                <a href="{{ route('login') }}" class="btn btn-primary" onclick="alert('โปรดเข้าสู่ระบบก่อนทำการจอง');">
                                    {{ __('จองสนาม') }}
                                </a>
                            @endauth
                        </div>

                        <!-- พื้นที่แสดงผลลัพธ์การจอง -->
                        <div id="booking-result" class="text-center mt-4"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

@push('scripts')
<script>
    let selectedTimeSlots = {}; // วัตถุเก็บช่วงเวลาที่เลือกสำหรับแต่ละสนาม

    // ฟังก์ชันสำหรับจัดการการเลือกช่วงเวลา
    function selectTimeSlot(button, stadiumId) {
        const time = button.getAttribute('data-time'); // รับค่าช่วงเวลาที่เลือก
        // ตรวจสอบว่าเป็นปุ่มที่ถูกปิดอยู่หรือไม่
        if (button.classList.contains('disabled')) {
            alert('สนามนี้อยู่ในสถานะปิดปรับปรุง ไม่สามารถเลือกช่วงเวลาได้'); // แสดงข้อความเมื่อกดปุ่มที่ถูกปิด
            return;
        }

        // กำหนดค่าเริ่มต้นสำหรับสนามถ้ายังไม่มี
        if (!selectedTimeSlots[stadiumId]) {
            selectedTimeSlots[stadiumId] = [];
        }

        // ตรวจสอบว่าช่วงเวลาถูกเลือกไว้แล้วหรือไม่
        const timeIndex = selectedTimeSlots[stadiumId].indexOf(time);
        if (timeIndex > -1) {
            // ถ้าเลือกอยู่ ให้ลบออก
            selectedTimeSlots[stadiumId].splice(timeIndex, 1);
            button.classList.remove('active'); // ลบคลาส active
        } else {
            // ถ้ายังไม่เลือก ให้เพิ่มเข้าไป
            selectedTimeSlots[stadiumId].push(time);
            button.classList.add('active'); // เพิ่มคลาส active
        }
    }

    // ฟังก์ชันเพื่ออัปเดตการจองตามวันที่ที่เลือก
    function updateBookings() {
        const date = document.getElementById('booking-date').value;
        const minDate = new Date('{{ \Carbon\Carbon::now()->format('Y-m-d') }}'); // วันที่ปัจจุบัน
        const maxDate = new Date('{{ \Carbon\Carbon::now()->addDays(7)->format('Y-m-d') }}'); // 7 วันจากปัจจุบัน

        const selectedDate = new Date(date);

        // ตรวจสอบว่าหมายเลขวันที่เลือกอยู่ในช่วงที่กำหนด
        if (date && (selectedDate < minDate || selectedDate > maxDate)) {
            alert('กรุณาเลือกวันที่ภายใน 7 วันจากปัจจุบัน');
            document.getElementById('booking-date').value = ''; // รีเซ็ตค่า input
        }
    }

    // ฟังก์ชันสำหรับส่งการจอง
    function submitBooking() {
        const date = document.getElementById('booking-date').value;
        const selectedStadiums = Object.keys(selectedTimeSlots); // รับสนามที่เลือก

        // ตรวจสอบข้อมูลที่ป้อน
        if (!date) {
            alert('กรุณาเลือกวันที่');
            return;
        }

        if (selectedStadiums.length === 0) {
            alert('กรุณาเลือกสนาม');
            return;
        }

        const bookingData = {
            date: date,
            timeSlots: selectedTimeSlots, // ส่งช่วงเวลาที่เลือก
            _token: '{{ csrf_token() }}' // CSRF token สำหรับความปลอดภัย
        };

        // ส่งข้อมูลการจองไปยังเซิร์ฟเวอร์
        fetch('{{ route('booking.store') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest' // แสดงว่าคำร้องนี้เป็น AJAX request
            },
            body: JSON.stringify(bookingData) // แปลงข้อมูลการจองเป็น JSON
        })
        .then(response => response.json())
        .then(data => {
            if (data.login_required) {
                window.location.href = '{{ route('login') }}'; // เปลี่ยนเส้นทางไปที่หน้าเข้าสู่ระบบถ้ายังไม่ได้ล็อกอิน
            } else if (data.success) {
                // เปลี่ยนเส้นทางไปหน้ารายละเอียดการจองทันที
                const bookingStadiumId = data.booking_stadium_id; // รับ ID การจองจากการตอบกลับ
                window.location.href = `{{ url('/bookingDetail') }}/${bookingStadiumId}`; // เปลี่ยนเส้นทางไปหน้ารายละเอียดการจอง
            } else {
                document.getElementById('booking-result').innerHTML = '<div class="alert alert-danger">' + data.message + '</div>'; // แสดงข้อความผิดพลาด
            }
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('booking-result').innerHTML = '<div class="alert alert-danger">เกิดข้อผิดพลาดในการจองสนาม</div>'; // จัดการข้อผิดพลาดเมื่อเรียกใช้งาน
        });
    }

    // ฟังก์ชันเพื่ออัปเดตสไตล์ปุ่มตามช่วงเวลาที่เลือก
    function updateSelectedButtons() {
        document.querySelectorAll('.time-slot-button').forEach(button => {
            const stadiumId = button.getAttribute('data-stadium');
            const time = button.getAttribute('data-time');
            // ตรวจสอบว่าช่วงเวลานั้นถูกเลือกหรือไม่
            if (selectedTimeSlots[stadiumId] && selectedTimeSlots[stadiumId].includes(time)) {
                button.classList.add('btn-warning'); // เปลี่ยนสีปุ่มเพื่อแสดงการเลือก
                button.classList.remove('btn-outline-primary'); // ลบสไตล์ปุ่มเริ่มต้น
            }
        });
    }
</script>


@endpush
@endsection