<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class WardController extends Controller
{
    public function index()
    {
        return view('pages.master.wards');
    }
}
