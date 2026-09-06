# GO_LIVE.md
## ERP Lite Hybrid PJU — DISHUB
## Go-Live Readiness & Deployment Guide

**Version**: 1.0 — CLASS 08
**Last Updated**: 2026-08-17

---

## 1. Deployment Environments

| Environment | Purpose | APP_DEBUG | HTTPS | Database |
|---|---|---|---|---|
| **Development** | Local development & unit testing | `true` | Optional | `inventory_gudang_dev` |
| **Staging** | UAT, integration testing, data migration rehearsal | `false` | Required | `inventory_gudang_staging` |
| **Production** | Live operational system | `false` | **REQUIRED** | `inventory_gudang_prod` |

---

## 2. Production Configuration Checklist

```env
# === PRODUCTION ENVIRONMENT SETTINGS ===
APP_ENV=production
APP_DEBUG=false
APP_URL=https://erp-pju.dishub.go.id

# Session & Security
SESSION_DRIVER=database
SESSION_SECURE_COOKIE=true
SESSION_HTTP_ONLY=true
SESSION_SAME_SITE=strict

# Queue
QUEUE_CONNECTION=database

# Logging
LOG_CHANNEL=daily
LOG_LEVEL=warning

# Google Sheets Sync
SYNC_INTERVAL_MINUTES=60
GOOGLE_SHEETS_ENABLED=true
```

---

## 3. Backup & Recovery Strategy

### Database Backup Schedule

| Frequency | Retention | Method | Storage |
|---|---|---|---|
| **Daily** (Incremental) | Minimum **30 hari** | `mysqldump --single-transaction` | Local + Cloud Storage |
| **Weekly** (Full) | **8–12 minggu** | Full database dump + file attachments | Cloud Storage |
| **Monthly** (Archive) | **12 bulan** | Compressed full backup + audit logs | Archive Storage |

### Backup Commands

```bash
# Daily Backup
mysqldump -u root -p --single-transaction inventory_gudang_prod > /backup/daily/db_$(date +%Y%m%d).sql

# Weekly Full Backup
mysqldump -u root -p --single-transaction --routines --triggers inventory_gudang_prod | gzip > /backup/weekly/full_$(date +%Y%m%d).sql.gz

# Test Restore (WAJIB dijalankan minimal 1x sebelum go-live)
mysql -u root -p inventory_gudang_restore_test < /backup/daily/db_20260817.sql
```

### Rollback Procedure

1. **Stop** queue worker dan scheduler.
2. **Restore** database backup terakhir yang diverifikasi.
3. **Rollback** migration jika diperlukan: `php artisan migrate:rollback --step=N`.
4. **Clear** application cache: `php artisan cache:clear && php artisan config:clear`.
5. **Restart** queue worker dan scheduler.
6. **Verify** sistem beroperasi normal.

---

## 4. Queue & Scheduler Configuration

```bash
# Start Queue Worker (Production)
php artisan queue:work --sleep=3 --tries=3 --max-time=3600

# Laravel Scheduler Crontab (Production)
* * * * * cd /var/www/erp-pju && php artisan schedule:run >> /dev/null 2>&1

# Google Sheets Sync Schedule (setiap 60 menit)
php artisan sync:google-sheets
```

---

## 5. Monitoring & Alerting

| Component | Monitoring Method | Alert Threshold |
|---|---|---|
| **Application** | Laravel Log (`storage/logs/laravel.log`) | Error-level logs |
| **Database** | MySQL slow query log | Query > 2 detik |
| **Queue** | `php artisan queue:monitor` | Failed jobs > 0 |
| **Disk** | OS monitoring | Disk usage > 85% |
| **Sync** | `sync_logs` table monitoring | Status `FAILED` berulang > 3x |

---

## 6. Go-Live Checklist

### Pre-Go-Live Requirements

