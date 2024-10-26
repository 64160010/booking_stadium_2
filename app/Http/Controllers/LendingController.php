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

    return view('lending.index', compact('items', 'itemTypes', 'bookingDate', 'bookingTime', 'stadiumId'));

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
        $request->validate([
            'stadium_id' => 'required|exists:stadium,id',
            'booking_date' => 'required|date',
            'item_id' => 'required|array',
            'item_id.*' => 'exists:item,id',
            'borrow_quantity' => 'required|array',
            'borrow_quantity.*' => 'integer|min:0',
        ]);
    
        $bookingStadium = BookingStadium::where('users_id', auth()->id())
            ->where('booking_status', 'รอการชำระเงิน')
            ->latest()
            ->first();
    
        if (!$bookingStadium) {
            return redirect()->back()->withErrors('การจองสนามไม่พบ');
        }
    
        $borrow = Borrow::firstOrCreate(
            [
                'users_id' => auth()->id(),
                'booking_stadium_id' => $bookingStadium->id,
                'borrow_date' => $request->booking_date,
                'borrow_status' => 'รอการชำระเงิน',
            ]
        );
    
        $bookingDetails = BookingDetail::where('booking_stadium_id', $bookingStadium->id)
            ->where('booking_date', $request->booking_date)
            ->get();
    
        foreach ($request->item_id as $index => $itemId) {
            $borrowQuantity = $request->borrow_quantity[$index];
            
            if ($borrowQuantity == 0) {
                continue;
            }
    
            $item = Item::find($itemId);
            if (!$item) {
                return redirect()->back()->withErrors("Item not found: $itemId.");
            }
    
            foreach ($bookingDetails as $bookingDetail) {
                if ($bookingDetail->stadium_id == $request->stadium_id) {
                    $timeSlotId = $bookingDetail->time_slot_id;
                    $totalPrice = $item->price * $borrowQuantity;
    
                    $existingDetail = BorrowDetail::where('borrow_id', $borrow->id)
                        ->where('item_id', $itemId)
                        ->where('stadium_id', $bookingDetail->stadium_id)
                        ->where('borrow_date', $bookingDetail->booking_date)
                        ->where('time_slot_id', $timeSlotId)
                        ->first();
    
                    if ($existingDetail) {
                        $existingDetail->borrow_quantity += $borrowQuantity;
                        $existingDetail->borrow_total_price += $totalPrice;
                        $existingDetail->save();
                    } else {
                        BorrowDetail::create([
                            'stadium_id' => $bookingDetail->stadium_id,
                            'borrow_date' => $bookingDetail->booking_date,
                            'time_slot_id' => $timeSlotId,
                            'item_id' => $itemId,
                            'borrow_quantity' => $borrowQuantity,
                            'borrow_total_price' => $totalPrice,
                            'borrow_total_hour' => 0,
                            'item_item_type_id' => $item->item_type_id,
                            'borrow_id' => $borrow->id,
                            'users_id' => auth()->id(),
                        ]);
                    }
                }
            }
        }
    
        return redirect()->back()->with('success', 'ยืมอุปกรณ์สำเร็จ');
    }
    


    
public function showBookingDetail($id)
{
    // ค้นหาข้อมูลการจอง
    $bookingDetail = Booking::find($id);
     // ค้นหาข้อมูลการยืมอุปกรณ์ที่เกี่ยวข้อง
    $borrowingDetails = Borrow::where('booking_id', $id)->get();

    return view('bookingDetail', compact('bookingDetail', 'borrowingDetails'));
}

public function destroyBorrow($id)
{
    // ค้นหาการยืมตาม ID
    $borrow = Borrow::findOrFail($id);
    
    // ลบรายละเอียดการยืมที่เกี่ยวข้อง
    $borrowDetails = BorrowDetail::where('borrow_id', $borrow->id);
    $borrowDetails->delete();

    // ลบการยืม
    $borrow->delete();

    return response()->json(['success' => 'ลบการยืมสำเร็จ']);
}



}