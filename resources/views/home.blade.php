@extends('layouts.app')

@section('title', 'Sistem Absensi Sekolah')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card shadow-sm">
                <div class="card-body p-5">
                    <div class="text-center mb-5">
                        <h1 class="display-4 fw-bold text-primary">Sistem Absensi Sekolah</h1>
                        <p class="lead">Solusi modern untuk pengelolaan absensi karyawan sekolah</p>
                    </div>
                    
                    <div class="row g-4 py-4">
                        <div class="col-md-4">
                            <div class="card h-100 border-0 shadow-sm">
                                <div class="card-body text-center p-4">
                                    <div class="mb-3">
                                        <i class="fas fa-user-check fa-3x text-primary"></i>
                                    </div>
                                    <h4>Absensi Mudah</h4>
                                    <p class="text-muted">Absen dengan mudah menggunakan foto selfie dan GPS untuk verifikasi lokasi.</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="card h-100 border-0 shadow-sm">
                                <div class="card-body text-center p-4">
                                    <div class="mb-3">
                                        <i class="fas fa-chart-line fa-3x text-primary"></i>
                                    </div>
                                    <h4>Laporan Real-time</h4>
                                    <p class="text-muted">Pantau kehadiran dan keterlambatan secara real-time dengan dashboard interaktif.</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="card h-100 border-0 shadow-sm">
                                <div class="card-body text-center p-4">
                                    <div class="mb-3">
                                        <i class="fas fa-cog fa-3x text-primary"></i>
                                    </div>
                                    <h4>Pengaturan Fleksibel</h4>
                                    <p class="text-muted">Sesuaikan jadwal kerja, hari libur, dan lokasi absensi sesuai kebutuhan sekolah.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="text-center mt-4">
                        <a href="{{ route('login') }}" class="btn btn-primary btn-lg px-5">Masuk Sekarang</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 