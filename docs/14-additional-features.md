# 14 — Additional Features

> **Goal:** Document all extended features that enhance the HMS system beyond the core modules — covering notifications, audit, communication, patient engagement, operations, and system health.

---

## Feature 1 — In-App Notification System

### Purpose
Real-time notifications inside the HMS UI for all roles.

### Database Table: `notifications` *(Laravel built-in via Notifiable trait)*

### Notification Types

| Notification | Recipient Role | Trigger |
|---|---|---|
| New token created | Doctor | Walk-in or appointment token made |
| Lab results ready | Doctor | Lab order completed |
| Prescription dispensed | Doctor/Receptionist | Pharmacy dispenses |
| Low stock alert | Pharmacist, Doctor Owner | Inventory below reorder level |
| Expiry alert | Pharmacist | Medicine expiring in 30 days |
| Invoice paid | Accountant, Doctor Owner | Payment recorded |
| Invoice overdue | Accountant | Balance > 0 after 7 days |
| Patient admitted | All IPD staff | New IPD admission |
| Patient discharged | All IPD staff | Discharge finalized |
| New lab order | Lab Technician | Lab order placed from OPD/IPD |
| Discharge pending review | Doctor Owner | Discharge in review status |

### Micro Tasks

- [ ] **MT-140.1.1** — Install Laravel notification channels: `laravel/notifications` (built-in)
- [ ] **MT-140.1.2** — Create Notification classes for each event in `app/Notifications/`
- [ ] **MT-140.1.3** — Create Livewire: `app/Livewire/Shared/NotificationBell.php`
  - Badge count in top header
  - Dropdown on click: list of unread notifications
  - Mark all as read button
  - Click → navigate to relevant record
- [ ] **MT-140.1.4** — Add Livewire polling (every 30s) or push via Laravel Echo + Redis pub/sub
- [ ] **MT-140.1.5** — Notification preferences: settings to enable/disable per type per role

---

## Feature 2 — Audit Log System

### Purpose
Complete audit trail of every create, update, and delete performed on critical records. Required for medical compliance and security.

### Database Table: `audit_logs`

| Column | Type | Notes |
|---|---|---|
| id | BIGINT UNSIGNED PK | |
| user_id | BIGINT UNSIGNED FK NULLABLE | Who performed the action |
| model_type | VARCHAR(100) | e.g. `App\Models\CaseSheet` |
| model_id | BIGINT UNSIGNED | |
| action | ENUM('created','updated','deleted','restored') | |
| old_values | JSON NULLABLE | Previous state |
| new_values | JSON NULLABLE | New state |
| ip_address | VARCHAR(45) NULLABLE | |
| user_agent | TEXT NULLABLE | |
| created_at | TIMESTAMP | |

### Micro Tasks

- [ ] **MT-140.2.1** — Create `AuditLog` model and migration
- [ ] **MT-140.2.2** — Create `app/Traits/Auditable.php` trait
- [ ] **MT-140.2.3** — Apply `Auditable` trait to all critical models: `CaseSheet`, `Prescription`, `LabResult`, `DischargeSummary`, `Invoice`, `Payment`, `Admission`, `Patient`
- [ ] **MT-140.2.4** — Create Livewire: `app/Livewire/Settings/AuditLog.php`
  - Filter by model, user, date, action
  - Show old vs new values (diff view)
  - Route: `/settings/audit-log`
- [ ] **MT-140.2.5** — Retain audit logs for minimum 5 years (add archival cron)

---

## Feature 3 — Patient Portal (Read-Only)

### Purpose
Allow patients to view their own records via a secure link or portal login.

### Features
- View past visit summaries
- Download prescriptions as PDF
- View lab reports
- View invoices and payment status
- Upcoming appointments

### Micro Tasks

