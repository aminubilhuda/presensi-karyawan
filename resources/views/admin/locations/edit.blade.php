@extends('layouts.app')

@section('title', 'Edit Lokasi Absensi')

@section('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
    #map {
        height: 500px;
        width: 100%;
    }
</style>
@endsection

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Edit Lokasi Absensi</h1>
        <a href="{{ route('admin.locations.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-1"></i> Kembali
        </a>
    </div>

    <div class="card shadow">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="fas fa-map-marker-alt me-2"></i> Form Edit Lokasi</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.locations.update', $location) }}" method="POST">
                @csrf
                @method('PUT')
                
                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="name" class="form-label">Nama Lokasi <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $location->name) }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="radius" class="form-label">Radius (meter) <span class="text-danger">*</span></label>
                            <input type="number" class="form-control @error('radius') is-invalid @enderror" id="radius" name="radius" value="{{ old('radius', $location->radius) }}" min="50" max="1000" required>
                            @error('radius')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Minimal 50 meter, maksimal 1000 meter</small>
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="address" class="form-label">Alamat <span class="text-danger">*</span></label>
                    <textarea class="form-control @error('address') is-invalid @enderror" id="address" name="address" rows="2" required>{{ old('address', $location->address) }}</textarea>
                    @error('address')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">Lokasi pada Peta <span class="text-danger">*</span></label>
                    <button type="button" id="detect-location" class="btn btn-sm btn-info ms-2">
                        <i class="fas fa-location-arrow me-1"></i> Deteksi Lokasi Saat Ini
                    </button>
                    <div id="map" class="mb-2"></div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="latitude" class="form-label">Latitude</label>
                                <input type="text" class="form-control @error('latitude') is-invalid @enderror" id="latitude" name="latitude" value="{{ old('latitude', $location->latitude) }}" readonly required>
                                @error('latitude')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="longitude" class="form-label">Longitude</label>
                                <input type="text" class="form-control @error('longitude') is-invalid @enderror" id="longitude" name="longitude" value="{{ old('longitude', $location->longitude) }}" readonly required>
                                @error('longitude')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i> Klik pada peta untuk mengubah lokasi. Lingkaran menunjukkan radius lokasi.
                    </div>
                </div>

                <div class="mb-3">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', $location->is_active) ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_active">Aktifkan Lokasi</label>
                    </div>
                    <small class="text-muted">Lokasi yang aktif akan digunakan dalam proses absensi</small>
                </div>

                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                    <a href="{{ route('admin.locations.index') }}" class="btn btn-secondary me-md-2">
                        <i class="fas fa-times me-1"></i> Batal
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i> Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Ambil nilai awal
        const initialLat = {{ $location->latitude }};
        const initialLng = {{ $location->longitude }};
        const initialRadius = {{ $location->radius }};
        
        // Inisialisasi peta dengan lokasi yang ada
        const map = L.map('map').setView([initialLat, initialLng], 15);
        
        // Layer peta
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(map);
        
        // Variabel untuk marker dan circle
        let marker = null;
        let circle = null;
        
        // Fungsi untuk menambahkan marker
        function addMarker(lat, lng) {
            // Hapus marker dan circle yang ada jika sudah ada
            if (marker) {
                map.removeLayer(marker);
                map.removeLayer(circle);
            }
            
            // Tambahkan marker baru
            marker = L.marker([lat, lng]).addTo(map);
            
            // Dapatkan nilai radius dari input
            const radius = parseInt(document.getElementById('radius').value);
            
            // Tambahkan circle dengan radius
            circle = L.circle([lat, lng], {
                color: '{{ $location->is_active ? "blue" : "red" }}',
                fillColor: '{{ $location->is_active ? "#30f" : "#f03" }}',
                fillOpacity: 0.2,
                radius: radius
            }).addTo(map);
            
            // Isi nilai latitude dan longitude
            document.getElementById('latitude').value = lat.toFixed(6);
            document.getElementById('longitude').value = lng.toFixed(6);
        }
        
        // Event ketika user mengklik peta
        map.on('click', function(e) {
            addMarker(e.latlng.lat, e.latlng.lng);
        });
        
        // Event ketika radius berubah
        document.getElementById('radius').addEventListener('change', function() {
            // Jika sudah ada marker, update radius circle
            if (marker) {
                const lat = parseFloat(document.getElementById('latitude').value);
                const lng = parseFloat(document.getElementById('longitude').value);
                addMarker(lat, lng);
            }
        });
        
        // Event ketika status aktif berubah
        document.getElementById('is_active').addEventListener('change', function() {
            if (circle) {
                const isActive = this.checked;
                const color = isActive ? 'blue' : 'red';
                const fillColor = isActive ? '#30f' : '#f03';
                
                // Hapus circle lama
                map.removeLayer(circle);
                
                // Buat circle baru dengan warna sesuai status
                const lat = parseFloat(document.getElementById('latitude').value);
                const lng = parseFloat(document.getElementById('longitude').value);
                const radius = parseInt(document.getElementById('radius').value);
                
                circle = L.circle([lat, lng], {
                    color: color,
                    fillColor: fillColor,
                    fillOpacity: 0.2,
                    radius: radius
                }).addTo(map);
            }
        });
        
        // Tampilkan marker awal
        addMarker(initialLat, initialLng);
        
        // Deteksi lokasi saat ini
        document.getElementById('detect-location').addEventListener('click', function() {
            if (navigator.geolocation) {
                // Tampilkan pesan loading
                this.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Mendeteksi...';
                this.disabled = true;
                
                navigator.geolocation.getCurrentPosition(
                    function(position) {
                        // Berhasil mendapatkan lokasi
                        const lat = position.coords.latitude;
                        const lng = position.coords.longitude;
                        
                        // Pindahkan peta ke lokasi yang terdeteksi
                        map.setView([lat, lng], 17);
                        
                        // Tambahkan marker di lokasi tersebut
                        addMarker(lat, lng);
                        
                        // Kembalikan tampilan tombol
                        const detectBtn = document.getElementById('detect-location');
                        detectBtn.innerHTML = '<i class="fas fa-location-arrow me-1"></i> Deteksi Lokasi Saat Ini';
                        detectBtn.disabled = false;
                    },
                    function(error) {
                        // Gagal mendapatkan lokasi
                        let errorMsg = 'Gagal mendeteksi lokasi';
                        switch(error.code) {
                            case error.PERMISSION_DENIED:
                                errorMsg = 'Akses lokasi ditolak oleh pengguna';
                                break;
                            case error.POSITION_UNAVAILABLE:
                                errorMsg = 'Informasi lokasi tidak tersedia';
                                break;
                            case error.TIMEOUT:
                                errorMsg = 'Waktu permintaan lokasi habis';
                                break;
                        }
                        
                        // Tampilkan alert error
                        alert(errorMsg);
                        
                        // Kembalikan tampilan tombol
                        const detectBtn = document.getElementById('detect-location');
                        detectBtn.innerHTML = '<i class="fas fa-location-arrow me-1"></i> Deteksi Lokasi Saat Ini';
                        detectBtn.disabled = false;
                    },
                    {
                        enableHighAccuracy: true,
                        timeout: 10000,
                        maximumAge: 0
                    }
                );
            } else {
                alert('Browser Anda tidak mendukung Geolocation API');
            }
        });
    });
</script>
@endsection 