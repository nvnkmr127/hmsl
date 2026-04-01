# HMS System Audit Report
> Generated: 2026-03-31 | Project: Hospital Management System (Laravel + Livewire)

---

## Summary

| Category | Count |
|---|---|
| 🔴 Code Bugs / Errors | 6 |
| 🟠 Incomplete / Stub Modules | 4 |
| 🟡 Missing Features | 9 |
| 🔵 Architecture / Security Gaps | 5 |

---

## 🔴 Code Bugs & Errors

### Bug 1. `ward_name` Accessed on `Admission` Model — Non-Existent Attribute
**Files:** `PatientHistory.php:55`, `ConsultationDesk.php:75`

```php
// BUG: Admission model has no `ward_name` column or accessor
'meta' => "Ward: {$a->ward_name}",
```

**Cause:** The `admissions` table has no `ward_name` column. The ward name lives on the related `Ward` model, accessed via `Bed → Ward`. This silently returns `null` for all admission timeline entries.

**Fix:**
```php
'meta' => "Ward: " . (optional(optional($a->bed)->ward)->name ?? 'N/A'),
// Also ensure eager loading: Admission::with(['patient', 'bed.ward', ...])
```

---

### Bug 2. `full_name` SQL Search Bug on Patient Relationship
**File:** `AppointmentManagement.php:49`

```php
$q->where('full_name', 'like', '%' . $this->search . '%')
// Patient has no `full_name` COLUMN — only a computed accessor.
// This SQL query always returns 0 results.
```

The `Patient` model exposes `getFullNameAttribute()` combining `first_name + last_name`. Raw SQL cannot use computed accessors.

**Fix:**
```php
$q->where(function($sub) {
    $sub->where('first_name', 'like', '%' . $this->search . '%')
        ->orWhere('last_name', 'like', '%' . $this->search . '%');
});
```

---

### Bug 3. `insurance_validity` Not Cast in Patient Model
**File:** `app/Models/Patient.php`

```php
protected $casts = [
    'date_of_birth' => 'date',
    'is_active'     => 'boolean',
    // MISSING: 'insurance_validity' => 'date',
];
```

The field is a `date` column (migration `add_clinical_fields_to_patients`) but is not cast in the model. Date formatting and Carbon comparisons will treat it as a raw string, causing potential runtime errors.

---

### Bug 4. `AutoLoginController` Has No Environment Protection
**File:** `app/Http/Controllers/Auth/AutoLoginController.php`  
**Route:** `routes/web.php:19`

```php
// Any visitor can authenticate as admin/doctor/counter via a public GET URL!
Route::get('/autologin', [AutoLoginController::class, 'login'])->name('autologin');
```

No middleware or environment guard is applied. This is a **critical security vulnerability** if deployed beyond local development.

**Fix:**
```php
// Add inside the controller method:
if (!app()->isLocal()) { abort(403, 'Only available in local environment.'); }
```

---

### Bug 5. `OpdBooking` Loses Doctor Auto-Select After First Booking
**File:** `app/Livewire/Counter/OpdBooking.php:175`

```php
$this->reset([..., 'selectedDoctor', ...]);
// mount() auto-select logic does NOT re-run after reset().
// Livewire only calls mount() once on initial load.
```

After the first OPD booking is saved, the `reset()` clears `selectedDoctor`. The auto-selection logic in `mount()` (lines 51–65) doesn't re-execute, so the next booking form has no doctor pre-selected in single-doctor clinics.

**Fix:** Extract doctor selection into a method and call it after every reset:
```php
private function autoSelectDoctor(): void
{
    $doctors = Doctor::where('is_active', true)->get();
    if ($doctors->count() === 1) {
        $this->selectedDoctor = $doctors->first()->id;
        $this->fee = $doctors->first()->consultation_fee;
    }
}
// After $this->reset([...]): call $this->autoSelectDoctor();
```

---

### Bug 6. `DoctorList` Hard-Filters to Active Only — Cannot Re-Activate Doctors
**File:** `app/Livewire/Master/DoctorList.php:50`

```php
$doctors = Doctor::with('department')
    ->where('is_active', true)  // Hard-coded; inactive doctors cannot be found
    ->...
```

Once `toggleActive()` deactivates a doctor, they vanish from the list with no way to find and re-activate them through the UI.

