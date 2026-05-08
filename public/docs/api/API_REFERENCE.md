# HMS API Reference (v1)

Welcome to the Hospital Management System (HMS) API. This API is designed for third-party developers to build applications and integrations with our hospital platform.

## Authentication

The API uses **Laravel Sanctum** for authentication. To access protected endpoints, you must obtain a Bearer token.

### Login
`POST /api/v1/login`

**Request Body:**
```json
{
    "email": "user@example.com",
    "password": "your-password",
    "device_name": "my-mobile-app"
}
```

**Response:**
```json
{
    "token": "1|abc123xyz...",
    "user": {
        "id": 1,
        "name": "John Doe",
        "email": "user@example.com"
    }
}
```

Include this token in the `Authorization` header for subsequent requests:
`Authorization: Bearer 1|abc123xyz...`

---

## Core Endpoints

### Patients
Manage patient records.

- `GET /api/v1/patients`: List patients (supports `search` query param).
- `POST /api/v1/patients`: Create a new patient.
- `GET /api/v1/patients/{id}`: View patient details.
- `PUT /api/v1/patients/{id}`: Update patient details.

### Doctors
Access doctor information.

- `GET /api/v1/doctors`: List active doctors.
- `GET /api/v1/doctors/{id}`: View doctor profile and department.

### Appointments (Consultations)
Manage OPD bookings.

- `GET /api/v1/appointments`: List appointments (filters: `patient_id`, `doctor_id`, `status`, `date`).
- `POST /api/v1/appointments`: Book a new appointment.
- `GET /api/v1/appointments/{id}`: View appointment details.
- `DELETE /api/v1/appointments/{id}`: Cancel an appointment.

### Billing
Retrieve financial records.

- `GET /api/v1/bills`: List bills.
- `GET /api/v1/bills/{id}`: Detailed bill view with items and payment history.

### Medical Records
- `GET /api/v1/prescriptions`: List prescriptions.
- `GET /api/v1/prescriptions/{id}`: View specific prescription and medicine list.
- `GET /api/v1/lab-results`: List laboratory test results (filters: `patient_id`, `status`).
- `GET /api/v1/lab-results/{id}`: View detailed lab report and values.
- `GET /api/v1/vitals`: List patient vitals (weight, BP, SpO2, etc.).

### Inpatient Management (IPD)
- `GET /api/v1/admissions`: List patient admissions/hospitalizations.
- `GET /api/v1/admissions/{id}`: View admission details, bed info, and clinical notes.
- `GET /api/v1/wards`: Check ward availability and daily charges.

### Pharmacy & Inventory
- `GET /api/v1/medicines`: Search for medicines, check pricing and stock levels.

### Master Data
- `GET /api/v1/services`: List available services and pricing.
- `GET /api/v1/departments`: List hospital departments.

---

## Webhooks

The HMS supports both incoming and outgoing webhooks for real-time integrations.

### Outgoing Webhooks (Event Subscriptions)
The HMS can notify your application when specific events occur. You can manage these subscriptions via the API.

- `GET /api/v1/webhook-endpoints`: List your registered webhook URLs.
- `POST /api/v1/webhook-endpoints`: Register a new webhook URL.
- `PUT /api/v1/webhook-endpoints/{id}`: Update settings.
- `DELETE /api/v1/webhook-endpoints/{id}`: Unsubscribe.

#### Supported Events
- `patient.created`: Fired when a new patient is registered.
- `appointment.booked`: Fired when a new consultation is scheduled.
- `bill.paid`: Fired when a payment is successfully processed.

#### Security
All outgoing webhooks include an `X-Webhook-Signature` header. This is an HMAC SHA256 signature of the raw request body, using the `secret` provided during registration.

### Incoming Webhooks
If you are an external provider (e.g., an external Lab), you can send data to HMS via our inbound webhook system.

- `POST /api/v1/webhooks/{source}`: Send a payload to the specified source.

The authentication requirements for incoming webhooks are configured per source (Open, Bearer Token, or HMAC Secret).

---

## Response Formats

All responses are returned as JSON. Successful responses typically contain a `data` key.

**Example Error Response (422 Unprocessable Entity):**
```json
{
    "errors": {
        "phone": ["The phone has already been taken."]
    }
}
```
