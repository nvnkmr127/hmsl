<?php

namespace App\Services;

use Carbon\Carbon;

class GrowthChartService
{
    /**
     * Image 1: Infant Growth Chart (0-12 months)
     * Format: [month => [w_min, w_max, h_min, h_max]]
     */
    protected const INFANT_CHART_BOYS = [
        0 => [2.5, 4.3, 46.3, 53.4],
        1 => [3.4, 5.7, 51.1, 58.4],
        2 => [4.4, 7.0, 54.7, 62.2],
        3 => [5.1, 7.9, 57.6, 65.3],
        4 => [5.6, 8.6, 60.0, 67.8],
        5 => [6.1, 9.2, 61.9, 69.9],
        6 => [6.4, 9.7, 63.6, 71.6],
        7 => [6.7, 10.2, 65.1, 73.2],
        8 => [7.0, 10.5, 66.5, 74.7],
        9 => [7.2, 10.9, 67.7, 76.2],
        10 => [7.5, 11.2, 67.7, 76.2],
        11 => [7.4, 11.5, 70.2, 78.9],
        12 => [7.8, 11.8, 71.3, 80.2],
    ];

    protected const INFANT_CHART_GIRLS = [
        0 => [2.4, 4.2, 45.6, 52.7],
        1 => [3.2, 5.4, 50.0, 57.4],
        2 => [4.0, 6.5, 53.2, 60.9],
        3 => [4.6, 7.4, 55.8, 63.8],
        4 => [5.1, 8.1, 58.0, 66.2],
        5 => [5.5, 8.7, 59.9, 68.2],
        6 => [5.8, 9.2, 61.5, 70.0],
        7 => [6.1, 9.6, 62.9, 71.6],
        8 => [6.3, 10.0, 64.3, 73.2],
        9 => [6.6, 10.4, 65.6, 74.7],
        10 => [6.8, 10.7, 66.8, 76.1],
        11 => [7.0, 11.0, 68.0, 77.5],
        12 => [7.1, 11.3, 69.2, 78.9],
    ];

    /**
     * Image 2: Child Growth Chart (1-20 years)
     * Format: [start_year => [w_min, w_max, h_min_cm, h_max_cm]]
     */
    protected const CHILD_CHART = [
        1 => [9.5, 12, 75.2, 84.8],    // 1-2y
        2 => [12, 15, 75.2, 85.6],     // 2-4y
        4 => [15.4, 20, 100.3, 115.6], // 4-6y
        6 => [19.5, 25.5, 115.6, 128.3], // 6-8y
        8 => [25.5, 31.9, 128.3, 139.7], // 8-10y
        10 => [32, 41.5, 138.4, 149.9],  // 10-12y
        12 => [42, 47.6, 152.4, 158.8],  // 12-14y
        14 => [45, 53, 158.8, 162.6],    // 14-16y
        16 => [53, 56.7, 162.6, 163.1],  // 16-18y
        18 => [56, 58, 163.1, 163.8],    // 18-20y
    ];

    public function getGrowthStatus($patient, $actualWeight, $actualHeight = null)
    {
        if (!$patient || (!$actualWeight && !$actualHeight)) {
            return null;
        }

        $dob = Carbon::parse($patient->date_of_birth);
        $gender = strtolower($patient->gender);
        $now = Carbon::now();
        
        $totalMonths = (int)$dob->diffInMonths($now);
        $years = (int)$dob->diffInYears($now);

        $isInfant = $totalMonths <= 12;
        $chartData = [];
        $expectedRangeWeight = null;
        $expectedRangeHeight = null;
        
        if ($isInfant) {
            $rawChart = ($gender === 'female') ? self::INFANT_CHART_GIRLS : self::INFANT_CHART_BOYS;
            $data = $rawChart[$totalMonths] ?? null;
            if ($data) {
                $expectedRangeWeight = [$data[0], $data[1], ($data[0] + $data[1]) / 2];
                $expectedRangeHeight = [$data[2], $data[3], ($data[2] + $data[3]) / 2];
            }
            $ageLabel = "$totalMonths months";
            $ageValue = $totalMonths;
        } else {
            // Find appropriate range in CHILD_CHART
            $data = null;
            foreach (array_reverse(self::CHILD_CHART, true) as $startYear => $values) {
                if ($years >= $startYear) {
                    $data = $values;
                    break;
                }
            }
            if ($data) {
                $expectedRangeWeight = [$data[0], $data[1], ($data[0] + $data[1]) / 2];
                $expectedRangeHeight = [$data[2], $data[3], ($data[2] + $data[3]) / 2];
            }
            $ageLabel = "$years years";
            $ageValue = $years;
        }

        $weightStatus = $this->calculateStatus($actualWeight, $expectedRangeWeight);
        $heightStatus = $this->calculateStatus($actualHeight, $expectedRangeHeight);

        return [
            'age_label' => $ageLabel,
            'actual_weight' => $actualWeight,
            'actual_height' => $actualHeight,
            'weight' => [
                'expected_range' => $expectedRangeWeight ? "{$expectedRangeWeight[0]} - {$expectedRangeWeight[1]} kg" : 'N/A',
                'expected_value' => $expectedRangeWeight ? number_format($expectedRangeWeight[2], 1) : 'N/A',
                'status' => $weightStatus['status'],
                'status_color' => $weightStatus['status_color'],
            ],
            'height' => [
                'expected_range' => $expectedRangeHeight ? "{$expectedRangeHeight[0]} - {$expectedRangeHeight[1]} cm" : 'N/A',
                'expected_value' => $expectedRangeHeight ? number_format($expectedRangeHeight[2], 1) : 'N/A',
                'status' => $heightStatus['status'],
                'status_color' => $heightStatus['status_color'],
            ],
            'chart_data' => $this->prepareChartData($isInfant ? 'infant' : 'child', $gender, $ageValue, $actualWeight, $actualHeight)
        ];
    }

