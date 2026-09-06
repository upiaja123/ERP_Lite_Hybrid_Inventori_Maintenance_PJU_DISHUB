@extends('layouts.app')

@section('content')
    <div class="section-header">
        <h1>Master Kecamatan</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item active"><a href="/dashboard">Dashboard</a></div>
            <div class="breadcrumb-item">Wilayah & Tim</div>
            <div class="breadcrumb-item">Kecamatan</div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <div class="card card-primary">
                <div class="card-header">
                    <h4>Daftar Wilayah Kecamatan</h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="table_id" class="table table-striped table-bordered">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Kode Kecamatan</th>
                                    <th>Nama Kecamatan</th>
                                    <th>Jumlah Kelurahan</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($kecamatans as $index => $k)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td><span class="badge badge-info">{{ $k->kode_kecamatan ?? '-' }}</span></td>
                                        <td><strong>{{ $k->nama_kecamatan }}</strong></td>
                                        <td>{{ $k->kelurahans->count() }} Kelurahan</td>
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