**Fix:**
```php
public $showInactive = false;

// In render():
->when(!$this->showInactive, fn($q) => $q->where('is_active', true))
```

---

## 🟠 Incomplete / Stub Modules

### Module 1. Discharge Management — UI Shell Only
**File:** `resources/views/pages/discharge/index.blade.php`

- Displays a static placeholder: *"No pending discharge workflows yet."*
- **No** `Discharge` Livewire component exists anywhere in `app/Livewire/`
- **No** dedicated discharge route beyond the placeholder in `web.php:46`
- `IpdManager::dischargePatient()` IS implemented but only callable from the IPD Admissions list

**What's Missing:**
- [ ] Discharge summary / clinical notes form
- [ ] Discharge clearance checklist (billing settled, pharmacy cleared, lab complete)
- [ ] Auto-trigger final bill generation on discharge
- [ ] Discharge certificate / summary print view

---

### Module 2. Pharmacy Module — No Backend
**Routes:** `routes/modules/pharmacy.php`  
**Views:** `pages/pharmacy/index.blade.php`, `orders.blade.php`, `stock.blade.php`

- All three routes return static placeholder views
- **No** Pharmacy Livewire component in `app/Livewire/`
- **No** pharmacy-related database schema (no stock/transactions table)
- `Medicine` model exists for prescription lookup only — no dispensing or stock tracking

**What's Missing:**
- [ ] Medicine dispensing workflow linked to prescriptions
- [ ] Stock level tracking (in/out movements)
- [ ] Pharmacy order management Livewire component
- [ ] Low stock alerts

---

### Module 3. Laboratory Module — No Backend
**Routes:** `routes/modules/laboratory.php`  
**Views:** `pages/laboratory/index.blade.php`, `tests.blade.php`, `results.blade.php`

- All three routes return static placeholder views
- **No** Lab Livewire component in `app/Livewire/`
- `LabTest` and `LabParameter` models and migrations exist ✅
- `LabManager` service exists (`app/Services/LabManager.php`) — but nothing uses it

**What's Missing:**
- [ ] Lab test order form (linked to consultation)
- [ ] Test result entry form with parameter-level values
- [ ] Lab report print view
- [ ] Livewire components for listing and managing test orders

---

### Module 4. Inventory Module — No Backend
**Routes:** `routes/modules/inventory.php`  
**Views:** `pages/inventory/index.blade.php`, `stock.blade.php`, `suppliers.blade.php`

- All routes return static placeholder views
- **No** `Inventory` model or migrations for items/suppliers/stock tables
- **No** Livewire component
- `stock.blade.php` shows: *"No stock lines available yet."*

**What's Missing:**
- [ ] Inventory item schema & migrations
- [ ] Supplier / vendor schema & migrations
- [ ] Stock transaction records
- [ ] Livewire CRUD components for all above

---

## 🟡 Missing Features

### Feature 1. No Reports Beyond Revenue
- Only `RevenueDashboard` is implemented in `app/Livewire/Reports/`
- `reports/index` route calls `ReportController::index()` — no report selector page exists
- **Missing reports:** patient visits, doctor-wise consultations, IPD occupancy, lab utilization, medicine consumption

---

### Feature 2. No Soft Delete on Patient Records
- `Patient` model does not use `SoftDeletes` trait
- Hard deletes cascade to Consultations, Bills, Prescriptions, and Admissions via FK constraints
- Patient medical history is permanently lost on accidental delete

---

### Feature 3. Doctor Dashboard Missing IPD Stats
**File:** `app/Livewire/Doctor/DoctorDashboardStats.php`
- Stats only cover OPD: `total_appointments`, `monthly_earnings`, `pending_today`, `completed_today`
- No IPD admissions count or currently admitted patients for this doctor

---

### Feature 4. `consultation.completed` Missing from Webhook Event UI
**File:** `app/Livewire/Settings/WebhookEndpoints.php:26-31`

```php
protected $availableEvents = [
    'patient.registered' => 'Patient Registered',
    'admission.created'  => 'IPD Admission',
    'invoice.paid'       => 'Invoice Paid',
    'daily.summary'      => 'System: Daily Summary',
    // MISSING: 'consultation.completed' => 'OPD Consultation Completed',
];
```

`WebhookDispatcher` handles `ConsultationCompleted` (line 66) and `AppServiceProvider` registers it, but you cannot subscribe to this event through the UI.

