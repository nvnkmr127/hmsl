<?php

namespace App\Livewire\Reports;

use App\DTOs\ReportFilter;
use App\Services\ReportService;
use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Patient;

class RegistrationReport extends Component
{
    use WithPagination;

    public $from;
    public $to;
    public $gender;
    public $ageGroup;
    public $city;
    public $search = '';
    
    protected $queryString = [
        'from' => ['except' => ''],
        'to' => ['except' => ''],
        'gender' => ['except' => ''],
        'ageGroup' => ['except' => ''],
        'city' => ['except' => ''],
        'search' => ['except' => ''],
    ];

    public function mount()
    {
        $this->from = $this->from ?: now()->subMonths(6)->toDateString();
        $this->to = $this->to ?: now()->toDateString();
    }

    public function updated($property)
    {
        if (in_array($property, ['from', 'to', 'gender', 'ageGroup', 'city', 'search'])) {
            $this->resetPage();
        }
    }

    protected function getFilteredQuery()
    {
        $query = Patient::query()
            ->whereBetween('created_at', [$this->from . ' 00:00:00', $this->to . ' 23:59:59']);

        if ($this->gender) {
            $query->where('gender', $this->gender);
        }
        if ($this->city) {
            $query->where('city', $this->city);
        }
        if ($this->ageGroup) {
            $now = \Carbon\Carbon::now();
            switch ($this->ageGroup) {
                case '0-1 Year':
                    $query->whereBetween('date_of_birth', [$now->copy()->subYear(), $now]);
                    break;
                case '1-5 Years':
                    $query->whereBetween('date_of_birth', [$now->copy()->subYears(5), $now->copy()->subYear()]);
                    break;
                case '5-12 Years':
                    $query->whereBetween('date_of_birth', [$now->copy()->subYears(12), $now->copy()->subYears(5)]);
                    break;
                case '12+ Years':
                    $query->where('date_of_birth', '<=', $now->copy()->subYears(12));
                    break;
            }
        }
        if (!empty($this->search)) {
            $query->search($this->search);
        }

        return $query;
    }

    public function exportCSV()
    {
        $patients = $this->getFilteredQuery()->latest('created_at')->get();

        $csvHeader = ['Date', 'Time', 'UHID', 'Name', 'Gender', 'Age', 'City', 'Phone'];
        $csvData = [];
        $csvData[] = implode(',', $csvHeader);

        foreach ($patients as $patient) {
            $row = [
                $patient->created_at->format('Y-m-d'),
                $patient->created_at->format('H:i:s'),
                $patient->uhid,
                '"' . addslashes($patient->full_name) . '"',
                $patient->gender ?? 'N/A',
                $patient->age,
                '"' . addslashes($patient->city ?? '') . '"',
                $patient->phone ?? 'N/A',
            ];
            $csvData[] = implode(',', $row);
        }

        $csvContent = implode("\n", $csvData);

        return response()->streamDownload(function () use ($csvContent) {
            echo $csvContent;
        }, 'registration_metrics_' . now()->format('Y-m-d_His') . '.csv');
    }

    public function render(ReportService $reportService)
    {
        $filter = new ReportFilter(
            from: $this->from,
            to: $this->to,
            gender: $this->gender,
            ageGroup: $this->ageGroup,
            city: $this->city,
        );

        $stats = $reportService->getRegistrationStats($filter);
        $query = $this->getFilteredQuery();

        $patients = $query->latest('created_at')->paginate(10);
        $villages = Patient::select('city')->whereNotNull('city')->where('city', '!=', '')->distinct()->pluck('city');

        // Update charts dynamically because they are inside wire:ignore
        $this->dispatch('refreshChart-reg-trend-chart', data: $stats['daily_trend']);
        $this->dispatch('refreshChart-age-dist-chart', data: $stats['age_distribution']);
        $this->dispatch('refreshChart-village-dist-chart', data: $stats['village_distribution']);

        return view('livewire.reports.registration-report', [
            'stats' => $stats,
            'patients' => $patients,
            'villages' => $villages,
        ]);
    }
}
