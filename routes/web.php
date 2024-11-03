<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\Auth\VerificationController;
use App\Http\Middleware\IsAdmin;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;

use App\Http\Controllers\BorrowingController;
use App\Http\Controllers\StadiumController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\LendingController;
use App\Http\Controllers\PaymentController;

// เส้นทางหลัก
Route::get('/', function () {
    return view('home');
});

// เส้นทางการจัดการผู้ใช้
Auth::routes();
Route::get('/home', [HomeController::class, 'index'])->name('home');

// เส้นทางสำหรับหน้าแรกของผู้ดูแลระบบ
Route::get('/admin/home', [HomeController::class, 'adminHome'])
    ->name('admin.home')
    ->middleware(IsAdmin::class);

// เส้นทางของปฏิทิน
Route::get('/calendar', [CalendarController::class, 'index'])->name('calendar');
Route::post('/calendar', [CalendarController::class, 'index']);

// การจัดการโปรไฟล์
Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
Route::put('/profile/update', [ProfileController::class, 'update'])->name('profile.update');

// การจัดการสนาม
Route::resource('stadiums', StadiumController::class);

// การจัดการผู้ใช้
Route::resource('users', UserController::class);





// เส้นทางที่ต้องการผู้ดูแลระบบ
Route::group(['middleware' => ['auth', 'is_admin']], function() {
    Route::get('/admin/stadium', [StadiumController::class, 'index'])->name('stadium.index');
    Route::post('/admin/stadium', [StadiumController::class, 'store'])->name('stadium.store');
    // เส้นทางอื่น ๆ ที่เกี่ยวกับการจัดการสนาม
});

// เส้นทางสำหรับการจองสนาม
Route::get('/booking', [BookingController::class, 'index'])->name('booking');

// เส้นทางสำหรับการยืมอุปกรณ์
// Route::get('/lending', [LendingController::class, 'index'])->name('lending.index');
Route::get('/lending/index', [LendingController::class, 'index'])->name('lending.index');

// เส้นทางสำหรับการยืมอุปกรณ์ รองรับพารามิเตอร์วันที่ (เฉพาะผู้ใช้ที่ล็อกอินเท่านั้นที่สามารถยืมได้)
// Route::get('/lending/borrow-item/{id}', [LendingController::class, 'borrowItem'])->name('lending.borrow-item')
//     ->middleware('auth');
Route::post('/borrow-item', [LendingController::class, 'borrowItem'])->name('borrow.item');




// เส้นทางสำหรับการบันทึกข้อมูลการยืม
Route::post('/borrow/store', [LendingController::class, 'storeBorrow'])
    ->name('borrow.store')
    ->middleware('auth');

// เส้นทางการจัดการรายการอุปกรณ์
Route::get('/items/{id}/edit', [LendingController::class, 'edit'])->name('edit-item');
Route::put('/items/{id}', [LendingController::class, 'update'])->name('update-item');
Route::delete('/item/{id}', [LendingController::class, 'destroy'])->name('delete-item');

// เส้นทางสำหรับการซ่อมและเพิ่มรายการอุปกรณ์
Route::get('/repair', [LendingController::class, 'repair'])->name('repair');
Route::get('/add-item', [LendingController::class, 'addItem'])->name('add-item');
Route::post('/store-item', [LendingController::class, 'storeItem'])->name('store-item');

// เส้นทางที่ต้องการการล็อกอินสำหรับการจองสนาม
Route::group(['middleware' => 'auth'], function() {
    Route::post('/booking/store', [BookingController::class, 'store'])->name('booking.store');
});

// เส้นทางสำหรับการแสดงรายละเอียดการยืมอุปกรณ์
Route::get('/lending/borrow-detail', [LendingController::class, 'borrowDetail'])
    ->name('lending.borrow-detail')
    ->middleware('auth'); // เพิ่ม middleware ถ้าต้องการให้ต้องล็อกอิน

    Route::delete('/lending/borrow/{id}', [LendingController::class, 'destroyBorrow'])->name('lending.borrow.destroy');

Route::get('/bookingDetail/{id}', [BookingController::class, 'show'])->name('booking.detail');
Route::post('/confirmBooking/{booking_stadium_id}', [BookingController::class, 'confirmBooking'])->name('confirmBooking');


Route::get('/payment-booking/{booking_stadium_id}', [PaymentController::class, 'showPaymentForm'])->name('paymentBooking');
Route::post('/process-payment', [PaymentController::class, 'processPayment'])->name('processPayment');

Route::get('/history-booking', [PaymentController::class, 'historyBooking'])->name('history.booking');

Route::get('/booking/details', [PaymentController::class, 'getBookingDetails'])->name('booking.details');
Route::get('/history-detail/{booking_stadium_id}', [BookingController::class, 'showHistoryDetail'])->name('historyDetail');


Route::post('/booking/{id}/confirm', [BookingController::class, 'confirm'])->name('booking.confirm');
Route::post('/booking/{id}/reject', [BookingController::class, 'reject'])->name('booking.reject');

Route::get('/admin-borrow', [HomeController::class, 'adminBorrow'])->name('admin.borrow');