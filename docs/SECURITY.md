# SECURITY.md
## ERP Lite Hybrid PJU — DISHUB
## Security & Access Control Architecture

**Version**: 1.4 — CLASS 08
**Last Updated**: 2026-08-17

---

## 1. Security Overview

Sistem ERP Lite Hybrid PJU mematuhi standar keamanan aplikasi web enterprise:
1. **Zero Assumption Authorization**: Akses ditolak secara default (*deny-by-default*).
2. **Backend Enforcement**: Proteksi otorisasi divalidasi di level Controller via Policies, Gates & Middleware.
3. **Audit Trail Append-Only**: Seluruh aktivitas pengguna tercatat secara permanen dan **DILARANG DIHAPUS**.
4. **Integration & Data Transparency Shield**: Data publik yang disinkronkan ke Google Sheets divalidasi ketat via Whitelist Fields.
5. **Defense in Depth**: Multi-layer security (CSRF, XSS escaping, SQL injection prevention, rate limiting, file upload validation).

---

## 2. Security Layers & Protections

| Layer | Protection | Implementation |
|---|---|---|
| **Authentication** | Session-based login, password hashing (bcrypt) | Laravel Auth, `auth` middleware |
| **Authorization** | Role-based access control (RBAC) | `checkRole` middleware, Policies, Gates |
| **CSRF** | Cross-Site Request Forgery protection | Laravel `@csrf` / `VerifyCsrfToken` middleware |
| **XSS** | Cross-Site Scripting prevention | Blade `{{ }}` auto-escaping |
| **SQL Injection** | Parameterized query protection | Eloquent ORM, Query Builder bindings |
| **Rate Limiting** | API abuse prevention | `RateLimitMiddleware` (60 req/min, `429` response) |
| **File Upload** | Malicious file rejection | Validator `mimes:` whitelist (csv, jpg, png, pdf) |
| **Data Shielding** | Public dataset transparency whitelist | `GoogleSheetsService::buildWhitelistedDataset()` |
| **Audit Trail** | Append-only immutable logging | Spatie Activity Log, `destroy()` returns `403` |

---

## 3. Transparency Layer & Data Shielding

### 🔒 DILARANG DIEXPOSE ke dataset publik / API response:
- Password hash / Auth tokens / API Keys / `.env` secrets
- File credentials (`google-credentials.json`)
- Struktur hak akses internal / permission tables
- Harga beli, biaya pengadaan, catatan rahasia internal
- `remember_token`, `email_verified_at`

---

## 4. Failure Isolation Policy

Jika koneksi ke Google Sheets API down, jaringan internet terputus, atau target eksternal melempar error:
- Sistem mencatat status `FAILED` di `sync_logs`.
- **DATABASE UTAMA MYSQL & TRANSAKSI ERP TETAP SUKSES BERJALAN** tanpa terganggu oleh kegagalan sistem luar.

---

## 5. REST API Security

| Endpoint | Auth Required | Rate Limit | Notes |
|---|---|---|---|
| `GET /api/v1/barang` | No (public read) | 60 req/min | Paginated, no secrets |
| `GET /api/v1/barang/{id}` | No (public read) | 60 req/min | Filtered fields only |
| `GET /api/user` | Sanctum Bearer Token | Standard | Returns authenticated user |
| `POST /import/preview` | Session (superadmin/kepala gudang) | Standard | File upload validation |
| `POST /import/approve` | Session (superadmin/kepala gudang) | Standard | Staging → Production |
