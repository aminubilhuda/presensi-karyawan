@extends('layouts.app')

@section('title', 'Pendaftaran Wajah')

@section('styles')
<style>
    /* Mobile optimization */
    @media (max-width: 768px) {
        #video-container, 
        #capturedImage {
            width: 100% !important;
            height: auto !important;
            min-height: 250px;
        }
    }
    
    /* General improvements */
    #video-container {
        background: #000;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
    
    .btn-primary, .btn-success {
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        transition: all 0.3s ease;
    }
    
    .btn-primary:hover, .btn-success:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 8px rgba(0,0,0,0.15);
    }
    
    .registered-face {
        width: 150px;
        height: 150px;
        object-fit: cover;
        border-radius: 10px;
        border: 3px solid #198754;
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }
</style>
<!-- SweetAlert2 CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.min.css">
@endsection

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Pendaftaran Wajah</h5>
                </div>
                <div class="card-body">
                    @if(isset($hasFace) && $hasFace)
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="alert alert-info border-0 shadow-sm">
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0 me-3">
                                        <img src="{{ $facePhoto }}" alt="Wajah Terdaftar" class="registered-face">
                                    </div>
                                    <div class="flex-grow-1">
                                        <h5 class="alert-heading mb-2"><i class="bi bi-info-circle-fill me-2"></i>Wajah Sudah Terdaftar</h5>
                                        <p class="mb-1">Anda sudah memiliki wajah terdaftar dalam sistem. Jika ingin mendaftarkan ulang, wajah lama Anda akan dihapus dan diganti dengan wajah baru.</p>
                                        <button type="button" id="reregisterFace" class="btn btn-warning mt-2">
                                            <i class="bi bi-arrow-repeat me-2"></i>Daftarkan Ulang Wajah
                                        </button>
                                        <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary mt-2 ms-2">
                                            <i class="bi bi-arrow-left me-2"></i>Kembali ke Dashboard
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div id="registrationForm" class="@if(isset($hasFace) && $hasFace) d-none @endif">
                    @endif
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="text-center mb-3">
                                <div id="video-container" class="position-relative mx-auto" style="width: 400px; height: 300px; background-color: #f8f9fa; border-radius: 8px; overflow: hidden;">
                                    <video id="video" width="400" height="300" autoplay muted style="transform: scaleX(-1);"></video>
                                    <canvas id="overlay" width="400" height="300" class="position-absolute top-0 start-0" style="transform: scaleX(-1);"></canvas>
                                    
                                    <!-- Status deteksi -->
                                    <div id="detection-status" class="position-absolute top-0 start-0 end-0 bg-dark bg-opacity-75 text-white p-2 d-flex align-items-center justify-content-between">
                                        <span><i class="bi bi-camera-video-fill me-2"></i>Kamera</span>
                                        <span class="badge bg-warning">Mendeteksi...</span>
                                    </div>
                                    
                                    <!-- Panduan penempatan wajah -->
                                    <div id="face-guide" class="position-absolute" style="border: 2px dashed rgba(255,255,255,0.5); width: 200px; height: 200px; border-radius: 50%; top: 50%; left: 50%; transform: translate(-50%, -50%);"></div>
                                </div>
                                <div class="mt-3">
                                    <button id="capture" class="btn btn-primary">
                                        <i class="bi bi-camera-fill me-2"></i>Ambil Gambar
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="text-center mb-3">
                                <div class="position-relative mx-auto" style="width: 400px; height: 300px; background-color: #f8f9fa; border-radius: 8px; overflow: hidden;">
                                    <canvas id="capturedImage" width="400" height="300" class="border" style="transform: scaleX(-1);"></canvas>
                                </div>
                                <div class="mt-3">
                                    <button id="save" class="btn btn-success" disabled>
                                        <i class="bi bi-check-circle me-2"></i>Simpan Wajah
                                    </button>
                                    <button id="retake" class="btn btn-secondary ms-2" disabled>
                                        <i class="bi bi-arrow-repeat me-2"></i>Ambil Ulang
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="alert alert-info">
                                <i class="bi bi-info-circle-fill me-2"></i>
                                <strong>Petunjuk:</strong> Posisikan wajah Anda dengan jelas di tengah lingkaran. Pastikan pencahayaan baik dan tidak ada penghalang di wajah Anda. Setelah selesai, klik tombol "Ambil Gambar" untuk menangkap wajah Anda.
                            </div>
                        </div>
                    </div>
                    
                    @if(isset($hasFace) && $hasFace)
                    </div> <!-- end registration form -->
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Konfirmasi -->
<div class="modal fade" id="registerFaceModal" tabindex="-1" aria-labelledby="registerFaceModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="registerFaceModalLabel">Pendaftaran Wajah</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                @if(isset($hasFace) && $hasFace)
                <p><strong>Anda akan mendaftarkan ulang wajah Anda.</strong></p>
                <p>Wajah lama Anda akan diganti dengan wajah baru. Apakah Anda yakin ingin melanjutkan?</p>
                @else
                <p>Apakah Anda ingin mendaftarkan wajah Anda untuk fitur absensi wajah?</p>
                <p class="text-muted small">Wajah yang terdaftar akan digunakan untuk verifikasi identitas pada saat melakukan absensi.</p>
                @endif
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" id="confirmRegister">
                    @if(isset($hasFace) && $hasFace)
                    Ya, Daftarkan Ulang
                    @else
                    Ya, Daftarkan
                    @endif
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Loading -->
<div class="modal fade" id="loadingModal" tabindex="-1" aria-labelledby="loadingModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow rounded-4 bg-white">
            <div class="modal-body text-center py-5">
                <div class="spinner-grow text-primary mb-4" style="width: 4rem; height: 4rem;" role="status">
                    <span class="visually-hidden">Loading...</span>
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

