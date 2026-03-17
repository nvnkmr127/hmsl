# 12 — Enhanced Reports System

> **Goal:** Build a comprehensive, filterable, exportable reports module covering every operational and financial dimension of the hospital.

---

## Report Categories

| Category | Reports |
|---|---|
| **Clinical** | OPD visits, IPD admissions, Discharge summary stats, Diagnosis frequency |
| **Financial** | Revenue, Collections, Outstanding dues, Payment methods, Doctor-wise revenue |
| **Patient** | New vs returning, Demographics, Visit frequency, Referred by source |
| **Pharmacy** | Sales, Stock, Dispensing, Expiry |
| **Laboratory** | Orders, Turnaround time, Result stats, Revenue |
| **Inventory** | Stock valuation, Low stock, Purchase history, Supplier-wise |
| **Operations** | Daily OPD count, Bed occupancy, Appointment compliance |

---

## Database Tables

### report_schedules

| Column | Type | Notes |
|---|---|---|
| id | BIGINT UNSIGNED PK | |
| report_type | VARCHAR(100) | e.g. `daily_summary`, `monthly_revenue` |
| frequency | ENUM('daily','weekly','monthly') | |
| recipients | JSON | Array of email addresses |
| filters | JSON | Saved filter config |
| is_active | TINYINT(1) DEFAULT 1 | |
| last_run_at | TIMESTAMP NULLABLE | |
| created_by | BIGINT UNSIGNED FK NULLABLE | |
| created_at | TIMESTAMP | |
| updated_at | TIMESTAMP | |

---

### saved_reports

| Column | Type | Notes |
|---|---|---|
| id | BIGINT UNSIGNED PK | |
| name | VARCHAR(150) | |
| report_type | VARCHAR(100) | |
| filters | JSON | Saved filter state |
| created_by | BIGINT UNSIGNED FK | |
| created_at | TIMESTAMP | |
| updated_at | TIMESTAMP | |

---

## Micro Tasks

---

### MT-120.1 — Report Service Architecture

- [ ] Create `app/Services/ReportService.php` (refactor from Phase 4)
- [ ] Each report method accepts a `ReportFilter` DTO (value object)
- [ ] `ReportFilter` DTO: `from`, `to`, `doctor_id`, `department_id`, `invoice_type`, `ward_id`
- [ ] All reports return structured arrays / collections suitable for Livewire rendering AND PDF export
- [ ] Avoid N+1 — all queries use aggregations + eager loading

---

### MT-120.2 — OPD Report

- [ ] Livewire: `app/Livewire/Reports/OpdReport.php`
- [ ] Filters: date range, doctor, department
- [ ] Metrics:
  - Total visits
  - New patients vs returning patients
  - Walk-in vs appointment
  - Visit count by day (bar chart)
  - Top 10 diagnoses
  - Average consultation time (if tracked)
  - Doctor-wise OPD count
- [ ] Export: PDF, CSV

---

### MT-120.3 — IPD Report

- [ ] Livewire: `app/Livewire/Reports/IpdReport.php`
- [ ] Filters: date range, ward, doctor
- [ ] Metrics:
  - Total admissions
  - Total discharges
  - Current occupancy (active admissions)
  - Average length of stay (days)
  - Bed occupancy rate (%) per ward
  - Admissions by ward type
  - Doctor-wise admissions
- [ ] Export: PDF, CSV

---

### MT-120.4 — Revenue Report

- [ ] Livewire: `app/Livewire/Reports/RevenueReport.php`
- [ ] Filters: date range, invoice type, doctor, payment method
- [ ] Metrics:
  - Gross revenue
  - Collected (paid) amount
  - Outstanding balance
  - Discount given
  - Net revenue
  - Revenue by type (OPD / IPD / Pharmacy / Lab)
  - Revenue trend (line chart — daily/weekly/monthly toggle)
  - Payment method breakdown (cash, card, UPI, cheque)
  - Doctor-wise revenue (fee collected)
- [ ] Export: PDF, CSV

---

### MT-120.5 — Outstanding Dues Report

- [ ] Livewire: `app/Livewire/Reports/OutstandingDues.php`
- [ ] Show all invoices where `balance > 0`
- [ ] Group by: patient
- [ ] Aging buckets: 0–7 days, 8–30 days, 31–90 days, 90+ days
- [ ] Action: click patient → go to billing page
- [ ] Total outstanding amount in header
- [ ] Export: PDF, CSV

---

### MT-120.6 — Patient Demographics Report

- [ ] Livewire: `app/Livewire/Reports/PatientDemographics.php`
- [ ] Filters: date range (registration date)
- [ ] Metrics:
  - Gender distribution (pie chart)
  - Age group distribution (0–18, 19–40, 41–60, 60+)
  - City/area distribution
  - Blood group distribution
  - New registrations per month (bar chart)
  - Top 10 referring sources
- [ ] Export: PDF, CSV

---

### MT-120.7 — Diagnosis Frequency Report

- [ ] Livewire: `app/Livewire/Reports/DiagnosisReport.php`
- [ ] Filters: date range, doctor
- [ ] Top 20 most common diagnoses
- [ ] Trend over time for selected diagnosis
- [ ] Export: PDF

---

### MT-120.8 — Pharmacy Sales Report

- [ ] Livewire: `app/Livewire/Reports/PharmacySalesReport.php`
- [ ] Filters: date range
- [ ] Metrics:
  - Total sales amount
  - Number of dispenses
  - Top 10 medicines sold (by qty and by revenue)
  - Category-wise sales
  - Linked to OPD prescription vs direct sale
