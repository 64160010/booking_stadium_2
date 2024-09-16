@extends('layouts.app')

@section('content')
<main class="py-4">
    <div class="container-fluid mt-4">
        <div class="row justify-content-center">
            <div class="col-12">
                <div class="card shadow-lg border-0">
                    <div class="card-header bg-primary text-white text-center">
                        <h4>{{ __('การจองสนาม') }}</h4>
                    </div>
                    <div class="card-body p-3">
                        <div class="mb-4">
                            <label for="booking-date" class="form-label">เลือกวันที่</label>
                            <input type="date" id="booking-date" class="form-control" onchange="updateBookings()" min="{{ \Carbon\Carbon::now()->format('Y-m-d') }}" max="{{ \Carbon\Carbon::now()->addDays(7)->format('Y-m-d') }}">
                        </div>

                        <div class="mb-4 text-start">
                            <button class="btn btn-md btn-success me-3">ว่าง</button>
                            <button class="btn btn-md btn-warning text-dark me-3">รอการตรวจสอบ</button>
                            <button class="btn btn-md btn-secondary">มีการจองแล้ว</button>
                        </div>

                        <!-- Stadiums Loop -->
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
                                    
                                    <div class="d-flex flex-wrap">
                                        @foreach ($stadium->timeSlots as $timeSlot)
                                            <button class="btn btn-outline-primary m-1 time-slot-button" data-stadium="{{ $stadium->id }}" data-time="{{ $timeSlot->time_slot }}" onclick="selectTimeSlot(this, {{ $stadium->id }})">{{ $timeSlot->time_slot }}</button>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach

                        <div class="text-center">
                            <button class="btn btn-primary" onclick="submitBooking()">จองสนาม</button>
                        </div>

                        <div id="booking-result" class="text-center mt-4"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

@push('scripts')
<script>
    let selectedTimeSlots = {};
    let selectedStadiums = {};

    function selectTimeSlot(button, stadiumId) {
        const time = button.getAttribute('data-time');
        if (!selectedTimeSlots[stadiumId]) {
            selectedTimeSlots[stadiumId] = [];
        }

        const timeIndex = selectedTimeSlots[stadiumId].indexOf(time);
        if (timeIndex > -1) {
            selectedTimeSlots[stadiumId].splice(timeIndex, 1);
            button.classList.remove('active');
        } else {
            selectedTimeSlots[stadiumId].push(time);
            button.classList.add('active');
        }
    }

    function updateBookings() {
        const date = document.getElementById('booking-date').value;
        const minDate = new Date();
        const maxDate = new Date();
        maxDate.setDate(minDate.getDate() + 7);

        const selectedDate = new Date(date);

        if (date && (selectedDate < minDate || selectedDate > maxDate)) {
            alert('กรุณาเลือกวันที่ภายใน 7 วัน');
            document.getElementById('booking-date').value = '';
        }
    }

    function submitBooking() {
    const date = document.getElementById('booking-date').value;
    const selectedStadiums = Object.keys(selectedTimeSlots);

    if (!date) {
        alert('กรุณาเลือกวันที่');
        return;
    }

    if (selectedStadiums.length === 0) {
        alert('กรุณาเลือกสนาม');
        return;
    }

    let hasSelectedTime = false;
    for (let stadiumId of selectedStadiums) {
        if (selectedTimeSlots[stadiumId].length > 0) {
            hasSelectedTime = true;
            break;
        }
    }

    if (!hasSelectedTime) {
        alert('กรุณาเลือกช่วงเวลาที่ต้องการจอง');
        return;
    }

    const bookingData = {
        date: date,
        timeSlots: selectedTimeSlots,
        _token: '{{ csrf_token() }}'
    };

    fetch('{{ route('booking.store') }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify(bookingData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.getElementById('booking-result').innerHTML = '<div class="alert alert-success">การจองสำเร็จ</div>';
            // เปลี่ยนสีของปุ่มเวลาที่เลือก
            updateSelectedButtons();
        } else {
            document.getElementById('booking-result').innerHTML = '<div class="alert alert-danger">' + data.message + '</div>';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        document.getElementById('booking-result').innerHTML = '<div class="alert alert-danger">เกิดข้อผิดพลาดในการจองสนาม</div>';
    });
}

function updateSelectedButtons() {
    document.querySelectorAll('.time-slot-button').forEach(button => {
        const stadiumId = button.getAttribute('data-stadium');
        const time = button.getAttribute('data-time');
        if (selectedTimeSlots[stadiumId] && selectedTimeSlots[stadiumId].includes(time)) {
            button.classList.add('btn-warning'); // เปลี่ยนสีปุ่มเป็นสีส้ม
            button.classList.remove('btn-outline-primary');
        }
    });
}

</script>
@endpush
@endsection
