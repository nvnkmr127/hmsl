<?php

namespace App\Livewire\Master;

use App\Models\LabTest;
use App\Services\LabManager;
use Livewire\Attributes\On;
use Livewire\Attributes\Validate;
use Livewire\Component;

class LabForm extends Component
{
    public $isEditing = false;
    public $testId;

    #[Validate('nullable|string|max:50')]
    public $code;

    #[Validate('required|string|max:255')]

    public $name;

    #[Validate('required|string|max:100')]
    public $category;

    #[Validate('required|numeric|min:0')]
    public $price;

    #[Validate('nullable|string|max:1000')]
    public $description;

    public $is_active = true;

    public $parameters = [];

    protected function rules()
    {
        return [
            'parameters.*.name' => 'required|string|max:255',
            'parameters.*.unit' => 'nullable|string|max:50',
            'parameters.*.reference_range' => 'nullable|string|max:255',
        ];
    }

    public function addParameter()
    {
        $this->parameters[] = [
            'name' => '',
            'unit' => '',
            'reference_range' => ''
        ];
    }

    public function removeParameter($index)
    {
        unset($this->parameters[$index]);
        $this->parameters = array_values($this->parameters);
    }

    #[On('edit-lab-test')]
    public function edit($id)
    {
        $this->isEditing = true;
        $this->testId = $id;
        
        $test = LabTest::with('parameters')->findOrFail($id);
        $this->code = $test->code;
        $this->name = $test->name;
        $this->category = $test->category;
        $this->price = $test->price;
        $this->description = $test->description;
        $this->is_active = $test->is_active;

        
        $this->parameters = $test->parameters->map(fn($p) => [
            'name' => $p->name,
            'unit' => $p->unit,
            'reference_range' => $p->reference_range
        ])->toArray();

        $this->dispatch('open-modal', name: 'lab-modal');
    }

    #[On('create-lab-test')]
    public function create()
    {
        $this->reset(['code', 'name', 'category', 'price', 'description', 'is_active', 'parameters', 'testId', 'isEditing']);

        $this->resetValidation();
        $this->addParameter(); // Start with one parameter field
        $this->dispatch('open-modal', name: 'lab-modal');
    }

    public function save(LabManager $manager)
    {
        $this->validate();

        $testData = [
            'code' => $this->code,
            'name' => $this->name,
            'category' => $this->category,
            'price' => $this->price,
            'description' => $this->description,
            'is_active' => $this->is_active,
        ];


        if ($this->isEditing) {
            $test = LabTest::findOrFail($this->testId);
            $manager->updateTest($test, $testData, $this->parameters);
        } else {
            $manager->createTest($testData, $this->parameters);
        }

        $this->dispatch('close-modal', name: 'lab-modal');
        $this->dispatch('lab-updated');
        
        $this->dispatch('notify', [
            'type' => 'success',
            'message' => $this->isEditing ? 'Lab test updated!' : 'Lab test created!'
        ]);
    }

    public function render()
    {
        return view('livewire.master.lab-form');
    }
}
