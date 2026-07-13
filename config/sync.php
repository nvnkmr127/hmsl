<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Sync Enabled
    |--------------------------------------------------------------------------
    |
    | Whether the synchronization is active and enabled for this device.
    |
    */
    'enabled' => env('SYNC_ENABLED', false),

    /*
    |--------------------------------------------------------------------------
    | Sync Server Configuration
    |--------------------------------------------------------------------------
    |
    | This value determines the URL of the central production web app that
    | this local installation will synchronize with.
    |
    */
    'server_url' => env('SYNC_SERVER_URL'),

    /*
    |--------------------------------------------------------------------------
    | Sync Security Token
    |--------------------------------------------------------------------------
    |
    | The Sanctum token used to authenticate requests to the sync server.
    |
    */
    'token' => env('SYNC_TOKEN'),

    /*
    |--------------------------------------------------------------------------
    | Device Identity
    |--------------------------------------------------------------------------
    |
    | A unique identifier for this installation.
    |
    */
    'device_id' => env('DEVICE_ID', 'default_device'),

    /*
    |--------------------------------------------------------------------------
    | Sync Schedule
    |--------------------------------------------------------------------------
    |
    | How often (in minutes) the background sync should run.
    |
    */
    'interval' => env('SYNC_INTERVAL', 1),

    /*
    |--------------------------------------------------------------------------
    | Syncable Tables
    |--------------------------------------------------------------------------
    |
    | The list of database tables that should be included in the sync process.
    |
    */
    'tables' => [
        'patients',
        'doctors',
        'appointments',
        'bills',
        'bill_items',
        'bill_payments',
        'bill_discounts',
        'medicines',
        'prescriptions',
        'prescription_items',
        'lab_orders',
        'lab_results',
        'admissions',
        'beds',
        'wards',
        'departments',
        'patient_vitals',
        'inventory_items',
        'services',
        'diagnoses',
        'consultations',
        'users',
    ],
];
