<?php

namespace App\Http\Controllers;

use App\Models\Stadium;
use App\Models\TimeSlot;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StadiumController extends Controller
{
    public function index() {
        $stadiums = Stadium::with('timeSlots')->get(); // ดึงข้อมูลสนามพร้อมช่วงเวลา
        return view('stadium.index', compact('stadiums'));
    }
    

    public function show() {
        $stadiums = Stadium::with('timeSlots')->get(); // ดึงข้อมูลสนามทั้งหมด
        return view('stadium.show', compact('stadiums'));
    }

    public function create() {
        // ดึง time slots ทั้งหมดเพื่อใช้ในหน้า create
        $time_slots = TimeSlot::all(); 
        return view('stadium.create', compact('time_slots'));
    }

    public function store(Request $request)
{
    $request->validate([
        'stadium_name' => 'required|string|max:255',
        'stadium_price' => 'required|numeric',
        'stadium_status' => 'required|string',
        'time_slots' => 'required|array',
        'time_slots.*' => 'string'
    ]);
    
    $stadium = new Stadium();
    $stadium->stadium_name = $request->stadium_name;
    $stadium->stadium_price = $request->stadium_price;
    $stadium->stadium_status = $request->stadium_status;
    $stadium->save();
    
        // เก็บช่วงเวลาใหม่
    foreach ($request->time_slots as $time) {
        $timeSlot = new TimeSlot();
        $timeSlot->time_slot = $time;
        $timeSlot->stadium_id = $stadium->id;
        $timeSlot->save();
    }
    
    return redirect()->route('stadiums.index')->with('success', 'สนามและช่วงเวลาเพิ่มเรียบร้อยแล้ว');
}
    
    

public function edit($id) {
    $stadium = Stadium::findOrFail($id);
    $time_slots = TimeSlot::where('stadium_id', $id)->get(); // ดึงช่วงเวลาที่เกี่ยวข้องกับสนาม

    if (Auth::user()->is_admin != 1) {
        return redirect()->route('stadiums.show')->with('error', "You don't have admin access.");
    }

    return view('stadium.edit', compact('stadium', 'time_slots'));
}

public function update(Request $request, $id)
{
    $stadium = Stadium::findOrFail($id);

    // อัปเดตข้อมูลสนาม
    $stadium->stadium_name = $request->stadium_name;
    $stadium->stadium_price = $request->stadium_price;
    $stadium->stadium_status = $request->stadium_status;
    $stadium->save();

    // ลบช่วงเวลาเก่าทั้งหมดแล้วเพิ่มใหม่
    TimeSlot::where('stadium_id', $id)->delete();

    if ($request->has('time_slots')) {
        foreach ($request->time_slots as $time) {
            $timeSlot = new TimeSlot();
            $timeSlot->time_slot = $time;
            $timeSlot->stadium_id = $stadium->id;
            $timeSlot->save();
        }
    }

    return redirect()->route('stadiums.index')->with('success', 'สนามถูกอัปเดตเรียบร้อยแล้ว');
}

public function destroy($id)
{
    // หา stadium ตาม ID
    $stadium = Stadium::findOrFail($id);

    // ลบช่วงเวลาเก่าทั้งหมดที่เกี่ยวข้องกับสนาม
    TimeSlot::where('stadium_id', $id)->delete();

    // ลบ stadium จากฐานข้อมูล
    $stadium->delete();

    // ส่งกลับไปยังหน้าแสดงรายการสนาม
    return redirect()->route('stadiums.index')->with('success', 'สนามถูกลบเรียบร้อยแล้ว');
}

    
}    


