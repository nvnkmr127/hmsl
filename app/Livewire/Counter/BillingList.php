<?php

namespace App\Livewire\Counter;

use App\Models\Bill;
use App\Models\BillPayment;
use App\Models\Admission;
use App\Services\BillingService;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class BillingList extends Component
{
    use WithPagination;

    public $activeTab = 'bills';

    // Bills Filters
    public $search = '';
    public $statusFilter = '';
    public $methodFilter = '';
    public $fromDate = '';
    public $toDate = '';

    // OP Filters
    public $opSearch = '';
    public $opStatusFilter = '';
    public $opDoctorFilter = '';
    public $opVisitTypeFilter = '';
    public $opFromDate = '';
    public $opToDate = '';

    // IP Filters
    public $ipSearch = '';
    public $ipStatusFilter = '';
    public $ipDoctorFilter = '';
    public $ipFromDate = '';
    public $ipToDate = '';

    public ?int $selectedBillId = null;
    public string $paymentType = 'payment';
    public string $paymentMethod = 'Cash';
    public $paymentAmount = 0;
    public ?string $paymentReference = null;
    public ?string $paymentNotes = null;

    // Discount Properties
    public $discountType = 'flat';
    public $discountValue = 0;
    public $discountReason = '';
    public $discountItemId = null;
    public $isAuthorizedByDoctor = false;
    public $authorizedLimit = 0;

    // OP Discount Properties
    public $selectedOpId = null;
    public $opDiscountAmount = 0;
    public $opDiscountReason = '';

    protected $queryString = [
        'search' => ['except' => ''],
        'statusFilter' => ['except' => ''],
        'methodFilter' => ['except' => ''],
        'fromDate' => ['except' => ''],
        'toDate' => ['except' => ''],
        'activeTab' => ['except' => 'bills'],
        'opSearch' => ['except' => ''],
        'opStatusFilter' => ['except' => ''],
        'opDoctorFilter' => ['except' => ''],
        'opVisitTypeFilter' => ['except' => ''],
        'opFromDate' => ['except' => ''],
        'opToDate' => ['except' => ''],
        'ipSearch' => ['except' => ''],
        'ipStatusFilter' => ['except' => ''],
        'ipDoctorFilter' => ['except' => ''],
        'ipFromDate' => ['except' => ''],
        'ipToDate' => ['except' => ''],
    ];

    public function updatedSearch()    { $this->resetPage('bills-page'); }
    public function updatedStatusFilter() { $this->resetPage('bills-page'); }
    public function updatedMethodFilter() { $this->resetPage('bills-page'); }
    public function updatedFromDate() { $this->resetPage('bills-page'); }
    public function updatedToDate() { $this->resetPage('bills-page'); }
    
    public function updatedOpSearch() { $this->resetPage('ops-page'); }
    public function updatedOpStatusFilter() { $this->resetPage('ops-page'); }
    public function updatedOpDoctorFilter() { $this->resetPage('ops-page'); }
    public function updatedOpVisitTypeFilter() { $this->resetPage('ops-page'); }
    public function updatedOpFromDate() { $this->resetPage('ops-page'); }
    public function updatedOpToDate() { $this->resetPage('ops-page'); }

    public function updatedIpSearch() { $this->resetPage('ips-page'); }
    public function updatedIpStatusFilter() { $this->resetPage('ips-page'); }
    public function updatedIpDoctorFilter() { $this->resetPage('ips-page'); }
    public function updatedIpFromDate() { $this->resetPage('ips-page'); }
    public function updatedIpToDate() { $this->resetPage('ips-page'); }

    public function setTab($tab)
    {
        $this->activeTab = $tab;
    }

    public function resetBillsFilters()
    {
        $this->reset(['search', 'statusFilter', 'methodFilter', 'fromDate', 'toDate']);
        $this->resetPage('bills-page');
    }

    public function resetOpFilters()
    {
        $this->reset(['opSearch', 'opStatusFilter', 'opDoctorFilter', 'opVisitTypeFilter', 'opFromDate', 'opToDate']);
        $this->resetPage('ops-page');
    }

    public function resetIpFilters()
    {
        $this->reset(['ipSearch', 'ipStatusFilter', 'ipDoctorFilter', 'ipFromDate', 'ipToDate']);
        $this->resetPage('ips-page');
    }

    public function exportBills()
    {
        $query = $this->getBillsQuery();
        $bills = $query->get();

        return response()->streamDownload(function () use ($bills) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Bill No', 'Date', 'Patient', 'Total Amount', 'Paid Amount', 'Due Amount', 'Status', 'Method']);

            foreach ($bills as $bill) {
                fputcsv($handle, [
                    $bill->bill_number,
                    $bill->created_at->format('d M Y, h:i A'),
                    $bill->patient ? $bill->patient->full_name : 'N/A',
                    $bill->total_amount,
                    $bill->paid_amount,
                    $bill->balance_amount,
                    ucfirst($bill->payment_status),
                    $bill->payment_method ?? 'N/A'
                ]);
            }
            fclose($handle);
        }, 'bills_export_' . now()->format('Ymd_His') . '.csv');
    }

    public function exportOp()
    {
        $query = $this->getOpQuery();
        $ops = $query->get();

        return response()->streamDownload(function () use ($ops) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Token', 'Date', 'Patient', 'Doctor', 'Visit Type', 'Fee', 'Discount', 'Status']);

            foreach ($ops as $op) {
                $status = $op->bill ? ucfirst($op->bill->payment_status) : 'Not Billed';
                fputcsv($handle, [
                    $op->token_number,
                    $op->created_at->format('d M Y, h:i A'),
                    $op->patient ? $op->patient->full_name : 'N/A',
                    $op->doctor ? $op->doctor->full_name : 'N/A',
                    $op->visit_type,
                    $op->fee,
                    $op->discount_amount,
                    $status
                ]);
            }
            fclose($handle);
        }, 'op_export_' . now()->format('Ymd_His') . '.csv');
    }

    public function exportIp()
    {
        $query = $this->getIpQuery();
        $ips = $query->get();

        return response()->streamDownload(function () use ($ips) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Admission No', 'Date', 'Patient', 'Doctor', 'Ward', 'Status']);

            foreach ($ips as $ip) {
                $status = $ip->finalBill ? ucfirst($ip->finalBill->payment_status) : 'Not Billed';
                fputcsv($handle, [
                    $ip->admission_number,
                    $ip->created_at->format('d M Y, h:i A'),
                    $ip->patient ? $ip->patient->full_name : 'N/A',
                    $ip->doctor ? $ip->doctor->full_name : 'N/A',
                    $ip->ward_name ?? 'N/A',
                    $status
                ]);
            }
            fclose($handle);
        }, 'ip_export_' . now()->format('Ymd_His') . '.csv');
    }

    public function sendEmail($billId)
    {
        $bill = Bill::findOrFail($billId);
        
        try {
            if (!$bill->patient || !$bill->patient->email) {
                $this->dispatch('notify', ['type' => 'error', 'message' => 'Patient record or email address is missing.']);
                return;
            }

            app(\App\Services\CommunicationService::class)->sendInvoice($bill);
            $this->dispatch('notify', ['type' => 'success', 'message' => 'Invoice emailed to patient!']);
        } catch (\Exception $e) {
            $this->dispatch('notify', ['type' => 'error', 'message' => $e->getMessage()]);
        }
    }

    public function openPaymentModal(int $billId): void
    {
        $bill = Bill::with('payments')->findOrFail($billId);
        $this->selectedBillId = $bill->id;
        $this->paymentType = 'payment';
        $this->paymentMethod = $bill->payment_method ?: 'Cash';
        $this->paymentAmount = max(0, (float) $bill->balance_amount);
        $this->paymentReference = null;
        $this->paymentNotes = null;
        $this->dispatch('open-modal', name: 'billing-payment-modal');
    }

    public function submitPayment(BillingService $service): void
    {
        $this->validate([
            'selectedBillId' => 'required|integer|exists:bills,id',
            'paymentType' => 'required|in:payment,refund',
            'paymentMethod' => 'nullable|string|max:50',
            'paymentAmount' => 'required|numeric|min:0.01',
            'paymentReference' => 'nullable|string|max:100',
            'paymentNotes' => 'nullable|string|max:2000',
        ]);

        $bill = Bill::with('payments')->findOrFail($this->selectedBillId);
        $amount = round((float) $this->paymentAmount, 2);

        if ($this->paymentType === 'refund' && $amount > (float) $bill->paid_amount) {
            $this->dispatch('notify', ['type' => 'error', 'message' => 'Refund amount cannot be more than paid amount.']);
            return;
        }

        try {
            $service->recordPayment(
                $bill,
                $amount,
                $this->paymentMethod,
                $this->paymentType,
                $this->paymentReference ?: null,
                $this->paymentNotes ?: null
            );

            $bill->refresh();
            $service->recalculatePaymentStatus($bill);

            $this->dispatch('close-modal', name: 'billing-payment-modal');
            $this->dispatch('notify', ['type' => 'success', 'message' => 'Payment saved.']);
            $this->reset(['selectedBillId', 'paymentType', 'paymentMethod', 'paymentAmount', 'paymentReference', 'paymentNotes']);
        } catch (\Exception $e) {
            $this->dispatch('notify', ['type' => 'error', 'message' => $e->getMessage()]);
        }
    }

    public function openDiscountModal(int $billId): void
    {
        $bill = Bill::with(['consultation', 'admission'])->findOrFail($billId);
        $this->selectedBillId = $bill->id;
        $this->discountType = 'flat';
        $this->discountValue = 0;
        $this->discountReason = '';
        $this->discountItemId = null;

        $clinicalSource = $bill->consultation ?? $bill->admission;
        $this->isAuthorizedByDoctor = (bool) ($clinicalSource->is_discount_authorized ?? false);
        $this->authorizedLimit = (float) ($clinicalSource->authorized_discount_limit ?? 0);

        $this->dispatch('open-modal', name: 'bill-discount-modal');
    }

    public function submitDiscount(BillingService $service): void
    {
        $this->validate([
            'selectedBillId' => 'required|integer|exists:bills,id',
            'discountType' => 'required|in:percentage,flat',
            'discountValue' => 'required|numeric|min:0.01',
            'discountReason' => 'nullable|string|max:255',
            'discountItemId' => 'nullable|exists:bill_items,id',
        ]);

        try {
            $bill = Bill::findOrFail($this->selectedBillId);
            $service->applyDiscount($bill, [
                'type' => $this->discountType,
                'value' => $this->discountValue,
                'reason' => $this->discountReason,
                'bill_item_id' => $this->discountItemId ?: null,
            ]);

            $this->dispatch('close-modal', name: 'bill-discount-modal');
            $this->dispatch('notify', ['type' => 'success', 'message' => 'Discount applied successfully!']);
            $this->reset(['selectedBillId', 'discountType', 'discountValue', 'discountReason', 'discountItemId']);
        } catch (\Exception $e) {
            $this->dispatch('notify', ['type' => 'error', 'message' => $e->getMessage()]);
        }
    }

    public function openOpDiscountModal($opId)
    {
        $op = \App\Models\Consultation::findOrFail($opId);
        $this->selectedOpId = $opId;
        $this->opDiscountAmount = 0;
        $this->opDiscountReason = '';
        $this->dispatch('open-modal', name: 'op-discount-modal');
    }

    public function submitOpDiscount()
    {
        $this->validate([
            'selectedOpId' => 'required|exists:consultations,id',
            'opDiscountAmount' => 'required|numeric|min:0.01',
            'opDiscountReason' => 'nullable|string|max:255',
        ]);

        $op = \App\Models\Consultation::with('bill.items')->findOrFail($this->selectedOpId);
        
        if ($this->opDiscountAmount > $op->fee) {
            $this->dispatch('notify', ['type' => 'error', 'message' => 'Discount cannot be more than the fee.']);
            return;
        }

        DB::transaction(function () use ($op) {
            $op->discount_amount = ($op->discount_amount ?? 0) + $this->opDiscountAmount;
            $op->fee = $op->fee - $this->opDiscountAmount;
            $op->notes = ($op->notes ? $op->notes . ' ' : '') . "[Discount: ₹{$this->opDiscountAmount} - {$this->opDiscountReason}]";
            $op->save();

            if ($bill = $op->bill) {
                $billItem = $bill->items()
                    ->where('item_type', 'Consultation')
                    ->first();
                
                if ($billItem) {
                    $billItem->unit_price = $op->fee;
                    $billItem->total_price = $op->fee;
                    $billItem->save();
                    
                    app(BillingService::class)->recalculatePaymentStatus($bill);
                }
            }
        });

        $this->dispatch('close-modal', name: 'op-discount-modal');
        $this->dispatch('notify', ['type' => 'success', 'message' => 'Discount applied to OP booking and Bill updated.']);
        $this->reset(['selectedOpId', 'opDiscountAmount', 'opDiscountReason']);
    }

    private function getBillsQuery()
    {
        return \App\Models\Bill::with(['patient', 'consultation.doctor', 'payments', 'discounts'])
            ->when($this->search, function ($q) {
                $q->where(function ($query) {
                    $query->where('bill_number', 'like', "%{$this->search}%")
                      ->orWhereHas('patient', fn($pq) =>
                          $pq->where('first_name', 'like', "%{$this->search}%")
                             ->orWhere('last_name', 'like', "%{$this->search}%")
                             ->orWhere('uhid', 'like', "%{$this->search}%")
                             ->orWhere('phone', 'like', "%{$this->search}%")
                             ->orWhere('father_name', 'like', "%{$this->search}%")
                             ->orWhere('mother_name', 'like', "%{$this->search}%")
                      );
                });
            })
            ->when($this->statusFilter, fn($q) => $q->where('payment_status', $this->statusFilter))
            ->when($this->methodFilter, fn($q) => $q->where('payment_method', $this->methodFilter))
            ->when($this->fromDate, fn($q) => $q->whereDate('created_at', '>=', $this->fromDate))
            ->when($this->toDate, fn($q) => $q->whereDate('created_at', '<=', $this->toDate))
            ->when(!$this->fromDate && !$this->toDate && empty($this->search), fn($q) => $q->whereDate('created_at', today()));
    }

    private function getOpQuery()
    {
        return \App\Models\Consultation::query()
            ->when($this->opSearch, function ($q) {
                $q->where(function ($query) {
                    $query->where('token_number', 'like', "%{$this->opSearch}%")
                        ->orWhereHas('patient', fn($pq) =>
                            $pq->where('first_name', 'like', "%{$this->opSearch}%")
                               ->orWhere('last_name', 'like', "%{$this->opSearch}%")
                               ->orWhere('uhid', 'like', "%{$this->opSearch}%")
                               ->orWhere('phone', 'like', "%{$this->opSearch}%")
                               ->orWhere('father_name', 'like', "%{$this->opSearch}%")
                               ->orWhere('mother_name', 'like', "%{$this->opSearch}%")
                        );
                });
            })
            ->when($this->opStatusFilter, fn($q) => $q->where('status', $this->opStatusFilter))
            ->when($this->opDoctorFilter, fn($q) => $q->where('doctor_id', $this->opDoctorFilter))
            ->when($this->opVisitTypeFilter, fn($q) => $q->where('visit_type', $this->opVisitTypeFilter))
            ->when($this->opFromDate, fn($q) => $q->whereDate('consultation_date', '>=', $this->opFromDate))
            ->when($this->opToDate, fn($q) => $q->whereDate('consultation_date', '<=', $this->opToDate))
            ->when(!$this->opFromDate && !$this->opToDate && empty($this->opSearch), fn($q) => $q->whereDate('consultation_date', today()));
    }

    private function getIpQuery()
    {
        return \App\Models\Admission::query()
            ->when($this->ipSearch, function ($q) {
                $q->where(function ($query) {
                    $query->where('admission_number', 'like', "%{$this->ipSearch}%")
                        ->orWhereHas('patient', fn($pq) =>
                            $pq->where('first_name', 'like', "%{$this->ipSearch}%")
                               ->orWhere('last_name', 'like', "%{$this->ipSearch}%")
                               ->orWhere('uhid', 'like', "%{$this->ipSearch}%")
                               ->orWhere('phone', 'like', "%{$this->ipSearch}%")
                               ->orWhere('father_name', 'like', "%{$this->ipSearch}%")
                               ->orWhere('mother_name', 'like', "%{$this->ipSearch}%")
                        );
                });
            })
            ->when($this->ipStatusFilter, fn($q) => $q->where('status', $this->ipStatusFilter))
            ->when($this->ipDoctorFilter, fn($q) => $q->where('doctor_id', $this->ipDoctorFilter))
            ->when($this->ipFromDate, fn($q) => $q->whereDate('admission_date', '>=', $this->ipFromDate))
            ->when($this->ipToDate, fn($q) => $q->whereDate('admission_date', '<=', $this->ipToDate))
            ->when(!$this->ipFromDate && !$this->ipToDate && empty($this->ipSearch), fn($q) => $q->whereDate('admission_date', today()));
    }

    public function render()
    {
        $billsBase = $this->getBillsQuery();
        $bills = (clone $billsBase)->latest()->paginate(15, pageName: 'bills-page');

        $opBase = $this->getOpQuery();
        $ops = (clone $opBase)->with(['patient', 'doctor', 'bill'])->latest()->paginate(15, pageName: 'ops-page');

        $ipBase = $this->getIpQuery();
        $ips = (clone $ipBase)->with(['patient', 'doctor', 'finalBill', 'bed.ward'])->latest()->paginate(15, pageName: 'ips-page');

        // Dynamic stats for Bills
        $statsRaw = (clone $billsBase)->selectRaw('
            COUNT(*) as total_count,
            SUM(CASE WHEN payment_status IN ("Unpaid", "Partially Paid") THEN 1 ELSE 0 END) as unpaid_count,
            SUM(paid_amount) as total_paid
        ')->first();

        $stats = [
            'total_paid'    => (float) ($statsRaw->total_paid ?? 0),
            'total_unpaid'  => (int) ($statsRaw->unpaid_count ?? 0),
            'op_count'      => \App\Models\Consultation::count(),
            'op_today'      => \App\Models\Consultation::whereDate('consultation_date', today())->count(),
        ];

        // Specific OP Reports Stats
        $opStatsRaw = (clone $opBase)->selectRaw('
            COUNT(*) as total,
            SUM(CASE WHEN visit_type = "Review" THEN 1 ELSE 0 END) as review,
            SUM(CASE WHEN payment_status = "Paid" AND fee > 0 THEN 1 ELSE 0 END) as paid,
            SUM(fee) as revenue,
            SUM(discount_amount) as discount
        ')->first();

        $opStats = [
            'total' => (int) ($opStatsRaw->total ?? 0),
            'review' => (int) ($opStatsRaw->review ?? 0),
            'paid' => (int) ($opStatsRaw->paid ?? 0),
            'revenue' => (float) ($opStatsRaw->revenue ?? 0),
            'discount' => (float) ($opStatsRaw->discount ?? 0),
        ];

        // Specific IP Reports Stats
        $ipStatsRaw = (clone $ipBase)->selectRaw('
            COUNT(*) as total,
            SUM(CASE WHEN status = "Admitted" THEN 1 ELSE 0 END) as admitted,
            SUM(CASE WHEN status = "Discharged" THEN 1 ELSE 0 END) as discharged
        ')->first();

        $ipStats = [
            'total' => (int) ($ipStatsRaw->total ?? 0),
            'admitted' => (int) ($ipStatsRaw->admitted ?? 0),
            'discharged' => (int) ($ipStatsRaw->discharged ?? 0),
        ];

        $doctors = \App\Models\Doctor::all();

        return view('livewire.counter.billing-list', compact('bills', 'ops', 'ips', 'stats', 'opStats', 'ipStats', 'doctors'));
    }
}
