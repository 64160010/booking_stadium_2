@extends('layouts.app')

@section('content')
<div class="container-fluid mt-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-lg border-0">
                <div class="card-header bg-primary text-white text-center">
                    <h4>{{ __('เพิ่มสนามใหม่') }}</h4>
                </div>
                <div class="card-body p-3">
                    <form action="{{ route('stadiums.store') }}" method="POST" id="stadium-form">
                        @csrf
                        <div class="mb-3">
                            <label for="stadium_name" class="form-label">ชื่อสนาม</label>
                            <input type="text" class="form-control @error('stadium_name') is-invalid @enderror" id="stadium_name" name="stadium_name" value="{{ old('stadium_name') }}" required>
                            @error('stadium_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="stadium_price" class="form-label">ราคา</label>
                            <div class="input-group">
                                <input type="number" class="form-control @error('stadium_price') is-invalid @enderror" id="stadium_price" name="stadium_price" value="{{ old('stadium_price') }}" required>
                                <span class="input-group-text">บาท</span>
                            </div>
                            @error('stadium_price') 
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="stadium_status" class="form-label">สถานะ</label>
                            <select class="form-select @error('stadium_status') is-invalid @enderror" id="stadium_status" name="stadium_status" required>
                                <option value="">เลือกสถานะ</option>
                                <option value="พร้อมให้บริการ" {{ old('stadium_status') == 'พร้อมให้บริการ' ? 'selected' : '' }}>พร้อมให้บริการ</option>
                                <option value="ปิดปรับปรุง" {{ old('stadium_status') == 'ปิดปรับปรุง' ? 'selected' : '' }}>ปิดปรับปรุง</option>
                            </select>
                            @error('stadium_status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="time_slots" class="form-label">ช่วงเวลา</label>
                            <div id="time-slots-container">
                                <div class="input-group mb-2">
                                    <select class="form-select" name="start_time[]" required>
                                        @for ($i = 0; $i <= 23; $i++)
                                            <option value="{{ sprintf('%02d:00', $i) }}">{{ sprintf('%02d:00', $i) }}</option>
                                        @endfor
                                    </select>
                                    <span class="input-group-text">ถึง</span>
                                    <select class="form-select" name="end_time[]" required>
                                        @for ($i = 1; $i <= 24; $i++)
                                            <option value="{{ sprintf('%02d:00', $i) }}">{{ sprintf('%02d:00', $i) }}</option>
                                        @endfor
                                    </select>
                                    <button type="button" class="btn btn-outline-danger remove-time-slot" aria-label="ลบช่วงเวลา">ลบ</button>
                                </div>
                            </div>
                            <button type="button" class="btn btn-outline-success" id="add-time-slot">เพิ่มช่วงเวลา</button>
                        </div>
                        

                        <button type="submit" class="btn btn-primary">เพิ่มสนาม</button>
                        <a href="{{ route('stadiums.index') }}" class="btn btn-secondary">ยกเลิก</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
   document.addEventListener('DOMContentLoaded', function() {
    const container = document.getElementById('time-slots-container');
    const addButton = document.getElementById('add-time-slot');
    const form = document.getElementById('stadium-form');

    addButton.addEventListener('click', function() {
    const div = document.createElement('div');
    div.classList.add('input-group', 'mb-2');
    div.innerHTML = `
        <select class="form-select" name="start_time[]" required>
            ${generateHourOptions()}
        </select>
        <span class="input-group-text">ถึง</span>
        <select class="form-select" name="end_time[]" required>
            ${generateHourOptions(true)}
        </select>
        <button type="button" class="btn btn-outline-danger remove-time-slot" aria-label="ลบช่วงเวลา">ลบ</button>
    `;

     // ตรวจสอบการซ้ำของช่วงเวลา
     if (!checkDuplicateTimeSlots()) {
            container.appendChild(div); // เพิ่มช่องช่วงเวลาใหม่
        } else {
            alert('ช่วงเวลานี้ซ้ำกับที่มีอยู่แล้ว');
        }
    });



// ฟังก์ชันสำหรับสร้างตัวเลือกเวลาเป็นชั่วโมง
function generateHourOptions(end = false) {
    let options = '';
    const startHour = end ? 1 : 0;
    const endHour = end ? 24 : 23;

    for (let i = startHour; i <= endHour; i++) {
        options += `<option value="${String(i).padStart(2, '0')}:00">${String(i).padStart(2, '0')}:00</option>`;
    }
    return options;
}

// ฟังก์ชันตรวจสอบการซ้ำของช่วงเวลา
function checkDuplicateTimeSlots() {
        const startTimes = document.getElementsByName('start_time[]');
        const endTimes = document.getElementsByName('end_time[]');

        // เก็บช่วงเวลาที่มีอยู่ในรูปแบบของ array
        const timeSlots = [];
        for (let i = 0; i < startTimes.length; i++) {
            const startTime = startTimes[i].value;
            const endTime = endTimes[i].value;

            if (timeSlots.some(slot => slot.start === startTime && slot.end === endTime)) {
                return true; // พบช่วงเวลาที่ซ้ำกัน
            }

            timeSlots.push({ start: startTime, end: endTime });
        }
        return false;
    }


    // ลบช่วงเวลา
    container.addEventListener('click', function(event) {
        if (event.target.classList.contains('remove-time-slot')) {
            // ลบช่วงเวลาเมื่อมีมากกว่า 1
            if (container.children.length > 1) {
                event.target.parentElement.remove();
            } else {
                alert('อย่างน้อยต้องมีช่วงเวลาอย่างน้อยหนึ่งช่วง');
            }
        }
    });

    // ตรวจสอบการส่งฟอร์ม
    form.addEventListener('submit', function(event) {
    const startTimes = document.getElementsByName('start_time[]');
    const endTimes = document.getElementsByName('end_time[]');

    for (let i = 0; i < startTimes.length; i++) {
        const startTime = startTimes[i].value;
        const endTime = endTimes[i].value;

        if (startTime >= endTime) {
            alert('กรุณากรอกเวลาที่ถูกต้อง: เวลาเริ่มต้องมาก่อนเวลาสิ้นสุด');
            event.preventDefault(); // หยุดการส่งฟอร์ม
            return;
        }
    }

     // ตรวจสอบว่ามีช่วงเวลาซ้ำกันหรือไม่
     if (checkDuplicateTimeSlots()) {
            alert('มีช่วงเวลาที่ซ้ำกัน กรุณาตรวจสอบ');
            event.preventDefault(); // หยุดการส่งฟอร์ม
        }

});

});
</script>
@endpush
@endsection
