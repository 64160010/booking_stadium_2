<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index(Request $request) {
        $query = User::query();
    
        // ฟังก์ชันค้นหา
        if ($request->filled('search')) {
            $search = $request->search;
    
            // ค้นหาทั้งชื่อ, นามสกุล, เบอร์โทร
            $query->where(function($q) use ($search) {
                $q->where('fname', 'like', '%' . $search . '%')
                  ->orWhere('lname', 'like', '%' . $search . '%')
                  ->orWhere('phone', 'like', '%' . $search . '%');
            });
        }

    // คำนวณจำนวนผู้ใช้ทั้งหมด
       
    
        // Paginate the results
        $users = $query->paginate(10); // ดึงผู้ใช้ 10 คนต่อหน้า
        return view('users.index', compact('users'));
    }
    
    
    public function create() {
        return view('users.create');
    }

    // บันทึกข้อมูลสมาชิกใหม่
    public function store(Request $request) {
        // ตรวจสอบข้อมูลที่ซ้ำกันในตาราง users
        $request->validate([
            'fname' => 'required',
            'lname' => 'required',
            'email' => 'required|email|unique:users,email', // ตรวจสอบอีเมลซ้ำ
            'phone' => 'required|unique:users,phone|regex:/^[0-9]{10}$/', // ตรวจสอบเบอร์โทรซ้ำและต้องมี 10 ตัวอักษรเท่านั้น
            'password' => 'required|min:8|confirmed',
            'is_admin' => 'boolean',
        ], [
            'email.unique' => 'อีเมลนี้ถูกใช้ไปแล้ว กรุณาใช้อีเมลอื่น', // ข้อความแจ้งเตือนสำหรับอีเมลซ้ำ
            'phone.unique' => 'เบอร์โทรนี้ถูกใช้ไปแล้ว กรุณาใช้เบอร์โทรอื่น', // ข้อความแจ้งเตือนสำหรับเบอร์โทรซ้ำ
            'phone.regex' => 'กรุณากรอกเบอร์โทรให้ถูกต้อง (10 หลัก และเป็นตัวเลขเท่านั้น)', // ข้อความแจ้งเตือนสำหรับเบอร์โทรไม่ถูกต้อง
        ]);
    
        // ตรวจสอบความซ้ำซ้อนของ ชื่อ และ นามสกุล
        $duplicateUser = User::where('fname', $request->fname)
                            ->where('lname', $request->lname)
                            ->first();
    
        if ($duplicateUser) {
            return redirect()->back()->withErrors([
                'duplicate' => 'ชื่อและนามสกุลนี้ถูกใช้ไปแล้ว กรุณากรอกข้อมูลใหม่'
            ])->withInput();
        }
    
        // บันทึกข้อมูล
        User::create([
            'fname' => $request->fname,
            'lname' => $request->lname,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
            'is_admin' => $request->is_admin,
        ]);
    
        return redirect()->route('users.index')->with('success', 'สมาชิกถูกเพิ่มเรียบร้อยแล้ว');
    }
    

    public function edit(User $user) {
        return view('users.edit', compact('user'));
    }

   

       // อัปเดตข้อมูลสมาชิก
       public function update(Request $request, User $user) {
        $request->validate([
            'fname' => 'required',
            'lname' => 'required',
            'email' => 'required|email|unique:users,email,' . $user->id, // ตรวจสอบความซ้ำซ้อนของอีเมล
            'phone' => 'required|unique:users,phone,' . $user->id . '|regex:/^[0-9]{10}$/', // ตรวจสอบความซ้ำซ้อนของเบอร์โทรและต้องมี 10 ตัว
            'password' => 'nullable|min:8', // ถ้าไม่มีการเปลี่ยนรหัสผ่าน ไม่ต้องกรอก
            'is_admin' => 'boolean',
        ], [
            'phone.regex' => 'กรุณากรอกเบอร์โทรให้ถูกต้อง (10 หลัก และเป็นตัวเลขเท่านั้น)', // ข้อความแจ้งเตือนสำหรับเบอร์โทรไม่ถูกต้อง
        ]);
    
        // อัปเดตข้อมูลผู้ใช้
        $user->fname = $request->fname;
        $user->lname = $request->lname;
        $user->email = $request->email;
        $user->phone = $request->phone;
        if ($request->password) {
            $user->password = Hash::make($request->password); // อัปเดตรหัสผ่านถ้ามีการเปลี่ยน
        }
        $user->is_admin = $request->is_admin;
    
        $user->save();
    
        return redirect()->route('users.index')->with('success', 'ข้อมูลสมาชิกถูกแก้ไขเรียบร้อยแล้ว');
    }

    // ลบสมาชิก
    public function destroy(User $user) {
        $user->delete();
        return redirect()->route('users.index')->with('success', 'สมาชิกถูกลบเรียบร้อยแล้ว');
    }

    
}