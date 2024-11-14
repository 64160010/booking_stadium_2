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
                                <div class="input-group">
                                    <!-- ฟอร์มเลือกวันที่ -->
                                    <input type="date" id="booking-date" class="form-control form-control-sm"
                                        min="{{ \Carbon\Carbon::now()->format('Y-m-d') }}"
                                        max="{{ \Carbon\Carbon::now()->addDays(7)->format('Y-m-d') }}"
                                        value="{{ request()->get('date', \Carbon\Carbon::now()->format('Y-m-d')) }}">

                                    <!-- ปุ่มตกลง -->
                                    <button class="btn btn-primary btn-sm" type="button" id="confirm-btn"
                                        onclick="confirmDateSelection()">
                                        ตกลง
                                    </button>
                                </div>
                            </div>

                            <!-- ปุ่มสถานะ -->
                            <div class="mb-4 text-start">
                                <button class="btn btn-md btn-success me-3">ว่าง</button>
                                <button class="btn btn-md btn-warning text-dark me-3">รอการตรวจสอบ</button>
                                <button class="btn btn-md btn-secondary">มีการจองแล้ว</button>
                            </div>


                            @foreach ($stadiums as $stadium)
                                <div class="mb-4">
                                    <div class="card border-light">
                                        <div class="card-body border stadium-card">
                                            <h5 class="card-title">{{ $stadium->stadium_name }}</h5>
                                            <p class="card-text">ราคา: {{ number_format($stadium->stadium_price) }} บาท</p>
                                            <p class="card-text">สถานะ:
                                                <span
                                                    class="badge @if ($stadium->stadium_status == 'พร้อมให้บริการ') bg-success @else bg-danger @endif">
                                                    {{ $stadium->stadium_status }}
                                                </span>
                                            </p>

                                            <!-- การเลือกช่วงเวลา -->
                                            <div class="d-flex flex-wrap">
                                                @foreach ($stadium->timeSlots as $timeSlot)
                                                @php
                                                    $statusClass = 'btn-outline-primary'; 
                                                    $statusText = 'ว่าง';
                                            
                                                    // ตรวจสอบสถานะการจอง
                                                    if ($timeSlot->conflictingBooking) {
                                                        $statusClass = 'btn-secondary disabled'; // สีเทา และปิดการใช้งาน
                                                        $statusText = 'จองซ้ำ';
                                                    } elseif ($timeSlot->booking_status == 'รอการตรวจสอบ') {
                                                        $statusClass = 'btn-warning text-dark'; // สีเหลืองสำหรับ 'รอการตรวจสอบ'
                                                        $statusText = 'รอการตรวจสอบ';
                                                    }
                                                @endphp
                                            
                                                <button class="btn m-1 time-slot-button {{ $statusClass }}"
                                                    data-stadium="{{ $stadium->id }}"
                                                    data-time="{{ $timeSlot->time_slot }}"
                                                    onclick="selectTimeSlot(this, {{ $stadium->id }})"
                                                    {{ $statusClass === 'btn-secondary disabled' ? 'disabled' : '' }}>
                                                    {{ $timeSlot->time_slot }} - {{ $statusText }}
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
                                    <button class="btn btn-primary" onclick="submitBooking()" id="booking-btn" disabled>
                                        {{ __('จองสนาม') }}
                                    </button>
                                @else
                                    <a href="{{ route('login') }}" class="btn btn-primary"
                                        onclick="alert('โปรดเข้าสู่ระบบก่อนทำการจอง');">
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
            let isDateConfirmed = false; // ตัวแปรเช็คว่าได้ยืนยันวันที่หรือยัง

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

            // ฟังก์ชันยืนยันการเลือกวันที่
            function confirmDateSelection() {
                const selectedDate = document.getElementById('booking-date').value;
                if (!selectedDate) {
                    alert('กรุณาเลือกวันที่ก่อน');
                    return;
                }

                isDateConfirmed = true; // ตั้งค่าว่าผู้ใช้ยืนยันวันที่แล้ว
                alert('คุณเลือกวันที่: ' + selectedDate);

                // ทำให้สามารถเลือกช่วงเวลาได้หลังจากยืนยันวันที่
                const timeSlotButtons = document.querySelectorAll('.time-slot-button');
                timeSlotButtons.forEach(button => {
                    if (!button.classList.contains('disabled')) {
                        button.disabled = false; // เปิดการใช้งานปุ่มเลือกช่วงเวลา
                    }
                });

                // เปิดปุ่มจองสนามเมื่อยืนยันวันที่
                document.getElementById('booking-btn').disabled = false;

                // อัปเดต URL ด้วยวันที่ที่เลือก
                window.history.pushState(null, null, `?date=${selectedDate}`);
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
                            window.location.href = '{{ route('login') }}';
                        } else if (data.success) {
                            const bookingStadiumId = data.booking_stadium_id;
                            window.location.href = `{{ url('/bookingDetail') }}/${bookingStadiumId}`;
                        } else if (data.conflictingTimeSlots) {
                            data.conflictingTimeSlots.forEach(timeSlot => {
                                const button = document.querySelector(`button[data-time="${timeSlot}"]`);
                                if (button) {
                                    button.classList.add('btn-secondary', 'disabled');
                                    button.textContent = `${timeSlot} - จองซ้ำ`;
                                }
                            });
                        } else {
                            document.getElementById('booking-result').innerHTML = '<div class="alert alert-danger">' + data
                                .message + '</div>';
                        }
                    })


                    .catch(error => {
                        console.error('Error:', error);
                        document.getElementById('booking-result').innerHTML =
                            '<div class="alert alert-danger">เกิดข้อผิดพลาดในการส่งคำขอการจอง</div>';
                    });
            }

            // ตรวจสอบวันที่จาก URL ทุกครั้งที่โหลดหน้า
            window.onload = function() {
                const urlParams = new URLSearchParams(window.location.search);
                const dateFromUrl = urlParams.get('date');
                if (dateFromUrl) {
                    document.getElementById('booking-date').value = dateFromUrl; // กำหนดค่า date ตาม URL
                    isDateConfirmed = true; // ผู้ใช้ได้ยืนยันวันที่จาก URL
                }
            };
        </script>
    @endpush
@endsection
