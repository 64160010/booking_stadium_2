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
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\BorrowingController;
use App\Http\Controllers\StadiumController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\LendingController;

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

// การจัดการการชำระเงิน
Route::get('/payments', [PaymentController::class, 'index'])->name('payments.index');

// การจัดการการยืม
Route::get('/borrowings', [BorrowingController::class, 'index'])->name('borrowings.index');

// เส้นทางที่ต้องการผู้ดูแลระบบ
Route::group(['middleware' => ['auth', 'is_admin']], function() {
    Route::get('/admin/stadium', [StadiumController::class, 'index'])->name('stadium.index');
    Route::post('/admin/stadium', [StadiumController::class, 'store'])->name('stadium.store');
    // เส้นทางอื่น ๆ ที่เกี่ยวกับการจัดการสนาม
});

// เส้นทางสำหรับการจองสนาม
Route::get('/booking', [BookingController::class, 'index'])->name('booking');

// เส้นทางสำหรับการยืมอุปกรณ์
Route::get('/lending', [LendingController::class, 'index'])->name('lending.index');

// เส้นทางสำหรับการยืมอุปกรณ์ รองรับพารามิเตอร์วันที่ (เฉพาะผู้ใช้ที่ล็อกอินเท่านั้นที่สามารถยืมได้)
Route::get('/borrow-item/{item_id}/{date?}', [LendingController::class, 'borrowItem'])
    ->name('borrow-item')
    ->middleware('auth');

// เส้นทางสำหรับการบันทึกข้อมูลการยืม
Route::post('/borrow', [LendingController::class, 'storeBorrow'])
    ->name('borrow-item.store')
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
