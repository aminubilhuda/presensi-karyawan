@extends('layouts.app')

@section('title', 'Kelola Lokasi Absensi')

@section('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
    #map {
        height: 500px;
        width: 100%;
    }
    .location-card {
        transition: all 0.3s ease;
    }
    .location-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.1);
    }
</style>
@endsection

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Kelola Lokasi Absensi</h1>
        <a href="{{ route('admin.locations.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i> Tambah Lokasi
        </a>
    </div>

    <!-- Peta Lokasi -->
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="fas fa-map-marker-alt me-2"></i> Peta Lokasi Absensi</h5>
        </div>
        <div class="card-body">
            <div id="map"></div>
        </div>
    </div>

    <!-- Daftar Lokasi -->
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="fas fa-list me-2"></i> Daftar Lokasi Absensi</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama</th>
                            <th>Alamat</th>
                            <th>Koordinat</th>
                            <th>Radius</th>
                            <th>Status</th>
                            <th>Tindakan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($locations as $index => $location)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $location->name }}</td>
                                <td>{{ $location->address }}</td>
                                <td>
                                    <small>
                                        Lat: {{ $location->latitude }}<br>
                                        Long: {{ $location->longitude }}
                                    </small>
                                </td>
                                <td>{{ $location->radius }} meter</td>
                                <td>
                                    @if($location->is_active)
                                        <span class="badge bg-success">Aktif</span>
                                    @else
                                        <span class="badge bg-danger">Tidak Aktif</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('admin.locations.edit', $location) }}" class="btn btn-sm btn-warning">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('admin.locations.destroy', $location) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus lokasi ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center">Tidak ada data lokasi absensi.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <div class="card mt-4">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">Informasi Lokasi Absensi</h5>
        </div>
        <div class="card-body">
            <div class="alert alert-info">
                <p class="mb-0">
                    <i class="fas fa-info-circle me-2"></i>
                    Lokasi absensi digunakan untuk menentukan area di mana karyawan dapat melakukan absensi.
                    Karyawan hanya dapat melakukan absensi jika mereka berada dalam radius yang ditentukan
                    dari lokasi absensi yang aktif.
                </p>
            </div>
            <div class="alert alert-warning">
                <h5><i class="fas fa-exclamation-triangle me-2"></i>Catatan Penting:</h5>
                <ul class="mb-0">
                    <li>Radius lokasi diukur dalam meter dari titik koordinat</li>
                    <li>Pastikan radius cukup luas untuk mencakup area sekolah</li>
                    <li>Lokasi dengan status tidak aktif tidak akan digunakan dalam absensi</li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Inisialisasi peta
        const map = L.map('map').setView([-6.2088, 106.8456], 13); // Koordinat default Jakarta

        // Tambahkan layer peta
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(map);

        // Buat bound untuk menyesuaikan tampilan peta
        const bounds = L.latLngBounds();
        let hasLocations = false;

        // Tambahkan marker untuk setiap lokasi
        @foreach($locations as $location)
            // Tambahkan marker lokasi
            const marker = L.marker([{{ $location->latitude }}, {{ $location->longitude }}])
                .addTo(map)
                .bindPopup('<b>{{ $location->name }}</b><br>{{ $location->address }}<br>Radius: {{ $location->radius }} meter<br>Status: {{ $location->is_active ? "Aktif" : "Tidak Aktif" }}');
            
            // Tambahkan circle radius
            const circle = L.circle([{{ $location->latitude }}, {{ $location->longitude }}], {
                color: '{{ $location->is_active ? "blue" : "red" }}',
                fillColor: '{{ $location->is_active ? "#30f" : "#f03" }}',
                fillOpacity: 0.1,
                radius: {{ $location->radius }}
            }).addTo(map);
            
            // Tambahkan ke bounds
            bounds.extend([{{ $location->latitude }}, {{ $location->longitude }}]);
            hasLocations = true;
        @endforeach

        // Sesuaikan tampilan peta
        if (hasLocations) {
            map.fitBounds(bounds);
        } else {
            // Jika tidak ada lokasi, gunakan default
            map.setView([-6.2088, 106.8456], 13);
        }
    });
</script>
@endsection 