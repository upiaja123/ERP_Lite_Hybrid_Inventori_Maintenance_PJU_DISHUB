# FINAL ROLE CRUD MATRIX
## ERP Lite Hybrid Inventori & Maintenance PJU — DISHUB

This matrix defines the exact capabilities of each role across the system's modules, verified through both backend automated tests and frontend browser E2E tests.

### Legend
- ✅ : Full Access / Permitted
- 👁️ : Read Only (View/List/Detail)
- ❌ : Denied / Hidden

| Module / Feature | Superadmin | Admin Gudang | Kepala Gudang | Teknisi | Dept Manager | Viewer |
| :--- | :---: | :---: | :---: | :---: | :---: | :---: |
| **Dashboard** | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ |
| **Master Data (Barang, Jenis, dsb.)** | ✅ | ✅ | ✅ | 👁️ | 👁️ | 👁️ |
| **Inventori: Barang Masuk** | ✅ | ✅ | 👁️ | ❌ | 👁️ | ❌ |
| **Inventori: Barang Keluar** | ✅ | ✅ | 👁️ | ❌ | 👁️ | ❌ |
| **Inventori: Stock Opname** | ✅ | 👁️ | ✅ (Approve) | ❌ | ❌ | ❌ |
| **Inventori: Retur Vendor** | ✅ | 👁️ | ✅ (Complete) | ❌ | ❌ | ❌ |
| **Operasional: Aset PJU** | ✅ | 👁️ | 👁️ | ✅ | 👁️ | 👁️ |
| **Operasional: Maintenance PJU** | ✅ | 👁️ | 👁️ | ✅ | 👁️ | 👁️ |
| **Laporan (Stok, Masuk, Keluar)** | ✅ | ✅ | ✅ | ❌ | ✅ | ✅ |
| **Manajemen Sistem (Users, Roles)** | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ |
| **Activity Log / Audit Trail** | ✅ | ❌ | ✅ | ❌ | ❌ | ❌ |
| **Debug / Patch DB** | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ |

### Frontend Parity Verification
- **Sidebar**: Menus are dynamically hidden based on the user's role (e.g., "Manajemen Sistem" is only visible to Superadmin; "Laporan Pimpinan" for Dept Manager).
- **Action Buttons**: "Tambah", "Edit", and "Hapus" buttons are completely removed from the DOM when a user only has `Read Only (👁️)` access.
- **Backend Enforcement**: Even if a user manually constructs a POST/PUT/DELETE request (e.g., via Postman or by un-hiding a button), the `CheckRole` middleware and FormRequest authorizations will reject the action with a `403 Forbidden` response.
