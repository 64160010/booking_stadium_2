@extends('layouts.app')

@section('title', 'รายการอุปกรณ์')

@if (session('success'))
    <div id="success-alert" class="alert alert-success">
        {{ session('success') }}
    </div>
@endif

@section('content')
    <div class="container-fluid mt-4">
        <div class="row justify-content-center">
            <div class="col-12">
                <div class="card shadow-lg border-0">
                    <div class="card-header" style="text-align: center; color:white; background-color:#4800ff">
                        <h4>{{ __('รายการอุปกรณ์') }}</h4>
                    </div>

                    @auth
                        @if (Auth::user()->is_admin)
                            <div class="card-body p-3">
                                <div class="d-flex justify-content-end mb-3">
                                    <a href="{{ route('repair') }}" class="btn btn-danger me-2">ซ่อม</a>
                                    <a href="{{ route('add-item') }}" class="btn btn-primary">เพิ่ม</a>
                                </div>
                            </div>
                        @endif
                    @endauth

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
                                @forelse($items as $item)
                                    <tr>
                                        <td>{{ $item->item_code }}</td>
                                        <td>{{ $item->item_name }}</td>
                                        <td>
                                            @if ($item->item_picture)
                                                <img src="{{ asset('storage/images/' . $item->item_picture) }}"
                                                    alt="{{ $item->item_name }}" class="img-thumbnail"
                                                    style="max-width: 100px; max-height: 100px; object-fit: cover;">
                                            @else
                                                <span>ไม่มีรูปภาพ</span>
                                            @endif
                                        </td>
                                        <td>{{ $item->itemType->type_name }}</td>
                                        <td>{{ $item->price }} บาท</td>
                                        <td>{{ $item->borrowed_quantity }}</td>
                                        <td>{{ $item->repair_quantity }}</td>
                                        <td>{{ $item->item_quantity - $item->borrowed_quantity - $item->repair_quantity }}
                                        </td>
                                        <td>
                                            @auth
                                                @if (!Auth::user()->is_admin)
                                                    <a href="{{ route('borrow-item', ['item_id' => $item->id]) }}"
                                                        class="btn btn-primary">
                                                        {{ __('ยืม') }}
                                                    </a>
                                                @endif
                                            @else
                                                <a href="{{ route('login') }}" class="btn btn-primary"
                                                    onclick="alert('โปรดเข้าสู่ระบบก่อนทำการยืม');">
                                                    {{ __('ยืม') }}
                                                </a>
                                            @endauth
                                            @auth
                                                @if (Auth::user()->is_admin)
                                                    <a href="{{ route('edit-item', $item->id) }}"
                                                        class="btn btn-secondary btn-sm d-inline ms-2">แก้ไข</a>
                                                    <form action="{{ route('delete-item', $item->id) }}" method="POST"
                                                        class="d-inline ms-2">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-danger btn-sm"
                                                            onclick="return confirm('คุณต้องการลบรายการนี้ใช่หรือไม่?');">ลบ</button>
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

    @push('styles')
        <style>
            /* ปรับแต่งสไตล์ของ Datepicker */
            .datepicker-dropdown {
                background-color: #f8f9fa;
                /* สีพื้นหลัง */
                border-radius: 0.5rem;
                /* ปรับความโค้ง */
                border: 1px solid #ced4da;
                /* ขอบบางๆ */
                padding: 10px;
                /* ระยะห่างภายใน */
            }

            .datepicker table {
                margin: 0;
                width: 100%;
            }

            .datepicker table th,
            .datepicker table td {
                text-align: center;
                vertical-align: middle;
                padding: 10px;
                /* ขนาดช่องใน Datepicker */
            }

            /* ปรับสีไฮไลต์วันที่ปัจจุบัน */
            .datepicker table .today {
                background-color: #007bff;
                color: white;
                border-radius: 50%;
            }

            /* ปรับสไตล์ปุ่มเลือกวันที่ */
            .input-group-text {
                cursor: pointer;
                border-radius: 0 0.375rem 0.375rem 0;
                /* ปรับความโค้งมุมขวา */
            }

            /* ปรับช่อง input ให้ดูทันสมัย */
            .form-control {
                border: 1px solid #ced4da;
                padding: 10px;
                border-radius: 0.375rem 0 0 0.375rem;
                /* ความโค้งมุมซ้าย */
            }

            /* ปรับสีเมื่อโฟกัสในช่อง input */
            .form-control:focus {
                border-color: #007bff;
                box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, .25);
            }
        </style>
    @endpush

    @push('scripts')
        <!-- โหลด jQuery -->
        <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>

        <script>
            // ฟังก์ชันสำหรับซ่อนข้อความแจ้งเตือนหลังจาก 5 วินาที
            setTimeout(function() {
                const alert = document.getElementById('success-alert');
                if (alert) {
                    alert.style.display = 'none'; // ซ่อนข้อความแจ้งเตือน
                }
            }, 5000); // 5000 milliseconds = 5 seconds
        </script>
    @endpush
@endsection
