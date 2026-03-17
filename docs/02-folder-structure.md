# 02 — Module-wise Folder Structure

> **Goal:** Define the complete Laravel project folder layout with modular architecture.

---

## Laravel Project Root

```
hms/
├── app/
│   ├── Console/
│   ├── Exceptions/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   └── Api/
│   │   └── Middleware/
│   ├── Livewire/               ← All Livewire components
│   ├── Models/                 ← All Eloquent models
│   ├── Services/               ← All business logic (service layer)
│   ├── Repositories/           ← Optional: data access layer
│   └── Providers/
│
├── config/
│   ├── app.php
│   ├── hms.php                 ← Custom HMS configuration
│   └── ...
│
├── database/
│   ├── migrations/             ← All migration files
│   ├── seeders/                ← Role, permission, settings seeders
│   └── factories/              ← For testing
│
├── docs/                       ← All planning documents
│
├── resources/
│   ├── views/
│   │   ├── layouts/            ← Base layouts
│   │   ├── components/         ← Blade UI components
│   │   ├── auth/               ← Login, password pages
│   │   └── pages/              ← Module views
│   └── css/
│       └── app.css
│
├── routes/
│   ├── web.php                 ← Web routes
│   ├── api.php                 ← API routes
│   └── modules/                ← Per-module route includes
│       ├── settings.php
│       ├── master.php
│       ├── patients.php
│       ├── appointments.php
│       ├── opd.php
│       ├── ipd.php
│       ├── discharge.php
│       ├── pharmacy.php
│       ├── lab.php
│       ├── inventory.php
│       └── reports.php
│
├── tests/
│   ├── Feature/
│   └── Unit/
│
└── docker-compose.yml          ← Optional dev Docker setup
```

---

## Models

```
app/Models/
├── User.php
├── Patient.php
├── Doctor.php
├── Department.php
├── Service.php
├── Setting.php
├── Appointment.php
├── Visit.php
├── CaseSheet.php
├── Vital.php
├── Diagnosis.php
├── Medicine.php
├── Prescription.php
├── PrescriptionItem.php
├── LabTest.php
├── LabTestParameter.php
├── LabOrder.php
├── LabOrderItem.php
├── LabResult.php
├── Ward.php
├── Bed.php
├── Admission.php
├── IpdNote.php
├── IpdVital.php
├── IpdMedicationChart.php
├── DischargeSummary.php
├── DischargeMedication.php
├── Invoice.php
├── InvoiceItem.php
├── Payment.php
├── InventoryItem.php
├── Supplier.php
├── PurchaseOrder.php
├── PurchaseOrderItem.php
├── StockTransaction.php
├── Dispense.php
└── DispenseItem.php
```

---

## Controllers

```
app/Http/Controllers/
├── DashboardController.php
│
├── Settings/
│   └── SettingsController.php
│
├── Master/
│   ├── DepartmentController.php
│   ├── DoctorController.php
│   ├── ServiceController.php
│   ├── MedicineController.php
│   ├── LabTestController.php
│   ├── WardController.php
│   └── BedController.php
│
├── Patient/
│   └── PatientController.php
│
├── Appointment/
│   └── AppointmentController.php
│
├── OPD/
│   ├── VisitController.php
│   ├── CaseSheetController.php
│   └── PrescriptionController.php
│
├── IPD/
│   ├── AdmissionController.php
│   ├── IpdNoteController.php
│   └── IpdMedicationController.php
│
├── Discharge/
│   └── DischargeSummaryController.php
│
├── Billing/
│   ├── InvoiceController.php
│   └── PaymentController.php
│
├── Pharmacy/
│   └── PharmacyController.php
│
├── Lab/
│   ├── LabOrderController.php
│   └── LabResultController.php
│
├── Inventory/
│   ├── InventoryController.php
│   └── PurchaseOrderController.php
│
├── Reports/
│   └── ReportController.php
│
└── Api/
    ├── PatientApiController.php
    ├── AppointmentApiController.php
    ├── CaseSheetApiController.php
    └── InvoiceApiController.php
```

---

## Services (Business Logic)

```
app/Services/
├── SettingsService.php
├── PatientService.php
├── AppointmentService.php
├── TokenService.php
├── VisitService.php
├── CaseSheetService.php
├── VitalsService.php
├── PrescriptionService.php
├── LabOrderService.php
├── LabResultService.php
├── AdmissionService.php
├── BedService.php
├── IpdNoteService.php
├── DischargeSummaryService.php
├── InvoiceService.php
├── PaymentService.php
├── PharmacyService.php
├── InventoryService.php
├── StockService.php
├── ReportService.php
└── PdfService.php
```

---

## Livewire Components

