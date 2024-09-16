@extends('layouts.app')

@section('title', 'เพิ่มอุปกรณ์')

@section('content')
    <div class="container mt-5">
        <h1 class="text-center">เพิ่มอุปกรณ์</h1>
        <form action="{{ route('store-item') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="mb-3">
                <label for="item_name" class="form-label">ชื่ออุปกรณ์</label>
                <input type="text" class="form-control @error('item_name') is-invalid @enderror" id="item_name" name="item_name" value="{{ old('item_name') }}" required>
                @error('item_name')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                @enderror
            </div>
            <div class="mb-3">
                <label for="item_picture" class="form-label">รูปภาพ</label>
                <input type="file" class="form-control @error('item_picture') is-invalid @enderror" id="item_picture" name="item_picture" required>
                @error('item_picture')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                @enderror
            </div>
            <div class="mb-3">
                <label for="item_type_id" class="form-label">ประเภทอุปกรณ์</label>
                <select class="form-select @error('item_type_id') is-invalid @enderror" id="item_type_id" name="item_type_id" required>
                    <option value="" disabled selected>เลือกประเภทอุปกรณ์</option>
                    @foreach($itemTypes as $itemType)
                        <option value="{{ $itemType->id }}" {{ old('item_type_id') == $itemType->id ? 'selected' : '' }}>
                            {{ $itemType->type_name }}
                        </option>
                    @endforeach
                </select>
                @error('item_type_id')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                @enderror
            </div>
            <div class="mb-3">
                <label for="price" class="form-label">ราคา</label>
                <input type="number" class="form-control @error('price') is-invalid @enderror" id="price" name="price" value="{{ old('price') }}" required>
                @error('price')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                @enderror
            </div>
            <div class="mb-3">
                <label for="item_quantity" class="form-label">จำนวน</label>
                <input type="number" class="form-control @error('item_quantity') is-invalid @enderror" id="item_quantity" name="item_quantity" value="{{ old('item_quantity') }}" required>
                @error('item_quantity')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                @enderror
            </div>
            <button type="submit" class="btn btn-primary">เพิ่มอุปกรณ์</button>
        </form>
    </div>
@endsection
