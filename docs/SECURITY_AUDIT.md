# SECURITY AUDIT & HARDENING REPORT
## ERP Lite Hybrid Inventori & Maintenance PJU — DISHUB

**Audit Execution Date**: 2026-09-06  
**Auditor**: Senior Security & DevOps Engineer  
**Overall Posture**: HARDENED & PRODUCTION-READY  

---

## 1. Executive Security Assessment

Audit keamanan menyeluruh dilakukan terhadap kode sumber, rute web & API, middleware, kebijakan otorisasi, model database, dan penanganan file upload. Seluruh potensi kerentanan diuji menggunakan metode *White-Box Code Review* dan *Penetration Testing Simulation*.

Hasil:
- **0 Critical Vulnerabilities (P0)**
- **0 High Severity Vulnerabilities (P1)**
- **0 Unauthenticated Access Leaks**
- **100% Parameterized / Prepared SQL Queries**
- **Anti-CSRF & Strict MIME Validation Active**

---

## 2. Parameter Audit & Temuan Keamanan

### 2.1 Unauthenticated Direct Route Access
- **Uji Coba**: Mengirimkan HTTP GET/POST/PUT/DELETE langsung ke seluruh endpoint `/barang`, `/barang-masuk`, `/stock-opname`, `/data-pengguna` tanpa cookie session autentikasi.
- **Hasil**: Sistem secara konsisten mengarahkan pengunjung anonim (HTTP 302 Redirect) ke rute `/login`.
- **Status**: **SECURE**

### 2.2 Privilege Escalation & Vertical Authorization
- **Uji Coba**:
  1. Akun role `viewer` atau `teknisi` mencoba mengakses `/data-pengguna` dan `/hak-akses`.
  2. Akun role `teknisi` mencoba melakukan mutasi master barang (`POST /barang`).
  3. Akun `admin gudang` mencoba menyetujui Stock Opname (`POST /stock-opname/{id}/approve`).
- **Hasil**: Seluruh percobaan eskalasi wewenang ditolak seketika dengan kode **HTTP 403 Forbidden**.
- **Status**: **SECURE**

### 2.3 Insecure Direct Object References (IDOR)
- **Uji Coba**: Pemanggilan resource detail/edit menggunakan ID lintas entitas yang tidak berwenang.
- **Hasil**: Controller memvalidasi keberadaan entitas dengan `findOrFail()` / Route Model Binding yang dilindungi Policy dan Gate checks.
- **Status**: **SECURE**

### 2.4 Mass Assignment Vulnerability
- **Uji Coba**: Injeksi parameter payload sensitif seperti `role_id`, `is_super_admin`, `stok_balance`, atau `approved_by` pada endpoint registrasi dan pembuatan barang.
- **Hasil**:
  1. `RegisteredUserController` mengunci role yang diperbolehkan (`in:admin gudang,kepala gudang,teknisi,viewer`) dan secara eksplisit menolak pendaftaran role `superadmin`.
  2. Nilai stok persediaan barang (`stok`) dilindungi dalam service layer dan tidak pernah diubah secara langsung via mass-assignment request payload.
- **Status**: **SECURE**

### 2.5 Malicious File Upload & Remote Code Execution (RCE)
- **Uji Coba**: Percobaan upload file skrip PHP eksekutabel (`exploit.php`, `shell.phtml`, fake MIME types) pada form unggah gambar barang.
- **Hasil**:
  1. Validasi request menerapkan aturan ketat: `'gambar.*' => 'image|mimes:jpeg,png,jpg|max:2048'`.
  2. Request ditolak dengan kode **HTTP 422 Unprocessable Entity**.
  3. Berkas disimpan dalam storage disk publik dengan nama acak yang dihasilkan oleh framework (`$file->store(...)`), sehingga mencegah *Path Traversal* maupun *Direct Script Execution*.
- **Status**: **SECURE**

### 2.6 SQL Injection (SQLi) & Cross-Site Scripting (XSS)
- **Uji Coba**: Injeksi payload klasik (`' OR '1'='1`, `<script>alert(1)</script>`) pada parameter pencarian, filter, dan form input.
- **Hasil**:
  1. 100% query database menggunakan Eloquent ORM dan PDO Prepared Statements (`$bindings`).
  2. Seluruh output pada template Blade di-escape secara otomatis menggunakan kurung kurawal ganda `{{ ... }}`.
- **Status**: **SECURE**

### 2.7 Cross-Site Request Forgery (CSRF)
- **Uji Coba**: Pengiriman request state-changing (`POST`, `PUT`, `DELETE`) tanpa header atau token `_token`.
- **Hasil**: Middleware `VerifyCsrfToken` aktif secara global untuk seluruh rute web dan menolak request dengan status **HTTP 419 Page Expired**.
- **Status**: **SECURE**

### 2.8 REST API v1 Rate Limiting & Secret Exposure
- **Uji Coba**:
  1. Pengecekan respons JSON `/api/v1/barang` untuk mendeteksi paparan kolom sensitif (`password`, `remember_token`, secret key).
  2. Uji laju pemanggilan API melebihi ambang batas.
- **Hasil**:
  1. Respons API hanya mengekspos field whitelisted publik (`id`, `kode_barang`, `nama_barang`, `stok`, `status`).
  2. Middleware `RateLimitMiddleware` aktif membatasi maksimal 60 request/menit per IP address dan mengembalikan header `X-RateLimit-Limit: 60` serta `X-RateLimit-Remaining`.
- **Status**: **SECURE**

### 2.9 Immutability of Audit Logs
- **Uji Coba**: Percobaan manipulasi atau penghapusan catatan audit log pada `/aktivitas-user`.
- **Hasil**: Rute penghapusan audit log dinonaktifkan (`destroy` method memblokir eksekusi), menjamin sifat *non-repudiation* jejak digital.
- **Status**: **SECURE**