    public function getGrowthForecast($patient)
    {
        if (!$patient) return null;

        $dob = Carbon::parse($patient->date_of_birth);
        $gender = strtolower($patient->gender);
        $now = Carbon::now();
        
        $totalMonths = (int)$dob->diffInMonths($now);
        $years = (int)$dob->diffInYears($now);

        $isInfant = $totalMonths < 12;
        $milestones = [];

        if ($isInfant) {
            // Forecast for next 1, 3, 6 months
            $offsets = [1, 3, 6];
            foreach ($offsets as $offset) {
                $futureMonths = $totalMonths + $offset;
                if ($futureMonths <= 12) {
                    $milestones[] = [
                        'label' => "{$futureMonths}m",
                        'data' => $this->getMedianForAge('infant', $gender, $futureMonths)
                    ];
                }
            }
        } else {
            // Forecast for next 1, 3, 5 years
            $offsets = [1, 3, 5];
            foreach ($offsets as $offset) {
                $futureYears = $years + $offset;
                if ($futureYears <= 20) {
                    $milestones[] = [
                        'label' => "{$futureYears}y",
                        'data' => $this->getMedianForAge('child', $gender, $futureYears)
                    ];
                }
            }
        }

        return $milestones;
    }

    protected function getMedianForAge($type, $gender, $age)
    {
        $data = null;
        if ($type === 'infant') {
            $rawChart = ($gender === 'female') ? self::INFANT_CHART_GIRLS : self::INFANT_CHART_BOYS;
            $data = $rawChart[$age] ?? null;
        } else {
            foreach (array_reverse(self::CHILD_CHART, true) as $startYear => $values) {
                if ($age >= $startYear) {
                    $data = $values;
                    break;
                }
            }
        }

        if (!$data) return null;

        return [
            'weight' => ($data[0] + $data[1]) / 2,
            'height' => ($data[2] + $data[3]) / 2
        ];
    }

    protected function calculateStatus($actual, $expected)
    {
        if (!$actual || !$expected) {
            return ['status' => 'N/A', 'status_color' => 'text-gray-400'];
        }

        [$min, $max, $median] = $expected;

        if ($actual < $min) {
            return ['status' => 'Under', 'status_color' => 'text-red-600'];
        } elseif ($actual > $max) {
            return ['status' => 'Over', 'status_color' => 'text-orange-600'];
        }
        
        return ['status' => 'Normal', 'status_color' => 'text-green-600'];
    }

    protected function prepareChartData($type, $gender, $currentAge, $actualWeight, $actualHeight)
    {
        $rawChart = [];
        if ($type === 'infant') {
            $rawChart = ($gender === 'female') ? self::INFANT_CHART_GIRLS : self::INFANT_CHART_BOYS;
        } else {
            $rawChart = self::CHILD_CHART;
        }

        $labels = [];
        $wMin = []; $wMax = []; $wMedian = []; $wActual = [];
        $hMin = []; $hMax = []; $hMedian = []; $hActual = [];

        foreach ($rawChart as $age => $values) {
            $labels[] = $type === 'infant' ? "{$age}m" : "{$age}y";
            $wMin[] = $values[0];
            $wMax[] = $values[1];
            $wMedian[] = ($values[0] + $values[1]) / 2;
            
            $hMin[] = $values[2];
            $hMax[] = $values[3];
            $hMedian[] = ($values[2] + $values[3]) / 2;
            
            if ($age == $currentAge) {
                $wActual[] = $actualWeight;
                $hActual[] = $actualHeight;
            } else {
                $wActual[] = null;
                $hActual[] = null;
            }
        }

        return [
            'labels' => $labels,
            'weight' => ['min' => $wMin, 'max' => $wMax, 'median' => $wMedian, 'actual' => $wActual],
            'height' => ['min' => $hMin, 'max' => $hMax, 'median' => $hMedian, 'actual' => $hActual],
            'current_age_index' => array_search($type === 'infant' ? "{$currentAge}m" : "{$currentAge}y", $labels)
        ];
    }
}
