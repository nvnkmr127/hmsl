# 08 — API Structure

> **Goal:** Define clean, RESTful API endpoints that expose core HMS data, enabling future mobile app integration or third-party systems.

---

## API Design Principles

- All API routes are prefixed with `/api/v1/`
- All routes protected by `auth:sanctum`
- Consistent JSON response format:
  ```json
  {
    "success": true,
    "message": "...",
    "data": {...},
    "meta": { "page": 1, "total": 50 }
  }
  ```
- Error format:
  ```json
  {
    "success": false,
    "message": "Validation failed",
    "errors": { "phone": ["The phone field is required."] }
  }
  ```
- Use Laravel API Resources for all responses
- Version prefix: `/api/v1/` (allows future v2 without breaking changes)

---

## Authentication Endpoints

| Method | Endpoint | Description |
|---|---|---|
| POST | `/api/v1/auth/login` | Login, returns Sanctum token |
| POST | `/api/v1/auth/logout` | Revoke current token |
| GET | `/api/v1/auth/me` | Get authenticated user info |
| POST | `/api/v1/auth/refresh` | Refresh token |

---

## Patient Endpoints

| Method | Endpoint | Description |
|---|---|---|
| GET | `/api/v1/patients` | List patients (search, paginate) |
| POST | `/api/v1/patients` | Create new patient |
| GET | `/api/v1/patients/{id}` | Get patient details |
| PUT | `/api/v1/patients/{id}` | Update patient |
| DELETE | `/api/v1/patients/{id}` | Soft delete patient |
| GET | `/api/v1/patients/{id}/visits` | Get visit history |
| GET | `/api/v1/patients/{id}/invoices` | Get billing history |
| GET | `/api/v1/patients/{id}/admissions` | Get IPD history |

---

## Appointment Endpoints

| Method | Endpoint | Description |
|---|---|---|
| GET | `/api/v1/appointments` | List appointments |
| POST | `/api/v1/appointments` | Book appointment |
| GET | `/api/v1/appointments/{id}` | Get appointment |
| PUT | `/api/v1/appointments/{id}` | Update appointment |
| DELETE | `/api/v1/appointments/{id}` | Cancel appointment |
| POST | `/api/v1/appointments/walkin` | Create walk-in token |
| GET | `/api/v1/appointments/queue` | Get today's queue |
| PATCH | `/api/v1/appointments/{id}/status` | Update token status |

---

## OPD / Case Sheet Endpoints

| Method | Endpoint | Description |
|---|---|---|
| GET | `/api/v1/visits` | List visits |
| POST | `/api/v1/visits` | Create visit |
| GET | `/api/v1/visits/{id}` | Get visit details |
| GET | `/api/v1/visits/{id}/case-sheet` | Get case sheet for visit |
| PUT | `/api/v1/visits/{id}/case-sheet` | Update case sheet |
| POST | `/api/v1/visits/{id}/case-sheet/finalize` | Finalize case sheet |
| GET | `/api/v1/visits/{id}/vitals` | Get vitals for visit |
| POST | `/api/v1/visits/{id}/vitals` | Record vitals |

---

## Prescription Endpoints

| Method | Endpoint | Description |
|---|---|---|
| GET | `/api/v1/prescriptions/{id}` | Get prescription details |
| POST | `/api/v1/prescriptions` | Create prescription |
| POST | `/api/v1/prescriptions/{id}/items` | Add medicine to prescription |
| PUT | `/api/v1/prescriptions/{id}/items/{itemId}` | Update medicine item |
| DELETE | `/api/v1/prescriptions/{id}/items/{itemId}` | Remove medicine item |

---

## Lab Order Endpoints

| Method | Endpoint | Description |
|---|---|---|
| GET | `/api/v1/lab-orders` | List lab orders |
| POST | `/api/v1/lab-orders` | Create lab order |
| GET | `/api/v1/lab-orders/{id}` | Get lab order details |
| PATCH | `/api/v1/lab-orders/{id}/status` | Update order status |
| POST | `/api/v1/lab-orders/{id}/results` | Enter results |
| GET | `/api/v1/lab-orders/{id}/report` | Download lab report PDF |

