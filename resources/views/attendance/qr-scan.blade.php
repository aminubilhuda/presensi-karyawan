<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Scan QR Code Absensi</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.1.0/dist/css/adminlte.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        body {
            background-color: #f4f6f9;
            height: 100vh;
        }
        .scanner-container {
            position: relative;
            max-width: 100%;
            min-height: 300px;
            overflow: hidden;
            border-radius: 8px;
        }
        #reader {
            width: 100%;
            border: none !important;
            box-shadow: none !important;
        }
        #reader video {
            border-radius: 8px;
            max-height: 70vh;
        }
        .scanner-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            z-index: 10;
        }
        .scanner-instructions {
            padding: 20px;
            background: rgba(0,0,0,0.7);
            border-radius: 10px;
            text-align: center;
        }
        /* Sembunyikan elemen yang tidak diperlukan dari pustaka html5-qrcode */
        .html5-qrcode-element {
            font-size: 14px !important;
        }
        .html5-qrcode-button {
            padding: 8px 16px !important;
            background-color: #007bff !important;
            color: white !important;
            border: none !important;
            border-radius: 4px !important;
        }
    </style>
</head>
<body class="hold-transition">
    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card card-primary card-outline">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-qrcode mr-2"></i>Scan QR Code Absensi
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle mr-2"></i>
                            Arahkan kamera ke QR Code yang ingin di-scan untuk melakukan absensi.
                        </div>

                        @if(isset($error))
                            <div class="alert alert-danger mb-3">
                                <i class="fas fa-exclamation-circle mr-2"></i>
                                {{ $error }}
                            </div>
                        @endif

                        @if(session('success'))
                            <div class="alert alert-success mb-3">
                                <i class="fas fa-check-circle mr-2"></i>
                                {{ session('success') }}
                            </div>
                        @endif

                        @if(session('error'))
                            <div class="alert alert-danger mb-3">
                                <i class="fas fa-exclamation-circle mr-2"></i>
                                {{ session('error') }}
                            </div>
                        @endif

                        <div class="scanner-container mb-3">
                            <div id="reader"></div>
                            <div class="scanner-overlay" id="scannerOverlay">
                                <div class="scanner-instructions">
                                    <i class="fas fa-camera fa-2x mb-2"></i>
                                    <p>Klik tombol "Start Scanning" untuk memulai scan</p>
                                </div>
                            </div>
                        </div>

                        <div id="scanResult" class="d-none">
                            <!-- Hasil scan akan ditampilkan disini -->
                        </div>

                        @if(isset($user) && isset($attendance))
                            <div class="mt-4">
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <div class="text-center mb-3">
                                            <img src="{{ asset('images/avatar.png') }}" alt="User Avatar" class="img-circle elevation-2" style="width: 80px; height: 80px;">
                                            <h5 class="mt-2">{{ $user->name }}</h5>
                                            <p class="text-muted">{{ $user->role->name }}</p>
                                        </div>

                                        <form action="{{ route('qr.process-attendance') }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="token" value="{{ $token }}">
                                            
                                            <div class="form-group">
                                                <label>Status Absensi:</label>
                                                <div class="d-flex justify-content-center my-3">
                                                    @if($attendanceStatus === 'checkin')
                                                        <button type="submit" name="attendance_type" value="checkin" class="btn btn-success btn-lg mx-2">
                                                            <i class="fas fa-sign-in-alt mr-2"></i>Absen Masuk
                                                        </button>
                                                    @elseif($attendanceStatus === 'checkout')
                                                        <button type="submit" name="attendance_type" value="checkout" class="btn btn-danger btn-lg mx-2">
                                                            <i class="fas fa-sign-out-alt mr-2"></i>Absen Pulang
                                                        </button>
                                                    @elseif($attendanceStatus === 'complete')
                                                        <div class="alert alert-success text-center">
                                                            <i class="fas fa-check-circle fa-2x mb-2"></i>
                                                            <p>Anda telah melakukan absen masuk dan pulang hari ini.</p>
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>

                                            <div class="text-center mt-3">
                                                <p class="mb-1">Tanggal: <strong>{{ now()->format('d F Y') }}</strong></p>
                                                <p>Waktu: <strong id="currentTime">{{ now()->format('H:i:s') }}</strong></p>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="text-center mt-3">
                    <a href="{{ route('login') }}" class="btn btn-outline-primary">
                        <i class="fas fa-arrow-left mr-1"></i> Kembali ke Login
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/admin-lte@3.1.0/dist/js/adminlte.min.js"></script>
    <script src="https://unpkg.com/html5-qrcode@2.3.0/html5-qrcode.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Tampilkan waktu terkini
            function updateTime() {
                const timeElement = document.getElementById('currentTime');
                if (timeElement) {
                    timeElement.textContent = new Date().toLocaleTimeString('id-ID');
                }
            }
            setInterval(updateTime, 1000);
            
            // Setup QR Scanner
            const html5QrCode = new Html5Qrcode("reader");
            const scannerOverlay = document.getElementById('scannerOverlay');
            const scanResult = document.getElementById('scanResult');
            
            const qrCodeSuccessCallback = (decodedText, decodedResult) => {
                // Hentikan scanning
                html5QrCode.stop();
                
                // Tampilkan loading
                scanResult.classList.remove('d-none');
                scanResult.innerHTML = `
                    <div class="text-center my-4">
                        <div class="spinner-border text-primary" role="status">
                            <span class="sr-only">Loading...</span>
                        </div>
                        <p class="mt-2">Memproses QR Code...</p>
                    </div>
                `;
                
                // Redirect ke URL dari QR Code
                window.location.href = decodedText;
            };
            
            const config = {
                fps: 10,
                qrbox: { width: 250, height: 250 },
                aspectRatio: 1.0,
                formatsToSupport: [ Html5QrcodeSupportedFormats.QR_CODE ]
            };
            
            // Mulai scan saat halaman dimuat
            html5QrCode.start(
                { facingMode: "environment" },
                config,
                qrCodeSuccessCallback
            ).then(() => {
                // Scanner dimulai, sembunyikan overlay
                scannerOverlay.style.display = 'none';
            }).catch((err) => {
                // Error memulai scanner
                scannerOverlay.querySelector('.scanner-instructions').innerHTML = `
                    <i class="fas fa-exclamation-triangle fa-2x mb-2"></i>
                    <p>Tidak dapat mengakses kamera.<br>Pastikan Anda memberikan izin.</p>
                    <button id="requestCameraPermission" class="btn btn-primary mt-2">
                        <i class="fas fa-camera mr-1"></i> Izinkan Kamera
                    </button>
                `;
                
                // Tambahkan listener untuk mencoba lagi
                document.getElementById('requestCameraPermission').addEventListener('click', () => {
                    location.reload();
                });
            });
        });
    </script>
</body>
</html> 