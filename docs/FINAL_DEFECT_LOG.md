# FINAL DEFECT LOG & COMPLIANCE FINDINGS
## ERP LITE HYBRID INVENTORI & MAINTENANCE PJU — DISHUB

**Audit Version**: 2.0 (Post-Hardening & Full Verification)  
**Date**: 2026-09-06  
**Auditor**: Senior QA Engineer & Lead Security Architect  

---

## 1. KLASIFIKASI DEFECT & KATEGORI

- **P0**: Critical / Security / Data Corruption / Negative Balance
- **P1**: Core Business Function Broken / View Resolution Failure
- **P2**: Non-Critical Functional Issue / Alignment Discrepancy
- **P3**: Minor / UI / Cosmetic
- **Spesifikasi Mismatch**: `VERSION-MISMATCH`, `ROLE-MISMATCH`, `PERMISSION-MISMATCH`, `ARCHITECTURE-MISMATCH`, `DATA-MODEL-MISMATCH`

---

## 2. LOG TEMUAN AUDIT LENGKAP

### FINDING #1: [VERSION-MISMATCH] Laravel 12 Specification vs Laravel 10.50.2 Actual Runtime
- **ID**: `DEF-001`
- **Category**: `VERSION-MISMATCH`
- **Requirement**: Master Project Documentation menyebutkan ekspektasi rilis pada Laravel 12 / PHP 8.3.
- **Actual**: Runtime aktual sistem berjalan pada **Laravel 10.50.2** dengan **PHP 8.4.7**.
- **Mismatch**: Versi framework mayor berbeda (Laravel 10 vs Laravel 12).
- **Severity**: `P2 (Non-Critical Architecture Specification Mismatch)`
- **Root Cause**: Dokumen `PROJECT_DISCOVERY.md` baris 49 dan `REQUIREMENTS.md` baris 202 mendefinisikan *Laravel 10 (existing) $\rightarrow$ upgrade path ke 11/12 jika diperlukan*. Proyek ini sengaja dibangun di atas baseline Laravel 10 LTS yang stabil untuk menghindari inkompatibilitas pustaka pihak ketiga (`breadcrumbs`, `sweet-alert`, `dompdf`).
- **Recommendation**: Jangan ubah versi secara mendadak saat ini karena berisiko merusak dependensi pihak ketiga. Perbarui dokumen `REQUIREMENTS.md` dan `PROJECT_DISCOVERY.md` dengan menyatakan Laravel 10.50.2 (PHP 8.4) sebagai rilis produksi aktif Fase 1, dan Laravel 12 sebagai target Fase 2.
- **Status**: `RESOLVED (DOCUMENTATION_UPDATE_REQUIRED)`

---

### FINDING #2: [ROLE-MISMATCH] Reconciling English Master Roles with Indonesian Operational Roles
- **ID**: `DEF-002`
- **Category**: `ROLE-MISMATCH`
- **Requirement**: Dokumen sistem mendefinisikan 7 peran: *Super Admin, System Administrator, Admin Gudang, Warehouse Manager, Teknisi, Department Manager, Viewer*.
- **Actual**: Basis data awal hanya memiliki 5 peran lapangan: `superadmin`, `kepala gudang`, `admin gudang`, `teknisi`, `viewer`.
- **Mismatch**: Ketidaksesuaian penamaan dan ketiadaan entri formal untuk *System Administrator*, *Warehouse Manager*, dan *Department Manager*.
- **Severity**: `P1 (Role Authorization Inconsistency)`
- **Root Cause**: Perbedaan konvensi terminologi antara dokumen arsitektur teknis berbahasa Inggris dan antarmuka operasional pengguna lokal di lapangan.
- **Recommendation**: Tambahkan pemetaan sinonim kanonikal pada `User::hasRole()`, daftarkan seluruh 8 peran ke tabel `roles` melalui `RbacSeeder`, dan tetapkan permission set masing-masing.
- **Status**: `FIXED & TESTED (RbacSeeder updated, User model aliasing implemented, verified in RbacTest & RolePermissionMatrixTest)`

---

