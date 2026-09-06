@extends('layouts.app')

@section('content')
    <div class="section-header">
        <h1>Migrasi & Impor Data Excel / CSV</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item active"><a href="/dashboard">Dashboard</a></div>
            <div class="breadcrumb-item">Integrasi</div>
            <div class="breadcrumb-item">Impor Data</div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card card-primary">
                <div class="card-header">
                    <h4>Upload File Migrasi (CSV Staging)</h4>
                </div>
                <div class="card-body">
                    <p class="text-muted">
                        Format file yang didukung: <strong>CSV</strong> (Maksimal 5MB).
                        Sistem akan menjalankan validasi bertahap:
                        <code>Staging &rarr; Normalisasi (Trim/Uppercase) &rarr; Validasi Integrity &rarr; Ledger Post</code>.
                    </p>

                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible show fade">
                            <div class="alert-body">
                                <button class="close" data-dismiss="alert"><span>&times;</span></button>
                                {{ session('success') }}
                            </div>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible show fade">
                            <div class="alert-body">
                                <button class="close" data-dismiss="alert"><span>&times;</span></button>
                                {{ session('error') }}
                            </div>
                        </div>
                    @endif

                    <form action="{{ route('import.preview') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="form-group">
                            <label>Pilih Berkas CSV Data Lampu / Persediaan</label>
                            <input type="file" name="file_import" class="form-control" required accept=".csv,.txt">
                            <small class="form-text text-muted">
                                Kolom minimal: <code>nama_barang, kode_barang, jenis, satuan, stok_awal, barcode, merk, watt</code>
                            </small>
                        </div>
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-search"></i> Upload & Preview Staging
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card card-info">
                <div class="card-header">
                    <h4>Pedoman Migrasi Data</h4>
                </div>
                <div class="card-body">
                    <h6>Sumber Excel Valid:</h6>
                    <ul>
                        <li><code>form lampu panasonic 30 Gudang 6.xlsx</code></li>
                        <li><code>Data Copotan Keseluruhan.xlsx</code></li>
                    </ul>
                    <hr>
                    <p class="text-small text-muted">
                        Semua data yang diimpor akan otomatis menghasilkan mutasi saldo di <strong>StockLedger</strong> dan memvalidasi keunikan barcode.
                    </p>
                </div>
            </div>
        </div>
    </div>
@endsection