---

### Feature 5. No PDF Generation
- Bill and prescription print views exist as HTML pages only
- No PDF library in `composer.json` (no `dompdf`, `snappy`, `browsershot`, etc.)
- All printing relies on browser `window.print()` — no server-side PDF download

---

### Feature 6. No Appointment Action from Doctor Side
**File:** `app/Livewire/Doctor/AppointmentManagement.php`
- Doctors can only **view** appointments — no action buttons
- No Cancel / Reschedule capability from the doctor portal
- Counter-side cancellation exists in `OpdBooking::cancelBooking()` only

---

### Feature 7. No Global Search
- Each module has its own isolated search field
- No unified patient/appointment lookup across the system
- No search bar in the main navigation layout

---

### Feature 8. No Patient Notification on Booking
- `OpdBooking::book()` dispatches no email/SMS to the patient
- `PatientRegistered` event fires a webhook but sends no confirmation email to the patient
- `CommunicationService` only handles prescription and invoice emails

---

### Feature 9. Billing Always Creates Fully-Paid Bills
**File:** `app/Livewire/Counter/BillGenerate.php:78`

```php
'payment_status' => 'Paid', // Assuming immediate payment
```

Every bill is instantly marked as paid. No workflow for:
- Pending / outstanding bills
- Insurance claim billing
- Partial payments or installments

---

## 🔵 Architecture & Security Gaps

### Gap 1. AutoLogin Route Exposed — Security Risk
Described in Bug #4. Must be resolved before any non-local deployment.

---

### Gap 2. WebhookDispatcher Not Queued — Blocks HTTP Requests
`WebhookDispatcher` does not implement `ShouldQueue`. External HTTP calls to webhook endpoints happen **synchronously** during the user's request, blocking the response until every endpoint responds. Slow/unreachable endpoints directly impact user experience.

**Fix:** Add `implements ShouldQueue` to `WebhookDispatcher` and configure a queue driver (Redis or database).

---

### Gap 3. No Test Coverage
- `tests/` directory has zero feature or unit test files
- Core flows (OPD booking, billing, IPD admission, prescription) have no automated coverage
- Bug regressions can only be caught manually

---

### Gap 4. `DailySummaryReport` Command May Not Be Scheduled
**File:** `app/Console/Commands/DailySummaryReport.php` exists, but verify `routes/console.php`:

```php
// routes/console.php should contain:
Schedule::command('hms:daily-summary')->dailyAt('08:00');
```

If the schedule is missing, the `daily.summary` webhook event never fires automatically.

---

### Gap 5. No Role-Based Navigation Visibility
- The sidebar shows all modules regardless of user permissions
- A user with only `view opd` permission sees pharmacy, lab, and inventory links
- Access is blocked at route level (403) but links remain visible, causing a confusing UX

---

## 📋 Priority Fix Roadmap

| Priority | Item | Effort |
|---|---|---|
| 🔴 CRITICAL | AutoLogin route has no environment guard | XS |
| 🔴 CRITICAL | `ward_name` non-existent attribute — silent null in timelines | XS |
| 🔴 HIGH | `full_name` SQL column search always returns 0 results | XS |
| 🔴 HIGH | `insurance_validity` missing cast in Patient model | XS |
| 🟠 HIGH | `DoctorList` cannot show / re-activate inactive doctors | S |
| 🟠 HIGH | `OpdBooking` loses doctor auto-select after first booking | S |
| 🟠 MEDIUM | Add `consultation.completed` to webhook event UI options | XS |
| 🟡 MEDIUM | Discharge module — Livewire component + discharge form | L |
| 🟡 MEDIUM | Laboratory module — test ordering + result entry Livewire | L |
| 🟡 MEDIUM | Pharmacy module — dispensing + stock workflow | L |
| 🟡 MEDIUM | Inventory module — schema + Livewire CRUD components | XL |
| 🟡 MEDIUM | PDF export for bills and prescriptions | M |
| 🟡 LOW | Queue driver for webhook dispatching (ShouldQueue) | S |
| 🟡 LOW | Soft-delete on Patient model | XS |
| 🟡 LOW | Partial / unpaid billing workflow | M |
| �� LOW | Patient email notification on appointment booking | M |
| 🔵 LOW | Role-based navigation visibility in sidebar | M |
| 🔵 LOW | Test coverage for core modules | XL |
