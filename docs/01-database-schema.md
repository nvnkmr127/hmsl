# 01 — Full Database Schema

> **Goal:** Define all tables, columns, data types, relationships, and indexes for the entire HMS system.

---

## Schema Design Principles

- All tables include `id` (BIGINT UNSIGNED, primary key, auto increment)
- All tables include `created_at`, `updated_at` (TIMESTAMP, nullable)
- Soft deletes (`deleted_at`) on critical tables
- Foreign keys enforced at DB level
- Audit columns: `created_by`, `updated_by` (nullable FK to `users.id`)
- Use `ENUM` only for stable fixed values; use lookup tables for dynamic values

---

## Tables

---

### users

| Column | Type | Notes |
|---|---|---|
| id | BIGINT UNSIGNED PK | |
| name | VARCHAR(150) | |
| email | VARCHAR(150) UNIQUE | |
| password | VARCHAR(255) | |
| phone | VARCHAR(20) NULLABLE | |
| is_active | TINYINT(1) DEFAULT 1 | |
| remember_token | VARCHAR(100) NULLABLE | |
| created_at | TIMESTAMP NULLABLE | |
| updated_at | TIMESTAMP NULLABLE | |
| deleted_at | TIMESTAMP NULLABLE | Soft delete |

---

### roles *(managed by Spatie)*

| Column | Type | Notes |
|---|---|---|
| id | BIGINT UNSIGNED PK | |
| name | VARCHAR(125) UNIQUE | doctor_owner, receptionist, nurse, lab_technician, pharmacist, accountant |
| guard_name | VARCHAR(125) | web |
| created_at | TIMESTAMP | |
| updated_at | TIMESTAMP | |

---

### permissions *(managed by Spatie)*

Standard Spatie permission tables: `permissions`, `model_has_roles`, `model_has_permissions`, `role_has_permissions`

---

### settings

| Column | Type | Notes |
|---|---|---|
| id | BIGINT UNSIGNED PK | |
| key | VARCHAR(100) UNIQUE | e.g. hospital_name, currency |
| value | TEXT NULLABLE | |
| group | VARCHAR(50) NULLABLE | hospital, invoice, print, system |
| created_at | TIMESTAMP | |
| updated_at | TIMESTAMP | |

---

### departments

| Column | Type | Notes |
|---|---|---|
| id | BIGINT UNSIGNED PK | |
| name | VARCHAR(100) | |
| code | VARCHAR(20) NULLABLE | |
| is_active | TINYINT(1) DEFAULT 1 | |
| created_at | TIMESTAMP | |
| updated_at | TIMESTAMP | |
| deleted_at | TIMESTAMP NULLABLE | |

---

### doctors

| Column | Type | Notes |
|---|---|---|
| id | BIGINT UNSIGNED PK | |
| user_id | BIGINT UNSIGNED FK → users.id | |
| department_id | BIGINT UNSIGNED FK → departments.id NULLABLE | |
| qualification | VARCHAR(200) NULLABLE | |
| specialization | VARCHAR(200) NULLABLE | |
| registration_number | VARCHAR(100) NULLABLE | |
| consultation_fee | DECIMAL(10,2) DEFAULT 0 | |
| signature | VARCHAR(255) NULLABLE | path to image |
| is_active | TINYINT(1) DEFAULT 1 | |
| created_at | TIMESTAMP | |
| updated_at | TIMESTAMP | |
| deleted_at | TIMESTAMP NULLABLE | |

---

### services

| Column | Type | Notes |
|---|---|---|
| id | BIGINT UNSIGNED PK | |
| department_id | BIGINT UNSIGNED FK NULLABLE | |
| name | VARCHAR(150) | |
| code | VARCHAR(50) NULLABLE | |
| price | DECIMAL(10,2) DEFAULT 0 | |
| is_active | TINYINT(1) DEFAULT 1 | |
| created_at | TIMESTAMP | |
| updated_at | TIMESTAMP | |
| deleted_at | TIMESTAMP NULLABLE | |

---

### patients

