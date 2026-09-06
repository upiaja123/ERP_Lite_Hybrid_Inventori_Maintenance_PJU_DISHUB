# ARCHITECTURE.md
## ERP Lite Hybrid PJU — DISHUB
## System Architecture Document

**Version**: 1.0
**Last Updated**: 2026-08-09

---

## 1. High-Level Architecture

```
┌─────────────────────────────────────────────────────────┐
│                    FRONTEND LAYER                        │
│     Laravel Blade + Stisla Bootstrap + jQuery/AJAX       │
└───────────────────────┬─────────────────────────────────┘
                        │ HTTP Request
┌───────────────────────▼─────────────────────────────────┐
│                   CONTROLLER LAYER                       │
│         HTTP Controllers (thin — delegate to service)    │
└───────────────────────┬─────────────────────────────────┘
                        │
┌───────────────────────▼─────────────────────────────────┐
│                   MIDDLEWARE LAYER                       │
│    Auth | RBAC | Rate Limiting | CSRF | Audit            │
└───────────────────────┬─────────────────────────────────┘
                        │
┌───────────────────────▼─────────────────────────────────┐
│                  SERVICE LAYER                           │
│  InventoryService | PjuLifecycleService                  │
│  ApprovalWorkflowService | ItemCodeGeneratorService      │
│  ReportingService | GoogleSheetsService                  │
└───────────────────────┬─────────────────────────────────┘
                        │
┌───────────────────────▼─────────────────────────────────┐
│                  REPOSITORY / MODEL LAYER                │
│  Eloquent Models + Relationships                         │
│  Form Request Validation | Policy Authorization          │
└───────────────────────┬─────────────────────────────────┘
                        │
┌───────────────────────▼─────────────────────────────────┐
│                   DATABASE LAYER                         │
│               MySQL — System of Record                   │
└─────────────────────────────────────────────────────────┘
                        │ (async)
┌───────────────────────▼─────────────────────────────────┐
│               INTEGRATION LAYER (Async)                  │
│         Queue Jobs | Google Sheets API | Retry           │
└─────────────────────────────────────────────────────────┘
```

---

## 2. Application Layer Structure

```
app/
├── Console/
│   └── Commands/
│       └── SyncGoogleSheetsCommand.php
├── Exceptions/
│   ├── Handler.php
│   ├── InsufficientStockException.php
│   ├── ApprovalException.php
│   └── ItemCodeException.php
├── Http/
│   ├── Controllers/
│   │   ├── Auth/               ← existing
│   │   ├── Api/
│   │   │   └── V1/             ← REST API (CLASS 08)
│   │   ├── BarangController.php
│   │   ├── BarangMasukController.php  ← refactor ke service
│   │   ├── BarangKeluarController.php ← refactor ke service
│   │   ├── PjuAssetController.php     ← CLASS 03
│   │   ├── LokasiPjuController.php    ← CLASS 03
│   │   ├── DistribusiController.php   ← CLASS 04
│   │   ├── PemasanganController.php   ← CLASS 04
│   │   ├── KosakanController.php      ← CLASS 05
│   │   ├── MaintenanceController.php  ← CLASS 05
│   │   ├── PencopotanController.php   ← CLASS 05
│   │   ├── GaransiController.php      ← CLASS 06
│   │   └── ReturVendorController.php  ← CLASS 06
│   ├── Middleware/
│   │   ├── CheckRole.php              ← existing (deprecated)
│   │   └── RateLimitMiddleware.php    ← CLASS 08
│   └── Requests/
│       ├── StoreBarangMasukRequest.php
│       ├── StoreBarangKeluarRequest.php
│       ├── StorePjuAssetRequest.php
│       └── ...
├── Models/
│   ├── User.php              ← extend dengan permissions
│   ├── Barang.php            ← existing
│   ├── BarangMasuk.php       ← existing + approval fields
│   ├── BarangKeluar.php      ← existing + approval fields
│   ├── StockLedger.php       ← NEW (CLASS 02)
│   ├── PjuAsset.php          ← NEW (CLASS 03)
│   ├── Kecamatan.php         ← NEW (CLASS 03)
│   ├── Kelurahan.php         ← NEW (CLASS 03)
│   ├── LokasiPju.php         ← NEW (CLASS 03)
│   ├── DistribusiPju.php     ← NEW (CLASS 04)
│   ├── PemasanganPju.php     ← NEW (CLASS 04)
│   ├── KosakanPju.php        ← NEW (CLASS 05)
│   ├── MaintenancePju.php    ← NEW (CLASS 05)
│   ├── PencopotanPju.php     ← NEW (CLASS 05)
│   ├── GaransiPju.php        ← NEW (CLASS 06)
│   ├── ReturVendor.php       ← NEW (CLASS 06)
│   └── SyncLog.php           ← NEW (CLASS 07)
├── Policies/
│   ├── BarangMasukPolicy.php
│   ├── BarangKeluarPolicy.php
│   ├── PjuAssetPolicy.php
│   └── ...
├── Services/
│   ├── InventoryService.php           ← NEW (CLASS 02)
│   ├── PjuLifecycleService.php        ← NEW (CLASS 03)
│   ├── ApprovalWorkflowService.php    ← NEW (CLASS 01)
│   ├── ItemCodeGeneratorService.php   ← NEW (CLASS 01)
│   ├── ReportingService.php           ← NEW (CLASS 07)
│   └── GoogleSheetsService.php        ← NEW (CLASS 07)
└── Jobs/
    └── SyncToGoogleSheetsJob.php      ← NEW (CLASS 07)
```

