@extends('layouts.app')

@section('title', 'Laporan Absensi')

@section('styles')
<!-- Select2 -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap4-theme@1.0.0/dist/select2-bootstrap4.min.css">
@endsection

@section('content')
<div class="container-fluid">
    <!-- Content Header -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Laporan Absensi</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Laporan Absensi</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Filter Laporan</h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.attendance.report.export') }}?{{ http_build_query(request()->all()) }}" class="btn btn-success btn-sm">
                            <i class="fas fa-file-excel mr-1"></i> Ekspor
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.attendance.report') }}" method="GET">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="user_id">Pengguna</label>
                                    <select class="form-control select2" id="user_id" name="user_id" style="width: 100%;">
                                        <option value="">Semua Pengguna</option>
                                        @foreach($users as $user)
                                            <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                                                {{ $user->name }} ({{ $user->role->name }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="start_date">Tanggal Mulai</label>
                                    <div class="input-group date">
                                        <input type="date" class="form-control" id="start_date" name="start_date" value="{{ request('start_date') }}">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="end_date">Tanggal Selesai</label>
                                    <div class="input-group date">
                                        <input type="date" class="form-control" id="end_date" name="end_date" value="{{ request('end_date') }}">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12">
                                <div class="form-group">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-filter mr-1"></i> Filter
                                    </button>
                                    <a href="{{ route('admin.attendance.report') }}" class="btn btn-default">
                                        <i class="fas fa-sync mr-1"></i> Reset
                                    </a>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Data Absensi</h3>
                </div>
                <div class="card-body table-responsive p-0">
                    <table class="table table-hover text-nowrap">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Tanggal</th>
                                <th>Nama</th>
                                <th>Peran</th>
                                <th>Jam Masuk</th>
                                <th>Jam Pulang</th>
                                <th>Lokasi Masuk</th>
                                <th>Lokasi Pulang</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($attendances as $index => $attendance)
                                <tr>
                                    <td>{{ $index + $attendances->firstItem() }}</td>
                                    <td>{{ date('d/m/Y', strtotime($attendance->attendance_date)) }}</td>
                                    <td>{{ $attendance->user->name }}</td>
                                    <td>{{ $attendance->user->role->name }}</td>
                                    <td>{{ date('H:i', strtotime($attendance->check_in_time)) }}</td>
                                    <td>{{ $attendance->check_out_time ? date('H:i', strtotime($attendance->check_out_time)) : '-' }}</td>
                                    <td>
                                        <small>
                                            Lat: {{ $attendance->check_in_latitude }}<br>
                                            Long: {{ $attendance->check_in_longitude }}
                                        </small>
                                    </td>
                                    <td>
                                        @if($attendance->check_out_time)
                                            <small>
                                                Lat: {{ $attendance->check_out_latitude }}<br>
                                                Long: {{ $attendance->check_out_longitude }}
                                            </small>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>
                                        @if($attendance->check_out_time)
                                            <span class="badge bg-success">Lengkap</span>
                                        @else
                                            <span class="badge bg-warning">Belum Absen Pulang</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center">Tidak ada data absensi ditemukan.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="card-footer clearfix">
                    <div class="float-right">
                        {{ $attendances->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<!-- Select2 -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(function () {
        //Initialize Select2 Elements
        $('.select2').select2({
            theme: 'bootstrap4',
            placeholder: 'Pilih pengguna',
            allowClear: true
        });
    });
</script>
@endsection 