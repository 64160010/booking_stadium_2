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
                                    <h5 class="card-title">{{ $stadium->stadium_name }}</h5>
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
                                                <button class="btn btn-outline-primary m-1 time-slot-button" data-time="{{ $timeSlot->time_slot }}" onclick="selectTimeSlot(this)">{{ $timeSlot->time_slot }}</button>
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
    let selectedTimeSlots = [];

    function selectTimeSlot(button) {
        const time = button.getAttribute('data-time');

        // Toggle button state
        if (selectedTimeSlots.includes(time)) {
            // Remove time from the array if already selected
            selectedTimeSlots = selectedTimeSlots.filter(slot => slot !== time);
            button.classList.remove('active');
        } else {
            // Add time to the array if not selected
            selectedTimeSlots.push(time);
            button.classList.add('active');
        }

        console.log('Selected Time Slots:', selectedTimeSlots); // Debug log
    }

    function submitBooking() {
    const date = document.getElementById('booking-date').value;

    if (selectedTimeSlots.length === 0) {
        alert('กรุณาเลือกช่วงเวลาที่ต้องการจอง');
        return;
    }

    const bookingData = {
        date: date,
        timeSlots: selectedTimeSlots.join(','), // Convert array to a comma-separated string
        stadiums: @json($stadiums), // Ensure stadiums are included
        // Add additional data if needed
    };

    // Create a form and submit it to the booking confirmation page
    const form = document.createElement('form');
    form.method = 'GET'; // or 'POST' if necessary
    form.action = '{{ route('booking.confirmation') }}'; // The route to the confirmation page

    // Append data as hidden inputs
    for (const key in bookingData) {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = key;
        input.value = bookingData[key];
        form.appendChild(input);
    }

    document.body.appendChild(form);
    form.submit();
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
        window.location.href = `{{ route('booking') }}?date=${bookingDateInput.value}`;
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
