<?php

namespace App\Services;

use App\Models\LabTest;
use App\Models\LabParameter;

class LabService
{
    public function getAllTests()
    {
        return LabTest::with('parameters')->latest()->get();
    }

    public function createTest(array $testData, array $parameters = [])
    {
        $test = LabTest::create($testData);
        
        foreach ($parameters as $param) {
            $test->parameters()->create($param);
        }

        return $test;
    }

    public function updateTest(LabTest $test, array $testData, array $parameters = [])
    {
        $test->update($testData);
        
        // Simple sync strategy for parameters
        $test->parameters()->delete();
        foreach ($parameters as $param) {
            $test->parameters()->create($param);
        }

        return $test;
    }

    public function toggleActive(LabTest $test)
    {
        $test->update(['is_active' => !$test->is_active]);
        return $test;
    }

    public function deleteTest(LabTest $test)
    {
        return $test->delete();
    }
}
