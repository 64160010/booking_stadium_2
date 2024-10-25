@extends('layouts.app')
@if ($errors->any())
    <div class="alert alert-danger">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

@section('content')
    <main class="py-4">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center">
                <h1>รายละเอียดการจอง</h1>
                @if (isset($groupedBookingDetails) && $groupedBookingDetails->isNotEmpty())
                    <span style="padding:10px; font-size:15px;" class="badge bg-info">{{ $groupedBookingDetails->first()['booking_status'] }}</span>
                @endif
            </div>

            @if (isset($groupedBookingDetails) && $groupedBookingDetails->isNotEmpty())
                @php
                    $firstGroup = $groupedBookingDetails->first();
                @endphp
                <div class="d-flex justify-content-between align-items-center bg-light p-3 rounded mb-4">
                    @if ($booking_stadium_id)
                        <p class="mb-0 fw-bold">รหัสการจอง: <span class="text-success">{{ $booking_stadium_id }}</span></p>
                    @endif
                </div>
            @endif

            @if (session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif

            <!-- ถ้ามีข้อความแจ้งเตือน ไม่มีข้อมูลการจอง -->
            @if (isset($message))
                <div class="alert alert-info">
                    {{ $message }}
                </div>
            @elseif ($groupedBookingDetails->Empty())
                <!-- ถ้ามีข้อมูลการจอง -->
                <table class="table table-bordered table-striped mt-4">
                    <thead class="table-light">
                        <tr>
                            <th>สนาม</th>
                            <th>วันที่จอง</th>
                            <th>เวลา</th>
                            <th>ราคา</th>
                            <th>ชั่วโมง</th>
                            <th>ยืมอุปกรณ์</th>
                            <th>ลบ</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($groupedBookingDetails as $group)
                        <tr>
                            <td>{{ $group['stadium_name'] }}</td>
                            <td>{{ $group['booking_date'] }}</td>
                            <td>{{ $group['time_slots'] }}</td>
                            <td>{{ number_format($group['total_price']) }} บาท</td>
                            <td>{{ $group['total_hours'] }}</td>
                            <td>
                                <button class="btn btn-primary"
                                    data-bs-toggle="modal"
                                    data-bs-target="#lendingModal"
                                    data-stadium-name="{{ $group['stadium_name'] }}"
                                    data-booking-date="{{ $group['booking_date'] }}"
                                    data-time-slots="{{ $group['time_slots'] }}"
                                    data-stadium-id="{{ $group['stadium_id'] }}"> <!-- เพิ่ม data-stadium-id -->
                                    ยืมอุปกรณ์
                                </button>
                            </td>
                            <td>
                            </td>
                            
                            
                            
                            
                        </tr>
                    @endforeach
                    
                    </tbody>
                </table>
            @else
                <p>ไม่พบข้อมูลการจอง</p>
            @endif

            <!-- แสดงรายละเอียดการยืมด้านล่าง -->
            @if (isset($borrowingDetails) && $borrowingDetails->isNotEmpty())
<h2 class="mt-5">รายละเอียดการยืมอุปกรณ์</h2>
<table class="table table-bordered table-striped">
    <thead class="table-light">
        <tr>
            <th>สนามที่ใช้</th>
            <th>วันที่ยืม</th>
            <th>ชื่ออุปกรณ์</th>
            
          
            <th>เวลา</th>
            <th>ชั่วโมง</th>
            <th>จำนวน</th>
            <th>ราคา</th>
            <th>ลบ</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($borrowingDetails as $borrow)
            @php
                $groupedDetails = []; // สร้าง array สำหรับเก็บรายละเอียดที่กลุ่ม
                foreach ($borrow->details as $detail) {
                    $key = $detail->item->id . '-' . $detail->stadium->id . '-' . $borrow->borrow_date; // สร้างคีย์สำหรับกลุ่ม
                    if (!isset($groupedDetails[$key])) {
                        $groupedDetails[$key] = [
                            'borrow' => $borrow,
                            'item_name' => $detail->item->item_name,
                            'stadium_name' => $detail->stadium->stadium_name,
                            'time_slots' => [$detail->timeSlot ? $detail->timeSlot->time_slot : 'ไม่มีข้อมูลเวลา'], // ใช้ array เพื่อเก็บช่วงเวลา
                            'total_quantity' => $detail->borrow_quantity,
                            'total_price' => $detail->borrow_total_price,
                        ];
                    } else {
                        // ถ้าซ้ำ ให้เพิ่มจำนวนและราคา
                        $groupedDetails[$key]['total_quantity'] += $detail->borrow_quantity;
                        $groupedDetails[$key]['total_price'] += $detail->borrow_total_price;
                        // รวมช่วงเวลาทั้งหมด
                        if ($detail->timeSlot) {
                            $groupedDetails[$key]['time_slots'][] = $detail->timeSlot->time_slot;
                        }
                    }
                }
            @endphp

            @foreach ($groupedDetails as $group)
                <tr id="borrow-row-{{ $group['borrow']->id }}">
                    <td>{{ $group['stadium_name'] }}</td>
                    <td>{{ $group['borrow']->borrow_date }}</td>
                    <td>{{ $group['item_name'] }}</td>
                    
                    
                    <td>{{ implode(', ', $group['time_slots']) }}</td> <!-- รวมช่วงเวลาเป็น string -->
                    <td></td>
                    <td>{{ $group['total_quantity'] }}</td>
                    <td>{{ number_format($group['total_price']) }} บาท</td>
                    <td>
                        <button class="btn btn-outline-danger delete-borrow" data-id="{{ $group['borrow']->id }}">ลบ</button>
                    </td>
                </tr>
            @endforeach
        @endforeach
    </tbody>
</table>
@endif


        

          


            <div class="d-flex justify-content-between mt-4">
                <button class="btn btn-outline-secondary"
                    onclick="window.location='{{ route('booking') }}'">ย้อนกลับ</button>
                <div>
                    <div class="text-end">
                        <!-- ปุ่มยืนยันการจอง -->
                        @if ($booking_stadium_id)
                            <form action="{{ route('confirmBooking', ['booking_stadium_id' => $booking_stadium_id]) }}"
                                method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-success">ยืนยันการจอง</button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- JavaScript ส่วนจัดการลบข้อมูลแบบ AJAX -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            // เมื่อคลิกปุ่มลบการจอง
            $('.delete-booking').on('click', function(e) {
                e.preventDefault();
                var bookingId = $(this).data('id');
                var row = $('#booking-row-' + bookingId);

                if (confirm('คุณแน่ใจที่จะลบรายการนี้?')) {
                    $.ajax({
                        url: '/booking/' + bookingId,
                        type: 'DELETE',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            row.remove();
                            alert('ลบรายการจองสำเร็จ');
                        },
                        error: function(xhr) {
                            alert('เกิดข้อผิดพลาดในการลบข้อมูล');
                        }
                    });
                }
            });

            // เมื่อคลิกปุ่มลบการยืม
            $('.delete-borrow').on('click', function(e) {
                e.preventDefault();
                var borrowId = $(this).data('id');
                var row = $('#borrow-row-' + borrowId);

                if (confirm('คุณแน่ใจที่จะลบรายการนี้?')) {
                    $.ajax({
                        url: '/lending/borrow/' + borrowId, // เส้นทางสำหรับลบการยืม
                        type: 'DELETE',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            row.remove();
                            alert('ลบรายการยืมสำเร็จ');
                        },
                        error: function(xhr) {
                            alert('เกิดข้อผิดพลาดในการลบข้อมูล');
                        }
                    });
                }
            });
        });

        // เมื่อ modal ถูกเปิด
  // เมื่อคลิกปุ่มยืมอุปกรณ์
  $('.btn.btn-primary[data-bs-toggle="modal"]').on('click', function() {
            var stadiumName = $(this).data('stadium-name');
            var bookingDate = $(this).data('booking-date');
            var timeSlots = $(this).data('time-slots');

            // แสดงข้อมูลใน modal
            $('#lendingModal').find('.stadium-name').text(stadiumName);
            $('#lendingModal').find('#booking_date_display').text(bookingDate);
            $('#lendingModal').find('#time_slots_display').text(timeSlots);

            // อัปเดตค่า hidden input ในฟอร์ม
            $('#stadium_id').val($(this).data('stadium-id')); // แก้ไขเพื่อเก็บ stadium ID หากมี
            $('#booking_date').val(bookingDate); // เก็บวันที่จอง
            $('#time_slots').val(timeSlots); // เก็บช่วงเวลา

        });
    

    </script>

