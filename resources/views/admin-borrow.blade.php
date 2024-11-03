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
        <button class="btn btn-info" onclick="filterBorrowings('รอยืม')">รอยืม</button>
        <button class="btn btn-success" onclick="filterBorrowings('ยืมแล้ว')">ยืมแล้ว</button>
        <button class="btn btn-danger" onclick="filterBorrowings('คืนแล้ว')">คืนแล้ว</button>
        <button class="btn btn-warning" onclick="filterBorrowings('ซ่อม')">ซ่อม</button>
        <button class="btn btn-secondary" onclick="resetFilters()">แสดงทั้งหมด</button>
    </div>

    <!-- Borrowing Table -->
    <table class="table table-striped">
        <thead>
            <tr>
                <th>รหัสการยืม</th>
                <th>ชื่อผู้ยืม</th>
                <th>อุปกรณ์</th>
                <th>จำนวน</th>
                <th>วันที่ยืม</th>
                <th>สถานะการยืม</th>
                <th>รายละเอียด</th>
                @auth
                    @if (Auth::user()->is_admin)
                        <th>จัดการ</th>
                    @endif
                @endauth
            </tr>
        </thead>
        <tbody>
            @foreach($borrows as $borrowing)
            <tr class="{{ $borrowing->borrow_status }}">
                <td>{{ $borrowing->id }}</td>
                <td>{{ $borrowing->user->name ?? 'N/A' }}</td>
                <td>{{ $borrowing->item->name ?? 'N/A' }}</td>
                <td>{{ $borrowing->quantity }}</td>
                <td>{{ $borrowing->borrow_date }}</td>
                <td>{{ $borrowing->borrow_status }}</td>
                <td>
                    <a href="{{ route('admin.borrow', $borrowing->id) }}" class="btn btn-primary">รายการ</a>
                </td>
                @auth
                    @if (Auth::user()->is_admin && $borrowing->borrow_status != 'ปฏิเสธ')
                        <td>
                            <!-- Approve and Reject Actions -->
                            <form action="{{ route('admin.borrow', $borrowing->id) }}" method="POST" style="display: inline;">
                                @csrf
                                <button type="submit" class="btn btn-success">อนุมัติ</button>
                            </form>
                            <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#rejectModal{{ $borrowing->id }}">ปฏิเสธ</button>
                            
                            <!-- Reject Modal -->
                            <div class="modal fade" id="rejectModal{{ $borrowing->id }}" tabindex="-1" aria-labelledby="rejectModalLabel{{ $borrowing->id }}" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="rejectModalLabel{{ $borrowing->id }}">เหตุผลที่ปฏิเสธการยืม</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <form action="{{ route('admin.borrow', $borrowing->id) }}" method="POST">
                                                @csrf
                                                <div class="form-group">
                                                    <label for="reject_reason">หมายเหตุ (ไม่เกิน 20 ตัวอักษร):</label>
                                                    <input type="text" name="reject_reason" class="form-control" maxlength="20" required>
                                                </div>
                                                <button type="submit" class="btn btn-danger mt-3">ปฏิเสธ</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </td>
                    @elseif ($borrowing->borrow_status == 'ปฏิเสธ')
                        <td>
                            <small>{{ $borrowing->reject_reason ?? 'ไม่มีเหตุผล' }}</small>
                        </td>
                    @endif
                @endauth
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

<script>
    function filterBorrowings(status) {
        window.location.href = `?status=${status}`;
    }

    function resetFilters() {
        window.location.href = '?';
    }
</script>
@endsection
