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

    public function resetFilters()
    {
        $this->reset(['gender', 'ageGroup', 'city', 'search']);
        $this->from = now()->subMonths(6)->toDateString();
        $this->to = now()->toDateString();
        $this->resetPage();
    }

    protected function getFilteredQuery()
    {
        $query = Patient::query()
            ->whereBetween('created_at', [$this->from . ' 00:00:00', $this->to . ' 23:59:59']);

        if ($this->gender) {
            $query->where('gender', $this->gender);
        }
        if ($this->city) {
            $query->where(function($q) {
                $q->where('city', 'LIKE', '%' . $this->city . '%')
                  ->orWhere('address', 'LIKE', '%' . $this->city . '%');
            });
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

    public static function extractVillage(?string $city, ?string $address): string
    {
        $ignoreWords = ['telangana', 'andhra pradesh', 'india', 'in', 'ts', 'ap', 'maharashtra', 'karnataka'];

        if (!empty($address)) {
            $parts = array_map('trim', explode(',', $address));
            foreach ($parts as $part) {
                $clean = trim($part);
                if (empty($clean)) continue;
                if (preg_match('/^(\d+|[hHDd]\.?[nN]o|#|flat|plot|room|shop|ward|street|st\.|road|rd\.)/i', $clean) && count($parts) > 1) {
                    continue;
                }
                if (in_array(strtolower($clean), $ignoreWords) && count($parts) > 1) {
                    continue;
                }
                if (strlen($clean) > 2 && !is_numeric($clean)) {
                    return ucwords(strtolower($clean));
                }
            }
        }

        if (!empty($city)) {
            $parts = array_map('trim', explode(',', $city));
            foreach ($parts as $part) {
                $clean = trim($part);
                if (!empty($clean) && strlen($clean) > 2 && !is_numeric($clean)) {
                    return ucwords(strtolower($clean));
                }
            }
        }

        return 'Nizamabad';
    }

    public function exportCSV()
    {
        $patients = $this->getFilteredQuery()->latest('created_at')->get();

        $csvHeader = ['Date', 'Time', 'UHID', 'Name', 'Gender', 'Age', 'City', 'Phone'];
        $csvData = [];
        $csvData[] = implode(',', $csvHeader);

        foreach ($patients as $patient) {
            $village = self::extractVillage($patient->city, $patient->address);
            $row = [
                $patient->created_at->format('Y-m-d'),
                $patient->created_at->format('H:i:s'),
                $patient->uhid,
                '"' . addslashes($patient->full_name) . '"',
                $patient->gender ?? 'N/A',
                $patient->age,
                '"' . addslashes($village) . '"',
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
        
        $villageList = [];
        foreach (Patient::select('city', 'address')->distinct()->get() as $p) {
            $v = self::extractVillage($p->city, $p->address);
            $villageList[$v] = true;
        }
        ksort($villageList);
        $villages = array_keys($villageList);

        $villageRevenues = [];
        $bills = \App\Models\Bill::with('patient:id,city,address')
            ->whereBetween('created_at', [$this->from . ' 00:00:00', $this->to . ' 23:59:59'])
            ->get();
        foreach ($bills as $bill) {
            if ($bill->patient) {
                $v = self::extractVillage($bill->patient->city, $bill->patient->address);
                $villageRevenues[$v] = ($villageRevenues[$v] ?? 0) + (float) $bill->paid_amount;
            }
        }

        $totalRegs = max(1, $stats['summary']['total_registrations']);
        $areaMapData = [];
        $rank = 1;
        foreach ($stats['village_distribution'] as $cityName => $count) {
            $cityRevenue = (float) ($villageRevenues[$cityName] ?? 0);

            [$lat, $lng] = $this->getCityCoordinates($cityName);

            $areaMapData[] = [
                'rank' => $rank++,
                'name' => $cityName,
                'count' => $count,
                'share' => round(($count / $totalRegs) * 100, 1),
                'revenue' => $cityRevenue,
                'avg_spend' => $count > 0 ? round($cityRevenue / $count, 2) : 0,
                'lat' => $lat,
                'lng' => $lng,
            ];
        }

        // Update charts and map dynamically
        $this->dispatch('refreshChart-reg-trend-chart', data: $stats['daily_trend']);
        $this->dispatch('refreshChart-age-dist-chart', data: $stats['age_distribution']);
        $this->dispatch('refreshChart-village-dist-chart', data: $stats['village_distribution']);
        $this->dispatch('update-map-data', areaMapData: $areaMapData);

        return view('livewire.reports.registration-report', [
            'stats' => $stats,
            'patients' => $patients,
            'villages' => $villages,
            'areaMapData' => $areaMapData,
        ]);
    }

    private function getCityCoordinates(string $cityName): array
    {
        $coordsMap = [
            'nizamabad' => [18.6725, 78.0941],
            'nizamabad district' => [18.6750, 78.1000],
            'nizamabad town' => [18.6725, 78.0941],
            'nizamabad rural' => [18.6500, 78.1200],
            'bodhan' => [18.6653, 77.8978],
            'armoor' => [18.7889, 78.2869],
            'kamareddy' => [18.3183, 78.3375],
            'dichpally' => [18.5772, 78.2045],
            'varni' => [18.5342, 77.9015],
            'banswada' => [18.3842, 77.8812],
            'navipet' => [18.8000, 78.0333],
            'renjal' => [18.8250, 77.8850],
            'ranjal' => [18.8250, 77.8850],
            'nandipet' => [18.8400, 78.1400],
            'makloor' => [18.7500, 78.0800],
            'sirikonda' => [18.4500, 78.2800],
            'kotagiri' => [18.5500, 77.7800],
            'balkonda' => [18.8682, 78.3412],
            'velpur' => [18.8000, 78.3800],
            'morthad' => [18.8500, 78.4300],
            'yedapally' => [18.6500, 77.9500],
            'yeda pally' => [18.6500, 77.9500],
            'yellareddy' => [18.2104, 78.0163],
            'indalwai' => [18.5000, 78.1800],
            'dharpally' => [18.5300, 78.2500],
            'jakranpally' => [18.7200, 78.2200],
            'rudrur' => [18.5700, 77.8700],
            'penta khurd' => [18.6400, 78.0500],
            'mopul' => [18.6000, 78.1200],
            'hyderabad' => [17.3850, 78.4867],
            'secunderabad' => [17.4399, 78.4983],
            'karimnagar' => [18.4386, 79.1288],
            'warangal' => [17.9784, 79.5941],
            'siddipet' => [18.1018, 78.8520],
            'medak' => [18.0454, 78.2612],
            'adilabad' => [19.6667, 78.5333],
        ];

        $key = strtolower(trim($cityName));
        foreach ($coordsMap as $name => $coords) {
            if (str_contains($key, $name) || str_contains($name, $key)) {
                return $coords;
            }
        }

        // Tight clustering for local villages around Nizamabad District (18.6725, 78.0941)
        $hash = abs(crc32($cityName));
        $latOffset = (($hash % 100) - 50) * 0.0015;
        $lngOffset = (((int)($hash / 100) % 100) - 50) * 0.0015;

        return [round(18.6725 + $latOffset, 4), round(78.0941 + $lngOffset, 4)];
    }
}