| Column | Type | Notes |
|---|---|---|
| id | BIGINT UNSIGNED PK | |
| uhid | VARCHAR(20) UNIQUE | Auto generated (e.g. HMS-0001) |
| name | VARCHAR(150) | |
| dob | DATE NULLABLE | |
| age | TINYINT UNSIGNED NULLABLE | Stored if DOB not available |
| age_unit | ENUM('years','months','days') DEFAULT 'years' | |
| gender | ENUM('male','female','other') | |
| blood_group | VARCHAR(5) NULLABLE | |
| phone | VARCHAR(20) | |
| email | VARCHAR(150) NULLABLE | |
| address | TEXT NULLABLE | |
| city | VARCHAR(100) NULLABLE | |
| state | VARCHAR(100) NULLABLE | |
| pincode | VARCHAR(10) NULLABLE | |
| emergency_contact_name | VARCHAR(150) NULLABLE | |
| emergency_contact_phone | VARCHAR(20) NULLABLE | |
| referred_by | VARCHAR(150) NULLABLE | |
| notes | TEXT NULLABLE | |
| photo | VARCHAR(255) NULLABLE | |
| created_by | BIGINT UNSIGNED FK NULLABLE | |
| created_at | TIMESTAMP | |
| updated_at | TIMESTAMP | |
| deleted_at | TIMESTAMP NULLABLE | |

---

### appointments

| Column | Type | Notes |
|---|---|---|
| id | BIGINT UNSIGNED PK | |
| patient_id | BIGINT UNSIGNED FK → patients.id | |
| doctor_id | BIGINT UNSIGNED FK → doctors.id | |
| appointment_date | DATE | |
| appointment_time | TIME NULLABLE | |
| token_number | VARCHAR(20) NULLABLE | |
| type | ENUM('walkin','appointment') DEFAULT 'walkin' | |
| status | ENUM('waiting','in_progress','completed','cancelled') DEFAULT 'waiting' | |
| notes | TEXT NULLABLE | |
| created_by | BIGINT UNSIGNED FK NULLABLE | |
| created_at | TIMESTAMP | |
| updated_at | TIMESTAMP | |

---

### visits

| Column | Type | Notes |
|---|---|---|
| id | BIGINT UNSIGNED PK | |
| patient_id | BIGINT UNSIGNED FK → patients.id | |
| appointment_id | BIGINT UNSIGNED FK NULLABLE → appointments.id | |
| doctor_id | BIGINT UNSIGNED FK → doctors.id | |
| visit_date | DATE | |
| visit_type | ENUM('opd','ipd_followup') DEFAULT 'opd' | |
| status | ENUM('registered','in_consultation','completed') DEFAULT 'registered' | |
| created_by | BIGINT UNSIGNED FK NULLABLE | |
| created_at | TIMESTAMP | |
| updated_at | TIMESTAMP | |

---

### case_sheets

| Column | Type | Notes |
|---|---|---|
| id | BIGINT UNSIGNED PK | |
| visit_id | BIGINT UNSIGNED FK → visits.id UNIQUE | One per visit |
| patient_id | BIGINT UNSIGNED FK → patients.id | |
| doctor_id | BIGINT UNSIGNED FK → doctors.id | |
| chief_complaints | TEXT NULLABLE | |
| history_of_present_illness | TEXT NULLABLE | |
| past_medical_history | TEXT NULLABLE | |
| family_history | TEXT NULLABLE | |
| personal_history | TEXT NULLABLE | |
| drug_allergy | TEXT NULLABLE | |
| examination_notes | TEXT NULLABLE | |
| diagnosis | TEXT NULLABLE | |
| advice | TEXT NULLABLE | |
| follow_up_date | DATE NULLABLE | |
| follow_up_notes | TEXT NULLABLE | |
| is_finalized | TINYINT(1) DEFAULT 0 | |
| created_by | BIGINT UNSIGNED FK NULLABLE | |
| created_at | TIMESTAMP | |
| updated_at | TIMESTAMP | |

---

### vitals

