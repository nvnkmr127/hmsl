# 10 — Master Task List (All Phases)

> **The single source of truth for development progress.**  
> Update status as work progresses: ⬜ Not Started | 🔄 In Progress | ✅ Done | 🚫 Blocked

---

## PHASE 0 — Core Foundation

### 0.A — Project Setup

| ID | Task | Est. Hours | Status | Notes |
|---|---|---|---|---|
| MT-000.1 | Create Laravel 11 project | 1h | ⬜ | |
| MT-000.2 | Configure `.env` file | 1h | ⬜ | |
| MT-000.3 | Install packages (Spatie, Livewire, DomPDF, Sanctum) | 2h | ⬜ | |
| MT-000.4 | Database setup & first migration run | 1h | ⬜ | |
| MT-000.5 | Configure authentication | 2h | ⬜ | |
| MT-000.6 | Redis setup & verification | 1h | ⬜ | |
| MT-000.7 | Queue and scheduler setup | 1h | ⬜ | |
| MT-000.8 | Logging and Telescope | 1h | ⬜ | |
| MT-000.9 | Application config (timezone, locale, storage) | 1h | ⬜ | |
| MT-000.10 | Git initialization | 0.5h | ⬜ | |

---

### 0.B — Theme System

| ID | Task | Est. Hours | Status | Notes |
|---|---|---|---|---|
| MT-030.1.1 | Layout setup (app, auth, print) | 2h | ⬜ | |
| MT-030.1.2 | Sidebar navigation component | 3h | ⬜ | |
| MT-030.1.3 | Top header component | 2h | ⬜ | |
| MT-030.1.4 | Dark/light mode system | 2h | ⬜ | |
| MT-030.1.5 | Reusable Blade components (card, alert, modal, badge) | 3h | ⬜ | |
| MT-030.1.6 | Form components (input, select, textarea, etc.) | 3h | ⬜ | |
| MT-030.1.7 | Table components (wrapper, th, empty, pagination) | 2h | ⬜ | |
| MT-030.1.8 | Toast notification system | 2h | ⬜ | |

---

### 0.C — Settings Module

| ID | Task | Est. Hours | Status | Notes |
|---|---|---|---|---|
| MT-030.2.1 | Settings model, migration, helpers | 2h | ⬜ | |
| MT-030.2.2 | Settings service | 2h | ⬜ | |
| MT-030.2.3 | Hospital details screen | 2h | ⬜ | |
| MT-030.2.4 | System preferences screen | 2h | ⬜ | |
| MT-030.2.5 | Invoice & print settings screen | 2h | ⬜ | |
| MT-030.2.6 | Settings routes & controller | 1h | ⬜ | |

---

### 0.D — Master Data

| ID | Task | Est. Hours | Status | Notes |
|---|---|---|---|---|
| MT-030.3.1 | Department CRUD (model, migration, Livewire, service) | 3h | ⬜ | |
| MT-030.3.2 | Doctor module (model, migration, Livewire, service) | 4h | ⬜ | |
| MT-030.3.3 | Services/Procedures CRUD | 2h | ⬜ | |
| MT-030.3.4 | Medicines CRUD | 3h | ⬜ | |
| MT-030.3.5 | Lab Tests + Parameters CRUD | 4h | ⬜ | |
| MT-030.3.6 | Ward and Bed manager | 4h | ⬜ | |

---

### 0.E — Roles & Permissions

| ID | Task | Est. Hours | Status | Notes |
|---|---|---|---|---|
| MT-030.4.1 | Spatie permission setup | 1h | ⬜ | |
| MT-030.4.2 | Roles & permissions seeder | 2h | ⬜ | |
| MT-030.4.3 | Middleware guards on routes | 2h | ⬜ | |
| MT-030.4.4 | Role-based dashboards | 3h | ⬜ | |
| MT-030.4.5 | User management screen | 3h | ⬜ | |

**Phase 0 Total:** ~64 tasks | ~55 hours

---

## PHASE 1 — OPD System

### 1.A — Patient Management

