# 05 — Phase 2: IPD System Micro Tasks

> **Prerequisite:** Phase 0 and Phase 1 must be complete.  
> **Goal:** Full in-patient workflow — Admission → Daily Rounds → Medication Chart → Lab → Billing.

---

## 2.1 Patient Admission

### MT-050.1.1 — Admission Migration & Model

- [ ] Create migration: `create_admissions_table`
- [ ] Create `app/Models/Admission.php`
- [ ] Relationships: `belongsTo(Patient)`, `belongsTo(Doctor)`, `belongsTo(Bed)`, `belongsTo(Ward)`, `hasMany(IpdNote)`, `hasMany(IpdVital)`, `hasMany(IpdMedicationChart)`, `hasOne(DischargeSummary)`
- [ ] Add `admission_number` auto-generation: `IPD-{YYYY}-{seq}` (e.g. `IPD-2026-0001`)
- [ ] Add scope: `scopeActive()` → status = admitted

---

### MT-050.1.2 — Admission Form Livewire

- [ ] Create `app/Livewire/IPD/AdmissionForm.php`
- [ ] Step 1: Search and select patient (reuse `PatientSearch` component)
- [ ] Step 2: Select admitting doctor
- [ ] Step 3: Select ward → then select available bed (filtered by ward)
- [ ] Additional: Admission date/time, reason for admission, chief complaints
- [ ] Admission notes (free text)
- [ ] On submit: mark bed as `occupied`, create admission record, redirect to IPD patient view

---

### MT-050.1.3 — Admission Service

- [ ] Create `app/Services/AdmissionService.php`
- [ ] Methods: `admit(Patient $patient, Doctor $doctor, Bed $bed, array $data)`, `discharge(Admission $admission, array $dischargeData)`, `transferBed(Admission $admission, Bed $newBed)`, `getActive()`, `getForPatient(Patient $patient)`
- [ ] In `admit()`: wrap in DB transaction, use `BedService::allocate()`
- [ ] In `discharge()`: release bed, set discharge date, update status

---

## 2.2 Bed and Ward Management

### MT-050.2.1 — Ward and Bed Migration & Models

- [ ] Create migration: `create_wards_table`
- [ ] Create migration: `create_beds_table`
- [ ] Model `Ward.php`: `hasMany(Bed)`
- [ ] Model `Bed.php`: `belongsTo(Ward)`, `hasOne(Admission)` (active)
- [ ] Add scope: `Bed::scopeAvailable()` — status = available

---

### MT-050.2.2 — Ward/Bed Manager Livewire

- [ ] Create `app/Livewire/Master/WardBedManager.php`
- [ ] Show all wards with expandable bed list
- [ ] Color-coded beds: green = available, red = occupied, yellow = maintenance
- [ ] Add ward form (name, type, charge per day)
- [ ] Add beds to ward (bed number, add multiple at once)
- [ ] Toggle bed status (maintenance/available)
- [ ] Click occupied bed → show patient info in tooltip/modal

---

### MT-050.2.3 — Bed Selector Component

- [ ] Create `app/Livewire/IPD/BedSelector.php`
- [ ] Filter by ward → show available beds only
- [ ] Display bed grid/list with status colors
- [ ] Click to select, confirm selection

---

### MT-050.2.4 — Bed Service

- [ ] Create `app/Services/BedService.php`
- [ ] Methods: `allocate(Bed $bed, Admission $admission)`, `release(Bed $bed)`, `getAvailable(Ward $ward = null)`, `getOccupancyRate()`

---

## 2.3 IPD Patient Dashboard

### MT-050.3.1 — IPD Patient View

- [ ] Create a dedicated IPD patient page: `/ipd/{admission}/`
- [ ] Show: Patient demographics, admission details (ward, bed, doctor, date)
- [ ] Tabs: Overview, Doctor Notes, Nursing Notes, Vitals, Medication Chart, Lab Orders, Billing
- [ ] Each tab is a separate Livewire component (lazy loaded)
- [ ] Header: Status badge (Admitted/Discharged), Quick actions: Add Note, Record Vitals, order Lab

---

### MT-050.3.2 — Active IPD List Livewire

- [ ] Create `app/Livewire/IPD/ActiveAdmissionList.php`
- [ ] Table: Patient name, UHID, Ward/Bed, Doctor, Admission date, Days admitted
- [ ] Filter by ward, doctor
- [ ] Search by patient name or UHID
- [ ] Action: View IPD chart, Initiate Discharge

---

## 2.4 IPD Case Sheet (Daily Records)

### MT-050.4.1 — IPD Notes Migration & Model

