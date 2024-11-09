<?php

namespace App\Http\Controllers;

use App\Models\Stadium;
use Illuminate\Http\Request;
use App\Models\BookingStadium;
use App\Models\BorrowDetail;
use App\Models\BookingDetail;
use App\Models\Borrow;
use App\Models\item;
use App\Models\TimeSlot;
use Carbon\Carbon;



use Illuminate\Support\Facades\Auth;

class BookingController extends Controller
{
    public function index(Request $request)
{
    // รับวันที่จาก query string หรือกำหนดเป็นวันที่ปัจจุบันถ้าไม่มีการระบุ
    $date = $request->query('date', date('Y-m-d'));

    // ดึงข้อมูลสนามทั้งหมดรวมถึง timeSlots และการจองในวันนี้จาก booking_detail
    $stadiums = Stadium::with(['timeSlots', 'details' => function($query) use ($date) {
        // กรองข้อมูลการจองที่เกี่ยวข้องกับวันที่จาก booking_detail
        $query->whereHas('bookingStadium', function($query) use ($date) {
            $query->where('booking_date', Carbon::parse($date)); // กรองวันที่จาก booking_stadium
        });
    }])->get();

    // ดึงข้อมูลการจองของผู้ใช้ในวันนี้
    $userBookings = BookingStadium::where('users_id', auth()->id())->get();

    // ดึงข้อมูลการจองในวันที่ที่ระบุจาก booking_detail ที่เกี่ยวข้องกับ booking_stadium
    $details = BookingDetail::with(['stadium', 'bookingStadium'])
        ->whereHas('bookingStadium', function($query) use ($date) {
            $query->where('booking_date', Carbon::parse($date)); // กรองวันที่จาก booking_stadium
        })
        ->whereNotNull('stadium_id') // กรอง stadium_id ที่ไม่เป็น null จาก booking_detail
        ->get();

    // ส่งข้อมูลไปยัง view
    return view('booking', compact('stadiums', 'date', 'details', 'userBookings'));
}


    
    
    
    
    

public function store(Request $request)
{
    $validatedData = $request->validate([
        'date' => 'required|date',
        'timeSlots' => 'required|array'
    ]);

    try {
        // ตรวจสอบสถานะการจองของผู้ใช้ที่มีอยู่แล้วว่าเป็น 'รอการชำระเงิน' หรือไม่
        $existingBooking = BookingStadium::where('users_id', auth()->id())
            ->where('booking_status', 'รอการชำระเงิน')
            ->first();

        // ใช้ bookingStadiumId ที่มีอยู่ หรือสร้างใหม่ถ้าไม่มี
        if ($existingBooking) {
            $bookingStadiumId = $existingBooking->id;
        } else {
            $bookingStadium = BookingStadium::create([
                'booking_status' => 'รอการชำระเงิน',
                'booking_date' => $validatedData['date'],
                'users_id' => auth()->id(),
            ]);
            $bookingStadiumId = $bookingStadium->id;
        }

// คำเตือนถ้ามีการจองซ้ำ
$conflictingTimeSlots = [];  // เพื่อเก็บช่วงเวลาที่มีการจองซ้ำ

// ตรวจสอบช่องเวลาที่เลือกว่า มีผู้ใช้คนอื่นที่มีสถานะ 'รอการตรวจสอบ' อยู่หรือไม่
foreach ($validatedData['timeSlots'] as $stadiumId => $timeSlots) {
    foreach ($timeSlots as $timeSlot) {
        $timeSlotData = \DB::table('time_slot')
            ->where('time_slot', $timeSlot)
            ->where('stadium_id', $stadiumId)
            ->first();

        if (!$timeSlotData) {
            return response()->json(['success' => false, 'message' => 'เวลาหรือสนามไม่ถูกต้อง.']);
        }

        // ตรวจสอบการจองซ้ำจากผู้ใช้คนอื่นที่มีสถานะ 'รอการตรวจสอบ' ในตาราง booking_stadium
        $existingOtherUserBooking = BookingDetail::where('booking_date', $validatedData['date'])
            ->where('stadium_id', $stadiumId)
            ->where('time_slot_id', 'LIKE', '%' . $timeSlotData->id . '%')
            ->whereHas('bookingStadium', function ($query) {
                $query->where('booking_status', 'รอการตรวจสอบ');
            })
            ->exists();

        if ($existingOtherUserBooking) {
            $conflictingTimeSlots[] = $timeSlot;  // เก็บช่วงเวลาที่มีการจองซ้ำ
        }

        // ตรวจสอบการจองซ้ำของผู้ใช้คนเดิมที่มีสถานะ 'รอการชำระเงิน'
        $existingUserBooking = BookingDetail::where('booking_date', $validatedData['date'])
            ->where('users_id', auth()->id())
            ->where('time_slot_id', 'LIKE', '%' . $timeSlotData->id . '%')
            ->exists();

        if ($existingUserBooking) {
            return response()->json([
                'success' => false,
                'message' => 'คุณได้ทำรายการนี้ไปแล้ว ไม่สามารถทำซ้ำได้'
            ]);
        }
    }
}

// ถ้ามีช่วงเวลาที่มีการจองซ้ำ แสดงข้อมูลเหล่านั้นในข้อความ
if (!empty($conflictingTimeSlots)) {
    $conflictingTimeSlotsText = implode(', ', $conflictingTimeSlots);
    return response()->json([
        'success' => false,
        'message' => 'มีผู้ใช้ท่านอื่นที่มีสถานะ "รอการตรวจสอบ" ในช่วงเวลา ' . $conflictingTimeSlotsText . ' ดังนั้นไม่สามารถจองซ้ำได้',
        'conflictingTimeSlots' => $conflictingTimeSlots // ส่งข้อมูลที่เกี่ยวข้องกับช่วงเวลาที่มีการจองซ้ำ
    ]);
}

// บันทึกหรืออัปเดตรายละเอียดการจองตามที่ระบุไว้
foreach ($validatedData['timeSlots'] as $stadiumId => $timeSlots) {
    $stadium = Stadium::find($stadiumId);
    if (!$stadium) {
        return response()->json(['success' => false, 'message' => 'สนามไม่ถูกต้อง.']);
    }

    $newTimeSlotIds = [];
    foreach ($timeSlots as $timeSlot) {
        $timeSlotData = \DB::table('time_slot')
            ->where('time_slot', $timeSlot)
            ->where('stadium_id', $stadiumId)
            ->first();

        if (!$timeSlotData) {
            return response()->json(['success' => false, 'message' => 'เวลาหรือสนามไม่ถูกต้อง.']);
        }

        $newTimeSlotIds[] = $timeSlotData->id;
    }

    $timeSlotIdsString = implode(',', $newTimeSlotIds);
    $totalHours = count($newTimeSlotIds);

    $existingBookingDetail = BookingDetail::where('stadium_id', $stadiumId)
        ->where('booking_date', $validatedData['date'])
        ->where('booking_stadium_id', $bookingStadiumId)
        ->first();

    if ($existingBookingDetail) {
        $existingTimeSlotIds = explode(',', $existingBookingDetail->time_slot_id);
        $newTimeSlotIdsToAdd = array_diff($newTimeSlotIds, $existingTimeSlotIds);
        $newTotalHours = count($newTimeSlotIdsToAdd);

        $existingBookingDetail->update([
            'booking_total_hour' => $existingBookingDetail->booking_total_hour + $newTotalHours,
            'booking_total_price' => $stadium->stadium_price * ($existingBookingDetail->booking_total_hour + $newTotalHours),
            'time_slot_id' => $existingBookingDetail->time_slot_id . ($newTimeSlotIdsToAdd ? ',' . implode(',', $newTimeSlotIdsToAdd) : ''),
        ]);
    } else {
        BookingDetail::create([
            'stadium_id' => $stadiumId,
            'booking_stadium_id' => $bookingStadiumId,
            'booking_total_hour' => $totalHours,
            'booking_total_price' => $stadium->stadium_price * $totalHours,
            'booking_date' => $validatedData['date'],
            'users_id' => auth()->id(),
            'time_slot_id' => $timeSlotIdsString,
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
            ->where('booking_status', 'รอการชำระเงิน') // Check booking status
            ->latest()
            ->first();
    
        $booking_stadium_id = $latestBookingStadium ? $latestBookingStadium->id : null;
        $borrowingDetails = null;
        $groupedBookingDetails = collect(); // Start as an empty collection if no booking details exist
        $items = null; // Initialize $items as null
    
        if ($booking_stadium_id) {
            // Fetch booking details
            $bookingDetails = BookingDetail::where('booking_stadium_id', $booking_stadium_id)->get();
    
            if ($bookingDetails->isNotEmpty()) {
                $groupedBookingDetails = $bookingDetails->groupBy(function ($item) {
                    return $item->stadium_id . '|' . $item->booking_date;
                })->map(function ($group) use ($latestBookingStadium) {
                    // Retrieve all time slots associated with this booking
                    $allTimeSlots = $group->flatMap(function ($detail) {
                        $timeSlotIds = explode(',', $detail->time_slot_id);
                        return \DB::table('time_slot')->whereIn('id', $timeSlotIds)->pluck('time_slot');
                    })->join(', ');
    
                    $bookingStatus = $latestBookingStadium->booking_status;
    
                    return [
                        'id' => $group->first()->booking_stadium_id,
                        'stadium_id' => $group->first()->stadium_id,
                        'stadium_name' => $group->first()->stadium->stadium_name,
                        'booking_date' => $group->first()->booking_date,
                        'time_slots' => $allTimeSlots,
                        'total_price' => $group->sum('booking_total_price'),
                        'total_hours' => $group->sum('booking_total_hour'),
                        'booking_status' => $bookingStatus,
                    ];
                })->values();
            }
    
            // Retrieve borrowing details
            $borrowingDetails = Borrow::where('booking_stadium_id', $booking_stadium_id)->get();
    
            // Fetch available items for borrowing
            $items = Item::all();
    
            return view('bookingDetail', compact('groupedBookingDetails', 'bookingDetails', 'borrowingDetails', 'booking_stadium_id', 'items'));
        } else {
            $message = 'คุณยังไม่มีรายการจอง';
            return view('bookingDetail', compact('message', 'booking_stadium_id'));
        }
    }
    


    // use Illuminate\Support\Facades\Log;

    public function destroy($id)
    {
        Log::info('Deleting booking with ID: ' . $id);
    
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


public function showHistoryDetail($booking_stadium_id)
{
    $userId = auth()->id();
    $user = auth()->user();

    // ตรวจสอบบทบาทผู้ใช้และดึงข้อมูลการจองสนาม
    if ($user->is_admin == 1) {
        $bookingStadium = BookingStadium::with('stadium')
            ->where('id', $booking_stadium_id)
            ->first();
    } else {
        $bookingStadium = BookingStadium::with('stadium')
            ->where('id', $booking_stadium_id)
            ->where('users_id', $userId)
            ->first();
    }

    if (!$bookingStadium) {
        return redirect()->route('history.booking')->with('error', 'ไม่พบข้อมูลการจอง');
    }

    // ดึงข้อมูล bookingDetails และแยก time_slot_id ที่เป็นคอมม่าออกมา
    $bookingDetails = BookingDetail::where('booking_stadium_id', $booking_stadium_id)->get();
    $groupedBookingDetails = $bookingDetails->map(function ($detail) {
        $timeSlotIds = explode(',', $detail->time_slot_id);  // แยก time_slot_id ที่เก็บเป็นคอมม่าแยก
        $timeSlots = \DB::table('time_slot')->whereIn('id', $timeSlotIds)->pluck('time_slot')->toArray();
        $detail->time_slots = implode(', ', $timeSlots);  // เก็บข้อมูล time_slot เป็น string
        return $detail;
    })->groupBy('stadium_id'); // สามารถจัดกลุ่มตาม stadium_id ได้

    // ดึงรายละเอียดการยืม
    $borrowingDetails = Borrow::where('booking_stadium_id', $booking_stadium_id)->get();

    // ดึงข้อมูลอุปกรณ์ที่สามารถยืมได้
    $items = Item::all();

    return view('history-detail', compact('bookingStadium', 'bookingDetails', 'borrowingDetails', 'items', 'groupedBookingDetails'));
}

public function confirm($id)
{
    // ค้นหาการจองด้วย ID ที่ระบุ
    $booking = BookingStadium::findOrFail($id);
    
    // เปลี่ยนสถานะการจอง
    $booking->booking_status = 'ชำระเงินแล้ว';
    $booking->save();

   // เปลี่ยนสถานะการยืมที่เชื่อมโยง
   foreach ($booking->borrow as $borrowing) {
    // เปลี่ยนสถานะ borrow_detail ที่เชื่อมโยงกับ borrow และ booking_stadium นี้
    foreach ($borrowing->details as $detail) {
        // ตรวจสอบว่ารายละเอียดการยืมนี้เชื่อมโยงกับ booking_stadium นี้
        if ($detail->borrow->booking_stadium_id == $booking->id) {
            $detail->return_status = 'รอยืม';
            $detail->save();
        }
    }
}
    return redirect()->back()->with('success', 'ยืนยันการชำระเงินเรียบร้อยแล้ว');
}

public function reject(Request $request, $id)
{
    $booking = BookingStadium::findOrFail($id);
    $booking->booking_status = 'การชำระเงินถูกปฏิเสธ';
    $booking->reject_reason = $request->input('reject_reason');
    $booking->save();

    // ค้นหารายการยืมที่เกี่ยวข้อง
    $borrows = Borrow::where('booking_stadium_id', $id)->get();
    foreach ($borrows as $borrow) {
        // อัปเดตสถานะของรายการยืมเป็น 'การชำระเงินถูกปฏิเสธ'
        $borrow->borrow_status = 'การชำระเงินถูกปฏิเสธ';
        $borrow->save();

        // ค้นหารายละเอียดการยืม
        $borrowDetails = BorrowDetail::where('borrow_id', $borrow->id)->get();
        foreach ($borrowDetails as $detail) {
            // เพิ่มจำนวนที่ยืมกลับไปยังรายการอุปกรณ์
            $item = Item::findOrFail($detail->item_id);
            $item->item_quantity += $detail->borrow_quantity; // บวกจำนวนที่ยืมกลับ
            $item->save();
        }
    }

    return redirect()->back()->with('success', 'การปฏิเสธการชำระเงินสำเร็จแล้ว');
}


public function showBookingForm(Request $request)
{
    // กำหนดวันที่จาก URL หรือใช้วันที่ปัจจุบัน
    $date = $request->get('date', \Carbon\Carbon::now()->format('Y-m-d'));

    // ดึงข้อมูลสนามที่มีเวลาและรายละเอียด
    $stadiums = Stadium::with('timeSlots')->get();

    // ดึงข้อมูลการจองที่มีการจองในวันที่กำหนด
    $bookingDetails = BookingDetail::where('booking_date', $request->get('date'))
    ->with('bookingStadium') // ดึงข้อมูลที่เกี่ยวข้องกับสถานะการจอง
    ->get();

    // ส่งข้อมูลไปยัง View
    return view('booking', compact('stadiums', 'date', 'bookingDetails'));  // ส่งค่า bookingDetails ไปด้วย
}



public function historyShowbooking(Request $request)
{
    $query = BookingStadium::query();

    // Filter by booking_stadium_id
    if ($request->has('booking_stadium_id') && $request->booking_stadium_id != '') {
        $query->where('id', $request->booking_stadium_id);
    }

   // Filter by payer_name (จาก payment_booking)
   if ($request->has('fname') && $request->fname != '') {
    $query->whereHas('payment', function ($q) use ($request) {
        $q->where('payer_name', 'like', '%' . $request->fname . '%');
    });
}
    // Filter by borrow_date
    if ($request->has('borrow_date') && $request->borrow_date != '') {
        $query->whereDate('created_at', $request->borrow_date);
    }

      // Filter by status (optional)
      if ($request->has('status') && $request->status != '') {
        $query->where('booking_status', $request->status);
    }

    // Exclude the status "รอการชำระเงิน"
    $query->where('booking_status', '!=', 'รอการชำระเงิน');

    

    // Get filtered bookings
    $bookings = $query->get();

    return view('history-booking', compact('bookings'));
}






}








