# SYNC.md
## ERP Lite Hybrid PJU — DISHUB
## Hybrid Synchronization & Transparency Layer Architecture

**Version**: 1.0 — CLASS 07
**Last Updated**: 2026-08-17

---

## 1. System of Record & Hybrid Architecture

### 🔒 RULE NON-NEGOTIABLE
1. **MySQL adalah SYSTEM OF RECORD**: Database MySQL / MariaDB pada ERP utama adalah satu-satunya sumber kebenaran data persediaan, transaksi, dan aset PJU.
2. **Google Sheets adalah TRANSPARENCY LAYER**: Google Sheets bersifat *read-only public/transparency report channel*.
3. **Failure Isolation**: Jika Google Sheets API down, koneksi internet terputus, atau terjadi kegagalan jaringan eksternal, transaksi ERP utama **TIDAK BOLEH** gagal atau dibatalkan.

---

## 2. Synchronization Engine & Workflow

```
┌─────────────────────────────────────────────────────────────┐
│                 MySQL — SYSTEM OF RECORD                    │
│     (Barang, StockLedger, LokasiPju, PjuAsset, Transaksi)   │
└──────────────────────────────┬──────────────────────────────┘
                               │ Trigger / Schedule (Cron)
┌──────────────────────────────▼──────────────────────────────┐
│                  GoogleSheetsService                        │
│   1. Build Whitelisted Dataset                              │
│   2. Compute Payload MD5 Hash (Idempotency Check)           │
│   3. Check Conflict Status                                  │
└──────────────────────────────┬──────────────────────────────┘
                               │ Async Queue (`SyncToGoogleSheetsJob`)
┌──────────────────────────────▼──────────────────────────────┐
│                Google Sheets API / Apps Script              │
│                (Public Transparency Spreadsheet)            │
└─────────────────────────────────────────────────────────────┘
```

---

## 3. Whitelisted Public Datasets

Hanya dataset publik yang diizinkan untuk dikirim ke Google Sheets Transparency Layer:

| Dataset Name | Allowed Fields | Protected / Excluded Fields |
|---|---|---|
| `stok_ringkasan` | `kode_barang`, `nama_barang`, `stok`, `stok_minimum` | Price, cost, supplier detail, internal notes |
| `laporan_barang_masuk` | `tanggal_masuk`, `kode_transaksi`, `nama_barang`, `jumlah_masuk` | User password, API tokens, internal IDs |
| `laporan_barang_keluar` | `tanggal_keluar`, `kode_transaksi`, `nama_barang`, `jumlah_keluar` | Internal notes, customer private data |
| `pju_status` | `kode_pju`, `nama_pju`, `status_aset` | Internal serials, supplier cost, internal logs |

---

## 4. Conflict Handling & Resolution Policy

- **Conflict Detection**: Jika ditemukan perbedaan versi data (*data conflict*) antara sistem lokal dan target eksternal:
  - System **TIDAK BOLEH** melakukan *automatic overwrite*.
  - Status sinkronisasi di-set menjadi `CONFLICT`.
  - Detail konflik dicatat pada `sync_logs.conflict_details` (menyimpan `source`, `target`, `timestamp`, `local_hash`, `remote_hash`, `status = RESOLVE_MANUALLY_DO_NOT_OVERWRITE`).
  - Administrator/Pimpinan melakukan peninjauan manual.
