# 04 — Phase 1: OPD System Micro Tasks

> **Prerequisite:** Phase 0 must be complete before starting Phase 1.  
> **Goal:** Full digital OPD workflow — Patient Registration → Token → Consultation → Prescription → Billing.

---

## 1.1 Patient Management

### MT-040.1.1 — Patient Migration & Model

- [ ] Create migration: `create_patients_table`
- [ ] Create `app/Models/Patient.php`
- [ ] Add relationships: `hasMany(Visit)`, `hasMany(Appointment)`, `hasMany(Invoice)`
- [ ] Add computed property: `age()` — calculate from DOB or use stored age
- [ ] Add accessor: `uhidFormatted()` → `HMS-0001`

---

### MT-040.1.2 — UHID Generator

- [ ] In `PatientService.php`, add `generateUhid()` method
- [ ] Format: `{prefix}{zero-padded id}` (e.g. `HMS-00001`)
- [ ] Prefix comes from settings: `Setting::get('uhid_prefix', 'HMS-')`
- [ ] Ensure race-condition safety with DB transaction

---

### MT-040.1.3 — Patient Registration Livewire

- [ ] Create `app/Livewire/Patient/PatientRegistration.php`
- [ ] Fields: Name, DOB/Age, Gender, Blood Group, Phone (required), Email, Address, City, State, Pincode, Emergency Contact, Referred By
- [ ] Phone uniqueness soft check: if phone exists, prompt to view existing patient
- [ ] On save: generate UHID, show success, option to create OPD token immediately
- [ ] Validation rules: phone required, minimum length, gender required

---

### MT-040.1.4 — Patient Search Livewire

- [ ] Create `app/Livewire/Patient/PatientSearch.php`
- [ ] Search by: UHID, Name (partial), Phone
- [ ] Use Livewire `wire:model.live` with debounce 400ms
- [ ] Results show in dropdown or table
- [ ] Clicking result navigates to Patient Profile

---

### MT-040.1.5 — Patient Profile Livewire

- [ ] Create `app/Livewire/Patient/PatientProfile.php`
- [ ] Display all patient demographics
- [ ] Show visit history (dates, doctor, status)
- [ ] Edit demographics inline
- [ ] Quick action buttons: New OPD Token, View Past Prescriptions, View Invoices

---

### MT-040.1.6 — Patient Service

- [ ] Create `app/Services/PatientService.php`
- [ ] Methods: `create(array $data)`, `update(Patient $patient, array $data)`, `search(string $query)`, `findByPhone(string $phone)`, `getVisitHistory(Patient $patient)`

---

## 1.2 Appointment and Token System

### MT-040.2.1 — Appointment Migration & Model

- [ ] Create migration: `create_appointments_table`
- [ ] Create `app/Models/Appointment.php`
- [ ] Relationships: `belongsTo(Patient)`, `belongsTo(Doctor)`
- [ ] Scope: `scopeToday(query)`, `scopeWaiting(query)`, `scopeForDoctor(query, doctorId)`

---

### MT-040.2.2 — Token Queue Livewire

- [ ] Create `app/Livewire/Appointment/TokenQueue.php`
- [ ] Show today's queue for logged-in doctor (or selected doctor for receptionist)
- [ ] Display: token number, patient name, wait time, status badge
- [ ] Actions per token: Start Consultation → moves to `in_progress`, Complete → `completed`, Cancel
- [ ] Auto-calculate next available token number for today
- [ ] Poll or Livewire event refresh every 30 seconds

---

### MT-040.2.3 — Walk-in Token Creation

- [ ] Create `app/Livewire/Appointment/WalkinToken.php`
- [ ] Fast form: patient search → select patient → select doctor → generate token
- [ ] If new patient: button to register inline (slide-out form)
- [ ] Token number: sequential for the day per doctor
- [ ] On success: show token slip (printable)

---

### MT-040.2.4 — Appointment Booking Livewire

- [ ] Create `app/Livewire/Appointment/AppointmentBooking.php`
- [ ] Select patient (search), select doctor, select date, enter time slot
- [ ] Show existing appointments on selected date
- [ ] Booking confirmation with appointment summary
- [ ] Option to send confirmation (SMS/print)

