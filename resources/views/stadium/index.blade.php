@extends('layouts.app')

@section('content')

<div class="container-fluid mt-4">
    <div class="row justify-content-center">
        <div class="col-12">
            <div class="card shadow-lg border-0">
                <div class="card-header bg-primary text-white text-center">
                    <h4>{{ __('การจัดการสนาม') }}</h4>
                </div>
                <div class="card-body p-3">
                    @if(Auth::user()->is_admin == 1)
                        <a href="{{ route('stadiums.create') }}" class="btn btn-primary mb-3">เพิ่มสนามใหม่</a>
                    @endif

                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>ชื่อสนาม</th>
                                <th>ราคา</th>
                                <th>สถานะ</th>
                                <th>ช่วงเวลาจอง</th>
                                @if(Auth::user()->is_admin == 1)
                                    <th>การจัดการ</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($stadiums as $stadium)
                            <tr>
                                <td>{{ $stadium->stadium_name }}</td>
                                <td>{{ $stadium->stadium_price }} บาท</td>
                                
                                <td>
                                    @if($stadium->stadium_status == 'พร้อมให้บริการ')
                                        <span class="badge bg-success">{{ $stadium->stadium_status }}</span>
                                    @else
                                        <span class="badge bg-danger">{{ $stadium->stadium_status }}</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex justify-content-between">
                                        @for ($i = 11; $i <= 17; $i++)
                                            <button class="btn btn-outline-primary">{{ $i }}:00-{{ $i+1 }}:00</button>
                                        @endfor
                                    </div>
                                </td>
                                @if(Auth::user()->is_admin == 1)
                                    <td>
                                        <a href="{{ route('stadiums.edit', $stadium->id) }}" class="btn btn-warning btn-sm">แก้ไข</a>
                                        <form action="{{ route('stadiums.destroy', $stadium->id) }}" method="POST" style="display:inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm">ลบ</button>
                                        </form>
                                    </td>
                                @endif
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
