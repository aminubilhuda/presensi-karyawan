@extends('layouts.app')

@section('title', 'Absensi Wajah')

@section('styles')
<style>
    /* Mobile optimization */
    @media (max-width: 768px) {
        .container {
            padding: 10px;
        }
        #video-container {
            /* height: auto !important; */
            height: 250px !important;
            min-height: 250px;
        }
        #video {
            height: auto !important;
        }
        .card-body {
            padding: 1rem;
        }
        .btn {
            padding: 0.75rem;
            font-size: 1.1rem;
        }
    }
    
    /* General improvements */
    #video-container {
        background: #000;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
    
    .form-control {
        padding: 0.75rem;
        font-size: 1rem;
    }
    
    .btn-primary, .btn-success {
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        transition: all 0.3s ease;
    }
    
    .btn-primary:hover, .btn-success:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 8px rgba(0,0,0,0.15);
    }
    
    .alert {
        border-radius: 8px;
    }
</style>
<!-- SweetAlert2 CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.min.css">
@endsection

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-header bg-primary text-white py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 fw-bold"><i class="bi bi-camera-fill me-2"></i>Absensi dengan Pengenalan Wajah</h5>
                    </div>
                </div>
                
                <!-- Untuk Mobile: tata letak berubah menjadi stack vertical -->
                <div class="card-body p-3 p-md-4">
                    <!-- Di mobile, video akan penuh di atas -->
                    <div class="row g-4">
                        @if(isset($attendanceStatus) && $attendanceStatus == 'complete')
                        <div class="col-12 mb-3">
                            <div class="alert alert-success border-0 shadow-sm">
                                <div class="d-flex align-items-center">
                                    <div class="me-3 fs-3"><i class="bi bi-check-circle-fill"></i></div>
                                    <div>
                                        <h5 class="alert-heading mb-1">Absensi Hari Ini Sudah Lengkap</h5>
                                        <p class="mb-0">{{ $attendanceMessage }}</p>
                                        <div class="mt-2">
                                            <a href="{{ route('dashboard') }}" class="btn btn-sm btn-primary">
                                                <i class="bi bi-house-fill me-2"></i>Kembali ke Dashboard
                                            </a>
                                            <a href="{{ route('attendance.history') }}" class="btn btn-sm btn-outline-primary ms-2">
                                                <i class="bi bi-clock-history me-2"></i>Lihat Riwayat Absensi
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @elseif(isset($attendanceMessage))
                        <div class="col-12 mb-3">
                            <div class="alert {{ $attendanceStatus == 'half' ? 'alert-info' : 'alert-warning' }} border-0 shadow-sm">
                                <div class="d-flex align-items-center">
                                    <div class="me-3 fs-3">
                                        @if($attendanceStatus == 'half')
                                        <i class="bi bi-hourglass-split"></i>
                                        @else
                                        <i class="bi bi-exclamation-circle-fill"></i>
                                        @endif
                                    </div>
                                    <div>
                                        <h5 class="alert-heading mb-1">
                                            @if($attendanceStatus == 'half')
                                            Absensi Masuk Sudah Tercatat
                                            @else
                                            Belum Ada Absensi Hari Ini
                                            @endif
                                        </h5>
                                        <p class="mb-0">{{ $attendanceMessage }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif

                        @if(!isset($attendanceStatus) || $attendanceStatus != 'complete')
                        <div class="col-md-7 mb-4 mb-md-0">
                            <div class="text-center">
                                <div id="video-container" class="position-relative mx-auto overflow-hidden d-flex justify-content-center align-items-center"
                                    style="width: 100%; max-width: 640px; height: 480px; background-color: #000; border-radius: 12px;">
                                    {{-- <div class="position-absolute top-0 start-0 end-0 z-1 bg-dark bg-opacity-75 text-white p-2 d-flex align-items-center justify-content-between">
                                    </div> --}}
                                    <video id="video" width="100%" height="480" autoplay muted playsinline></video>
                                    <canvas id="overlay" width="640" height="480" class="position-absolute top-0 start-0"></canvas>
                                    
                                    <!-- Panduan penempatan wajah -->
                                    <div id="face-guide" class="position-absolute" style="border: 2px dashed rgba(255,255,255,0.5); width: 200px; height: 200px; border-radius: 50%; top: 50%; left: 50%; transform: translate(-50%, -50%);"></div>
                                    
                                    <!-- Tombol switch kamera -->
                                    <button id="switchCamera" class="btn btn-sm btn-success position-absolute" style="bottom: 15px; right: 15px; border-radius: 5%; opacity: 0.7;">
                                        Balik
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-5">
                            <div class="card h-100 border-0 shadow-sm rounded-3">
                                <div class="card-header bg-light py-3">
                                    <h5 class="mb-0 fw-bold"><i class="bi bi-clipboard-data-fill me-2"></i>Informasi Absensi</h5>
                                </div>
                                <div class="card-body">
                                    <form id="attendanceForm">
                                        <div class="mb-4">
                                            <label for="username" class="form-label fw-bold"><i class="bi bi-person-fill me-2"></i>Username</label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-primary text-white"><i class="bi bi-person-badge"></i></span>
                                                <input type="text" class="form-control form-control-lg" id="username" name="username" placeholder="Masukkan username Anda" required>
                                            </div>
                                        </div>
                                        
                                        <div class="mb-4">
                                            <label class="form-label fw-bold"><i class="bi bi-geo-alt-fill me-2"></i>Lokasi</label>
                                            <div id="location-status" class="alert alert-warning py-3">
                                                <div class="d-flex align-items-center">
                                                    <div class="me-3 fs-4"><i class="bi bi-geo-alt-fill"></i></div>
                                                    <div>
                                                        <span class="fw-bold">Mendapatkan lokasi...</span>
                                                        <div class="progress mt-2" style="height: 5px;">
                                                            <div class="progress-bar progress-bar-striped progress-bar-animated" style="width: 100%"></div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <input type="hidden" id="latitude" name="latitude">
                                            <input type="hidden" id="longitude" name="longitude">
                                        </div>
                                        
                                        <div class="mb-4">
                                            <label class="form-label fw-bold"><i class="bi bi-camera-fill me-2"></i>Status Wajah</label>
                                            <div id="face-status" class="alert alert-warning py-3">
                                                <div class="d-flex align-items-center">
                                                    <div class="me-3 fs-4"><i class="bi bi-camera-fill"></i></div>
                                                    <div>
                                                        <span class="fw-bold">Menunggu pendeteksian wajah...</span>
                                                        <div class="progress mt-2" style="height: 5px;">
                                                            <div class="progress-bar progress-bar-striped progress-bar-animated" style="width: 100%"></div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="d-grid gap-3 mt-4">
                                            <button type="button" id="verifyBtn" class="btn btn-primary btn-lg py-3 fw-bold" disabled>
                                                <i class="bi bi-box-arrow-in-right me-2"></i>Verifikasi & Absen
                                            </button>
                                            <button type="button" class="btn btn-outline-secondary" onclick="window.location.href='{{ route('login') }}'">
                                                <i class="bi bi-shield-lock-fill me-2"></i>Login
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>
                    
                    @if(!isset($attendanceStatus) || $attendanceStatus != 'complete')
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="alert alert-info p-3">
                                <div class="d-flex">
                                    <div class="me-3 fs-3"><i class="bi bi-info-circle-fill"></i></div>
                                    <div>
                                        <strong class="fs-5">Petunjuk:</strong>
                                        <ul class="mt-2 mb-0">
                                            <li>Pastikan wajah Anda terlihat jelas di tengah lingkaran</li>
                                            <li>Masukkan username Anda dengan benar</li>
                                            <li>Pastikan Anda berada di lokasi yang diizinkan</li>
                                            <li>Klik tombol "Verifikasi & Absen" untuk absensi</li>
                                        </ul>
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

<!-- Modal Loading - Diperbarui dengan animasi -->
<div class="modal fade" id="loadingModal" tabindex="-1" aria-labelledby="loadingModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow rounded-4 bg-white">
            <div class="modal-body text-center py-5">
                <div class="spinner-grow text-primary mb-4" style="width: 4rem; height: 4rem;" role="status">
                    <!-- <span class="visually-hidden">Loading...</span> -->
                </div>
                <h4 class="fw-bold mb-3">Sedang Memproses</h4>
                <p class="fs-5 text-muted mb-0">Mohon tunggu sebentar...</p>
                <div class="progress mt-4" style="height: 8px;">
                    <div class="progress-bar progress-bar-striped progress-bar-animated" style="width: 100%"></div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="{{ asset('models/face-api.min.js') }}"></script>
<!-- SweetAlert2 JS -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.all.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Inisialisasi referensi elemen DOM
    const video = document.getElementById('video');
    const overlay = document.getElementById('overlay');
    const verifyBtn = document.getElementById('verifyBtn');
    const usernameInput = document.getElementById('username');
    const latitudeInput = document.getElementById('latitude');
    const longitudeInput = document.getElementById('longitude');
    const faceStatusEl = document.getElementById('face-status');
    const locationStatusEl = document.getElementById('location-status');
    const loadingModal = new bootstrap.Modal(document.getElementById('loadingModal'));
    
    // Inisialisasi variabel untuk pengenalan wajah
    let labeledFaceDescriptors = [];
    let faceMatcher = null;
    
    // Data wajah yang terdaftar dari server
    const registeredFaces = @json($faceData ?? []);
    
    let currentDetections = [];
    let isFaceDetected = false;
    let isLocationDetected = false;
    let capturedImageData = null;
    let recognizedUser = null;
    let currentStream = null;
    
    // Deteksi apakah menggunakan perangkat mobile
    const isMobileDevice = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
    
    // Default menggunakan kamera depan di perangkat mobile, belakang di non-mobile
    let currentFacingMode = isMobileDevice ? 'user' : 'environment';
    
    // Responsif video container untuk mobile
    function adjustVideoSize() {
        const videoContainer = document.getElementById('video-container');
        if (window.innerWidth < 768) {
            const containerWidth = videoContainer.offsetWidth;
            const newHeight = (containerWidth * 3) / 4; // Rasio aspek 4:3
            videoContainer.style.height = `${newHeight}px`;
            overlay.width = containerWidth;
            overlay.height = newHeight;
        } else {
            videoContainer.style.height = '480px';
            overlay.width = 640;
            overlay.height = 480;
        }
    }

    // Panggil saat load dan saat resize window
    adjustVideoSize();
    window.addEventListener('resize', adjustVideoSize);
    
    // Load face-api models
    loadingModal.show();
    Promise.all([
        faceapi.nets.tinyFaceDetector.loadFromUri('/models'),
        faceapi.nets.faceLandmark68Net.loadFromUri('/models'),
        faceapi.nets.faceRecognitionNet.loadFromUri('/models'),
        faceapi.nets.ssdMobilenetv1.loadFromUri('/models')
    ]).then(startVideo)
    .catch(err => {
        loadingModal.hide();
        console.error('Error loading face-api models:', err);
        faceStatusEl.innerHTML = 
            `<div class="d-flex align-items-center">
                <div class="me-3 fs-4"><i class="bi bi-exclamation-triangle-fill text-danger"></i></div>
                <div>
                    <span class="fw-bold text-danger">Gagal memuat model pendeteksi wajah</span>
                    <div class="small">Coba refresh halaman atau gunakan browser lain</div>
                </div>
            </div>`;
        faceStatusEl.className = 'alert alert-danger';
    });
    
    // Fungsi untuk memuat dan membandingkan wajah yang terdaftar
    async function loadRegisteredFaces() {
        if (registeredFaces.length === 0) {
            console.log('Tidak ada wajah terdaftar');
            return;
        }
        
        try {
            // Muat label descriptor untuk setiap wajah terdaftar
            const labeledDescriptors = await Promise.all(
                registeredFaces.map(async (user) => {
                    // Membuat elemen img sementara untuk memuat gambar wajah
                    const img = await faceapi.fetchImage(user.facePhoto);
                    
                    // Deteksi fitur wajah
                    const detection = await faceapi.detectSingleFace(img)
                        .withFaceLandmarks()
                        .withFaceDescriptor();
                    
                    if (!detection) {
                        console.log(`Tidak dapat mendeteksi wajah untuk: ${user.name}`);
                        return null;
                    }
                    
                    // Return labeled descriptor
                    return new faceapi.LabeledFaceDescriptors(
                        JSON.stringify({id: user.id, name: user.name, username: user.username}),
                        [detection.descriptor]
                    );
                })
            );
            
            // Filter null values
            labeledFaceDescriptors = labeledDescriptors.filter(desc => desc !== null);
            
            // Buat face matcher jika ada descriptor yang berhasil dibuat
            if (labeledFaceDescriptors.length > 0) {
                faceMatcher = new faceapi.FaceMatcher(labeledFaceDescriptors, 0.6); // Threshold 0.6
                console.log(`Berhasil memuat ${labeledFaceDescriptors.length} wajah terdaftar`);
            } else {
                console.log('Tidak ada wajah terdaftar yang bisa dipakai untuk pengenalan');
            }
        } catch (error) {
            console.error('Error loading registered faces:', error);
        }
    }
    
    // Mulai video
    function startVideo() {
        // Hentikan stream yang sedang berjalan jika ada
        if (currentStream) {
            currentStream.getTracks().forEach(track => track.stop());
        }
        
        // Konfigurasi constraints untuk kamera
        const constraints = {
            video: {
                facingMode: { ideal: currentFacingMode },
                width: { ideal: 640 },
                height: { ideal: 480 }
            }
        };

        navigator.mediaDevices.getUserMedia(constraints)
            .then(stream => {
                currentStream = stream;
                video.srcObject = stream;
                
                // Setelah model dimuat, muat wajah terdaftar
                loadRegisteredFaces().then(() => {
                    loadingModal.hide();
                });
            })
            .catch(err => {
                loadingModal.hide();
                console.error('Error accessing camera:', err);
                faceStatusEl.innerHTML = 
                    `<div class="d-flex align-items-center">
                        <div class="me-3 fs-4"><i class="bi bi-exclamation-triangle-fill text-danger"></i></div>
                        <div>
                            <span class="fw-bold text-danger">Tidak dapat mengakses kamera</span>
                            <div class="small">Izinkan akses kamera di pengaturan browser Anda</div>
                        </div>
                    </div>`;
                faceStatusEl.className = 'alert alert-danger';
            });
    }
    
    // Deteksi lokasi
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
            (position) => {
                // Deteksi kemungkinan mock location
                const isMockLocation = checkMockLocation(position);
                
                latitudeInput.value = position.coords.latitude;
                longitudeInput.value = position.coords.longitude;
                
                // Kirim tambahan informasi perangkat
                collectDeviceInfo();
                
                if (isMockLocation) {
                    locationStatusEl.innerHTML = 
                        `<div class="d-flex align-items-center">
                            <div class="me-3 fs-4"><i class="bi bi-exclamation-triangle-fill text-danger"></i></div>
                            <div>
                                <span class="fw-bold ">Terdeteksi kemungkinan lokasi palsu</span>
                                <div class="small">Mohon gunakan lokasi asli tanpa aplikasi GPS palsu</div>
                            </div>
                        </div>`;
                    locationStatusEl.className = 'alert alert-danger';
                } else {
                    locationStatusEl.innerHTML = 
                        `<div class="d-flex align-items-center">
                            <div class="me-3 fs-4"><i class="bi bi-geo-alt-fill text-white"></i></div>
                            <div>
                                <span class="fw-bold text-white">Lokasi terdeteksi</span>
                                <div class="small text-white">${position.coords.latitude.toFixed(6)}, ${position.coords.longitude.toFixed(6)}</div>
                            </div>
                        </div>`;
                    locationStatusEl.className = 'alert alert-success';
                    isLocationDetected = true;
                }
                checkAllConditions();
            },
            (error) => {
                console.error('Error getting location:', error);
                locationStatusEl.innerHTML = 
                    `<div class="d-flex align-items-center">
                        <div class="me-3 fs-4"><i class="bi bi-exclamation-triangle-fill text-danger"></i></div>
                        <div>
                            <span class="fw-bold text-danger">Gagal mendapatkan lokasi</span>
                            <div class="small">Izinkan akses lokasi di pengaturan browser Anda</div>
                        </div>
                    </div>`;
                locationStatusEl.className = 'alert alert-danger';
            }, 
            {
                enableHighAccuracy: true,
                timeout: 10000,
                maximumAge: 0
            }
        );
    } else {
        locationStatusEl.innerHTML = 
            `<div class="d-flex align-items-center">
                <div class="me-3 fs-4"><i class="bi bi-exclamation-triangle-fill text-danger"></i></div>
                <div>
                    <span class="fw-bold text-danger">Browser tidak mendukung geolokasi</span>
                    <div class="small">Gunakan browser lain yang lebih baru</div>
                </div>
            </div>`;
        locationStatusEl.className = 'alert alert-danger';
    }
    
    // Fungsi untuk mendeteksi kemungkinan mock location
    function checkMockLocation(position) {
        // Jika pengguna menggunakan desktop/laptop (bukan mobile)
        const isDesktop = !(/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent));
        
        if (isDesktop) {
            console.log('Pengguna menggunakan perangkat desktop, melewati pengecekan GPS palsu');
            // Pada perangkat desktop, lewati pengecekan GPS palsu
            return false;
        }
        
        console.log('Position data:', {
            altitude: position.coords.altitude,
            accuracy: position.coords.accuracy,
            altitudeAccuracy: position.coords.altitudeAccuracy,
            speed: position.coords.speed
        });
        
        // Khusus untuk perangkat mobile:
        // Di Android, mock location biasanya memiliki nilai altitude dan accuracy yang aneh
        if (position.coords.altitude === 0 && position.coords.accuracy === 0) {
            console.log('Terdeteksi lokasi palsu: altitude dan accuracy bernilai 0');
            return true; // Kemungkinan fake GPS
        }
        
        // Pada beberapa mock GPS, altitude accuracy null tapi ini juga bisa terjadi pada browser desktop
        // Jadi kita hanya memeriksa ini pada perangkat mobile dengan nilai accuracy yang mencurigakan
        if (position.coords.altitudeAccuracy === 0 && position.coords.accuracy < 5) {
            console.log('Terdeteksi lokasi palsu: altitudeAccuracy 0 dengan accuracy sangat rendah');
            return true;
        }
        
        // Kecepatan 0 dengan accuracy sempurna juga mencurigakan, tapi hanya pada perangkat mobile
        if (position.coords.speed === 0 && position.coords.accuracy < 5) {
            console.log('Terdeteksi lokasi palsu: speed 0 dengan accuracy sangat rendah');
            return true;
        }
        
        return false;
    }
    
    // Fungsi untuk mengumpulkan informasi perangkat
    function collectDeviceInfo() {
        const deviceInfo = {
            userAgent: navigator.userAgent,
            screenResolution: `${screen.width}x${screen.height}`,
            timezone: Intl.DateTimeFormat().resolvedOptions().timeZone,
            language: navigator.language,
            platform: navigator.platform,
            cores: navigator.hardwareConcurrency || 'unknown',
            devicePixelRatio: window.devicePixelRatio || 'unknown'
        };
        
        // Simpan ke hidden input untuk dikirim bersama form
        const deviceInfoInput = document.createElement('input');
        deviceInfoInput.type = 'hidden';
        deviceInfoInput.name = 'device_info';
        deviceInfoInput.value = JSON.stringify(deviceInfo);
        document.getElementById('attendanceForm').appendChild(deviceInfoInput);
    }
    
    // Deteksi wajah secara realtime
    video.addEventListener('play', () => {
        const overlayCtx = overlay.getContext('2d');
        
        function detectFaces() {
            if (video.paused || video.ended) return;
            
            faceapi.detectAllFaces(video, new faceapi.TinyFaceDetectorOptions())
                .withFaceLandmarks()
                .withFaceDescriptors()
                .then(results => {
                    // Bersihkan canvas
                    overlayCtx.clearRect(0, 0, overlay.width, overlay.height);
                    
                    currentDetections = results;
                    recognizedUser = null;
                    
                    // Hitung skala untuk menyesuaikan posisi landmark dengan ukuran canvas
                    const displaySize = {
                        width: overlay.width,
                        height: overlay.height
                    };
                    const videoSize = {
                        width: video.videoWidth,
                        height: video.videoHeight
                    };
                    
                    // Hitung faktor skala dari ukuran video ke ukuran tampilan
                    const scaleX = displaySize.width / videoSize.width;
                    const scaleY = displaySize.height / videoSize.height;
                    
                    // Update status wajah
                    if (results.length === 0) {
                        faceStatusEl.innerHTML = 
                            `<div class="d-flex align-items-center">
                                <div class="me-3 fs-4"><i class="bi bi-camera-fill text-warning"></i></div>
                                <div>
                                    <span class="fw-bold">Tidak ada wajah terdeteksi</span>
                                    <div class="small">Posisikan wajah Anda di tengah layar</div>
                                </div>
                            </div>`;
                        faceStatusEl.className = 'alert alert-warning';
                        isFaceDetected = false;
                    } else if (results.length > 1) {
                        faceStatusEl.innerHTML = 
                            `<div class="d-flex align-items-center">
                                <div class="me-3 fs-4"><i class="bi bi-exclamation-triangle-fill text-warning"></i></div>
                                <div>
                                    <span class="fw-bold text-warning">Terdeteksi lebih dari satu wajah</span>
                                    <div class="small">Pastikan hanya wajah Anda yang terlihat di kamera</div>
                                </div>
                            </div>`;
                        faceStatusEl.className = 'alert alert-warning';
                        isFaceDetected = false;
                    } else {
                        // Jika ada face matcher dan hanya 1 wajah terdeteksi
                        if (faceMatcher && results.length === 1) {
                            const result = faceMatcher.findBestMatch(results[0].descriptor);
                            
                            if (result.label !== 'unknown') {
                                // Parse label untuk mendapatkan info pengguna
                                const userData = JSON.parse(result.label);
                                recognizedUser = userData;
                                
                                // Isi otomatis username
                                usernameInput.value = userData.username;
                                
                                // Update status pengenalan wajah
                                faceStatusEl.innerHTML = 
                                    `<div class="d-flex align-items-center">
                                        <div class="me-3 fs-4"><i class="bi bi-check-circle-fill text-success"></i></div>
                                        <div>
                                            <span class="fw-bold text-white">Wajah dikenali: ${userData.name}</span>
                                            <div class="small">Username: ${userData.username}</div>
                                        </div>
                                    </div>`;
                                faceStatusEl.className = 'alert alert-success';
                                isFaceDetected = true;
                            } else {
                                // Wajah terdeteksi tapi tidak dikenali
                                faceStatusEl.innerHTML = 
                                    `<div class="d-flex align-items-center">
                                        <div class="me-3 fs-4"><i class="bi bi-question-circle-fill text-warning"></i></div>
                                        <div>
                                            <span class="fw-bold text-warning">Wajah tidak dikenali</span>
                                            <div class="small">Wajah terdeteksi tetapi tidak terdaftar dalam sistem</div>
                                        </div>
                                    </div>`;
                                faceStatusEl.className = 'alert alert-warning';
                                isFaceDetected = true; // Tetap true karena wajah terdeteksi
                            }
                        } else {
                            // Jika tidak ada face matcher, hanya tampilkan bahwa wajah terdeteksi
                            faceStatusEl.innerHTML = 
                                `<div class="d-flex align-items-center">
                                    <div class="me-3 fs-4"><i class="bi bi-check-circle-fill text-success"></i></div>
                                    <div>
                                        <span class="fw-bold text-white">Wajah terdeteksi</span>
                                        <div class="small">Wajah Anda terlihat jelas</div>
                                    </div>
                                </div>`;
                            faceStatusEl.className = 'alert alert-success';
                            isFaceDetected = true;
                        }
                    }
                    
                    // Gambar kotak di sekitar wajah dengan style yang lebih modern
                    results.forEach((detection, i) => {
                        const box = detection.detection.box;
                        
                        // Terapkan skala pada kotak wajah
                        const scaledBox = {
                            x: box.x * scaleX,
                            y: box.y * scaleY,
                            width: box.width * scaleX,
                            height: box.height * scaleY
                        };
                        
                        // Kotak wajah dengan sudut melengkung
                        overlayCtx.lineWidth = 3;
                        overlayCtx.strokeStyle = recognizedUser ? '#00FF00' : '#FFFF00';
                        overlayCtx.beginPath();
                        
                        // Gambar kotak dengan sudut melengkung
                        const radius = 10;
                        overlayCtx.moveTo(scaledBox.x + radius, scaledBox.y);
                        overlayCtx.lineTo(scaledBox.x + scaledBox.width - radius, scaledBox.y);
                        overlayCtx.arcTo(scaledBox.x + scaledBox.width, scaledBox.y, scaledBox.x + scaledBox.width, scaledBox.y + radius, radius);
                        overlayCtx.lineTo(scaledBox.x + scaledBox.width, scaledBox.y + scaledBox.height - radius);
                        overlayCtx.arcTo(scaledBox.x + scaledBox.width, scaledBox.y + scaledBox.height, scaledBox.x + scaledBox.width - radius, scaledBox.y + scaledBox.height, radius);
                        overlayCtx.lineTo(scaledBox.x + radius, scaledBox.y + scaledBox.height);
                        overlayCtx.arcTo(scaledBox.x, scaledBox.y + scaledBox.height, scaledBox.x, scaledBox.y + scaledBox.height - radius, radius);
                        overlayCtx.lineTo(scaledBox.x, scaledBox.y + radius);
                        overlayCtx.arcTo(scaledBox.x, scaledBox.y, scaledBox.x + radius, scaledBox.y, radius);
                        
                        overlayCtx.stroke();
                        
                        // Label wajah
                        const labelBgColor = recognizedUser ? 'rgba(0, 255, 0, 0.7)' : 'rgba(255, 255, 0, 0.7)';
                        const labelText = recognizedUser ? recognizedUser.name : 'Tidak Dikenali';
                        
                        // Ukur lebar teks sebenarnya
                        overlayCtx.font = 'bold 14px Arial';
                        const textMetrics = overlayCtx.measureText(labelText);
                        const labelWidth = Math.ceil(textMetrics.width) + 12; // Tambahkan padding
                        
                        overlayCtx.fillStyle = labelBgColor;
                        overlayCtx.fillRect(scaledBox.x, scaledBox.y - 24, labelWidth, 24);
                        overlayCtx.fillStyle = '#000';
                        overlayCtx.fillText(labelText, scaledBox.x + 5, scaledBox.y - 8);
                    });
                    
                    checkAllConditions();
                    
                    requestAnimationFrame(detectFaces);
                })
                .catch(err => {
                    console.error('Error in face detection:', err);
                    requestAnimationFrame(detectFaces);
                });
        }
        
        detectFaces();
    });
    
    // Cek apakah semua kondisi terpenuhi untuk mengaktifkan tombol verifikasi
    function checkAllConditions() {
        if (isFaceDetected && isLocationDetected && usernameInput.value.trim() !== '') {
            verifyBtn.disabled = false;
            verifyBtn.classList.add('pulse-animation');
        } else {
            verifyBtn.disabled = true;
            verifyBtn.classList.remove('pulse-animation');
        }
    }
    
    // Event listener untuk input username
    usernameInput.addEventListener('input', checkAllConditions);
    
    // Event listener untuk tombol verifikasi
    verifyBtn.addEventListener('click', () => {
        if (currentDetections.length !== 1) {
            alert('Pastikan hanya satu wajah yang terdeteksi');
            return;
        }
        
        if (!latitudeInput.value || !longitudeInput.value) {
            alert('Lokasi tidak terdeteksi. Harap izinkan akses lokasi');
            return;
        }
        
        // Capture gambar untuk diverifikasi
        const canvas = document.createElement('canvas');
        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;
        const ctx = canvas.getContext('2d');
        ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
        capturedImageData = canvas.toDataURL('image/jpeg');
        
        // Tampilkan loading
        loadingModal.show();
        
        // Kirim data ke server
        fetch("{{ route('face.attendance.process') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': "{{ csrf_token() }}"
            },
            body: JSON.stringify({
                face_image: capturedImageData,
                username: usernameInput.value,
                latitude: latitudeInput.value,
                longitude: longitudeInput.value,
                device_info: document.querySelector('input[name="device_info"]').value,
                ip_address: '{{ request()->ip() }}'
            })
        })
        .then(response => response.json())
        .then(data => {
            loadingModal.hide();
            console.log('Server response:', data);
            
            if (data.success) {
                let title = 'Absensi Berhasil';
                let attendanceType = 'masuk';
                
                // Cek jenis absensi dari respons
                if (data.data && data.data.type === 'check_out') {
                    attendanceType = 'pulang';
                }
                
                Swal.fire({
                    icon: 'success',
                    title: `Absen ${attendanceType} Berhasil`,
                    text: data.message,
                    confirmButtonText: 'OK',
                    confirmButtonColor: '#0d6efd'
                });
            } else {
                // Jika error terkait lokasi, tambahkan info koordinat untuk membantu debugging
                let errorMessage = data.message;
                
                if (errorMessage.includes('lokasi') || errorMessage.includes('area')) {
                    const lat = latitudeInput.value;
                    const lng = longitudeInput.value;
                    console.log(`Lokasi saat gagal: Lat ${lat}, Lng ${lng}`);
                    
                    // Tampilkan error dengan detail lokasi
                    Swal.fire({
                        icon: 'error',
                        title: 'Absensi Gagal',
                        html: `${errorMessage}<br><br>Koordinat saat ini: <b>${lat}, ${lng}</b><br><small>Screenshot pesan ini dan laporkan kepada admin.</small>`,
                        confirmButtonText: 'OK',
                        confirmButtonColor: '#0d6efd'
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Absensi Gagal',
                        text: errorMessage,
                        confirmButtonText: 'OK',
                        confirmButtonColor: '#0d6efd'
                    });
                }
            }
        })
        .catch(error => {
            loadingModal.hide();
            console.error('Error:', error);
            
            Swal.fire({
                icon: 'error',
                title: 'Terjadi Kesalahan',
                text: 'Gagal memproses absensi. Silakan coba lagi.',
                confirmButtonText: 'OK',
                confirmButtonColor: '#0d6efd'
            });
        });
    });
    
    // Event listener untuk tombol switch kamera
    document.getElementById('switchCamera').addEventListener('click', function() {
        // Ubah facing mode
        currentFacingMode = currentFacingMode === 'environment' ? 'user' : 'environment';
        
        // Restart video dengan facing mode yang baru
        loadingModal.show();
        startVideo();
    });
});
</script>
@endsection 