- [ ] **MT-140.3.1** — Create patient portal auth: `portal_tokens` table (token, patient_id, expires_at)
- [ ] **MT-140.3.2** — Send secure link via SMS/WhatsApp after each visit
- [ ] **MT-140.3.3** — Create separate portal layout: `resources/views/layouts/portal.blade.php`
- [ ] **MT-140.3.4** — Portal screens:
  - MY Visits: `/portal/visits`
  - Visit Detail + Prescription: `/portal/visits/{id}`
  - Lab Reports: `/portal/lab-reports`
  - Invoices: `/portal/invoices`
- [ ] **MT-140.3.5** — All portal routes under `/portal/*` with `portal.auth` middleware

---

## Feature 4 — Appointment Reminders (Scheduled)

### Purpose
Automatically remind patients of upcoming appointments via SMS / WhatsApp 24 hours and 2 hours before.

### Micro Tasks

- [ ] **MT-140.4.1** — Create `app/Jobs/SendAppointmentReminderJob.php`
- [ ] **MT-140.4.2** — Register in scheduler: run every 30 minutes, find appointments in next 24h and 2h windows
- [ ] **MT-140.4.3** — Track sent: add `reminder_24h_sent`, `reminder_2h_sent` columns to `appointments` table
- [ ] **MT-140.4.4** — Message format for WhatsApp and SMS
- [ ] **MT-140.4.5** — Setting: enable/disable reminders globally and per channel

---

## Feature 5 — Email & SMS Gateway Integration

### Purpose
Central outbound communication layer for all system-generated messages.

### Database Table: `communication_logs`

| Column | Type | Notes |
|---|---|---|
| id | BIGINT UNSIGNED PK | |
| channel | ENUM('email','sms','whatsapp') | |
| recipient | VARCHAR(150) | Phone or email |
| template | VARCHAR(100) | e.g. `appointment_reminder` |
| content | TEXT | Actual message sent |
| status | ENUM('sent','failed','pending') | |
| reference_type | VARCHAR(100) NULLABLE | e.g. Appointment |
| reference_id | BIGINT NULLABLE | |
| error_message | TEXT NULLABLE | |
| sent_at | TIMESTAMP NULLABLE | |
| created_at | TIMESTAMP | |

### Micro Tasks

- [ ] **MT-140.5.1** — Create `app/Services/SmsService.php`
  - Interface-based: swap SMS gateway without code change
  - Support: Fast2SMS, Textlocal, Twilio
  - Config: driver in settings
- [ ] **MT-140.5.2** — Create `app/Services/WhatsAppService.php`
  - Send via WhatsApp Business API or third-party (Interakt, WATI)
  - Template-based messages (for Business API compliance)
- [ ] **MT-140.5.3** — Create `app/Services/CommunicationService.php`
  - Unified `send(channel, recipient, template, data)` method
  - Logs all sends to `communication_logs`
- [ ] **MT-140.5.4** — Communication logs UI: `/settings/communication-logs`
- [ ] **MT-140.5.5** — Test message button in settings to verify gateway connection

---

## Feature 6 — Prescription Templates

### Purpose
Allow doctor to save frequently used prescription combinations as named templates to speed up data entry.

### Database Tables

**prescription_templates**  
| Column | Type | Notes |
|---|---|---|
| id | BIGINT UNSIGNED PK | |
| name | VARCHAR(150) | e.g. "URTI Standard" |
| doctor_id | BIGINT UNSIGNED FK | |
| is_shared | TINYINT(1) DEFAULT 0 | Shared with all doctors |
| created_at | TIMESTAMP | |

**prescription_template_items**  
| Column | Type | Notes |
|---|---|---|
| id | BIGINT UNSIGNED PK | |
| prescription_template_id | BIGINT UNSIGNED FK | |
| medicine_id | BIGINT UNSIGNED FK | |
| medicine_name | VARCHAR(150) | |
| dosage | VARCHAR(50) | |
| frequency | VARCHAR(100) | |
| duration | VARCHAR(50) | |
| route | VARCHAR(50) | |
| instructions | TEXT | |

### Micro Tasks

