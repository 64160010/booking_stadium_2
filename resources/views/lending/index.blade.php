@extends('layouts.app')

@section('title', 'รายการอุปกรณ์')

@section('content')
    <div class="container-fluid mt-4">
        <div class="row justify-content-center">
            <div class="col-12">
                <div class="card shadow-lg border-0">
                    <div class="card-header bg-primary text-white text-center">
                        <h4>{{ __('รายการอุปกรณ์') }}</h4>
                    </div>

                    <!-- ปุ่มกรองประเภท -->
                    <div class="card-body p-3">
                        <div class="d-flex justify-content-end mb-3">
                            @foreach($itemTypes as $itemType)
                                <button class="btn btn-outline-danger me-2 filter-btn" data-type="{{ $itemType->type_code }}">
                                    {{ $itemType->type_name }}
                                </button>
                            @endforeach
                            <button class="btn btn-outline-primary" id="show-all">แสดงทั้งหมด</button>
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
                                <tbody id="item-table-body">
                                    @foreach($items as $item)
                                        <tr class="item-row" data-type="{{ $item->itemType->type_code }}">
                                            <td>{{ $item->item_code }}</td>
                                            <td>{{ $item->item_name }}</td>
                                            <td>
                                                <img src="{{ asset('storage/images/' . $item->item_picture) }}" 
                                                     alt="{{ $item->item_name }}" 
                                                     class="img-thumbnail" 
                                                     style="max-width: 100px; max-height: 100px; object-fit: cover;">
                                            </td>
                                            <td>{{ $item->itemType->type_name }}</td>
                                            <td>{{ $item->price }} บาท</td>
                                            <td>{{ $item->borrowed_quantity }}</td>
                                            <td>{{ $item->repair_quantity }}</td>
                                            <td>{{ $item->item_quantity - $item->borrowed_quantity - $item->repair_quantity }}</td>
                                            <td>
                                                @if(Auth::user()->is_admin == 0)
                                                    <a href="{{ route('borrow-item', $item->id) }}" class="btn btn-success btn-sm d-inline">ยืม</a>
                                                @endif

                                                @if(Auth::user()->is_admin == 1)
                                                    <a href="{{ route('edit-item', $item->id) }}" class="btn btn-secondary btn-sm d-inline ms-2">แก้ไข</a>
                                                    <form action="{{ route('delete-item', $item->id) }}" method="POST" class="d-inline ms-2">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('คุณต้องการลบรายการนี้ใช่หรือไม่?');">ลบ</button>
                                                    </form>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    @if(Auth::user()->is_admin == 1)
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const filterButtons = document.querySelectorAll('.filter-btn');
            const showAllButton = document.getElementById('show-all');
            const itemRows = document.querySelectorAll('.item-row');

            filterButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const type = this.getAttribute('data-type');
                    itemRows.forEach(row => {
                        if (row.getAttribute('data-type') === type) {
                            row.style.display = '';
                        } else {
                            row.style.display = 'none';
                        }
                    });
                });
            });

            showAllButton.addEventListener('click', function() {
                itemRows.forEach(row => {
                    row.style.display = '';
                });
            });
        });
    </script>
    @endpush

@endsection
