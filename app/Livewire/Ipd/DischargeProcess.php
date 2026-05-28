<?php

namespace App\Livewire\Ipd;

use App\Models\Admission;
use App\Models\Ward;
use App\Services\IpdService;
use App\Services\BillingService;
use Livewire\Component;
use Illuminate\Support\Carbon;

class DischargeProcess extends Component
{
    public Admission $admission;
    public $hideTrigger = false;

    public $bedCharges = [];
    public $ipServiceCharges = [];
    public $existingCharges = [];
    
    public $selectedExistingCharges = [];
    public $totalAmount = 0;

    public function mount(Admission $admission)
    {
        $this->admission = $admission;
        $this->initCharges();
    }

    public function initCharges()
    {
        $this->bedCharges = [];
        $this->existingCharges = [];
        $this->selectedExistingCharges = [];

        $ipdService = app(IpdService::class);
        $rawItems = $ipdService->buildFinalBillItems($this->admission);

        $wards = Ward::all()->keyBy('id');

        // Separate other charges
        foreach ($rawItems as $index => $item) {
            if ($item['type'] !== 'IPD') {
                $item['id'] = 'charge_' . $index;
                $item['selected'] = true;
                $this->existingCharges[] = $item;
                $this->selectedExistingCharges[] = $item['id'];
            }
        }

        $bedHistories = \App\Models\AdmissionBedHistory::with(['bed.ward'])->where('admission_id', $this->admission->id)->get();
        if ($bedHistories->isNotEmpty()) {
            foreach ($bedHistories as $history) {
                $start = Carbon::parse($history->start_time);
                $end = $history->end_time ? Carbon::parse($history->end_time) : now();
                
                $stayHours = max(0, $start->diffInHours($end));
                $fullDays = floor($stayHours / 24);
                $remainderHours = $stayHours % 24;
                
                if ($stayHours == 0) {
                    $stayDays = 0.5;
                } else {
                    if ($remainderHours > 0 && $remainderHours <= 12) {
                        $stayDays = $fullDays + 0.5;
                    } elseif ($remainderHours > 12) {
                        $stayDays = $fullDays + 1;
                    } else {
                        $stayDays = $fullDays;
                    }
                }
                
                $ward = $history->bed?->ward;
                $bed = $history->bed;
                $dailyCharge = (float) ($history->daily_charge ?? $bed?->per_day_charge ?? $ward?->daily_charge ?? 0);

                $this->bedCharges[] = [
                    'ward_id' => $ward?->id ?? '',
                    'name' => ($ward?->name ?? 'Ward') . ($bed ? ' - ' . $bed->bed_number : ''),
                    'start_date' => $start->format('Y-m-d\TH:i'),
                    'end_date' => $end->format('Y-m-d\TH:i'),
                    'days' => $stayDays,
                    'price' => $dailyCharge,
                    'total' => $stayDays * $dailyCharge
                ];
            }
        }
        
        if (empty($this->bedCharges)) {
            $this->addBedCharge();
        }

        $this->calculateTotal();
    }

    public function addBedCharge()
    {
        $this->bedCharges[] = [
            'ward_id' => '',
            'name' => '',
            'start_date' => now()->subDay()->format('Y-m-d\TH:i'),
            'end_date' => now()->format('Y-m-d\TH:i'),
            'days' => 1,
            'price' => 0,
            'total' => 0
        ];
    }

    public function removeBedCharge($index)
    {
        unset($this->bedCharges[$index]);
        $this->bedCharges = array_values($this->bedCharges);
        $this->calculateTotal();
    }

    public function updatedBedCharges($value, $key)
    {
        $parts = explode('.', $key);
        if (count($parts) >= 2) {
            $index = $parts[0];
            $field = $parts[1];

            if (isset($this->bedCharges[$index])) {
                $charge = &$this->bedCharges[$index];

                if ($field === 'ward_id' && !empty($charge['ward_id'])) {
                    $ward = Ward::find($charge['ward_id']);
                    if ($ward) {
                        $charge['name'] = $ward->name;
                        $charge['price'] = (float) $ward->daily_charge;
                    }
                }

                if (in_array($field, ['start_date', 'end_date'])) {
                    if (!empty($charge['start_date']) && !empty($charge['end_date'])) {
                        $start = Carbon::parse($charge['start_date']);
                        $end = Carbon::parse($charge['end_date']);
                        $stayHours = max(0, $start->diffInHours($end));
                        $fullDays = floor($stayHours / 24);
                        $remainderHours = $stayHours % 24;
                        
                        if ($stayHours == 0) {
                            $calculatedDays = 0.5;
                        } else {
                            if ($remainderHours > 0 && $remainderHours <= 12) {
                                $calculatedDays = $fullDays + 0.5;
                            } elseif ($remainderHours > 12) {
                                $calculatedDays = $fullDays + 1;
                            } else {
                                $calculatedDays = $fullDays;
                            }
                        }
                        $charge['days'] = $calculatedDays;
                    }
                }

                // Let calculateTotal handle the math without overwriting the raw string input
            }
        }
        
        $this->calculateTotal();
    }