---

### MT-040.2.5 — Token Service

- [ ] Create `app/Services/TokenService.php`
- [ ] Methods: `generateToken(Doctor $doctor, $date)`, `getQueue(Doctor $doctor, $date)`, `updateStatus(Appointment $appointment, string $status)`, `getDailyCount(Doctor $doctor, $date)`

---

## 1.3 Visit Creation

### MT-040.3.1 — Visit Migration & Model

- [ ] Create migration: `create_visits_table`
- [ ] Create `app/Models/Visit.php`
- [ ] Relationships: `belongsTo(Patient)`, `belongsTo(Doctor)`, `belongsTo(Appointment)`, `hasOne(CaseSheet)`, `hasMany(LabOrder)`, `hasOne(Invoice)`

---

### MT-040.3.2 — Visit Service

- [ ] Create `app/Services/VisitService.php`
- [ ] `createVisit(Patient $patient, Doctor $doctor, Appointment $appointment = null)` — creates visit record
- [ ] On visit creation: automatically create a blank `CaseSheet` linked to this visit
- [ ] `completeVisit(Visit $visit)` — marks visit as completed

---

## 1.4 OPD Consultation — Case Sheet

### MT-040.4.1 — Case Sheet Migration & Model

- [ ] Create migration: `create_case_sheets_table`
- [ ] Create `app/Models/CaseSheet.php`
- [ ] Relationships: `belongsTo(Visit)`, `belongsTo(Patient)`, `belongsTo(Doctor)`, `hasMany(Diagnosis)`, `hasOne(Prescription)`, `hasMany(LabOrder)`

---

### MT-040.4.2 — Case Sheet Editor Livewire (Main)

- [ ] Create `app/Livewire/OPD/CaseSheetEditor.php`
- [ ] Tabbed layout: Patient Info → Vitals → Complaints → History → Examination → Diagnosis → Lab Orders → Prescription → Advice → Follow Up
- [ ] Patient Info tab: read-only demographics panel
- [ ] Navigate between tabs without page reload
- [ ] Auto-save on tab switch (draft save)
- [ ] Finalize button: locks the case sheet (`is_finalized = true`)

---

### MT-040.4.3 — Vitals Entry Component

- [ ] Create `app/Livewire/OPD/VitalsEntry.php`
- [ ] Fields: BP (systolic / diastolic), Pulse, Temperature, SPO2, Weight, Height, Resp Rate
- [ ] Auto-calculate BMI on weight/height input
- [ ] Flag abnormal values with color (e.g. high BP = red)
- [ ] Migration: `create_vitals_table`
- [ ] Model: `Vital.php`
- [ ] Service: `VitalsService.php` — `record(Visit $visit, array $data)`, `getLatestForVisit(Visit $visit)`

---

### MT-040.4.4 — Diagnosis Entry

- [ ] Create `app/Livewire/OPD/DiagnosisEntry.php`
- [ ] Migration: `create_diagnoses_table`
- [ ] Model: `Diagnosis.php`
- [ ] Typeahead search from diagnosis master list
- [ ] Support multiple diagnoses per case sheet
- [ ] Tag as Primary or Secondary
- [ ] Free-text fallback if not in list

---

### MT-040.4.5 — Case Sheet Service

- [ ] Create `app/Services/CaseSheetService.php`
- [ ] Methods: `getOrCreate(Visit $visit)`, `update(CaseSheet $caseSheet, array $data)`, `finalize(CaseSheet $caseSheet)`, `getWithRelations(int $id)`
- [ ] Enforce: if `is_finalized = true`, reject further updates

---

## 1.5 Prescription System

### MT-040.5.1 — Prescription Migration & Model

- [ ] Create migration: `create_prescriptions_table`, `create_prescription_items_table`
- [ ] Model `Prescription.php`: `belongsTo(CaseSheet)`, `hasMany(PrescriptionItem)`
- [ ] Model `PrescriptionItem.php`: `belongsTo(Medicine)`, snapshot of medicine name at time of prescription

---

### MT-040.5.2 — Prescription Editor Livewire

