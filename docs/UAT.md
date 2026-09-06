# UAT.md
## ERP Lite Hybrid PJU — DISHUB
## User Acceptance Testing (UAT) Scenarios

**Version**: 1.0 — CLASS 08
**Last Updated**: 2026-08-17

---

## Roles Under Test

| Role Code | Display Name | Deskripsi |
|---|---|---|
| `superadmin` | Super Admin | Akses penuh seluruh modul dan fitur sistem |
| `kepala gudang` | Warehouse Manager | Manajemen gudang, approval, dan laporan |
| `admin gudang` | Admin Gudang | Input transaksi, master data, operasional harian |
| `teknisi` | Teknisi | Operasi lapangan, maintenance, pemasangan, pencopotan |
| `viewer` | Viewer | Hanya melihat data, tidak dapat mengedit |

---

## UAT Scenario Format

Setiap scenario dicatat dengan format berikut:

| Field | Description |
|---|---|
| **ID** | Kode unik scenario (UAT-XXX) |
| **Role** | Role pengguna yang menjalankan scenario |
| **Precondition** | Kondisi awal yang harus dipenuhi sebelum scenario dijalankan |
| **Steps** | Langkah-langkah yang dilakukan user |
| **Expected** | Hasil yang diharapkan |
| **Actual** | Hasil aktual saat pengujian (diisi saat UAT) |
| **Evidence** | Screenshot / Video / Log ID |
| **Status** | ⬜ NOT TESTED / ✅ PASSED / ❌ FAILED / ⚠️ PARTIAL |

---

## 1. Authentication & Authorization

| ID | Role | Precondition | Steps | Expected | Actual | Evidence | Status |
|---|---|---|---|---|---|---|---|
| UAT-001 | ALL | Belum login | Akses `/dashboard` | Redirect ke `/login` | | | ⬜ |
| UAT-002 | superadmin | Akun aktif | Login dengan email & password valid | Masuk dashboard Pimpinan | | | ⬜ |
| UAT-003 | viewer | Akun aktif | Login, coba akses `/data-pengguna` | HTTP 403 Forbidden | | | ⬜ |
| UAT-004 | admin gudang | Akun aktif | Login, coba akses `/hak-akses` | HTTP 403 Forbidden | | | ⬜ |

---

## 2. Dashboard Role-Based

| ID | Role | Precondition | Steps | Expected | Actual | Evidence | Status |
|---|---|---|---|---|---|---|---|
| UAT-010 | superadmin | Login | Akses `/dashboard` | Melihat: Total Stock, Maintenance WO, Garansi, Retur, Trend Bulanan, Wilayah, KPI | | | ⬜ |
| UAT-011 | admin gudang | Login | Akses `/dashboard` | Melihat: Stock, Inbound/Outbound, Stok Minimum, Opname Discrepancy. Tidak melihat metrik Pimpinan | | | ⬜ |
| UAT-012 | teknisi | Login | Akses `/dashboard` | Melihat: WO Assigned, Active, Completed. Tidak melihat metrik Pimpinan | | | ⬜ |

---

## 3. Master Data CRUD

| ID | Role | Precondition | Steps | Expected | Actual | Evidence | Status |
|---|---|---|---|---|---|---|---|
| UAT-020 | admin gudang | Login | Tambah Barang baru dengan barcode INDIVIDUAL unique | Barang tersimpan, barcode tercatat | | | ⬜ |
| UAT-021 | admin gudang | Barang UAT-020 ada | Tambah Barang dengan barcode INDIVIDUAL yang sama | Gagal: validasi unique barcode | | | ⬜ |
| UAT-022 | admin gudang | Login | Tambah Merk, Watt, Satuan, Jenis | Master data tersimpan | | | ⬜ |
| UAT-023 | admin gudang | Login | Tambah Lokasi PJU, Kecamatan, Kelurahan | Lokasi terdaftar, normalisasi alamat berjalan | | | ⬜ |
| UAT-024 | admin gudang | Login | Tambah Tim operasional | Tim tersimpan | | | ⬜ |

---

## 4. Inventory Transactions

| ID | Role | Precondition | Steps | Expected | Actual | Evidence | Status |
|---|---|---|---|---|---|---|---|
| UAT-030 | admin gudang | Barang master ada | Buat Barang Masuk (qty: 100) → Post | Stok bertambah 100, Ledger entry `INBOUND` tercatat | | | ⬜ |
| UAT-031 | admin gudang | Stok = 100 | Buat Barang Keluar (qty: 30) → Post | Stok berkurang ke 70, Ledger entry `OUTBOUND` tercatat | | | ⬜ |
| UAT-032 | admin gudang | Stok = 70 | Stock Opname (fisik: 68, selisih: -2) → Approve | Adjustment ledger -2, Stok final 68 | | | ⬜ |
| UAT-033 | admin gudang | Stok > 0 | Buat Stock Mutasi → Post | Transfer ledger tercatat, stok asal berkurang | | | ⬜ |

---

## 5. Field Operations (PJU Lifecycle)

