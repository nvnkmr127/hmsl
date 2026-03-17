# 13 — Daily Automated Summary to Owner

> **Goal:** Every day at a configurable time, the system automatically compiles the day's activity into a rich summary and delivers it to the doctor-owner via Email and/or WhatsApp.

---

## Overview

The daily summary is the **doctor-owner's morning briefing** — delivered before they arrive at the clinic. It covers everything that happened yesterday: patients seen, revenue collected, pending tasks, lab results, admissions, stocks, and any critical alerts.

---

## Delivery Channels

| Channel | Method | Config |
|---|---|---|
| Email | HTML email via SMTP / SES | `MAIL_*` env vars |
| WhatsApp | Outbound webhook (WhatsApp Business API) | Configured webhook endpoint |
| SMS | Outbound HTTP request (Textlocal, Fast2SMS, etc.) | Settings |

---

## Schedule

- Default time: **6:00 AM** (configurable in Settings)
- Covers: **previous calendar day** (midnight to midnight)
- Can be manually triggered from Settings screen

---

## Summary Sections

### 1. OPD Summary
- Total patients seen
- New patients registered
- Returning patients
- Walk-in vs appointment breakdown
- Incomplete/pending case sheets (not finalized)

### 2. IPD Summary
- Currently admitted patients (count)
- New admissions yesterday
- Discharges yesterday
- Average length of stay (all current patients)
- Patients with pending lab results

### 3. Revenue Summary
- Total collections (all payment methods)
- OPD revenue
- IPD revenue
- Pharmacy revenue
- Lab revenue
- Discount given
- Outstanding balance (total pending)
- Payment method breakdown

### 4. Lab Summary
- Orders placed yesterday
- Results completed
- Pending results (ordered but not completed)
- Critical/abnormal results count

### 5. Pharmacy Summary
- Prescriptions dispensed
- Revenue from pharmacy
- Low stock items (below reorder level)
- Near-expiry items (within 30 days)

### 6. Appointment Summary (for today)
- Appointments booked for today
- Walk-in slots expected
- Doctor schedule overview

### 7. Alerts
- ⚠️ Medicines expiring within 7 days
- ⚠️ Out-of-stock items
- ⚠️ Overdue invoices (balance > 0, older than 7 days)
- ⚠️ Pending lab results older than 24 hours
- ⚠️ Patients admitted > 7 days without discharge note update

---

## Database Tables

### daily_summaries

| Column | Type | Notes |
|---|---|---|
| id | BIGINT UNSIGNED PK | |
| summary_date | DATE UNIQUE | The date being summarized |
| data | JSON | Full compiled summary payload |
| email_sent | TINYINT(1) DEFAULT 0 | |
| whatsapp_sent | TINYINT(1) DEFAULT 0 | |
| sms_sent | TINYINT(1) DEFAULT 0 | |
| sent_at | TIMESTAMP NULLABLE | |
| created_at | TIMESTAMP | |
| updated_at | TIMESTAMP | |

---

## Micro Tasks

### MT-130.1 — Daily Summary Service

- [ ] Create `app/Services/DailySummaryService.php`
- [ ] Method: `compile(Carbon $date): array` — fetches all data for the given date
- [ ] Sub-methods:
  - `compileOpdSection(Carbon $date)`
  - `compileIpdSection(Carbon $date)`
  - `compileRevenueSection(Carbon $date)`
  - `compileLabSection(Carbon $date)`
  - `compilePharmacySection(Carbon $date)`
  - `compileTodayAppointmentSection(Carbon $for)`
  - `compileAlerts()`
- [ ] Returns structured array (serializable to JSON for storage in `daily_summaries`)
- [ ] Cache result: store in `daily_summaries` table immediately after compile

---

### MT-130.2 — Daily Summary Migration & Model

- [ ] Create migration: `create_daily_summaries_table`
- [ ] Create `app/Models/DailySummary.php`
- [ ] Cast `data` column as array
- [ ] Scope: `scopeForDate(Carbon $date)`

---

