<?php

namespace App\Services;

use App\Models\Admission;
use App\Models\Bill;
use App\Models\BillItem;
use App\Models\DischargeSummary;
use App\Models\IpdMedicationChart;
use App\Models\LabOrder;
use App\Models\ProcedureCharge;
use Illuminate\Support\Facades\DB;

class IpdBillingService
{
    public function buildFinalBillItems(Admission $admission): array
    {
        $items = [];

        $ward = $admission->bed?->ward;
        $bed = $admission->bed;

        if ($ward && $bed) {
            $daysAdmitted = max(1, (int) $admission->admission_date->diffInDays($admission->discharge_date ?? now()));
            $bedCharge = $bed->per_day_charge * $daysAdmitted;

            $items[] = [
                'item_id' => 'ward_' . $ward->id,
                'item_type' => 'Ward Charges',
                'item_name' => $ward->name . ' - ' . $bed->bed_number . ' (' . $daysAdmitted . ' days)',
                'quantity' => $daysAdmitted,
                'rate' => $bed->per_day_charge,
                'amount' => $bedCharge,
            ];
        }

        $consultationDays = $this->getConsultationDays($admission);
        if ($consultationDays > 0) {
            $doctorCharge = ($admission->doctor?->consultation_charge ?? 500) * $consultationDays;

            $items[] = [
                'item_id' => 'doctor_visit',
                'item_type' => 'Doctor Visit',
                'item_name' => 'Doctor Visit Charges (' . $consultationDays . ' days)',
                'quantity' => $consultationDays,
                'rate' => $admission->doctor?->consultation_charge ?? 500,
                'amount' => $doctorCharge,
            ];
        }

        $procedureCharges = ProcedureCharge::where('admission_id', $admission->id)
            ->where('status', 'Performed')
            ->whereNull('bill_item_id')
            ->get();

        foreach ($procedureCharges as $proc) {
            $items[] = [
                'item_id' => 'procedure_' . $proc->id,
                'item_type' => 'Procedure',
                'item_name' => $proc->procedure_name,
                'quantity' => $proc->quantity,
                'rate' => $proc->charge,
                'amount' => $proc->charge * $proc->quantity,
            ];
        }

        $labOrders = LabOrder::where('admission_id', $admission->id)
            ->where('status', 'Completed')
            ->whereNull('bill_item_id')
            ->get();

        foreach ($labOrders as $lab) {
            $items[] = [
                'item_id' => 'lab_' . $lab->id,
                'item_type' => 'Lab',
                'item_name' => $lab->labTest?->name ?? 'Lab Test',
                'quantity' => 1,
                'rate' => $lab->labTest?->price ?? 0,
                'amount' => $lab->labTest?->price ?? 0,
            ];
        }

        $ipdMeds = IpdMedicationChart::where('admission_id', $admission->id)
            ->whereIn('status', ['Active', 'Stopped', 'Completed'])
            ->whereNull('bill_item_id')
            ->get();

        foreach ($ipdMeds as $med) {
            $medicine = $med->medicine;
            $price = $medicine?->price ?? 0;
            $qty = $this->calculateMedicationQuantity($med);

            $items[] = [
                'item_id' => 'ipd_med_' . $med->id,
                'item_type' => 'Pharmacy',
                'item_name' => $med->medicine_name . ($med->dosage ? ' - ' . $med->dosage : ''),
                'quantity' => $qty,
                'rate' => $price,
                'amount' => $price * $qty,
            ];
        }

        return $items;
    }

    protected function getConsultationDays(Admission $admission): int
    {
        $start = $admission->admission_date;
        $end = $admission->discharge_date ?? now();

        return max(1, (int) $start->diffInDays($end) + 1);
    }

    protected function calculateMedicationQuantity(IpdMedicationChart $med): int
    {
        $start = $med->start_date;
        $end = $med->end_date ?? ($med->stopped_at ?? now());

        $days = max(1, (int) $start->diffInDays($end));

        $freqMultiplier = match ($med->frequency) {
            'OD', 'Once daily' => 1,
            'BD', 'Twice daily' => 2,
            'TDS', 'Three times daily' => 3,
            'QID', 'Four times daily' => 4,
            'SOS', 'PRN' => 1,
            default => 1,
        };

        return $days * $freqMultiplier;
    }

    public function generateFinalBill(Admission $admission): Bill
    {
        return DB::transaction(function () use ($admission) {
            $items = $this->buildFinalBillItems($admission);

            $totalAmount = collect($items)->sum('amount');

            $existingBill = Bill::where('admission_id', $admission->id)
                ->where('bill_type', 'Final')
                ->first();

            if ($existingBill) {
                BillItem::where('bill_id', $existingBill->id)->delete();
                $existingBill->update([
                    'total_amount' => $totalAmount,
                    'tax_amount' => $totalAmount * 0.18,
                    'discount_amount' => 0,
                    'net_amount' => $totalAmount * 1.18,
                ]);
                $bill = $existingBill;
            } else {
                $bill = Bill::create([
                    'bill_number' => Bill::generateBillNumber('FIN'),
                    'patient_id' => $admission->patient_id,
                    'admission_id' => $admission->id,
                    'bill_type' => 'Final',
                    'total_amount' => $totalAmount,
                    'tax_amount' => $totalAmount * 0.18,
                    'discount_amount' => 0,
                    'net_amount' => $totalAmount * 1.18,
                    'paid_amount' => 0,
                    'balance_amount' => $totalAmount * 1.18,
                    'payment_status' => 'Unpaid',
                ]);
            }

            foreach ($items as $item) {
                BillItem::create([
                    'bill_id' => $bill->id,
                    'item_id' => $item['item_id'],
                    'item_type' => $item['item_type'],
                    'item_name' => $item['item_name'],
                    'quantity' => $item['quantity'],
                    'rate' => $item['rate'],
                    'amount' => $item['amount'],
                ]);
            }

            ProcedureCharge::where('admission_id', $admission->id)
                ->where('status', 'Performed')
                ->whereNull('bill_item_id')
                ->update(['bill_item_id' => $bill->id]);

            return $bill->load('items');
        });
    }
}
