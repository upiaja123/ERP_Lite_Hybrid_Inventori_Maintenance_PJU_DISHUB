@extends('layouts.app')

@section('content')
    <div class="section-header">
        <h1>Pencopotan PJU</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item active"><a href="/dashboard">Dashboard</a></div>
            <div class="breadcrumb-item">Operasional PJU</div>
            <div class="breadcrumb-item">Pencopotan</div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <div class="card card-primary">
                <div class="card-header">
                    <h4>Riwayat Pencopotan Lampu / Komponen PJU</h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="table_id" class="table table-striped table-bordered">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>No Bukti Copot</th>
                                    <th>Aset PJU</th>
                                    <th>Lokasi Asal</th>
                                    <th>Tanggal Copot</th>
                                    <th>Teknisi</th>
                                    <th>Alasan Copot</th>
                                    <th>Kondisi Barang</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($pencopotans as $index => $copot)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td><span class="badge badge-info">{{ $copot->no_pencopotan }}</span></td>
                                        <td><strong>{{ $copot->asset->nama_pju ?? '-' }}</strong><br><small class="text-muted">{{ $copot->asset->kode_pju ?? '-' }}</small></td>
                                        <td>{{ $copot->lokasi->nama_lokasi ?? '-' }}</td>
                                        <td>{{ $copot->tanggal_copot }}</td>
                                        <td>{{ $copot->teknisi->name ?? '-' }}</td>
                                        <td><span class="badge badge-danger">{{ $copot->alasan }}</span></td>
                                        <td>{{ $copot->kondisi_barang }}</td>
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
