# 11 — Webhooks System

> **Goal:** Build a configurable, reliable outbound webhook system that notifies external services (CRMs, billing software, notification platforms) on key HMS events in real time.

---

## What Are Webhooks Here?

The HMS system fires **outbound webhooks** when key events happen (patient registered, appointment booked, invoice paid, discharge finalized, etc.). Recipients can be any URL — WhatsApp API, SMS gateway, CRM, accounting software, or a custom automation.

All webhooks are:
- **Queued** (via Redis queue) — never block the main request
- **Signed** (HMAC-SHA256) — so receivers can verify authenticity
- **Retried** (exponential backoff) — up to 5 retries on failure
- **Logged** — every delivery attempt recorded with status and response

---

## Database Tables

### webhook_endpoints

| Column | Type | Notes |
|---|---|---|
| id | BIGINT UNSIGNED PK | |
| name | VARCHAR(150) | e.g. "WhatsApp Notifications" |
| url | VARCHAR(500) | Target URL |
| secret | VARCHAR(255) | HMAC signing secret |
| events | JSON | Array of event names to subscribe to |
| is_active | TINYINT(1) DEFAULT 1 | |
| timeout_seconds | TINYINT DEFAULT 10 | |
| created_by | BIGINT UNSIGNED FK NULLABLE | |
| created_at | TIMESTAMP | |
| updated_at | TIMESTAMP | |

---

### webhook_logs

| Column | Type | Notes |
|---|---|---|
| id | BIGINT UNSIGNED PK | |
| webhook_endpoint_id | BIGINT UNSIGNED FK | |
| event_name | VARCHAR(100) | e.g. `patient.registered` |
| payload | JSON | Sent payload |
| response_status | SMALLINT NULLABLE | HTTP status code |
| response_body | TEXT NULLABLE | |
| attempt_number | TINYINT DEFAULT 1 | |
| status | ENUM('pending','success','failed','retrying') | |
| error_message | TEXT NULLABLE | |
| delivered_at | TIMESTAMP NULLABLE | |
| created_at | TIMESTAMP | |
| updated_at | TIMESTAMP | |

---

## Webhook Events Catalog

### Patient Events
| Event Name | Trigger |
|---|---|
| `patient.registered` | New patient created |
| `patient.updated` | Patient demographics updated |

### Appointment Events
| Event Name | Trigger |
|---|---|
| `appointment.booked` | Appointment created |
| `appointment.cancelled` | Appointment cancelled |
| `appointment.token_called` | Token status → in_progress |
| `appointment.completed` | Token status → completed |

### OPD Events
| Event Name | Trigger |
|---|---|
| `visit.created` | New OPD visit created |
| `visit.case_sheet_finalized` | Case sheet finalized by doctor |
| `prescription.created` | Prescription saved for a visit |

### IPD Events
| Event Name | Trigger |
|---|---|
| `admission.created` | Patient admitted |
| `admission.discharged` | Patient discharged |
| `ipd.note_added` | Doctor/nurse note added |

### Lab Events
| Event Name | Trigger |
|---|---|
| `lab_order.created` | Lab order placed |
| `lab_order.sample_collected` | Sample marked collected |
| `lab_order.results_ready` | All results entered |

### Billing Events
| Event Name | Trigger |
|---|---|
| `invoice.created` | Invoice generated |
| `invoice.paid` | Invoice fully paid |
| `invoice.partial_payment` | Partial payment recorded |
| `invoice.cancelled` | Invoice cancelled |

### Pharmacy Events
| Event Name | Trigger |
|---|---|
| `prescription.dispensed` | Prescription dispensed from pharmacy |

### Discharge Events
| Event Name | Trigger |
|---|---|
| `discharge.finalized` | Discharge summary finalized |

### System Events
| Event Name | Trigger |
|---|---|
| `daily.summary` | Automated daily summary (scheduled) |
| `low_stock.alert` | Inventory item below reorder level |
| `expiry.alert` | Medicine/item expiring within 30 days |

---

## Webhook Payload Format

All payloads follow this standard envelope:

```json
{
  "event": "patient.registered",
  "timestamp": "2026-03-17T09:30:00+05:30",
  "hospital": "City Health Clinic",
  "data": {
    "patient": {
      "id": 1,
      "uhid": "HMS-00001",
      "name": "John Doe",
      "phone": "9876543210",
      "gender": "male",
      "age": 35
    }
  }
}
```

### Signing Header

Every request includes:
```
X-HMS-Signature: sha256=<HMAC-SHA256 of payload using endpoint secret>
X-HMS-Event: patient.registered
X-HMS-Timestamp: 1710658200
```

---

## Architecture

```
HMS Event (e.g. patient saved)
        ↓
Laravel Event fired (e.g. PatientRegistered)
        ↓
Event Listener: WebhookDispatcher
        ↓
Builds payload → finds subscribed endpoints
        ↓
Dispatches: SendWebhookJob (queued via Redis)
        ↓
Job runs → HTTP POST to endpoint URL
        ↓
Log result → success or schedule retry
        ↓
On retry: exponential backoff (1min, 5min, 15min, 1hr, 4hr)
```