```
app/Livewire/
│
├── Dashboard/
│   ├── DoctorDashboard.php
│   ├── ReceptionDashboard.php
│   └── AccountantDashboard.php
│
├── Settings/
│   ├── HospitalSettings.php
│   ├── InvoiceSettings.php
│   └── PrintSettings.php
│
├── Master/
│   ├── DepartmentList.php
│   ├── DepartmentForm.php
│   ├── DoctorList.php
│   ├── DoctorForm.php
│   ├── ServiceList.php
│   ├── MedicineList.php
│   ├── MedicineForm.php
│   ├── LabTestList.php
│   ├── LabTestForm.php
│   ├── WardBedManager.php
│   └── ...
│
├── Patient/
│   ├── PatientSearch.php
│   ├── PatientRegistration.php
│   ├── PatientProfile.php
│   └── PatientVisitHistory.php
│
├── Appointment/
│   ├── TokenQueue.php
│   ├── AppointmentBooking.php
│   └── QueueDisplay.php
│
├── OPD/
│   ├── CaseSheetEditor.php
│   ├── VitalsEntry.php
│   ├── DiagnosisEntry.php
│   ├── PrescriptionEditor.php
│   ├── LabOrderEntry.php
│   └── OpdBilling.php
│
├── IPD/
│   ├── AdmissionForm.php
│   ├── BedSelector.php
│   ├── IpdNoteEntry.php
│   ├── IpdVitals.php
│   ├── MedicationChart.php
│   ├── IpdLabOrder.php
│   └── IpdBilling.php
│
├── Discharge/
│   ├── DischargeSummaryForm.php
│   └── DischargePrint.php
│
├── Billing/
│   ├── InvoiceList.php
│   ├── InvoiceForm.php
│   ├── PaymentEntry.php
│   └── InvoicePrint.php
│
├── Pharmacy/
│   ├── PrescriptionDispense.php
│   ├── PharmacySales.php
│   └── PharmacyBilling.php
│
├── Lab/
│   ├── LabOrderList.php
│   ├── SampleCollection.php
│   ├── ResultEntry.php
│   └── LabReport.php
│
├── Inventory/
│   ├── InventoryList.php
│   ├── StockAdjustment.php
│   ├── PurchaseOrderForm.php
│   └── ExpiryAlert.php
│
└── Reports/
    ├── PatientReport.php
    ├── RevenueReport.php
    └── PharmacyReport.php
```

---

## Blade Views

```
resources/views/
│
├── layouts/
│   ├── app.blade.php           ← Main authenticated layout
│   ├── auth.blade.php          ← Auth pages layout
│   └── print.blade.php         ← Print only layout (no nav)
│
├── components/
│   ├── sidebar.blade.php
│   ├── topbar.blade.php
│   ├── breadcrumb.blade.php
│   ├── alert.blade.php
│   ├── modal.blade.php
│   ├── card.blade.php
│   ├── table/
│   │   ├── wrapper.blade.php
│   │   ├── th.blade.php
│   │   └── empty.blade.php
│   └── form/
│       ├── input.blade.php
│       ├── select.blade.php
│       ├── textarea.blade.php
│       ├── checkbox.blade.php
│       └── label.blade.php
│
├── auth/
│   ├── login.blade.php
│   └── passwords/
│
└── pages/
    ├── dashboard.blade.php
    ├── settings/
    ├── master/
    ├── patients/
    ├── appointments/
    ├── opd/
    ├── ipd/
    ├── discharge/
    ├── billing/
    ├── pharmacy/
    ├── lab/
    ├── inventory/
    └── reports/
```

---

## Routes Structure

```
routes/
├── web.php                     ← Imports all module route files
├── api.php                     ← API routes (sanctum protected)
└── modules/
    ├── settings.php            ← /settings/*
    ├── master.php              ← /master/*
    ├── patients.php            ← /patients/*
    ├── appointments.php        ← /appointments/*
    ├── opd.php                 ← /opd/*
    ├── ipd.php                 ← /ipd/*
    ├── discharge.php           ← /discharge/*
    ├── billing.php             ← /billing/*
    ├── pharmacy.php            ← /pharmacy/*
    ├── lab.php                 ← /lab/*
    ├── inventory.php           ← /inventory/*
    └── reports.php             ← /reports/*
```

---

## Database Migrations Order

```
database/migrations/
001 - create_settings_table
002 - create_departments_table
003 - create_doctors_table
004 - create_services_table
005 - create_patients_table
006 - create_appointments_table
007 - create_visits_table
008 - create_case_sheets_table
009 - create_vitals_table
010 - create_diagnoses_table
011 - create_medicines_table
012 - create_prescriptions_table
013 - create_prescription_items_table
014 - create_lab_tests_table
015 - create_lab_test_parameters_table
016 - create_lab_orders_table
017 - create_lab_order_items_table
018 - create_lab_results_table
019 - create_wards_table
020 - create_beds_table
021 - create_admissions_table
022 - create_ipd_notes_table
023 - create_ipd_vitals_table
024 - create_ipd_medication_charts_table
025 - create_discharge_summaries_table
026 - create_discharge_medications_table
027 - create_invoices_table
028 - create_invoice_items_table
029 - create_payments_table
030 - create_suppliers_table
031 - create_inventory_items_table
032 - create_purchase_orders_table
033 - create_purchase_order_items_table
034 - create_stock_transactions_table
035 - create_dispenses_table
036 - create_dispense_items_table
```

---

## Seeders

```
database/seeders/
├── DatabaseSeeder.php          ← Calls all seeders
├── RolePermissionSeeder.php    ← Roles, permissions
├── SettingsSeeder.php          ← Default hospital settings
├── DepartmentSeeder.php        ← Default departments
└── AdminUserSeeder.php         ← Default doctor owner account
```
