@extends('layouts.app')

@section('content')
    <div class="section-header">
        <h1>Garansi Vendor PJU</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item active"><a href="/dashboard">Dashboard</a></div>
            <div class="breadcrumb-item">Operasional PJU</div>
            <div class="breadcrumb-item">Garansi Vendor</div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <div class="card card-primary">
                <div class="card-header">
                    <h4>Tracking Status & Masa Berlaku Garansi Vendor</h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="table_id" class="table table-striped table-bordered">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>No Kontrak / Garansi</th>
                                    <th>Aset PJU</th>
                                    <th>Supplier / Vendor</th>
                                    <th>Tanggal Mulai</th>
                                    <th>Tanggal Berakhir</th>
                                    <th>Status Garansi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($garansis as $index => $g)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td><span class="badge badge-info">{{ $g->no_kontrak_garansi }}</span></td>
                                        <td><strong>{{ $g->asset->nama_pju ?? '-' }}</strong><br><small class="text-muted">{{ $g->asset->kode_pju ?? '-' }}</small></td>
                                        <td>{{ $g->supplier->nama_supplier ?? '-' }}</td>
                                        <td>{{ $g->tanggal_mulai }}</td>
                                        <td>{{ $g->tanggal_berakhir }}</td>
                                        <td>
                                            @if($g->status === 'BERLAKU')
                                                <span class="badge badge-success">BERLAKU</span>
                                            @elseif($g->status === 'EXPIRING')
                                                <span class="badge badge-warning">MENDEKATI HABIS</span>
                                            @else
                                                <span class="badge badge-danger">{{ $g->status }}</span>
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
