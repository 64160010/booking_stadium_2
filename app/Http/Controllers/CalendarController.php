<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;

class CalendarController extends Controller
{
    public function index(Request $request)
    {
        $year = $request->input('year', Carbon::now()->year);
        $month = $request->input('month', Carbon::now()->month);

        $date = Carbon::createFromDate($year, $month, 1);
        $daysInMonth = $date->daysInMonth;
        $firstDayOfWeek = $date->dayOfWeek;

        return view('calendar', compact('year', 'month', 'daysInMonth', 'firstDayOfWeek'));
    }
}


