# TESTING.md
## ERP Lite Hybrid PJU — DISHUB
## Testing Architecture & Quality Assurance Document

**Version**: 1.4 — CLASS 08
**Last Updated**: 2026-08-17

---

## 1. Testing Strategy Overview

Sistem ERP Lite Hybrid PJU menggunakan strategi pengujian berlapis (*layered testing strategy*):
1. **Smoke Tests**: Memastikan kestabilan dasar HTTP routes, auth redirect, dan rendering halaman dasar (`SmokeTest.php`).
2. **RBAC & Security Tests**: Memastikan proteksi otorisasi, check role, permission, penolakan user inaktif, dan superadmin bypass (`RbacTest.php`).
3. **Master Data Tests**: Memastikan validasi CRUD, keunikan barcode individual vs batch, presensi nilai seeder watt, dan relasi Eloquent (`MasterDataTest.php`).
4. **Critical Inventory Lifecycle Tests**: Memastikan integritas transaction ledger, pencegahan stok minus, formula perhitungan persediaan ($100 - 30 + (-2) = 68$), serta penelusuran histori (`CriticalInventoryTest.php`).
5. **End-to-End PJU Lifecycle Tests**: Memastikan siklus lengkap barang masuk → pasang → rusak → copot → warranty/retur vendor → kembali ke gudang → pasang kembali dapat ditelusuri presisi (`PjuLifecycleTest.php`).
6. **Reporting & Audit Security Tests**: Memastikan isolasi dashboard berbasis role, validasi filter 13 jenis laporan, dan penolakan penghapusan audit log append-only (`ReportingAndAuditTest.php`).
7. **Hybrid Integration & Sync Tests**: Memastikan keamanan whitelist field, ketahanan transaksi ERP utama saat Google API/jaringan down, idempotensi sync, dan deteksi konflik tanpa overwrite (`SyncAndIntegrationTest.php`).
8. **Security & API Tests**: Memvalidasi unauthorized route blocking, privilege escalation protection, malicious file upload rejection, REST API rate limiting, dan secret non-exposure (`SecurityAndApiTest.php`).

---

## 2. Test Suite Specifications

### 2.7 Hybrid Integration & Sync Test (`SyncAndIntegrationTest.php`)
- `test_whitelisted_dataset_contains_no_sensitive_secrets`: Dataset publik tidak mengandung password/credentials.
- `test_primary_transaction_succeeds_when_google_api_is_down`: Transaksi ERP tetap sukses saat Google API 503.
- `test_idempotency_skips_duplicate_sync`: Sync duplikat dilewati (`SKIP_DUPLICATE`).
- `test_conflict_detection_does_not_overwrite`: Konflik tidak meng-overwrite target.

### 2.8 Security & API Test (`SecurityAndApiTest.php`)
- `test_unauthenticated_user_redirected_from_protected_routes`: User tanpa login di-redirect ke `/login`.
- `test_ordinary_user_cannot_access_user_management`: Privilege escalation ditolak (403).
- `test_malicious_file_upload_rejected`: File PHP/script berbahaya ditolak (422).
- `test_api_v1_exposes_no_sensitive_passwords_or_secrets`: Response API v1 tidak mengandung `password` / `remember_token`.

---

## 3. Security Testing Coverage

| Security Vector | Test Method | Status |
|---|---|---|
| Unauthorized route access | HTTP GET tanpa session → redirect | ✅ Covered |
| Unauthorized API access | HTTP request tanpa auth → 401/302 | ✅ Covered |
| Privilege escalation | Role `viewer` akses `/data-pengguna` → 403 | ✅ Covered |
| Malicious file upload | Upload `.php` file → 422 rejected | ✅ Covered |
| Validation bypass | Input tanpa `nama_barang` → 422 | ✅ Covered |
| SQL injection | Parameterized queries (Eloquent ORM) | ✅ By design |
| XSS | Blade `{{ }}` auto-escaping | ✅ By design |
| CSRF | Laravel `@csrf` middleware | ✅ By design |
| Rate limiting | `RateLimitMiddleware` → 429 | ✅ Covered |
| Secret exposure | API response scan for `password`/`token` | ✅ Covered |
| Audit log deletion | DELETE `/aktivitas-user/{id}` → 403 | ✅ Covered |

---

## 4. Performance Optimization Checklist

| Area | Check | Recommendation |
|---|---|---|
| **N+1 Queries** | Eager loading via `with()` on all controllers | ✅ Applied |
| **Slow Queries** | Indexes on `kode_barang`, `barcode_value`, `kode_transaksi`, `status` | ✅ Applied |
| **Pagination** | API v1 defaults `limit=15`, max `100` | ✅ Applied |
| **Report Performance** | Lazy collection / chunked queries for large exports | ⚠️ Monitor |
| **Queue** | `SyncToGoogleSheetsJob` async queue (3 retries, backoff) | ✅ Applied |
| **API Timeout** | Rate limiting 60 req/min with `429` response | ✅ Applied |

---

## 5. How to Run Tests

```bash
# Menjalankan seluruh test suite (Unit + Feature + Integration + Security + API)
php artisan test

# Filter per suite
php artisan test --filter=SmokeTest
php artisan test --filter=RbacTest
php artisan test --filter=MasterDataTest
php artisan test --filter=CriticalInventoryTest
php artisan test --filter=PjuLifecycleTest
php artisan test --filter=ReportingAndAuditTest
php artisan test --filter=SyncAndIntegrationTest
php artisan test --filter=SecurityAndApiTest
```
