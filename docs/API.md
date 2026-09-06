# API.md
## ERP Lite Hybrid PJU — DISHUB
## Hybrid Synchronization & REST API Specification

**Version**: 1.0 — CLASS 07
**Last Updated**: 2026-08-17

---

## 1. Hybrid Communication Policy

Sistem ERP Lite Hybrid PJU mendukung komunikasi antar-node (*Cloud + On-Premise*) **HANYA** melalui **REST API (HTTPS JSON)**. Dilarang melakukan *direct database-to-database connection* lintas jaringan publik demi menjaga keamanan data persediaan.

---

## 2. Synchronization API Endpoints

### 2.1 Trigger Background Sync
- **Endpoint**: `GET /laporan/generate?type={dataset}&format=json`
- **Authentication**: Session / Sanctum Bearer Token
- **Query Parameters**:
  - `type`: `stock` | `inbound` | `outbound` | `mutation` | `pju_status`
  - `format`: `json` | `pdf`
- **Response Example**:
```json
{
  "success": true,
  "type": "stok_ringkasan",
  "data": [
    {
      "kode_barang": "PRPTY-00001",
      "nama_barang": "Lampu LED 120W",
      "stok": 68,
      "stok_minimum": 10
    }
  ]
}
```

---

## 3. Sync Audit Log Statuses

Seluruh log eksekusi API & sync tercatat pada tabel `sync_logs`:

| Status | Meaning |
|---|---|
| `PENDING` | Sync job terdaftar dalam antrean queue |
| `PROCESSING` | Sync job sedang dieksekusi oleh worker |
| `SUCCESS` | Sync berhasil terkirim ke target eksternal |
| `FAILED` | Sync gagal (API down, network timeout) — **ERP utama tetap sukses** |
| `CONFLICT` | Deteksi konflik data — **Target tidak di-overwrite otomatis** |
