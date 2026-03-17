# 06 — Phase 3: Discharge Summary Micro Tasks

> **Prerequisite:** Phase 0, 1, and 2 must be complete.  
> **Goal:** Complete digital discharge process — from draft to finalized, printable summary.

---

## 3.1 Discharge Summary Module

### MT-060.1.1 — Discharge Summary Migration & Model

- [ ] Create migration: `create_discharge_summaries_table`
- [ ] Create migration: `create_discharge_medications_table`
- [ ] Create `app/Models/DischargeSummary.php`
- [ ] Create `app/Models/DischargeMedication.php`
- [ ] Relationships:
  - `DischargeSummary` → `belongsTo(Admission)`, `belongsTo(Patient)`, `belongsTo(Doctor)`, `hasMany(DischargeMedication)`
  - `DischargeMedication` → `belongsTo(DischargeSummary)`
- [ ] Status ENUM: `draft`, `review`, `finalized`
- [ ] Add observer: when `status` changes to `finalized`, prevent further edits

---

### MT-060.1.2 — Discharge Summary Form Livewire

- [ ] Create `app/Livewire/Discharge/DischargeSummaryForm.php`
- [ ] Auto-populate from admission data: patient info, doctor, dates
- [ ] Sections (tabbed):
  1. **Diagnosis** — Admission diagnosis, Final diagnosis (from IPD case entries)
  2. **Treatment Summary** — Free text, procedures done (multiselect from procedure list)
  3. **Medications on Discharge** — Repeatable rows: medicine name, dosage, frequency, duration, instructions
  4. **Condition** — Condition at discharge dropdown (Stable / Improved / Critical / Referred)
  5. **Advice** — General advice, Diet advice, Activity advice
  6. **Follow Up** — Follow-up date, follow-up notes
- [ ] Auto-load: pull medications from IPD medication chart (active ones as a starting list, editable)
- [ ] Draft save on each section navigation

---

### MT-060.1.3 — Discharge Summary Service

- [ ] Create `app/Services/DischargeSummaryService.php`
- [ ] Methods:
  - `createDraft(Admission $admission)` — create or get draft linked to admission
  - `update(DischargeSummary $ds, array $data)` — save section updates
  - `submitForReview(DischargeSummary $ds)` — move to `review` status
  - `finalize(DischargeSummary $ds, User $by)` — move to `finalized`, lock record, timestamp
  - `getForAdmission(Admission $admission)`
- [ ] Guard: if `is_finalized`, throw exception on any update attempt
- [ ] Auto-populate medications: copy from IPD medication chart

---

## 3.2 Discharge Workflow

### MT-060.2.1 — Workflow Status Machine

- [ ] Implement status transitions: `draft → review → finalized`
- [ ] Only `doctor_owner` can finalize
- [ ] `doctor_owner` or `nurse` can move to review
- [ ] Finalized discharge cannot be edited
- [ ] Show current status with color badge in the discharge form header
- [ ] Action buttons change based on status:
  - **Draft:** "Save Draft", "Submit for Review"
  - **Review:** "Back to Draft", "Finalize Discharge"
  - **Finalized:** "Print Summary" only

---

### MT-060.2.2 — Discharge Action Livewire

- [ ] Extend `DischargeSummaryForm.php` with status workflow buttons
- [ ] "Initiate Discharge" button: appears on IPD patient view → creates draft discharge summary
- [ ] Confirmation modal before finalizing
- [ ] On finalize: call `AdmissionService::discharge()` → set bed to available, set discharge date
- [ ] Emit event to refresh IPD patient view

---

---

## 3.3 Discharge Print & Templates

### MT-060.3.1 — Discharge Summary Print View

- [ ] Create `resources/views/print/discharge-summary.blade.php`
- [ ] Use print layout (no sidebar, no nav)
- [ ] Pull hospital header from settings (hospital name, address, phone, logo)
- [ ] Pull doctor info: name, qualification, registration number
- [ ] Sections in print:
  1. Patient Demographics (name, age, gender, UHID)
  2. Admission & Discharge Dates
  3. Admission Diagnosis
  4. Final Diagnosis
  5. Treatment Summary
  6. Procedures Done
  7. Investigations Summary (lab results summary)
  8. Medications on Discharge (table)
  9. Condition at Discharge
  10. Advice
  11. Follow Up
  12. Doctor signature block
- [ ] Footer from settings (disclaimer text)

---

### MT-060.3.2 — PDF Generation

- [ ] Use `barryvdh/laravel-dompdf` to generate PDF
- [ ] Create `app/Services/PdfService.php`
- [ ] Method: `generateDischargeSummary(DischargeSummary $ds): Response`
- [ ] Method: `generatePrescription(Prescription $prescription): Response`
- [ ] Method: `generateInvoice(Invoice $invoice): Response`
- [ ] Store generated PDFs in `storage/app/pdfs/discharge/` (optional, for reprint)

---

### MT-060.3.3 — Configurable Print Templates

- [ ] In Settings module, add "Print Settings" section
- [ ] Configurable: Show/hide hospital logo on print, header text, footer text
- [ ] Configurable: Paper size (A4 / A5 / Letter)
- [ ] All print views read these settings dynamically via `SettingsService`

---

## Phase 3 Checklist Summary

| Task Group | Items | Est. Hours |
|---|---|---|
| Discharge Module | 3 tasks | 12h |
| Workflow System | 2 tasks | 6h |
| Print & PDF | 3 tasks | 10h |
| **Total Phase 3** | **8 tasks** | **~28h** |
