<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;

class IpServiceController extends Controller
{
    public function index()
    {
        return view('pages.master.ip-services');
    }
}
