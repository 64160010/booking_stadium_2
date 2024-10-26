<?php

namespace App\Http\Controllers;

use App\Models\Stadium;
use Illuminate\Http\Request;
use App\Models\BookingStadium;
use App\Models\BookingDetail;
use App\Models\Borrow;
use App\Models\item;
use App\Models\TimeSlot;


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
    
                $totalHours = 1;
    
                // Get stadium price
                $stadium = Stadium::find($stadiumId);
                if (!$stadium) {
                    return response()->json(['success' => false, 'message' => 'สนามไม่ถูกต้อง.']);
                }
    
                $totalPrice = $stadium->stadium_price * $totalHours;
    
                foreach ($timeSlots as $timeSlot) {
                    // ตรวจสอบว่ามีการจองในวันที่และช่วงเวลานั้นอยู่แล้วหรือไม่
                    $existingBookingDetail = BookingDetail::where('stadium_id', $stadiumId)
                        ->where('booking_date', $validatedData['date'])
                        ->whereHas('timeSlot', function ($query) use ($timeSlot) {
                            $query->where('time_slot', $timeSlot);
                        })
                        ->exists();
    
                    if ($existingBookingDetail) {
                        // ถ้ามีการจองอยู่แล้ว ให้ส่งข้อความแจ้งเตือน
                        return response()->json([
                            'success' => false,
                            'message' => 'ช่วงเวลาที่คุณเลือกถูกจองแล้ว กรุณาเลือกช่วงเวลาอื่น.'
                        ]);
                    }
    
                    // ดึงข้อมูลของช่วงเวลา
                    $timeSlotData = \DB::table('time_slot')
                        ->where('time_slot', $timeSlot)
                        ->where('stadium_id', $stadiumId)
                        ->first();
    
                    if (!$timeSlotData) {
                        return response()->json(['success' => false, 'message' => 'เวลาหรือสนามไม่ถูกต้อง.']);
                    }
    
                    // บันทึกข้อมูลใน booking_detail
                    BookingDetail::create([
                        'stadium_id' => $stadiumId,
                        'booking_stadium_id' => $bookingStadiumId,
                        'booking_total_hour' => $totalHours,
                        'booking_total_price' => $totalPrice,
                        'booking_date' => $validatedData['date'],
                        'users_id' => auth()->id(),
                        'time_slot_id' => $timeSlotData->id,
                    ]);
                }
            }
    
            return response()->json([
                'success' => true,
                'booking_stadium_id' => $bookingStadiumId
            ]);
        } catch (\Exception $e) {
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
                    'id' => $group->first()->booking_stadium_id, 
                    'stadium_id' => $group->first()->stadium_id,
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
        $items = Item::all(); // ใช้ชื่อให้ตรงกับชื่อของโมเดล (Items คือ อุปกรณ์หลายตัว) // ตัวอย่างการดึงข้อมูลอุปกรณ์ตัวแรก

        
        return view('bookingDetail', compact('groupedBookingDetails', 'bookingDetails', 'borrowingDetails', 'booking_stadium_id', 'items'));
    } else {
        $message = 'คุณยังไม่มีรายการจอง';
        return view('bookingDetail', compact('message', 'booking_stadium_id'));
    }
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
        // ไม่อัปเดตสถานะการจอง
        // $booking->update(['booking_status' => 'รอการตรวจสอบ']); // ลบหรือคอมเมนต์บรรทัดนี้

        // ดึงการยืมที่เกี่ยวข้อง
        $borrowing = Borrow::where('booking_stadium_id', $booking_stadium_id)->first();

        if ($borrowing) {
            // ไม่อัปเดตสถานะการยืม
            // $borrowing->update(['borrow_status' => 'รอการตรวจสอบ']); // ลบหรือคอมเมนต์บรรทัดนี้
        }

        // ส่งกลับไปยังหน้าชำระเงินพร้อมข้อมูลการจอง
        return redirect()->route('paymentBooking', ['booking_stadium_id' => $booking_stadium_id])
            ->with('success', 'การจองของคุณได้รับการยืนยันเรียบร้อยแล้ว');
    } else {
        // ถ้าไม่พบรายการจองหรือสถานะไม่ถูกต้อง ส่งกลับพร้อมข้อผิดพลาด
        return redirect()->route('bookingDetail', ['id' => $booking_stadium_id])
            ->with('error', 'ไม่สามารถยืนยันการจองได้');
    }
}




public function showLendingModal($bookingId)
{
    // สมมติว่าคุณมี Booking Model ที่เก็บข้อมูลการจอง
    $booking = Booking::with('stadium')->find($bookingId);
    $items = Item::all(); // หรือเรียกใช้ข้อมูลอุปกรณ์ตามความเหมาะสม
    $group = Booking::find($id);  // ดึงข้อมูลการจองจากฐานข้อมูล

    return view('bookindDetail', compact('booking', 'items','group'));
}


public function showHistoryDetail($booking_stadium_id)
{
    $userId = auth()->id();
    
    // ดึงรายการจองที่ตรงกับ booking_stadium_id ที่ส่งเข้ามา และรวมข้อมูลสนาม
    $bookingStadium = BookingStadium::with('stadium')
        ->where('id', $booking_stadium_id)
        ->where('users_id', $userId)
        ->first();
    
    if (!$bookingStadium) {
        return redirect()->route('booking')->with('error', 'ไม่พบข้อมูลการจอง');
    }
    
    // ดึงรายละเอียดการจองและรวมข้อมูล time_slot
    $bookingDetails = BookingDetail::with('timeSlot')
        ->where('booking_stadium_id', $booking_stadium_id)
        ->get();

    // ดึงรายละเอียดการยืม
    $borrowingDetails = Borrow::where('booking_stadium_id', $booking_stadium_id)->get();

    // ดึงข้อมูลอุปกรณ์ที่สามารถยืมได้
    $items = Item::all();

    // จัดกลุ่ม bookingDetails (เช่นตาม time_slot หรือ stadium)
    $groupedBookingDetails = $bookingDetails->groupBy('time_slot_id');

    return view('history-detail', compact('bookingStadium', 'bookingDetails', 'borrowingDetails', 'items', 'groupedBookingDetails'));
}

public function showAdminHistoryOverview()
{
    // ดึงข้อมูลการจองทั้งหมด
    $allBookingDetails = BookingDetail::with('stadium', 'timeSlot', 'bookingStadium')
        ->get();

    // ดึงรายละเอียดการยืมทั้งหมด
    $allBorrowingDetails = Borrow::with( 'details.item', 'details.timeSlot')
        ->get();

    return view('admin.history-overview', compact('allBookingDetails', 'allBorrowingDetails'));
}


}