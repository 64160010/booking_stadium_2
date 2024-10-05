@extends('layouts.app')

@section('content')
<main class="py-4">
    <div class="container-fluid mt-4">
        <div class="row justify-content-center">
            <div class="col-12">
                <div class="card shadow-lg border-0">
                    <div class="card-header bg-primary text-white text-center">
                        <h4>{{ __('รายละเอียดการจองสนาม') }}</h4>
                    </div>
                    <div class="card-body p-3">
                        <h5 class="card-title">{{ $bookingDetail->stadium->stadium_name }}</h5>
                        <p class="card-text">วันที่: {{ $bookingDetail->booking_date }}</p>
                        
                        @php
                            // Retrieve booked time slot using time_slot_id
                            $timeSlot = \DB::table('time_slot')->where('id', $bookingDetail->time_slot_id)->first();
                        @endphp
                        
                        @if ($timeSlot)
                            <p class="card-text">เวลาที่จอง: {{ $timeSlot->time }}</p> <!-- Use correct field -->
                        @else
                            <p class="card-text">ไม่พบเวลาที่จอง</p>
                        @endif
                        
                        <p class="card-text">ราคา: {{ number_format($bookingDetail->booking_total_price) }} บาท</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
@endsection
