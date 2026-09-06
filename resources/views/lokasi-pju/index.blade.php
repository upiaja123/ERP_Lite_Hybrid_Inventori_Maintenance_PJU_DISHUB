@extends('layouts.app')

@section('content')
    <div class="section-header">
        <h1>Titik Lokasi PJU</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item active"><a href="/dashboard">Dashboard</a></div>
            <div class="breadcrumb-item">Wilayah & Tim</div>
            <div class="breadcrumb-item">Titik Lokasi PJU</div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <div class="card card-primary">
                <div class="card-header">
                    <h4>Master Titik Tiang & Lokasi PJU</h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="table_id" class="table table-striped table-bordered">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Kode Lokasi</th>
                                    <th>Nama Lokasi / Jalan</th>
                                    <th>Kecamatan</th>
                                    <th>Kelurahan</th>
                                    <th>No Tiang</th>
                                    <th>Tim Operasional</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($lokasis as $index => $lokasi)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td><span class="badge badge-info">{{ $lokasi->kode_lokasi }}</span></td>
                                        <td><strong>{{ $lokasi->nama_lokasi }}</strong><br><small class="text-muted">{{ $lokasi->alamat_jalan }}</small></td>
                                        <td>{{ $lokasi->kecamatan->nama_kecamatan ?? '-' }}</td>
                                        <td>{{ $lokasi->kelurahan->nama_kelurahan ?? '-' }}</td>
                                        <td>{{ $lokasi->pole_number ?? '-' }}</td>
                                        <td>{{ $lokasi->tim_operasional ?? '-' }}</td>
                                        <td>
                                            @if($lokasi->status === 'AKTIF')
                                                <span class="badge badge-success">AKTIF</span>
                                            @else
                                                <span class="badge badge-secondary">{{ $lokasi->status }}</span>
                                            @endif
                                        </td>
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
