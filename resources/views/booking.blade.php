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
                        <!-- Date Picker -->
                        <div class="mb-4">
                            <label for="booking-date" class="form-label">เลือกวันที่</label>
                            <input type="date" id="booking-date" class="form-control" value="{{ $date }}" onchange="updateBookings()">
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
                                    <div class="d-flex flex-wrap">
                                        @foreach (['11:00-12:00', '12:00-13:00', '13:00-14:00', '14:00-15:00', '15:00-16:00', '16:00-17:00', '17:00-18:00'] as $slot)
                                            @php
                                                $status = 'btn-success'; // Default to available
                                                $startTime = \Carbon\Carbon::createFromFormat('H:i', explode('-', $slot)[0]);
                                                $booking = $bookings->first(function ($booking) use ($stadium, $startTime) {
                                                    return $booking->stadium_id == $stadium->id && $booking->start_time->eq($startTime);
                                                });

                                                if ($booking) {
                                                    if ($booking->booking_status == 1) {
                                                        $status = 'btn-secondary'; // Booked
                                                    } elseif ($booking->booking_status == 0) {
                                                        $status = 'btn-warning'; // Pending
                                                    }
                                                }
                                            @endphp
                                            <button class="btn {{ $status }} text-white me-2 mb-2 time-slot-btn" data-bs-toggle="tooltip" data-bs-placement="top" title="{{ $slot }}">{{ $slot }}</button>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach

                        <!-- Booking Button -->
                        <div class="text-center">
                            <button class="btn btn-primary">จองสนาม</button>
                        </div>
                    </div>
                </div>
            </div>
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

@push('styles')
<style>
    .stadium-card {
        border: 2px solid #007bff; /* เพิ่มกรอบรอบสนาม */
        padding: 15px;
        margin-bottom: 15px;
    }

    .time-slot-btn {
        border: 2px solid #ccc; /* เพิ่มกรอบรอบเวลา */
        padding: 5px 10px;
    }
</style>
@endpush
@endsection
