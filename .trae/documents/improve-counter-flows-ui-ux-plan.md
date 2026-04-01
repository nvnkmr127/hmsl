# Improve Counter Flows UI/UX Plan (Patients, Registration, OPD, IPD, Billing)

## Summary
Improve the core Counter workflows with a **structured, consistent UI** and close “backend implemented but not surfaced in frontend” gaps for:
- `/counter/patients` (registry + history)
- New patient registration
- `/counter/opd` (token + queue + billing)
- `/counter/ipd` (admissions + discharge)
- `/billing` (billing list + create bill + reports link)

Key decisions confirmed:
- Billing “Create Bill”: **support both** (from OPD token and from patient directly)
- Billing “Download Report”: **go to Reports** (not export)
- OPD “Generate Bill”: **Paid only**
- Counter page header navigation: **Yes** (real tabs, no “Planned” placeholders)

## Current State Analysis (Grounded)
### Routes and entry views
- Counter routes: [counter.php](file:///Users/naveenadicharla/Documents/hms/routes/modules/counter.php)
  - Patients list: `counter.patients.index`
  - Patient history: `counter.patients.history` → shell view [history.blade.php](file:///Users/naveenadicharla/Documents/hms/resources/views/pages/counter/history.blade.php) which renders Livewire history
  - OPD: `counter.opd.index` + slip print
  - IPD: `counter.ipd.index` + create
  - Counter billing: `counter.billing.index` + `counter.bills.print`
- Global billing + discharge routes: [web.php](file:///Users/naveenadicharla/Documents/hms/routes/web.php)
  - Billing list view: [billing/index.blade.php](file:///Users/naveenadicharla/Documents/hms/resources/views/pages/billing/index.blade.php)
  - Discharge summary views exist: [summary.blade.php](file:///Users/naveenadicharla/Documents/hms/resources/views/pages/discharge/summary.blade.php), [summary-print.blade.php](file:///Users/naveenadicharla/Documents/hms/resources/views/pages/discharge/summary-print.blade.php)

### Current UI components already available
- Standard header and card patterns:
  - [page-header.blade.php](file:///Users/naveenadicharla/Documents/hms/resources/views/components/page-header.blade.php)
  - [card.blade.php](file:///Users/naveenadicharla/Documents/hms/resources/views/components/card.blade.php)
  - Table wrapper + empty state: [wrapper.blade.php](file:///Users/naveenadicharla/Documents/hms/resources/views/components/table/wrapper.blade.php), [empty.blade.php](file:///Users/naveenadicharla/Documents/hms/resources/views/components/table/empty.blade.php)

### Backend capabilities that are not fully surfaced in UI (examples)
- OPD billing modal exists but lacks a queue action to trigger it:
  - Bill generator listens to `generate-bill`: [BillGenerate.php](file:///Users/naveenadicharla/Documents/hms/app/Livewire/Counter/BillGenerate.php)
  - OPD page includes `<livewire:counter.bill-generate />` but no visible “Generate Bill” button in queue: [opd-booking.blade.php](file:///Users/naveenadicharla/Documents/hms/resources/views/livewire/counter/opd-booking.blade.php)
- IPD discharge supports notes in service, but IPD list discharges without notes:
  - Service supports notes: [IpdManager.php](file:///Users/naveenadicharla/Documents/hms/app/Services/IpdManager.php)
  - IPD UI uses confirm-only discharge: [IpdAdmissions.php](file:///Users/naveenadicharla/Documents/hms/app/Livewire/Counter/IpdAdmissions.php), [ipd-admissions.blade.php](file:///Users/naveenadicharla/Documents/hms/resources/views/livewire/counter/ipd-admissions.blade.php)
- Billing page buttons are not wired:
  - “Download Report” + “Create Bill” are static: [billing/index.blade.php](file:///Users/naveenadicharla/Documents/hms/resources/views/pages/billing/index.blade.php)
- Counter header navigation is inconsistent and contains “Planned” placeholders:
  - Patients page shows disabled OPD/Billing tabs: [patients.blade.php](file:///Users/naveenadicharla/Documents/hms/resources/views/pages/counter/patients.blade.php)
  - OPD page shows disabled Billing tab: [opd.blade.php](file:///Users/naveenadicharla/Documents/hms/resources/views/pages/counter/opd.blade.php)

## Proposed Changes (Decision-Complete)
### 1) Create a unified Counter header navigation (real tabs)
**Goal:** Consistent navigation across Counter pages with real links and permission-based visibility.

**Changes**
- Add a reusable Blade component (or include partial), e.g. `resources/views/components/counter-nav.blade.php`, that renders tabs:
  - Patients → `counter.patients.index` (requires `view patients`)
  - OPD → `counter.opd.index` (requires `view opd`)
  - IPD → `counter.ipd.index` (requires `view ipd`)
  - Billing → `billing.index` (requires `view billing`)
- Replace the custom tab strips in:
  - [patients.blade.php](file:///Users/naveenadicharla/Documents/hms/resources/views/pages/counter/patients.blade.php)
  - [opd.blade.php](file:///Users/naveenadicharla/Documents/hms/resources/views/pages/counter/opd.blade.php)
  - [ipd/index.blade.php](file:///Users/naveenadicharla/Documents/hms/resources/views/pages/ipd/index.blade.php)
  - Optionally add to [billing/index.blade.php](file:///Users/naveenadicharla/Documents/hms/resources/views/pages/billing/index.blade.php) for cross-module consistency.

**UX rules**
- No “Planned” placeholders.
- Tabs only appear if the user has permission for that module (`@can(...)`).
- Active tab highlights based on route.

### 2) Improve /counter/patients (registry + registration)
**Goal:** Faster registry workflow + mobile-friendly list.

**Changes**
- Add a mobile card layout to patient registry list (`md:hidden`) and keep table on desktop (`md:block`) in:
  - [patient-list.blade.php](file:///Users/naveenadicharla/Documents/hms/resources/views/livewire/counter/patient-list.blade.php)
- Improve search correctness (avoid broad OR conditions leaking past intended grouping) in:
  - [PatientService.php](file:///Users/naveenadicharla/Documents/hms/app/Services/PatientService.php)
  - Ensure search groups name/uhid/phone conditions inside a single `where(function(){...})`.
- New registration UX improvements:
  - Ensure email and insurance fields are visible and validated appropriately in:
    - [PatientForm.php](file:///Users/naveenadicharla/Documents/hms/app/Livewire/Counter/PatientForm.php)
    - [patient-form.blade.php](file:///Users/naveenadicharla/Documents/hms/resources/views/livewire/counter/patient-form.blade.php)

### 3) Improve /counter/patients/{id}/history (Patient History dashboard)
**Goal:** Ensure all datasets are accessible with consistent export actions per section.

**Changes**
- Ensure each tab exposes export actions (CSV) consistent with `export($type)` in:
  - [PatientHistory.php](file:///Users/naveenadicharla/Documents/hms/app/Livewire/Counter/PatientHistory.php)
  - [patient-history.blade.php](file:///Users/naveenadicharla/Documents/hms/resources/views/livewire/counter/patient-history.blade.php)
- Add clear empty states, loading states, and filter controls per tab.
- Confirm print links map to available print routes:
  - OP slip: `counter.opd.print`
  - Bill receipt: prefer `billing.bills.print` (keep `counter.bills.print` as alias if needed)
  - Discharge summary: `discharge.summary` + `discharge.print`

### 4) Improve /counter/opd (token → queue → bill) and surface missing billing feature
**Goal:** Token workflow is fast; billing is one click from OPD when Paid.

**Changes**
- Add a “Generate Bill” action in OPD queue (desktop + mobile):
  - Only visible/enabled when `payment_status === 'Paid'` (confirmed decision)
  - Dispatch `generate-bill` event to open billing modal component:
    - Uses existing [BillGenerate.php](file:///Users/naveenadicharla/Documents/hms/app/Livewire/Counter/BillGenerate.php)
- Improve post-generation UX:
  - Modify BillGenerate to dispatch `bill-generated` with the created bill id
  - Add a JS listener on OPD page to open print route automatically:
    - `route('billing.bills.print', billId)` (or `counter.bills.print` fallback)
- Ensure OPD list remains responsive (keep the mobile cards pattern already used in [opd-booking.blade.php](file:///Users/naveenadicharla/Documents/hms/resources/views/livewire/counter/opd-booking.blade.php)).

### 5) Improve /counter/ipd (admissions + discharge notes + summary link)
**Goal:** Discharge captures notes and leads to summary print.

**Changes**
- Add a discharge notes modal to IPD admissions list:
  - Update Livewire [IpdAdmissions.php](file:///Users/naveenadicharla/Documents/hms/app/Livewire/Counter/IpdAdmissions.php) to hold `selectedAdmissionId` + `dischargeNotes` and call `IpdManager::dischargePatient($admission, $notes)`.
  - Update UI [ipd-admissions.blade.php](file:///Users/naveenadicharla/Documents/hms/resources/views/livewire/counter/ipd-admissions.blade.php) to:
    - Replace confirm-only discharge button with “Discharge” → opens modal → submit.
    - For discharged rows, show “Summary” and “Print” buttons linking to:
      - `discharge.summary` and `discharge.print`.
- Add mobile card layout for IPD admissions list (similar to Billing/Discharge patterns).

### 6) Improve /billing page UX and surface missing “Create Bill” feature
**Goal:** Billing page becomes the canonical billing workspace: list + create + print/email.

**Changes**
- Wire “Download Report” to Reports:
  - Replace static button with link to `reports.index` (or `reports.revenue` if you prefer a direct revenue view).
  - Permission gate with `@can('view reports')`.
- Implement “Create Bill” with **both flows** (confirmed decision):
  1) **From OPD token**:
     - Add a small “Find OP Token” modal: search by token/bill number/UHID/date and pick a `Consultation`.
     - Trigger existing BillGenerate via `generate-bill` with selected consultation id.
  2) **From patient directly**:
     - Extend BillGenerate to support a second event (e.g., `generate-bill-for-patient`) that opens the modal with:
       - patient selected
       - empty `items[]` ready to add
       - `consultation_id` null
     - Add a “Select Patient” picker to choose `Patient` (search by UHID/phone/name).
- Unify print route usage:
  - Update Billing list UI to use `billing.bills.print` for print links consistently, while keeping `counter.bills.print` functional for backward compatibility.
  - Files involved:
    - [billing-list.blade.php](file:///Users/naveenadicharla/Documents/hms/resources/views/livewire/counter/billing-list.blade.php)
    - [counter.php](file:///Users/naveenadicharla/Documents/hms/routes/modules/counter.php)
    - [web.php](file:///Users/naveenadicharla/Documents/hms/routes/web.php)

## Assumptions & Decisions
- “Missing backend features” means: features already available as services/events/components will be surfaced via UI actions (not adding brand-new domain models like Appointments/Payments tables).
- “Appointments” are represented by Consultations (current codebase does not have a separate Appointment model).
- Exports use CSV (fast, reliable, avoids PDF complexity unless explicitly requested).
- Permission-driven visibility is enforced consistently: actions/links must match route middleware.

## Verification Steps
### Automated
- Add/extend Feature tests to cover:
  - Patient registry renders and mobile/desktop blocks compile (Blade render)
  - OPD “Generate Bill” event triggers BillGenerate and returns a printable bill route
  - IPD discharge with notes updates Admission and exposes discharge summary links
  - Billing “Create Bill” supports both consultation-driven and patient-only flow
  - Patient history tabs load and export endpoints respond (CSV download)
- Re-run:
  - `php artisan test`
  - `npm run build`

### Manual smoke checklist
- As receptionist:
  - Register patient → open history → browse tabs → export billing/payments CSV
  - OPD: create token (paid) → generate bill → print
  - Billing page: Create Bill (patient-only) → print
- As nurse:
  - IPD: discharge with notes → open discharge summary → print

## Rollout / Risk Controls
- Implement changes behind existing routes/components (no URL changes).
- Keep print route alias working (`counter.bills.print`) while migrating UI to `billing.bills.print`.
