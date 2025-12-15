<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class RasaServerController extends Controller
{
    /**
     * Display the Rasa Server Manager page.
     */
    public function index()
    {
        return view('dashboards.admin.rasa-server.index');
    }
}