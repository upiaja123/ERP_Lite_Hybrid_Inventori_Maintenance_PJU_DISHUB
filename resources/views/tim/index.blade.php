@extends('layouts.app')

@section('content')
    <div class="section-header">
        <h1>Tim Kerja Lapangan</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item active"><a href="/dashboard">Dashboard</a></div>
            <div class="breadcrumb-item">Wilayah & Tim</div>
            <div class="breadcrumb-item">Tim Kerja</div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <div class="card card-primary">
                <div class="card-header">
                    <h4>Daftar Tim Lapangan / PJU</h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="table_id" class="table table-striped table-bordered">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Kode Tim</th>
                                    <th>Nama Tim</th>
                                    <th>Penanggung Jawab</th>
                                    <th>Kontak</th>
                                    <th>Wilayah Tugas</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($tims as $index => $t)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td><span class="badge badge-info">{{ $t->kode_tim }}</span></td>
                                        <td><strong>{{ $t->nama_tim }}</strong></td>
                                        <td>{{ $t->penanggung_jawab ?? '-' }}</td>
                                        <td>{{ $t->kontak ?? '-' }}</td>
                                        <td>{{ $t->wilayah_tugas ?? '-' }}</td>
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
