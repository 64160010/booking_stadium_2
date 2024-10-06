@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>รายละเอียดการยืมอุปกรณ์</h1>

        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        <table class="table">
            <thead>
                <tr>
                    <th>รหัสการยืม</th>
                    <th>รหัสอุปกรณ์</th> <!-- เพิ่มคอลัมน์รหัสอุปกรณ์ -->
                    <th>ชื่ออุปกรณ์</th>
                    <th>ชื่อสนาม</th> <!-- เพิ่มคอลัมน์ชื่อสนาม -->
                    <th>วันที่ยืม</th>
                    <th>จำนวน</th>
                    <th>สถานะ</th>
                    <th>การกระทำ</th> <!-- เพิ่มคอลัมน์สำหรับการกระทำ -->
                </tr>
            </thead>
            <tbody>
                @foreach ($borrows as $borrow)
                    <tr>
                        <td>{{ $borrow->id }}</td>
                        <td>{{ $borrow->item->item_code }}</td> <!-- แสดงรหัสอุปกรณ์ -->
                        <td>{{ $borrow->item->item_name }}</td>
                        <td>{{ $borrow->timeSlot->stadium->stadium_name ?? 'ไม่มีข้อมูลสนาม' }}</td> <!-- แสดงชื่อสนาม -->
                        <td>{{ $borrow->borrow_date }}</td>
                        <td>
                            @if ($borrow->Details)
                                @foreach ($borrow->Details as $detail)
                                    {{ $detail->borrow_quantity }}
                                @endforeach
                            @else
                                ไม่มีข้อมูลการยืม
                            @endif
                        </td>
                        <td>{{ $borrow->borrow_status }}</td>
                        <td>
                            <form action="{{ route('lending.destroyBorrow', $borrow->id) }}" method="POST"
                                onsubmit="return confirm('คุณแน่ใจหรือไม่ว่าต้องการลบรายการนี้?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger">ลบ</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection