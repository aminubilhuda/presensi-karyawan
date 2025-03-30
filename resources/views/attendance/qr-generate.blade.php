@extends('layouts.app')

@section('title', 'QR Code Absensi')

@section('content')
<div class="container-fluid">
    <!-- Content Header -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">QR Code Absensi</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">QR Code Absensi</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="card card-primary card-outline">
                <div class="card-header">
                    <h3 class="card-title">QR Code Anda</h3>
                </div>
                <div class="card-body text-center">
                    <div class="qr-container p-3">
                        <div class="mb-3">
                            {!! $qrCode !!}
                        </div>
                        <div class="mb-2">
                            <span class="badge badge-success">Aktif: <span id="countdown">05:00</span></span>
                        </div>
                        <div class="small text-muted">
                            QR Code ini valid hingga: {{ $qrToken->expires_at->format('H:i:s') }}
                        </div>
                    </div>
                    
                    <div class="mt-4">
                        <button id="refreshButton" class="btn btn-primary btn-block">
                            <i class="fas fa-sync-alt mr-2"></i> Perbarui QR Code
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card card-info card-outline">
                <div class="card-header">
                    <h3 class="card-title">Informasi</h3>
                </div>
                <div class="card-body">
                    <div class="callout callout-info">
                        <h5><i class="fas fa-info-circle"></i> Tentang QR Code Absensi</h5>
                        <p>QR Code ini digunakan untuk melakukan absensi tanpa harus login terlebih dahulu. Tunjukkan QR Code ini untuk di-scan saat absen masuk atau pulang.</p>
                    </div>
                    
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle mr-2"></i>
                        <strong>Penting:</strong> QR Code ini hanya berlaku selama 5 menit. Jika sudah kadaluarsa, silakan perbarui QR Code.
                    </div>
                    
                    <div class="mt-3">
                        <button class="btn btn-success btn-block" id="shareButton">
                            <i class="fas fa-share-alt mr-2"></i> Bagikan QR Code
                        </button>
                        
                        <a href="{{ $qrUrl }}" target="_blank" class="btn btn-info btn-block mt-2">
                            <i class="fas fa-external-link-alt mr-2"></i> Buka Link QR Code
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Set waktu kedaluwarsa (5 menit dari sekarang)
        var countDownDate = new Date("{{ $qrToken->expires_at->format('Y-m-d H:i:s') }}").getTime();
        
        // Update countdown setiap 1 detik
        var x = setInterval(function() {
            // Waktu sekarang
            var now = new Date().getTime();
            
            // Selisih waktu
            var distance = countDownDate - now;
            
            // Hitung menit dan detik
            var minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            var seconds = Math.floor((distance % (1000 * 60)) / 1000);
            
            // Format tampilan
            var displayMinutes = minutes < 10 ? "0" + minutes : minutes;
            var displaySeconds = seconds < 10 ? "0" + seconds : seconds;
            
            // Tampilkan countdown
            document.getElementById("countdown").innerHTML = displayMinutes + ":" + displaySeconds;
            
            // Jika countdown selesai
            if (distance < 0) {
                clearInterval(x);
                document.getElementById("countdown").innerHTML = "EXPIRED";
                document.getElementById("countdown").parentElement.classList.remove("badge-success");
                document.getElementById("countdown").parentElement.classList.add("badge-danger");
            }
        }, 1000);
        
        // Refresh QR Code
        document.getElementById('refreshButton').addEventListener('click', function() {
            window.location.reload();
        });
        
        // Share QR Code jika Web Share API tersedia
        document.getElementById('shareButton').addEventListener('click', function() {
            if (navigator.share) {
                navigator.share({
                    title: 'QR Code Absensi Saya',
                    text: 'Gunakan QR Code ini untuk melakukan absensi',
                    url: '{{ $qrUrl }}'
                })
                .catch(error => console.log('Error sharing:', error));
            } else {
                alert('Maaf, browser Anda tidak mendukung fitur berbagi.');
            }
        });
    });
</script>
@endsection 