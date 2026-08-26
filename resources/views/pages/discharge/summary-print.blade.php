@extends('layouts.print')

@section('title', 'Discharge Summary — ' . $admission->admission_number)

@section('content')
<div style="text-align: center; border-bottom: 2px solid #000; padding-bottom: 10px; margin-bottom: 15px;">
    <img src="{{ asset('images/DW_header.png') }}" alt="Hospital Header" style="max-width: 100%; height: auto; max-height: 140px; object-fit: contain;">
</div>

<div style="text-align: center; margin-bottom: 20px;">
    <h2 style="margin: 0; font-size: 16pt; font-weight: bold; text-decoration: underline; font-family: Arial, sans-serif;">DISCHARGE SUMMARY</h2>
</div>

<div class="content" style="font-family: Arial, sans-serif; font-size: 11pt; color: #000;">
    <!-- PATIENT INFO -->
    <table style="width: 100%; border: 1px solid #000; border-collapse: collapse; margin-bottom: 15px;">
        <tr>
            <td style="padding: 8px; border: 1px solid #000; width: 15%;"><strong>Patient Name</strong></td>
            <td style="padding: 8px; border: 1px solid #000; width: 35%;">{{ $admission->patient->full_name }}</td>
            <td style="padding: 8px; border: 1px solid #000; width: 15%;"><strong>UHID / IP No</strong></td>
            <td style="padding: 8px; border: 1px solid #000; width: 35%;">{{ $admission->patient->uhid }} / {{ $admission->admission_number }}</td>
        </tr>
        <tr>
            <td style="padding: 8px; border: 1px solid #000;"><strong>Age / Sex</strong></td>
            <td style="padding: 8px; border: 1px solid #000;">{{ $admission->patient->age }} / {{ $admission->patient->gender }}</td>
            <td style="padding: 8px; border: 1px solid #000;"><strong>Ward / Bed</strong></td>
            <td style="padding: 8px; border: 1px solid #000;">{{ $admission->bed?->ward?->name ?? '—' }} - {{ $admission->bed?->bed_number ?? '—' }}</td>
        </tr>
        <tr>
            <td style="padding: 8px; border: 1px solid #000;"><strong>Admitted On</strong></td>
            <td style="padding: 8px; border: 1px solid #000;">{{ $admission->admission_date?->format('d-m-Y h:i A') ?? '—' }}</td>
            <td style="padding: 8px; border: 1px solid #000;"><strong>Discharged On</strong></td>
            <td style="padding: 8px; border: 1px solid #000;">{{ $admission->discharge_date?->format('d-m-Y h:i A') ?? '—' }}</td>
        </tr>
        <tr>
            <td style="padding: 8px; border: 1px solid #000;"><strong>Consultant</strong></td>
            <td style="padding: 8px; border: 1px solid #000;" colspan="3">{{ $admission->doctor?->full_name ?? '—' }}</td>
        </tr>
    </table>

    @php
        $summary = $admission->dischargeSummary;
        $admissionDiag = $summary?->admission_diagnosis ?: $admission->reason_for_admission;
        $finalDiag = $summary?->final_diagnosis;
        $treatmentSummary = $summary?->treatment_summary ?: $admission->notes;
        $proceduresDone = $summary?->procedures_done;
        $investigationsSummary = $summary?->investigations_summary;
        $conditionAtDischarge = $summary?->condition_at_discharge;
        $conditionNotes = $summary?->condition_notes;
        $latestVital = $admission->ipdVitals?->sortByDesc('recorded_at')->first();
        $hasVitals = $latestVital && ($latestVital->temperature || $latestVital->bp_systolic || $latestVital->pulse || $latestVital->spo2);
        $meds = $summary ? $summary->medications : collect();
        $generalAdvice = $summary?->general_advice;
        $dietAdvice = $summary?->diet_advice;
        $activityAdvice = $summary?->activity_advice;
        $followUpDate = $summary?->follow_up_date;
        $followUpNotes = $summary?->follow_up_notes;
    @endphp

    <!-- 1. DIAGNOSIS -->
    @if(!empty($admissionDiag) || !empty($finalDiag))
    <table style="width: 100%; border: 1px solid #000; border-collapse: collapse; margin-bottom: 12px;">
        <tr style="background-color: #f8fafc;">
            <th colspan="2" style="padding: 6px 8px; border: 1px solid #000; text-align: left; font-size: 11pt; font-weight: bold; text-transform: uppercase;">
                Diagnosis
            </th>
        </tr>
        @if(!empty($admissionDiag))
        <tr>
            <td style="padding: 6px 8px; border: 1px solid #000; width: 25%; vertical-align: top;"><strong>Admission / Provisional Diagnosis:</strong></td>
            <td style="padding: 6px 8px; border: 1px solid #000; white-space: pre-line;">{{ $admissionDiag }}</td>
        </tr>
        @endif
        @if(!empty($finalDiag))
        <tr>
            <td style="padding: 6px 8px; border: 1px solid #000; width: 25%; vertical-align: top;"><strong>Final Diagnosis:</strong></td>
            <td style="padding: 6px 8px; border: 1px solid #000; font-weight: bold; white-space: pre-line;">{{ $finalDiag }}</td>
        </tr>
        @endif
    </table>
    @endif

    <!-- 2. TREATMENT & HOSPITAL COURSE -->
    @if(!empty($treatmentSummary) || !empty($proceduresDone) || !empty($investigationsSummary))
    <table style="width: 100%; border: 1px solid #000; border-collapse: collapse; margin-bottom: 12px;">
        <tr style="background-color: #f8fafc;">
            <th colspan="2" style="padding: 6px 8px; border: 1px solid #000; text-align: left; font-size: 11pt; font-weight: bold; text-transform: uppercase;">
                Treatment & Hospital Course
            </th>
        </tr>
        @if(!empty($treatmentSummary))
        <tr>
            <td style="padding: 6px 8px; border: 1px solid #000; width: 25%; vertical-align: top;"><strong>Treatment Given / Summary:</strong></td>
            <td style="padding: 6px 8px; border: 1px solid #000; white-space: pre-line;">{{ $treatmentSummary }}</td>
        </tr>
        @endif
        @if(!empty($proceduresDone))
        <tr>
            <td style="padding: 6px 8px; border: 1px solid #000; width: 25%; vertical-align: top;"><strong>Procedures / Surgeries Done:</strong></td>
            <td style="padding: 6px 8px; border: 1px solid #000; white-space: pre-line;">{{ $proceduresDone }}</td>
        </tr>
        @endif
        @if(!empty($investigationsSummary))
        <tr>
            <td style="padding: 6px 8px; border: 1px solid #000; width: 25%; vertical-align: top;"><strong>Investigations Summary:</strong></td>
            <td style="padding: 6px 8px; border: 1px solid #000; white-space: pre-line;">{{ $investigationsSummary }}</td>
        </tr>
        @endif
    </table>
    @endif

    <!-- 3. CONDITION AT DISCHARGE -->
    @if(!empty($conditionAtDischarge) || !empty($conditionNotes) || $hasVitals)
    <table style="width: 100%; border: 1px solid #000; border-collapse: collapse; margin-bottom: 12px;">
        <tr style="background-color: #f8fafc;">
            <th colspan="2" style="padding: 6px 8px; border: 1px solid #000; text-align: left; font-size: 11pt; font-weight: bold; text-transform: uppercase;">
                Condition at Discharge
            </th>
        </tr>
        @if(!empty($conditionAtDischarge))
        <tr>
            <td style="padding: 6px 8px; border: 1px solid #000; width: 25%;"><strong>Condition:</strong></td>
            <td style="padding: 6px 8px; border: 1px solid #000; font-weight: bold;">{{ $conditionAtDischarge }}</td>
        </tr>
        @endif
        @if(!empty($conditionNotes))
        <tr>
            <td style="padding: 6px 8px; border: 1px solid #000; width: 25%; vertical-align: top;"><strong>Condition Notes:</strong></td>
            <td style="padding: 6px 8px; border: 1px solid #000; white-space: pre-line;">{{ $conditionNotes }}</td>
        </tr>
        @endif
        @if($hasVitals)
        <tr>
            <td style="padding: 6px 8px; border: 1px solid #000; width: 25%;"><strong>Vitals at Discharge:</strong></td>
            <td style="padding: 6px 8px; border: 1px solid #000;">
                @php
                    $vitalsParts = [];
                    if (!empty($latestVital->temperature)) $vitalsParts[] = 'Temp: ' . $latestVital->temperature . ' °F';
                    if (!empty($latestVital->bp_systolic)) $vitalsParts[] = 'BP: ' . $latestVital->bp_systolic . ($latestVital->bp_diastolic ? '/' . $latestVital->bp_diastolic : '') . ' mmHg';
                    if (!empty($latestVital->pulse)) $vitalsParts[] = 'Pulse: ' . $latestVital->pulse . ' bpm';
                    if (!empty($latestVital->spo2)) $vitalsParts[] = 'SpO2: ' . $latestVital->spo2 . '%';
                @endphp
                {{ implode(' | ', $vitalsParts) }}
            </td>
        </tr>
        @endif
    </table>
    @endif

    <!-- 4. DISCHARGE MEDICATIONS -->
    @if($meds->count() > 0)
    <div style="margin-bottom: 12px;">
        <table style="width: 100%; border: 1px solid #000; border-collapse: collapse; font-size: 10pt;">
            <tr style="background-color: #f8fafc;">
                <th colspan="6" style="padding: 6px 8px; border: 1px solid #000; text-align: left; font-size: 11pt; font-weight: bold; text-transform: uppercase;">
                    Discharge Medications
                </th>
            </tr>
            <thead>
                <tr style="background-color: #f1f5f9;">
                    <th style="padding: 6px; border: 1px solid #000; text-align: center; width: 5%;">#</th>
                    <th style="padding: 6px; border: 1px solid #000; text-align: left; width: 30%;">Medicine Name</th>
                    <th style="padding: 6px; border: 1px solid #000; text-align: left; width: 15%;">Dosage</th>
                    <th style="padding: 6px; border: 1px solid #000; text-align: left; width: 15%;">Frequency</th>
                    <th style="padding: 6px; border: 1px solid #000; text-align: left; width: 15%;">Duration</th>
                    <th style="padding: 6px; border: 1px solid #000; text-align: left; width: 20%;">Instructions / Route</th>
                </tr>
            </thead>
            <tbody>
                @foreach($meds as $index => $rx)
                    <tr>
                        <td style="padding: 6px; border: 1px solid #000; text-align: center;">{{ $index + 1 }}</td>
                        <td style="padding: 6px; border: 1px solid #000; font-weight: bold;">{{ $rx->medicine_name }}</td>
                        <td style="padding: 6px; border: 1px solid #000;">{{ $rx->dosage ?? '—' }}</td>
                        <td style="padding: 6px; border: 1px solid #000;">{{ $rx->frequency ?? '—' }}</td>
                        <td style="padding: 6px; border: 1px solid #000;">{{ $rx->duration ?? '—' }}</td>
                        <td style="padding: 6px; border: 1px solid #000;">
                            {{ $rx->instructions ?? '—' }}
                            @if($rx->route && $rx->route !== 'Oral')
                                <span style="font-size: 8pt; color: #555;">({{ $rx->route }})</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <!-- 5. ADVICE & DISCHARGE INSTRUCTIONS -->
    @if(!empty($generalAdvice) || !empty($dietAdvice) || !empty($activityAdvice))
    <table style="width: 100%; border: 1px solid #000; border-collapse: collapse; margin-bottom: 12px;">
        <tr style="background-color: #f8fafc;">
            <th colspan="2" style="padding: 6px 8px; border: 1px solid #000; text-align: left; font-size: 11pt; font-weight: bold; text-transform: uppercase;">
                Advice & Instructions
            </th>
        </tr>
        @if(!empty($generalAdvice))
        <tr>
            <td style="padding: 6px 8px; border: 1px solid #000; width: 25%; vertical-align: top;"><strong>General Advice:</strong></td>
            <td style="padding: 6px 8px; border: 1px solid #000; white-space: pre-line;">{{ $generalAdvice }}</td>
        </tr>
        @endif
        @if(!empty($dietAdvice))
        <tr>
            <td style="padding: 6px 8px; border: 1px solid #000; width: 25%; vertical-align: top;"><strong>Dietary Advice:</strong></td>
            <td style="padding: 6px 8px; border: 1px solid #000; white-space: pre-line;">{{ $dietAdvice }}</td>
        </tr>
        @endif
        @if(!empty($activityAdvice))
        <tr>
            <td style="padding: 6px 8px; border: 1px solid #000; width: 25%; vertical-align: top;"><strong>Activity / Rest Advice:</strong></td>
            <td style="padding: 6px 8px; border: 1px solid #000; white-space: pre-line;">{{ $activityAdvice }}</td>
        </tr>
        @endif
    </table>
    @endif

    <!-- 6. FOLLOW UP -->
    @if(!empty($followUpDate) || !empty($followUpNotes))
    <table style="width: 100%; border: 1px solid #000; border-collapse: collapse; margin-bottom: 15px;">
        <tr style="background-color: #f8fafc;">
            <th colspan="2" style="padding: 6px 8px; border: 1px solid #000; text-align: left; font-size: 11pt; font-weight: bold; text-transform: uppercase;">
                Follow Up Details
            </th>
        </tr>
        @if(!empty($followUpDate))
        <tr>
            <td style="padding: 6px 8px; border: 1px solid #000; width: 25%;"><strong>Follow Up Date:</strong></td>
            <td style="padding: 6px 8px; border: 1px solid #000; font-weight: bold; color: #1e40af;">
                {{ $followUpDate->format('d-m-Y') }}
            </td>
        </tr>
        @endif
        @if(!empty($followUpNotes))
        <tr>
            <td style="padding: 6px 8px; border: 1px solid #000; width: 25%; vertical-align: top;"><strong>Follow Up Instructions:</strong></td>
            <td style="padding: 6px 8px; border: 1px solid #000; white-space: pre-line;">{{ $followUpNotes }}</td>
        </tr>
        @endif
    </table>
    @endif

    <!-- FINAL BILL -->
    @if(\Illuminate\Support\Facades\Schema::hasColumn('bills', 'admission_id') && $admission->finalBill)
    <div style="page-break-before: always;">
        <div style="text-align: center; border-bottom: 2px solid #000; padding-bottom: 10px; margin-bottom: 15px;">
            <img src="{{ asset('images/DW_header.png') }}" alt="Hospital Header" style="max-width: 100%; height: auto; max-height: 140px; object-fit: contain;">
        </div>
        <div style="text-align: center; margin-bottom: 20px;">
            <h2 style="margin: 0; font-size: 16pt; font-weight: bold; text-decoration: underline; font-family: Arial, sans-serif;">FINAL BILL</h2>
        </div>
        <table style="width: 100%; border: 1px solid #000; border-collapse: collapse; margin-bottom: 15px;">
            <tr>
                <td style="padding: 8px; border: 1px solid #000; width: 15%;"><strong>Patient Name</strong></td>
                <td style="padding: 8px; border: 1px solid #000; width: 35%;">{{ $admission->patient->full_name }}</td>
                <td style="padding: 8px; border: 1px solid #000; width: 15%;"><strong>UHID / IP No</strong></td>
                <td style="padding: 8px; border: 1px solid #000; width: 35%;">{{ $admission->patient->uhid }} / {{ $admission->admission_number }}</td>
            </tr>
        </table>
        <h3 style="margin: 0 0 5px; font-size: 12pt; text-decoration: underline;">Invoice No: {{ $admission->finalBill->bill_number }}</h3>
        <table style="width:100%; border: 1px solid #000; border-collapse: collapse; font-size: 10pt; margin-bottom: 15px;">
            <thead>
                <tr>
                    <th style="padding: 6px; border: 1px solid #000; text-align: center; width: 5%;">S.No</th>
                    <th style="padding: 6px; border: 1px solid #000; text-align: left; width: 15%;">Date</th>
                    <th style="padding: 6px; border: 1px solid #000; text-align: left; width: 50%;">Particulars</th>
                    <th style="padding: 6px; border: 1px solid #000; text-align: right; width: 10%;">Qty</th>
                    <th style="padding: 6px; border: 1px solid #000; text-align: right; width: 10%;">Rate (₹)</th>
                    <th style="padding: 6px; border: 1px solid #000; text-align: right; width: 10%;">Amount (₹)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($admission->finalBill->items as $index => $item)
                    @php
                        $rawName = $item->item_name ?? $item->name;
                        $dateStr = $item->created_at?->format('d-m-Y') ?? '—';
                        if (preg_match('/\[(.*?)\]/', $rawName, $matches)) {
                            $dateStr = trim($matches[1]);
                            $rawName = trim(str_replace($matches[0], '', $rawName));
                        }
                    @endphp
                    <tr>
                        <td style="padding: 6px; border: 1px solid #000; text-align: center;">{{ $index + 1 }}</td>
                        <td style="padding: 6px; border: 1px solid #000;">{{ str_replace('/', '-', $dateStr) }}</td>
                        <td style="padding: 6px; border: 1px solid #000;">{{ $rawName }}</td>
                        <td style="padding: 6px; border: 1px solid #000; text-align: right;">{{ $item->quantity }}</td>
                        <td style="padding: 6px; border: 1px solid #000; text-align: right;">{{ number_format($item->unit_price, 2) }}</td>
                        <td style="padding: 6px; border: 1px solid #000; text-align: right;">{{ number_format($item->total_price ?? ($item->quantity * $item->unit_price), 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- TOTALS & PAYMENTS -->
        <table style="width:100%; border: none; border-collapse: collapse; font-size: 10pt; margin-bottom: 20px;">
            <tr>
                <td style="width: 50%; vertical-align: top;">
                    @if($admission->finalBill->payments->count() > 0)
                        <strong>Payment Details:</strong>
                        <table style="width: 90%; border: 1px solid #000; border-collapse: collapse; margin-top: 5px;">
                            <tr>
                                <th style="border: 1px solid #000; padding: 4px;">Date</th>
                                <th style="border: 1px solid #000; padding: 4px;">Method</th>
                                <th style="border: 1px solid #000; padding: 4px;">Amount</th>
                            </tr>
                            @foreach($admission->finalBill->payments as $payment)
                            <tr>
                                <td style="border: 1px solid #000; padding: 4px;">{{ $payment->received_at ? $payment->received_at->format('d-m-Y') : $payment->created_at->format('d-m-Y') }}</td>
                                <td style="border: 1px solid #000; padding: 4px;">{{ $payment->method ?? 'Cash' }}</td>
                                <td style="border: 1px solid #000; padding: 4px; text-align: right;">{{ number_format($payment->amount, 2) }}</td>
                            </tr>
                            @endforeach
                        </table>
                    @endif
                </td>
                <td style="width: 50%; vertical-align: top;">
                    <table style="width: 100%; border: 1px solid #000; border-collapse: collapse;">
                        <tr>
                            <td style="padding: 6px; border: 1px solid #000; font-weight: bold;">Subtotal</td>
                            <td style="padding: 6px; border: 1px solid #000; text-align: right;">₹{{ number_format($admission->finalBill->subtotal, 2) }}</td>
                        </tr>
                        @if($admission->finalBill->discount_amount > 0)
                        <tr>
                            <td style="padding: 6px; border: 1px solid #000; font-weight: bold;">Discount</td>
                            <td style="padding: 6px; border: 1px solid #000; text-align: right;">-₹{{ number_format($admission->finalBill->discount_amount, 2) }}</td>
                        </tr>
                        @endif
                        <tr>
                            <td style="padding: 8px; border: 1px solid #000; font-weight: bold; font-size: 12pt;">Grand Total</td>
                            <td style="padding: 8px; border: 1px solid #000; text-align: right; font-weight: bold; font-size: 12pt;">₹{{ number_format($admission->finalBill->total_amount, 2) }}</td>
                        </tr>
                        <tr>
                            <td style="padding: 6px; border: 1px solid #000; font-weight: bold;">Total Paid</td>
                            <td style="padding: 6px; border: 1px solid #000; text-align: right;">₹{{ number_format((float) $admission->finalBill->paid_amount, 2) }}</td>
                        </tr>
                        <tr>
                            <td style="padding: 8px; border: 1px solid #000; font-weight: bold; font-size: 12pt;">Balance Due</td>
                            <td style="padding: 8px; border: 1px solid #000; text-align: right; font-weight: bold; font-size: 12pt;">₹{{ number_format(max(0, (float) $admission->finalBill->balance_amount), 2) }}</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </div>
    @endif
</div>

<div style="margin-top: 40px; display: flex; justify-content: space-between;">
    <div style="text-align: center;">
        <p style="margin: 0; padding-top: 40px; border-top: 1px solid #000; display: inline-block; width: 200px;">Prepared By</p>
    </div>
    <div style="text-align: center;">
        <p style="margin: 0; padding-top: 40px; border-top: 1px solid #000; display: inline-block; width: 200px;">Authorized Signatory</p>
    </div>
</div>
@endsection
