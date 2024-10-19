<?php

namespace App\Http\Controllers;

use App\Models\Stadium;
use Illuminate\Http\Request;
use App\Models\BookingStadium;
use App\Models\BookingDetail;
use App\Models\Borrow;
use App\Models\item;
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

            $totalHours = count($timeSlots);

            // Get stadium price
            $stadium = Stadium::find($stadiumId);
            if (!$stadium) {
                return response()->json(['success' => false, 'message' => 'สนามไม่ถูกต้อง.']);
            }

            $totalPrice = $stadium->stadium_price * $totalHours;

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

              
                

                // บันทึกข้อมูลใน booking_detail
                BookingDetail::create([
                    'stadium_id' => $stadiumId,
                    'booking_stadium_id' => $bookingStadiumId,
                    'booking_total_hour' => $totalHours, // Total hours based on time slots
                    'booking_total_price' => $totalPrice,
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
    $userId = auth()->id();
    $latestBookingStadium = BookingStadium::where('users_id', $userId)
        ->where('booking_status', 'รอการชำระเงิน') // เช็คสถานะการจอง
        ->latest()
        ->first();
        
    $booking_stadium_id = $latestBookingStadium ? $latestBookingStadium->id : null;

    // เริ่มต้นตัวแปร $borrowingDetails ให้เป็น null
    $borrowingDetails = null;
    $item = null; // เริ่มต้น $item ให้เป็น null

    if ($booking_stadium_id) {
        // ดึงรายละเอียดการจอง
        $bookingDetails = BookingDetail::where('booking_stadium_id', $booking_stadium_id)->get();

        // เพิ่มตรวจสอบเงื่อนไขถ้ามีรายการจอง
        if ($bookingDetails->isNotEmpty()) {
            $groupedBookingDetails = $bookingDetails->groupBy(function ($item) {
                return $item->stadium_id . '|' . $item->booking_date;
            })->map(function ($group) use ($latestBookingStadium) {
                $timeSlots = $group->pluck('timeSlot.time_slot')->join(', ');
                $bookingStatus = $latestBookingStadium->booking_status;

                return [
                    'stadium_name' => $group->first()->stadium->stadium_name,
                    'booking_date' => $group->first()->booking_date,
                    'time_slots' => $timeSlots,
                    'total_price' => $group->sum('booking_total_price'),
                    'total_hours' => $group->sum('booking_total_hour'),
                    'booking_status' => $bookingStatus,
                ];
            })->values();
        }

        // ดึงรายละเอียดการยืม
        $borrowingDetails = Borrow::where('booking_stadium_id', $booking_stadium_id)->get();

        // ดึงข้อมูลอุปกรณ์ที่สามารถยืมได้ (ต้องมีการกำหนดเงื่อนไขให้ตรงกับฐานข้อมูลของคุณ)
        $item = Item::first(); // ตัวอย่างการดึงข้อมูลอุปกรณ์ตัวแรก

        
        return view('bookingDetail', compact('groupedBookingDetails', 'bookingDetails', 'borrowingDetails', 'booking_stadium_id', 'item'));
    } else {
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
    $booking = BookingStadium::find($id);
    if ($booking) {
        $booking->delete();
        return response()->json(['success' => true, 'message' => 'ลบการจองสำเร็จ']);
    }

    return response()->json(['success' => false, 'message' => 'ไม่พบการจอง']);
}


public function confirmBooking($booking_stadium_id)
{
    // ค้นหารายการจองที่ต้องการยืนยัน
    $booking = BookingStadium::find($booking_stadium_id);

    if ($booking && $booking->booking_status === 'รอการชำระเงิน') {
        // อัปเดตสถานะการจองเป็น 'จองแล้ว'
        $booking->update(['booking_status' => 'จองแล้ว']);

        // ส่งกลับพร้อมข้อความยืนยัน
        return redirect()->route('bookingDetail', ['id' => $booking_stadium_id])
            ->with('success', 'การจองของคุณได้รับการยืนยันเรียบร้อยแล้ว');
    } else {
        // ถ้าไม่พบรายการจองหรือสถานะไม่ถูกต้อง ส่งกลับพร้อมข้อผิดพลาด
        return redirect()->route('bookingDetail', ['id' => $booking_stadium_id])
            ->with('error', 'ไม่สามารถยืนยันการจองได้');
    }
}



}