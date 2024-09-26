<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index() {
        $users = User::all();
        return view('users.index', compact('users'));
    }

    public function create() {
        return view('users.create');
    }

    public function store(Request $request) {
        // ตรวจสอบข้อมูลที่ซ้ำกันในตาราง users
        $request->validate([
            'fname' => 'required',
            'lname' => 'required',
            'email' => 'required|email|unique:users,email', // ตรวจสอบอีเมลซ้ำ
            'phone' => 'required|unique:users,phone', // ตรวจสอบเบอร์โทรซ้ำ
            'password' => 'required|min:6|confirmed',
            'is_admin' => 'boolean',
        ], [
            'email.unique' => 'อีเมลนี้ถูกใช้ไปแล้ว กรุณาใช้อีเมลอื่น', // ข้อความแจ้งเตือนสำหรับอีเมลซ้ำ
            'phone.unique' => 'เบอร์โทรนี้ถูกใช้ไปแล้ว กรุณาใช้เบอร์โทรอื่น', // ข้อความแจ้งเตือนสำหรับเบอร์โทรซ้ำ
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

    public function update(Request $request, User $user) {
        $request->validate([
            'fname' => 'required',
            'lname' => 'required',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'phone' => 'required',
            'password' => 'nullable|min:6',
            'is_admin' => 'boolean',
        ]);

        $user->fname = $request->fname;
        $user->lname = $request->lname;
        $user->email = $request->email;
        $user->phone = $request->phone;
        if ($request->password) {
            $user->password = Hash::make($request->password);
        }
        $user->is_admin = $request->is_admin;

        $user->save();

        return redirect()->route('users.index')->with('success', 'ข้อมูลสมาชิกถูกแก้ไขเรียบร้อยแล้ว');
    }

    public function destroy(User $user) {
        $user->delete();
        return redirect()->route('users.index')->with('success', 'สมาชิกถูกลบเรียบร้อยแล้ว');
    }
}