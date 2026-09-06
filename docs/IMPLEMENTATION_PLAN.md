# IMPLEMENTATION_PLAN.md
## ERP Lite Hybrid PJU — DISHUB
## Comprehensive System Implementation Plan

**Version**: 1.0 — CLASS 01
**Status**: APPROVED & IN EXECUTION
**Last Updated**: 2026-08-09

---

## 1. Executive Summary

Transformasi sistem Inventory Gudang sederhana menjadi **Production-Ready ERP Lite Hybrid PJU** untuk Dinas Perhubungan. Sistem mengintegrasikan lifecycle lengkap PJU dari pengadaan, gudang, distribusi, pemasangan, kerusakan, maintenance, garansi, hingga retur vendor dengan arsitektur Transaction Ledger dan Google Sheets Integration.

---

## 2. Module Breakdown & Development Plan

```
┌────────────────────────────────────────────────────────────────────────┐
│                        DEVELOPMENT ROADMAP                             │
├──────────────┬──────────────────────────────────────────┬──────────────┤
│ Class        │ Core Focus                               │ Status       │
├──────────────┼──────────────────────────────────────────┼──────────────┤
│ CLASS 01     │ Project Discovery & Foundation Setup     │ COMPLETED    │
│ CLASS 02     │ Transaction Ledger & Inventory Core      │ COMPLETED    │
│ CLASS 03     │ PJU Master Data & Dynamic Location       │ COMPLETED    │
│ CLASS 04     │ Distribusi & Pemasangan PJU              │ NEXT         │
│ CLASS 05     │ Kerusakan, Pencopotan & Maintenance      │ PENDING      │
│ CLASS 06     │ Garansi PJU & Retur Vendor               │ PENDING      │
│ CLASS 07     │ Reporting, PDF, Excel & Google Sheets    │ PENDING      │
│ CLASS 08     │ REST API v1, Security Audit & Prod Ready │ PENDING      │
└──────────────┴──────────────────────────────────────────┴──────────────┘
```

---

### Section 2.1 — Foundation (CLASS 01)
- Setup Laravel configuration, environment settings (`.env.example`), and core services.
- `ItemCodeGeneratorService` — concurrency-safe, configurable item code generation (Q1).
- `ApprovalWorkflowService` — multi-level configurable approval workflow engine (Q2).
- Core exception handling: `InsufficientStockException`, `ApprovalException`, `ItemCodeException`.

### Section 2.2 — Database (CLASS 01 - 03)
- Non-destructive migration strategy (no dropping of existing tables).
- Transaction Ledger table (`stock_ledgers`).
- Added non-breaking fields to `barang_masuks` and `barang_keluars` (`status`, `approved_by`, `approved_at`, `approval_notes`, `no_dokumen`).
- PJU Master tables: `kecamatans`, `kelurahans`, `lokasi_pjus` (GPS nullable), `pju_assets`.

### Section 2.3 — RBAC (Role-Based Access Control)
- Granular permissions using Spatie Permission:
  - `inventory.view`, `inventory.create`, `inventory.update`, `inventory.delete`, `inventory.approve`, `inventory.void`, `inventory.export`
  - `pju.view`, `pju.create`, `pju.update`, `pju.distribute`, `pju.install`
  - `maintenance.view`, `maintenance.create`, `maintenance.assign`, `maintenance.complete`
  - `vendor.view`, `vendor.retur.create`, `vendor.retur.approve`
  - `laporan.view`, `laporan.export`
  - `admin.user.manage`, `admin.role.manage`
- Role hierarchy: Super Admin, System Administrator, Admin Gudang, Warehouse Manager, Teknisi, Department Manager, Viewer.

### Section 2.4 — Master Data (CLASS 03)
- Master Barang, Jenis, Satuan, Supplier, Customer (existing + enhanced).
- Master Wilayah: Kecamatan & Kelurahan (ready for official Dishub seed).
- Master Lokasi PJU: Alamat, kelurahan, kecamatan, nomor tiang (nullable), latitude/longitude (nullable/optional), Google Maps URL helper.
- Master Aset PJU: Individual PJU item tracking with serial number, specs, warranty dates, and status.

### Section 2.5 — Inventory Core (CLASS 02)
- Pure Service Layer (`InventoryService`) for stock inbound (`recordInbound`) and outbound (`recordOutbound`).
- Atomic ledger calculation: `quantity_before`, `quantity_after`, `reference_type`, `reference_id`, `user_id`.
- Strict stock checks preventing negative stock.
- Counter-ledger entries for voided transactions (`voidBarangMasuk`, `voidBarangKeluar`).

### Section 2.6 — Maintenance & Work Order (CLASS 05)
- Module Laporan Kerusakan PJU (`kerusakan_pjus`).
- Module Work Order Maintenance (`maintenance_pjus`).
- Module Pencopotan PJU (`pencopotan_pjus`).
- Tracking spare part usage and technician assignment.

### Section 2.7 — Warranty (CLASS 06)
- Tracking garansi PJU per vendor (`garansi_pjus`).
- Expiration warnings for active warranties.

### Section 2.8 — Return (CLASS 06)
- Module Retur Vendor (`retur_vendors`).
- Tracking status barang retur (proses, selesai, ditolak) & barang kembali ke gudang.

### Section 2.9 — Reporting (CLASS 07)
- PDF Reports via DomPDF (Stok, Barang Masuk, Barang Keluar, Aset PJU, Maintenance).
- Excel Export via Maatwebsite Excel.
- Interactive Dashboard Charts (Chart.js).

### Section 2.10 — Audit (CLASS 01 - 08)
- Spatie Activity Log integrated into all models (`Barang`, `StockLedger`, `LokasiPju`, `PjuAsset`, etc.).
- Audit trail for all approval decisions, voiding operations, and configuration updates.

### Section 2.11 — Integration (CLASS 07)
- Google Sheets Integration Architecture via `GoogleSheetsService` & `SyncToGoogleSheetsJob`.
- Whitelisted dataset configuration (`config/integration.php`).

### Section 2.12 — Hybrid Synchronization (CLASS 07)
- Scheduled Cron sync (`SYNC_INTERVAL_MINUTES=60` default, configurable).
- Independent async queue execution — failure in Google API does not impact ERP transactions.
- Retry mechanism with exponential backoff (3 attempts).

### Section 2.13 — Data Migration (Staging & Cleaning)
- Excel Data Import Staging: `Excel → Staging Table → Normalization (Trim/Case) → Validation → DB`.
- Non-destructive handling of original Excel files.

### Section 2.14 — Testing (CLASS 01 & 08)
- Smoke Test suite (`tests/Feature/SmokeTest.php`).
- Unit & Feature tests for `InventoryService`, `ApprovalWorkflowService`, and `LocationService`.

### Section 2.15 — Deployment (CLASS 08)
- Nginx + PHP-FPM 8.3 + MySQL 8 configuration.
- Environment secret management.
- Production HTTPS & Security Headers setup.

---

## 3. Verification & Acceptance Criteria

- [x] Structure and documentation updated under `/docs/`
- [x] `config/erp.php` & `config/integration.php` properly structured
- [x] Dynamic location architecture implemented (GPS nullable, Google Maps URL fallback)
- [x] Transaction ledger active for inbound/outbound stock
- [x] Zero breaking changes to existing Blade views/layouts
- [ ] UAT & Client Confirmation post-interview tomorrow
