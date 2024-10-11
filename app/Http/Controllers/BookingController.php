<?php

namespace App\Http\Controllers;

use App\Models\Stadium;
use Illuminate\Http\Request;
use App\Models\BookingStadium;
use App\Models\BookingDetail;
use App\Models\Borrow;
use Illuminate\Support\Facades\Auth;

class BookingController extends Controller
{
    public function index(Request $request)
    {
        $date = $request->query('date', date('Y-m-d'));
        $stadiums = Stadium::all();
        
        // ไม่ต้องกำหนด bookingStadiumId ในที่นี้
        return view('booking', compact('stadiums', 'date'));
    }

    public function store(Request $request)
{
    $validatedData = $request->validate([
        'date' => 'required|date',
        'timeSlots' => 'required|array' // ตรวจสอบว่า timeSlots เป็น array หรือไม่
    ]);

    try {
        // ตรวจสอบว่ามีการสร้าง booking_stadium_id ที่ยังไม่ยืนยันในฐานข้อมูลหรือไม่
        $existingBooking = BookingStadium::where('users_id', auth()->id())
            ->where('booking_status', 'รอการชำระเงิน') // เช็คสถานะการจองว่าเป็น "รอการชำระเงิน"
            ->first();

        // ถ้ามีการจองที่ยังไม่ยืนยันอยู่ ให้ใช้ booking_stadium_id เดิม
        if ($existingBooking) {
            $bookingStadiumId = $existingBooking->id;
        } else {
            // ถ้าไม่มี ให้สร้างการจองใหม่
            $bookingStadium = BookingStadium::create([
                'booking_status' => 'รอการชำระเงิน',
                'booking_date' => $validatedData['date'],
                'users_id' => auth()->id(),
            ]);
            $bookingStadiumId = $bookingStadium->id;
        }

        // วนลูปตาม stadiumId และช่วงเวลาที่เลือก
        foreach ($validatedData['timeSlots'] as $stadiumId => $timeSlots) {
            foreach ($timeSlots as $timeSlot) {
                // ดึงข้อมูลของช่วงเวลา
                $timeSlotData = \DB::table('time_slot')
                    ->where('time_slot', $timeSlot)
                    ->where('stadium_id', $stadiumId)
                    ->first();

                // ถ้าไม่พบข้อมูลช่วงเวลา ให้คืนค่าผิดพลาด
                if (!$timeSlotData) {
                    return response()->json(['success' => false, 'message' => 'เวลาหรือสนามไม่ถูกต้อง.']);
                }

                // ดึงข้อมูลของสนามเพื่อคำนวณราคา
                $stadium = Stadium::find($stadiumId);
                if (!$stadium) {
                    return response()->json(['success' => false, 'message' => 'สนามไม่ถูกต้อง.']);
                }

                // คำนวณราคาทั้งหมดโดยใช้ราคาต่อชั่วโมง
                $totalPrice = $stadium->stadium_price * count($timeSlots);

                // บันทึกข้อมูลใน booking_detail
                BookingDetail::create([
                    'stadium_id' => $stadiumId,
                    'booking_stadium_id' => $bookingStadiumId,
                    'booking_total_hour' => count($timeSlots), // จำนวนชั่วโมง
                    'booking_total_price' => $totalPrice, // ราคาทั้งหมด
                    'booking_date' => $validatedData['date'],
                    'users_id' => auth()->id(),
                    'time_slot_id' => $timeSlotData->id, // บันทึก time_slot_id จากข้อมูล time_slot
                ]);
            }
        }

        // ส่งคืนค่า JSON เมื่อสำเร็จ พร้อม booking_stadium_id
        return response()->json([
            'success' => true,
            'booking_stadium_id' => $bookingStadiumId
        ]);
    } catch (\Exception $e) {
        // จัดการข้อผิดพลาด
        return response()->json(['success' => false, 'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()]);
    }
}


    public function show()
    {
        // รับ ID ของผู้ใช้ที่ล็อกอิน
        $userId = auth()->id();
    
        // ดึงรายการจองล่าสุดของผู้ใช้
        // $latestBookingStadium = BookingStadium::where('users_id', $userId)->latest()->first();
        // $booking_stadium_id = $latestBookingStadium ? $latestBookingStadium->id : null;
        
        // ดึงรายการจองที่ยังไม่ยืนยันล่าสุดของผู้ใช้
        $latestBookingStadium = BookingStadium::where('users_id', $userId)
        ->where('booking_status', 'รอการชำระเงิน') // เช็คสถานะการจอง
        ->latest()
        ->first();
        $booking_stadium_id = $latestBookingStadium ? $latestBookingStadium->id : null;

        // ตรวจสอบว่าผู้ใช้นี้มีการจองหรือไม่
        // $bookingDetails = BookingDetail::where('users_id', $userId)->get();
        $bookingDetails = BookingDetail::where('booking_stadium_id', $booking_stadium_id)->get();

    
        if ($bookingDetails->isNotEmpty()) {
            $groupedBookingDetails = $bookingDetails->groupBy(function ($item) {
                return $item->stadium_id . '|' . $item->booking_date;
            })->map(function ($group) {
                $timeSlots = $group->pluck('timeSlot.time_slot')->join(', ');
                return [
                    'stadium_name' => $group->first()->stadium->stadium_name,
                    'booking_date' => $group->first()->booking_date,
                    'time_slots' => $timeSlots,
                    'total_price' => $group->sum('booking_total_price'),
                    'total_hours' => $group->sum('booking_total_hour'),
                ];
            })->values();
    
            // ดึงรายละเอียดการยืมอุปกรณ์
            $borrowingDetails = Borrow::where('booking_stadium_id', $booking_stadium_id)->get(); // แก้ไขการดึงข้อมูล borrowingDetails ให้ตรงกับการจอง
    
            return view('bookingDetail', compact('groupedBookingDetails', 'borrowingDetails', 'booking_stadium_id'));
        } else {
            // ถ้าไม่มีการจอง ให้แสดงข้อความแจ้งเตือน
            $message = 'คุณยังไม่มีรายการจอง ต้องการจองสนามไหม';
            return view('bookingDetail', compact('message', 'booking_stadium_id'));
        }
    }
    


    

// ฟังก์ชันนี้ใช้เพื่อส่งค่าที่ดึงมาจากการยืมไปยัง borrow-item
public function showBorrowItem($booking_stadium_id)
{
    $borrowingDetails = Borrow::where('booking_stadium_id', $booking_stadium_id)->get();
    $availableBorrowDates = $borrowingDetails->pluck('borrow_date')->unique(); 

    // แสดงข้อความถ้าไม่มีการยืม
    $borrowMessage = $borrowingDetails->isEmpty() ? 'ไม่มีรายการยืม' : null;

    return view('borrow-item', compact('availableBorrowDates', 'borrowingDetails', 'borrowMessage'));
}

    



public function destroy($id)
{
    // ค้นหาข้อมูลการจอง
    $bookingDetail = BookingDetail::findOrFail($id);

    // ลบข้อมูลการจอง
    $bookingDetail->delete();

    // ส่ง response กลับไปที่หน้าเว็บ
    return response()->json(['success' => 'ลบรายการจองสำเร็จ']);
}



}