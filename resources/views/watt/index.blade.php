@extends('layouts.app')

@section('content')
    <div class="section-header">
        <h1>Master Watt Daya PJU</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item active"><a href="/dashboard">Dashboard</a></div>
            <div class="breadcrumb-item">Spesifikasi PJU</div>
            <div class="breadcrumb-item">Watt Daya</div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <div class="card card-primary">
                <div class="card-header">
                    <h4>Standar Daya Listrik (Watt)</h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="table_id" class="table table-striped table-bordered">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Nilai Daya</th>
                                    <th>Satuan</th>
                                    <th>Keterangan</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($watts as $index => $w)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td><strong>{{ $w->nilai_watt }}</strong></td>
                                        <td>{{ $w->satuan_daya ?? 'Watt' }}</td>
                                        <td>{{ $w->keterangan ?? '-' }}</td>
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
