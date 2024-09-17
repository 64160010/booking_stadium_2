<?php

namespace App\Http\Middleware;

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class IsAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        // ตรวจสอบว่าผู้ใช้ได้เข้าสู่ระบบหรือไม่
        if (!Auth::check()) {
            // ถ้ายังไม่ได้เข้าสู่ระบบ ให้ redirect ไปหน้า login
            return redirect('login')->with('error', 'กรุณาเข้าสู่ระบบก่อน');
        }

        // ถ้าผู้ใช้เข้าสู่ระบบแล้ว ให้ตรวจสอบว่าเป็นผู้ดูแลระบบหรือไม่
        if (Auth::user()->is_admin != 1) {
            return redirect('home')->with('error', "คุณไม่มีสิทธิ์เข้าถึงหน้านี้");
        }

        // ถ้าผู้ใช้เป็นแอดมิน ให้ดำเนินการต่อ
        return $next($request);
    }
}
