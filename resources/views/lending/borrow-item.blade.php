@extends('layouts.app')

@section('title', 'ยืมอุปกรณ์')

<!-- แสดงข้อความสำเร็จและข้อความผิดพลาด -->
@if (session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
@endif

@if (session('error'))
    <div class="alert alert-danger">
        {{ session('error') }}
    </div>
@endif

@if ($errors->any())
    <div class="alert alert-danger">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

@section('content')
    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white text-center font-weight-bold" style="font-size: 1.5rem;">
                        {{ __('ยืมอุปกรณ์') }}
                    </div>

                    <div class="card-body">
                        @if (Auth::check())
                            <!-- แบบฟอร์มสำหรับส่งข้อมูลการยืม -->
                            <form method="POST" action="{{ route('borrow-item.store') }}">
                                @csrf
                                <input type="hidden" name="item_id" value="{{ $item->id }}">
                                <input type="hidden" name="borrow_date" value="{{ $bookingDate }}">
                                <input type="hidden" name="booking_stadium_id" value="{{ $stadiumId }}">
                                
                                <!-- Using passed stadium ID -->

                                <!-- ข้อมูลอุปกรณ์ -->
                                <div class="form-row d-flex justify-content-between" style="gap: 5px;">
                                    <div class="form-group col-md-6">
                                        <label for="item_name">{{ __('ชื่ออุปกรณ์') }}</label>
                                        <input type="text" id="item_name" name="item_name" class="form-control"
                                            style="background-color:#e2e2e2" value="{{ $item->item_name }}" readonly>
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label for="item_code">{{ __('รหัสอุปกรณ์') }}</label>
                                        <input type="text" id="item_code" class="form-control" name="item_code"
                                            style="background-color:#e2e2e2" value="{{ $item->item_code }}" readonly>
                                    </div>
                                </div>

                                <div class="form-row d-flex justify-content-between" style="gap: 5px;">
                                    <div class="form-group col-md-6 mt-2">
                                        <label for="item_type">{{ __('ประเภท') }}</label>
                                        <input type="text" id="item_type" name="item_type" class="form-control"
                                            style="background-color:#e2e2e2" value="{{ $item->itemType->type_name }}"
                                            readonly>
                                    </div>

                                    <div class="form-group col-md-6 mt-2">
                                        <label for="price">{{ __('ราคา') }}</label>
                                        <input type="text" id="price" name="price" class="form-control"
                                            style="background-color:#e2e2e2" value="{{ $item->price }} บาท" readonly>
                                    </div>
                                </div>

                                <div class="form-row d-flex justify-content-between" style="gap: 5px;">
                                    <div class="form-group col-md-6 mt-2">
                                        <label for="borrow_date">{{ __('วันที่ยืม') }}</label>
                                        <input type="text" id="borrow_date" class="form-control"
                                            value="{{ \Carbon\Carbon::parse($bookingDate)->format('d/m/Y') }}" readonly>

                                    </div>
                                    <div class="form-group col-md-6 mt-2">
                                        <label for="borrow_quantity">{{ __('จำนวน') }}</label>
                                        <input type="number" id="borrow_quantity" name="borrow_quantity"
                                            class="form-control" min="1" max="{{ $item->item_quantity }}"
                                            value="1" required>
                                    </div>
                                </div>

                                <div class="form-row">
                                    <div class="form-group col-md-12 mt-2">
                                        <label for="stadium_id">{{ __('เลือกสนามที่คุณใช้') }}</label>
                                        <select id="stadium_id" name="stadium_id" class="form-control" required>
                                            @foreach ($stadiums as $stadium)
                                                <option value="{{ $stadium->id }}">{{ $stadium->stadium_name }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <!-- การเลือกช่วงเวลา -->
                                    <div class="form-group col-md-12 mt-2">
                                        <label for="time_slot">{{ __('ช่วงเวลา') }}</label>
                                        <input type="text" class="form-control" value="{{ $timeSlots }}" readonly>
                                    </div>
                                    
                                </div>

                                <!-- ปุ่มยืม -->
                                <div class="d-flex justify-content-end mt-3">
                                    <button type="submit" class="btn btn-primary">{{ __('ยืม') }}</button>
                                </div>
                            </form>
                        @else
                            <div class="text-center">
                                <p>{{ __('กรุณาเข้าสู่ระบบเพื่อทำการยืมอุปกรณ์') }}</p>
                                <a href="{{ route('login') }}" class="btn btn-primary">{{ __('เข้าสู่ระบบ') }}</a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript สำหรับการจัดการช่วงเวลา -->
    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const stadiums = @json($stadiums);
                const stadiumSelect = document.getElementById('stadium_id');
                const timeSlotButtonsContainer = document.getElementById('time-slot-buttons');
                const timeSlotsInput = document.getElementById('time_slot_id');
                let selectedTimeSlots = new Set();

                function createTimeSlotButtons(timeSlots) {
                    timeSlotButtonsContainer.innerHTML = '';

                    timeSlots.forEach(slot => {
                        const button = document.createElement('button');
                        button.type = 'button';
                        button.classList.add('btn', 'btn-outline-primary', 'm-1', 'time-slot-button');
                        button.textContent = slot.time_slot;
                        button.setAttribute('data-time', slot.time_slot);
                        button.addEventListener('click', function() {
                            toggleTimeSlot(button);
                        });
                        timeSlotButtonsContainer.appendChild(button);
                    });
                }

                function toggleTimeSlot(button) {
                    const time = button.getAttribute('data-time');

                    if (selectedTimeSlots.has(time)) {
                        selectedTimeSlots.delete(time);
                        button.classList.remove('active');
                    } else {
                        selectedTimeSlots.add(time);
                        button.classList.add('active');
                    }

                    timeSlotsInput.value = Array.from(selectedTimeSlots).join(',');
                }

                stadiumSelect.addEventListener('change', function() {
                    const selectedStadiumId = parseInt(this.value);
                    const selectedStadium = stadiums.find(s => s.id === selectedStadiumId);

                    if (selectedStadium && selectedStadium.time_slots) {
                        createTimeSlotButtons(selectedStadium.time_slots);
                    } else {
                        timeSlotButtonsContainer.innerHTML = '<p>ไม่มีช่วงเวลาสำหรับสนามนี้</p>';
                    }

                    selectedTimeSlots.clear();
                    timeSlotsInput.value = '';
                });

                // กำหนดช่วงวันยืมเริ่มต้น
                const borrowDateInput = document.getElementById('borrow_date');
                const today = new Date().toISOString().split('T')[0];
                const maxDate = new Date();
                maxDate.setDate(maxDate.getDate() + 7);
                const maxDateStr = maxDate.toISOString().split('T')[0];

                borrowDateInput.setAttribute('min', today);
                borrowDateInput.setAttribute('max', maxDateStr);
            });
        </script>
    @endpush
@endsection