### FINDING #3: [PERMISSION-MISMATCH] Hak Export Department Manager vs Viewer
- **ID**: `DEF-003`
- **Category**: `PERMISSION-MISMATCH`
- **Requirement**: Dokumen `REQUIREMENTS.md` Tabel 5 menetapkan bahwa *Department Manager* memiliki hak `Export = ✅`, sedangkan *Viewer* memiliki hak `Export = ❌`.
- **Actual**: Sebelumnya peran pengawas dipukul rata menjadi *Viewer* tanpa pemisahan peran manajerial yang berhak mengekspor PDF/Excel.
- **Mismatch**: Potensi kebingungan izin ekspor antara auditor internal dengan pimpinan departemen.
- **Severity**: `P2 (Functional Permission Alignment)`
- **Root Cause**: Belum didaftarkannya peran `Department Manager` secara terpisah di tabel `roles` dan `RbacSeeder`.
- **Recommendation**: Daftarkan `Department Manager` dengan hak izin `laporan.export` dan `inventory.approve`, dan isolasi `viewer` menjadi murni *read-only* tanpa hak ekspor.
- **Status**: `FIXED & TESTED (RbacSeeder updated, verified in RolePermissionMatrixTest)`

---

### FINDING #4: [BLADE_VIEW_PARITY] Ketiadaan Berkas View Blade pada Modul PJU yang Terpajang di Sidebar
- **ID**: `DEF-004`
- **Category**: `ARCHITECTURE-MISMATCH / P1-DEFECT`
- **Requirement**: Semua tautan navigasi pada sidebar menu Stisla (`/pju-asset`, `/lokasi-pju`, `/pemasangan-pju`, `/pencopotan-pju`, `/maintenance-pju`, `/garansi-pju`, `/retur-vendor`, `/stock-opname`, `/stock-mutasi`, `/merk`, `/watt`, `/tim`, `/kecamatan`, `/kelurahan`, `/import`) harus dapat dirender oleh browser tanpa galat.
- **Actual**: Pengujian browser subagent menemukan navigasi ke rute-rute tersebut memicu *View resolution exception* (`View [pju-asset.index] not found`, dll.) yang menyebabkan HTTP 500 error bagi pengguna web.
- **Mismatch**: Rute dan API controller sudah ada, namun berkas Blade view belum dibuat.
- **Severity**: `P1 (Core Feature Broken on Web UI)`
- **Root Cause**: Modul PJU dan Master Spesifikasi diselesaikan pada lapisan Controller, Service, dan API, namun berkas Blade view di direktori `resources/views/` belum sempat dipublikasikan.
- **Recommendation**: Buat seluruh berkas index Blade untuk 15 modul tersebut dengan styling standar Stisla Bootstrap 4 dan DataTables jQuery.
- **Status**: `FIXED & TESTED (Seluruh 15 Blade views dibuat; SmokeTest memverifikasi seluruh 29 rute mengembalikan HTTP 200 OK)`

---

### FINDING #5: [DATA-MODEL-MISMATCH] Ketidaksesuaian Nama Kolom Watt Daya
- **ID**: `DEF-005`
- **Category**: `DATA-MODEL-MISMATCH`
- **Requirement**: Master spesifikasi PJU mencatat besaran watt daya (20W, 30W, 40W, 90W, 120W).
- **Actual**: Query pada pengujian awal mencoba mencari `daya_watt` pada tabel `watts`.
- **Mismatch**: Nama kolom aktual di tabel `watts` adalah `nilai_watt`.
- **Severity**: `P2 (Schema Column Discrepancy)`
- **Root Cause**: Perbedaan terminologi antara atribut di tabel aset (`pju_assets.daya_watt`) dan tabel referensi (`watts.nilai_watt`).
- **Recommendation**: Selaraskan pemanggilan kueri referensi master agar selalu merujuk pada `watts.nilai_watt`.
- **Status**: `FIXED & TESTED (ExcelTraceabilityTest updated to nilai_watt; test passed)`

---

### FINDING #6: [DATA-MODEL-MISMATCH] Konvensi Tipe Transaksi StockLedger (IN vs INBOUND)
- **ID**: `DEF-006`
- **Category**: `DATA-MODEL-MISMATCH`
- **Requirement**: Setiap barang masuk harus mencatat transaksi penambahan stok pada buku besar persediaan (`stock_ledgers`).
- **Actual**: Kueri pengujian mencari `transaction_type = 'INBOUND'`.
- **Mismatch**: Nilai riil yang disimpan oleh `InventoryService` pada skema database adalah `transaction_type = 'IN'`.
- **Severity**: `P2 (Ledger Type Naming Discrepancy)`
- **Root Cause**: Implementasi skema `stock_ledgers` menggunakan ENUM ringkas (`IN`, `OUT`, `OPNAME_IN`, `OPNAME_OUT`, `MUTASI_IN`, `MUTASI_OUT`).
- **Recommendation**: Gunakan nilai riil `IN` dan `OUT` secara konsisten pada seluruh service, controller, dan automated test.
- **Status**: `FIXED & TESTED (ExcelTraceabilityTest & CriticalInventoryTest passed)`

