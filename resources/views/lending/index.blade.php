@extends('layouts.app')

@section('title', 'ยืมอุปกรณ์')

@section('content')
    <div class="container-fluid mt-4">
        <div class="row justify-content-center">
            <div class="col-12">
                <div class="card shadow-lg border-0">
                    <div class="card-header bg-primary text-white text-center">
                        <h4>{{ __('ยืมอุปกรณ์') }}</h4>
                    </div>
                    <div class="card-body p-3">
                        <div class="d-flex justify-content-end mb-3">
                            <a href="{{ route('repair') }}" class="btn btn-danger me-2">ซ่อม</a>
                            <a href="{{ route('add-item') }}" class="btn btn-primary">เพิ่ม</a>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead class="bg-primary text-white">
                                    <tr>
                                        <th>รหัสอุปกรณ์</th>
                                        <th>ชื่ออุปกรณ์</th>
                                        <th>รูปภาพ</th>
                                        <th>ประเภท</th>
                                        <th>ราคา</th>
                                        <th>ถูกยืม</th>
                                        <th>ซ่อมอยู่</th>
                                        <th>คงเหลือ</th>
                                        <th>จัดการ</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($items as $item)
                                    <tr>
                                        <td>{{ $item->item_code }}</td>
                                        <td>{{ $item->item_name }}</td>
                                        <td>
                                            <img src="{{ asset('storage/images/' . $item->item_picture) }}" 
                                                 alt="{{ $item->item_name }}" 
                                                 class="img-thumbnail" 
                                                 style="max-width: 100px; max-height: 100px; object-fit: cover;">
                                        </td>
                                        <td>{{ $item->itemType->type_name }}</td> <!-- ใช้ itemType เพื่อดึงชื่อประเภท -->
                                        <td>{{ $item->price }} บาท</td>
                                        <td>{{ $item->borrowed_quantity }}</td>
                                        <td>{{ $item->repair_quantity }}</td>
                                        <td>{{ $item->available_quantity }}</td>
                                        <td>
                                            <a href="{{ route('borrow-item', $item->id) }}" class="btn btn-success btn-sm">ยืม</a>
                                            <a href="{{ route('edit-item', $item->id) }}" class="btn btn-secondary btn-sm mt-2">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-pencil" viewBox="0 0 16 16">
                                                    <path d="M12.146.146a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1 0 .708l-10 10a.5.5 0 0 1-.233.131l-5 1.5a.5.5 0 0 1-.606-.606l1.5-5a.5.5 0 0 1 .131-.233l10-10zM11.207 3H13.5L3 13.5V11.207L11.207 3zM15 4.5 13.5 3 12 4.5 13.5 6 15 4.5zM1.5 13l-1 3 3-1 8.5-8.5-2-2L1.5 13z"/>
                                                </svg>
                                            </a>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
