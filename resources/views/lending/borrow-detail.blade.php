@extends('layouts.app')

@section('content')
<div class="container">
    <h1>รายละเอียดการยืมอุปกรณ์</h1>
    <table class="table">
        <thead>
            <tr>
                <th>ID</th>
                <th>ชื่ออุปกรณ์</th>
                <th>วันที่ยืม</th>
                <th>จำนวน</th>
                <th>สถานะ</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($borrows as $borrow)
            <tr>
                <td>{{ $borrow->id }}</td>
                <td>{{ $borrow->item->item_name }}</td>
                <td>{{ $borrow->borrow_date }}</td>
                <td>{{ $borrow->borrow_quantity }}</td>
                <td>{{ $borrow->borrow_status }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