| Column | Type | Notes |
|---|---|---|
| id | BIGINT UNSIGNED PK | |
| visit_id | BIGINT UNSIGNED FK → visits.id | |
| patient_id | BIGINT UNSIGNED FK → patients.id | |
| bp_systolic | SMALLINT UNSIGNED NULLABLE | mmHg |
| bp_diastolic | SMALLINT UNSIGNED NULLABLE | mmHg |
| pulse | SMALLINT UNSIGNED NULLABLE | bpm |
| temperature | DECIMAL(4,1) NULLABLE | °F |
| spo2 | TINYINT UNSIGNED NULLABLE | % |
| weight | DECIMAL(5,2) NULLABLE | kg |
| height | DECIMAL(5,2) NULLABLE | cm |
| bmi | DECIMAL(4,2) NULLABLE | Auto calculated |
| resp_rate | TINYINT UNSIGNED NULLABLE | /min |
| recorded_at | TIMESTAMP | |
| recorded_by | BIGINT UNSIGNED FK NULLABLE | |

---

### diagnoses

| Column | Type | Notes |
|---|---|---|
| id | BIGINT UNSIGNED PK | |
| case_sheet_id | BIGINT UNSIGNED FK → case_sheets.id | |
| icd_code | VARCHAR(20) NULLABLE | |
| description | VARCHAR(255) | |
| type | ENUM('primary','secondary') DEFAULT 'primary' | |
| created_at | TIMESTAMP | |
| updated_at | TIMESTAMP | |

---

### medicines

| Column | Type | Notes |
|---|---|---|
| id | BIGINT UNSIGNED PK | |
| name | VARCHAR(150) | |
| generic_name | VARCHAR(150) NULLABLE | |
| category | VARCHAR(100) NULLABLE | |
| form | VARCHAR(50) NULLABLE | tablet, syrup, injection |
| strength | VARCHAR(50) NULLABLE | |
| unit | VARCHAR(30) NULLABLE | mg, ml |
| manufacturer | VARCHAR(150) NULLABLE | |
| is_active | TINYINT(1) DEFAULT 1 | |
| created_at | TIMESTAMP | |
| updated_at | TIMESTAMP | |
| deleted_at | TIMESTAMP NULLABLE | |

---

### prescriptions

| Column | Type | Notes |
|---|---|---|
| id | BIGINT UNSIGNED PK | |
| case_sheet_id | BIGINT UNSIGNED FK → case_sheets.id | |
| visit_id | BIGINT UNSIGNED FK → visits.id | |
| patient_id | BIGINT UNSIGNED FK → patients.id | |
| doctor_id | BIGINT UNSIGNED FK → doctors.id | |
| notes | TEXT NULLABLE | |
| is_dispensed | TINYINT(1) DEFAULT 0 | |
| dispensed_at | TIMESTAMP NULLABLE | |
| created_at | TIMESTAMP | |
| updated_at | TIMESTAMP | |

---

### prescription_items

| Column | Type | Notes |
|---|---|---|
| id | BIGINT UNSIGNED PK | |
| prescription_id | BIGINT UNSIGNED FK → prescriptions.id | |
| medicine_id | BIGINT UNSIGNED FK → medicines.id | |
| medicine_name | VARCHAR(150) | Snapshot at prescription time |
| dosage | VARCHAR(50) NULLABLE | e.g. 1-0-1 |
| frequency | VARCHAR(100) NULLABLE | Twice daily |
| duration | VARCHAR(50) NULLABLE | 5 Days |
| route | VARCHAR(50) NULLABLE | Oral |
| instructions | TEXT NULLABLE | Take after food |
| quantity | SMALLINT UNSIGNED DEFAULT 1 | |
| created_at | TIMESTAMP | |
| updated_at | TIMESTAMP | |

---

### lab_tests

| Column | Type | Notes |
|---|---|---|
| id | BIGINT UNSIGNED PK | |
| name | VARCHAR(150) | |
| code | VARCHAR(50) NULLABLE | |
| category | VARCHAR(100) NULLABLE | Haematology, Biochemistry |
| price | DECIMAL(10,2) DEFAULT 0 | |
| turnaround_time | TINYINT NULLABLE | hours |
| is_active | TINYINT(1) DEFAULT 1 | |
| created_at | TIMESTAMP | |
| updated_at | TIMESTAMP | |