<!-- Modal สำหรับยืมอุปกรณ์ -->
<div class="modal fade" id="lendingModal" tabindex="-1" aria-labelledby="lendingModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="lendingModalLabel">ยืมอุปกรณ์</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="{{ route('borrow.item') }}" method="POST" id="lendingForm">
                    @csrf
                    <input type="hidden" name="stadium_id" id="stadium_id" value="">
                    <input type="hidden" name="booking_date" id="booking_date" value="">
                    <input type="hidden" name="time_slots" id="time_slots" value="">
            
                    <div class="border p-3 mb-3">
                        <h6>รายละเอียดการจอง</h6>
                        <p><strong>สนามที่ยืมอุปกรณ์:</strong> <span class="stadium-name"></span></p>
                        <p><strong>วันที่จองและยืม:</strong> <span id="booking_date_display"></span></p>
                        <p><strong>ช่วงเวลาที่จองและยืม:</strong> <span id="time_slots_display"></span></p>
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
                                    <th>จำนวน</th> <!-- คอลัมน์สำหรับการใส่จำนวน -->
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($items ?? [] as $item)
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
                                    <td>{{ $item->borrow_quantity - $item->borrowed_quantity - $item->repair_quantity }}</td>
                                    <td>
                                        <!-- Include a hidden field for the item ID -->
                                        <input type="hidden" name="item_id[]" value="{{ $item->id }}">
                                        <input type="number" name="borrow_quantity[]" min="0" value="0" 
                                               max="{{ $item->item_quantity - $item->borrowed_quantity - $item->repair_quantity }}"
                                               placeholder="จำนวน" class="form-control" style="width: 100px;">
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
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ปิด</button>
                <button type="submit" class="btn btn-primary" form="lendingForm">ยืนยันการยืม</button>
            </div>
        </div>
    </div>
</div>




@endsection