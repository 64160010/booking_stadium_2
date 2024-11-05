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
            'timeSlots' => 'required|array'
        ]);
    
        try {
            // Check for an existing booking with 'รอการชำระเงิน' status for this user
            $existingBooking = BookingStadium::where('users_id', auth()->id())
                ->where('booking_status', 'รอการชำระเงิน')
                ->first();
    
            // Use existing booking ID if found, otherwise create a new one
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
    
            // Check for any existing time slot conflicts across all selected stadiums on the same date
            foreach ($validatedData['timeSlots'] as $stadiumId => $timeSlots) {
                foreach ($timeSlots as $timeSlot) {
                    $timeSlotData = \DB::table('time_slot')
                        ->where('time_slot', $timeSlot)
                        ->where('stadium_id', $stadiumId)
                        ->first();
    
                    if (!$timeSlotData) {
                        return response()->json(['success' => false, 'message' => 'เวลาหรือสนามไม่ถูกต้อง.']);
                    }
    
                    // Check if the user has any existing bookings for the same date and time slot across all stadiums
                    $existingUserBooking = BookingDetail::where('booking_date', $validatedData['date'])
                        ->where('users_id', auth()->id())
                        ->where('time_slot_id', 'LIKE', '%' . $timeSlotData->id . '%')
                        ->exists();
    
                    if ($existingUserBooking) {
                        return response()->json([
                            'success' => false,
                            'message' => 'คุณได้ทำการจองช่วงเวลานี้ไปแล้วในวันเดียวกัน ไม่สามารถจองสนามอื่นในวันเดียวกันได้'
                        ]);
                    }
                }
            }
    
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
    
                // Convert array of time slot IDs to a string
                $timeSlotIdsString = implode(',', $newTimeSlotIds);
                $totalHours = count($newTimeSlotIds);
    
                // Retrieve existing booking detail
                $existingBookingDetail = BookingDetail::where('stadium_id', $stadiumId)
                    ->where('booking_date', $validatedData['date'])
                    ->where('booking_stadium_id', $bookingStadiumId)
                    ->first();
    
                if ($existingBookingDetail) {
                    // Check for new time slots that haven't been added yet
                    $existingTimeSlotIds = explode(',', $existingBookingDetail->time_slot_id);
                    $newTimeSlotIdsToAdd = array_diff($newTimeSlotIds, $existingTimeSlotIds);
                    $newTotalHours = count($newTimeSlotIdsToAdd);
    
                    // Update existing booking detail
                    $existingBookingDetail->update([
                        'booking_total_hour' => $existingBookingDetail->booking_total_hour + $newTotalHours,
                        'booking_total_price' => $stadium->stadium_price * ($existingBookingDetail->booking_total_hour + $newTotalHours),
                        'time_slot_id' => $existingBookingDetail->time_slot_id . ($newTimeSlotIdsToAdd ? ',' . implode(',', $newTimeSlotIdsToAdd) : ''),
                    ]);
                } else {
                    // Create a new booking detail entry
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

        return redirect()->back()->with('success', 'การปฏิเสธการชำระเงินสำเร็จแล้ว');
    }






}