- [ ] Export: PDF, CSV

---

### MT-120.9 — Pharmacy Stock Report

- [ ] Livewire: `app/Livewire/Reports/PharmacyStockReport.php`
- [ ] Filters: category, expiry range
- [ ] Show: all items with current quantity, value (qty × cost price), expiry status
- [ ] Summary: total stock value, low stock count, expiring count
- [ ] Export: PDF, CSV

---

### MT-120.10 — Laboratory Report

- [ ] Livewire: `app/Livewire/Reports/LabAnalyticsReport.php`
- [ ] Filters: date range
- [ ] Metrics:
  - Total tests ordered
  - Completed vs pending
  - Average turnaround time per test type
  - Most ordered tests (top 10)
  - Lab revenue
  - Abnormal result rate
- [ ] Export: PDF, CSV

---

### MT-120.11 — Bed Occupancy Report

- [ ] Livewire: `app/Livewire/Reports/BedOccupancyReport.php`
- [ ] Filters: date range, ward
- [ ] Metrics:
  - Occupancy % per ward per day
  - Average occupancy over period
  - Timeline heatmap (ward vs date grid)
  - Total bed-days utilized
- [ ] Export: PDF

---

### MT-120.12 — Doctor Performance Report

- [ ] Livewire: `app/Livewire/Reports/DoctorPerformanceReport.php`
- [ ] Filters: date range, doctor
- [ ] Metrics:
  - OPD patients seen
  - IPD admissions
  - Revenue generated
  - Average prescriptions per visit
  - Patient follow-up compliance
- [ ] Role guard: only `doctor_owner` and `accountant` can view

---

### MT-120.13 — Report Export Service

- [ ] Create `app/Services/ReportExportService.php`
- [ ] Methods:
  - `exportToPdf(string $view, array $data, string $filename)`
  - `exportToCsv(array $headers, array $rows, string $filename)`
  - `exportToExcel(array $sheets, string $filename)` (optional — use `maatwebsite/excel`)
- [ ] All exports stream directly to browser (no intermediate file storage for on-demand reports)
- [ ] Scheduled reports saved to `storage/app/reports/` and emailed

---

### MT-120.14 — Saved Reports & Scheduled Reports

- [ ] Create migration: `create_report_schedules_table`, `create_saved_reports_table`
- [ ] Livewire: `app/Livewire/Reports/ReportScheduler.php`
  - Save current filter as named report
  - Schedule: daily / weekly / monthly
  - Add email recipients
  - Toggle active/inactive
- [ ] Create `app/Jobs/GenerateScheduledReportJob.php`
  - Generate PDF for report type
  - Email to all recipients with PDF attachment
- [ ] Register in scheduler: `$schedule->job(GenerateScheduledReportJob::class)->dailyAt('06:00')`

---

### MT-120.15 — Charts Integration

- [ ] Include **Chart.js** via CDN in `app.blade.php`
- [ ] Create Blade component: `<x-chart type="bar" :data="$chartData" id="revenue-chart" />`
- [ ] All chart data prepared as JSON in Livewire `computed` properties
- [ ] Alpine.js initializes Chart.js on component mount
- [ ] Chart types used: Bar (trends), Pie/Donut (distribution), Line (time series)

---

### MT-120.16 — Reports Navigation & Permissions

- [ ] Reports landing page: `/reports` — card grid of available reports
- [ ] Each report card shows: icon, title, description, last viewed date
- [ ] Role-based visibility:
  - `accountant` → Revenue, Outstanding, Pharmacy Sales
  - `doctor_owner` → All reports
  - `pharmacist` → Stock, Sales
  - `lab_technician` → Lab Analytics
  - `receptionist` → OPD, Patient Demographics

---

## Report Screen List

| Screen | Route | Component |
|---|---|---|
| Reports Dashboard | `/reports` | Reports landing grid |
| OPD Report | `/reports/opd` | `Reports/OpdReport` |
| IPD Report | `/reports/ipd` | `Reports/IpdReport` |
| Revenue Report | `/reports/revenue` | `Reports/RevenueReport` |
| Outstanding Dues | `/reports/dues` | `Reports/OutstandingDues` |
| Patient Demographics | `/reports/patients` | `Reports/PatientDemographics` |
| Diagnosis Frequency | `/reports/diagnosis` | `Reports/DiagnosisReport` |
| Pharmacy Sales | `/reports/pharmacy/sales` | `Reports/PharmacySalesReport` |
| Pharmacy Stock | `/reports/pharmacy/stock` | `Reports/PharmacyStockReport` |
| Lab Analytics | `/reports/lab` | `Reports/LabAnalyticsReport` |
| Bed Occupancy | `/reports/beds` | `Reports/BedOccupancyReport` |
| Doctor Performance | `/reports/doctors` | `Reports/DoctorPerformanceReport` |
| Report Scheduler | `/reports/scheduled` | `Reports/ReportScheduler` |

---

## Estimated Hours

| Task | Est. Hours |
|---|---|
| Report Service (DTO + base) | 3h |
| OPD Report | 3h |
| IPD Report | 3h |
| Revenue Report | 4h |
| Outstanding Dues | 2h |
| Patient Demographics | 3h |
| Diagnosis Frequency | 2h |
| Pharmacy Sales | 2h |
| Pharmacy Stock | 2h |
| Lab Analytics | 3h |
| Bed Occupancy | 3h |
| Doctor Performance | 3h |
| Export Service (PDF + CSV) | 3h |
| Scheduled Reports + Job | 4h |
| Charts Integration | 3h |
| Navigation + Permissions | 2h |
| **Total** | **~45h** |
