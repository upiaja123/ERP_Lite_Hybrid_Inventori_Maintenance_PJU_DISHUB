# DATABASE.md
## ERP Lite Hybrid PJU — DISHUB
## Relational Database Design & Schema Specification

**Version**: 1.3 — CLASS 05
**Last Updated**: 2026-08-17

---

## 1. ERD & Entity Domain Overview

Sistem menggunakan database relasional MySQL/MariaDB dengan aturan non-negotiable:
- **Primary Keys**: Auto-increment BigInt (`id`).
- **Foreign Keys**: Cascading/Set Null On Delete sesuai aturan integritas data.
- **Indexes**: Diindeks pada `kode_barang`, `barcode_value`, `kode_transaksi`, `kode_opname`, `kode_mutasi`, `no_pemasangan`, `no_pencopotan`, `no_work_order`, `no_retur`, `kode_pju`, `pole_number`, `status`, `user_id`, `created_at`.
- **Soft Deletes**: Diberlakukan pada data master & aset (`barangs`, `lokasi_pjus`, `pju_assets`, `stock_opnames`, `stock_mutasis`, `pemasangan_pjus`, `kerusakan_pjus`, `maintenance_pjus`, `pencopotan_pjus`, `retur_vendors`).
- **Transaction Ledger**: `stock_ledgers` bertindak sebagai append-only ledger persediaan.

---

## 2. Table Specifications — Field Operations Domain

#### `pemasangan_pjus` (Pemasangan PJU)
| Column | Type | Attributes | Description |
|---|---|---|---|
| `id` | BIGINT | PK, AUTO_INC | Installation ID |
| `no_pemasangan` | VARCHAR(100) | UNIQUE, INDEX | Nomor bukti pemasangan |
| `pju_asset_id` | BIGINT | FK -> pju_assets.id | Aset PJU dipasang |
| `lokasi_id` | BIGINT | FK -> lokasi_pjus.id | Lokasi pemasangan |
| `teknisi_id` | BIGINT | FK -> users.id (NULLABLE) | Teknisi pelaksana |
| `maintenance_id` | BIGINT | FK -> maintenance_pjus.id | Ref work order (nullable) |
| `tanggal_pasang` | DATE | NOT NULL | Tanggal pemasangan |
| `kondisi_pasang` | VARCHAR(100) | DEFAULT 'BAIK' | Kondisi saat dipasang |
| `foto_pemasangan` | VARCHAR(255) | NULLABLE | Path foto dokumentasi |
| `status` | ENUM | PENDING, TERPASANG, BATAL | Status pemasangan |

#### `pencopotan_pjus` (Pencopotan PJU)
| Column | Type | Attributes | Description |
|---|---|---|---|
| `id` | BIGINT | PK, AUTO_INC | Uninstallation ID |
| `no_pencopotan` | VARCHAR(100) | UNIQUE, INDEX | Nomor bukti pencopotan |
| `pju_asset_id` | BIGINT | FK -> pju_assets.id | Aset PJU dicopot |
| `lokasi_id` | BIGINT | FK -> lokasi_pjus.id | Lokasi asal pencopotan |
| `tanggal_copot` | DATE | NOT NULL | Tanggal pencopotan |
| `teknisi_id` | BIGINT | FK -> users.id (NULLABLE) | Teknisi pelaksana |
| `alasan` | ENUM | RUSAK_BERAT, MAINTENANCE, RETUR_VENDOR, PEREMAJAAN | Alasan pencopotan |
| `kondisi_barang` | VARCHAR(255) | NOT NULL | Kondisi barang saat dicopot |

#### `maintenance_pjus` (Work Order Maintenance)
| Column | Type | Attributes | Description |
|---|---|---|---|
| `id` | BIGINT | PK, AUTO_INC | Maintenance ID |
| `no_work_order` | VARCHAR(100) | UNIQUE, INDEX | Nomor Work Order |
| `kerusakan_id` | BIGINT | FK -> kerusakan_pjus.id | Ref laporan kerusakan |
| `pju_asset_id` | BIGINT | FK -> pju_assets.id | Aset PJU di-maintenance |
| `lokasi_id` | BIGINT | FK -> lokasi_pjus.id | Lokasi aset |
| `teknisi_id` | BIGINT | FK -> users.id (NULLABLE) | Teknisi/Tim ditugaskan |
| `jenis_maintenance`| ENUM | PREVENTIF, KOREKTIF, DARURAT | Tipe pemeliharaan |
| `tindakan_perbaikan`| TEXT | NOT NULL | Catatan tindakan |
| `spare_part_digunakan`| JSON | NULLABLE | JSON array spare part |
| `hasil` | ENUM | PENDING, BERHASIL, BUTUH_RETUR, GAGAL | Hasil pekerjaan |
| `status` | ENUM | SCHEDULED, IN_PROGRESS, COMPLETED, CANCELLED | Status WO |

#### `garansi_pjus` & `retur_vendors`
- `garansi_pjus`: `id`, `pju_asset_id`, `supplier_id`, `no_kontrak_garansi`, `tanggal_mulai`, `tanggal_berakhir`, `status` (`BERLAKU`, `EXPIRED`, `CLAIMED`).
- `retur_vendors`: `id`, `no_retur`, `pju_asset_id`, `supplier_id`, `alasan_retur`, `tanggal_retur`, `tanggal_kembali`, `kondisi_kembali`, `status` (`PROSES_VENDOR`, `SELESAI_GANTI`, `DITOLAK_VENDOR`).
