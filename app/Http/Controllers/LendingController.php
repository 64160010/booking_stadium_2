<?php

namespace App\Http\Controllers;

use App\Models\Stadium;
use App\Models\Item;
use App\Models\ItemType;
use App\Models\Borrow;
use App\Models\TimeSlot;
use App\Models\BorrowDetail; 
use App\Models\BookingDetail; 
use App\Models\BookingStadium; 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB; // เพิ่มบรรทัดนี้
use Illuminate\Support\Facades\Auth;

class LendingController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth')->only(['storeBorrow', 'borrowItem']); // ต้องเข้าสู่ระบบก่อน
    }

    // แสดงรายการอุปกรณ์
    public function index(Request $request)
{
    $search = $request->get('search');
    $itemTypeId = $request->get('item_type_id');

    $query = Item::query();

    if ($search) {
        $query->where('item_name', 'like', '%' . $search . '%');
    }

    if ($itemTypeId) {
        $query->where('item_type_id', $itemTypeId);
    }

    $items = $query->paginate(10); // Limit to 10 items per page

    $itemTypes = ItemType::all(); // ดึงประเภทอุปกรณ์

    $bookingDate = '2024-10-11'; // ตัวอย่างวันที่
    $bookingTime = '11:00'; // ตัวอย่างเวลา
    $stadiumId = 1; // ตัวอย่าง ID สนาม

    return view('lending.borrow-equipment', compact('items', 'itemTypes', 'bookingDate', 'bookingTime', 'stadiumId'));

}


    


    // แสดงฟอร์มเพิ่มอุปกรณ์
    public function addItem()
    {
        $itemTypes = ItemType::all();
        return view('lending.add-item', compact('itemTypes'));
    }

    // เก็บข้อมูลอุปกรณ์ใหม่
    public function storeItem(Request $request)
    {
        $request->validate([
            'item_name' => 'required|string|max:45',
            'item_picture' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'item_type_id' => 'required|exists:item_type,id',
            'price' => 'required|integer',
            'item_quantity' => 'required|integer',
        ]);
    
        $itemType = ItemType::find($request->item_type_id);
        $lastItem = Item::where('item_code', 'LIKE', $itemType->type_code . '%')
            ->orderBy('item_code', 'desc')
            ->first();
    
        $lastCodeNumber = $lastItem ? intval(substr($lastItem->item_code, 2)) + 1 : 1;
        $newItemCode = $itemType->type_code . str_pad($lastCodeNumber, 3, '0', STR_PAD_LEFT);
    
        $imageName = null;
        if ($request->hasFile('item_picture')) {
            $imageName = time() . '.' . $request->item_picture->extension();
            $request->item_picture->storeAs('public/images', $imageName);
        }
    
        Item::create([
            'item_code' => $newItemCode,
            'item_name' => $request->item_name,
            'item_picture' => $imageName,
            'item_type_id' => $request->item_type_id,
            'price' => $request->price,
            'item_quantity' => $request->item_quantity,
        ]);
    
        return redirect()->route('lending.index')->with('success', 'เพิ่มอุปกรณ์สำเร็จ!');
    }

    // แสดงฟอร์มแก้ไขอุปกรณ์
    public function edit($id)
    {
        $item = Item::findOrFail($id);
        $itemTypes = ItemType::all();
        return view('lending.edit-item', compact('item', 'itemTypes'));
    }

    // อัปเดตอุปกรณ์
    public function update(Request $request, $id)
    {
        $request->validate([
            'item_name' => 'required|string|max:255',
            'item_picture' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'item_type_id' => 'required|exists:item_type,id',
            'price' => 'required|numeric',
            'item_quantity' => 'required|integer|min:0',
        ]);

        $item = Item::findOrFail($id);
        $item->item_name = $request->input('item_name');

        if ($request->hasFile('item_picture')) {
            if ($item->item_picture && Storage::exists('public/images/' . $item->item_picture)) {
                Storage::delete('public/images/' . $item->item_picture);
            }
            $imagePath = $request->file('item_picture')->store('images', 'public');
            $item->item_picture = basename($imagePath);
        }

        $item->item_type_id = $request->input('item_type_id');
        $item->price = $request->input('price');
        $item->item_quantity = $request->input('item_quantity');
        $item->save();

        return redirect()->route('lending.index')->with('success', 'อัพเดตอุปกรณ์สำเร็จ!');
    }

    // ลบอุปกรณ์
    public function destroy($id)
    {
        $item = Item::findOrFail($id);

        if ($item->item_picture && Storage::exists('public/images/' . $item->item_picture)) {
            Storage::delete('public/images/' . $item->item_picture);
        }

        $item->delete();
        return redirect()->route('lending.index')->with('success', 'ลบอุปกรณ์สำเร็จ!');
    }


    public function borrowItem(Request $request)
{
    // ตรวจสอบข้อมูล
    $request->validate([
        'stadium_id' => 'required|exists:stadium,id',
        'booking_date' => 'required|date',
        'time_slots' => 'required',
        'item_id' => 'required|array',
        'item_id.*' => 'exists:item,id',
        'borrow_quantity' => 'required|array',
        'borrow_quantity.*' => 'integer|min:0',
    ]);

    // ดึงข้อมูลการจองจาก booking_stadium โดยใช้ stadium_id, booking_date, time_slots
    $bookingStadium = BookingStadium::where('booking_date', $request->booking_date)
        ->where('users_id', auth()->id()) // สมมุติว่าผู้ใช้ที่ล็อกอินต้องเป็นเจ้าของการจอง
        ->first();

    if (!$bookingStadium) {
        return redirect()->back()->withErrors('การจองสนามไม่พบ');
    }

// ดึงข้อมูลจาก booking_detail ที่เกี่ยวข้อง โดยใช้ booking_stadium_id และ booking_date
$bookingDetail = BookingDetail::where('booking_stadium_id', $bookingStadium->id)
    ->where('booking_date', $bookingStadium->booking_date)
    ->first();

if (!$bookingDetail) {
    return redirect()->back()->withErrors('ข้อมูลการจองไม่พบ');
}

// ใช้ time_slot_id จาก booking_detail
$timeSlotId = $bookingDetail->time_slot_id;

// ตรวจสอบว่า time_slot_id ที่ได้มาจาก booking_detail นั้นถูกต้อง
$timeSlot = TimeSlot::find($timeSlotId);

if (!$timeSlot) {
    return redirect()->back()->withErrors('Time slot not found.');
}

// คำนวณเวลาการยืม โดยใช้ค่าจาก booking_total_hour
$borrowTotalHour = $bookingDetail->booking_total_hour; // ดึงค่าจาก booking_total_hour

// สร้างการบันทึกในตาราง borrow
$borrow = Borrow::create([
    'borrow_date' => $bookingDetail->booking_date,
    'users_id' => auth()->id(),
    'booking_stadium_id' => $bookingStadium->id, // ใช้ id ของ booking_stadium
]);

// วนลูปสร้างรายการยืมสำหรับแต่ละ item
foreach ($request->item_id as $index => $itemId) {
    $borrowQuantity = $request->borrow_quantity[$index];

    // ข้ามการบันทึกถ้าจำนวนการยืมเป็น 0
    if ($borrowQuantity == 0) {
        continue;
    }

    // ตรวจสอบว่า item มีอยู่หรือไม่
    $item = Item::find($itemId);
    if (!$item) {
        return redirect()->back()->withErrors("Item not found: $itemId.");
    }

    // คำนวณราคายืมรวม
    $totalPrice = $item->price * $borrowQuantity;

    // บันทึกรายการยืมในตาราง borrow_detail
    BorrowDetail::create([
        'stadium_id' => $bookingDetail->stadium_id, // ดึงจาก booking_detail
        'borrow_date' => $bookingDetail->booking_date,
        'time_slot_id' => $timeSlotId, // ดึง time_slot_id จาก booking_detail
        'item_id' => $itemId,
        'borrow_quantity' => $borrowQuantity,
        'borrow_total_price' => $totalPrice,
        'borrow_total_hour' => $borrowTotalHour, // เพิ่มเวลาการยืมที่คำนวณจาก booking_detail
        'item_item_type_id' => $item->item_type_id, // เพิ่มการบันทึก item_item_type_id
        'borrow_id' => $borrow->id, // เชื่อมโยงกับ borrow ที่สร้างขึ้น
        'users_id' => auth()->id(),
    ]);
}
}

    
    
    
    
    
public function showBookingDetail($bookingId)
{
    // ดึงข้อมูลอุปกรณ์ทั้งหมดจากตาราง Item
    $items = Item::all(); // หรือคุณสามารถปรับ Query ตามที่ต้องการ

    $booking = BookingStadium::with('details')->findOrFail($bookingId);
   
    // ส่งตัวแปร $items ไปยัง View 'bookingDetail'
    return view('bookingDetail', compact('items', 'booking'));


}

}