<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no" name="viewport">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Inventory Gudang</title>

  <!-- General CSS Files -->
  <link rel="stylesheet" href="assets/modules/bootstrap/css/bootstrap.min.css">
  <link rel="stylesheet" href="assets/modules/fontawesome/css/all.min.css">


  <!-- CSS Libraries -->

  <!-- Template CSS -->

  <link rel="stylesheet" href="assets/css/style.css">
  <link rel="stylesheet" href="assets/css/components.css">

  <script src="https://code.jquery.com/jquery-3.7.0.min.js" integrity="sha256-2Pmvv0kuTBOenSvLm6bvfBSSHrUJ+3A7x6P5Ebd07/g=" crossorigin="anonymous"></script>


  <!-- Select2 -->
  <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />


  <!-- Datatable Jquery -->
  <link rel="stylesheet" href="//cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">

  <link rel="stylesheet" href="https://cdn.datatables.net/datetime/1.4.1/css/dataTables.dateTime.min.css">

  <!-- Start GA -->
  <script async src="https://www.googletagmanager.com/gtag/js?id=UA-94034622-3"></script>
  <script>
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);}
    gtag('js', new Date());

    gtag('config', 'UA-94034622-3');
  </script>


<!-- /END GA --></head>

<body>
  <div id="app">
    <div class="main-wrapper main-wrapper-1">
      <div class="navbar-bg"></div>
      <nav class="navbar navbar-expand-lg main-navbar">
        <form class="form-inline mr-auto">
          <ul class="navbar-nav mr-3">
            <li><a href="#" data-toggle="sidebar" class="nav-link nav-link-lg"><i class="fas fa-bars"></i></a></li>
            <li><a href="#" data-toggle="search" class="nav-link nav-link-lg d-sm-none"><i class="fas fa-search"></i></a></li>
          </ul>
          <div class="search-element">
            <input class="form-control" type="search" placeholder="Search" aria-label="Search" data-width="250">
            <button class="btn" type="submit"><i class="fas fa-search"></i></button>
            <div class="search-backdrop"></div>
          </div>
        </form>
        <ul class="navbar-nav navbar-right">


          <li class="dropdown"><a href="#" data-toggle="dropdown" class="nav-link dropdown-toggle nav-link-lg nav-link-user">
            <img alt="image" src="assets/img/avatar/avatar-1.png" class="rounded-circle mr-1">
            <div class="d-sm-none d-lg-inline-block">Hi, {{ auth()->user()->name }}</div></a>
            <div class="dropdown-menu dropdown-menu-right">
              <a href="/ubah-password" class="dropdown-item has-icon">
                <i class="fa fa-sharp fa-solid fa-lock"></i> Ubah Password
              </a>
              <div class="dropdown-divider"></div>
                  <a class="dropdown-item" href="{{ route('logout') }}"
                    onclick="event.preventDefault();
                                Swal.fire({
                                    title: 'Konfirmasi Keluar',
                                    text: 'Apakah Anda yakin ingin keluar?',
                                    icon: 'warning',
                                    showCancelButton: true,
                                    confirmButtonColor: '#3085d6',
                                    cancelButtonColor: '#d33',
                                    confirmButtonText: 'Ya, Keluar!'
                                  }).then((result) => {
                                    if (result.isConfirmed) {
                                      document.getElementById('logout-form').submit();
                                    }
                                  });">
                               <i class="fas fa-sign-out-alt"></i> {{ __('Keluar') }}
                              </a>
                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                        @csrf
                    </form>
                  </a>
            </div>
          </li>
        </ul>
      </nav>
      <div class="main-sidebar sidebar-style-2">
        <aside id="sidebar-wrapper">

          <div class="sidebar-brand">
            <a href="/">INVENTORY GUDANG</a>
          </div>

          <ul class="sidebar-menu">
            <!-- DASHBOARD (ALL ROLES) -->
            <li class="sidebar-item {{ Request::is('/') || Request::is('dashboard') ? 'active' : '' }}">
              <a class="nav-link" href="/">
                <i class="fas fa-fire"></i> <span class="align-middle">Dashboard</span>
              </a>
            </li>

            <!-- DATA MASTER (SUPERADMIN, ADMIN GUDANG, KEPALA GUDANG) -->
            @if (auth()->user()->isSuperAdmin() || auth()->user()->hasRole(['admin gudang', 'kepala gudang']))
              <li class="menu-header">DATA MASTER</li>
              <li class="dropdown {{ Request::is('barang*') || Request::is('jenis-barang*') || Request::is('satuan-barang*') ? 'active' : '' }}">
                <a href="#" class="nav-link has-dropdown" data-toggle="dropdown"><i class="fas fa-cubes"></i><span>Data Barang</span></a>
                <ul class="dropdown-menu">
                  <li class="{{ Request::is('barang*') ? 'active' : '' }}"><a class="nav-link" href="/barang"><i class="fa fa-circle fa-xs"></i> Nama Barang</a></li>
                  <li class="{{ Request::is('jenis-barang*') ? 'active' : '' }}"><a class="nav-link" href="/jenis-barang"><i class="fa fa-circle fa-xs"></i> Jenis</a></li>
                  <li class="{{ Request::is('satuan-barang*') ? 'active' : '' }}"><a class="nav-link" href="/satuan-barang"><i class="fa fa-circle fa-xs"></i> Satuan</a></li>
                </ul>
              </li>
              <li class="dropdown {{ Request::is('merk*') || Request::is('watt*') ? 'active' : '' }}">
                <a href="#" class="nav-link has-dropdown" data-toggle="dropdown"><i class="fas fa-bolt"></i><span>Spesifikasi PJU</span></a>
                <ul class="dropdown-menu">
                  <li class="{{ Request::is('merk*') ? 'active' : '' }}"><a class="nav-link" href="/merk"><i class="fa fa-circle fa-xs"></i> Merk</a></li>
                  <li class="{{ Request::is('watt*') ? 'active' : '' }}"><a class="nav-link" href="/watt"><i class="fa fa-circle fa-xs"></i> Watt Daya</a></li>
                </ul>
              </li>
              <li class="dropdown {{ Request::is('tim*') || Request::is('lokasi-pju*') || Request::is('kecamatan*') || Request::is('kelurahan*') ? 'active' : '' }}">
                <a href="#" class="nav-link has-dropdown" data-toggle="dropdown"><i class="fas fa-map-marked-alt"></i><span>Wilayah & Tim</span></a>
                <ul class="dropdown-menu">
                  <li class="{{ Request::is('tim*') ? 'active' : '' }}"><a class="nav-link" href="/tim"><i class="fa fa-circle fa-xs"></i> Tim Kerja</a></li>
                  <li class="{{ Request::is('lokasi-pju*') ? 'active' : '' }}"><a class="nav-link" href="/lokasi-pju"><i class="fa fa-circle fa-xs"></i> Titik Lokasi PJU</a></li>
                  <li class="{{ Request::is('kecamatan*') ? 'active' : '' }}"><a class="nav-link" href="/kecamatan"><i class="fa fa-circle fa-xs"></i> Kecamatan</a></li>
                  <li class="{{ Request::is('kelurahan*') ? 'active' : '' }}"><a class="nav-link" href="/kelurahan"><i class="fa fa-circle fa-xs"></i> Kelurahan</a></li>
                </ul>
              </li>
              <li class="dropdown {{ Request::is('supplier*') || Request::is('customer*') ? 'active' : '' }}">
                <a href="#" class="nav-link has-dropdown" data-toggle="dropdown"><i class="fas fa-building"></i><span>Mitra Perusahaan</span></a>
                <ul class="dropdown-menu">
                  <li class="{{ Request::is('supplier*') ? 'active' : '' }}"><a class="nav-link" href="/supplier"><i class="fa fa-circle fa-xs"></i> Supplier Vendor</a></li>
                  <li class="{{ Request::is('customer*') ? 'active' : '' }}"><a class="nav-link" href="/customer"><i class="fa fa-circle fa-xs"></i> Customer / Unit</a></li>
                </ul>
              </li>
            @endif

            <!-- INVENTORI & TRANSAKSI (SUPERADMIN, ADMIN GUDANG, KEPALA GUDANG) -->
            @if (auth()->user()->isSuperAdmin() || auth()->user()->hasRole(['admin gudang', 'kepala gudang']))
              <li class="menu-header">TRANSAKSI & GUDANG</li>
              <li class="{{ Request::is('barang-masuk*') ? 'active' : '' }}">
                <a class="nav-link" href="/barang-masuk"><i class="fas fa-arrow-right text-success"></i><span>Barang Masuk</span></a>
              </li>
              <li class="{{ Request::is('barang-keluar*') ? 'active' : '' }}">
                <a class="nav-link" href="/barang-keluar"><i class="fas fa-arrow-left text-danger"></i><span>Barang Keluar</span></a>
              </li>
              <li class="{{ Request::is('stock-opname*') ? 'active' : '' }}">
                <a class="nav-link" href="/stock-opname"><i class="fas fa-clipboard-check text-warning"></i><span>Stock Opname</span></a>
              </li>
              <li class="{{ Request::is('stock-mutasi*') ? 'active' : '' }}">
                <a class="nav-link" href="/stock-mutasi"><i class="fas fa-exchange-alt text-info"></i><span>Mutasi Stok</span></a>
              </li>
            @endif

            <!-- OPERASIONAL PJU (SUPERADMIN, ADMIN GUDANG, KEPALA GUDANG, TEKNISI) -->
            @if (auth()->user()->isSuperAdmin() || auth()->user()->hasRole(['admin gudang', 'kepala gudang', 'teknisi']))
              <li class="menu-header">OPERASIONAL PJU</li>
              <li class="{{ Request::is('pju-asset*') ? 'active' : '' }}">
                <a class="nav-link" href="/pju-asset"><i class="fas fa-lightbulb text-warning"></i><span>Aset PJU</span></a>
              </li>
              <li class="{{ Request::is('pemasangan-pju*') ? 'active' : '' }}">
                <a class="nav-link" href="/pemasangan-pju"><i class="fas fa-tools text-primary"></i><span>Pemasangan</span></a>
              </li>
              <li class="{{ Request::is('pencopotan-pju*') ? 'active' : '' }}">
                <a class="nav-link" href="/pencopotan-pju"><i class="fas fa-unlink text-danger"></i><span>Pencopotan</span></a>
              </li>
              <li class="{{ Request::is('maintenance-pju*') ? 'active' : '' }}">
                <a class="nav-link" href="/maintenance-pju"><i class="fas fa-wrench text-success"></i><span>Maintenance & WO</span></a>
              </li>
              @if (auth()->user()->hasRole('teknisi'))
                <li class="{{ Request::is('lokasi-pju*') ? 'active' : '' }}">
                  <a class="nav-link" href="/lokasi-pju"><i class="fas fa-map-marker-alt"></i><span>Titik Lokasi</span></a>
                </li>
              @endif
            @endif

            <!-- GARANSI & RETUR VENDOR (SUPERADMIN, ADMIN GUDANG, KEPALA GUDANG, VIEWER) -->
            @if (auth()->user()->isSuperAdmin() || auth()->user()->hasRole(['admin gudang', 'kepala gudang', 'viewer']))
              <li class="menu-header">GARANSI & RETUR</li>
              <li class="{{ Request::is('garansi-pju*') ? 'active' : '' }}">
                <a class="nav-link" href="/garansi-pju"><i class="fas fa-shield-alt text-info"></i><span>Garansi Aset</span></a>
              </li>
              @if (!auth()->user()->hasRole('viewer'))
                <li class="{{ Request::is('retur-vendor*') ? 'active' : '' }}">
                  <a class="nav-link" href="/retur-vendor"><i class="fas fa-undo-alt text-danger"></i><span>Retur Vendor</span></a>
                </li>
              @endif
            @endif

            <!-- VIEWER TRANSPARANSI MONITORING (VIEWER ROLE) -->
            @if (auth()->user()->hasRole('viewer'))
              <li class="menu-header">TRANSPARANSI DATA</li>
              <li class="{{ Request::is('barang*') ? 'active' : '' }}">
                <a class="nav-link" href="/barang"><i class="fas fa-cubes"></i><span>Data Barang & Stok</span></a>
              </li>
              <li class="{{ Request::is('pju-asset*') ? 'active' : '' }}">
                <a class="nav-link" href="/pju-asset"><i class="fas fa-lightbulb"></i><span>Aset PJU</span></a>
              </li>
              <li class="{{ Request::is('maintenance-pju*') ? 'active' : '' }}">
                <a class="nav-link" href="/maintenance-pju"><i class="fas fa-wrench"></i><span>Riwayat Maintenance</span></a>
              </li>
            @endif

            <!-- LAPORAN & PELAPORAN (SUPERADMIN, ADMIN GUDANG, KEPALA GUDANG, VIEWER) -->
            @if (auth()->user()->isSuperAdmin() || auth()->user()->hasRole(['admin gudang', 'kepala gudang', 'viewer']))
              <li class="menu-header">LAPORAN</li>
              <li class="{{ Request::is('laporan-stok*') ? 'active' : '' }}">
                <a class="nav-link" href="/laporan-stok"><i class="fas fa-file-alt"></i><span>Laporan Stok</span></a>
              </li>
              <li class="{{ Request::is('laporan-barang-masuk*') ? 'active' : '' }}">
                <a class="nav-link" href="/laporan-barang-masuk"><i class="fas fa-file-import"></i><span>Laporan Masuk</span></a>
              </li>
              <li class="{{ Request::is('laporan-barang-keluar*') ? 'active' : '' }}">
                <a class="nav-link" href="/laporan-barang-keluar"><i class="fas fa-file-export"></i><span>Laporan Keluar</span></a>
              </li>
              <li class="{{ Request::is('laporan/generate*') ? 'active' : '' }}">
                <a class="nav-link" href="/laporan/generate"><i class="fas fa-chart-line"></i><span>Laporan Terpadu</span></a>
              </li>
            @endif

            <!-- SISTEM & USER MANAGEMENT (SUPERADMIN, KEPALA GUDANG) -->
            @if (auth()->user()->isSuperAdmin() || auth()->user()->hasRole(['kepala gudang']))
              <li class="menu-header">MANAJEMEN SISTEM</li>
              @if (auth()->user()->isSuperAdmin())
                <li class="{{ Request::is('data-pengguna*') ? 'active' : '' }}">
                  <a class="nav-link" href="/data-pengguna"><i class="fas fa-users"></i><span>Data Pengguna</span></a>
                </li>
                <li class="{{ Request::is('hak-akses*') ? 'active' : '' }}">
                  <a class="nav-link" href="/hak-akses"><i class="fas fa-user-lock"></i><span>Hak Akses / Role</span></a>
                </li>
              @endif
              <li class="{{ Request::is('aktivitas-user*') ? 'active' : '' }}">
                <a class="nav-link" href="/aktivitas-user"><i class="fas fa-history"></i><span>Aktivitas User</span></a>
              </li>
              <li class="{{ Request::is('import*') ? 'active' : '' }}">
                <a class="nav-link" href="/import"><i class="fas fa-file-excel text-success"></i><span>Migrasi & Import Excel</span></a>
              </li>
            @endif
          </ul>

        </aside>
      </div>

      <!-- Main Content -->
      <div class="main-content">
        <section class="section">

            @yield('content')
          <div class="section-body">
          </div>
        </section>
      </div>
