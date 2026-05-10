<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Webhook Event Catalog
    |--------------------------------------------------------------------------
    |
    | List of all events that can be dispatched via webhooks.
    | Each event includes a label, a group, and a stable name.
    |
    */

    'events' => [
        'patient.registered' => [
            'label' => 'Patient Registered',
            'group' => 'Patient',
            'desc'  => 'Triggered when a new patient record is created.',
        ],
        'patient.updated' => [
            'label' => 'Patient Updated',
            'group' => 'Patient',
            'desc'  => 'Triggered when patient demographics are modified.',
        ],
        'patient.deleted' => [
            'label' => 'Patient Deleted',
            'group' => 'Patient',
            'desc'  => 'Triggered when a patient record is soft-deleted.',
        ],
        'appointment.booked' => [
            'label' => 'Appointment Booked',
            'group' => 'OPD',
            'desc'  => 'Triggered when a new OPD appointment is confirmed.',
        ],
        'consultation.created' => [
            'label' => 'Consultation Created',
            'group' => 'OPD',
            'desc'  => 'Triggered when a doctor starts a new consultation.',
        ],
        'bill.paid' => [
            'label' => 'Bill Paid',
            'group' => 'Billing',
            'desc'  => 'Triggered when a payment is received for a bill.',
        ],
        'lab.order.completed' => [
            'label' => 'Lab Order Completed',
            'group' => 'Laboratory',
            'desc'  => 'Triggered when all results for a lab order are verified.',
        ],
        'pharmacy.prescription.created' => [
            'label' => 'Prescription Created',
            'group' => 'Pharmacy',
            'desc'  => 'Triggered when a doctor issues a new prescription.',
        ],
        'ipd.admission.created' => [
            'label' => 'Patient Admitted',
            'group' => 'IPD',
            'desc'  => 'Triggered when a patient is admitted to a ward.',
        ],
        'system.daily.summary' => [
            'label' => 'Daily Summary',
            'group' => 'System',
            'desc'  => 'Automated daily activity report dispatch.',
        ],
    ],

    'api_version' => '1.0.0',

    'hospital' => [
        'name' => env('HOSPITAL_NAME', 'HMS Hospital'),
        'id'   => env('HOSPITAL_ID', 'hms-001'),
    ],
];
