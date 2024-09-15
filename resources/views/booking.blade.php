@extends('layouts.app')
@stack('styles')

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
                        <!-- Date Picker -->
                        <div class="mb-4">
                            <label for="booking-date" class="form-label">เลือกวันที่</label>
                            <input type="date" id="booking-date" class="form-control" value="{{ $date }}" onchange="updateBookings()" min="{{ \Carbon\Carbon::now()->format('Y-m-d') }}" max="{{ \Carbon\Carbon::now()->addDays(7)->format('Y-m-d') }}">
                        </div>

                        <!-- Status Indicators -->
                        <div class="mb-4 text-start">
                            <button class="btn btn-md btn-success me-3">ว่าง</button>
                            <button class="btn btn-md btn-warning text-dark me-3">รอการตรวจสอบ</button>
                            <button class="btn btn-md btn-secondary">มีการจองแล้ว</button>
                        </div>

                        <!-- Fields -->
                        @foreach ($stadiums as $stadium)
                        <div class="mb-4">
                            <div class="card border-light">
                                <div class="card-body border stadium-card">
                                    <h5 class="card-title">
                                        <input type="checkbox" name="stadium_id" value="{{ $stadium->id }}" id="stadium-{{ $stadium->id }}" data-stadium-name="{{ $stadium->stadium_name }}">
                                        <label for="stadium-{{ $stadium->id }}">{{ $stadium->stadium_name }}</label>
                                    </h5>
                                    <p class="card-text">ราคา: {{ number_format($stadium->stadium_price) }} บาท</p>
                                    <p class="card-text">สถานะ: 
                                        <span class="badge 
                                            @if ($stadium->stadium_status == 'พร้อมให้บริการ') 
                                                bg-success
                                            @elseif ($stadium->stadium_status == 'ปิดปรับปรุง') 
                                                bg-danger
                                            @else 
                                                bg-secondary
                                            @endif">
                                            {{ $stadium->stadium_status }}
                                        </span>
                                    </p>
                                    
                                    <!-- Display Time Slots for Users -->
                                    <div class="d-flex flex-wrap">
                                        @foreach ($stadium->timeSlots as $timeSlot)
                                            @if($stadium->stadium_status == 'ปิดปรับปรุง')
                                                <button class="btn btn-outline-secondary m-1" disabled>{{ $timeSlot->time_slot }}</button>
                                            @else
                                                <button class="btn btn-outline-primary m-1 time-slot-button" data-stadium="{{ $stadium->id }}" data-time="{{ $timeSlot->time_slot }}" onclick="selectTimeSlot(this)">{{ $timeSlot->time_slot }}</button>
                                            @endif
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                        
                        <!-- Booking Button -->
                        <div class="text-center">
                            <button class="btn btn-primary" onclick="submitBooking()">จองสนาม</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

@push('scripts')
<script>
    let selectedTimeSlots = {}; // Object to store selected time slots per stadium

    function selectTimeSlot(button) {
        const time = button.getAttribute('data-time');
        const stadiumId = button.getAttribute('data-stadium');

        // Initialize the stadium entry in selectedTimeSlots if not present
        if (!selectedTimeSlots[stadiumId]) {
            selectedTimeSlots[stadiumId] = [];
        }

        // Toggle button state
        if (selectedTimeSlots[stadiumId].includes(time)) {
            // Remove time from the array if already selected
            selectedTimeSlots[stadiumId] = selectedTimeSlots[stadiumId].filter(slot => slot !== time);
            button.classList.remove('active');
        } else {
            // Add time to the array if not selected
            selectedTimeSlots[stadiumId].push(time);
            button.classList.add('active');
        }

        console.log('Selected Time Slots:', selectedTimeSlots); // Debug log
    }

    function submitBooking() {
    const date = document.getElementById('booking-date').value;

    if (!date) {
        alert('กรุณาเลือกวันที่');
        return;
    }

    if (Object.keys(selectedTimeSlots).length === 0) {
        alert('กรุณาเลือกช่วงเวลาที่ต้องการจอง');
        return;
    }

    const stadiumIds = Object.keys(selectedTimeSlots);

    if (stadiumIds.length === 0) {
        alert('กรุณาเลือกสนาม');
        return;
    }

    const bookingData = {
        date: date,
        timeSlots: selectedTimeSlots, // Keep the object structure for sending
        _token: '{{ csrf_token() }}' // CSRF token for security
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
            window.location.href = `{{ route('booking.confirmation') }}?date=${encodeURIComponent(date)}&stadiums=${encodeURIComponent(JSON.stringify(selectedTimeSlots))}`;
        } else {
            alert('เกิดข้อผิดพลาดในการจองสนาม');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('เกิดข้อผิดพลาดในการจองสนาม');
    });
}



function updateBookings() {
    const bookingDateInput = document.getElementById('booking-date');
    const selectedDate = new Date(bookingDateInput.value);
    const today = new Date();
    const maxDate = new Date();
    maxDate.setDate(today.getDate() + 7);

    if (selectedDate < today || selectedDate > maxDate) {
        alert('กรุณาเลือกวันที่ภายใน 7 วันจากวันนี้');
        bookingDateInput.value = ''; // Clear the input
        return;
    }

    // Proceed with updating bookings
    window.location.href = `{{ route('booking') }}?date=${encodeURIComponent(bookingDateInput.value)}`;
}

</script>
@endpush

@push('styles')
<style>
    .stadium-card {
        border: 2px solid #0050a7;
        padding: 15px;
        margin-bottom: 15px;
    }
    .time-slot-button.active {
        background-color: #007bff;
        color: white;
    }
</style>

@endpush
@endsection
