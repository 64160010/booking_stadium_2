@extends('layouts.app')

@section('title', 'รายการอุปกรณ์')

@section('content')
    <div class="container-fluid mt-4">
        <div class="row justify-content-center">
            <div class="col-12">
                <div class="card shadow-lg border-0">
                    <div class="card-header" style="text-align: center; color:white; background-color:#4800ff">
                        <h4>{{ __('รายการอุปกรณ์') }}</h4>
                    </div>

                    @auth
                        @if(Auth::user()->is_admin)
                            <div class="card-body p-3">
                                <div class="d-flex justify-content-end mb-3">
                                    <a href="{{ route('repair') }}" class="btn btn-danger me-2">ซ่อม</a>
                                    <a href="{{ route('add-item') }}" class="btn btn-primary">เพิ่ม</a>
                                </div>
                            </div>
                        @endif
                    @endauth

                    <div class="table-responsive">
                        <!-- ฟอร์มเลือกวันที่ -->
                        <div class="mb-4">
                            <label for="borrow-date" class="form-label">เลือกวันที่</label>
                            <input type="date" id="borrow-date" class="form-control" 
                                min="{{ \Carbon\Carbon::now()->format('Y-m-d') }}" 
                                max="{{ \Carbon\Carbon::now()->addDays(7)->format('Y-m-d') }}">
                        </div>
                        
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
                                @forelse($items as $item)
                                    <tr>
                                        <td>{{ $item->item_code }}</td>
                                        <td>{{ $item->item_name }}</td>
                                        <td>
                                            @if($item->item_picture)
                                                <img src="{{ asset('storage/images/' . $item->item_picture) }}" 
                                                     alt="{{ $item->item_name }}" 
                                                     class="img-thumbnail" 
                                                     style="max-width: 100px; max-height: 100px; object-fit: cover;">
                                            @else
                                                <span>ไม่มีรูปภาพ</span>
                                            @endif
                                        </td>
                                        <td>{{ $item->itemType->type_name }}</td>
                                        <td>{{ $item->price }} บาท</td>
                                        <td>{{ $item->borrowed_quantity }}</td>
                                        <td>{{ $item->repair_quantity }}</td>
                                        <td>{{ $item->item_quantity - $item->borrowed_quantity - $item->repair_quantity }}</td>
                                        <td>
                                            @auth
                                                @if(!Auth::user()->is_admin)
                                                <a href="{{ route('borrow-item', ['item_id' => $item->id, 'date' => request('date')]) }}" 
                                                    class="btn btn-primary" 
                                                    onclick="setBorrowDate(event, {{ $item->id }})">
                                                     {{ __('ยืม') }}
                                                 </a>
                                                 
                                                @endif
                                            @else
                                                <a href="{{ route('login') }}" class="btn btn-primary" onclick="alert('โปรดเข้าสู่ระบบก่อนทำการยืม');">
                                                    {{ __('ยืม') }}
                                                </a>
                                            @endauth
                                            @auth
                                                @if(Auth::user()->is_admin)
                                                    <a href="{{ route('edit-item', $item->id) }}" class="btn btn-secondary btn-sm d-inline ms-2">แก้ไข</a>
                                                    <form action="{{ route('delete-item', $item->id) }}" method="POST" class="d-inline ms-2">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('คุณต้องการลบรายการนี้ใช่หรือไม่?');">ลบ</button>
                                                    </form>
                                                @endif
                                            @endauth
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center">{{ __('ไม่พบรายการอุปกรณ์') }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

@push('scripts')
<script>
    function setBorrowDate(event, itemId) {
        event.preventDefault();
        const date = document.getElementById('borrow-date').value;
        if (!date) {
            alert('กรุณาเลือกวันที่ก่อนทำการยืม');
            return;
        }
        
        // แก้ไขการสร้าง URL โดยส่งพารามิเตอร์อย่างถูกต้อง
        const url = `{{ route('borrow-item', ['item_id' => ':itemId', 'date' => ':date']) }}`;
        const finalUrl = url.replace(':itemId', itemId).replace(':date', date);

        window.location.href = finalUrl;
    }
</script>
@endpush
@endsection
