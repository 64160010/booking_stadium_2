@extends('layouts.app')

@section('content')
<div class="container">
    <h1>ประวัติการจอง</h1>
    
    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <h2 class="mt-5">รายละเอียดการจอง</h2>
    <table class="table table-bordered table-striped mt-4">
        <thead class="table-light">
            <tr>
                <th>สนาม</th>
                <th>วันที่จอง</th>
                <th>เวลา</th>
                <th>ราคา</th>
                <th>ชั่วโมง</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($groupedBookingDetails as $group)
                <tr>
                    <td>{{ $group->first()->stadium->stadium_name ?? 'ไม่มีข้อมูลสนาม' }}</td>
                    <td>{{ $group->first()->booking_date ?? 'ไม่มีข้อมูลวันที่จอง' }}</td>
                    <td>{{ $group->pluck('time_slots')->implode(', ') ?: 'ไม่มีข้อมูลเวลา' }}</td>
                    <td>{{ number_format($group->sum('booking_total_price')) }} บาท</td>
                    <td>{{ $group->sum('booking_total_hour') ?? 'ไม่มีข้อมูล' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- คำนวณราคารวมทั้งหมดของการจอง -->
    <h4>รวมราคาการจอง: {{ number_format($groupedBookingDetails->sum(function($group) { return $group->sum('booking_total_price'); })) }} บาท</h4>

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
                </tr>
            </thead>
            <tbody>
                @foreach ($borrowingDetails as $borrow)
                @php
                    $groupedDetails = [];
                    foreach ($borrow->details as $detail) {
                        $key = $detail->item->id . '-' . $detail->stadium->id . '-' . $borrow->borrow_date;
                        
                        // ดึง time_slot_id ทั้งหมดออกมา
                        $timeSlotIds = explode(',', $detail->time_slot_id);
                        // ดึงข้อมูล time_slot จากฐานข้อมูล
                        $timeSlots = \App\Models\TimeSlot::whereIn('id', $timeSlotIds)->pluck('time_slot')->toArray();
        
                        if (!isset($groupedDetails[$key])) {
                            $groupedDetails[$key] = [
                                'borrow' => $borrow,
                                'item_name' => $detail->item->item_name,
                                'stadium_name' => $detail->stadium->stadium_name,
                                'time_slots' => $timeSlots,
                                'total_quantity' => $detail->borrow_quantity,
                                'total_price' => $detail->borrow_total_price,
                            ];
                        } else {
                            $groupedDetails[$key]['total_quantity'] += $detail->borrow_quantity;
                            $groupedDetails[$key]['total_price'] += $detail->borrow_total_price;
                            // รวม time_slots ใหม่
                            $groupedDetails[$key]['time_slots'] = array_unique(array_merge($groupedDetails[$key]['time_slots'], $timeSlots));
                        }
                    }
                @endphp
        
                @foreach ($groupedDetails as $group)
                    <tr id="borrow-row-{{ $group['borrow']->id }}">
                        <td>{{ $group['stadium_name'] }}</td>
                        <td>{{ $group['borrow']->borrow_date }}</td>
                        <td>{{ $group['item_name'] }}</td>
                        <td>{{ implode(', ', $group['time_slots']) }}</td>
                        <td>{{ count($group['time_slots']) }} ชั่วโมง</td>
                        <td>{{ $group['total_quantity'] }}</td>
                        <td>{{ number_format($group['total_price']) }} บาท</td>
                    </tr>
                @endforeach
                @endforeach
            </tbody>
        </table>

        <!-- คำนวณราคารวมทั้งหมดของการยืม -->
        <h4>รวมราคาการยืม: {{ number_format($borrowingDetails->sum(function($borrow) { return $borrow->details->sum('borrow_total_price'); })) }} บาท</h4>
    @endif

    <!-- คำนวณราคารวมทั้งหมดของการจองและการยืม -->
    <h4>ราคารวมทั้งหมด: {{ number_format($groupedBookingDetails->sum(function($group) { return $group->sum('booking_total_price'); }) + $borrowingDetails->sum(function($borrow) { return $borrow->details->sum('borrow_total_price'); })) }} บาท</h4>

    <div class="mt-4">
        <a href="{{ route('history.booking') }}" class="btn btn-secondary">ย้อนกลับ</a>
    </div>
</div>
@endsection
