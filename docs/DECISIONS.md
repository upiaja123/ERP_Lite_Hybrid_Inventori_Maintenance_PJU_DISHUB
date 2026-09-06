# DECISIONS.md
## ERP Lite Hybrid PJU — DISHUB
## Architecture & Business Decisions Log (Pre-Interview State)

**Status**: PRE-INTERVIEW (Wawancara Dishub: H-1)
**Last Updated**: 2026-08-09

---

## ⚠️ PRINSIP PRE-INTERVIEW

1. **CONFIRMED REQUIREMENT**: Fitur/desain yang sudah divalidasi oleh dokumen master & Excel.
2. **CURRENT TECHNICAL DECISION**: Keputusan arsitektur sementara agar pengembangan tetap berjalan secara modular.
3. **NEEDS CLIENT CONFIRMATION**: Requirement yang **WAJIB** dikonfirmasi pada wawancara besok.
4. **FUTURE-READY DESIGN**: Desain DB/fitur yang fleksibel (misal GPS nullable) sehingga dapat diaktifkan tanpa redesign.

---

## INTERVIEW QUESTION CHECKLIST (UNTUK WAWANCARA BESOK)

| ID | Pertanyaan Wawancara Besok | Target Jawaban |
|---|---|---|
| Q1 | Apakah Dishub sudah memiliki format resmi Kode Barang / Kode PJU? Jika belum, apakah kode tersebut perlu dibuat otomatis oleh sistem? | Format string & auto-numbering rule |
| Q2 | Untuk Barang Masuk dan Barang Keluar, siapa yang berhak menyetujui transaksi sebelum transaksi dianggap sah dan mempengaruhi stok? | Peran approver per jenis transaksi |
| Q3 | Apakah alur approval cukup satu level atau membutuhkan lebih dari satu level? | 1 level vs 2 level approval |
| Q4 | Apakah approval Barang Masuk dan Barang Keluar menggunakan orang/level yang sama? | Pemisahan kewenangan approver |
| Q5 | Untuk Google Sheets/transparansi, seberapa sering data perlu diperbarui? (Real-time, 15m, 30m, 1 jam, atau harian) | Sync interval |
| Q6 | Apakah setiap lokasi PJU wajib memiliki koordinat (GPS), atau koordinat hanya dicatat jika tersedia? | Mandatory vs Optional GPS |
| Q7 | Ketika ada laporan kerusakan PJU, data lokasi apa saja yang biasanya diberikan oleh pelapor? | Format alamat / acuan lokasi |
| Q8 | Apakah alamat/jalan sudah cukup untuk menemukan lokasi, atau biasanya diperlukan nomor tiang? | Peran nomor tiang (pole_number) |
| Q9 | Apakah nomor tiang memiliki format/ID yang unik secara sistemik di Dishub? | Uniqueness constraint nomor tiang |
| Q10 | Apakah Dishub sudah memiliki daftar resmi Kecamatan dan Kelurahan yang harus digunakan sebagai master wilayah? | Master data wilayah Dishub |
| Q11 | Apakah teknisi/vendor biasanya menggunakan Google Maps untuk menemukan lokasi pekerjaan? | UX Google Maps link/navigation |
| Q12 | Apakah sistem akan lebih membantu jika alamat/lokasi dapat langsung dibuka di Google Maps? | Geocoding & Open Maps link |

---

## LOG KEPUTUSAN TEKNIS SEMENTARA (PRE-INTERVIEW)

### Q1 — ITEM CODE GENERATOR
- **Status**: **NEEDS_CLIENT_CONFIRMATION**
- **Decision**: Dibuat `ItemCodeGeneratorService` yang membaca format dari `config/erp.php`. Default development placeholder: `PRPTY-00001`.
- **Rule**: Dilarang hard-code `PRPTY-XXXXX` di business logic. Perubahan format pasca-wawancara hanya mengubah konfigurasi/service.

### Q2 — APPROVAL WORKFLOW ENGINE
- **Status**: **NEEDS_CLIENT_CONFIRMATION**
- **Decision**: Dibuat `ApprovalWorkflowService` yang configurable (0 level / 1 level / 2 level).
- **Rule**: Dilarang hard-code `Admin Gudang → Kepala Gudang`. Status transaksi mendukung: `DRAFT`, `SUBMITTED`, `PENDING_APPROVAL`, `APPROVED`, `REJECTED`, `POSTED`, `VOID`.

### Q3 — GOOGLE SHEETS SYNCHRONIZATION
- **Status**: **PERIODIC / SCHEDULED (CONFIRMED)**. Interval final: **NEEDS_CLIENT_CONFIRMATION**
- **Decision**: Configurable via `.env` (`SYNC_INTERVAL_MINUTES=60` default).
- **Rule**: MySQL adalah **System of Record**. Google Sheets adalah read-only transparency/reporting layer. Jika Google Sheets API error, transaksi ERP utama **TIDAK BOLEH** gagal.

### Q4 — LOKASI PJU DYNAMIC & GPS OPTIONAL
- **Status**: **LOCATION = CONFIRMED. GPS/KOORDINAT = OPTIONAL (NEEDS_CLIENT_CONFIRMATION)**
- **Decision**:
  1. **GPS Tidak Wajib**: Field `latitude` dan `longitude` bersifat `nullable`. Sistem tidak mewajibkan koordinat di awal.
  2. **Dynamic Location Creation**: Lokasi PJU **BUKAN** master data statis yang harus lengkap sebelum sistem digunakan. Lokasi muncul dan bertumbuh secara dinamis mengikuti transaksi/laporan operasional.
  3. **Duplicate Location Protection**: Menggunakan `LocationService` untuk melakukan normalisasi alamat (`trim`, `lowercase`, `whitespace normalization`) sebelum pembuatan lokasi baru.
  4. **Google Maps Link**: Menyeediakan helper `google_maps_url` (berdasarkan koordinat jika ada, atau pencarian alamat jika koordinat null) untuk membantu teknisi/vendor di lapangan.
  5. **Location History**: Setiap lokasi mencatat histori laporan kerusakan, maintenance, barang/lampu yang terpasang, pencopotan, dan teknisi/vendor.

---

*Last updated: 2026-08-09*
