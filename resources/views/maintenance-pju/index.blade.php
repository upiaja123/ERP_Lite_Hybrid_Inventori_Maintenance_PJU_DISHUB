@extends('layouts.app')

@section('content')
    <div class="section-header">
        <h1>Maintenance & Work Order</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item active"><a href="/dashboard">Dashboard</a></div>
            <div class="breadcrumb-item">Operasional PJU</div>
            <div class="breadcrumb-item">Maintenance & WO</div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <div class="card card-primary">
                <div class="card-header">
                    <h4>Daftar Work Order Pemeliharaan PJU</h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="table_id" class="table table-striped table-bordered">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>No Work Order</th>
                                    <th>Aset PJU</th>
                                    <th>Jenis Maintenance</th>
                                    <th>Teknisi / Tim</th>
                                    <th>Tindakan Perbaikan</th>
                                    <th>Hasil</th>
                                    <th>Status WO</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($maintenances as $index => $wo)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td><span class="badge badge-info">{{ $wo->no_work_order }}</span></td>
                                        <td><strong>{{ $wo->asset->nama_pju ?? '-' }}</strong><br><small class="text-muted">{{ $wo->asset->kode_pju ?? '-' }}</small></td>
                                        <td><span class="badge badge-light">{{ $wo->jenis_maintenance }}</span></td>
                                        <td>{{ $wo->teknisi->name ?? '-' }}</td>
                                        <td>{{ Str::limit($wo->tindakan_perbaikan, 50) }}</td>
                                        <td>
                                            @if($wo->hasil === 'BERHASIL')
                                                <span class="badge badge-success">BERHASIL</span>
                                            @elseif($wo->hasil === 'BUTUH_RETUR')
                                                <span class="badge badge-danger">BUTUH RETUR</span>
                                            @else
                                                <span class="badge badge-secondary">{{ $wo->hasil ?? 'PENDING' }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($wo->status === 'COMPLETED')
                                                <span class="badge badge-success">COMPLETED</span>
                                            @elseif($wo->status === 'IN_PROGRESS')
                                                <span class="badge badge-warning">IN PROGRESS</span>
                                            @else
                                                <span class="badge badge-info">{{ $wo->status }}</span>
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
