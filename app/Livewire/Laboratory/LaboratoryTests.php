<?php

namespace App\Livewire\Laboratory;

use App\Models\LabTest;
use App\Models\LabParameter;
use Livewire\Component;
use Livewire\WithPagination;
use Throwable;

class LaboratoryTests extends Component
{
    use WithPagination;

    protected $paginationTheme = 'tailwind';

    public $search = '';
    public $categoryFilter = 'All';

    public $selectedTestId;
    public $testName, $testCategory, $testPrice, $testDescription;

    public $parameters = []; // For the test definition

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function openCreate()
    {
        $this->reset(['testName', 'testCategory', 'testPrice', 'testDescription', 'selectedTestId', 'parameters']);
        $this->parameters = [];
        $this->dispatch('open-modal', ['name' => 'test-modal']);
    }

    public function edit($id)
    {
        $test = LabTest::with('parameters')->findOrFail($id);
        $this->selectedTestId = $id;
        $this->testName = $test->name;
        $this->testCategory = $test->category;
        $this->testPrice = $test->price;
        $this->testDescription = $test->description;
        
        $this->parameters = $test->parameters->map(fn($p) => [
            'id' => $p->id,
            'name' => $p->name,
            'unit' => $p->unit,
            'reference_range' => $p->reference_range
        ])->toArray();

        $this->dispatch('open-modal', ['name' => 'test-modal']);
    }

    public function addParameter()
    {
        $this->parameters[] = ['name' => '', 'unit' => '', 'reference_range' => ''];
    }

    public function removeParameter($index)
    {
        unset($this->parameters[$index]);
        $this->parameters = array_values($this->parameters);
    }

    public function save()
    {
        $this->validate([
            'testName' => 'required|string|max:255',
            'testCategory' => 'nullable|string|max:100',
            'testPrice' => 'required|numeric|min:0',
            'parameters.*.name' => 'required|string|max:255',
        ]);

        try {
            $test = $this->selectedTestId 
                ? LabTest::findOrFail($this->selectedTestId) 
                : new LabTest();

            $test->fill([
                'name' => $this->testName,
                'category' => $this->testCategory,
                'price' => $this->testPrice,
                'description' => $this->testDescription,
            ])->save();

            // Sync parameters
            $currentIds = collect($this->parameters)->pluck('id')->filter()->toArray();
            $test->parameters()->whereNotIn('id', $currentIds)->delete();

            foreach ($this->parameters as $p) {
                if (isset($p['id'])) {
                    LabParameter::findOrFail($p['id'])->update($p);
                } else {
                    $test->parameters()->create($p);
                }
            }

            $this->dispatch('close-modal', ['name' => 'test-modal']);
            $this->dispatch('notify', ['type' => 'success', 'message' => 'Lab test definition saved!']);
            $this->reset(['selectedTestId', 'testName', 'testCategory', 'testPrice', 'testDescription', 'parameters']);
        } catch (Throwable $e) {
            report($e);
            $this->dispatch('notify', ['type' => 'error', 'message' => 'Failed to save test definition.']);
        }
    }

    public function render()
    {
        $tests = LabTest::with('parameters')
            ->when($this->search, fn($q) => $q->where('name', 'like', '%' . $this->search . '%'))
            ->when($this->categoryFilter !== 'All', fn($q) => $q->where('category', $this->categoryFilter))
            ->latest()
            ->paginate(10);

        $categories = LabTest::select('category')->distinct()->pluck('category');

        return view('livewire.laboratory.laboratory-tests', compact('tests', 'categories'));
    }
}
