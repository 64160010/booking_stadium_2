<?php

namespace App\Http\Controllers;

use App\Models\Stadium;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StadiumController extends Controller
{
    public function index() {
        $stadiums = Stadium::all();
        return view('stadium.index', compact('stadiums'));
    }

    public function show() {
        $stadiums = Stadium::all();
        return view('stadium.show', compact('stadiums'));
    }

    public function create() {
        return view('stadium.create');
    }

    public function store(Request $request) {
        $request->validate([
            'stadium_name' => 'required',
            'stadium_price' => 'required|numeric',
            'stadium_status' => 'required',
        ]);

        $stadium = new Stadium();
        $stadium->stadium_name = $request->stadium_name;
        $stadium->stadium_price = $request->stadium_price;
        $stadium->stadium_status = $request->stadium_status;
        $stadium->save();

        return redirect()->route('stadiums.index')->with('success', 'สนามถูกเพิ่มเรียบร้อยแล้ว');
    }

    public function update(Request $request, Stadium $stadium) {
        $request->validate([
            'stadium_name' => 'required',
            'stadium_price' => 'required|numeric',
            'stadium_status' => 'required',
        ]);

        $stadium->stadium_name = $request->stadium_name;
        $stadium->stadium_price = $request->stadium_price;
        $stadium->stadium_status = $request->stadium_status;
        $stadium->save();

        return redirect()->route('stadiums.index')->with('success', 'ข้อมูลสนามถูกแก้ไขเรียบร้อยแล้ว');
    }

    public function destroy(Stadium $stadium) {
        $stadium->delete();
        return redirect()->route('stadiums.index')->with('success', 'สนามถูกลบเรียบร้อยแล้ว');
    }

    public function edit($id)
    {
        $stadium = Stadium::findOrFail($id);

        if (Auth::user()->is_admin != 1) {
            return redirect()->route('stadium.show')->with('error', "You don't have admin access.");
        }

        return view('stadium.edit', compact('stadium'));
    }
}
