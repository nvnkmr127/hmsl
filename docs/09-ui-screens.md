# 09 — UI Screen List

> **Goal:** Define every screen in the system with its module, purpose, and responsible Livewire component.

---

## Auth Screens

| Screen | Route | View File |
|---|---|---|
| Login | `/login` | `auth/login.blade.php` |
| Forgot Password | `/forgot-password` | `auth/forgot-password.blade.php` |
| Reset Password | `/reset-password` | `auth/reset-password.blade.php` |

---

## Dashboard Screens

| Screen | Route | Livewire Component |
|---|---|---|
| Doctor Dashboard | `/dashboard` | `Dashboard/DoctorDashboard` |
| Reception Dashboard | `/dashboard` | `Dashboard/ReceptionDashboard` |
| Accountant Dashboard | `/dashboard` | `Dashboard/AccountantDashboard` |

> Dashboard is role-aware — same URL, different component rendered based on role.

---

## Settings Screens

| Screen | Route | Livewire Component |
|---|---|---|
| Hospital Details | `/settings/hospital` | `Settings/HospitalSettings` |
| System Preferences | `/settings/system` | `Settings/SystemPreferences` |
| Invoice Settings | `/settings/invoice` | `Settings/InvoiceSettings` |
| Print Settings | `/settings/print` | `Settings/InvoiceSettings` (sub-section) |

---

## Master Data Screens

| Screen | Route | Livewire Component |
|---|---|---|
| Department List | `/master/departments` | `Master/DepartmentList` |
| Doctor List | `/master/doctors` | `Master/DoctorList` |
| Services List | `/master/services` | `Master/ServiceList` |
| Medicine List | `/master/medicines` | `Master/MedicineList` |
| Lab Tests List | `/master/lab-tests` | `Master/LabTestList` |
| Lab Test Detail (params) | `/master/lab-tests/{id}` | `Master/LabTestForm` |
| Ward & Bed Manager | `/master/wards` | `Master/WardBedManager` |
| User Management | `/master/users` | `Master/UserList` |

---

## Patient Screens

| Screen | Route | Livewire Component |
|---|---|---|
| Patient List / Search | `/patients` | `Patient/PatientSearch` |
| Patient Registration | `/patients/create` | `Patient/PatientRegistration` |
| Patient Profile | `/patients/{id}` | `Patient/PatientProfile` |
| Edit Patient | `/patients/{id}/edit` | `Patient/PatientRegistration` (edit mode) |

---

## OPD — Appointment & Token Screens

| Screen | Route | Livewire Component |
|---|---|---|
| Walk-in Token | `/opd/token` | `Appointment/WalkinToken` |
| Appointment Booking | `/opd/appointments/book` | `Appointment/AppointmentBooking` |
| Patient Queue | `/opd/queue` | `Appointment/TokenQueue` |

---

## OPD — Consultation Screens

| Screen | Route | Livewire Component |
|---|---|---|
| Case Sheet Editor | `/opd/visits/{id}/case-sheet` | `OPD/CaseSheetEditor` |
| — Tab: Vitals | (embedded) | `OPD/VitalsEntry` |
| — Tab: Complaints & History | (embedded) | Part of `CaseSheetEditor` |
| — Tab: Examination | (embedded) | Part of `CaseSheetEditor` |
| — Tab: Diagnosis | (embedded) | `OPD/DiagnosisEntry` |
| — Tab: Lab Orders | (embedded) | `OPD/LabOrderEntry` |
| — Tab: Prescription | (embedded) | `OPD/PrescriptionEditor` |
| — Tab: Advice & Follow Up | (embedded) | Part of `CaseSheetEditor` |

---

## OPD — Billing Screens

| Screen | Route | Livewire Component |
|---|---|---|
| OPD Billing | `/opd/visits/{id}/billing` | `OPD/OpdBilling` |
| Invoice Print | `/billing/invoices/{id}/print` | Print view (no Livewire) |

---

## IPD Screens

