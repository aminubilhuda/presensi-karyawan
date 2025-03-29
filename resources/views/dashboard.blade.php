@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="container-fluid">
    <h1 class="mb-4">Dashboard</h1>
    
    @if(auth()->user()->isAdmin())
        <!-- Dashboard untuk Admin -->
        <div class="row g-4 mb-4">
            <div class="col-md-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted mb-2">Total Pengguna</h6>
                                <h4 class="mb-0">{{ $totalUsers }}</h4>
                            </div>
                            <div class="bg-primary bg-opacity-10 p-3 rounded">
                                <i class="fas fa-users text-primary"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted mb-2">Absensi Hari Ini</h6>
                                <h4 class="mb-0">{{ $todayAttendances }}</h4>
                            </div>
                            <div class="bg-success bg-opacity-10 p-3 rounded">
                                <i class="fas fa-clipboard-check text-success"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted mb-2">Hadir Hari Ini</h6>
                                <h4 class="mb-0">{{ $presentToday }}</h4>
                            </div>
                            <div class="bg-info bg-opacity-10 p-3 rounded">
                                <i class="fas fa-user-check text-info"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted mb-2">Tidak Hadir Hari Ini</h6>
                                <h4 class="mb-0">{{ $absentToday }}</h4>
                            </div>
                            <div class="bg-warning bg-opacity-10 p-3 rounded">
                                <i class="fas fa-user-times text-warning"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">Aksi Cepat</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <a href="{{ route('admin.users.index') }}" class="btn btn-outline-primary d-block py-2">
                                    <i class="fas fa-users me-2"></i> Kelola Pengguna
                                </a>
                            </div>
                            <div class="col-md-3">
                                <a href="{{ route('admin.attendance.report') }}" class="btn btn-outline-primary d-block py-2">
                                    <i class="fas fa-chart-bar me-2"></i> Laporan Absensi
                                </a>
                            </div>
                            <div class="col-md-3">
                                <a href="{{ route('notifications.broadcast') }}" class="btn btn-outline-primary d-block py-2">
                                    <i class="fas fa-paper-plane me-2"></i> Broadcast WhatsApp
                                </a>
                            </div>
                            <div class="col-md-3">
                                <a href="{{ route('admin.settings.index') }}" class="btn btn-outline-primary d-block py-2">
                                    <i class="fas fa-cogs me-2"></i> Pengaturan
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @else
        <!-- Dashboard untuk Guru dan Staf TU -->
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">Status Absensi Hari Ini</h5>
                    </div>
                    <div class="card-body">
                        @if($todayAttendance)
                            <div class="d-flex align-items-center mb-3">
                                <div class="me-3">
                                    <span class="badge bg-{{ $todayAttendance->status == 'hadir' ? 'success' : ($todayAttendance->status == 'terlambat' ? 'warning' : 'danger') }} p-2">
                                        <i class="fas fa-{{ $todayAttendance->status == 'hadir' ? 'check' : ($todayAttendance->status == 'terlambat' ? 'clock' : 'times') }}"></i>
                                    </span>
                                </div>
                                <div>
                                    <h6 class="mb-0 text-capitalize">{{ $todayAttendance->status }}</h6>
                                    <small class="text-muted">{{ \Carbon\Carbon::parse($todayAttendance->date)->format('d F Y') }}</small>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <small class="text-muted d-block">Jam Masuk</small>
                                        <strong>{{ $todayAttendance->check_in_time ? \Carbon\Carbon::parse($todayAttendance->check_in_time)->format('H:i') : '-' }}</strong>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <small class="text-muted d-block">Jam Pulang</small>
                                        <strong>{{ $todayAttendance->check_out_time ? \Carbon\Carbon::parse($todayAttendance->check_out_time)->format('H:i') : '-' }}</strong>
                                    </div>
                                </div>
                            </div>
                            
                            @if(!$todayAttendance->check_in_time)
                                <a href="{{ route('attendance.check-in') }}" class="btn btn-primary mt-2">
                                    <i class="fas fa-sign-in-alt me-2"></i> Absen Masuk
                                </a>
                            @elseif(!$todayAttendance->check_out_time)
                                <a href="{{ route('attendance.check-out') }}" class="btn btn-primary mt-2">
                                    <i class="fas fa-sign-out-alt me-2"></i> Absen Pulang
                                </a>
                            @endif
                        @else
                            <div class="text-center py-4">
                                <i class="fas fa-clipboard-list fa-3x text-muted mb-3"></i>
                                <p>Anda belum melakukan absensi hari ini</p>
                                <a href="{{ route('attendance.check-in') }}" class="btn btn-primary">
                                    <i class="fas fa-sign-in-alt me-2"></i> Absen Masuk
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">Ringkasan Bulan Ini</h5>
                    </div>
                    <div class="card-body">
                        <div class="row text-center g-3">
                            <div class="col-md-4">
                                <div class="p-3 border rounded bg-light">
                                    <h2 class="text-success">{{ $present }}</h2>
                                    <small class="text-muted">Hadir</small>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="p-3 border rounded bg-light">
                                    <h2 class="text-danger">{{ $absent }}</h2>
                                    <small class="text-muted">Tidak Hadir</small>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="p-3 border rounded bg-light">
                                    <h2 class="text-warning">{{ $late }}</h2>
                                    <small class="text-muted">Terlambat</small>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mt-4 text-center">
                            <a href="{{ route('attendance.history') }}" class="btn btn-outline-primary">
                                <i class="fas fa-history me-2"></i> Lihat Riwayat Lengkap
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection 