<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StaffController extends Controller
{
    // Staff-related methods can be added here in the future
    public function index()
    {
        $user = Auth::user();
        return view('dashboards.staff.index', compact('user'));
    }
}
