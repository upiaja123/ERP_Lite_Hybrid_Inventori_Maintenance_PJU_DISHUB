@extends('layouts.app')

@section('content')
    <div class="section-header">
        <h1>Master Kelurahan</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item active"><a href="/dashboard">Dashboard</a></div>
            <div class="breadcrumb-item">Wilayah & Tim</div>
            <div class="breadcrumb-item">Kelurahan</div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <div class="card card-primary">
                <div class="card-header">
                    <h4>Daftar Wilayah Kelurahan</h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="table_id" class="table table-striped table-bordered">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Kode Kelurahan</th>
                                    <th>Nama Kelurahan</th>
                                    <th>Kecamatan</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($kelurahans as $index => $kel)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td><span class="badge badge-info">{{ $kel->kode_kelurahan ?? '-' }}</span></td>
                                        <td><strong>{{ $kel->nama_kelurahan }}</strong></td>
                                        <td>{{ $kel->kecamatan->nama_kecamatan ?? '-' }}</td>
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
