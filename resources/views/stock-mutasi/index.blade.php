@extends('layouts.app')

@section('content')
    <div class="section-header">
        <h1>Mutasi Stok</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item active"><a href="/dashboard">Dashboard</a></div>
            <div class="breadcrumb-item">Transaksi & Gudang</div>
            <div class="breadcrumb-item">Mutasi Stok</div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <div class="card card-primary">
                <div class="card-header">
                    <h4>Pencatatan Perpindahan & Mutasi Stok Barang</h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="table_id" class="table table-striped table-bordered">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Kode Mutasi</th>
                                    <th>Barang</th>
                                    <th>Gudang Asal</th>
                                    <th>Tujuan / Lokasi</th>
                                    <th>Jumlah</th>
                                    <th>Tanggal</th>
                                    <th>Petugas</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($mutasis as $index => $m)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td><span class="badge badge-info">{{ $m->kode_mutasi }}</span></td>
                                        <td><strong>{{ $m->barang->nama_barang ?? '-' }}</strong></td>
                                        <td>{{ $m->gudang_asal ?? 'Gudang Utama' }}</td>
                                        <td>{{ $m->tujuanLokasi->nama_lokasi ?? $m->gudang_tujuan ?? '-' }}</td>
                                        <td><span class="badge badge-primary">{{ $m->jumlah }}</span></td>
                                        <td>{{ $m->tanggal_mutasi }}</td>
                                        <td>{{ $m->creator->name ?? '-' }}</td>
                                    </tr>
                                @empty
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            $('#table_id').DataTable({
                paging: true,
                responsive: true
            });
        });
    </script>
@endsection