- [ ] **MT-140.6.1** — Create migrations and models
- [ ] **MT-140.6.2** — In `PrescriptionEditor` Livewire: add "Load Template" button
  - Opens modal with list of saved templates
  - Click template → fills prescription rows
- [ ] **MT-140.6.3** — "Save as Template" button on prescription editor
  - Name the template, save current medicines
- [ ] **MT-140.6.4** — Template management screen: view, edit, delete templates
- [ ] Route: `/master/prescription-templates`

---

## Feature 7 — Clinical Notes Templates

### Purpose
Quick pre-filled text blocks for common clinical phrases to speed up doctor's case sheet entry.

### Micro Tasks

- [ ] **MT-140.7.1** — Create `clinical_templates` table: `id`, `section`, `label`, `content`, `is_global`, `doctor_id`
- [ ] **MT-140.7.2** — In case sheet textarea fields, add a "Quick Text" button
- [ ] **MT-140.7.3** — Opens a modal: select category (complaints, examination, advice) → shows list of saved phrases → click to insert
- [ ] **MT-140.7.4** — Manage templates from: `/master/clinical-templates` (CRUD Livewire)

---

## Feature 8 — OPD/IPD Follow-Up Tracker

### Purpose
Track all patients who have a follow-up date set and surface them in a daily "Due Follow-Ups" view.

### Micro Tasks

- [ ] **MT-140.8.1** — Create `app/Livewire/OPD/FollowUpTracker.php`
  - Show today's follow-ups (patients with `follow_up_date = today`)
  - Show overdue follow-ups (follow_up_date < today, no new visit)
  - Action: Create new OPD token for patient
  - Route: `/opd/follow-ups`
- [ ] **MT-140.8.2** — Add to reception dashboard as a widget (patients due today)
- [ ] **MT-140.8.3** — Optional: send follow-up reminder SMS/WhatsApp day before

---

## Feature 9 — Bed Transfer (IPD)

### Purpose
Move a patient from one bed/ward to another during admission.

### Database Table: `bed_transfers`

| Column | Type | Notes |
|---|---|---|
| id | BIGINT UNSIGNED PK | |
| admission_id | BIGINT UNSIGNED FK | |
| from_bed_id | BIGINT UNSIGNED FK | |
| to_bed_id | BIGINT UNSIGNED FK | |
| transfer_date | DATETIME | |
| reason | TEXT NULLABLE | |
| transferred_by | BIGINT UNSIGNED FK | |
| created_at | TIMESTAMP | |

### Micro Tasks

- [ ] **MT-140.9.1** — Create migration and model
- [ ] **MT-140.9.2** — In IPD patient view: "Transfer Bed" button
- [ ] **MT-140.9.3** — Livewire modal: select new ward → available bed
- [ ] **MT-140.9.4** — On transfer: create `bed_transfer` record, release old bed, allocate new bed
- [ ] **MT-140.9.5** — Show transfer history in IPD patient overview tab

---

## Feature 10 — Referral Out Tracking

### Purpose
When a patient is referred out to another hospital, log the referral details.

### Database Table: `referrals`

| Column | Type | Notes |
|---|---|---|
| id | BIGINT UNSIGNED PK | |
| patient_id | BIGINT UNSIGNED FK | |
| visit_id | BIGINT UNSIGNED FK NULLABLE | |
| admission_id | BIGINT UNSIGNED FK NULLABLE | |
| referred_to_hospital | VARCHAR(200) | |
| referred_to_doctor | VARCHAR(150) NULLABLE | |
| referral_reason | TEXT | |
| referral_date | DATE | |
| created_by | BIGINT UNSIGNED FK | |
| created_at | TIMESTAMP | |

### Micro Tasks

- [ ] **MT-140.10.1** — Create migration and model
- [ ] **MT-140.10.2** — Add "Refer Out" button in case sheet and IPD chart
- [ ] **MT-140.10.3** — Referral form: hospital name, doctor name, reason
- [ ] **MT-140.10.4** — Generate referral letter (PDF): patient details, diagnosis, reason, referring doctor signature