| ID | Role | Precondition | Steps | Expected | Actual | Evidence | Status |
|---|---|---|---|---|---|---|---|
| UAT-040 | teknisi | PJU Asset status GUDANG | Pasang PJU di Lokasi A | Status → TERPASANG, lokasi_id terisi | | | ⬜ |
| UAT-041 | teknisi | PJU Asset status TERPASANG | Lapor Kerusakan | Status → RUSAK, record kerusakan tercatat | | | ⬜ |
| UAT-042 | teknisi | Laporan kerusakan ada | Buat Work Order Maintenance | Status → MAINTENANCE, WO tercatat | | | ⬜ |
| UAT-043 | teknisi | WO status SCHEDULED | Selesaikan Maintenance (Hasil: BUTUH_RETUR) | Status aset → RUSAK, WO → COMPLETED | | | ⬜ |
| UAT-044 | teknisi | PJU Asset status TERPASANG/MAINTENANCE | Copot PJU (alasan: RETUR_VENDOR) | Status → RETUR, record pencopotan tercatat | | | ⬜ |
| UAT-045 | admin gudang | PJU Asset status RUSAK/RETUR | Buat Retur Vendor | Status → RETUR, retur record tercatat | | | ⬜ |
| UAT-046 | admin gudang | Retur dalam proses | Selesaikan Retur (SELESAI_GANTI) | Status → GUDANG, lokasi_id = NULL | | | ⬜ |
| UAT-047 | teknisi | PJU Asset status GUDANG (kembali dari retur) | Pasang Kembali di Lokasi B | Status → TERPASANG, lokasi_id = Lokasi B | | | ⬜ |
| UAT-048 | kepala gudang | PJU Asset ada | Lihat Traceability History | Seluruh 8 event lifecycle tampil kronologis | | | ⬜ |

---

## 6. Garansi & Warranty

| ID | Role | Precondition | Steps | Expected | Actual | Evidence | Status |
|---|---|---|---|---|---|---|---|
| UAT-050 | admin gudang | PJU Asset & Supplier ada | Daftarkan Garansi (berlaku 1 tahun) | Status garansi = BERLAKU | | | ⬜ |
| UAT-051 | admin gudang | Garansi sisa ≤ 30 hari | Cek status garansi | Status garansi = EXPIRING | | | ⬜ |
| UAT-052 | admin gudang | Garansi sudah lewat | Cek status garansi | Status garansi = EXPIRED | | | ⬜ |

---

## 7. Reporting & Export

| ID | Role | Precondition | Steps | Expected | Actual | Evidence | Status |
|---|---|---|---|---|---|---|---|
| UAT-060 | kepala gudang | Data transaksi ada | Generate Laporan Stok PDF | PDF ter-generate dan ter-stream | | | ⬜ |
| UAT-061 | kepala gudang | Data transaksi ada | Generate Laporan Barang Masuk dengan filter tanggal | Data terfilter sesuai rentang tanggal | | | ⬜ |
| UAT-062 | kepala gudang | Data maintenance ada | Generate Laporan Maintenance | Laporan tampil dengan teknisi, lokasi, hasil | | | ⬜ |

---

## 8. Google Sheets Sync & Hybrid Integration

| ID | Role | Precondition | Steps | Expected | Actual | Evidence | Status |
|---|---|---|---|---|---|---|---|
| UAT-070 | superadmin | Data stok ada | Jalankan `sync:google-sheets stok_ringkasan` | Sync SUCCESS, records_synced > 0 | | | ⬜ |
| UAT-071 | superadmin | Sync pernah SUCCESS (payload sama) | Jalankan ulang sync dataset yang sama | Status SUCCESS dengan SKIP_DUPLICATE | | | ⬜ |
| UAT-072 | superadmin | Google API tidak available | Jalankan sync | Status FAILED, transaksi ERP utama tetap aman | | | ⬜ |

---

## 9. Data Migration (Excel Import)

| ID | Role | Precondition | Steps | Expected | Actual | Evidence | Status |
|---|---|---|---|---|---|---|---|
| UAT-080 | kepala gudang | File CSV siap | Upload CSV → Preview | Preview data valid & invalid ditampilkan | | | ⬜ |
| UAT-081 | kepala gudang | Preview sudah dilakukan | Approve Import | Data valid masuk DB, opening stock via ledger | | | ⬜ |
| UAT-082 | kepala gudang | Barcode duplikat di CSV | Upload CSV berisi duplikat barcode | Error report menunjukkan baris invalid | | | ⬜ |

---

## 10. Security & Audit

| ID | Role | Precondition | Steps | Expected | Actual | Evidence | Status |
|---|---|---|---|---|---|---|---|
| UAT-090 | viewer | Login | Coba akses `/data-pengguna` | HTTP 403 Forbidden | | | ⬜ |
| UAT-091 | admin gudang | Login | Coba DELETE `/aktivitas-user/1` | HTTP 403 (Audit Append-Only) | | | ⬜ |
| UAT-092 | ALL | Login | Lakukan aksi CRUD | Activity log tercatat di Audit Trail | | | ⬜ |
