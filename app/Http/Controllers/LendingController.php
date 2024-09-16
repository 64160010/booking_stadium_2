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
    public function index()
    {
        // ดึงข้อมูลอุปกรณ์พร้อมกับประเภท
        $items = Item::with('itemType')->get();

         // ดึงข้อมูลประเภทอุปกรณ์ทั้งหมด
        $itemTypes = ItemType::all();

        // ส่งข้อมูลไปยัง view
        return view('lending.index', compact('items', 'itemTypes'));
    }

    public function addItem()
    {
        // ดึงข้อมูลประเภทอุปกรณ์ทั้งหมด
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
    
        $itemType = ItemType::find($request->item_type_id);
        $lastItem = Item::where('item_code', 'LIKE', $itemType->type_code . '%')
            ->orderBy('item_code', 'desc')
            ->first();
    
        $newCode = $itemType->type_code . str_pad(($lastItem ? intval(substr($lastItem->item_code, 2)) + 1 : 1), 3, '0', STR_PAD_LEFT);
    
        // เก็บรูปภาพ
        $imageName = null;
        if ($request->hasFile('item_picture')) {
            $imageName = time() . '.' . $request->item_picture->extension();
            $request->item_picture->storeAs('public/images', $imageName);
        }
    
        // บันทึกข้อมูลลงฐานข้อมูล
        Item::create([
            'item_code' => $newCode,
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
            // 'item_code' => 'required|string|max:255', // ไม่จำเป็นถ้าไม่ต้องการให้แก้ไข
            'item_name' => 'required|string|max:255',
            'item_picture' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'item_type_id' => 'required|exists:item_type,id', // เปลี่ยนจาก 'item_type' เป็น 'item_type_id'
            'price' => 'required|numeric',
            'item_quantity' => 'required|integer|min:0',
        ]);
    
        $item = Item::findOrFail($id);
        $item->item_name = $request->input('item_name');
    
        if ($request->hasFile('item_picture')) {
            // ลบรูปภาพเก่าถ้ามี
            if ($item->item_picture && Storage::exists('public/images/' . $item->item_picture)) {
                Storage::delete('public/images/' . $item->item_picture);
            }
    
            $imagePath = $request->file('item_picture')->store('images', 'public');
            $item->item_picture = basename($imagePath);
        }
    
        $item->item_type_id = $request->input('item_type_id'); // เปลี่ยนจาก 'item_type' เป็น 'item_type_id'
        $item->price = $request->input('price');
        $item->item_quantity = $request->input('item_quantity');
        $item->save();
    
        return redirect()->route('lending.index')->with('success', 'อัพเดตอุปกรณ์สำเร็จ!');
    }
    


    public function destroy($id)
    {
        $item = Item::findOrFail($id);
        
        // ลบรูปภาพถ้ามี
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
        $stadiums = Stadium::with('timeSlots')->get(); // ดึงข้อมูลสนามพร้อมช่วงเวลา

        return view('lending.borrow-item', compact('item', 'stadiums'));
    }

    public function storeBorrow(Request $request)
{
    $request->validate([
        'borrow_date' => 'required|date',
        'time_slots' => 'required|array', // ใช้เป็น array สำหรับเลือกหลายช่วงเวลา
        'time_slots.*' => 'exists:time_slots,id', // ตรวจสอบว่าแต่ละช่วงเวลามีอยู่ในตาราง time_slots
        'borrow_quantity' => 'required|integer|min:1',
        'stadium_id' => 'required|exists:stadiums,id',
    ]);

    $borrow = Borrow::create([
        'borrow_date' => $request->borrow_date,
        'borrow_quantity' => $request->borrow_quantity,
        'stadium_id' => $request->stadium_id,
        'item_id' => $request->item_id,
    ]);

    // บันทึกหลายช่วงเวลาที่เลือก
    $borrow->timeSlots()->attach($request->time_slots);

    return redirect()->route('lending.index')->with('success', 'การยืมอุปกรณ์สำเร็จ!');
}

}