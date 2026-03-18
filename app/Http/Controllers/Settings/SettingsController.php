<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    /**
     * Display settings overview or hospital settings by default.
     */
    public function index()
    {
        return view('pages.settings.index');
    }

    /**
     * System preferences screen.
     */
    public function preferences()
    {
        return view('pages.settings.preferences');
    }

    /**
     * Invoice and print settings screen.
     */
    public function invoice()
    {
        return view('pages.settings.invoice');
    }
}
