@extends('layouts.app')

@section('title', 'เพิ่มอุปกรณ์')

@section('content')
    <div class="container mt-5">
        <h1 class="text-center">เพิ่มอุปกรณ์</h1>
        <form action="{{ route('store-item') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="mb-3">
                <label for="item_code" class="form-label">รหัสอุปกรณ์</label>
                <input type="text" class="form-control" id="item_code" name="item_code" required>
            </div>
            <div class="mb-3">
                <label for="item_name" class="form-label">ชื่ออุปกรณ์</label>
                <input type="text" class="form-control" id="item_name" name="item_name" required>
            </div>
            <div class="mb-3">
                <label for="item_picture" class="form-label">รูปภาพ</label>
                <input type="file" class="form-control" id="item_picture" name="item_picture" required>
            </div>
            <div class="mb-3">
                <label for="item_type_id" class="form-label">ประเภทอุปกรณ์</label>
                <select class="form-select" id="item_type_id" name="item_type_id" required>
                    @foreach($itemTypes as $itemType)
                        <option value="{{ $itemType->id }}">{{ $itemType->type_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="mb-3">
                <label for="price" class="form-label">ราคา</label>
                <input type="number" class="form-control" id="price" name="price" required>
            </div>
            <div class="mb-3">
                <label for="item_quantity" class="form-label">จำนวน</label>
                <input type="number" class="form-control" id="item_quantity" name="item_quantity" required>
            </div>
            <button type="submit" class="btn btn-primary">เพิ่มอุปกรณ์</button>
        </form>
    </div>
@endsection