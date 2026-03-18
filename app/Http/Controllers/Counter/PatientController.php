<?php

namespace App\Http\Controllers\Counter;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PatientController extends Controller
{
    public function index()
    {
        return view('pages.counter.patients');
    }
}