---

### lab_test_parameters

| Column | Type | Notes |
|---|---|---|
| id | BIGINT UNSIGNED PK | |
| lab_test_id | BIGINT UNSIGNED FK → lab_tests.id | |
| parameter_name | VARCHAR(100) | |
| unit | VARCHAR(30) NULLABLE | |
| normal_range | VARCHAR(100) NULLABLE | |
| sort_order | TINYINT DEFAULT 0 | |
| created_at | TIMESTAMP | |
| updated_at | TIMESTAMP | |

---

### lab_orders

| Column | Type | Notes |
|---|---|---|
| id | BIGINT UNSIGNED PK | |
| order_number | VARCHAR(30) UNIQUE | Auto generated |
| patient_id | BIGINT UNSIGNED FK → patients.id | |
| visit_id | BIGINT UNSIGNED FK → visits.id NULLABLE | |
| admission_id | BIGINT UNSIGNED FK → admissions.id NULLABLE | |
| doctor_id | BIGINT UNSIGNED FK → doctors.id | |
| source | ENUM('opd','ipd') DEFAULT 'opd' | |
| status | ENUM('ordered','sample_collected','processing','completed') DEFAULT 'ordered' | |
| ordered_at | TIMESTAMP | |
| collected_at | TIMESTAMP NULLABLE | |
| completed_at | TIMESTAMP NULLABLE | |
| created_by | BIGINT UNSIGNED FK NULLABLE | |
| created_at | TIMESTAMP | |
| updated_at | TIMESTAMP | |

---

### lab_order_items

| Column | Type | Notes |
|---|---|---|
| id | BIGINT UNSIGNED PK | |
| lab_order_id | BIGINT UNSIGNED FK → lab_orders.id | |
| lab_test_id | BIGINT UNSIGNED FK → lab_tests.id | |
| status | ENUM('pending','completed') DEFAULT 'pending' | |
| price | DECIMAL(10,2) | Snapshot |
| created_at | TIMESTAMP | |
| updated_at | TIMESTAMP | |

---

### lab_results

| Column | Type | Notes |
|---|---|---|
| id | BIGINT UNSIGNED PK | |
| lab_order_item_id | BIGINT UNSIGNED FK → lab_order_items.id | |
| parameter_id | BIGINT UNSIGNED FK → lab_test_parameters.id | |
| result_value | VARCHAR(255) NULLABLE | |
| is_abnormal | TINYINT(1) DEFAULT 0 | |
| notes | TEXT NULLABLE | |
| entered_by | BIGINT UNSIGNED FK NULLABLE | |
| entered_at | TIMESTAMP NULLABLE | |
| created_at | TIMESTAMP | |
| updated_at | TIMESTAMP | |

---

### wards

| Column | Type | Notes |
|---|---|---|
| id | BIGINT UNSIGNED PK | |
| name | VARCHAR(100) | e.g. General, ICU, Private |
| type | ENUM('general','semi_private','private','icu','emergency') | |
| charge_per_day | DECIMAL(10,2) DEFAULT 0 | |
| is_active | TINYINT(1) DEFAULT 1 | |
| created_at | TIMESTAMP | |
| updated_at | TIMESTAMP | |

---

### beds

| Column | Type | Notes |
|---|---|---|
| id | BIGINT UNSIGNED PK | |
| ward_id | BIGINT UNSIGNED FK → wards.id | |
| bed_number | VARCHAR(20) | |
| status | ENUM('available','occupied','maintenance') DEFAULT 'available' | |
| created_at | TIMESTAMP | |
| updated_at | TIMESTAMP | |

---

### admissions