---

## 3. Database Architecture

```
Existing (dipertahankan):
- users
- roles
- barangs
- barang_masuks (+ approval columns)
- barang_keluars (+ approval columns)
- jenis (jenis barang)
- satuans
- suppliers
- customers
- activity_log (Spatie)

New (ditambahkan):
- permissions (Spatie Permission)
- model_has_permissions (Spatie)
- model_has_roles (Spatie)
- role_has_permissions (Spatie)
- stock_ledgers
- approval_logs
- kecamatans
- kelurahans
- lokasi_pju
- pju_assets
- distribusi_pju
- pemasangan_pju
- kosakan_pju
- maintenance_pju
- pencopotan_pju
- garansi_pju
- retur_vendor
- sync_logs
- jobs (Laravel Queue)
- failed_jobs
```

---

## 4. RBAC Architecture

```
Menggunakan: spatie/laravel-permission

Permission naming convention:
{resource}.{action}

Contoh:
inventory.view
inventory.create
inventory.update
inventory.delete
inventory.approve
inventory.void
inventory.export

pju.view
pju.create
pju.update
pju.approve

laporan.view
laporan.export
laporan.print

admin.user.manage
admin.role.manage
```

---

## 5. Service Layer Pattern

```php
// Controller (THIN — hanya orkestrasi)
class BarangMasukController extends Controller
{
    public function store(StoreBarangMasukRequest $request)
    {
        $this->authorize('inventory.create');
        $result = $this->inventoryService->createBarangMasuk($request->validated());
        return response()->json(['success' => true, 'data' => $result]);
    }
}

// Service (BUSINESS LOGIC)
class InventoryService
{
    public function createBarangMasuk(array $data): BarangMasuk
    {
        return DB::transaction(function () use ($data) {
            $kode = $this->codeGenerator->generate('barang_masuk');
            $masuk = BarangMasuk::create([...$data, 'kode_transaksi' => $kode]);
            
            if ($this->approvalService->isRequired('barang_masuk')) {
                $masuk->updateStatus(TransactionStatus::SUBMITTED);
                $this->approvalService->initApproval($masuk);
            } else {
                $this->postTransaction($masuk);
            }
            
            return $masuk;
        });
    }
}
```

---

## 6. Google Sheets Integration Architecture

```
Trigger (Cron):
    config('integration.google_sheets.sync_interval') menit

Flow:
    SyncGoogleSheetsCommand::handle()
        ↓
    ReportingService::buildSyncDataset()
        ↓ (hanya whitelist fields)
    SyncToGoogleSheetsJob::dispatch()
        ↓ (async via queue)
    GoogleSheetsService::sync()
        ↓
    Google Sheets API
        ↓
    SyncLog::updateStatus()

Error Handling:
    Gagal → retry (max 3x dengan backoff)
    Max retry tercapai → SyncLog::FAILED
    Alert admin (via notification atau log)
    ERP TIDAK TERPENGARUH
```

---

## 7. Security Architecture

```
Layer 1 — Transport:     HTTPS (production)
Layer 2 — Authentication: Laravel Session + Sanctum (API)
Layer 3 — Authorization:  Spatie Permission + Policies
Layer 4 — Input:          Form Request Validation
Layer 5 — SQL:            Eloquent ORM (parameterized)
Layer 6 — CSRF:           Laravel CSRF Token
Layer 7 — XSS:            Blade auto-escaping
Layer 8 — Rate Limiting:  Laravel Rate Limiter
Layer 9 — Audit:          Spatie Activity Log
Layer 10 — Secrets:       .env (never committed)
```

---

*Dokumen ini diperbarui setiap ada perubahan arsitektur.*
