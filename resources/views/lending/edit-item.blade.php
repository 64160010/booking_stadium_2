@extends('layouts.app')

@section('title', 'แก้ไขอุปกรณ์')

@section('content')
    <div class="container-fluid mt-4">
        <div class="row justify-content-center">
            <div class="col-12">
                <div class="card shadow-lg border-0">
                    <div class="card-header bg-primary text-white text-center">
                        <h4>{{ __('แก้ไขอุปกรณ์') }}</h4>
                    </div>
                    <div class="card-body p-3">
                        <form action="{{ route('update-item', $item->id) }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            @method('PUT')

                            <div class="mb-3">
                                <label for="item_code" class="form-label">รหัสอุปกรณ์</label>
                                <input type="text" class="form-control @error('item_code') is-invalid @enderror" id="item_code" name="item_code" value="{{ old('item_code', $item->item_code) }}" required>
                                @error('item_code')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="item_name" class="form-label">ชื่ออุปกรณ์</label>
                                <input type="text" class="form-control @error('item_name') is-invalid @enderror" id="item_name" name="item_name" value="{{ old('item_name', $item->item_name) }}" required>
                                @error('item_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="item_picture" class="form-label">รูปภาพ</label>
                                <input type="file" class="form-control @error('item_picture') is-invalid @enderror" id="item_picture" name="item_picture">
                                @if ($item->item_picture)
                                    <img src="{{ asset('storage/images/' . $item->item_picture) }}" alt="{{ $item->item_name }}" class="img-thumbnail mt-2" style="max-width: 100px; max-height: 100px; object-fit: cover;">
                                @endif
                                @error('item_picture')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="item_type" class="form-label">ประเภท</label>
                                <select class="form-select @error('item_type') is-invalid @enderror" id="item_type" name="item_type" required>
                                    @foreach($itemTypes as $itemType)
                                        <option value="{{ $itemType->id }}" {{ $item->item_type_id == $itemType->id ? 'selected' : '' }}>
                                            {{ $itemType->type_name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('item_type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="price" class="form-label">ราคา</label>
                                <input type="number" step="0.01" class="form-control @error('price') is-invalid @enderror" id="price" name="price" value="{{ old('price', $item->price) }}" required>
                                @error('price')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="item_quantity" class="form-label">จำนวนทั้งหมด</label>
                                <input type="number" class="form-control @error('item_quantity') is-invalid @enderror" id="item_quantity" name="item_quantity" value="{{ old('item_quantity', $item->item_quantity) }}" required>
                                @error('item_quantity')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3 text-center">
                                <button type="submit" class="btn btn-primary">อัปเดต</button>
                                <a href="{{ route('lending.index') }}" class="btn btn-secondary">ยกเลิก</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
