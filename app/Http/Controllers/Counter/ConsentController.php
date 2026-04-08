<?php

namespace App\Http\Controllers\Counter;

use App\Http\Controllers\Controller;
use App\Models\HospitalOwner;
use App\Models\Patient;
use App\Models\Setting;
use Illuminate\Http\Request;

class ConsentController extends Controller
{
    public function highRisk(Request $request, int $id)
    {
        $patient = Patient::findOrFail($id);

        return view('pages.counter.consents.high-risk', [
            'patient' => $patient,
            'hospital' => [
                'name' => Setting::get('hospital_name'),
                'address' => Setting::get('hospital_address'),
                'city' => Setting::get('hospital_city'),
                'state' => Setting::get('hospital_state'),
                'pincode' => Setting::get('hospital_pincode'),
                'phone' => Setting::get('hospital_phone'),
            ],
            'doctorName' => HospitalOwner::ownerDoctor()?->full_name ?? $request->user()?->name,
        ]);
    }

    public function highRiskPrint(Request $request, int $id)
    {
        $patient = Patient::findOrFail($id);

        return view('pages.counter.consents.high-risk-print', [
            'patient' => $patient,
            'hospital' => [
                'name' => Setting::get('hospital_name'),
                'address' => Setting::get('hospital_address'),
                'city' => Setting::get('hospital_city'),
                'state' => Setting::get('hospital_state'),
                'pincode' => Setting::get('hospital_pincode'),
                'phone' => Setting::get('hospital_phone'),
            ],
            'doctorName' => HospitalOwner::ownerDoctor()?->full_name ?? $request->user()?->name,
        ]);
    }

    public function highRiskEnglish(Request $request, int $id)
    {
        $patient = Patient::findOrFail($id);

        return view('pages.counter.consents.high-risk-en', [
            'patient' => $patient,
            'hospital' => [
                'name' => Setting::get('hospital_name'),
                'address' => Setting::get('hospital_address'),
                'city' => Setting::get('hospital_city'),
                'state' => Setting::get('hospital_state'),
                'pincode' => Setting::get('hospital_pincode'),
                'phone' => Setting::get('hospital_phone'),
            ],
            'doctorName' => HospitalOwner::ownerDoctor()?->full_name ?? $request->user()?->name,
        ]);
    }

    public function highRiskPrintEnglish(Request $request, int $id)
    {
        $patient = Patient::findOrFail($id);

        return view('pages.counter.consents.high-risk-print-en', [
            'patient' => $patient,
            'hospital' => [
                'name' => Setting::get('hospital_name'),
                'address' => Setting::get('hospital_address'),
                'city' => Setting::get('hospital_city'),
                'state' => Setting::get('hospital_state'),
                'pincode' => Setting::get('hospital_pincode'),
                'phone' => Setting::get('hospital_phone'),
            ],
            'doctorName' => HospitalOwner::ownerDoctor()?->full_name ?? $request->user()?->name,
        ]);
    }
}
