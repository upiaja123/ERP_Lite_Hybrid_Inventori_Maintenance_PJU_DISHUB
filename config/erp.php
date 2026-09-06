<?php

return [

    /*
    |--------------------------------------------------------------------------
    | ERP Lite Hybrid PJU — DISHUB
    | Core Configuration
    |--------------------------------------------------------------------------
    |
    | Semua konfigurasi yang configurable berdasarkan keputusan Q1-Q4.
    | JANGAN hard-code nilai-nilai ini di business logic.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Q1 — Item Code Generator
    | Status: NEEDS_CLIENT_CONFIRMATION untuk format final
    |--------------------------------------------------------------------------
    | Format sementara: PRPTY-XXXXX (development placeholder)
    | Format dapat berubah setelah konfirmasi client.
    |
    | Drivers tersedia:
    |   'sequential' — PRPTY-00001, PRPTY-00002, dst.
    |   'yearly'     — PRPTY-2026-00001, dst.
    |   'custom'     — menggunakan format dari 'custom_format'
    */

    'item_code' => [

        // Driver untuk generate kode
        'driver' => env('ITEM_CODE_DRIVER', 'sequential'),

        // Prefix kode (NEEDS_CLIENT_CONFIRMATION — sementara PRPTY)
        'prefix' => env('ITEM_CODE_PREFIX', 'PRPTY'),

        // Separator antara prefix dan sequence
        'separator' => env('ITEM_CODE_SEPARATOR', '-'),

        // Panjang padding sequence (5 = 00001)
        'padding' => env('ITEM_CODE_PADDING', 5),

        // Format: PREFIX-SEQ atau PREFIX-YEAR-SEQ
        'format' => env('ITEM_CODE_FORMAT', 'PREFIX-SEQ'),

        // Kode per entity type (override prefix per tipe jika diperlukan)
        'entities' => [
            'barang'      => env('ITEM_CODE_PREFIX_BARANG', null),      // null = gunakan prefix global
            'barang_masuk'=> env('ITEM_CODE_PREFIX_BM', 'BM'),
            'barang_keluar'=> env('ITEM_CODE_PREFIX_BK', 'BK'),
            'pju_asset'   => env('ITEM_CODE_PREFIX_PJU', 'PJU'),
            'distribusi'  => env('ITEM_CODE_PREFIX_DIST', 'DIST'),
            'maintenance' => env('ITEM_CODE_PREFIX_MNT', 'MNT'),
            'retur'       => env('ITEM_CODE_PREFIX_RTR', 'RTR'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Q2 — Approval Workflow
    | Status: NEEDS_CLIENT_CONFIRMATION untuk level & approver final
    |--------------------------------------------------------------------------
    | Konfigurasi level approval per entity.
    | 'required' = apakah approval diperlukan
    | 'levels'   = berapa level approval (1, 2, dst.)
    */

    'approval' => [

        'barang_masuk' => [
            'required' => env('APPROVAL_BARANG_MASUK', false),
            'levels'   => env('APPROVAL_BARANG_MASUK_LEVELS', 1),
            // Permission yang dibutuhkan untuk approve per level
            // Level 1 approver permission, Level 2 dst.
            'permissions' => [
                1 => env('APPROVAL_BARANG_MASUK_L1_PERM', 'inventory.approve'),
                2 => env('APPROVAL_BARANG_MASUK_L2_PERM', 'inventory.approve.final'),
            ],
        ],

        'barang_keluar' => [
            'required' => env('APPROVAL_BARANG_KELUAR', false),
            'levels'   => env('APPROVAL_BARANG_KELUAR_LEVELS', 1),
            'permissions' => [
                1 => env('APPROVAL_BARANG_KELUAR_L1_PERM', 'inventory.approve'),
                2 => env('APPROVAL_BARANG_KELUAR_L2_PERM', 'inventory.approve.final'),
            ],
        ],

        'distribusi_pju' => [
            'required' => env('APPROVAL_DISTRIBUSI', false),
            'levels'   => env('APPROVAL_DISTRIBUSI_LEVELS', 1),
            'permissions' => [
                1 => env('APPROVAL_DISTRIBUSI_L1_PERM', 'pju.distribute.approve'),
            ],
        ],

        'retur_vendor' => [
            'required' => env('APPROVAL_RETUR', false),
            'levels'   => env('APPROVAL_RETUR_LEVELS', 1),
            'permissions' => [
                1 => env('APPROVAL_RETUR_L1_PERM', 'vendor.retur.approve'),
            ],
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Transaction Status
    | Status lifecycle semua transaksi
    |--------------------------------------------------------------------------
    */

    'transaction_statuses' => [
        'DRAFT'            => 'Draft',
        'SUBMITTED'        => 'Submitted',
        'PENDING_APPROVAL' => 'Menunggu Approval',
        'APPROVED'         => 'Disetujui',
        'REJECTED'         => 'Ditolak',
        'POSTED'           => 'Posted',
        'VOID'             => 'Void',
    ],

    /*
    |--------------------------------------------------------------------------
    | PJU Asset Status
    |--------------------------------------------------------------------------
    */

    'pju_statuses' => [
        'GUDANG'      => 'Di Gudang',
        'TERPASANG'   => 'Terpasang',
        'RUSAK'       => 'Rusak',
        'MAINTENANCE' => 'Maintenance',
        'RETUR'       => 'Retur Vendor',
        'NONAKTIF'    => 'Nonaktif',
    ],

    /*
    |--------------------------------------------------------------------------
    | Stock Ledger Transaction Types
    |--------------------------------------------------------------------------
    */

    'ledger_types' => [
        'IN'          => 'Barang Masuk',
        'OUT'         => 'Barang Keluar',
        'ADJUSTMENT'  => 'Penyesuaian Stok',
        'RETURN'      => 'Retur ke Gudang',
        'VOID_IN'     => 'Void Barang Masuk',
        'VOID_OUT'    => 'Void Barang Keluar',
        'OPENING'     => 'Saldo Awal',
    ],

    /*
    |--------------------------------------------------------------------------
    | Stok Configuration
    |--------------------------------------------------------------------------
    */

    'stock' => [
        // Apakah stok boleh negatif (JANGAN diubah ke true kecuali ada alasan kuat)
        'allow_negative' => env('STOCK_ALLOW_NEGATIVE', false),

        // Warning threshold (persen dari stok minimum)
        'warning_threshold' => env('STOCK_WARNING_THRESHOLD', 1.5),
    ],

];