| # | Item | Status | PIC |
|---|---|---|---|
| 1 | UAT passed (semua scenario ✅) | ⬜ | QA Team |
| 2 | Critical bug = **0** | ⬜ | Dev Team |
| 3 | Database backup tested | ⬜ | DBA |
| 4 | Database restore tested | ⬜ | DBA |
| 5 | Security scan completed | ⬜ | Security Team |
| 6 | Data migration validated & reconciled | ⬜ | Admin Gudang + Dev Team |
| 7 | Admin account (superadmin) ready | ⬜ | System Admin |
| 8 | User accounts (all roles) provisioned | ⬜ | System Admin |
| 9 | Domain & DNS configured | ⬜ | DevOps |
| 10 | SSL/TLS certificate installed | ⬜ | DevOps |
| 11 | Google Sheets integration configured | ⬜ | Dev Team |
| 12 | Hybrid sync tested end-to-end | ⬜ | Dev Team |
| 13 | Monitoring & alerting configured | ⬜ | DevOps |
| 14 | Documentation complete & reviewed | ⬜ | PM |
| 15 | Rollback procedure documented & tested | ⬜ | DevOps + DBA |

### Go-Live Day Sequence

1. **T-2 jam**: Final database backup, freeze code changes.
2. **T-1 jam**: Deploy ke production, run migrations (`php artisan migrate --force`).
3. **T-0**: Enable production access, start queue worker & scheduler.
4. **T+30 menit**: Smoke test seluruh critical path oleh QA.
5. **T+1 jam**: Monitoring dashboard & sync logs verification.
6. **T+24 jam**: Post-go-live review meeting.

---

## 7. Known Issues & Limitations (Pre-Go-Live)

> [!IMPORTANT]
> Sistem **BELUM DINYATAKAN production-ready** jika masih ada critical issue yang belum terselesaikan.

| Issue ID | Severity | Description | Status | Mitigation |
|---|---|---|---|---|
| KNOWN-001 | **LOW** | Google Sheets sync memerlukan konfigurasi Google API credentials manual | OPEN | Dokumentasi setup di `SYNC.md` |
| KNOWN-002 | **LOW** | Format kode barang PJU belum dikonfirmasi oleh Dishub | OPEN | Menggunakan generator fleksibel via `config/erp.php` |
| KNOWN-003 | **MEDIUM** | GPS koordinat lokasi PJU bersifat `nullable` (optional untuk MVP) | OPEN | Fitur GPS akan ditambahkan di versi berikutnya |

---

## 8. PIC & Support Procedure

| Role | Responsibility | Contact |
|---|---|---|
| **Technical Project Manager** | Koordinasi deployment, escalation | TBD |
| **Lead Developer** | Code review, hotfix, technical decisions | TBD |
| **DBA** | Backup, restore, database performance | TBD |
| **DevOps** | Server, domain, SSL, monitoring | TBD |
| **QA Lead** | UAT coordination, regression testing | TBD |
| **Admin Gudang (PIC Dishub)** | Data migration validation, operational testing | TBD |

### Support Escalation

1. **Level 1**: Admin Gudang — menangani masalah operasional harian (input data, laporan).
2. **Level 2**: Lead Developer — menangani bug, error, dan fitur enhancement.
3. **Level 3**: Technical PM + DBA — menangani downtime, data corruption, dan disaster recovery.

---

## 9. Git Release Strategy

### Branch Flow

```
feature/xxx
  → develop
    → staging
      → UAT
        → main
          → production (tagged release)
```

### Release Tag

```
v1.0.0 — Initial Production Release
├── CLASS 01: Project Foundation
├── CLASS 02: Database, Auth & RBAC
├── CLASS 03: Master Data Module
├── CLASS 04: Inventory MVP (Ledger, Opname, Mutasi)
├── CLASS 05: Field Operations & PJU Lifecycle
├── CLASS 06: Reporting, Dashboard & Auditability
├── CLASS 07: Google Sheets Sync & Hybrid Integration
└── CLASS 08: Migration, QA, UAT & Deployment
```

### Semantic Versioning

- `MAJOR.MINOR.PATCH` (e.g., `v1.0.0`)
- **MAJOR**: Breaking changes pada database schema atau API contract.
- **MINOR**: Fitur baru tanpa breaking change.
- **PATCH**: Bug fix dan security patch.