    public function addIpServiceCharge()
    {
        $this->ipServiceCharges[] = [
            'service_id' => '',
            'name' => '',
            'quantity' => 1,
            'price' => 0,
            'total' => 0
        ];
    }

    public function removeIpServiceCharge($index)
    {
        unset($this->ipServiceCharges[$index]);
        $this->ipServiceCharges = array_values($this->ipServiceCharges);
        $this->calculateTotal();
    }

    public function updatedIpServiceCharges($value, $key)
    {
        $parts = explode('.', $key);
        if (count($parts) >= 2) {
            $index = $parts[0];
            $field = $parts[1];

            if (isset($this->ipServiceCharges[$index])) {
                $charge = &$this->ipServiceCharges[$index];

                if ($field === 'service_id' && !empty($charge['service_id'])) {
                    if ($charge['service_id'] === 'manual') {
                        $charge['name'] = '';
                        $charge['price'] = 0;
                    } else {
                        $service = \App\Models\IpService::find($charge['service_id']);
                        if ($service) {
                            $charge['name'] = $service->name;
                            $charge['price'] = (float) $service->price;
                        }
                    }
                }

                // Let calculateTotal handle the math without overwriting the raw string input
            }
        }
        
        $this->calculateTotal();
    }

    public function updatedSelectedExistingCharges()
    {
        $this->calculateTotal();
    }

    public function updated($property, $value)
    {
        $this->calculateTotal();
    }

    public function calculateTotal()
    {
        $total = 0;
        foreach ($this->bedCharges as $index => $charge) {
            $days = (float) ($charge['days'] ?? 0);
            $price = (float) ($charge['price'] ?? 0);
            $t = $days * $price;
            $this->bedCharges[$index]['total'] = $t;
            $total += $t;
        }

        foreach ($this->ipServiceCharges as $index => $charge) {
            $qty = (int) ($charge['quantity'] ?? 0);
            $price = (float) ($charge['price'] ?? 0);
            $t = $qty * $price;
            $this->ipServiceCharges[$index]['total'] = $t;
            $total += $t;
        }

        foreach ($this->existingCharges as $charge) {
            if (in_array($charge['id'], $this->selectedExistingCharges)) {
                $total += ($charge['quantity'] * $charge['unit_price']);
            }
        }

        $this->totalAmount = $total;
    }

    public function generateBill()
    {
        $this->validate([
            'bedCharges.*.ward_id' => 'required|exists:wards,id',
        ]);

        foreach ($this->ipServiceCharges as $index => $charge) {
            if (empty($charge['service_id'])) {
                $this->addError("ipServiceCharges.{$index}.service_id", 'Service is required.');
                return;
            }
            if ($charge['service_id'] === 'manual' && empty($charge['name'])) {
                $this->addError("ipServiceCharges.{$index}.name", 'Service name is required.');
                return;
            }
        }

        $billingService = app(BillingService::class);
        
        $finalItems = [];
        
        foreach ($this->bedCharges as $charge) {
            if ($charge['days'] > 0 && $charge['price'] >= 0) {
                // If it already contains dates (e.g. from previous edit), don't append again
                $name = $charge['name'] ?: 'Ward/Bed Charge';
                
                if (!str_contains($name, '[')) {
                    $startDate = !empty($charge['start_date']) ? \Illuminate\Support\Carbon::parse($charge['start_date'])->format('d/m') : '';
                    $endDate = !empty($charge['end_date']) ? \Illuminate\Support\Carbon::parse($charge['end_date'])->format('d/m') : '';
                    if ($startDate && $endDate) {
                        $name .= " [{$startDate} - {$endDate}]";
                    }
                }

                $finalItems[] = [
                    'name' => $name,
                    'type' => 'IPD',
                    'quantity' => $charge['days'],
                    'unit_price' => $charge['price'],
                ];
            }
        }

        foreach ($this->ipServiceCharges as $charge) {
            if ($charge['quantity'] > 0 && $charge['price'] >= 0) {
                $finalItems[] = [
                    'name' => $charge['name'] ?: 'IP Service',
                    'type' => 'Service',
                    'quantity' => $charge['quantity'],
                    'unit_price' => $charge['price'],
                ];
            }
        }

        foreach ($this->existingCharges as $charge) {
            if (in_array($charge['id'], $this->selectedExistingCharges)) {
                $finalItems[] = [
                    'name' => $charge['name'],
                    'type' => $charge['type'],
                    'quantity' => $charge['quantity'],
                    'unit_price' => $charge['unit_price'],
                    'source_type' => $charge['source_type'] ?? null,
                    'source_id' => $charge['source_id'] ?? null,
                    'medicine_id' => $charge['medicine_id'] ?? null,
                ];
            }
        }

        // Upsert final bill
        $billingService->upsertAdmissionFinalBill($this->admission, $finalItems);


        $this->dispatch('close-modal', name: 'discharge-process-modal');
        $this->dispatch('refresh');
        
        return redirect()->route('counter.ipd.discharge', $this->admission->id);
    }

    public function render()
    {
        return view('livewire.ipd.discharge-process', [
            'wards' => Ward::where('is_active', true)->get(),
            'ipServicesList' => \App\Models\IpService::where('is_active', true)->get()
        ]);
    }
}
