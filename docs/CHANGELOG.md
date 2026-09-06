# CHANGELOG.md
## ERP Lite Hybrid PJU — DISHUB

Format: [YYYY-MM-DD] [CLASS] [TYPE] — Description

Types: ADDED | CHANGED | FIXED | DEPRECATED | REMOVED | SECURITY | DOCS

---

## [2026-08-17] — CLASS 08 COMPLETED (MIGRATION, QA, UAT & DEPLOYMENT)

### ADDED
- ADDED: `app/Http/Controllers/ImportController.php` — 6-stage Excel/CSV import pipeline: Upload → Preview → Validate → Error Report → Approve → Import with opening stock via ledger.
- ADDED: `app/Http/Middleware/RateLimitMiddleware.php` — Rate limiting protection (60 req/min, `429` response with `X-RateLimit-*` headers).
- ADDED: `app/Http/Controllers/Api/V1/BarangApiController.php` — REST API v1 for Barang with pagination and rate limiting.
- ADDED: `tests/Feature/SecurityAndApiTest.php` — Security test suite covering unauthorized routes, privilege escalation, malicious file upload rejection, and API secret exposure.
- ADDED: `docs/MIGRATION.md` — 9-stage Excel-to-MySQL migration pipeline specification with column mapping, validation rules, and data preservation policy.
- ADDED: `docs/UAT.md` — 40+ UAT scenarios across 5 roles covering all system modules with ID, Role, Precondition, Steps, Expected, Actual, Evidence, Status columns.
- ADDED: `docs/GO_LIVE.md` — Production deployment guide with environment config, backup strategy (daily/weekly/monthly), rollback procedure, monitoring, 15-item go-live checklist, PIC assignments, Git release flow, and known issues.

### CHANGED
- CHANGED: `routes/web.php` — Registered Import routes (`/import`, `/import/preview`, `/import/approve`) under `superadmin,kepala gudang` middleware.
- CHANGED: `routes/api.php` — Registered REST API v1 routes (`/api/v1/barang`) with `RateLimitMiddleware`.
- CHANGED: `docs/SECURITY.md` — Updated with multi-layer defense-in-depth architecture (CSRF, XSS, SQLi, Rate Limiting, File Upload, Audit Append-Only).
- CHANGED: `docs/TESTING.md` — Updated with `SecurityAndApiTest` specs, security coverage matrix, and performance optimization checklist.
- CHANGED: `docs/PROGRESS.md` — All 8 Execution Classes marked as 100% complete.

### SECURITY
- SECURITY: Rate limiting middleware enforced on all API v1 endpoints (60 req/min per IP/user).
- SECURITY: Malicious file upload validation (`.php`, `.exe`, script files rejected at controller level).
- SECURITY: API v1 response scanned and verified to contain no `password` or `remember_token` fields.

---

## [2026-08-17] — CLASS 07 COMPLETED (GOOGLE SHEETS SYNC & HYBRID INTEGRATION)

### ADDED
- ADDED: `app/Services/GoogleSheetsService.php` — Whitelisted datasets, idempotency, conflict protection, failure isolation.
- ADDED: `app/Jobs/SyncToGoogleSheetsJob.php` — Queue job with 3 retries, exponential backoff.
- ADDED: `app/Console/Commands/SyncGoogleSheetsCommand.php` — Console command for scheduled sync.
- ADDED: `tests/Feature/SyncAndIntegrationTest.php` — API down, idempotency, conflict tests.
- ADDED: `docs/SYNC.md`, `docs/API.md`.

---

## [2026-08-17] — CLASS 06 COMPLETED (REPORTING, DASHBOARD & AUDITABILITY)

### ADDED
- ADDED: `app/Services/ReportingService.php` — 13 report types with multi-criteria filters.
- ADDED: `app/Http/Controllers/LaporanMasterController.php` — Unified report generation (PDF, JSON).
- ADDED: `tests/Feature/ReportingAndAuditTest.php` — Dashboard isolation, audit append-only tests.
- ADDED: `docs/REPORTING.md`.

---

## [2026-08-17] — CLASS 05 COMPLETED (FIELD OPERATIONS & PJU LIFECYCLE)

### ADDED
- ADDED: `app/Services/PjuLifecycleService.php` — Full lifecycle: Pasang, Copot, Maintenance, Warranty, Retur, Traceability.
- ADDED: 6 Models (`PemasanganPju`, `KerusakanPju`, `MaintenancePju`, `PencopotanPju`, `GaransiPju`, `ReturVendor`).
- ADDED: 6 Controllers for field operations.
- ADDED: `tests/Feature/PjuLifecycleTest.php` — End-to-end lifecycle test (8 events).

---

## [2026-08-17] — CLASS 04 COMPLETED (INVENTORY MVP)

### ADDED
- ADDED: `StockMutasi` model, `StockOpnameController`, `StockMutasiController`.
- ADDED: `tests/Feature/CriticalInventoryTest.php` — Formula test (100 → 70 → SO 68 → Adj -2 → Final 68).

---

## [2026-08-09] — CLASS 01-03 COMPLETED (FOUNDATION, DATABASE, MASTER DATA)

### ADDED
- ADDED: Complete project documentation suite (`/docs/`), config files, RBAC system, 11 master data domains.

---

*Release Tag: v1.0.0 — All 8 Execution Classes Complete*