---

## Micro Tasks

### MT-110.1 — Database Setup
- [ ] Create migration: `create_webhook_endpoints_table`
- [ ] Create migration: `create_webhook_logs_table`
- [ ] Create `app/Models/WebhookEndpoint.php`
- [ ] Create `app/Models/WebhookLog.php`

---

### MT-110.2 — Webhook Service
- [ ] Create `app/Services/WebhookService.php`
- [ ] Methods:
  - `dispatch(string $event, array $data)` — find subscribed endpoints, queue jobs
  - `buildPayload(string $event, array $data)` — wrap in standard envelope
  - `sign(string $payload, string $secret)` — HMAC-SHA256
  - `logDelivery(WebhookEndpoint $endpoint, array $payload, $response)`
  - `getSubscribedEndpoints(string $event)`

---

### MT-110.3 — Webhook Queue Job
- [ ] Create `app/Jobs/SendWebhookJob.php`
- [ ] Accept: `WebhookEndpoint $endpoint`, `array $payload`, `int $attemptNumber`
- [ ] Use Laravel HTTP client with timeout
- [ ] On HTTP 2xx: log as success
- [ ] On failure: log as failed, reschedule with backoff if `attempt_number < 5`
- [ ] Dead letter: after 5 failures, mark as `failed`, alert admin

---

### MT-110.4 — Laravel Events & Listeners
- [ ] Create Laravel Events for each webhook trigger:
  - `Events/Patients/PatientRegistered.php`
  - `Events/Appointments/AppointmentBooked.php`
  - `Events/OPD/CaseSheetFinalized.php`
  - `Events/OPD/PrescriptionCreated.php`
  - `Events/IPD/PatientAdmitted.php`
  - `Events/IPD/PatientDischarged.php`
  - `Events/Lab/LabResultsReady.php`
  - `Events/Billing/InvoicePaid.php`
  - `Events/Pharmacy/PrescriptionDispensed.php`
  - `Events/Discharge/DischargeSummaryFinalized.php`
- [ ] Create `Listeners/WebhookDispatcher.php` — single listener for all events, routes to `WebhookService::dispatch()`
- [ ] Register in `EventServiceProvider`

---

### MT-110.5 — Fire Events from Services
- [ ] In `PatientService::create()` → fire `PatientRegistered`
- [ ] In `AppointmentService::book()` → fire `AppointmentBooked`
- [ ] In `CaseSheetService::finalize()` → fire `CaseSheetFinalized`
- [ ] In `AdmissionService::admit()` → fire `PatientAdmitted`
- [ ] In `AdmissionService::discharge()` → fire `PatientDischarged`
- [ ] In `LabResultService::markComplete()` → fire `LabResultsReady`
- [ ] In `InvoiceService::markPaid()` → fire `InvoicePaid`
- [ ] In `PharmacyService::dispense()` → fire `PrescriptionDispensed`
- [ ] In `DischargeSummaryService::finalize()` → fire `DischargeSummaryFinalized`

---

### MT-110.6 — Webhook Management UI
- [ ] Create `app/Livewire/Settings/WebhookEndpoints.php`
- [ ] List all configured endpoints with status
- [ ] Create/Edit form: Name, URL, Secret, Events (multi-select checkboxes)
- [ ] Test button: send test ping to endpoint
- [ ] Delete endpoint
- [ ] Route: `/settings/webhooks`

---

### MT-110.7 — Webhook Logs UI
- [ ] Create `app/Livewire/Settings/WebhookLogs.php`
- [ ] Filter: by endpoint, by event, by status, by date
- [ ] Show: event, status badge, attempt number, delivered at
- [ ] Expand row: show payload JSON and response
- [ ] "Retry" button: manually re-queue failed deliveries
- [ ] Route: `/settings/webhooks/logs`

---

### MT-110.8 — API Endpoints for Webhooks
- [ ] `GET /api/v1/webhooks` — list endpoints
- [ ] `POST /api/v1/webhooks` — create endpoint
- [ ] `PUT /api/v1/webhooks/{id}` — update endpoint
- [ ] `DELETE /api/v1/webhooks/{id}` — delete endpoint
- [ ] `GET /api/v1/webhooks/logs` — fetch delivery logs
- [ ] `POST /api/v1/webhooks/{id}/test` — send test event

---

## Estimated Hours

| Task | Est. Hours |
|---|---|
| DB setup | 2h |
| Webhook Service | 3h |
| Queue Job with retry | 3h |
| Laravel Events + Listeners | 4h |
| Fire events from services | 3h |
| Management UI | 4h |
| Logs UI | 3h |
| API endpoints | 2h |
| **Total** | **~24h** |