<footer class="main-footer">
    <div class="footer-left">
        Inventaris-Gudang &copy; {{ date('Y') }}
    </div>
    <div class="footer-right">

    </div>
</footer>

    </div>
  </div>



  <!-- General JS Scripts -->
  <script src="assets/modules/jquery.min.js"></script>
  <script src="assets/modules/popper.js"></script>
  <script src="assets/modules/tooltip.js"></script>
  <script src="assets/modules/bootstrap/js/bootstrap.min.js"></script>
  <script src="assets/modules/nicescroll/jquery.nicescroll.min.js"></script>
  <script src="assets/modules/moment.min.js"></script>
  <script src="assets/js/stisla.js"></script>

  <!-- JS Libraies -->

  <!-- Select2 Jquery -->
  <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

  <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js" integrity="sha256-lSjKY0/srUM9BE3dPm+c4fBo1dky2v27Gdjm2uoZaL0=" crossorigin="anonymous"></script>

  <!-- Page Specific JS File -->

  <!-- Template JS File -->
  <script src="assets/js/scripts.js"></script>
  <script src="assets/js/custom.js"></script>

  <!-- Datatables Jquery -->
  <script type="text/javascript" src="//cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>

  <!-- Sweet Alert -->
  @include('sweetalert::alert')
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>

  <!-- Day Js Format -->
  <script src="https://cdn.jsdelivr.net/npm/dayjs@1/dayjs.min.js"></script>


  @stack('scripts')


  <script>
    $(document).ready(function() {
      var currentPath = window.location.pathname;

      $('.nav-link a[href="' + currentPath + '"]').addClass('active');
    });
  </script>

