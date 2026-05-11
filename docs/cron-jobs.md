## Cron Job Management (cPanel)

This project centralizes cron job definitions in `config/cron.php` and runs them in cPanel using a single wrapper command:

`php artisan hms:cron-run <job-key>`

The wrapper:
- Writes per-job logs under `storage/logs/cron/`
- Records each run in the `cron_job_runs` table
- Triggers the `system.cron.failed` webhook event when a job fails

---

### Inventory (Current Jobs)

| Job Key | Purpose | Schedule (cron) | Underlying Command | Dependencies |
|---|---|---|---|---|
| `report-summary` | Daily activity metrics + dispatch `system.daily.summary` webhook | `0 8 * * *` | `hms:report-summary` | DB, queue worker, webhook endpoints configured |
| `prune-webhooks` | Delete old webhook delivery logs, inbound logs, and dispatched outbox rows | `0 0 * * *` | `hms:prune-webhooks --days=7` | DB |
| `retry-outbox` | Retry stuck webhook outbox entries by re-queueing delivery jobs | `*/30 * * * *` | `hms:retry-outbox --minutes=15` | DB, queue worker |
| `queue-worker` | Process queued jobs without a long-running daemon (cPanel-friendly) | `* * * * *` | `queue:work --stop-when-empty --tries=1 --max-time=50` | DB |

---

### Additional Automation (Found, Not Scheduled)

These exist in the codebase but are intended for manual runs unless you decide to schedule them:

- `hms:backfill-patient-vitals` (one-time data backfill)
- `webhooks:retry-outbound` (manual retry tool)
- `webhooks:replay-inbound` (manual replay tool)
- `webhooks:prune` (manual prune tool)
- `webhook:daily-summary` (manual daily summary trigger)

No project-level scripts were found for database dumps, filesystem backups, logrotate, or periodic cache clearing. Backups should be configured at the hosting layer (cPanel backup, snapshots, or database backup tools).

---

### cPanel Cron Entries (Recommended)

In cPanel → Cron Jobs, create one entry per job using the schedule from `config/cron.php`.

Use this command template (adjust paths):

`<cron> cd /home/<cpanel-user>/<project-root> && <php-bin> artisan hms:cron-run <job-key> >/dev/null 2>&1`

Example (generic):
- `0 8 * * * cd /home/<cpanel-user>/hms && php artisan hms:cron-run report-summary >/dev/null 2>&1`
- `0 0 * * * cd /home/<cpanel-user>/hms && php artisan hms:cron-run prune-webhooks >/dev/null 2>&1`
- `*/30 * * * * cd /home/<cpanel-user>/hms && php artisan hms:cron-run retry-outbox >/dev/null 2>&1`
- `* * * * * cd /home/<cpanel-user>/hms && php artisan hms:cron-run queue-worker >/dev/null 2>&1`

Notes:
- If cPanel does not use the expected PHP version, set `<php-bin>` to the absolute path (examples: `/usr/local/bin/php`, `/opt/cpanel/ea-php82/root/usr/bin/php`).
- `>/dev/null 2>&1` prevents cPanel from emailing cron output. Job logs are still written to `storage/logs/cron/`.
- Do not configure `schedule:run` in cPanel at the same time as these individual cron entries, otherwise jobs will run twice.

---

### Logging

Per-job logs:
- `storage/logs/cron/report-summary.log`
- `storage/logs/cron/prune-webhooks.log`
- `storage/logs/cron/retry-outbox.log`
- `storage/logs/cron/queue-worker.log`

Run history table:
- `cron_job_runs` (job_key, status, exit_code, timestamps, output_path)

---

### Failure Alerting

When a configured cron job fails, the system dispatches:
- Webhook event: `system.cron.failed`
- Payload includes: `job_key`, `run_id`, `exit_code`, `error`, timestamps, and `output_path`

To receive alerts, create a webhook endpoint subscribed to `system.cron.failed`.

---

### Testing Checklist

After deploying to the server:
- Run `php artisan migrate`
- Run `php artisan hms:cron-list`
- Trigger one job manually: `php artisan hms:cron-run report-summary`
- Confirm:
  - Log file appended in `storage/logs/cron/`
  - A `cron_job_runs` row exists with `status=succeeded`
  - Webhook queue jobs are processed (ensure `queue-worker` cron is enabled)