- [ ] Create migration: `create_ipd_notes_table`
- [ ] Create `app/Models/IpdNote.php`
- [ ] Relationships: `belongsTo(Admission)`, `belongsTo(User, 'created_by')`
- [ ] Types: `doctor`, `nurse`, `procedure`

---

### MT-050.4.2 — Doctor Notes Livewire

- [ ] Create `app/Livewire/IPD/IpdNoteEntry.php`
- [ ] Date/time stamp on each note (auto-filled, editable)
- [ ] Rich text area for note content
- [ ] Type selector: Doctor Note / Procedure Note
- [ ] Show all past notes in reverse chronological order
- [ ] Notes read-only after 24 hours (configurable setting)
- [ ] Role guard: only doctor or doctor_owner can add doctor notes

---

### MT-050.4.3 — Nursing Notes Livewire

- [ ] Reuse `IpdNoteEntry.php` or create `NursingNoteEntry.php`
- [ ] Quick text buttons: "Patient comfortable", "BP recorded", etc.
- [ ] Role guard: nurse or doctor_owner

---

### MT-050.4.4 — IPD Note Service

- [ ] Create `app/Services/IpdNoteService.php`
- [ ] Methods: `addNote(Admission $admission, string $type, string $content, User $by)`, `getDoctorNotes(Admission $admission)`, `getNursingNotes(Admission $admission)`, `isEditable(IpdNote $note)`

---

## 2.5 IPD Vitals Tracking

### MT-050.5.1 — IPD Vitals Migration & Model

- [ ] Create migration: `create_ipd_vitals_table`
- [ ] Create `app/Models/IpdVital.php`
- [ ] Relationships: `belongsTo(Admission)`

---

### MT-050.5.2 — IPD Vitals Livewire

- [ ] Create `app/Livewire/IPD/IpdVitals.php`
- [ ] Quick entry form: BP, Pulse, Temp, SPO2, Weight, Resp Rate
- [ ] Show vitals history as table: date/time, all values, recorded by
- [ ] Optionally: simple trend display (last 3 readings inline)
- [ ] Role guard: nurse or doctor_owner can record

---

## 2.6 IPD Medication Chart

### MT-050.6.1 — Medication Chart Migration & Model

- [ ] Create migration: `create_ipd_medication_charts_table`
- [ ] Create `app/Models/IpdMedicationChart.php`
- [ ] Relationships: `belongsTo(Admission)`, `belongsTo(Medicine)`, `belongsTo(User, 'prescribed_by')`

---

### MT-050.6.2 — Medication Chart Livewire

- [ ] Create `app/Livewire/IPD/MedicationChart.php`
- [ ] Add medicine: search by name, dosage, frequency, route, start/end dates
- [ ] Show active medications as a table
- [ ] Toggle: stop a medication (`is_active = false`)
- [ ] Role guard: only doctor or doctor_owner can add/stop medicines
- [ ] Nurse can mark administered (future enhancement — flag for now)

---

## 2.7 IPD Lab Orders

### MT-050.7.1 — IPD Lab Order Livewire

- [ ] Create `app/Livewire/IPD/IpdLabOrder.php`
- [ ] Reuse `LabOrderService` — pass `admission_id` and source = `ipd`
- [ ] Show all existing lab orders for this admission
- [ ] Status: ordered → sample collected → processing → completed

---

## 2.8 IPD Billing

### MT-050.8.1 — IPD Billing Livewire

- [ ] Create `app/Livewire/IPD/IpdBilling.php`
- [ ] Auto-calculate bed charges: `days_admitted × ward.charge_per_day`
- [ ] Add doctor visit charges (services master)
- [ ] Add lab charges (from lab orders for this admission)
- [ ] Add pharmacy charges (from dispenses for this admission)
- [ ] Add procedure charges
- [ ] Allow manual additions
- [ ] Partial payment tracking
- [ ] Final bill: generate invoice → print

---

### MT-050.8.2 — IPD Invoice Service

- [ ] Extend `InvoiceService.php`
- [ ] Add `createIpdInvoice(Admission $admission)` — auto-populate all charges
- [ ] Compute bed days: `carbon diff` between admission date and discharge date
- [ ] Aggregate lab orders, pharmacy dispenses linked to this admission

---

## Phase 2 Checklist Summary

| Task Group | Items | Est. Hours |
|---|---|---|
| Admission | 3 tasks | 12h |
| Bed & Ward | 4 tasks | 8h |
| IPD Dashboard | 2 tasks | 6h |
| IPD Daily Notes | 4 tasks | 10h |
| Vitals Tracking | 2 tasks | 4h |
| Medication Chart | 2 tasks | 8h |
| IPD Lab Orders | 1 task | 4h |
| IPD Billing | 2 tasks | 10h |
| **Total Phase 2** | **20 tasks** | **~62h** |
