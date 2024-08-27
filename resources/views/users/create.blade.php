@extends('layouts.app')

@section('content')
<div class="container-fluid mt-4">
    <div class="row justify-content-center">
        <div class="col-12">
            <div class="card shadow-lg border-0">
                <div class="card-header bg-primary text-white text-center">
                    <h4>{{ __('เพิ่มสมาชิกใหม่') }}</h4>
                </div>
                <div class="card-body">
                    <form action="{{ route('users.store') }}" method="POST">
                        @csrf
                        <!-- Existing fields -->

                        <!-- Time slots -->
                        <div class="mb-3">
                            <label for="time_slots" class="form-label">ช่วงเวลาที่สามารถจองได้</label>
                            <div id="time-slots-container">
                                <div class="time-slot-entry">
                                    <input type="text" class="form-control @error('time_slots.*') is-invalid @enderror" name="time_slots[]" placeholder="เช่น 11:00 - 12:00">
                                </div>
                            </div>
                            <button type="button" id="add-time-slot" class="btn btn-secondary mt-2">เพิ่มช่วงเวลา</button>
                            <button type="button" id="remove-time-slot" class="btn btn-danger mt-2">ลบช่วงเวลา</button>
                            @error('time_slots')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <button type="submit" class="btn btn-primary">บันทึก</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const timeSlotsContainer = document.getElementById('time-slots-container');
        const addButton = document.getElementById('add-time-slot');
        const removeButton = document.getElementById('remove-time-slot');
        
        addButton.addEventListener('click', function() {
            const newEntry = document.createElement('div');
            newEntry.classList.add('time-slot-entry');
            newEntry.innerHTML = '<input type="text" class="form-control mt-2" name="time_slots[]" placeholder="เช่น 11:00 - 12:00">';
            timeSlotsContainer.appendChild(newEntry);
        });

        removeButton.addEventListener('click', function() {
            const entries = timeSlotsContainer.querySelectorAll('.time-slot-entry');
            if (entries.length > 1) {
                timeSlotsContainer.removeChild(entries[entries.length - 1]);
            }
        });
    });
</script>
@endsection
@endsection
