# MIGRATION.md
## ERP Lite Hybrid PJU — DISHUB
## Data Migration Strategy: Excel to MySQL System of Record

**Version**: 1.0 — CLASS 08
**Last Updated**: 2026-08-17

---

## 1. Migration Pipeline Architecture

Migrasi data dari Excel ke MySQL production **DILARANG** dilakukan dengan cara insert langsung. Seluruh data Excel harus melewati pipeline multi-tahap berikut:

```
┌──────────────────────────────────────────────────────────────┐
│  1. EXCEL (Original File)                                    │
│     ↓ Upload CSV                                             │
│  2. RAW (File di-upload ke server, disimpan utuh)            │
│     ↓ Parse Header & Rows                                    │
│  3. STAGING (Data diparsing ke JSON staging cache)            │
│     ↓ Column Mapping + Cleaning                              │
│  4. CLEANING (Normalisasi teks, format tanggal, trim)        │
│     ↓ Master Data Matching                                   │
│  5. MAPPING (Jenis/Satuan/Supplier auto-mapped via firstOrCreate) │
│     ↓ Validation Rules                                       │
│  6. VALIDATION (Duplicate barcode, empty nama_barang, stok)  │
│     ↓ Error Report Generated                                 │
│  7. RECONCILIATION (Preview valid vs invalid rows)           │
│     ↓ Admin Approval                                         │
│  8. APPROVAL (Admin review error report & approve staging)   │
│     ↓ Batch Insert + Opening Ledger                          │
│  9. PRODUCTION (Insert ke `barangs` + Opening Stock Ledger)  │
└──────────────────────────────────────────────────────────────┘
```

---

## 2. Column Mapping Specification

| CSV Column Index | CSV Header (Expected) | Target DB Field | Data Type | Rules |
|---|---|---|---|---|
| 0 | `nama_barang` | `barangs.nama_barang` | STRING | **REQUIRED** |
| 1 | `deskripsi` | `barangs.deskripsi` | STRING | Default: 'Data Impor Excel' |
| 2 | `jenis` | Lookup → `jenis.id` | STRING→FK | Auto `firstOrCreate` |
| 3 | `satuan` | Lookup → `satuans.id` | STRING→FK | Auto `firstOrCreate` |
| 4 | `stok_minimum` | `barangs.stok_minimum` | INT | Default: 10 |
| 5 | `opening_stock` | Via `InventoryService::recordInbound()` | INT | Saldo awal via ledger |
| 6 | `barcode` | `barangs.barcode_value` | STRING | UNIQUE jika INDIVIDUAL |

---

## 3. Validation & Error Report

Setiap baris CSV divalidasi terhadap aturan berikut:

| Validation Rule | Description | Severity |
|---|---|---|
| `nama_barang` required | Nama barang tidak boleh kosong | **REJECT_ROW** |
| Barcode uniqueness | Barcode `INDIVIDUAL` harus unique di DB | **REJECT_ROW** |
| Numeric check | `stok_minimum` dan `opening_stock` harus numerik | **WARN_AND_DEFAULT** |
| Date format | Tanggal pengadaan dalam format `Y-m-d` | **WARN_AND_SKIP** |

Baris invalid **TIDAK DIMASUKKAN** ke production tetapi dilaporkan sebagai Error Report JSON.

---

## 4. Opening Stock & Cut-Off Date

- Opening stock dimasukkan sebagai transaksi `INBOUND` melalui `InventoryService::recordInbound()` dengan kode transaksi `OPENING-{kode_barang}` dan dokumen `MIGRASI-EXCEL-{YYYYMMDD}`.
- Supplier saldo awal otomatis di-set ke `Saldo Awal Migrasi Excel`.
- **Cut-off Date**: Tanggal migrasi menjadi batas pembatas transaksi lama (Excel) dan transaksi baru (ERP). Semua transaksi sebelum cut-off date dicatat sebagai opening balance.

---

## 5. Data Preservation Policy

### 🔒 RULE NON-NEGOTIABLE
- **Data Excel original TIDAK BOLEH DIHAPUS** setelah migrasi. File asli harus diarsipkan.
- Staging cache (`storage/staging/{token}.json`) dihapus hanya setelah proses impor berhasil dijalankan.
- Seluruh aksi impor dicatat di `activity_log` dengan label `MIGRASI DATA EXCEL`.

---

## 6. Migration Checklist

- [ ] Identifikasi seluruh file Excel dan sheet yang akan dimigrasi
- [ ] Mapping kolom Excel → kolom database per sheet
- [ ] Identifikasi duplicate records dan ambiguity
- [ ] Identifikasi data dengan format tanggal tidak standar
- [ ] Identifikasi barcode yang mungkin bentrok
- [ ] Identifikasi master data (Jenis, Satuan, Merk, Watt) yang perlu di-seed terlebih dahulu
- [ ] Identifikasi lokasi PJU yang perlu dimigrasi
- [ ] Tentukan cut-off date antara transaksi Excel dan transaksi ERP
- [ ] Jalankan preview dan error report
- [ ] Review error report bersama Admin/Kepala Gudang
- [ ] Approve staging import
- [ ] Verifikasi reconciliation saldo awal vs saldo Excel
- [ ] Arsipkan file Excel original
