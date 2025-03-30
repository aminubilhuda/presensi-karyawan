@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Dashboard</h3>
                </div>
                <div class="card-body">
                    @if(auth()->user()->isAdmin())
                        <!-- Dashboard untuk Admin -->
                        <div class="row">
                            <div class="col-lg-3 col-md-6 col-sm-6">
                                <div class="small-box bg-info">
                                    <div class="inner">
                                        <h3>{{ $totalUsers }}</h3>
                                        <p>Total Pengguna</p>
                                    </div>
                                    <div class="icon">
                                        <i class="fas fa-users"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-3 col-md-6 col-sm-6">
                                <div class="small-box bg-success">
                                    <div class="inner">
                                        <h3>{{ $todayAttendances }}</h3>
                                        <p>Absensi Hari Ini</p>
                                    </div>
                                    <div class="icon">
                                        <i class="fas fa-clipboard-check"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-3 col-md-6 col-sm-6">
                                <div class="small-box bg-primary">
                                    <div class="inner">
                                        <h3>{{ $presentToday }}</h3>
                                        <p>Hadir Hari Ini</p>
                                    </div>
                                    <div class="icon">
                                        <i class="fas fa-user-check"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-3 col-md-6 col-sm-6">
                                <div class="small-box bg-warning">
                                    <div class="inner">
                                        <h3>{{ $absentToday }}</h3>
                                        <p>Tidak Hadir Hari Ini</p>
                                    </div>
                                    <div class="icon">
                                        <i class="fas fa-user-times"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-12">
                                <div class="card card-primary card-outline">
                                    <div class="card-header">
                                        <h3 class="card-title">Aksi Cepat</h3>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-3 col-sm-6">
                                                <a href="{{ route('admin.users.index') }}" class="btn btn-outline-primary btn-block mb-3">
                                                    <i class="fas fa-users mr-2"></i> Kelola Pengguna
                                                </a>
                                            </div>
                                            <div class="col-md-3 col-sm-6">
                                                <a href="{{ route('admin.attendance.report') }}" class="btn btn-outline-primary btn-block mb-3">
                                                    <i class="fas fa-chart-bar mr-2"></i> Laporan Absensi
                                                </a>
                                            </div>
                                            <div class="col-md-3 col-sm-6">
                                                <a href="{{ route('notifications.broadcast') }}" class="btn btn-outline-primary btn-block mb-3">
                                                    <i class="fas fa-paper-plane mr-2"></i> Broadcast WhatsApp
                                                </a>
                                            </div>
                                            <div class="col-md-3 col-sm-6">
                                                <a href="{{ route('admin.settings.index') }}" class="btn btn-outline-primary btn-block mb-3">
                                                    <i class="fas fa-cogs mr-2"></i> Pengaturan
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @else
                        <!-- Dashboard untuk Guru dan Staf TU -->
                        <div class="row">
                            <div class="col-md-6">
                                <div class="card card-primary card-outline">
                                    <div class="card-header">
                                        <h3 class="card-title">Status Absensi Hari Ini</h3>
                                    </div>
                                    <div class="card-body">
                                        @if($todayAttendance)
                                            <div class="d-flex align-items-center mb-3">
                                                <div class="mr-3">
                                                    <span class="badge badge-{{ $todayAttendance->status == 'hadir' ? 'success' : ($todayAttendance->status == 'terlambat' ? 'warning' : 'danger') }} p-2">
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
                                                    <i class="fas fa-sign-in-alt mr-2"></i> Absen Masuk
                                                </a>
                                            @elseif(!$todayAttendance->check_out_time)
                                                <a href="{{ route('attendance.check-out') }}" class="btn btn-primary mt-2">
                                                    <i class="fas fa-sign-out-alt mr-2"></i> Absen Pulang
                                                </a>
                                            @endif
                                        @else
                                            <div class="text-center py-4">
                                                <i class="fas fa-clipboard-list fa-3x text-muted mb-3"></i>
                                                <p>Anda belum melakukan absensi hari ini</p>
                                                <a href="{{ route('attendance.check-in') }}" class="btn btn-primary">
                                                    <i class="fas fa-sign-in-alt mr-2"></i> Absen Masuk
                                                </a>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="card card-primary card-outline h-100">
                                    <div class="card-header">
                                        <h3 class="card-title">Ringkasan Bulan Ini</h3>
                                    </div>
                                    <div class="card-body">
                                        <div class="row text-center">
                                            <div class="col-md-4">
                                                <div class="info-box bg-success">
                                                    <span class="info-box-icon"><i class="fas fa-check"></i></span>
                                                    <div class="info-box-content">
                                                        <span class="info-box-text">Hadir</span>
                                                        <span class="info-box-number">{{ $present }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="info-box bg-danger">
                                                    <span class="info-box-icon"><i class="fas fa-times"></i></span>
                                                    <div class="info-box-content">
                                                        <span class="info-box-text">Tidak Hadir</span>
                                                        <span class="info-box-number">{{ $absent }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="info-box bg-warning">
                                                    <span class="info-box-icon"><i class="fas fa-clock"></i></span>
                                                    <div class="info-box-content">
                                                        <span class="info-box-text">Terlambat</span>
                                                        <span class="info-box-number">{{ $late }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="mt-4 text-center">
                                            <a href="{{ route('attendance.history') }}" class="btn btn-outline-primary">
                                                <i class="fas fa-history mr-2"></i> Lihat Riwayat Lengkap
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 