| Column | Type | Notes |
|---|---|---|
| id | BIGINT UNSIGNED PK | |
| admission_number | VARCHAR(30) UNIQUE | Auto generated |
| patient_id | BIGINT UNSIGNED FK → patients.id | |
| doctor_id | BIGINT UNSIGNED FK → doctors.id | |
| bed_id | BIGINT UNSIGNED FK → beds.id NULLABLE | |
| ward_id | BIGINT UNSIGNED FK → wards.id NULLABLE | |
| admission_date | DATETIME | |
| expected_discharge_date | DATE NULLABLE | |
| discharge_date | DATETIME NULLABLE | |
| admission_notes | TEXT NULLABLE | |
| status | ENUM('admitted','discharged') DEFAULT 'admitted' | |
| created_by | BIGINT UNSIGNED FK NULLABLE | |
| created_at | TIMESTAMP | |
| updated_at | TIMESTAMP | |

---

### ipd_notes

| Column | Type | Notes |
|---|---|---|
| id | BIGINT UNSIGNED PK | |
| admission_id | BIGINT UNSIGNED FK → admissions.id | |
| note_type | ENUM('doctor','nurse','procedure') | |
| note_date | DATE | |
| note_time | TIME NULLABLE | |
| content | TEXT | |
| created_by | BIGINT UNSIGNED FK NULLABLE | |
| created_at | TIMESTAMP | |
| updated_at | TIMESTAMP | |

---

### ipd_vitals

| Column | Type | Notes |
|---|---|---|
| id | BIGINT UNSIGNED PK | |
| admission_id | BIGINT UNSIGNED FK → admissions.id | |
| bp_systolic | SMALLINT NULLABLE | |
| bp_diastolic | SMALLINT NULLABLE | |
| pulse | SMALLINT NULLABLE | |
| temperature | DECIMAL(4,1) NULLABLE | |
| spo2 | TINYINT NULLABLE | |
| weight | DECIMAL(5,2) NULLABLE | |
| resp_rate | TINYINT NULLABLE | |
| recorded_at | TIMESTAMP | |
| recorded_by | BIGINT UNSIGNED FK NULLABLE | |

---

### ipd_medication_chart

| Column | Type | Notes |
|---|---|---|
| id | BIGINT UNSIGNED PK | |
| admission_id | BIGINT UNSIGNED FK → admissions.id | |
| medicine_id | BIGINT UNSIGNED FK → medicines.id | |
| medicine_name | VARCHAR(150) | |
| dosage | VARCHAR(50) NULLABLE | |
| frequency | VARCHAR(100) NULLABLE | |
| route | VARCHAR(50) NULLABLE | |
| start_date | DATE | |
| end_date | DATE NULLABLE | |
| is_active | TINYINT(1) DEFAULT 1 | |
| prescribed_by | BIGINT UNSIGNED FK NULLABLE | |
| created_at | TIMESTAMP | |
| updated_at | TIMESTAMP | |

---

### discharge_summaries

| Column | Type | Notes |
|---|---|---|
| id | BIGINT UNSIGNED PK | |
| admission_id | BIGINT UNSIGNED FK → admissions.id UNIQUE | |
| patient_id | BIGINT UNSIGNED FK → patients.id | |
| doctor_id | BIGINT UNSIGNED FK → doctors.id | |
| admission_diagnosis | TEXT NULLABLE | |
| final_diagnosis | TEXT NULLABLE | |
| treatment_summary | TEXT NULLABLE | |
| procedures_done | TEXT NULLABLE | |
| condition_at_discharge | VARCHAR(100) NULLABLE | Stable, Critical |
| advice | TEXT NULLABLE | |
| diet_advice | TEXT NULLABLE | |
| activity_advice | TEXT NULLABLE | |
| follow_up_date | DATE NULLABLE | |
| follow_up_notes | TEXT NULLABLE | |
| status | ENUM('draft','review','finalized') DEFAULT 'draft' | |
| finalized_at | TIMESTAMP NULLABLE | |
| finalized_by | BIGINT UNSIGNED FK NULLABLE | |
| created_by | BIGINT UNSIGNED FK NULLABLE | |
| created_at | TIMESTAMP | |
| updated_at | TIMESTAMP | |

---

### discharge_medications

