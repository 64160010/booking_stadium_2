<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Stadium;
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
}