| Screen | Route | Livewire Component |
|---|---|---|
| Admission Form | `/ipd/admit` | `IPD/AdmissionForm` |
| Active IPD List | `/ipd` | `IPD/ActiveAdmissionList` |
| IPD Patient Chart | `/ipd/{admissionId}` | Shell page + multiple sub-components |
| — Tab: Overview | (embedded) | Patient + Admission info |
| — Tab: Doctor Notes | (embedded) | `IPD/IpdNoteEntry` (type=doctor) |
| — Tab: Nursing Notes | (embedded) | `IPD/IpdNoteEntry` (type=nurse) |
| — Tab: Vitals | (embedded) | `IPD/IpdVitals` |
| — Tab: Medication Chart | (embedded) | `IPD/MedicationChart` |
| — Tab: Lab Orders | (embedded) | `IPD/IpdLabOrder` |
| — Tab: Billing | (embedded) | `IPD/IpdBilling` |

---

## Discharge Screens

| Screen | Route | Livewire Component |
|---|---|---|
| Discharge Form | `/ipd/{admissionId}/discharge` | `Discharge/DischargeSummaryForm` |
| Discharge Print | `/discharge/{id}/print` | Print view (no Livewire) |
| Discharge PDF | `/discharge/{id}/pdf` | Controller → PDF download |

---

## Billing Screens

| Screen | Route | Livewire Component |
|---|---|---|
| Invoice List | `/billing/invoices` | `Billing/InvoiceList` |
| Create Invoice | `/billing/invoices/create` | `Billing/InvoiceForm` |
| Invoice Detail | `/billing/invoices/{id}` | `Billing/InvoiceForm` (view) |
| Payment Entry | (modal on invoice detail) | `Billing/PaymentEntry` |
| Invoice Print | `/billing/invoices/{id}/print` | Print view |

---

## Pharmacy Screens

| Screen | Route | Livewire Component |
|---|---|---|
| Pharmacy Dashboard | `/pharmacy` | `Pharmacy/PharmacyDashboard` |
| Prescription Dispense | `/pharmacy/dispense` | `Pharmacy/PrescriptionDispense` |
| Pharmacy Billing | `/pharmacy/bill/{prescriptionId}` | `Pharmacy/PharmacyBilling` |

---

## Laboratory Screens

| Screen | Route | Livewire Component |
|---|---|---|
| Lab Dashboard | `/lab` | `Lab/LabDashboard` |
| Lab Order List | `/lab/orders` | `Lab/LabOrderList` |
| Sample Collection | `/lab/orders/{id}/collect` | `Lab/SampleCollection` |
| Result Entry | `/lab/orders/{id}/results` | `Lab/ResultEntry` |
| Lab Report Print | `/lab/orders/{id}/print` | Print view |
| Lab Report PDF | `/lab/orders/{id}/pdf` | Controller → PDF download |

---

## Inventory Screens

| Screen | Route | Livewire Component |
|---|---|---|
| Inventory List | `/inventory` | `Inventory/InventoryList` |
| Stock Adjustment | `/inventory/adjust` | `Inventory/StockAdjustment` |
| Purchase Orders | `/inventory/purchase-orders` | `Inventory/PurchaseOrderForm` |
| Supplier List | `/inventory/suppliers` | (simple CRUD) |

---

## Reports Screens

| Screen | Route | Livewire Component |
|---|---|---|
| Patient Report | `/reports/patients` | `Reports/PatientReport` |
| Revenue Report | `/reports/revenue` | `Reports/RevenueReport` |
| Pharmacy Report | `/reports/pharmacy` | `Reports/PharmacyReport` |
| Lab Report Summary | `/reports/lab` | `Reports/LabReportSummary` |

---

## Print-only Views (No Nav)

| View | File |
|---|---|
| Prescription | `resources/views/print/prescription.blade.php` |
| Invoice | `resources/views/print/invoice.blade.php` |
| Discharge Summary | `resources/views/print/discharge-summary.blade.php` |
| Lab Report | `resources/views/print/lab-report.blade.php` |
| Token Slip | `resources/views/print/token-slip.blade.php` |

---

## Total Screen Count

| Module | Screen Count |
|---|---|
| Auth | 3 |
| Dashboards | 3 |
| Settings | 4 |
| Master Data | 8 |
| Patient | 4 |
| OPD (Token/Queue) | 3 |
| OPD (Case Sheet) | 8 (tabbed as 1 page) |
| OPD Billing | 2 |
| IPD | 10 (tabbed as 1 page) |
| Discharge | 3 |
| Billing | 5 |
| Pharmacy | 3 |
| Lab | 6 |
| Inventory | 4 |
| Reports | 4 |
| Print Views | 5 |
| **Total** | **~75 screens/views** |
