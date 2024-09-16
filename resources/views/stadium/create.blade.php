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
                    <form action="{{ route('stadiums.store') }}" method="POST">
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
                                    <input type="text" class="form-control" name="time_slots[]" placeholder="เวลา เช่น 11:00 - 12:00" required>
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

    addButton.addEventListener('click', function() {
        const div = document.createElement('div');
        div.classList.add('input-group', 'mb-2');
        div.innerHTML = `
            <input type="text" class="form-control" name="time_slots[]" placeholder="เวลา เช่น 11:00 - 12:00" required>
            <button type="button" class="btn btn-outline-danger remove-time-slot" aria-label="ลบช่วงเวลา">ลบ</button>
        `;
        container.appendChild(div);
    });

    container.addEventListener('click', function(event) {
        if (event.target.classList.contains('remove-time-slot')) {
            // Only remove the time slot if there is more than one
            if (container.children.length > 1) {
                event.target.parentElement.remove();
            } else {
                alert('อย่างน้อยต้องมีช่วงเวลาอย่างน้อยหนึ่งช่วง');
            }
        }
    });
});

</script>
@endpush
@endsection