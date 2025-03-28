@extends('layouts.app')

@section('title', 'Laporan Absensi')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Laporan Absensi</h1>
        <a href="{{ route('admin.attendance.report.export') }}?{{ http_build_query(request()->all()) }}" class="btn btn-success">
            <i class="fas fa-file-excel me-1"></i> Ekspor
        </a>
    </div>

    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">Filter</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.attendance.report') }}" method="GET">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="user_id" class="form-label">Pengguna</label>
                        <select class="form-select" id="user_id" name="user_id">
                            <option value="">Semua Pengguna</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                                    {{ $user->name }} ({{ $user->role->name }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="start_date" class="form-label">Tanggal Mulai</label>
                        <input type="date" class="form-control" id="start_date" name="start_date" value="{{ request('start_date') }}">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="end_date" class="form-label">Tanggal Selesai</label>
                        <input type="date" class="form-control" id="end_date" name="end_date" value="{{ request('end_date') }}">
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-filter me-1"></i> Filter
                        </button>
                        <a href="{{ route('admin.attendance.report') }}" class="btn btn-secondary">
                            <i class="fas fa-sync me-1"></i> Reset
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">Hasil</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
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
                                        <span class="badge bg-warning text-dark">Belum Absen Pulang</span>
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

            <div class="d-flex justify-content-center mt-3">
                {{ $attendances->links() }}
            </div>
        </div>
    </div>
</div>
@endsection 