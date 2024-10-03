@extends('layouts.app')

@section('content')
<div class="container-fluid mt-4">
    <div class="row justify-content-center">
        <div class="col-12">
            <div class="card shadow-lg border-0">
                <div class="card-header bg-primary text-white text-center">
                    <h4>{{ __('การจัดการสมาชิก') }}</h4>
                </div>
                <div class="card-body p-3">
                    <a href="{{ route('users.create') }}" class="btn btn-primary mb-3">เพิ่มสมาชิกใหม่</a>

                    <!-- Search form -->
                    <form method="GET" action="{{ route('users.index') }}" class="mb-4">
                        <div class="row">
                            <div class="col-md-9">
                                <input type="text" name="search" class="form-control" placeholder="ค้นหาชื่อ, นามสกุล หรือ เบอร์โทร" value="{{ request('search') }}">
                            </div>
                            <div class="col-md-3 text-right">
                                <button type="submit" class="btn btn-secondary">ค้นหา</button>
                            </div>
                        </div>
                    </form>

                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>ลำดับ</th> <!-- เพิ่มคอลัมน์ลำดับ -->
                                <th>ชื่อ</th>
                                <th>นามสกุล</th>
                                <th>อีเมล</th>
                                <th>เบอร์โทร</th>
                                <th>สถานะ</th>
                                <th>การจัดการ</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($users as $user)
                            <tr>
                                <td>{{ $loop->index + 1 + ($users->currentPage() - 1) * $users->perPage() }}</td> <!-- คำนวณลำดับ -->
                                <td>{{ $user->fname }}</td>
                                <td>{{ $user->lname }}</td>
                                <td>{{ $user->email }}</td>
                                <td>{{ $user->phone }}</td>
                                <td>{{ $user->is_admin ? 'แอดมิน' : 'ผู้ใช้' }}</td>
                                <td>
                                    <a href="{{ route('users.edit', $user->id) }}" class="btn btn-warning btn-sm">แก้ไข</a>
                                    <form action="{{ route('users.destroy', $user->id) }}" method="POST" style="display:inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm">ลบ</button>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center">ไม่พบข้อมูลสมาชิก</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>

                    <!-- Pagination links -->
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            แสดง {{ $users->count() }} รายการจาก {{ $users->total() }}
                        </div>
                        <div class="d-flex">
                            <div class="me-2"> <!-- เพิ่ม margin ให้กับปุ่มก่อนหน้า -->
                                @if ($users->onFirstPage())
                                    <button class="btn btn-secondary" disabled>« ก่อนหน้า</button>
                                @else
                                    <a href="{{ $users->previousPageUrl() }}" class="btn btn-primary">« ก่อนหน้า</a>
                                @endif
                            </div>
                    
                            <div>
                                @if ($users->hasMorePages())
                                    <a href="{{ $users->nextPageUrl() }}" class="btn btn-primary">ถัดไป »</a>
                                @else
                                    <button class="btn btn-secondary" disabled>ถัดไป »</button>
                                @endif
                            </div>
                        </div>
                    </div>
                    
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