| ID | Task | Est. Hours | Status | Notes |
|---|---|---|---|---|
| MT-040.1.1 | Patient migration & model | 2h | ⬜ | |
| MT-040.1.2 | UHID generator | 1h | ⬜ | |
| MT-040.1.3 | Patient registration Livewire | 4h | ⬜ | |
| MT-040.1.4 | Patient search Livewire | 2h | ⬜ | |
| MT-040.1.5 | Patient profile Livewire | 3h | ⬜ | |
| MT-040.1.6 | Patient service | 2h | ⬜ | |

---

### 1.B — Appointment & Token

| ID | Task | Est. Hours | Status | Notes |
|---|---|---|---|---|
| MT-040.2.1 | Appointment migration & model | 1h | ⬜ | |
| MT-040.2.2 | Token queue Livewire | 3h | ⬜ | |
| MT-040.2.3 | Walk-in token creation | 3h | ⬜ | |
| MT-040.2.4 | Appointment booking Livewire | 3h | ⬜ | |
| MT-040.2.5 | Token service | 2h | ⬜ | |

---

### 1.C — Visit

| ID | Task | Est. Hours | Status | Notes |
|---|---|---|---|---|
| MT-040.3.1 | Visit migration & model | 1h | ⬜ | |
| MT-040.3.2 | Visit service | 2h | ⬜ | |

---

### 1.D — Case Sheet & Consultation

| ID | Task | Est. Hours | Status | Notes |
|---|---|---|---|---|
| MT-040.4.1 | Case sheet migration & model | 2h | ⬜ | |
| MT-040.4.2 | Case sheet editor Livewire (main) | 8h | ⬜ | Complex |
| MT-040.4.3 | Vitals entry Livewire + migration + model + service | 3h | ⬜ | |
| MT-040.4.4 | Diagnosis entry Livewire | 3h | ⬜ | |
| MT-040.4.5 | Case sheet service | 2h | ⬜ | |

---

### 1.E — Prescription

| ID | Task | Est. Hours | Status | Notes |
|---|---|---|---|---|
| MT-040.5.1 | Prescription migration & models | 2h | ⬜ | |
| MT-040.5.2 | Prescription editor Livewire | 5h | ⬜ | |
| MT-040.5.3 | Prescription service | 2h | ⬜ | |
| MT-040.5.4 | Prescription print view | 2h | ⬜ | |

---

### 1.F — Lab Orders (OPD)

| ID | Task | Est. Hours | Status | Notes |
|---|---|---|---|---|
| MT-040.6.1 | Lab order entry Livewire + migration + model | 3h | ⬜ | |
| MT-040.6.2 | Lab order service | 2h | ⬜ | |

---

### 1.G — OPD Billing

| ID | Task | Est. Hours | Status | Notes |
|---|---|---|---|---|
| MT-040.7.1 | Invoice migration & models | 2h | ⬜ | |
| MT-040.7.2 | OPD billing Livewire | 4h | ⬜ | |
| MT-040.7.3 | Invoice service | 3h | ⬜ | |
| MT-040.7.4 | Invoice print view | 2h | ⬜ | |

**Phase 1 Total:** ~28 tasks | ~76 hours

---

## PHASE 2 — IPD System

### 2.A — Admission

| ID | Task | Est. Hours | Status | Notes |
|---|---|---|---|---|
| MT-050.1.1 | Admission migration & model | 2h | ⬜ | |
| MT-050.1.2 | Admission form Livewire | 4h | ⬜ | |
| MT-050.1.3 | Admission service | 3h | ⬜ | |

---

### 2.B — Bed & Ward

| ID | Task | Est. Hours | Status | Notes |
|---|---|---|---|---|
| MT-050.2.1 | Ward & bed migration & models | 1h | ⬜ | |
| MT-050.2.2 | Ward/Bed manager Livewire | 3h | ⬜ | |
| MT-050.2.3 | Bed selector component | 2h | ⬜ | |
| MT-050.2.4 | Bed service | 2h | ⬜ | |

---

### 2.C — IPD Dashboard

| ID | Task | Est. Hours | Status | Notes |
|---|---|---|---|---|
| MT-050.3.1 | IPD patient view page (tabbed shell) | 3h | ⬜ | |
| MT-050.3.2 | Active IPD list Livewire | 2h | ⬜ | |

---

### 2.D — IPD Notes

