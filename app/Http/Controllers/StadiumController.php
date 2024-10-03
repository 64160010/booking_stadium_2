<?php

namespace App\Http\Controllers;

use App\Models\Stadium;
use App\Models\TimeSlot;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StadiumController extends Controller
{
    public function index() {
        // ดึงข้อมูลสนามพร้อมช่วงเวลา
        $stadiums = Stadium::with('timeSlots')->get(); 
        return view('stadium.index', compact('stadiums'));
    }

    public function show() {
        // ดึงข้อมูลสนามทั้งหมดพร้อมช่วงเวลา
        $stadiums = Stadium::with('timeSlots')->get();
        return view('stadium.show', compact('stadiums'));
    }

    public function create() {
        // ดึง time slots ทั้งหมดเพื่อใช้ในหน้า create
        $time_slots = TimeSlot::all(); 
        return view('stadium.create', compact('time_slots'));
    }

    public function store(Request $request)
    {
        // ตรวจสอบข้อมูลที่รับเข้ามา
        $request->validate([
            'stadium_name' => 'required|string|max:255',
            'stadium_price' => 'required|numeric',
            'stadium_status' => 'required|string',
            'start_time' => 'required|array',
            'end_time' => 'required|array',
            'start_time.*' => 'required|date_format:H:i',
            'end_time.*' => 'required|date_format:H:i|after:start_time.*'
        ]);

        // สร้างสนามใหม่
        $stadium = new Stadium();
        $stadium->stadium_name = $request->stadium_name;
        $stadium->stadium_price = $request->stadium_price;
        $stadium->stadium_status = $request->stadium_status;
        $stadium->save();
        
        // เก็บช่วงเวลาที่เลือก โดยเก็บเป็น "start_time-end_time"
        $startTimes = $request->input('start_time');
        $endTimes = $request->input('end_time');
        foreach ($startTimes as $index => $startTime) {
            $timeSlot = new TimeSlot();
            $timeSlot->time_slot = $startTime . '-' . $endTimes[$index];  // เก็บเป็นสตริง "11:00-12:00"
            $timeSlot->stadium_id = $stadium->id;
            $timeSlot->save();
        }
        
        // หลังจากบันทึกแล้วให้กลับไปที่หน้า stadium index พร้อมกับข้อความแสดงความสำเร็จ
        return redirect()->route('stadiums.index')->with('success', 'สนามและช่วงเวลาเพิ่มเรียบร้อยแล้ว');
    }

    public function edit($id) {
        // ดึงข้อมูลสนามตาม id พร้อมกับช่วงเวลา
        $stadium = Stadium::findOrFail($id);
        $time_slots = TimeSlot::where('stadium_id', $id)->get();

        // ตรวจสอบสิทธิ์ของผู้ใช้
        if (Auth::user()->is_admin != 1) {
            return redirect()->route('stadiums.show')->with('error', "You don't have admin access.");
        }

        return view('stadium.edit', compact('stadium', 'time_slots'));
    }

    public function update(Request $request, $id)
    {
        // ตรวจสอบข้อมูลที่ส่งมา
        $request->validate([
            'stadium_name' => 'required|string|max:255',
            'stadium_price' => 'required|numeric',
            'stadium_status' => 'required|string',
            'start_time' => 'required|array',
            'end_time' => 'required|array',
            'start_time.*' => 'required|date_format:H:i',
            'end_time.*' => 'required|date_format:H:i|after:start_time.*'
        ]);

        // หา stadium ตาม id
        $stadium = Stadium::findOrFail($id);

        // อัปเดตข้อมูลสนาม
        $stadium->stadium_name = $request->stadium_name;
        $stadium->stadium_price = $request->stadium_price;
        $stadium->stadium_status = $request->stadium_status;
        $stadium->save();

        // ลบช่วงเวลาเก่าทั้งหมดแล้วเพิ่มใหม่
        TimeSlot::where('stadium_id', $id)->delete();

        // เก็บช่วงเวลาที่อัปเดตใหม่
        $startTimes = $request->input('start_time');
        $endTimes = $request->input('end_time');
        foreach ($startTimes as $index => $startTime) {
            $timeSlot = new TimeSlot();
            $timeSlot->time_slot = $startTime . '-' . $endTimes[$index];  // เก็บเป็นสตริง "11:00-12:00"
            $timeSlot->stadium_id = $stadium->id;
            $timeSlot->save();
        }

        return redirect()->route('stadiums.index')->with('success', 'สนามถูกอัปเดตเรียบร้อยแล้ว');
    }

    public function destroy($id)
    {
        // หา stadium ตาม id
        $stadium = Stadium::findOrFail($id);

        // ลบช่วงเวลาเก่าทั้งหมดที่เกี่ยวข้องกับสนาม
        TimeSlot::where('stadium_id', $id)->delete();

        // ลบ stadium จากฐานข้อมูล
        $stadium->delete();

        // ส่งกลับไปยังหน้าแสดงรายการสนาม
        return redirect()->route('stadiums.index')->with('success', 'สนามถูกลบเรียบร้อยแล้ว');
    }
}