---

### FINDING #7: [SECURITY] Pencegahan Eskalasi Hak Akses pada Registrasi Publik
- **ID**: `DEF-007`
- **Category**: `SECURITY / PRIVILEGE-ESCALATION`
- **Requirement**: Pengguna yang mendaftar via halaman publik (`/register`) tidak boleh memilih peran istimewa *Super Admin* atau *System Administrator*.
- **Actual**: Validasi registrasi berpotensi menerima parameter `role_id` superadmin jika tidak diproteksi secara eksplisit.
- **Mismatch**: Risiko eskalasi hak akses (*privilege escalation*).
- **Severity**: `P0 (Critical Security Risk)`
- **Root Cause**: Belum adanya filter penyaringan ID role terlarang pada `RegisteredUserController`.
- **Recommendation**: Tambahkan filter ketat pada `RegisteredUserController::store()` yang memblokir pendaftaran akun dengan nama peran `superadmin`, `Super Admin`, `System Administrator`, atau `Warehouse Manager`.
- **Status**: `FIXED & TESTED (RegistrationTest verifies users cannot register as superadmin)`

---

### FINDING #8: [EXCEL-TRACEABILITY] Representasi Struktur Data Lampu Panasonic & Data Copotan
- **ID**: `DEF-008`
- **Category**: `DATA-MODEL-MISMATCH`
- **Requirement**: Sistem harus mampu merepresentasikan kolom penting dari berkas sumber Excel Dishub (`form lampu panasonic 30 Gudang 6.xlsx` dan `Data Copotan Keseluruhan.xlsx`).
- **Actual**: Belum ada pengujian otomatis yang secara eksplisit memverifikasi pemetaan kolom-kolom Excel sumber ke tabel ERP.
- **Mismatch**: Ketiadaan bukti formal (*evidence*) keterlacakan migrasi data Excel.
- **Severity**: `P1 (Data Traceability Verification)`
- **Root Cause**: Pengujian sebelumnya hanya berfokus pada alur transaksi internal tanpa menyertakan skenario pemetaan berkas Excel sumber.
- **Recommendation**: Tulis rangkaian pengujian otomatis `ExcelTraceabilityTest` yang memverifikasi atribut tahun, barcode, tipe lampu, watt, kuantitas, lokasi jalan, kelurahan, tim kerja, tanggal copot, tanggal pasang, dan status vendor.
- **Status**: `FIXED & TESTED (Tests\Feature\ExcelTraceabilityTest passed with 5 assertions)`

---

### FINDING #9: [INTEGRATION] Isolasi Kegagalan Google Sheets & Idempotensi
- **ID**: `DEF-009`
- **Category**: `ARCHITECTURE-MISMATCH`
- **Requirement**: Kegagalan Google Sheets API tidak boleh membatalkan transaksi ERP MySQL, dan sinkronisasi payload yang tidak berubah harus bersifat idempoten (*skip duplicate*).
- **Actual**: Sebelumnya sinkronisasi langsung dieksekusi tanpa proteksi hash MD5 dan penanganan status konflik.
- **Mismatch**: Risiko duplikasi data dan penghentian transaksi saat koneksi internet Dishub mengalami gangguan.
- **Severity**: `P1 (Resilience & Integration Architecture)`
- **Root Cause**: Belum diterapkannya lapisan penyangga hash payload dan exception boundary pada `GoogleSheetsService`.
- **Recommendation**: Terapkan hash MD5 payload, pemeriksaan idempotensi, penandaan status `CONFLICT` dengan instruksi `RESOLVE_MANUALLY_DO_NOT_OVERWRITE`, serta pencatatan terisolasi pada `sync_logs`.
- **Status**: `FIXED & TESTED (SyncAndIntegrationTest passed with 4 assertions)`

---

## 3. RINGKASAN STATUS DEFECT
- **Total Temuan**: 9 Temuan
- **Temuan P0 (Kritis)**: 1 (DEF-007: Security Registration Escalation) $\rightarrow$ **100% FIXED**
- **Temuan P1 (Mayor)**: 4 (DEF-002, DEF-004, DEF-008, DEF-009) $\rightarrow$ **100% FIXED**
- **Temuan P2 (Minor / Model)**: 4 (DEF-001, DEF-003, DEF-005, DEF-006) $\rightarrow$ **100% FIXED / DOC UPDATE**
- **Sisa Defect P0/P1**: **0 (NOL)**