| ID | Task | Est. Hours | Status | Notes |
|---|---|---|---|---|
| MT-050.4.1 | IPD notes migration & model | 1h | ⬜ | |
| MT-050.4.2 | Doctor notes Livewire | 3h | ⬜ | |
| MT-050.4.3 | Nursing notes Livewire | 2h | ⬜ | |
| MT-050.4.4 | IPD note service | 2h | ⬜ | |

---

### 2.E — IPD Vitals

| ID | Task | Est. Hours | Status | Notes |
|---|---|---|---|---|
| MT-050.5.1 | IPD vitals migration & model | 1h | ⬜ | |
| MT-050.5.2 | IPD vitals Livewire | 2h | ⬜ | |

---

### 2.F — Medication Chart

| ID | Task | Est. Hours | Status | Notes |
|---|---|---|---|---|
| MT-050.6.1 | Medication chart migration & model | 1h | ⬜ | |
| MT-050.6.2 | Medication chart Livewire | 4h | ⬜ | |

---

### 2.G — IPD Lab & Billing

| ID | Task | Est. Hours | Status | Notes |
|---|---|---|---|---|
| MT-050.7.1 | IPD lab order Livewire | 2h | ⬜ | Reuses services |
| MT-050.8.1 | IPD billing Livewire | 4h | ⬜ | |
| MT-050.8.2 | IPD invoice service extension | 3h | ⬜ | |

**Phase 2 Total:** ~20 tasks | ~62 hours

---

## PHASE 3 — Discharge Summary

| ID | Task | Est. Hours | Status | Notes |
|---|---|---|---|---|
| MT-060.1.1 | Discharge summary migration & models | 2h | ⬜ | |
| MT-060.1.2 | Discharge summary form Livewire (tabbed) | 6h | ⬜ | Complex |
| MT-060.1.3 | Discharge summary service | 3h | ⬜ | |
| MT-060.2.1 | Workflow status machine | 2h | ⬜ | |
| MT-060.2.2 | Discharge action buttons | 2h | ⬜ | |
| MT-060.3.1 | Discharge print view | 3h | ⬜ | |
| MT-060.3.2 | PDF generation service | 3h | ⬜ | |
| MT-060.3.3 | Configurable print templates | 2h | ⬜ | |

**Phase 3 Total:** ~8 tasks | ~23 hours

---

## PHASE 4 — Supporting Modules

### 4.A — Pharmacy

| ID | Task | Est. Hours | Status | Notes |
|---|---|---|---|---|
| MT-070.1.1 | Pharmacy dashboard Livewire | 2h | ⬜ | |
| MT-070.1.2 | Prescription dispensing Livewire | 5h | ⬜ | |
| MT-070.1.3 | Pharmacy billing Livewire | 2h | ⬜ | |
| MT-070.1.4 | Dispense migration & models | 2h | ⬜ | |
| MT-070.1.5 | Pharmacy service | 3h | ⬜ | |

---

### 4.B — Laboratory

| ID | Task | Est. Hours | Status | Notes |
|---|---|---|---|---|
| MT-070.2.1 | Lab dashboard Livewire | 2h | ⬜ | |
| MT-070.2.2 | Lab order list Livewire | 2h | ⬜ | |
| MT-070.2.3 | Sample collection Livewire | 2h | ⬜ | |
| MT-070.2.4 | Result entry Livewire | 4h | ⬜ | |
| MT-070.2.5 | Lab report print view | 2h | ⬜ | |
| MT-070.2.6 | Lab result service | 2h | ⬜ | |

---

### 4.C — Inventory

| ID | Task | Est. Hours | Status | Notes |
|---|---|---|---|---|
| MT-070.3.1 | Inventory migration & models | 3h | ⬜ | |
| MT-070.3.2 | Inventory list Livewire | 3h | ⬜ | |
| MT-070.3.3 | Stock adjustment Livewire | 2h | ⬜ | |
| MT-070.3.4 | Purchase order Livewire | 3h | ⬜ | |
| MT-070.3.5 | Supplier management | 2h | ⬜ | |
| MT-070.3.6 | Stock service | 3h | ⬜ | |
| MT-070.3.7 | Expiry alert command | 2h | ⬜ | |

---

### 4.D — Reports

