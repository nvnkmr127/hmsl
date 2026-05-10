# 11 — Webhooks & Integration Platform

> **Goal:** A professional, enterprise-style inbound and outbound webhook platform. Connect HMS to CRMs, billing systems, and external labs with full auditability, security, and resiliency.

---

## 1. Outbound Webhooks (HMS → External)

The HMS system fires **outbound webhooks** when key events happen. This allows external systems to react in real-time.

### Setup Guide (Outbound)
1. Navigate to **Settings > Webhooks** in the HMS Admin Panel.
2. Under the **Outgoing Webhooks** tab, click **Add Endpoint**.
3. Provide the target URL.
4. Select the events you want to subscribe to from the **Event Catalog**.
5. Save the endpoint. The system will display the **Webhook Secret**. Save this securely; it will be masked on subsequent views.

### Security & Delivery Logic
- **Signature Verification**: Every request includes `X-HMS-Signature` and `X-HMS-Timestamp` headers.
- **SSRF Protection**: Internal IP ranges, metadata servers (`169.254.169.254`), and loopback addresses are strictly blocked.
- **Circuit Breaker**: If an endpoint fails 15 consecutive times, it is automatically paused to prevent system strain.
- **Data Redaction**: Sensitive patient data (Aadhar, PAN, phone numbers) are masked before logs are saved.

### Retry Behavior
- **Transient Failures (5xx, Network Errors, Rate Limits - 429)**: The system implements an exponential backoff with jitter retry strategy.
- **Permanent Failures (4xx errors except 429)**: The system immediately marks the delivery as failed without retrying (e.g., 401 Unauthorized or 404 Not Found).
- **Manual Retry**: Administrators can manually retry deliveries via the UI or using the artisan command `php artisan webhooks:retry-outbound`.

### Event Catalog
Below are the stable events you can subscribe to:
- `patient.registered` - Fired when a new patient profile is created.
- `appointment.booked` - Fired when an OPD appointment is scheduled.
- `bill.paid` - Fired when an invoice is settled.
- `lab.order.completed` - Fired when lab results are finalized.
- `system.daily.summary` - Dispatched automatically at midnight with aggregate metrics.

---

## 2. Inbound Webhooks (External → HMS)

HMS provides a secure gateway to receive data from external providers (Stripe, GitHub, Shopify, Custom CRMs).

### Setup Guide (Inbound)
1. Navigate to **Settings > Webhooks**.
2. Switch to the **Incoming Sources** tab.
3. Click **Add Source**.
4. Define a **Slug** (this will be part of the URL) and choose the authentication type.
5. The system will generate a secret. Configure your external provider to use this secret for signing requests.
6. The endpoint URL will be: `https://your-hms-domain.com/api/v1/webhooks/{slug}`

### Inbound Features
- **Idempotency**: Automatic replay protection. The system prevents duplicate processing by tracking external IDs or payload hashes.
- **Correlation Tracking**: Every request is assigned a `correlation_id` for end-to-end tracing.
- **Manual Replay**: Administrators can clone and replay failed inbound webhooks (with a new correlation ID) from the UI or via `php artisan webhooks:replay-inbound`.

### Signature Verification (Security)
To ensure the payload is genuinely from HMS (for outbound) or from an authorized source (for inbound), a timestamped HMAC-SHA256 signature is used.

**Example cURL Request (Simulating an Inbound Call)**
```bash
# Calculate Signature beforehand using your secret and current timestamp
# echo -n "1715360000.{\"event\":\"test\"}" | openssl dgst -sha256 -hmac "your_secret"

curl -X POST https://hms.test/api/v1/webhooks/my-crm \
  -H "Content-Type: application/json" \
  -H "X-HMS-Timestamp: 1715360000" \
  -H "X-HMS-Signature: sha256=a1b2c3d4e5f6..." \
  -d '{"event":"test"}'
```

**Developer Verification Example (PHP)**
```php
$payload = file_get_contents('php://input');
$signature = $_SERVER['HTTP_X_HMS_SIGNATURE'];
$timestamp = $_SERVER['HTTP_X_HMS_TIMESTAMP'];

// Replay Attack Protection (5 minute tolerance)
if (abs(time() - (int)$timestamp) > 300) {
    http_response_code(401);
    die('Timestamp expired');
}

$expected = 'sha256=' . hash_hmac('sha256', $timestamp . '.' . $payload, $secret);

if (hash_equals($expected, $signature)) {
    // Authenticated
}
```

---

## 3. Operations & Maintenance Commands

HMS provides robust CLI tools for webhook lifecycle management.

```bash
# Retry all failed outbound webhooks from the last 2 days
php artisan webhooks:retry-outbound --days=2

# Replay failed inbound webhooks for a specific source
php artisan webhooks:replay-inbound --source=crm --failed-only

# Prune old logs, outbox entries, and inbound records older than 30 days
php artisan webhooks:prune --days=30

# Manually dispatch the Daily Summary webhook
php artisan webhook:daily-summary
```

---

## 4. Database Architecture

- **`webhook_endpoints` (Outbound)**: Configures where HMS sends data.
- **`webhook_sources` (Inbound)**: Configures who HMS receives data from.
- **`webhook_logs` (Outbound Logs)**: Complete audit trail of data sent OUT, including masked payloads and truncated responses.
- **`inbound_webhooks` (Inbound Logs)**: Audit trail of data received IN.
- **`webhook_outbox` (Persistence)**: Guarantees delivery by persisting events before background dispatch.
