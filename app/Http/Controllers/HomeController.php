<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Stadium;
use App\Models\Borrow;
use App\Models\BorrowDetail;
use App\Models\BookingStadium;
class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        return view('home');
    }
    
    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function adminHome()
    {
        $userCount = User::count(); // นับจำนวนผู้ใช้ทั้งหมด
        $stadiumCount = Stadium::count(); // นับจำนวนสนามทั้งหมด
        return view('adminHome', compact('userCount', 'stadiumCount')); // ส่งตัวแปร userCount และ stadiumCount ไปยัง view
    }

    public function adminBorrow()
    {
        $borrows = Borrow::with('user', 'bookingStadium.stadium')->get();
        return view('admin-borrow', compact('borrows'));
    }
    
}