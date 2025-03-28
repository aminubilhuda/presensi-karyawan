@extends('layouts.app')

@section('title', 'Monitoring Absensi')

@section('styles')
<style>
    #map {
        height: 500px;
        width: 100%;
    }
    .attendance-card {
        transition: all 0.3s ease;
    }
    .attendance-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.1);
    }
    .stat-card {
        border-radius: 10px;
        overflow: hidden;
    }
    .stat-icon {
        font-size: 2rem;
        padding: 15px;
        border-radius: 50%;
        margin-right: 15px;
    }
</style>
@endsection

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Monitoring Absensi</h1>
        <div>
            <span class="badge bg-primary">{{ date('d F Y', strtotime($today)) }}</span>
            <button class="btn btn-sm btn-secondary ms-2" id="refreshBtn">
                <i class="fas fa-sync-alt me-1"></i> Refresh
            </button>
        </div>
    </div>

    <!-- Statistik -->
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="card stat-card bg-primary text-white h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="stat-icon bg-white text-primary">
                        <i class="fas fa-users"></i>
                    </div>
                    <div>
                        <h6 class="card-title mb-0">Total Pengguna</h6>
                        <h2 class="my-2">{{ $stats['total_users'] }}</h2>
                        <p class="card-text mb-0">Guru & Staf TU</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card stat-card bg-success text-white h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="stat-icon bg-white text-success">
                        <i class="fas fa-sign-in-alt"></i>
                    </div>
                    <div>
                        <h6 class="card-title mb-0">Tepat Waktu</h6>
                        <h2 class="my-2">{{ $stats['on_time'] }}</h2>
                        <p class="card-text mb-0">{{ round(($stats['on_time'] / max(1, $stats['total_users'])) * 100) }}% dari total</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card stat-card bg-warning text-dark h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="stat-icon bg-white text-warning">
                        <i class="fas fa-user-times"></i>
                    </div>
                    <div>
                        <h6 class="card-title mb-0">Terlambat</h6>
                        <h2 class="my-2">{{ $stats['late'] }}</h2>
                        <p class="card-text mb-0">{{ round(($stats['late'] / max(1, $stats['total_users'])) * 100) }}% dari total</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card stat-card bg-danger text-white h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="stat-icon bg-white text-danger">
                        <i class="fas fa-user-times"></i>
                    </div>
                    <div>
                        <h6 class="card-title mb-0">Belum Hadir</h6>
                        <h2 class="my-2">{{ $stats['not_present'] }}</h2>
                        <p class="card-text mb-0">{{ round(($stats['not_present'] / max(1, $stats['total_users'])) * 100) }}% dari total</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Peta Lokasi -->
        <div class="col-lg-8 mb-4">
            <div class="card h-100">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-map-marker-alt me-2"></i> Peta Lokasi Absensi</h5>
                </div>
                <div class="card-body">
                    <div id="map"></div>
                </div>
            </div>
        </div>

        <!-- Daftar Absensi Hari Ini -->
        <div class="col-lg-4 mb-4">
            <div class="card h-100">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-clipboard-list me-2"></i> Absensi Hari Ini</h5>
                </div>
                <div class="card-body">
                    <div style="max-height: 460px; overflow-y: auto;">
                        @if(count($attendancesToday) > 0)
                            @foreach($attendancesToday as $attendance)
                                <div class="card attendance-card mb-3 border-{{ $attendance->check_out_time ? 'success' : 'warning' }}">
                                    <div class="card-body p-3">
                                        <div class="d-flex justify-content-between">
                                            <div>
                                                <h6 class="card-title mb-1">{{ $attendance->user->name }}</h6>
                                                <p class="card-text text-muted small mb-0">{{ $attendance->user->role->name }}</p>
                                            </div>
                                            <div class="text-end">
                                                @if($attendance->status === 'terlambat')
                                                    <span class="badge bg-warning">Terlambat</span>
                                                @elseif($attendance->status === 'tepat_waktu')
                                                    <span class="badge bg-success">Tepat Waktu</span>
                                                @else
                                                    <span class="badge bg-secondary">{{ ucfirst($attendance->status) }}</span>
                                                @endif
                                            </div>
                                        </div>
                                        <hr class="my-2">
                                        <div class="row text-center">
                                            <div class="col-6">
                                                <div class="small text-muted">Check-In</div>
                                                <div>{{ date('H:i', strtotime($attendance->check_in_time)) }}</div>
                                            </div>
                                            <div class="col-6">
                                                <div class="small text-muted">Check-Out</div>
                                                <div>{{ $attendance->check_out_time ? date('H:i', strtotime($attendance->check_out_time)) : '-' }}</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <div class="text-center my-5">
                                <i class="fas fa-clock text-muted mb-3" style="font-size: 3rem;"></i>
                                <p>Belum ada absensi hari ini</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabel Absensi -->
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="fas fa-table me-2"></i> Daftar Detail Absensi Hari Ini</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama</th>
                            <th>Peran</th>
                            <th>Jam Masuk</th>
                            <th>Lokasi Masuk</th>
                            <th>Jam Pulang</th>
                            <th>Lokasi Pulang</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if(count($attendancesToday) > 0)
                            @foreach($attendancesToday as $index => $attendance)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $attendance->user->name }}</td>
                                    <td>{{ $attendance->user->role->name }}</td>
                                    <td>{{ date('H:i', strtotime($attendance->check_in_time)) }}</td>
                                    <td>
                                        <small>
                                            Lat: {{ $attendance->check_in_latitude }}<br>
                                            Long: {{ $attendance->check_in_longitude }}
                                        </small>
                                    </td>
                                    <td>{{ $attendance->check_out_time ? date('H:i', strtotime($attendance->check_out_time)) : '-' }}</td>
                                    <td>
                                        @if($attendance->check_out_time)
                                            <small>
                                                Lat: {{ $attendance->check_out_latitude }}<br>
                                                Long: {{ $attendance->check_out_longitude }}
                                            </small>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>
                                        @if($attendance->status === 'terlambat')
                                            <span class="badge bg-warning">Terlambat</span>
                                        @elseif($attendance->status === 'tepat_waktu')
                                            <span class="badge bg-success">Tepat Waktu</span>
                                        @else
                                            <span class="badge bg-secondary">{{ ucfirst($attendance->status) }}</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="8" class="text-center">Belum ada data absensi hari ini.</td>
                            </tr>
                        @endif
                    </tbody>
                </table>
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

        // Tambahkan marker lokasi absensi
        const locations = @json($mapData['locations']);
        const attendances = @json($mapData['attendances']);

        // Buat bound untuk menyesuaikan tampilan peta
        const bounds = L.latLngBounds();

        // Tambahkan lokasi absensi ke peta
        if (locations.length > 0) {
            locations.forEach(location => {
                // Tambahkan marker lokasi
                const marker = L.marker([location.latitude, location.longitude])
                    .addTo(map)
                    .bindPopup(`<b>${location.name}</b><br>Radius: ${location.radius} meter`);
                
                // Tambahkan circle radius
                const circle = L.circle([location.latitude, location.longitude], {
                    color: 'blue',
                    fillColor: '#30f',
                    fillOpacity: 0.1,
                    radius: location.radius
                }).addTo(map);
                
                // Tambahkan ke bounds
                bounds.extend([location.latitude, location.longitude]);
            });
        }

        // Tambahkan marker absensi ke peta
        if (attendances.length > 0) {
            attendances.forEach(attendance => {
                // Marker check-in
                if (attendance.check_in_latitude && attendance.check_in_longitude) {
                    const checkInMarker = L.marker([attendance.check_in_latitude, attendance.check_in_longitude], {
                        icon: L.divIcon({
                            className: 'custom-div-icon',
                            html: `<div style="background-color: #28a745; width: 10px; height: 10px; border-radius: 50%;"></div>`,
                            iconSize: [10, 10]
                        })
                    }).addTo(map)
                    .bindPopup(`<b>${attendance.user_name}</b><br>Check-in: ${attendance.check_in_time}`);
                    
                    // Tambahkan ke bounds
                    bounds.extend([attendance.check_in_latitude, attendance.check_in_longitude]);
                }
                
                // Marker check-out
                if (attendance.check_out_time && attendance.check_out_latitude && attendance.check_out_longitude) {
                    const checkOutMarker = L.marker([attendance.check_out_latitude, attendance.check_out_longitude], {
                        icon: L.divIcon({
                            className: 'custom-div-icon',
                            html: `<div style="background-color: #dc3545; width: 10px; height: 10px; border-radius: 50%;"></div>`,
                            iconSize: [10, 10]
                        })
                    }).addTo(map)
                    .bindPopup(`<b>${attendance.user_name}</b><br>Check-out: ${attendance.check_out_time}`);
                    
                    // Tambahkan ke bounds
                    bounds.extend([attendance.check_out_latitude, attendance.check_out_longitude]);
                }
            });
        }

        // Sesuaikan tampilan peta
        if (!bounds.isValid()) {
            // Jika tidak ada bounds valid, gunakan default
            map.setView([-6.2088, 106.8456], 13);
        } else {
            map.fitBounds(bounds);
        }

        // Refresh halaman
        document.getElementById('refreshBtn').addEventListener('click', function() {
            location.reload();
        });
    });
</script>
@endsection 