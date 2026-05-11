<?php

return [
    'jobs' => [
        'report-summary' => [
            'enabled' => true,
            'label' => 'Daily Summary Report',
            'description' => 'Generates daily activity metrics and dispatches system.daily.summary via webhooks.',
            'cron' => '0 8 * * *',
            'command' => 'hms:report-summary',
            'args' => [],
            'dependencies' => [
                'database',
                'queue_worker',
                'webhooks_configured',
            ],
        ],
        'prune-webhooks' => [
            'enabled' => true,
            'label' => 'Prune Webhook Logs',
            'description' => 'Deletes old webhook delivery logs, inbound webhook logs, and dispatched outbox records.',
            'cron' => '0 0 * * *',
            'command' => 'hms:prune-webhooks',
            'args' => [
                '--days' => 7,
            ],
            'dependencies' => [
                'database',
            ],
        ],
        'retry-outbox' => [
            'enabled' => true,
            'label' => 'Retry Stuck Webhook Outbox',
            'description' => 'Retries webhook outbox entries stuck in pending/processing by re-queueing delivery jobs.',
            'cron' => '*/30 * * * *',
            'command' => 'hms:retry-outbox',
            'args' => [
                '--minutes' => 15,
            ],
            'dependencies' => [
                'database',
                'queue_worker',
            ],
        ],
        'queue-worker' => [
            'enabled' => true,
            'label' => 'Queue Worker (Short-lived)',
            'description' => 'Processes queued jobs without a long-running daemon (cPanel-friendly).',
            'cron' => '* * * * *',
            'command' => 'queue:work',
            'args' => [
                '--stop-when-empty' => true,
                '--tries' => 1,
                '--max-time' => 50,
            ],
            'dependencies' => [
                'database',
            ],
        ],
    ],
];
