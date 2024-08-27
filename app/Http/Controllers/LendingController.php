<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\ItemType; // ต้อง import Model สำหรับ item_type
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class LendingController extends Controller
{
    public function index()
    {
        // ดึงข้อมูลจากฐานข้อมูล
        $items = Item::with('itemType')->get(); // ใช้ with เพื่อดึงข้อมูลประเภท

        // ส่งข้อมูลไปยัง view
        return view('lending.index', compact('items'));
    }

    public function addItem()
    {
        $itemTypes = ItemType::all(); // ดึงรายการประเภทอุปกรณ์
        return view('lending.add-item', compact('itemTypes'));
    }

    public function storeItem(Request $request)
    {
        $request->validate([
            'item_code' => 'required|string|max:10',
            'item_name' => 'required|string|max:45',
            'item_picture' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'item_type_id' => 'required|exists:item_type,id',
            'price' => 'required|integer',
            'item_quantity' => 'required|integer',
        ]);

        // เก็บรูปภาพ
        if ($request->hasFile('item_picture')) {
            $imageName = time().'.'.$request->item_picture->extension();
            $request->item_picture->storeAs('public/images', $imageName);
        }

        // บันทึกข้อมูลลงฐานข้อมูล
        Item::create([
            'item_code' => $request->item_code,
            'item_name' => $request->item_name,
            'item_picture' => $imageName,
            'item_type_id' => $request->item_type_id, // ใช้ item_type_id
            'price' => $request->price,
            'item_quantity' => $request->item_quantity,
        ]);

        return redirect()->route('lending.index')->with('success', 'เพิ่มอุปกรณ์สำเร็จ!');
    }
    public function edit($id)
    {
        $item = Item::findOrFail($id);
        $itemTypes = ItemType::all(); // ดึงข้อมูลประเภทอุปกรณ์ทั้งหมด

        return view('lending.edit-item', compact('item', 'itemTypes'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'item_code' => 'required|string|max:255',
            'item_name' => 'required|string|max:255',
            'item_picture' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'item_type' => 'required|exists:item_type,id',
            'price' => 'required|numeric',
            'item_quantity' => 'required|integer|min:0',
        ]);

        $item = Item::findOrFail($id);
        $item->item_code = $request->input('item_code');
        $item->item_name = $request->input('item_name');

        if ($request->hasFile('item_picture')) {
            $imagePath = $request->file('item_picture')->store('images', 'public');
            $item->item_picture = basename($imagePath);
        }

        $item->item_type_id = $request->input('item_type');
        $item->price = $request->input('price');
        $item->item_quantity = $request->input('item_quantity');
        $item->save();

        return redirect()->route('lending.index')->with('success', 'Item updated successfully!');
    }

    public function destroy($id)
{
    $item = Item::findOrFail($id);
    
    // ลบไฟล์รูปภาพจาก storage (ถ้าจำเป็น)
    if ($item->item_picture && Storage::exists('public/images/' . $item->item_picture)) {
        Storage::delete('public/images/' . $item->item_picture);
    }
    
    // ลบข้อมูลจากฐานข้อมูล
    $item->delete();
    
    return redirect()->route('lending.index')->with('success', 'ลบรายการสำเร็จแล้ว');
}

}