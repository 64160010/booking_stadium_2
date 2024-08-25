<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\Auth\VerificationController;
use App\Http\Middleware\IsAdmin;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\StadiumController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\LendingController;

Route::get('/', function () {
    return view('home');
    
});



Auth::routes();
Route::get('/home', [HomeController::class, 'index'])->name('home');
// เส้นทางสำหรับหน้าแรกของผู้ดูแลระบบ
Route::get('/admin/home', [HomeController::class, 'adminHome'])
    ->name('admin.home')
    ->middleware(IsAdmin::class);

  
// เส้นทางของปฏิทิน
// Route::get('/calendar', [CalendarController::class, 'index']);
Route::get('/calendar', [CalendarController::class, 'index'])->name('calendar');
Route::post('/calendar', [CalendarController::class, 'index']);
//เส้นทางการแก้ไขโปรไฟล์
Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
Route::put('/profile/update', [ProfileController::class, 'update'])->name('profile.update');



Route::resource('stadiums', StadiumController::class);
Route::resource('users', UserController::class);
Route::get('/payments', [PaymentController::class, 'index'])->name('payments.index');



Route::group(['middleware' => ['auth', 'is_admin']], function() {
    Route::get('/admin/stadium', [StadiumController::class, 'index'])->name('stadium.index');
    Route::post('/admin/stadium', [StadiumController::class, 'store'])->name('stadium.store');
    // เส้นทางอื่น ๆ ที่เกี่ยวกับการจัดการสนาม
});
Route::get('/stadium', [StadiumController::class, 'show'])->name('stadium.show');

// เส้นทางสำหรับการจอง
Route::get('/booking', [BookingController::class, 'index'])->name('booking');
// เส้นทางสำหรับการยืม
Route::get('/lending', [LendingController::class, 'index'])->name('lending.index');
Route::get('/borrow-item/{id}', [LendingController::class, 'borrowItem'])->name('borrow-item');
Route::get('/items/{id}/edit', [LendingController::class, 'edit'])->name('edit-item');
Route::put('/items/{id}', [LendingController::class, 'update'])->name('update-item');
Route::get('/repair', [LendingController::class, 'repair'])->name('repair');
Route::get('/add-item', [LendingController::class, 'addItem'])->name('add-item');
Route::post('/store-item', [LendingController::class, 'storeItem'])->name('store-item');
