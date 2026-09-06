# REQUIREMENT TRACEABILITY MATRIX (RTM)
## ERP Lite Hybrid Inventori & Maintenance PJU — DISHUB

**Version**: 2.0  
**Date**: 2026-09-06  
**Status**: 100% VERIFIED & COVERED  

---

## 1. Mapping Traceability Matriks

| Req ID | Deskripsi Kebutuhan | Fitur / Route | Controller | Service Layer | Model | Permission / Role | Test Automated | Status |
|---|---|---|---|---|---|---|---|:---:|
| **FR-001-01** | Pengelolaan Data Master Barang | `GET/POST /barang` | `BarangController` | `ItemCodeGeneratorService` | `Barang` | `inventory.create` / `admin gudang` | `MasterDataTest`, `RolePermissionMatrixTest` | **PASS** |
| **FR-001-02** | Kode Unik Configurable Generator | `POST /barang` | `BarangController` | `ItemCodeGeneratorService` | `Barang` | `inventory.create` | `MasterDataTest` | **PASS** |
| **FR-001-04** | Master Spesifikasi Watt & Merk | `GET/POST /watt`, `/merk` | `WattController`, `MerkController` | Eloquent ORM | `Watt`, `Merk` | `inventory.create` | `MasterDataTest` | **PASS** |
| **FR-001-06** | Alert Batas Minimum Stok | `GET /barang`, `/dashboard` | `DashboardController` | `DashboardService` | `Barang` | `inventory.view` | `CriticalInventoryTest` | **PASS** |
| **FR-002-01** | Pencatatan Otomatis Buku Besar (Ledger) | Transaction Triggered | `InventoryService` | `InventoryService` | `InventoryLedger` | `inventory.create` | `InventoryLedgerTest`, `CriticalInventoryTest` | **PASS** |
| **FR-002-04** | Larangan Keras Saldo Negatif | `POST /barang-keluar` | `BarangKeluarController` | `InventoryService` | `Barang` | `inventory.create` | `InventoryLedgerTest` (`InsufficientStockException`) | **PASS** |
| **FR-003-01** | Transaksi Barang Masuk (Inbound) | `POST /barang-masuk` | `BarangMasukController` | `InventoryService` | `BarangMasuk` | `inventory.create` / `admin gudang` | `CriticalInventoryTest`, `RolePermissionMatrixTest` | **PASS** |
| **FR-003-05** | Void / Pembatalan Barang Masuk | `DELETE /barang-masuk/{id}` | `BarangMasukController` | `InventoryService` | `BarangMasuk` | `inventory.void` / `kepala gudang` | `CriticalInventoryTest` | **PASS** |
| **FR-004-01** | Transaksi Barang Keluar (Outbound) | `POST /barang-keluar` | `BarangKeluarController` | `InventoryService` | `BarangKeluar` | `inventory.create` / `admin gudang` | `CriticalInventoryTest` | **PASS** |
| **FR-004-03** | Void / Pembatalan Barang Keluar | `DELETE /barang-keluar/{id}` | `BarangKeluarController` | `InventoryService` | `BarangKeluar` | `inventory.void` / `kepala gudang` | `CriticalInventoryTest` | **PASS** |
| **FR-005-01** | Perekaman Aset Individual PJU | `GET/POST /pju-asset` | `PjuAssetController` | `PjuLifecycleService` | `PjuAsset` | `pju.create` | `PjuLifecycleTest` | **PASS** |
| **FR-006-01** | Normalisasi Master Lokasi & Wilayah | `GET/POST /lokasi-pju` | `LokasiPjuController` | Eloquent ORM | `LokasiPju` | `pju.view` / `pju.create` | `RolePermissionMatrixTest` | **PASS** |
| **FR-007-03** | Perekaman Pemasangan (Pasang) di Tiang | `POST /pemasangan-pju` | `PemasanganPjuController` | `PjuLifecycleService` | `PemasanganPju` | `pju.install` / `teknisi` | `PjuLifecycleTest`, `RolePermissionMatrixTest` | **PASS** |
| **FR-008-01** | Perekaman Pencopotan (Copot) Aset | `POST /pencopotan-pju` | `PencopotanPjuController` | `PjuLifecycleService` | `PencopotanPju` | `pju.install` / `teknisi` | `PjuLifecycleTest` | **PASS** |
| **FR-008-02** | Work Order Perbaikan & Maintenance | `POST /maintenance-pju` | `MaintenancePjuController` | `PjuLifecycleService` | `MaintenancePju` | `maintenance.create` / `teknisi` | `PjuLifecycleTest` | **PASS** |
| **FR-008-04** | Penyelesaian WO & Sparepart | `POST /maintenance-pju/{id}/complete`| `MaintenancePjuController` | `PjuLifecycleService` | `MaintenancePju` | `maintenance.complete` | `PjuLifecycleTest` | **PASS** |
| **FR-009-01** | Monitoring Status Garansi Otomatis | `GET /garansi-pju` | `GaransiPjuController` | `PjuLifecycleService` | `GaransiPju` | `vendor.view` | `PjuLifecycleTest` | **PASS** |
| **FR-009-03** | Perekaman Retur Vendor | `POST /retur-vendor` | `ReturVendorController` | `PjuLifecycleService` | `ReturVendor` | `vendor.retur.create` | `PjuLifecycleTest` | **PASS** |
| **FR-009-04** | Penyelesaian Retur & Pemulihan Gudang | `POST /retur-vendor/{id}/complete` | `ReturVendorController` | `PjuLifecycleService` | `ReturVendor` | `vendor.retur.approve` / `kepala gudang` | `PjuLifecycleTest`, `RolePermissionMatrixTest` | **PASS** |
| **FR-010-01** | Laporan Stok PDF & Excel | `GET /laporan-stok` | `LaporanStokController` | `ReportingService` | `Barang` | `laporan.view` | `ReportingAndAuditTest` | **PASS** |
| **FR-010-05** | Sinkronisasi Hybrid Google Sheets | Service Execution | GoogleSheetsSyncService | `GoogleSheetsSyncService` | Sync Queue | `admin` | `SyncAndIntegrationTest` | **PASS** |
| **FR-011-01** | Role-Based Access Control Granular | Route & Gate | `CheckRole`, `CheckPermission` | Gate Provider | `Role`, `User` | All Roles | `RbacTest`, `RolePermissionMatrixTest` | **PASS** |
| **FR-011-03** | Audit Logging Perubahan Data & Status | Event Observers | Spatie ActivityLog | Database Logger | `Activity` | Immutable | `ReportingAndAuditTest` | **PASS** |
| **FR-011-04** | REST API v1 Rate Limiting | `/api/v1/barang` | `BarangApiController` | `RateLimitMiddleware` | `Barang` | RateLimit: 60/min | `SecurityAndApiTest` | **PASS** |
| **NFR-003** | Perlindungan File Upload Eksploitasi | `POST /barang` | `BarangController` | Storage Facade | Disk Public | Validator: Mimes | `SecurityAndApiTest` | **PASS** |
