<?php

namespace App\Services;

use App\Models\Admission;
use App\Models\DischargeMedication;
use App\Models\DischargeSummary;
use App\Models\IpdMedicationChart;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class DischargeSummaryService
{
    public function createDraft(Admission $admission): DischargeSummary
    {
        return DB::transaction(function () use ($admission) {
            $existing = DischargeSummary::where('admission_id', $admission->id)->first();

            if ($existing) {
                return $existing;
            }

            $summary = DischargeSummary::create([
                'admission_id' => $admission->id,
                'patient_id' => $admission->patient_id,
                'doctor_id' => $admission->doctor_id,
                'created_by' => Auth::id(),
                'admission_number' => $admission->admission_number,
                'uhid' => $admission->patient->uhid,
                'admission_date' => $admission->admission_date,
                'discharge_date' => now(),
                'admission_diagnosis' => $admission->reason_for_admission,
                'status' => 'Draft',
                'is_finalized' => false,
            ]);

            $this->copyMedicationsFromChart($summary, $admission);

            return $summary;
        });
    }

    public function copyMedicationsFromChart(DischargeSummary $summary, Admission $admission): void
    {
        $activeMeds = IpdMedicationChart::where('admission_id', $admission->id)
            ->where('status', 'Active')
            ->get();

        foreach ($activeMeds as $med) {
            DischargeMedication::create([
                'discharge_summary_id' => $summary->id,
                'medicine_id' => $med->medicine_id,
                'medicine_name' => $med->medicine_name,
                'dosage' => $med->dosage,
                'frequency' => $med->frequency,
                'duration' => $med->duration,
                'route' => $med->route,
                'instructions' => $med->instructions,
                'is_continued' => true,
            ]);
        }
    }

    public function update(DischargeSummary $summary, array $data): DischargeSummary
    {
        if ($summary->is_finalized) {
            throw new \RuntimeException('Cannot update a finalized discharge summary.');
        }

        $summary->update($data);

        return $summary->fresh();
    }

    public function addMedication(DischargeSummary $summary, array $medicationData): DischargeMedication
    {
        if ($summary->is_finalized) {
            throw new \RuntimeException('Cannot add medication to a finalized discharge summary.');
        }

        return DischargeMedication::create(array_merge($medicationData, [
            'discharge_summary_id' => $summary->id,
        ]));
    }

    public function removeMedication(DischargeMedication $medication): void
    {
        if ($medication->dischargeSummary->is_finalized) {
            throw new \RuntimeException('Cannot remove medication from a finalized discharge summary.');
        }

        $medication->delete();
    }

    public function submitForReview(DischargeSummary $summary): DischargeSummary
    {
        if ($summary->status !== 'Draft') {
            throw new \RuntimeException('Only draft summaries can be submitted for review.');
        }

        $summary->update(['status' => 'Review']);

        return $summary;
    }

    public function returnToDraft(DischargeSummary $summary): DischargeSummary
    {
        if ($summary->status !== 'Review') {
            throw new \RuntimeException('Only review summaries can be returned to draft.');
        }

        $summary->update(['status' => 'Draft']);

        return $summary;
    }

    public function finalize(DischargeSummary $summary, User $user): DischargeSummary
    {
        if (!$summary->canFinalize()) {
            throw new \RuntimeException('This summary cannot be finalized.');
        }

        $summary->markAsFinalized($user);

        return $summary;
    }

    public function getForAdmission(Admission $admission): ?DischargeSummary
    {
        return DischargeSummary::where('admission_id', $admission->id)
            ->with(['medications', 'doctor', 'patient'])
            ->first();
    }
}
