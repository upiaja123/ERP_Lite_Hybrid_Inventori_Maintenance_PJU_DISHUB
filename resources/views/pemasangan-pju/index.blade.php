@extends('layouts.app')

@section('content')
    <div class="section-header">
        <h1>Pemasangan PJU</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item active"><a href="/dashboard">Dashboard</a></div>
            <div class="breadcrumb-item">Operasional PJU</div>
            <div class="breadcrumb-item">Pemasangan</div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <div class="card card-primary">
                <div class="card-header">
                    <h4>Riwayat Pemasangan & Instalasi PJU di Lapangan</h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="table_id" class="table table-striped table-bordered">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>No Bukti Pasang</th>
                                    <th>Aset PJU</th>
                                    <th>Lokasi / Titik Tiang</th>
                                    <th>Tanggal Pasang</th>
                                    <th>Teknisi Pelaksana</th>
                                    <th>Kondisi Pasang</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($pemasangans as $index => $pasang)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td><span class="badge badge-info">{{ $pasang->no_pemasangan }}</span></td>
                                        <td><strong>{{ $pasang->asset->nama_pju ?? '-' }}</strong><br><small class="text-muted">{{ $pasang->asset->kode_pju ?? '-' }}</small></td>
                                        <td>{{ $pasang->lokasi->nama_lokasi ?? '-' }}</td>
                                        <td>{{ $pasang->tanggal_pasang }}</td>
                                        <td>{{ $pasang->teknisi->name ?? '-' }}</td>
                                        <td><span class="badge badge-light">{{ $pasang->kondisi_pasang }}</span></td>
                                        <td>
                                            @if($pasang->status === 'TERPASANG')
                                                <span class="badge badge-success">TERPASANG</span>
                                            @else
                                                <span class="badge badge-warning">{{ $pasang->status }}</span>
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
