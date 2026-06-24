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

    // Discount Properties
    public $discountType = 'flat';
    public $discountValue = 0;
    public $discountReason = '';
    public $isAuthorizedByDoctor = false;
    public $authorizedLimit = 0;

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

        $this->isAuthorizedByDoctor = (bool) ($this->admission->is_discount_authorized ?? false);
        $this->authorizedLimit = (float) ($this->admission->authorized_discount_limit ?? 0);

        $bill = $this->admission->finalBill ?? \App\Models\Bill::where('admission_id', $this->admission->id)->first();
        if ($bill) {
            $existingDiscount = $bill->discounts()->where('status', 'approved')->first();
            if ($existingDiscount) {
                $this->discountType = $existingDiscount->discount_type;
                $this->discountValue = (float) $existingDiscount->discount_value;
                $this->discountReason = $existingDiscount->reason;
            }
        }

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
        
        if ($bill && $bill->items()->whereIn('item_type', ['IPD', 'Service'])->exists()) {
            // Load from existing bill
            foreach ($bill->items as $item) {
                if ($item->item_type === 'IPD') {
                    $wardId = '';
                    foreach ($wards as $ward) {
                        if (str_starts_with($item->item_name, $ward->name)) {
                            $wardId = $ward->id;
                            break;
                        }
                    }
                    
                    // Try to extract dates from name e.g. "Ward [12/10 - 15/10]"
                    $startDate = '';
                    $endDate = '';
                    if (preg_match('/\[(\d{2}\/\d{2})\s*-\s*(\d{2}\/\d{2})\]/', $item->item_name, $matches)) {
                        try {
                            $startDate = Carbon::createFromFormat('d/m/Y', $matches[1] . '/' . date('Y'))->format('Y-m-d\TH:i');
                            $endDate = Carbon::createFromFormat('d/m/Y', $matches[2] . '/' . date('Y'))->format('Y-m-d\TH:i');
                        } catch (\Exception $e) {}
                    }

                    $this->bedCharges[] = [
                        'ward_id' => $wardId,
                        'name' => $item->item_name,
                        'start_date' => $startDate,
                        'end_date' => $endDate,
                        'days' => $item->quantity,
                        'price' => $item->unit_price,
                        'total' => $item->total_price
                    ];
                } elseif ($item->item_type === 'Service') {
                    // Try to find service ID
                    $serviceId = 'manual';
                    $service = \App\Models\IpService::where('name', $item->item_name)->first();
                    if ($service) {
                        $serviceId = $service->id;
                    }
                    
                    $this->ipServiceCharges[] = [
                        'service_id' => $serviceId,
                        'name' => $item->item_name,
                        'quantity' => $item->quantity,
                        'price' => $item->unit_price,
                        'total' => $item->total_price
                    ];
                }
            }
            
            // Adjust existing charges selection based on bill items
            // If the bill has items, we should only select those that are in the bill.
            $billItemNames = $bill->items->pluck('item_name')->toArray();
            $this->selectedExistingCharges = [];
            foreach ($this->existingCharges as $charge) {
                if (in_array($charge['name'], $billItemNames)) {
                    $this->selectedExistingCharges[] = $charge['id'];
                }
            }
        } else {
            // Calculate from history if no draft bill
            if ($bedHistories = \App\Models\AdmissionBedHistory::with(['bed.ward'])->where('admission_id', $this->admission->id)->get()) {
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

        $taxRate = (float) \App\Models\Setting::get('tax_rate', 0);
        $taxAmount = $taxRate > 0 ? ($total * ($taxRate / 100)) : 0;

        $discountAmount = (float) $this->discountValue;
        if ($this->discountType === 'percentage') {
            $discountAmount = ($total * $discountAmount) / 100;
        }

        $this->totalAmount = max(0, $total - $discountAmount + $taxAmount);
    }

    public function generateBill()
    {
        $this->validate([
            'bedCharges.*.name' => 'required|string',
            'discountValue' => 'required|numeric|min:0',
            'discountReason' => 'nullable|string|max:255',
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

        $bill = null;
        try {
            \Illuminate\Support\Facades\DB::transaction(function () use ($billingService, $finalItems, &$bill) {
                // Upsert final bill
                $bill = $billingService->upsertAdmissionFinalBill($this->admission, $finalItems);
                
                // Clear any existing discounts before applying the updated one
                $bill->discounts()->delete();

                if ($this->discountValue > 0) {
                    $billingService->applyDiscount($bill, [
                        'type' => $this->discountType,
                        'value' => $this->discountValue,
                        'reason' => $this->discountReason,
                        'status' => 'approved',
                    ]);
                } else {
                    $billingService->recalculatePaymentStatus($bill);
                }
            });
        } catch (\Exception $e) {
            $this->addError('discountValue', $e->getMessage());
            return;
        }

        $this->dispatch('close-modal', name: 'discharge-process-modal');
        $this->dispatch('refresh');
        
        if ($bill) {
            $this->dispatch('print-bill-and-redirect', [
                'printUrl' => route('billing.bills.print', $bill->id),
                'redirectUrl' => route('counter.ipd.discharge', $this->admission->id)
            ]);
        }
    }

    public function render()
    {
        return view('livewire.ipd.discharge-process', [
            'wards' => Ward::where('is_active', true)->get(),
            'ipServicesList' => \App\Models\IpService::where('is_active', true)->get()
        ]);
    }
}
