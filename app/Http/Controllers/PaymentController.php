<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PaymentBooking;
use App\Models\BookingStadium;
use App\Models\Borrow;

class PaymentController extends Controller
{
    public function showPaymentForm($bookingId)
    {
        // ดึงข้อมูลการจองสนาม
        $booking = BookingStadium::find($bookingId);
    
        // ดึงรายการการจองทั้งหมดของผู้ใช้
        $bookings = BookingStadium::where('users_id', auth()->id())->get();
    
        // ส่งข้อมูลไปยัง view
        return view('paymentBooking', [
            'booking' => $booking,
            'bookings' => $bookings, // ส่งตัวแปร bookings ไปยังวิว
        ]);
    }
    
    // ประมวลผลการชำระเงิน
    public function processPayment(Request $request)
    {
        // Validate request data
    $validatedData = $request->validate([
        'booking_code' => 'required|exists:booking_stadium,id',
        'payer_name' => 'required|string|max:255',
        'phone_number' => 'required|string|max:15',
        'select_bank' => 'required|in:กสิกรไทย,ไทยพาณิชย์,กรุงไทย', // Validate against ENUM values
        'transfer_datetime' => 'required|date',
        'transfer_amount' => 'required|numeric',
        'transfer_slip' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
    ]);

    // Handle file upload
    if ($request->hasFile('transfer_slip')) {
        $fileName = time().'.'.$request->transfer_slip->extension();
        $request->transfer_slip->move(public_path('uploads/slips'), $fileName);
    }

     // Check for borrowing details
     $borrow = Borrow::where('booking_stadium_id', $request->input('booking_code'))
     ->where('users_id', auth()->id()) // Ensure this is the current user
     ->first();

   // Save payment data
   $payment = new PaymentBooking();
   $payment->amount = $request->input('transfer_amount');
   $payment->confirmation_pic = $fileName;
   $payment->booking_stadium_id = $request->input('booking_code');
   $payment->payer_name = $request->input('payer_name');
   $payment->phone_number = $request->input('phone_number');
   $payment->bank_name = $request->input('select_bank');
   $payment->transfer_datetime = $request->input('transfer_datetime');

   // Store borrow_id if there is a related borrow record
   if ($borrow) {
       $payment->borrow_id = $borrow->id; // Assuming 'id' is the primary key of the borrow record
   }

   $payment->save();

    return redirect()->route('booking')->with('success', 'การชำระเงินถูกบันทึกเรียบร้อยแล้ว');
}
}
