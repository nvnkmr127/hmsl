# Clinical Flows Verification Spec (IPD/OPD/Appointments/Billing/Receipts)

## Why
Core hospital workflows (outpatient tokens, appointments, inpatient admission/discharge, billing, receipts) must work end-to-end without hidden permission issues, missing screens, or broken print/receipt outputs.

## What Changes
- Define “fully working” acceptance for:
  - OPD token + appointment workflow
  - Doctor appointment/queue workflow
  - IPD admission + discharge workflow
  - Discharge summary
  - Billing + receipts
- Add a repeatable verification checklist for manual QA and automated smoke/flow tests.
- Add/adjust route permissions so the right roles can access these flows and the wrong roles cannot.
- Ensure receipts/print views exist and load for permitted roles without 500 errors.

## Impact
- Affected specs: OPD token system, appointments, inpatient admission, discharge, discharge summary, billing, receipts, permissions/navigation.
- Affected code: routes (counter/doctor/billing/discharge), Livewire flows, print views, services (OpdManager, BillingService, IpdManager), tests.

## ADDED Requirements
### Requirement: OPD Token System (Reception)
The system SHALL allow reception staff to generate an OPD token (Consultation) for a selected patient and doctor, with payment method and fee recorded.

#### Scenario: Generate token and print slip
- **WHEN** receptionist selects a patient and doctor and submits the booking
- **THEN** a Consultation is created with a token number for the day
- **AND** the user can open the token slip print view

#### Scenario: Cancel token
- **WHEN** receptionist cancels a token
- **THEN** the Consultation status becomes Cancelled
- **AND** cancelled tokens are visually marked and excluded from doctor “Pending” queue

### Requirement: Appointments (Doctor)
The system SHALL let doctors view appointments for today and manage their queue.

#### Scenario: Doctor sees queue and completes consultation
- **WHEN** doctor opens the consultation desk
- **THEN** pending consultations for that doctor are visible in token order
- **WHEN** doctor marks a consultation Completed
- **THEN** the consultation is removed from the active queue and appears in recent completed

### Requirement: Inpatient Admission (IPD)
The system SHALL allow admitting a patient to a bed and mark that bed as occupied.

#### Scenario: Admit patient
- **WHEN** staff completes the admission form selecting an available bed
- **THEN** an Admission is created with status Admitted
- **AND** the bed is marked unavailable

### Requirement: Discharge (IPD)
The system SHALL allow discharging an admitted patient and free the bed.

#### Scenario: Discharge patient
- **WHEN** staff discharges an admitted patient (with optional notes)
- **THEN** Admission status becomes Discharged and discharge_date is set
- **AND** the bed becomes available

### Requirement: Discharge Summary
The system SHALL provide a discharge summary artifact (screen and print output) for discharged patients.

#### Scenario: View discharge summary
- **WHEN** staff opens a discharged admission record
- **THEN** discharge summary data (notes/instructions/metadata) is visible

#### Scenario: Print discharge summary
- **WHEN** staff clicks print/download for discharge summary
- **THEN** a print view loads successfully and contains patient/admission identifiers

### Requirement: Billing and Receipts
The system SHALL support billing generation and provide a printable receipt for bills.

#### Scenario: Generate bill and open receipt
- **WHEN** staff generates a bill for a patient/consultation with items, totals, and payment method
- **THEN** a Bill exists with bill_number and items
- **AND** the receipt/print view loads for permitted roles

## MODIFIED Requirements
### Requirement: Permissions Match UI and Routes
All routes/screens involved in IPD, OPD token/appointments, discharge, billing, discharge summary, and receipts SHALL be protected by consistent permissions that match role expectations:
- Receptionist: patients, opd, billing (no doctor desk, no discharge unless explicitly granted)
- Nurse: ipd pages (and discharge only if granted)
- Doctor: doctor desk/appointments (and patient records as configured)
- Accountant: billing + reports

## REMOVED Requirements
None.