| Column | Type | Notes |
|---|---|---|
| id | BIGINT UNSIGNED PK | |
| discharge_summary_id | BIGINT UNSIGNED FK → discharge_summaries.id | |
| medicine_name | VARCHAR(150) | |
| dosage | VARCHAR(50) NULLABLE | |
| frequency | VARCHAR(100) NULLABLE | |
| duration | VARCHAR(50) NULLABLE | |
| instructions | TEXT NULLABLE | |
| created_at | TIMESTAMP | |
| updated_at | TIMESTAMP | |

---

### invoices

| Column | Type | Notes |
|---|---|---|
| id | BIGINT UNSIGNED PK | |
| invoice_number | VARCHAR(30) UNIQUE | Auto generated |
| patient_id | BIGINT UNSIGNED FK → patients.id | |
| visit_id | BIGINT UNSIGNED FK NULLABLE | For OPD |
| admission_id | BIGINT UNSIGNED FK NULLABLE | For IPD |
| invoice_type | ENUM('opd','ipd','pharmacy','lab') | |
| invoice_date | DATE | |
| subtotal | DECIMAL(12,2) DEFAULT 0 | |
| discount | DECIMAL(12,2) DEFAULT 0 | |
| tax | DECIMAL(12,2) DEFAULT 0 | |
| total | DECIMAL(12,2) DEFAULT 0 | |
| paid_amount | DECIMAL(12,2) DEFAULT 0 | |
| balance | DECIMAL(12,2) DEFAULT 0 | |
| status | ENUM('draft','issued','paid','partial','cancelled') DEFAULT 'draft' | |
| notes | TEXT NULLABLE | |
| created_by | BIGINT UNSIGNED FK NULLABLE | |
| created_at | TIMESTAMP | |
| updated_at | TIMESTAMP | |

---

### invoice_items

| Column | Type | Notes |
|---|---|---|
| id | BIGINT UNSIGNED PK | |
| invoice_id | BIGINT UNSIGNED FK → invoices.id | |
| item_type | ENUM('service','medicine','lab_test','bed_charge','custom') | |
| item_id | BIGINT UNSIGNED NULLABLE | Reference to item |
| description | VARCHAR(255) | |
| quantity | DECIMAL(8,2) DEFAULT 1 | |
| unit_price | DECIMAL(10,2) DEFAULT 0 | |
| discount | DECIMAL(10,2) DEFAULT 0 | |
| total | DECIMAL(12,2) DEFAULT 0 | |
| created_at | TIMESTAMP | |
| updated_at | TIMESTAMP | |

---

### payments

| Column | Type | Notes |
|---|---|---|
| id | BIGINT UNSIGNED PK | |
| invoice_id | BIGINT UNSIGNED FK → invoices.id | |
| patient_id | BIGINT UNSIGNED FK → patients.id | |
| amount | DECIMAL(12,2) | |
| payment_method | ENUM('cash','card','upi','cheque','online') DEFAULT 'cash' | |
| reference_number | VARCHAR(100) NULLABLE | |
| payment_date | DATE | |
| notes | TEXT NULLABLE | |
| created_by | BIGINT UNSIGNED FK NULLABLE | |
| created_at | TIMESTAMP | |
| updated_at | TIMESTAMP | |

---

### inventory_items

| Column | Type | Notes |
|---|---|---|
| id | BIGINT UNSIGNED PK | |
| medicine_id | BIGINT UNSIGNED FK → medicines.id NULLABLE | |
| name | VARCHAR(150) | |
| category | ENUM('medicine','consumable','equipment') DEFAULT 'medicine' | |
| batch_number | VARCHAR(100) NULLABLE | |
| expiry_date | DATE NULLABLE | |
| quantity | DECIMAL(10,3) DEFAULT 0 | |
| unit | VARCHAR(30) NULLABLE | |
| purchase_price | DECIMAL(10,2) DEFAULT 0 | |
| selling_price | DECIMAL(10,2) DEFAULT 0 | |
| reorder_level | DECIMAL(10,3) DEFAULT 0 | |
| supplier_id | BIGINT UNSIGNED FK NULLABLE | |
| is_active | TINYINT(1) DEFAULT 1 | |
| created_at | TIMESTAMP | |
| updated_at | TIMESTAMP | |
| deleted_at | TIMESTAMP NULLABLE | |

