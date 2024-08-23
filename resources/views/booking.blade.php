@extends('layouts.app')

@section('content')
<main class="py-4">
    <div class="container">
        <!-- Date Picker -->
        <div class="mb-4">
            <label for="booking-date" class="form-label">เลือกวันที่</label>
            <input type="date" id="booking-date" class="form-control" value="{{ $date }}" onchange="updateBookings()">
        </div>

        <!-- Fields -->
        @foreach ($stadiums as $stadium)
        <div class="field-container">
            <h3>{{ $stadium->stadium_name }}</h3>
            <p>ราคา: {{ number_format($stadium->stadium_price) }} บาท</p>
            <div>
                @foreach (['11:00-12:00', '12:00-13:00', '13:00-14:00', '14:00-15:00', '15:00-16:00', '16:00-17:00', '17:00-18:00'] as $slot)
                    @php
                        $status = 'green'; // Default to available
                        $startTime = \Carbon\Carbon::createFromFormat('H:i', explode('-', $slot)[0]);
                        $booking = $bookings->first(function ($booking) use ($stadium, $startTime) {
                            return $booking->stadium_id == $stadium->id && $booking->start_time->eq($startTime);
                        });
    
                        if ($booking) {
                            if ($booking->booking_status == 1) {
                                $status = 'grey'; // Booked
                            } elseif ($booking->booking_status == 0) {
                                $status = 'yellow'; // Pending
                            }
                        }
                    @endphp
                    <button class="time-slot-btn {{ $status }}">{{ $slot }}</button>
                @endforeach
            </div>
        </div>
    @endforeach
    

    

        <!-- Booking Button -->
        <div class="text-center">
            <button class="booking-btn">จองสนาม</button>
        </div>
    </div>
</main>

@push('scripts')
<script>
    function updateBookings() {
        const date = document.getElementById('booking-date').value;
        window.location.href = `{{ route('booking') }}?date=${date}`;
    }
</script>
@endpush
@endsection