| ID | Task | Est. Hours | Status | Notes |
|---|---|---|---|---|
| MT-070.4.1 | Patient report Livewire | 2h | ⬜ | |
| MT-070.4.2 | Revenue report Livewire | 3h | ⬜ | |
| MT-070.4.3 | Pharmacy report Livewire | 2h | ⬜ | |
| MT-070.4.4 | Lab report summary Livewire | 2h | ⬜ | |
| MT-070.4.5 | Report service | 3h | ⬜ | |

**Phase 4 Total:** ~23 tasks | ~57 hours

---

## PHASE 5 — Webhooks System

| ID | Task | Est. Hours | Status | Notes |
|---|---|---|---|---|
| MT-110.1 | DB: webhook_endpoints + webhook_logs migrations & models | 2h | ✅ | |
| MT-110.2 | WebhookService (dispatch, sign, log) | 3h | ✅ | |
| MT-110.3 | SendWebhookJob with exponential backoff retry | 3h | ✅ | |
| MT-110.4 | Laravel Events + WebhookDispatcher listener | 4h | ✅ | |
| MT-110.5 | Fire events from all services | 3h | ✅ | |
| MT-110.6 | Webhook management UI (Livewire) | 4h | ✅ | |
| MT-110.7 | Webhook logs UI (Livewire, retry button) | 3h | ✅ | |
| MT-110.8 | Webhook API endpoints | 2h | ✅ | |

**Phase 5 Total:** 8 tasks | ~24 hours

---

## PHASE 6 — Enhanced Reports

| ID | Task | Est. Hours | Status | Notes |
|---|---|---|---|---|
| MT-120.1 | Report Service architecture + ReportFilter DTO | 3h | ✅ | |
| MT-120.2 | OPD Report Livewire (charts, filters, export) | 3h | ✅ | |
| MT-120.3 | IPD Report Livewire (occupancy, LOS, ward breakdown) | 3h | ⬜ | |
| MT-120.4 | Revenue Report Livewire (trend chart, method breakdown) | 4h | ✅ | |
| MT-120.5 | Outstanding Dues Report (aging buckets) | 2h | ✅ | |
| MT-120.6 | Patient Demographics Report | 3h | ⬜ | |
| MT-120.7 | Diagnosis Frequency Report | 2h | ⬜ | |
| MT-120.8 | Pharmacy Sales Report | 2h | ⬜ | |
| MT-120.9 | Pharmacy Stock Report | 2h | ⬜ | |
| MT-120.10 | Lab Analytics Report | 3h | ⬜ | |
| MT-120.11 | Bed Occupancy Report | 3h | ⬜ | |
| MT-120.12 | Doctor Performance Report | 3h | ✅ | |
| MT-120.13 | Export Service (PDF + CSV) | 3h | ✅ | |
| MT-120.14 | Scheduled Reports (save, email, job) | 4h | ⬜ | |
| MT-120.15 | Chart.js integration (bar, pie, line) | 3h | ⬜ | |
| MT-120.16 | Reports navigation + role permissions | 2h | ✅ | |

**Phase 6 Total:** 16 tasks | ~45 hours

---

## PHASE 7 — Daily Automated Summary

| ID | Task | Est. Hours | Status | Notes |
|---|---|---|---|---|
| MT-130.1 | DailySummaryService (compile all sections) | 5h | ⬜ | |
| MT-130.2 | DB migration + DailySummary model | 1h | ⬜ | |
| MT-130.3 | HTML email template + DailySummaryMail class | 3h | ⬜ | |
| MT-130.4 | WhatsApp message builder (text format) | 2h | ⬜ | |
| MT-130.5 | SMS builder (compact format) | 1h | ⬜ | |
| MT-130.6 | SendDailySummaryJob + Scheduler registration | 2h | ⬜ | |
| MT-130.7 | Settings page: daily summary config | 2h | ⬜ | |
| MT-130.8 | Manual trigger button in Settings UI | 1h | ⬜ | |
| MT-130.9 | Daily Summary History screen | 3h | ⬜ | |

**Phase 7 Total:** 9 tasks | ~20 hours

---

## PHASE 8 — Additional Features

