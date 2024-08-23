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
            
        ]);
        $user->fname = $request->input('fname');
        $user->lname = $request->input('lname');

        $user->save();
        // เปลี่ยนเส้นทางไปหน้า home พร้อมกับส่งข้อความสำเร็จ

        return redirect()->route('home')->with('success', 'Profile updated successfully.');
    }
    
}
