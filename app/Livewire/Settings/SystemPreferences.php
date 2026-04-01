<?php

namespace App\Livewire\Settings;

use App\Services\SettingsService;
use Livewire\Component;
use Livewire\Attributes\Validate;

class SystemPreferences extends Component
{
    #[Validate('required|string|max:10')]
    public $currency_symbol;
    
    #[Validate('required|string|max:50')]
    public $currency_name;

    #[Validate('required|string|max:100')]
    public $timezone;

    #[Validate('required|string|max:50')]
    public $date_format;

    #[Validate('required|string|max:10')]
    public $financial_year_start;

    #[Validate('required|string|max:20')]
    public $uhid_prefix;

    #[Validate('required|string|max:20')]
    public $invoice_prefix;

    #[Validate('required|integer|min:0')]
    public $opd_validity_days;

    #[Validate('required|numeric|min:0')]
    public $consultation_fee_default;


    public function mount(SettingsService $settings)
    {
        $data = $settings->getGroup('system');
        
        $this->currency_symbol = $data['currency_symbol'] ?? '₹';
        $this->currency_name = $data['currency_name'] ?? 'INR';
        $this->timezone = $data['timezone'] ?? 'Asia/Kolkata';
        $this->date_format = $data['date_format'] ?? 'd/m/Y';
        $this->financial_year_start = $data['financial_year_start'] ?? '04-01';
        $this->uhid_prefix = $data['uhid_prefix'] ?? 'HMS-';
        $this->invoice_prefix = $data['invoice_prefix'] ?? 'INV-';
        $this->opd_validity_days = $data['opd_validity_days'] ?? '7';
        $this->consultation_fee_default = $data['consultation_fee_default'] ?? '500';

    }

    public function save(SettingsService $settings)
    {
        $this->validate();

        $data = [
            'currency_symbol' => $this->currency_symbol,
            'currency_name' => $this->currency_name,
            'timezone' => $this->timezone,
            'date_format' => $this->date_format,
            'financial_year_start' => $this->financial_year_start,
            'uhid_prefix' => $this->uhid_prefix,
            'invoice_prefix' => $this->invoice_prefix,
            'opd_validity_days' => $this->opd_validity_days,
            'consultation_fee_default' => $this->consultation_fee_default,

        ];

        $settings->updateGroup('system', $data);

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'System preferences updated successfully!'
        ]);
    }

    public function render()
    {
        return view('livewire.settings.system-preferences');
    }
}
