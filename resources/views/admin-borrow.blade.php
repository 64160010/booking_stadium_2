@extends('layouts.app')

@section('content')
<div class="container">
    <h2 class="text-center mb-4">การยืม-คืน-ซ่อมอุปกรณ์</h2>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

   <!-- Filter Buttons -->
<div class="text-center mb-3">
    <button class="btn btn-info {{ request('status') == 'รอยืม' ? 'active' : '' }}" onclick="filterBorrowings('รอยืม')">รอยืม</button>
    <button class="btn btn-success {{ request('status') == 'ยืมแล้ว' ? 'active' : '' }}" onclick="filterBorrowings('ยืมแล้ว')">ยืมแล้ว</button>
    <button class="btn btn-danger {{ request('status') == 'คืนแล้ว' ? 'active' : '' }}" onclick="filterBorrowings('คืนแล้ว')">คืนแล้ว</button>
    <button class="btn btn-warning {{ request('status') == 'ซ่อม' ? 'active' : '' }}" onclick="filterBorrowings('ซ่อม')">ซ่อม</button>
    <button class="btn btn-primary {{ request('status') == 'ซ่อมแล้ว' ? 'active' : '' }}" onclick="filterBorrowings('ซ่อมแล้ว')">ซ่อมแล้ว</button>
    <button class="btn btn-secondary {{ request('status') == null ? 'active' : '' }}" onclick="resetFilters()">แสดงทั้งหมด</button>
</div>


    <!-- Borrowing Table -->
    <table class="table table-striped">
        <thead>
            <tr>
                <th>รหัสการจอง</th>
                <th>ชื่อผู้ยืม</th>
                <th>ชื่ออุปกรณ์</th>
                <th>ประเภทอุปกรณ์</th>
                <th>จำนวน</th>
                <th>สนามที่ใช้</th>
                <th>วันที่ยืม</th>
                <th>ช่วงเวลา</th>
                <th>สถานะการยืม</th>
                @auth
                @if (Auth::user()->is_admin)
                <th>จัดการ</th>
                @if (request('status') == 'ซ่อม')
                    <th>หมายเหตุ</th> <!-- Added header for repair note -->
                @endif
            @endif
                @endauth
            </tr>
        </thead>
        <tbody>
            @foreach($borrowDetails as $detail)
                <tr class="{{ $detail->return_status }}">
                    <td>{{ $detail->borrow->bookingStadium->id ?? 'N/A' }}</td>
                    <td>{{ $detail->borrow->user->fname ?? 'N/A' }}</td>
                    <td>{{ $detail->item->item_name ?? 'N/A' }}</td>
                    <td>{{ $detail->item->itemType->type_name ?? 'N/A' }}</td>
                    <td>{{ $detail->borrow_quantity ?? 'N/A' }}</td>
                    <td>{{ $detail->stadium->stadium_name ?? 'N/A' }}</td>
                    <td>{{ $detail->borrow_date ?? 'N/A' }}</td> <!-- แสดงวันที่ยืม -->
                    <td>
                        @php
                            // ตรวจสอบว่ามีการตั้งค่า timeSlots หรือไม่
                            $timeSlots = $detail->timeSlots()->pluck('time_slot')->toArray();
                            $uniqueTimeSlots = array_unique($timeSlots);
                        @endphp
                        {{ !empty($uniqueTimeSlots) ? implode(', ', $uniqueTimeSlots) : 'N/A' }}
                    </td>
                    
                    <td>{{ $detail->return_status }}</td>
                    @auth
                        @if (Auth::user()->is_admin && $detail->return_status != 'ปฏิเสธ')
                        <td>
                            @if ($detail->return_status == 'รอยืม')
                                <!-- ปุ่มสำหรับยืม -->
                                <form action="{{ route('admin.borrow.approve', $detail->id) }}" method="POST" style="display: inline;">
                                    @csrf
                                    <button type="submit" class="btn btn-primary">ยืม</button>
                                </form>
                                
                            @elseif ($detail->return_status == 'ยืมแล้ว')
                                <!-- ปุ่มสำหรับคืน -->
                                <form action="{{ route('admin.borrow.return', $detail->id) }}" method="POST" style="display: inline;">
                                    @csrf
                                    <button type="submit" class="btn btn-success">คืน</button>
                                </form>
                                <!-- ปุ่มสำหรับซ่อม -->
                                <form action="{{ route('admin.borrow.repair', $detail->id) }}" method="GET" style="display: inline;">
                                    <button type="button" class="btn btn-warning" onclick="openRepairModal('{{ route('admin.borrow.repair', $detail->id) }}')">ซ่อม</button>
                                </form>
                                
                        
                            @elseif ($detail->return_status == 'คืนแล้ว')
                                <!-- ถ้าอุปกรณ์คืนแล้ว ไม่ต้องแสดงปุ่มใดๆ -->
                                <span class="text-muted">การยืมเสร็จสมบูรณ์</span>
                        
                                @elseif ($detail->return_status == 'ซ่อม')
                                <!-- สถานะซ่อมและปุ่มเปลี่ยนเป็น "ซ่อมแล้ว" -->
                                <form action="{{ route('admin.borrow.repairComplete', $detail->id) }}" method="POST" style="display: inline;">
                                    @csrf
                                    <input type="hidden" name="repair_note" value="ซ่อมแล้ว"> <!-- Hidden field to send repair note -->
                                    <button type="submit" class="btn btn-info">ซ่อมแล้ว</button>
                                </form>
                        
                            @elseif ($detail->return_status == 'ซ่อมแล้ว')
                                <!-- สถานะเมื่อการซ่อมเสร็จสมบูรณ์ -->
                                <span class="text-muted">ซ่อมเสร็จแล้ว</span>
                            @endif
                        </td>
                        
                        
                        
                        
                            </td>
                        @endif
                        @if (request('status') == 'ซ่อม')
                        <td>{{ $detail->repair_note ?? 'N/A' }}</td> <!-- Display repair note -->
                    @endif

                    @endauth
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

<!-- Repair Modal -->
<div class="modal fade" id="repairModal" tabindex="-1" aria-labelledby="repairModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form id="repairForm" action="" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="repairModalLabel">หมายเหตุการซ่อม</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="repair_note" class="form-label">กรอกหมายเหตุการซ่อม (ไม่เกิน 20 ตัวอักษร)</label>
                        <input type="text" class="form-control" id="repair_note" name="repair_note" required maxlength="20">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ปิด</button>
                    <button type="submit" class="btn btn-warning">ส่งหมายเหตุ</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    function openRepairModal(url) {
        document.getElementById('repairForm').action = url;
        $('#repairModal').modal('show');
    }
</script>



<script>
    function filterBorrowings(status) {
        window.location.href = `?status=${status}`;
    }

    function resetFilters() {
        window.location.href = '?';
    }

    function setRepairAction(actionUrl) {
        document.getElementById('repairForm').action = actionUrl;
    }
</script>

<!-- Bootstrap CSS -->
<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
<!-- jQuery and Bootstrap JS -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

@endsection
