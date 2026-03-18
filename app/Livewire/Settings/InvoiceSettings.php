<?php

namespace App\Livewire\Settings;

use App\Services\SettingsService;
use Livewire\Component;
use Livewire\Attributes\Validate;

class InvoiceSettings extends Component
{
    #[Validate('required|string|max:500')]
    public $invoice_header;
    
    #[Validate('required|string|max:500')]
    public $invoice_footer;

    #[Validate('required|boolean')]
    public $show_tax;

    #[Validate('required|numeric|min:0|max:100')]
    public $tax_percentage;

    #[Validate('required|string')]
    public $print_paper_size;

    public function mount(SettingsService $settings)
    {
        $data = $settings->getGroup('invoice');
        
        $this->invoice_header = $data['invoice_header'] ?? 'Invoice Header';
        $this->invoice_footer = $data['invoice_footer'] ?? 'Thank you!';
        $this->show_tax = filter_var($data['show_tax'] ?? true, FILTER_VALIDATE_BOOLEAN);
        $this->tax_percentage = $data['tax_percentage'] ?? '18';
        $this->print_paper_size = $data['print_paper_size'] ?? 'A4';
    }

    public function save(SettingsService $settings)
    {
        $this->validate();

        $data = [
            'invoice_header' => $this->invoice_header,
            'invoice_footer' => $this->invoice_footer,
            'show_tax' => $this->show_tax,
            'tax_percentage' => $this->tax_percentage,
            'print_paper_size' => $this->print_paper_size,
        ];

        $settings->updateGroup('invoice', $data);

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Invoice and print settings updated successfully!'
        ]);
    }

    public function render()
    {
        return view('livewire.settings.invoice-settings');
    }
}
