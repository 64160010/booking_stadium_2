<?php

namespace App\Http\Controllers;

use App\Models\Stadium;
use App\Models\Item;
use App\Models\ItemType;
use App\Models\Borrow;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class LendingController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth')->only(['storeBorrow', 'borrowItem']); // ต้องเข้าสู่ระบบก่อน
    }

    public function index()
    {
        $items = Item::with('itemType')->get();
        return view('lending.index', compact('items'));
    }

     public function addItem()
    {
        $itemTypes = ItemType::all();
        return view('lending.add-item', compact('itemTypes'));
    }

    public function storeItem(Request $request)
    {
        $request->validate([
            'item_name' => 'required|string|max:45',
            'item_picture' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'item_type_id' => 'required|exists:item_type,id',
            'price' => 'required|integer',
            'item_quantity' => 'required|integer',
        ]);
    
        // ค้นหา item type จาก item_type_id
        $itemType = ItemType::find($request->item_type_id);
        
        // ดึงข้อมูลล่าสุดของ item ที่มีรหัสเริ่มต้นด้วย type_code (FB, SF, SH)
        $lastItem = Item::where('item_code', 'LIKE', $itemType->type_code . '%')
            ->orderBy('item_code', 'desc')
            ->first();
    
        // สร้างรหัสอุปกรณ์ใหม่
        if ($lastItem) {
            // ดึงเฉพาะเลขจาก item_code และบวกเพิ่ม 1
            $lastCodeNumber = intval(substr($lastItem->item_code, 2)) + 1;
        } else {
            // ถ้าไม่มีอุปกรณ์ในประเภทนั้นๆ ให้เริ่มที่ 1
            $lastCodeNumber = 1;
        }
        
        // สร้างรหัสอุปกรณ์ใหม่ เช่น FB001, SF002
        $newItemCode = $itemType->type_code . str_pad($lastCodeNumber, 3, '0', STR_PAD_LEFT);
    
        // เก็บรูปภาพ
        $imageName = null;
        if ($request->hasFile('item_picture')) {
            $imageName = time() . '.' . $request->item_picture->extension();
            $request->item_picture->storeAs('public/images', $imageName);
        }
    
        // บันทึกข้อมูล
        Item::create([
            'item_code' => $newItemCode,  // ใช้รหัสอุปกรณ์ที่สร้างขึ้นใหม่
            'item_name' => $request->item_name,
            'item_picture' => $imageName,
            'item_type_id' => $request->item_type_id,
            'price' => $request->price,
            'item_quantity' => $request->item_quantity,
        ]);
    
        return redirect()->route('lending.index')->with('success', 'เพิ่มอุปกรณ์สำเร็จ!');
    }
    

    public function edit($id)
    {
        $item = Item::findOrFail($id);
        $itemTypes = ItemType::all();
        return view('lending.edit-item', compact('item', 'itemTypes'));
    }

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


    public function destroy($id)
    {
        $item = Item::findOrFail($id);

        if ($item->item_picture && Storage::exists('public/images/' . $item->item_picture)) {
            Storage::delete('public/images/' . $item->item_picture);
        }

        $item->delete();
        return redirect()->route('lending.index')->with('success', 'ลบอุปกรณ์สำเร็จ!');
    }

    // จัดการการยืมอุปกรณ์
    public function borrowItem($id)
    {
        $item = Item::findOrFail($id);
        $stadiums = Stadium::with('timeSlots')->get();
        return view('lending.borrow-item', compact('item', 'stadiums'));
    }


    public function storeBorrow(Request $request)
    {
        // ตรวจสอบข้อมูลที่ส่งมาจากฟอร์ม
        $request->validate([
            'item_id' => 'required|exists:item,id',
            'borrow_date' => 'required|date',
            'borrow_quantity' => 'required|integer|min:1',
            'stadium_id' => 'required|exists:stadium,id',
            'borrow_price' => 'required|numeric',
            'borrow_total_price' => 'required|numeric',
            'time_slot_id' => 'required|exists:time_slot,id',
            
        ]);
    
        // ดึงข้อมูลอุปกรณ์จากฐานข้อมูล
        $item = Item::findOrFail($request->item_id);

    
        // คำนวณราคารวม
        $pricePerUnit = $item->price;
        $quantity = $request->borrow_quantity;
        $timeSlotsCount = count(explode(',', $request->time_slot_id));
        $totalPrice = $pricePerUnit * $quantity * $timeSlotsCount;
    
        // เก็บข้อมูลการยืมลงในตาราง borrow
        Borrow::create([
            'user_id' => auth()->user()->id,
            'item_id' => $request->item_id,
            'borrow_date' => $request->borrow_date,
            'borrow_quantity' => $request->borrow_quantity,
            'stadium_id' => $request->stadium_id,
            'time_slot_id' => $request->time_slot_id,  // บันทึก time_slot_id
            'total_price' => $totalPrice,
            'borrow_price' => $request->input('borrow_price'),
        ]);
    
        // รีเฟรชหน้าและแสดงข้อความสำเร็จ
        return redirect()->route('lending.index')->with('success', 'การยืมอุปกรณ์สำเร็จ!');
    }
}