---

### suppliers

| Column | Type | Notes |
|---|---|---|
| id | BIGINT UNSIGNED PK | |
| name | VARCHAR(150) | |
| contact_person | VARCHAR(150) NULLABLE | |
| phone | VARCHAR(20) NULLABLE | |
| email | VARCHAR(150) NULLABLE | |
| address | TEXT NULLABLE | |
| gstin | VARCHAR(20) NULLABLE | |
| is_active | TINYINT(1) DEFAULT 1 | |
| created_at | TIMESTAMP | |
| updated_at | TIMESTAMP | |

---

### purchase_orders

| Column | Type | Notes |
|---|---|---|
| id | BIGINT UNSIGNED PK | |
| po_number | VARCHAR(30) UNIQUE | |
| supplier_id | BIGINT UNSIGNED FK → suppliers.id | |
| order_date | DATE | |
| status | ENUM('draft','ordered','received','partial') DEFAULT 'draft' | |
| total_amount | DECIMAL(12,2) DEFAULT 0 | |
| notes | TEXT NULLABLE | |
| created_by | BIGINT UNSIGNED FK NULLABLE | |
| created_at | TIMESTAMP | |
| updated_at | TIMESTAMP | |

---

### purchase_order_items

| Column | Type | Notes |
|---|---|---|
| id | BIGINT UNSIGNED PK | |
| purchase_order_id | BIGINT UNSIGNED FK → purchase_orders.id | |
| inventory_item_id | BIGINT UNSIGNED FK → inventory_items.id | |
| quantity_ordered | DECIMAL(10,3) | |
| quantity_received | DECIMAL(10,3) DEFAULT 0 | |
| unit_price | DECIMAL(10,2) | |
| total | DECIMAL(12,2) | |
| created_at | TIMESTAMP | |
| updated_at | TIMESTAMP | |

---

### stock_transactions

| Column | Type | Notes |
|---|---|---|
| id | BIGINT UNSIGNED PK | |
| inventory_item_id | BIGINT UNSIGNED FK → inventory_items.id | |
| type | ENUM('in','out','adjustment') | |
| quantity | DECIMAL(10,3) | |
| reference_type | VARCHAR(50) NULLABLE | purchase_order, prescription, manual |
| reference_id | BIGINT UNSIGNED NULLABLE | |
| notes | TEXT NULLABLE | |
| created_by | BIGINT UNSIGNED FK NULLABLE | |
| created_at | TIMESTAMP | |
| updated_at | TIMESTAMP | |

---

### dispenses

| Column | Type | Notes |
|---|---|---|
| id | BIGINT UNSIGNED PK | |
| prescription_id | BIGINT UNSIGNED FK → prescriptions.id | |
| patient_id | BIGINT UNSIGNED FK → patients.id | |
| dispensed_by | BIGINT UNSIGNED FK NULLABLE | |
| dispensed_at | TIMESTAMP | |
| created_at | TIMESTAMP | |
| updated_at | TIMESTAMP | |

---

### dispense_items

| Column | Type | Notes |
|---|---|---|
| id | BIGINT UNSIGNED PK | |
| dispense_id | BIGINT UNSIGNED FK → dispenses.id | |
| inventory_item_id | BIGINT UNSIGNED FK → inventory_items.id | |
| prescription_item_id | BIGINT UNSIGNED FK → prescription_items.id | |
| quantity_dispensed | DECIMAL(8,3) | |
| unit_price | DECIMAL(10,2) | |
| total | DECIMAL(12,2) | |
| created_at | TIMESTAMP | |
| updated_at | TIMESTAMP | |

---

## Indexes Summary

| Table | Index Columns |
|---|---|
| patients | phone, uhid |
| appointments | patient_id, appointment_date, doctor_id |
| visits | patient_id, visit_date, doctor_id |
| case_sheets | visit_id, patient_id |
| prescriptions | visit_id, patient_id |
| lab_orders | patient_id, status |
| admissions | patient_id, status |
| invoices | patient_id, invoice_type, status |
| inventory_items | medicine_id, expiry_date |
