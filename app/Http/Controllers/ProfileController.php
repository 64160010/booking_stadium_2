<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class ProfileController extends Controller
{
    public function edit()
    {
        return view('profile.edit');
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'fname' => 'required|string|max:255',
            'lname' => 'required|string|max:255',
            'phone' => 'required|digits:10|regex:/^[0-9]+$/|unique:users,phone,' . $user->id,
   
        ]);
        $user->fname = $request->input('fname');
        $user->lname = $request->input('lname');
        $user->phone = $request->input('phone');

        $user->save();
        // เปลี่ยนเส้นทางไปหน้า home พร้อมกับส่งข้อความสำเร็จ

        return redirect()->route('home')->with('success', 'แก้ไขข้อมูลสำเร็จแล้ว');
    }
    
    
}