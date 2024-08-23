@extends('layouts.app')

@section('content')
<div class="container-fluid mt-4">
    <div class="row justify-content-center">
        <div class="col-12">
            <div class="card shadow-lg border-0">
                <div class="card-header bg-primary text-white text-center">
                    <h4>{{ __('Dashboard') }}</h4>
                </div>
                <div class="card-body p-3">
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif

                    <div class="row">
                        @foreach ([
    ['info', 'การจัดการสมาชิก', 'เพิ่ม ลบ และแก้ไขข้อมูลสมาชิก', route('users.index'), 'จัดการสมาชิก'],
    ['success', 'การจองสนาม', 'ดูและจัดการการจองสนามทั้งหมด', route('stadiums.index'), 'จัดการการจอง'],
    ['warning', 'สถานะการชำระเงิน', 'ตรวจสอบและอัพเดตสถานะการชำระเงิน', route('payments.index'), 'จัดการสถานะ'],
    ['danger', 'การยืมอุปกรณ์', 'ตรวจสอบและจัดการการยืมอุปกรณ์', route('borrowings.index'), 'จัดการการยืม'],
] as [$color, $title, $text, $link, $buttonText])
    <div class="col-md-6 mb-3">
        <div class="card text-white bg-{{ $color }} shadow-sm">
            <div class="card-body d-flex flex-column justify-content-between">
                <h5 class="card-title">{{ $title }}</h5>
                <p class="card-text">{{ $text }}</p>
                <a href="{{ $link }}" class="btn btn-light align-self-end">{{ $buttonText }}</a>
            </div>
        </div>
    </div>
@endforeach

                    </div>

                    <hr class="my-3">

                    <div class="row mt-3">
                        <div class="col-md-12 mb-3">
                            <div class="card shadow-sm">
                                <div class="card-body">
                                    <h5 class="card-title text-center">สรุปข้อมูลวันนี้</h5>
                                    <div class="text-center mb-3">
                                        <select id="day-selector" class="form-select d-inline w-auto me-2">
                                            @for ($i = 1; $i <= 31; $i++)
                                                <option value="{{ $i }}">{{ $i }}</option>
                                            @endfor
                                        </select>
                                        <select id="month-selector" class="form-select d-inline w-auto me-2">
                                            @foreach([
                                                'มกราคม', 'กุมภาพันธ์', 'มีนาคม', 'เมษายน', 'พฤษภาคม', 'มิถุนายน',
                                                'กรกฎาคม', 'สิงหาคม', 'กันยายน', 'ตุลาคม', 'พฤศจิกายน', 'ธันวาคม'
                                            ] as $index => $month)
                                                <option value="{{ $index + 1 }}">{{ $month }}</option>
                                            @endforeach
                                        </select>
                                        <select id="year-selector" class="form-select d-inline w-auto">
                                            @for ($i = 2024; $i <= 2030; $i++)
                                                <option value="{{ $i }}">{{ $i + 543 }}</option>
                                            @endfor
                                        </select>
                                    </div>
                                    <div id="booking-data" class="mb-3 text-center">
                                        <h6>การจองสนาม</h6>
                                        <p id="booking-fields-count" class="h5">5 สนาม</p>
                                    </div>
                                    <div id="borrowing-data" class="text-center">
                                        <h6>การยืมอุปกรณ์</h6>
                                        <p>ฟุตบอล - 10 ลูก</p>
                                        <p>เสื้อเอี้ยม - 15 ตัว</p>
                                        <p>รองเท้า - 20 คู่</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-md-12 mb-3">
                            <div class="card shadow-sm">
                                <div class="card-body">
                                    <h5 class="card-title text-center">ปฏิทิน</h5>
                                    <div class="text-center mb-3">
                                        <select id="calendar-month-selector" class="form-select d-inline w-auto me-2">
                                            @foreach([
                                                'มกราคม', 'กุมภาพันธ์', 'มีนาคม', 'เมษายน', 'พฤษภาคม', 'มิถุนายน',
                                                'กรกฎาคม', 'สิงหาคม', 'กันยายน', 'ตุลาคม', 'พฤศจิกายน', 'ธันวาคม'
                                            ] as $index => $month)
                                                <option value="{{ $index + 1 }}">{{ $month }}</option>
                                            @endforeach
                                        </select>
                                        <select id="calendar-year-selector" class="form-select d-inline w-auto">
                                            @for ($i = 2024; $i <= 2030; $i++)
                                                <option value="{{ $i }}">{{ $i + 543 }}</option>
                                            @endfor
                                        </select>
                                    </div>
                                    <table class="table table-bordered text-center">
                                        <thead>
                                            <tr>
                                                <th>อา</th>
                                                <th>จ</th>
                                                <th>อ</th>
                                                <th>พ</th>
                                                <th>พฤ</th>
                                                <th>ศ</th>
                                                <th>ส</th>
                                            </tr>
                                        </thead>
                                        <tbody id="calendar-body">
                                            <!-- Calendar rows will be generated here -->
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const daySelector = document.getElementById('day-selector');
        const monthSelector = document.getElementById('month-selector');
        const yearSelector = document.getElementById('year-selector');
        const bookingFieldsCount = document.getElementById('booking-fields-count');
        const borrowingItemsList = document.getElementById('borrowing-items-list');
        const calendarBody = document.getElementById('calendar-body');

        function updateData() {
            const day = daySelector.value;
            const month = monthSelector.value;
            const year = yearSelector.value;

            // Update booking fields count
            bookingFieldsCount.textContent = '5 สนาม'; // Placeholder
            // Add detailed booking data here if needed

            // Update borrowing items
            borrowingItemsList.innerHTML = 
                <li class="list-group-item">ฟุตบอล - 10 ลูก</li>
                <li class="list-group-item">เสื้อเอี้ยม - 15 ตัว</li>
                <li class="list-group-item">รองเท้า - 20 คู่</li>
            ; // Placeholder
        }

        function updateCalendar() {
            const month = calendarMonthSelector.value;
            const year = calendarYearSelector.value;

            let days = new Date(year, month, 0).getDate();
            let firstDay = new Date(year, month - 1, 1).getDay();
            let calendarHtml = '';

            for (let i = 0; i < firstDay; i++) {
                calendarHtml += '<td></td>';
            }

            for (let day = 1; day <= days; day++) {
                calendarHtml += <td>${day}</td>;
                if ((firstDay + day) % 7 === 0) {
                    calendarHtml += '</tr><tr>';
                }
            }

            calendarBody.innerHTML = <tr>${calendarHtml}</tr>;
        }

        daySelector.addEventListener('change', updateData);
        monthSelector.addEventListener('change', updateData);
        yearSelector.addEventListener('change', updateData);

        calendarMonthSelector.addEventListener('change', updateCalendar);
        calendarYearSelector.addEventListener('change', updateCalendar);

        // Initialize data
        updateData();
        updateCalendar();
    });
</script>
@endpush