<!-- Modal Notifikasi -->
<div class="modal fade" id="notificationModal" tabindex="-1" aria-labelledby="notificationModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow rounded-4">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold" id="notificationModalLabel">Notifikasi</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center py-4">
                <div id="notification-icon" class="mb-4" style="font-size: 5rem; line-height: 1;">
                    <!-- Icon will be inserted here -->
                </div>
                <h3 id="notification-title" class="fw-bold mb-3">Proses Selesai</h3>
                <p id="notification-message" class="fs-5 text-muted mb-0">Pesan notifikasi akan muncul di sini.</p>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-primary px-4 py-2 fw-bold" id="notificationBtn">OK</button>
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
    // Referensi ke modal
    const registerFaceModal = new bootstrap.Modal(document.getElementById('registerFaceModal'));
    
    // Inisialisasi referensi elemen DOM
    const video = document.getElementById('video');
    const overlay = document.getElementById('overlay');
    const captureBtn = document.getElementById('capture');
    const saveBtn = document.getElementById('save');
    const retakeBtn = document.getElementById('retake');
    const capturedCanvas = document.getElementById('capturedImage');
    const loadingModal = new bootstrap.Modal(document.getElementById('loadingModal'));
    const notificationModal = new bootstrap.Modal(document.getElementById('notificationModal'));
    const confirmRegisterBtn = document.getElementById('confirmRegister');
    
    let capturedImageData = null;
    let detections = [];
    let isCaptured = false;
    let isReregistering = false;
    
    // Jika sudah ada wajah terdaftar, tambahkan listener untuk tombol daftar ulang
    @if(isset($hasFace) && $hasFace)
    document.getElementById('reregisterFace').addEventListener('click', function() {
        console.log('Tombol daftar ulang wajah diklik');
        isReregistering = true;
        document.getElementById('registrationForm').classList.remove('d-none');
        this.closest('.alert').classList.add('d-none');
        registerFaceModal.show();
    });
    @else
    // Tampilkan modal konfirmasi saat halaman dimuat untuk pendaftaran awal
    setTimeout(function() {
        registerFaceModal.show();
    }, 500);
    @endif
    
    // Jika batal, kembali ke halaman sebelumnya
    document.querySelector('#registerFaceModal .btn-secondary').addEventListener('click', function() {
        if (isReregistering) {
            // Jika membatalkan pendaftaran ulang, kembalikan tampilan awal
            document.getElementById('registrationForm').classList.add('d-none');
            document.querySelector('.alert.alert-info').classList.remove('d-none');
            isReregistering = false;
        } else {
            // Jika pendaftaran awal, kembali ke halaman sebelumnya
            window.history.back();
        }
    });
    
    // Fungsi untuk menampilkan notifikasi
    function showNotification(type, title, message, callback) {
        // Gunakan SweetAlert2
        Swal.fire({
            icon: type, // 'success', 'error', 'warning', 'info'
            title: title,
            text: message,
            confirmButtonText: 'OK',
            confirmButtonColor: '#0d6efd'
        }).then(callback);
    }
    
    // Jika modal notifikasi ditutup, lakukan callback
    document.getElementById('notificationBtn').addEventListener('click', function() {
        const notificationTitle = document.getElementById('notification-title').textContent;
        if (notificationTitle === 'Berhasil!') {
            window.location.href = "{{ route('dashboard') }}";
        } else {
            notificationModal.hide();
        }
    });
    
    // Jika konfirmasi ditekan, mulai proses
    confirmRegisterBtn.addEventListener('click', function() {
        console.log('Tombol konfirmasi register diklik, isReregistering:', isReregistering);
        registerFaceModal.hide();
        startCamera();
    });
    
    // Fungsi untuk memulai kamera
    function startCamera() {
        // Tampilkan loading
        loadingModal.show();
        
        // Load face-api models dan mulai webcam
        Promise.all([
            faceapi.nets.tinyFaceDetector.loadFromUri('/models'),
            faceapi.nets.faceLandmark68Net.loadFromUri('/models'),
            faceapi.nets.faceRecognitionNet.loadFromUri('/models')
        ])
        .then(() => {
            console.log('Model face-api berhasil dimuat');
            return navigator.mediaDevices.getUserMedia({ video: true });
        })
        .then(stream => {
            console.log('Kamera berhasil diakses');
            video.srcObject = stream;
            loadingModal.hide();
        })
        .catch(error => {
            console.error('Error:', error);
            loadingModal.hide();
            showNotification('error', 'Gagal', 'Tidak dapat mengakses kamera atau memuat model: ' + error.message);
        });
    }
    
    // Deteksi wajah secara realtime
    video.addEventListener('play', () => {
        const overlayCtx = overlay.getContext('2d');
        
        function detectFaces() {
            if (video.paused || video.ended || isCaptured) return;
            
            faceapi.detectAllFaces(video, new faceapi.TinyFaceDetectorOptions())
                .withFaceLandmarks()
                .then(results => {
                    // Bersihkan canvas
                    overlayCtx.clearRect(0, 0, overlay.width, overlay.height);
                    
                    detections = results;
                    
                    // Update status deteksi
                    const detectionStatus = document.querySelector('#detection-status .badge');
                    if (results.length === 0) {
                        detectionStatus.textContent = 'Tidak Ada Wajah';
                        detectionStatus.className = 'badge bg-warning';
                    } else if (results.length > 1) {
                        detectionStatus.textContent = 'Multi Wajah';
                        detectionStatus.className = 'badge bg-warning';
                    } else {
                        detectionStatus.textContent = 'Wajah Terdeteksi';
                        detectionStatus.className = 'badge bg-success';
                    }
                    
                    // Gambar kotak di sekitar wajah
                    results.forEach(detection => {
                        const box = detection.detection.box;
                        overlayCtx.beginPath();
                        overlayCtx.rect(box.x, box.y, box.width, box.height);
                        overlayCtx.strokeStyle = '#00FF00';
                        overlayCtx.lineWidth = 2;
                        overlayCtx.stroke();
                        
                        // Gambar landmark wajah
                        const landmarks = detection.landmarks;
                        const positions = landmarks.positions;
                        
                        positions.forEach(point => {
                            overlayCtx.beginPath();
                            overlayCtx.arc(point.x, point.y, 2, 0, 2 * Math.PI);
                            overlayCtx.fillStyle = '#00FFFF';
                            overlayCtx.fill();
                        });
                    });
                    
                    captureBtn.disabled = results.length === 0;
                    
                    requestAnimationFrame(detectFaces);
                })
                .catch(err => {
                    console.error('Error in face detection:', err);
                    requestAnimationFrame(detectFaces);
                });
        }
        
        detectFaces();
    });
    
    // Capture button
    captureBtn.addEventListener('click', () => {
        if (detections.length === 0) {
            showNotification('warning', 'Perhatian', 'Tidak ada wajah terdeteksi. Mohon posisikan wajah Anda dengan jelas di tengah kamera.');
            return;
        }
        
        if (detections.length > 1) {
            showNotification('warning', 'Perhatian', 'Terdeteksi lebih dari satu wajah. Mohon pastikan hanya wajah Anda yang terlihat di kamera.');
            return;
        }
        
        // Ambil gambar dari video
        const ctx = capturedCanvas.getContext('2d');
        ctx.drawImage(video, 0, 0, capturedCanvas.width, capturedCanvas.height);
        
        // Simpan data gambar
        capturedImageData = capturedCanvas.toDataURL('image/jpeg');
        
        // Enable tombol
        saveBtn.disabled = false;
        retakeBtn.disabled = false;
        
        // Tandai sudah dicapture
        isCaptured = true;
    });
    
    // Retake button
    retakeBtn.addEventListener('click', () => {
        // Bersihkan canvas
        const ctx = capturedCanvas.getContext('2d');
        ctx.clearRect(0, 0, capturedCanvas.width, capturedCanvas.height);
        capturedImageData = null;
        
        // Disable tombol
        saveBtn.disabled = true;
        retakeBtn.disabled = true;
        
        // Reset flag
        isCaptured = false;
    });
    
    // Save button
    saveBtn.addEventListener('click', () => {
        if (!capturedImageData) {
            showNotification('warning', 'Perhatian', 'Tidak ada gambar untuk disimpan.');
            return;
        }
        
        console.log('Menyimpan gambar wajah...');
        
        // Tampilkan loading
        loadingModal.show();
        
        // Kirim data ke server
        fetch("{{ route('face.register') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': "{{ csrf_token() }}"
            },
            body: JSON.stringify({
                face_image: capturedImageData
            })
        })
        .then(response => {
            console.log('Response status:', response.status);
            return response.json();
        })
        .then(data => {
            console.log('Response data:', data);
            loadingModal.hide();
            
            if (data.success) {
                showNotification('success', 'Berhasil!', data.message, () => {
                    window.location.href = "{{ route('dashboard') }}";
                });
            } else {
                showNotification('error', 'Gagal!', data.message);
            }
        })
        .catch(error => {
            console.error('Error detail:', error);
            loadingModal.hide();
            showNotification('error', 'Error', 'Terjadi kesalahan saat menyimpan data. Silakan coba lagi.');
        });
    });
});
</script>
@endsection 