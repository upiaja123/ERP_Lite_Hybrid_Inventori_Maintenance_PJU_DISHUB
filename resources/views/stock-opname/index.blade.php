@extends('layouts.app')

@section('content')
    <div class="section-header">
        <h1>Stock Opname Fisik</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item active"><a href="/dashboard">Dashboard</a></div>
            <div class="breadcrumb-item">Transaksi & Gudang</div>
            <div class="breadcrumb-item">Stock Opname</div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <div class="card card-primary">
                <div class="card-header">
                    <h4>Pencatatan Fisik & Rekonsiliasi Selisih Stok</h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="table_id" class="table table-striped table-bordered">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Kode Opname</th>
                                    <th>Barang</th>
                                    <th>Stok Sistem</th>
                                    <th>Stok Fisik</th>
                                    <th>Selisih</th>
                                    <th>Petugas</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($opnames as $index => $opname)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td><span class="badge badge-info">{{ $opname->kode_opname }}</span></td>
                                        <td><strong>{{ $opname->barang->nama_barang ?? '-' }}</strong></td>
                                        <td>{{ $opname->stok_sistem }}</td>
                                        <td>{{ $opname->stok_fisik }}</td>
                                        <td>
                                            @if($opname->selisih > 0)
                                                <span class="badge badge-success">+{{ $opname->selisih }}</span>
                                            @elseif($opname->selisih < 0)
                                                <span class="badge badge-danger">{{ $opname->selisih }}</span>
                                            @else
                                                <span class="badge badge-light">0</span>
                                            @endif
                                        </td>
                                        <td>{{ $opname->petugas->name ?? '-' }}</td>
                                        <td>
                                            @if($opname->status === 'APPROVED')
                                                <span class="badge badge-success">APPROVED</span>
                                            @elseif($opname->status === 'SUBMITTED')
                                                <span class="badge badge-warning">MENUNGGU APPROVAL</span>
                                            @else
                                                <span class="badge badge-secondary">{{ $opname->status }}</span>
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