| ID | Task | Priority | Est. Hours | Status |
|---|---|---|---|---|
| MT-140.1.1–1.5 | In-App Notification system (bell, dropdown, types) | High | 8h | ⬜ |
| MT-140.2.1–2.5 | Audit Log system (Auditable trait, UI) | High | 6h | ⬜ |
| MT-140.3.1–3.5 | Patient Portal (token auth, read-only screens) | Medium | 12h | ⬜ |
| MT-140.4.1–4.5 | Appointment Reminders (24h + 2h before) | High | 4h | ⬜ |
| MT-140.5.1–5.5 | Email/SMS/WhatsApp gateway integration | High | 8h | ⬜ |
| MT-140.6.1–6.4 | Prescription Templates (save + load) | High | 5h | ⬜ |
| MT-140.7.1–7.4 | Clinical Note Templates (quick text) | Medium | 4h | ⬜ |
| MT-140.8.1–8.3 | Follow-Up Tracker (today + overdue) | High | 4h | ⬜ |
| MT-140.9.1–9.5 | Bed Transfer tracking | Medium | 4h | ⬜ |
| MT-140.10.1–10.4 | Referral Out + Referral Letter PDF | Medium | 4h | ⬜ |
| MT-140.11.1–11.5 | Consent Forms (digital, PDF) | Low | 6h | ⬜ |
| MT-140.12.1 | System Health Dashboard | Medium | 4h | ⬜ |
| MT-140.13.1–13.6 | Backup System (spatie/laravel-backup) | High | 3h | ⬜ |
| MT-140.14.1–14.4 | Multi-language scaffold | Low | 3h | ⬜ |

**Phase 8 Total:** ~14 feature groups | ~75 hours

---

## Grand Summary (Updated)

| Phase | Tasks / Groups | Estimated Hours |
|---|---|---|
| Phase 0 — Foundation | 34 | ~55h |
| Phase 1 — OPD | 28 | ~76h |
| Phase 2 — IPD | 20 | ~62h |
| Phase 3 — Discharge | 8 | ~23h |
| Phase 4 — Supporting Modules | 23 | ~57h |
| Phase 5 — Webhooks | 8 | ~24h |
| Phase 6 — Enhanced Reports | 16 | ~45h |
| Phase 7 — Daily Automated Summary | 9 | ~20h |
| Phase 8 — Additional Features | 14 | ~75h |
| **Total** | **~160 tasks** | **~437 hours** |

> **Estimated calendar time:** 2–3 developers × full-time = approx. **14–20 weeks** for complete system.

---

## Development Priority Order (Updated)

```
Project Setup
    ↓
Theme System → Settings Module → Master Data + Roles
    ↓
Patient Module
    ↓
OPD (Token → Case Sheet → Prescription → Billing)
    ↓
IPD (Admission → Daily Records → Billing)
    ↓
Discharge Summary
    ↓
Pharmacy → Laboratory → Inventory
    ↓
Webhooks System                 ← enables event-driven integrations
    ↓
Email/SMS/WhatsApp Gateway      ← enables all communication features
    ↓
In-App Notifications            ← improves daily UX for all roles
    ↓
Appointment Reminders           ← reduces no-shows immediately
    ↓
Prescription Templates          ← speeds up doctor's workflow
    ↓
Follow-Up Tracker               ← closes the care loop
    ↓
Enhanced Reports (all 12)       ← business intelligence for owner
    ↓
Daily Automated Summary         ← morning briefing for owner
    ↓
Audit Logs → Backup System
    ↓
Patient Portal
    ↓
Bed Transfer → Referral Out → Consent Forms
    ↓
System Health Dashboard → Multi-Language Scaffold
```

---

## Coding Standards & Rules

- [ ] All business logic in Service classes — never in controllers or Livewire components
- [ ] All Livewire components use `#[Validate]` attribute for validation
- [ ] All models have explicit `$fillable` or `$guarded`
- [ ] All foreign keys have database-level constraints
- [ ] Soft deletes on all critical entity tables
- [ ] All new records linked to `created_by` (auth user id)
- [ ] Redis cache invalidated whenever settings change
- [ ] Print views never include Livewire scripts
- [ ] Medical records are append-only — no hard delete on case sheets, prescriptions, lab results, discharge summaries
- [ ] All outbound communication (SMS, email, WhatsApp) logged to `communication_logs`
- [ ] All webhook deliveries logged to `webhook_logs`
- [ ] All sensitive model changes recorded in `audit_logs`
- [ ] All scheduled jobs are idempotent (safe to re-run)
- [ ] Queue jobs must handle failures gracefully and log errors