- [ ] Create `app/Livewire/OPD/PrescriptionEditor.php`
- [ ] Add medicine: search by name (live typeahead from medicines table)
- [ ] Per medicine: Dosage (e.g. 1-0-1), Frequency (dropdown), Duration, Route, Instructions
- [ ] Repeatable rows — add/remove medicines dynamically
- [ ] Existing prescription auto-loads for this visit
- [ ] Save draft on each line change
- [ ] Print button → opens print view

---

### MT-040.5.3 — Prescription Service

- [ ] Create `app/Services/PrescriptionService.php`
- [ ] Methods: `getForVisit(Visit $visit)`, `addItem(Prescription $prescription, array $item)`, `removeItem(PrescriptionItem $item)`, `updateItem(PrescriptionItem $item, array $data)`, `finalize(Prescription $prescription)`
- [ ] Snapshot medicine name on creation (for historical accuracy)

---

### MT-040.5.4 — Prescription Print View

- [ ] Create `resources/views/print/prescription.blade.php`
- [ ] Use print layout (no nav)
- [ ] Show hospital header (from settings)
- [ ] Doctor name, qualification, registration number
- [ ] Patient details, date
- [ ] Medicines in formatted table
- [ ] Footer with signature line and disclaimer

---

## 1.6 Lab Order from OPD

### MT-040.6.1 — Lab Order Entry Livewire (OPD)

- [ ] Create `app/Livewire/OPD/LabOrderEntry.php`
- [ ] Search and select tests from lab test catalog
- [ ] Selected tests shown as list with remove option
- [ ] On submit: create `LabOrder` + `LabOrderItems`, mark status as `ordered`
- [ ] Migration: `create_lab_orders_table`, `create_lab_order_items_table`
- [ ] Model: `LabOrder.php`, `LabOrderItem.php`

---

### MT-040.6.2 — Lab Order Service

- [ ] Create `app/Services/LabOrderService.php`
- [ ] Methods: `create(Visit $visit, Doctor $doctor, array $testIds)`, `cancelItem(LabOrderItem $item)`, `getForVisit(Visit $visit)`

---

## 1.7 OPD Billing

### MT-040.7.1 — Invoice Migration & Model

- [ ] Create migration: `create_invoices_table`, `create_invoice_items_table`, `create_payments_table`
- [ ] Model: `Invoice.php`, `InvoiceItem.php`, `Payment.php`
- [ ] Auto-generate invoice number from settings prefix + sequence

---

### MT-040.7.2 — OPD Billing Livewire

- [ ] Create `app/Livewire/OPD/OpdBilling.php`
- [ ] Auto-load: consultation fee (from doctor settings)
- [ ] Add services manually (from services master)
- [ ] Add lab order charges (from pending lab orders for this visit)
- [ ] Show subtotal, discount (optional), total
- [ ] Payment entry: method (cash/card/UPI), amount
- [ ] Issue invoice button + print

---

### MT-040.7.3 — Invoice Service

- [ ] Create `app/Services/InvoiceService.php`
- [ ] Methods: `createOpdInvoice(Visit $visit, array $items)`, `addPayment(Invoice $invoice, array $payment)`, `getBalance(Invoice $invoice)`, `generateInvoiceNumber()`, `markPaid(Invoice $invoice)`, `cancel(Invoice $invoice)`

---

### MT-040.7.4 — Invoice Print View

- [ ] Create `resources/views/print/invoice.blade.php`
- [ ] Hospital header
- [ ] Patient details, invoice number, date
- [ ] Items table with qty, unit price, total
- [ ] Subtotal, discount, tax, total
- [ ] Payment details
- [ ] Signature block

---

## Phase 1 Checklist Summary

| Task Group | Items | Est. Hours |
|---|---|---|
| Patient Management | 6 tasks | 14h |
| Appointment & Token | 5 tasks | 10h |
| Visit System | 2 tasks | 4h |
| Case Sheet & Consultation | 5 tasks | 20h |
| Prescription System | 4 tasks | 12h |
| Lab Orders (OPD) | 2 tasks | 6h |
| OPD Billing | 4 tasks | 10h |
| **Total Phase 1** | **28 tasks** | **~76h** |
