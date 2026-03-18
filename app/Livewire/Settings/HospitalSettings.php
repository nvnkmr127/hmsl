<?php

namespace App\Livewire\Settings;

use App\Services\SettingsService;
use Livewire\Component;
use Livewire\Attributes\Validate;
use Livewire\WithFileUploads;

class HospitalSettings extends Component
{
    use WithFileUploads;

    #[Validate('required|string|max:255')]
    public $hospital_name;
    
    #[Validate('nullable|string|max:255')]
    public $hospital_tagline;

    #[Validate('required|string|max:500')]
    public $hospital_address;

    #[Validate('required|string|max:100')]
    public $hospital_city;

    #[Validate('required|string|max:100')]
    public $hospital_state;

    #[Validate('required|string|max:20')]
    public $hospital_pincode;

    #[Validate('required|string|max:20')]
    public $hospital_phone;

    #[Validate('required|email|max:100')]
    public $hospital_email;

    #[Validate('nullable|url|max:100')]
    public $hospital_website;

    public $logo;
    public $currentLogo;

    public function mount(SettingsService $settings)
    {
        $data = $settings->getGroup('hospital');
        
        $this->hospital_name = $data['hospital_name'] ?? '';
        $this->hospital_tagline = $data['hospital_tagline'] ?? '';
        $this->hospital_address = $data['hospital_address'] ?? '';
        $this->hospital_city = $data['hospital_city'] ?? '';
        $this->hospital_state = $data['hospital_state'] ?? '';
        $this->hospital_pincode = $data['hospital_pincode'] ?? '';
        $this->hospital_phone = $data['hospital_phone'] ?? '';
        $this->hospital_email = $data['hospital_email'] ?? '';
        $this->hospital_website = $data['hospital_website'] ?? '';
        $this->currentLogo = $data['hospital_logo'] ?? null;
    }

    public function save(SettingsService $settings)
    {
        $this->validate();

        $data = [
            'hospital_name' => $this->hospital_name,
            'hospital_tagline' => $this->hospital_tagline,
            'hospital_address' => $this->hospital_address,
            'hospital_city' => $this->hospital_city,
            'hospital_state' => $this->hospital_state,
            'hospital_pincode' => $this->hospital_pincode,
            'hospital_phone' => $this->hospital_phone,
            'hospital_email' => $this->hospital_email,
            'hospital_website' => $this->hospital_website,
        ];

        if ($this->logo) {
            $path = $this->logo->store('hospital', 'public');
            $data['hospital_logo'] = $path;
        }

        $settings->updateGroup('hospital', $data);

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Hospital details updated successfully!'
        ]);
    }

    public function render()
    {
        return view('livewire.settings.hospital-settings');
    }
}
