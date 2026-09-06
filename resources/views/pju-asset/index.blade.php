@extends('layouts.app')

@section('content')
    <div class="section-header">
        <h1>Data Aset PJU</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item active"><a href="/dashboard">Dashboard</a></div>
            <div class="breadcrumb-item">Operasional PJU</div>
            <div class="breadcrumb-item">Aset PJU</div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <div class="card card-primary">
                <div class="card-header">
                    <h4>Daftar Unit Aset PJU Individual</h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="table_id" class="table table-striped table-bordered">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Kode PJU</th>
                                    <th>Nama Aset</th>
                                    <th>Barang Referensi</th>
                                    <th>Lokasi / Titik</th>
                                    <th>Status Aset</th>
                                    <th>No Seri</th>
                                    <th>Tahun</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($assets as $index => $asset)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td><span class="badge badge-info">{{ $asset->kode_pju }}</span></td>
                                        <td><strong>{{ $asset->nama_pju }}</strong></td>
                                        <td>{{ $asset->barang->nama_barang ?? '-' }}</td>
                                        <td>{{ $asset->lokasi->nama_lokasi ?? 'Di Gudang' }}</td>
                                        <td>
                                            @if($asset->status_aset === 'TERPASANG')
                                                <span class="badge badge-success">TERPASANG</span>
                                            @elseif($asset->status_aset === 'GUDANG')
                                                <span class="badge badge-primary">GUDANG</span>
                                            @elseif($asset->status_aset === 'RUSAK')
                                                <span class="badge badge-danger">RUSAK</span>
                                            @elseif($asset->status_aset === 'MAINTENANCE')
                                                <span class="badge badge-warning">MAINTENANCE</span>
                                            @else
                                                <span class="badge badge-secondary">{{ $asset->status_aset }}</span>
                                            @endif
                                        </td>
                                        <td>{{ $asset->no_seri ?? '-' }}</td>
                                        <td>{{ $asset->tahun_pengadaan ?? '-' }}</td>
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
