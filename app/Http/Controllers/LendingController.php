<?php

namespace App\Http\Controllers;

use App\Models\Stadium;
use App\Models\Item;
use App\Models\ItemType;
use App\Models\Borrow;
use App\Models\TimeSlot;
use App\Models\BorrowDetail; 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class LendingController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth')->only(['storeBorrow', 'borrowItem']); // ต้องเข้าสู่ระบบก่อน
    }

    // แสดงรายการอุปกรณ์
    public function index()
    {
        $items = Item::with('itemType')->get();
        return view('lending.index', compact('items'));
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

    // แสดงฟอร์มยืมอุปกรณ์
    public function borrowItem($id)
    {
        $item = Item::findOrFail($id);
        $stadiums = Stadium::with('timeSlots')->get();
        $borrow_date = now()->format('Y-m-d'); // กำหนดวันที่ยืมเป็นวันที่ปัจจุบัน
        return view('lending.borrow-item', compact('item', 'stadiums', 'borrow_date'));
    }

    // เก็บข้อมูลการยืม
    public function storeBorrow(Request $request)
    {
        $request->validate([
            'borrow_date' => 'required|date',
            'borrow_items' => 'required|array',
            'borrow_items.*.item_id' => 'required|exists:item,id',
            'borrow_items.*.borrow_quantity' => 'required|integer|min:1',
            'borrow_items.*.stadium_id' => 'required|exists:stadium,id',
            'borrow_items.*.time_slot_id' => 'required|string',
        ]);
    
        // เริ่มทำการบันทึกใน transaction
        \DB::beginTransaction();
    
        try {
            // บันทึกข้อมูลการยืมลงในตาราง borrow
            $borrow = Borrow::create([
                'users_id' => auth()->user()->id,
                'borrow_date' => $request->borrow_date,
                'borrow_status' => 'รอการชำระเงิน',
            ]);
    
            // ตรวจสอบว่าบันทึกสำเร็จหรือไม่
            if ($borrow) {
                foreach ($request->borrow_items as $borrowItem) {
                    $item = Item::findOrFail($borrowItem['item_id']);
                    $timeSlot = TimeSlot::where('time_slot', $borrowItem['time_slot_id'])->first(); // ค้นหา time_slot ที่ตรงกับเวลา
    
                    if ($timeSlot) {
                        $timeSlotId = $timeSlot->id; // ได้ ID ของ time slot
                    } else {
                        return back()->withErrors(['error' => 'ไม่พบช่วงเวลาที่เลือก']);
                    }
    
                    // คำนวณรายละเอียดการยืม
                    $totalPrice = $item->price * $borrowItem['borrow_quantity']; // คำนวณราคา
    
                    // บันทึกรายละเอียดการยืมลงใน borrow_detail
                    BorrowDetail::create([
                        'borrow_id' => $borrow->id,
                        'item_id' => $item->id,
                        'item_item_type_id' => $item->item_type_id,
                        'borrow_date' => $request->borrow_date,
                        'borrow_quantity' => $borrowItem['borrow_quantity'],
                        'borrow_total_hour' => 1, // หรือค่าที่คุณต้องการ
                        'borrow_total_price' => $totalPrice,
                        'borrow_status' => 'รอการชำระเงิน',
                        'users_id' => auth()->user()->id,
                        'time_slot_id' => $timeSlotId,
                        'stadium_id' => $borrowItem['stadium_id'],
                    ]);
                }
    
                // ยืนยันการทำ transaction
                \DB::commit();
    
                // ส่งกลับไปยังหน้ารายละเอียด
                return redirect()->route('lending.borrow-detail')->with('success', 'ระบบเพิ่มรายการแล้ว โปรดตรวจสอบรายการอีกครั้งก่อนชำระเงิน');
            } else {
                return redirect()->back()->with('error', 'เกิดข้อผิดพลาด');
            }
        } catch (\Exception $e) {
            // ยกเลิก transaction ในกรณีเกิดข้อผิดพลาด
            \DB::rollBack();
            return back()->withErrors(['error' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()]);
        }
    }

    public function borrowDetail()
    {
        // ดึงข้อมูลการยืมทั้งหมด (หรือปรับให้เหมาะสมตามที่คุณต้องการ)
        $borrows = Borrow::with('details.item', 'user', 'details.stadium')->where('users_id', auth()->user()->id)->get(); // ตัวอย่างดึงข้อมูลตามผู้ใช้ที่ล็อกอิน
        return view('lending.borrow-detail', compact('borrows'));
    }

    public function destroyBorrow($id)
    {
        $borrow = Borrow::findOrFail($id);
        $borrow->delete(); // ลบรายการการยืม

        return redirect()->route('lending.borrow-detail')->with('success', 'ลบรายการยืมสำเร็จ!');
    }
}
