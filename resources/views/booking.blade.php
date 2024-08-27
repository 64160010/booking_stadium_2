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
    function submitBooking() {
        const date = document.getElementById('booking-date').value;
        const bookingData = {
            date: date,
            // Remove slots information if not needed
        };

        // Send bookingData to server (e.g., via AJAX or form submission)
        console.log('Booking Data:', bookingData);
        // Your AJAX call or form submission logic here
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
</style>
@endpush
@endsection