---

## IPD / Admission Endpoints

| Method | Endpoint | Description |
|---|---|---|
| GET | `/api/v1/admissions` | List admissions |
| POST | `/api/v1/admissions` | Admit patient |
| GET | `/api/v1/admissions/{id}` | Get admission details |
| PATCH | `/api/v1/admissions/{id}/discharge` | Discharge patient |
| GET | `/api/v1/admissions/{id}/notes` | Get IPD notes |
| POST | `/api/v1/admissions/{id}/notes` | Add IPD note |
| GET | `/api/v1/admissions/{id}/vitals` | Get IPD vitals |
| POST | `/api/v1/admissions/{id}/vitals` | Record IPD vitals |
| GET | `/api/v1/admissions/{id}/medication-chart` | Get medication chart |
| POST | `/api/v1/admissions/{id}/medication-chart` | Add medication |
| PATCH | `/api/v1/admissions/{id}/medication-chart/{mId}/stop` | Stop medication |

---

## Discharge Summary Endpoints

| Method | Endpoint | Description |
|---|---|---|
| GET | `/api/v1/discharge-summaries/{admissionId}` | Get discharge summary |
| POST | `/api/v1/discharge-summaries` | Create draft |
| PUT | `/api/v1/discharge-summaries/{id}` | Update summary |
| POST | `/api/v1/discharge-summaries/{id}/review` | Submit for review |
| POST | `/api/v1/discharge-summaries/{id}/finalize` | Finalize summary |
| GET | `/api/v1/discharge-summaries/{id}/pdf` | Download PDF |

---

## Billing Endpoints

| Method | Endpoint | Description |
|---|---|---|
| GET | `/api/v1/invoices` | List invoices |
| POST | `/api/v1/invoices` | Create invoice |
| GET | `/api/v1/invoices/{id}` | Get invoice |
| POST | `/api/v1/invoices/{id}/payments` | Add payment |
| GET | `/api/v1/invoices/{id}/pdf` | Download invoice PDF |
| PATCH | `/api/v1/invoices/{id}/cancel` | Cancel invoice |

---

## Master Data Endpoints (Read-only for UI consumers)

| Method | Endpoint | Description |
|---|---|---|
| GET | `/api/v1/departments` | List departments |
| GET | `/api/v1/doctors` | List doctors |
| GET | `/api/v1/services` | List services |
| GET | `/api/v1/medicines` | Search medicines (typeahead) |
| GET | `/api/v1/lab-tests` | Search lab tests |
| GET | `/api/v1/wards` | List wards |
| GET | `/api/v1/beds` | List beds (with availability) |

---

## Reports Endpoints

| Method | Endpoint | Description |
|---|---|---|
| GET | `/api/v1/reports/patients` | Patient stats |
| GET | `/api/v1/reports/revenue` | Revenue stats |
| GET | `/api/v1/reports/pharmacy` | Pharmacy stats |
| GET | `/api/v1/reports/lab` | Lab stats |

---

## API Resources (Laravel)

All responses use dedicated Resource classes:

```
app/Http/Resources/
├── PatientResource.php
├── AppointmentResource.php
├── VisitResource.php
├── CaseSheetResource.php
├── VitalResource.php
├── PrescriptionResource.php
├── PrescriptionItemResource.php
├── LabOrderResource.php
├── LabResultResource.php
├── AdmissionResource.php
├── IpdNoteResource.php
├── DischargeSummaryResource.php
├── InvoiceResource.php
├── InvoiceItemResource.php
├── PaymentResource.php
├── MedicineResource.php
├── LabTestResource.php
├── WardResource.php
└── BedResource.php
```

---

## Sample Response — Patient

```json
{
  "success": true,
  "data": {
    "id": 1,
    "uhid": "HMS-00001",
    "name": "John Doe",
    "age": 35,
    "gender": "male",
    "blood_group": "O+",
    "phone": "9876543210",
    "email": "john@example.com",
    "address": "123 Main St",
    "city": "Bangalore",
    "created_at": "2026-03-17T09:00:00Z"
  }
}
```