### MT-130.3 — Email Template

- [ ] Create `resources/views/emails/daily-summary.blade.php`
- [ ] Rich HTML email design:
  - Hospital logo + name in header
  - Date in bold
  - Section cards: OPD, IPD, Revenue, Lab, Pharmacy, Appointments, Alerts
  - Color-coded numbers: green for good, red for alerts
  - Clickable "View Full Report" button linking to HMS dashboard
- [ ] Create `app/Mail/DailySummaryMail.php`
  - Subject: `Daily Summary — {Hospital Name} — {Date}`
  - Pass compiled data
  - Queue: send via `queue()` not `send()`

---

### MT-130.4 — WhatsApp Message Builder

- [ ] Create `app/Services/WhatsAppSummaryService.php`
- [ ] Build a text message (plain text + emojis) suitable for WhatsApp:
  ```
  📋 *Daily Summary — City Health*
  📅 17 Mar 2026

  🏥 *OPD*
  • 24 patients seen
  • 6 new registrations

  💰 *Revenue*
  • ₹18,500 collected
  • ₹3,200 outstanding

  ⚠️ *Alerts*
  • 3 medicines expiring soon
  • 2 pending lab results
  ```
- [ ] Send via configured WhatsApp webhook endpoint from the webhook system

---

### MT-130.5 — SMS Builder (Optional)

- [ ] Create `app/Services/SmsSummaryService.php`
- [ ] Compact message (SMS character limit):
  ```
  HMS Summary 17-Mar:
  OPD:24, IPD:5, Collection:18500
  Alerts:3 expiry, 2 lab pending
  ```
- [ ] Uses SMS gateway configured in settings (API URL, API Key)

---

### MT-130.6 — Scheduled Job

- [ ] Create `app/Jobs/SendDailySummaryJob.php`
- [ ] Steps:
  1. Get yesterday's date
  2. Check if summary already run for this date (idempotent)
  3. Call `DailySummaryService::compile(Carbon $date)`
  4. Store in `daily_summaries` table
  5. Send email to owner's email (from settings: `owner_email`)
  6. If WhatsApp configured: send WhatsApp message
  7. If SMS configured: send SMS
  8. Mark channels as sent
  9. Fire `daily.summary` webhook event
- [ ] Register in `app/Console/Kernel.php`:
  ```php
  $schedule->job(new SendDailySummaryJob)->dailyAt($time);
  ```
- [ ] Time from settings: `Setting::get('daily_summary_time', '06:00')`

---

### MT-130.7 — Settings for Daily Summary

- [ ] Add "Daily Summary" tab in Settings screen
- [ ] Fields:
  - Enable daily summary (toggle)
  - Delivery time (time picker)
  - Owner email (for email delivery)
  - WhatsApp number (for WhatsApp)
  - SMS number (for SMS)
  - Include sections (multi-checkbox: OPD, IPD, Revenue, Lab, etc.)
- [ ] All settings stored in `settings` table under group `daily_summary`

---

### MT-130.8 — Manual Trigger from UI

- [ ] In Settings → Daily Summary screen, add "Send Now" button
- [ ] Dispatches `SendDailySummaryJob` for selected date (default: today)
- [ ] Show last sent timestamp and status per channel

---

### MT-130.9 — Daily Summary History Screen

- [ ] Create `app/Livewire/Reports/DailySummaryHistory.php`
- [ ] Route: `/reports/daily-summary`
- [ ] Table: Date, OPD count, Revenue, Emails sent, Sent at
- [ ] Click row → expand to show full compiled data
- [ ] Re-send button per row
- [ ] Filter by date range

---

## Estimated Hours

| Task | Est. Hours |
|---|---|
| Daily Summary Service | 5h |
| DB migration & model | 1h |
| Email template (HTML) | 3h |
| WhatsApp message builder | 2h |
| SMS builder | 1h |
| Scheduled job | 2h |
| Settings screen additions | 2h |
| Manual trigger UI | 1h |
| Summary history screen | 3h |
| **Total** | **~20h** |
