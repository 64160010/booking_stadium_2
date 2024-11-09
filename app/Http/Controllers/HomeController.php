<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Stadium;
use App\Models\Borrow;
use App\Models\BorrowDetail;
use App\Models\Item;
use App\Models\BookingStadium;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the user dashboard (for non-admins).
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $user = Auth::user();

        // ตรวจสอบว่าผู้ใช้เป็นแอดมินหรือไม่
        if ($user->is_admin == 1) {
            return redirect()->route('admin.home'); // เปลี่ยนเส้นทางไปยังหน้าแอดมิน
        }

        // ถ้าไม่ใช่แอดมิน ให้แสดงหน้า home.blade.php
        $currentYear = now()->year;
        $years = range($currentYear - 10, $currentYear + 10);
        $userCount = User::count();
        $stadiumCount = Stadium::count();

        return view('home', compact('years', 'userCount', 'stadiumCount'));
    }

    /**
     * Show the admin dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function adminHome()
    {
        $user = Auth::user();
        
        // ตรวจสอบสิทธิ์ของผู้ใช้
        if ($user->is_admin != 1) {
            abort(403, 'Unauthorized access'); // ถ้าไม่ใช่แอดมินให้แสดงหน้าข้อผิดพลาด
        }
    
        // นับจำนวนผู้ใช้และสนามทั้งหมด
        $userCount = User::count();
        $stadiumCount = Stadium::count();
    
        // ข้อมูลการจองสนามรายเดือน
        $monthlyBookings = DB::table('booking_stadium')
            ->join('booking_detail', 'booking_stadium.id', '=', 'booking_detail.booking_stadium_id')
            ->join('stadium', 'booking_detail.stadium_id', '=', 'stadium.id')
            ->select(DB::raw('stadium.stadium_name, MONTH(booking_stadium.created_at) as month, COUNT(*) as total_bookings'))
            ->where('booking_stadium.booking_status', 'ชำระเงินแล้ว')
            ->groupBy('stadium.stadium_name', 'month')
            ->get();
    
        // ข้อมูลราคารวมรายวันจากการยืม
        $dailyRevenueBorrow = DB::table('borrow_detail')
            ->join('borrow', 'borrow_detail.borrow_id', '=', 'borrow.id')
            ->select(DB::raw('DATE(borrow_detail.borrow_date) as date, SUM(borrow_detail.borrow_total_price) as total_revenue'))
            ->where('borrow_detail.return_status', '!=', 'ยังไม่ตรวจสอบ') // กรองรายการที่สถานะไม่ใช่ 'ยังไม่ตรวจสอบ'
            ->groupBy(DB::raw('DATE(borrow_detail.borrow_date)'))
            ->get();
    
        // ข้อมูลราคารวมรายวันจากการจองสนาม
        $dailyRevenueBooking = DB::table('booking_stadium')
            ->join('booking_detail', 'booking_stadium.id', '=', 'booking_detail.booking_stadium_id')
            ->select(DB::raw('DATE(booking_stadium.created_at) as date, SUM(booking_detail.booking_total_price) as total_revenue'))
            ->where('booking_stadium.booking_status', 'ชำระเงินแล้ว') // กรองเฉพาะการจองที่ชำระเงินแล้ว
            ->groupBy(DB::raw('DATE(booking_stadium.created_at)'))
            ->get();
    
        // ข้อมูลจำนวนผู้ใช้ที่มีสถานะ "หมดอายุการชำระเงิน" รายเดือน
        $expiredPaymentsMonthly = DB::table('booking_stadium')
            ->join('booking_detail', 'booking_stadium.id', '=', 'booking_detail.booking_stadium_id')
            ->select(DB::raw('MONTH(booking_stadium.created_at) as month, COUNT(DISTINCT booking_stadium.users_id) as total_users'))
            ->where('booking_stadium.booking_status', 'หมดอายุการชำระเงิน') // กรองเฉพาะการจองที่หมดอายุการชำระเงิน
            ->groupBy(DB::raw('MONTH(booking_stadium.created_at)'))
            ->get();
    
        // ข้อมูลจำนวนผู้ใช้ที่มีสถานะ "การชำระเงินถูกปฏิเสธ" รายเดือน
        $deniedPaymentsMonthly = DB::table('booking_stadium')
            ->join('booking_detail', 'booking_stadium.id', '=', 'booking_detail.booking_stadium_id')
            ->select(DB::raw('MONTH(booking_stadium.created_at) as month, COUNT(DISTINCT booking_stadium.users_id) as total_users'))
            ->where('booking_stadium.booking_status', 'การชำระเงินถูกปฏิเสธ') // กรองเฉพาะการจองที่การชำระเงินถูกปฏิเสธ
            ->groupBy(DB::raw('MONTH(booking_stadium.created_at)'))
            ->get();

// ดึงข้อมูลอุปกรณ์ที่อยู่ระหว่างการซ่อมจาก borrow_detail
$repairDataByDateAndItem = BorrowDetail::selectRaw('DATE(borrow_detail.borrow_date) as date, item.item_name, SUM(borrow_detail.borrow_quantity) as total_repairs')
    ->join('item', 'borrow_detail.item_id', '=', 'item.id') // เชื่อมตาราง item
    ->where('borrow_detail.return_status', 'ซ่อม')
    ->groupBy('date', 'item.item_name') // จัดกลุ่มตามวันที่และชื่ออุปกรณ์
    ->get()
    ->groupBy('date')
    ->map(function ($group) {
        return $group->pluck('total_repairs', 'item_name');
    });

// ดึงข้อมูลสำหรับอุปกรณ์ที่ซ่อมไม่ได้รายวัน
$unrepairableDataByDateAndItem = BorrowDetail::selectRaw('DATE(borrow_detail.borrow_date) as date, item.item_name, SUM(borrow_detail.borrow_quantity) as total_unrepairable')
    ->join('item', 'borrow_detail.item_id', '=', 'item.id') // เชื่อมตาราง item
    ->where('borrow_detail.return_status', 'ซ่อมไม่ได้') // เงื่อนไขซ่อมไม่ได้
    ->groupBy('date', 'item.item_name') // จัดกลุ่มตามวันที่และชื่ออุปกรณ์
    ->get()
    ->groupBy('date')
    ->map(function ($group) {
        return $group->pluck('total_unrepairable', 'item_name');
    });

    
        // การแยกข้อมูลเพื่อให้สามารถแสดงในกราฟได้
        $borrowDates = $dailyRevenueBorrow->pluck('date')->toArray();
        $borrowRevenue = $dailyRevenueBorrow->pluck('total_revenue')->toArray();
        $bookingDates = $dailyRevenueBooking->pluck('date')->toArray();
        $bookingRevenue = $dailyRevenueBooking->pluck('total_revenue')->toArray();
    
        // ข้อมูลสำหรับกราฟจำนวนผู้ใช้ที่หมดอายุการชำระเงินรายเดือน
        $expiredPaymentMonths = $expiredPaymentsMonthly->pluck('month')->toArray();
        $expiredPaymentUsers = $expiredPaymentsMonthly->pluck('total_users')->toArray();
    
        // ข้อมูลสำหรับกราฟจำนวนผู้ใช้ที่ถูกปฏิเสธการชำระเงินรายเดือน
        $deniedPaymentMonths = $deniedPaymentsMonthly->pluck('month')->toArray();
        $deniedPaymentUsers = $deniedPaymentsMonthly->pluck('total_users')->toArray();
    
        // ส่งข้อมูลไปยัง View
        return view('adminHome', compact(
            'repairDataByDateAndItem',
            'unrepairableDataByDateAndItem',
            'userCount', 
            'stadiumCount', 
            'monthlyBookings', 
            'borrowDates', 
            'borrowRevenue', 
            'bookingDates', 
            'bookingRevenue',
            'dailyRevenueBooking',
            'dailyRevenueBorrow',
            'expiredPaymentMonths', // เดือนที่มีการหมดอายุการชำระเงิน
            'expiredPaymentUsers', // จำนวนผู้ใช้ที่หมดอายุการชำระเงิน
            'deniedPaymentMonths', // เดือนที่การชำระเงินถูกปฏิเสธ
            'deniedPaymentUsers' // จำนวนผู้ใช้ที่การชำระเงินถูกปฏิเสธ
        ));
    }
    
    
    



    public function adminBorrow()
    {
        $user = Auth::user();

        if ($user->is_admin != 1) {
            abort(403, 'Unauthorized access');
        }

        $borrows = Borrow::with('user', 'bookingStadium.stadium')->get();
        return view('admin-borrow', compact('borrows'));
    }
}
