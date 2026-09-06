<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Integration & External Services Configuration
    |--------------------------------------------------------------------------
    |
    | Q3 — Google Sheets & Integration Channel
    | Status: PERIODIC / SCHEDULED CONFIRMED
    | Sync Interval: Configurable via .env (Default: 60 minutes)
    |
    */

    'google_sheets' => [
        'enabled' => env('GOOGLE_SHEETS_ENABLED', false),

        // Interval sinkronisasi dalam menit (NEEDS_CLIENT_CONFIRMATION — default 60 menit)
        'sync_interval_minutes' => (int) env('SYNC_INTERVAL_MINUTES', 60),

        // Spreadsheet ID
        'spreadsheet_id' => env('GOOGLE_SHEETS_SPREADSHEET_ID', ''),

        // File JSON credentials Google API
        'credentials_path' => env('GOOGLE_SHEETS_CREDENTIALS_PATH', storage_path('app/google-credentials.json')),

        // Queue name untuk sync job
        'queue' => env('GOOGLE_SHEETS_QUEUE', 'sync'),

        // Max retry attempts untuk job
        'max_retries' => 3,

        // Backoff interval per retry (detik)
        'retry_backoff' => [60, 300, 900],

        // Whitelist dataset yang diizinkan disinkronkan ke Google Sheets (NEEDS_CLIENT_CONFIRMATION)
        'whitelisted_datasets' => [
            'stok_ringkasan' => [
                'table' => 'barangs',
                'columns' => ['kode_barang', 'nama_barang', 'stok', 'stok_minimum'],
            ],
            'laporan_barang_masuk' => [
                'table' => 'barang_masuks',
                'columns' => ['tanggal_masuk', 'kode_transaksi', 'barang_id', 'jumlah_masuk', 'supplier_id'],
            ],
            'laporan_barang_keluar' => [
                'table' => 'barang_keluars',
                'columns' => ['tanggal_keluar', 'kode_transaksi', 'barang_id', 'jumlah_keluar', 'customer_id'],
            ],
            'pju_status' => [
                'table' => 'pju_assets',
                'columns' => ['kode_pju', 'nama_pju', 'status_aset', 'lokasi_id'],
            ]
        ],
    ],

];
