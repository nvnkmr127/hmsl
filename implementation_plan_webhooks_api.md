# Implementation Plan - Webhook & API System

This plan outlines the creation of a premium, state-of-the-art API and Webhook system for the Hospital Management System (HMS).

## Phase 1: API Core & Authentication

- [ ] **API Authentication**: Use Laravel Sanctum.
- [ ] **Global API Response**: Standardize JSON responses.
- [ ] **API Resources**: Create Resources for `Patient`, `Doctor`, `Appointment`, and `Service`.
- [ ] **API Controllers**:
    - `PatientApiController`
    - `AppointmentApiController`
    - `DoctorApiController`
- [ ] **Routing**: Implement `routes/api.php` with versioning (`v1`).

## Phase 2: Inbound Webhook System

- [ ] **Storage**: Create `inbound_webhooks` table to log payload, source, and status.
- [ ] **Security**: Implement HMAC signature verification.
- [ ] **Endpoint**: `POST /api/v1/webhooks/{provider}`.
- [ ] **Processing**: Use Laravel Queues to process webhooks asynchronously.

## Phase 3: Outbound Webhook System

- [ ] **Configuration**: Create `webhook_endpoints` table (URL, Events, Secret).
- [ ] **Logs**: Create `outbound_webhook_deliveries` table (Response status, duration, attempts).
- [ ] **Dispatcher**: Service class to send payloads.
- [ ] **Automation**: Trigger webhooks on model events (e.g., `PatientCreated`, `AppointmentScheduled`).

## Phase 4: UI / Management

- [ ] **Settings Integration**: Manage Webhook Endpoints from the Admin Dashboard.
- [ ] **Delivery Logs**: View outbound attempts and status in the UI.