</body>
</html>

<style>
    /* === GLOBAL THEME === */
body {
  background: linear-gradient(180deg, #f7f9ff 0%, #f1f4ff 100%);
  color: #2c2c2c;
  font-family: 'Poppins', sans-serif;
  font-weight: 400;
  letter-spacing: 0.2px;
}

/* === NAVBAR === */
.navbar-bg {
  background: linear-gradient(90deg, #7686ff 0%, #9ba8ff 100%);
  box-shadow: 0 2px 10px rgba(110, 130, 255, 0.2);
}

.navbar {
  background: #ffffff;
  border-bottom: 1px solid #e6e9f3;
  box-shadow: 0 3px 12px rgba(0, 0, 0, 0.04);
  height: 70px;
}

.navbar .nav-link {
  color: #333 !important;
  font-weight: 500;
}

.navbar .nav-link:hover {
  color: #2e38ff !important;
}

.navbar .dropdown-menu {
  border-radius: 10px;
  box-shadow: 0 6px 22px rgba(0, 0, 0, 0.08);
  border: none;
}

/* === SIDEBAR === */
.main-sidebar {
  background: #fdfdff;
  border-right: 1px solid #e5e8f3;
  box-shadow: 2px 0 10px rgba(0, 0, 0, 0.03);
}

.sidebar-brand a {
  font-weight: 700;
  color: #1d1f2c;
  letter-spacing: 0.5px;
}

.sidebar-menu li a {
  color: #555;
  border-radius: 10px;
  padding: 10px 16px;
  margin-bottom: 4px;
  transition: all 0.25s ease;
  font-weight: 500;
}

.sidebar-menu li a.active,
.sidebar-menu li a:hover {
  background: linear-gradient(90deg, rgba(130,138,255,0.2), rgba(210,216,255,0.4));
  color: #2b2b2b !important;
  box-shadow: inset 2px 0 0 #6e7eff;
  transform: translateX(2px);
}

.sidebar-menu .menu-header {
  color: #8c90a8;
  font-size: 0.75rem;
  letter-spacing: 0.6px;
  margin-top: 15px;
}

/* === MAIN CONTENT AREA === */
.main-content {
  background: #f8faff;
  padding-top: 85px;
}

/* === SECTION === */
.section {
  background: #ffffff;
  border-radius: 18px;
  padding: 30px;
  box-shadow: 0 8px 30px rgba(0, 0, 0, 0.05);
}

/* === DASHBOARD HEADER === */
.section .section-header {
  background: linear-gradient(90deg, #f5f7ff 0%, #f0f4ff 100%);
  border-radius: 12px;
  padding: 16px 20px;
  margin-bottom: 25px;
  box-shadow: 0 3px 10px rgba(0, 0, 0, 0.03);
}
.section .section-header h1 {
  color: #1f1f33;
  font-weight: 700;
}

/* === DASHBOARD CARDS === */
.card {
  border: none;
  border-radius: 16px;
  background: #ffffff;
  box-shadow: 0 4px 14px rgba(0, 0, 0, 0.05);
  transition: all 0.3s ease;
}

.card:hover {
  transform: translateY(-3px);
  box-shadow: 0 10px 25px rgba(110, 130, 255, 0.12);
}

.card .card-header {
  border-bottom: none;
  background: transparent;
  color: #4a4a4a;
  font-weight: 600;
  font-size: 1rem;
}

/* === STATISTIC CARDS === */
.card-statistic-1 {
  display: flex;
  align-items: center;
  background: #ffffff;
  border-radius: 14px;
  box-shadow: 0 4px 14px rgba(0, 0, 0, 0.05);
  transition: all 0.3s ease;
}

.card-statistic-1:hover {
  transform: translateY(-3px);
  box-shadow: 0 8px 20px rgba(120, 130, 255, 0.15);
}

.card-statistic-1 .card-icon {
  width: 70px;
  height: 70px;
  border-radius: 14px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 26px;
  margin: 16px;
  color: #fff;
}

.card-statistic-1 .card-wrap {
  padding: 10px 20px;
}

.card-statistic-1 .card-header h4 {
  color: #7a7a7a;
  font-weight: 500;
}

.card-statistic-1 .card-body {
  color: #1f1f1f;
  font-weight: 700;
  font-size: 1.8rem;
}

.bg-primary {
  background: linear-gradient(135deg, #6e73ff, #8c9aff) !important;
}
.bg-danger {
  background: linear-gradient(135deg, #ff5f6d, #ff7a85) !important;
}
.bg-warning {
  background: linear-gradient(135deg, #ffca58, #ffb347) !important;
}
.bg-success {
  background: linear-gradient(135deg, #54e08e, #33d4a0) !important;
}

/* === TABLE === */
.table {
  border-radius: 10px;
  overflow: hidden;
  color: #333;
}
.table thead {
  background: #f1f3ff;
  color: #444;
  font-weight: 600;
}
.table tbody tr:hover {
  background: rgba(150, 160, 255, 0.08);
}

/* === BADGES === */
.badge-warning {
  background: linear-gradient(90deg, #ffcd4f, #f7b74a);
  color: #3a3a3a;
  border-radius: 8px;
  font-weight: 600;
  padding: 6px 10px;
}

/* === FOOTER === */
.main-footer {
  background: #f8faff;
  border-top: 1px solid #e5e8f3;
  color: #666;
  font-size: 0.9rem;
  padding: 20px;
  text-align: center;
}

/* === SCROLLBAR === */
::-webkit-scrollbar {
  width: 8px;
}
::-webkit-scrollbar-thumb {
  background: linear-gradient(180deg, #b8bfff, #a6adff);
  border-radius: 10px;
}
::-webkit-scrollbar-track {
  background: #f0f3ff;
}

/* === RESPONSIVE === */
@media (max-width: 768px) {
  .card-statistic-1 .card-icon {
    width: 55px;
    height: 55px;
    font-size: 22px;
  }
  .card-statistic-1 .card-body {
    font-size: 1.4rem;
  }
}

</style>