---

## Feature 11 — Consent Forms

### Purpose
Digital record of patient consent for procedures, admission, and surgery.

### Database Table: `consent_forms`

| Column | Type | Notes |
|---|---|---|
| id | BIGINT UNSIGNED PK | |
| patient_id | BIGINT UNSIGNED FK | |
| visit_id / admission_id | BIGINT UNSIGNED FK NULLABLE | |
| consent_type | VARCHAR(100) | general, surgery, anaesthesia |
| content | TEXT | The consent text shown |
| signed_by | VARCHAR(150) | Patient or guardian name |
| relationship | VARCHAR(100) NULLABLE | If signed by guardian |
| signature_path | VARCHAR(255) NULLABLE | Digital signature image |
| consented_at | TIMESTAMP | |
| created_by | BIGINT UNSIGNED FK | |

### Micro Tasks

- [ ] **MT-140.11.1** — Create migration and model
- [ ] **MT-140.11.2** — Consent form templates in settings (general, procedure, surgery)
- [ ] **MT-140.11.3** — Livewire consent form: show template text, capture name of signatory
- [ ] **MT-140.11.4** — Optional touch signature capture (using Signature Pad JS library)
- [ ] **MT-140.11.5** — PDF generation for consent record

---

## Feature 12 — System Health Dashboard

### Purpose
Internal monitoring for the **doctor-owner/admin** to ensure the system is running correctly.

### Screens

- [ ] **MT-140.12.1** — Create `/admin/system-health` route
- [ ] Metrics shown:
  - Queue worker status (running / stopped)
  - Pending jobs count
  - Failed jobs count (with retry button)
  - Redis connectivity
  - Disk usage
  - Last scheduled job run times (daily summary, expiry alert, etc.)
  - Recent webhook failures
  - Recent error logs (last 10 exceptions from `laravel.log`)
- [ ] Auto-refresh every 60 seconds
- [ ] Add to doctor_owner sidebar under "System"

---

## Feature 13 — Backup System

### Purpose
Automated daily database and file backups.

### Micro Tasks

- [ ] **MT-140.13.1** — Install `spatie/laravel-backup`
- [ ] **MT-140.13.2** — Configure backup: DB dump + public storage files
- [ ] **MT-140.13.3** — Schedule: daily backup at 02:00 AM
- [ ] **MT-140.13.4** — Store backups to: local disk + optionally AWS S3
- [ ] **MT-140.13.5** — Send backup success/fail notification to owner email
- [ ] **MT-140.13.6** — Backup management screen: `/admin/backups` — list, download, delete

---

## Feature 14 — Multi-Language Support (Scaffold Only)

### Purpose
Prepare the codebase for future localization without implementing full translations now.

### Micro Tasks

- [ ] **MT-140.14.1** — Use Laravel's `__()` helper for all UI strings
- [ ] **MT-140.14.2** — Create `lang/en/` with key files: `auth.php`, `patients.php`, `opd.php`, `billing.php`, etc.
- [ ] **MT-140.14.3** — Language switcher in settings (off by default, English only)
- [ ] **MT-140.14.4** — Document: adding a new language is just adding a new lang folder

---

## Additional Features Summary

| Feature | Priority | Est. Hours |
|---|---|---|
| In-App Notifications | High | 8h |
| Audit Log System | High | 6h |
| Patient Portal | Medium | 12h |
| Appointment Reminders | High | 4h |
| Email/SMS Gateway Integration | High | 8h |
| Prescription Templates | High | 5h |
| Clinical Note Templates | Medium | 4h |
| Follow-Up Tracker | High | 4h |
| Bed Transfer | Medium | 4h |
| Referral Out Tracking | Medium | 4h |
| Consent Forms | Low | 6h |
| System Health Dashboard | Medium | 4h |
| Backup System | High | 3h |
| Multi-Language Scaffold | Low | 3h |
| **Total** | | **~75h** |
