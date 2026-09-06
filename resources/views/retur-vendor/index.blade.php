@extends('layouts.app')

@section('content')
    <div class="section-header">
        <h1>Retur ke Vendor</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item active"><a href="/dashboard">Dashboard</a></div>
            <div class="breadcrumb-item">Operasional PJU</div>
            <div class="breadcrumb-item">Retur Vendor</div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <div class="card card-primary">
                <div class="card-header">
                    <h4>Tracking Pengembalian / Klaim Garansi Aset Rusak ke Supplier</h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="table_id" class="table table-striped table-bordered">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>No Retur</th>
                                    <th>Aset PJU</th>
                                    <th>Supplier</th>
                                    <th>Tanggal Retur</th>
                                    <th>Alasan Retur</th>
                                    <th>Tanggal Kembali</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($returs as $index => $r)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td><span class="badge badge-info">{{ $r->no_retur }}</span></td>
                                        <td><strong>{{ $r->asset->nama_pju ?? '-' }}</strong><br><small class="text-muted">{{ $r->asset->kode_pju ?? '-' }}</small></td>
                                        <td>{{ $r->supplier->nama_supplier ?? '-' }}</td>
                                        <td>{{ $r->tanggal_retur }}</td>
                                        <td>{{ $r->alasan_retur }}</td>
                                        <td>{{ $r->tanggal_kembali ?? '-' }}</td>
                                        <td>
                                            @if($r->status === 'SELESAI_GANTI')
                                                <span class="badge badge-success">SELESAI (GANTI)</span>
                                            @elseif($r->status === 'PROSES_VENDOR')
                                                <span class="badge badge-warning">PROSES VENDOR</span>
                                            @else
                                                <span class="badge badge-danger">{{ $r->status }}</span>
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
