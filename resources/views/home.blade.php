@extends('layouts.app')

@section('title', 'Sistem Absensi Sekolah')

@section('content')
<div class="container">
    <div class="row justify-content-center mb-5">
        <div class="col-md-10">
            <div class="card border-0 bg-primary text-white shadow">
                <div class="card-body p-4">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h2 class="font-weight-bold mb-3">Sistem Absensi Presensi Karyawan</h2>
                            <p class="lead mb-4">Selamat datang di sistem absensi digital. Lakukan absensi dengan mudah menggunakan fitur pengenalan wajah.</p>
                            <a href="{{ route('face.attendance.form') }}" class="btn btn-light btn-lg px-4">
                                <i class="bi bi-camera-fill me-2"></i>Absen dengan Wajah
                            </a>
                        </div>
                        <div class="col-md-4 d-none d-md-block text-end">
                            <i class="bi bi-person-check-fill" style="font-size: 8rem; opacity: 0.6;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-md-4 mb-4">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body text-center p-4">
                    <div class="rounded-circle bg-primary text-white d-inline-flex justify-content-center align-items-center mb-3" style="width: 80px; height: 80px;">
                        <i class="bi bi-camera-fill" style="font-size: 2.5rem;"></i>
                    </div>
                    <h4 class="card-title">Absensi Wajah</h4>
                    <p class="card-text">Lakukan absensi dengan teknologi pengenalan wajah yang aman dan cepat.</p>
                    <a href="{{ route('face.attendance.form') }}" class="btn btn-primary mt-2">
                        <i class="bi bi-arrow-right-circle me-2"></i>Absen Sekarang
                    </a>
                </div>
            </div>
        </div>
        
        <div class="col-md-4 mb-4">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body text-center p-4">
                    <div class="rounded-circle bg-success text-white d-inline-flex justify-content-center align-items-center mb-3" style="width: 80px; height: 80px;">
                        <i class="bi bi-qr-code" style="font-size: 2.5rem;"></i>
                    </div>
                    <h4 class="card-title">Absensi QR Code</h4>
                    <p class="card-text">Scan QR Code untuk melakukan absensi dengan cepat dan efisien.</p>
                    <a href="{{ route('qr.scan', ['token' => 'demo']) }}" class="btn btn-success mt-2">
                        <i class="bi bi-arrow-right-circle me-2"></i>Pindai QR
                    </a>
                </div>
            </div>
        </div>
        
        <div class="col-md-4 mb-4">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body text-center p-4">
                    <div class="rounded-circle bg-info text-white d-inline-flex justify-content-center align-items-center mb-3" style="width: 80px; height: 80px;">
                        <i class="bi bi-box-arrow-in-right" style="font-size: 2.5rem;"></i>
                    </div>
                    <h4 class="card-title">Login</h4>
                    <p class="card-text">Masuk ke dashboard untuk melihat laporan absensi dan fitur lainnya.</p>
                    <a href="{{ route('login') }}" class="btn btn-info mt-2 text-white">
                        <i class="bi bi-arrow-right-circle me-2"></i>Login
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 