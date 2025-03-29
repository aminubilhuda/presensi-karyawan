@extends('layouts.app')

@section('title', 'Riwayat Absensi ' . $user->name)

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Riwayat Absensi {{ $user->name }}</h1>
        <div>
            <a href="{{ route('admin.users.show', $user) }}" class="btn btn-secondary">
                <i class="fas fa-user me-1"></i> Detail Pengguna
            </a>
            <a href="{{ route('admin.attendance.monitor') }}" class="btn btn-primary ms-2">
                <i class="fas fa-arrow-left me-1"></i> Monitor Absensi
            </a>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-user me-2"></i> Informasi Pengguna
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-2 text-center">
                            <img src="{{ $user->photo ? asset('storage/' . $user->photo) : asset('images/avatar.png') }}" 
                                 class="rounded-circle img-thumbnail mb-2" width="100" height="100" alt="User Avatar">
                        </div>
                        <div class="col-md-5">
                            <h5>{{ $user->name }}</h5>
                            <p class="mb-1"><i class="fas fa-tag me-2"></i> <span class="badge bg-primary">{{ $user->role->name }}</span></p>
                            <p class="mb-1"><i class="fas fa-envelope me-2"></i> {{ $user->email }}</p>
                            <p class="mb-0"><i class="fas fa-phone me-2"></i> {{ $user->phone ?: 'Belum diatur' }}</p>
                        </div>
                        <div class="col-md-5">
                            <div class="card bg-light">
                                <div class="card-body p-3">
                                    <h6 class="mb-3">Statistik Bulan {{ date('F Y', mktime(0, 0, 0, $month, 1, $year)) }}</h6>
                                    <div class="row text-center">
                                        <div class="col-3">
                                            <div class="bg-success text-white rounded p-1 mb-1">
                                                <h5 class="mb-0">{{ $stats['present'] }}</h5>
                                            </div>
                                            <small>Hadir</small>
                                        </div>
                                        <div class="col-3">
                                            <div class="bg-warning text-white rounded p-1 mb-1">
                                                <h5 class="mb-0">{{ $stats['late'] }}</h5>
                                            </div>
                                            <small>Terlambat</small>
                                        </div>
                                        <div class="col-3">
                                            <div class="bg-danger text-white rounded p-1 mb-1">
                                                <h5 class="mb-0">{{ $stats['absent'] }}</h5>
                                            </div>
                                            <small>Absen</small>
                                        </div>
                                        <div class="col-3">
                                            <div class="bg-info text-white rounded p-1 mb-1">
                                                <h5 class="mb-0">{{ $stats['avg_duration'] }}</h5>
                                            </div>
                                            <small>Avg</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <form action="{{ route('admin.attendances.user', $user) }}" method="GET" class="row g-3 align-items-center">
                        <div class="col-auto">
                            <label for="month" class="form-label">Bulan</label>
                            <select name="month" id="month" class="form-select">
                                @foreach(range(1, 12) as $m)
                                    <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>
                                        {{ date('F', mktime(0, 0, 0, $m, 1)) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-auto">
                            <label for="year" class="form-label">Tahun</label>
                            <select name="year" id="year" class="form-select">
                                @foreach(range(date('Y') - 2, date('Y')) as $y)
                                    <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-auto align-self-end">
                            <button type="submit" class="btn btn-primary">Filter</button>
                        </div>
                        <div class="col-auto ms-auto align-self-end">
                            <a href="{{ route('admin.attendance.report.export', ['user_id' => $user->id, 'month' => $month, 'year' => $year]) }}" class="btn btn-success">
                                <i class="fas fa-file-excel me-1"></i> Export
                            </a>
                        </div>
                    </form>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th width="50">No</th>
                                    <th>Tanggal</th>
                                    <th>Status</th>
                                    <th>Masuk</th>
                                    <th>Pulang</th>
                                    <th>Durasi</th>
                                    <th>Lokasi</th>
                                    <th>Catatan</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($attendances as $index => $attendance)
                                    <tr>
                                        <td>{{ $attendances->firstItem() + $index }}</td>
                                        <td>{{ \Carbon\Carbon::parse($attendance->date)->format('d F Y') }}</td>
                                        <td>
                                            <span class="badge bg-{{ 
                                                $attendance->status == 'hadir' ? 'success' : 
                                                ($attendance->status == 'terlambat' ? 'warning' : 
                                                ($attendance->status == 'izin' ? 'info' : 
                                                ($attendance->status == 'sakit' ? 'primary' : 'danger'))) 
                                            }}">
                                                {{ ucfirst($attendance->status) }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($attendance->check_in_time)
                                                {{ \Carbon\Carbon::parse($attendance->check_in_time)->format('H:i') }}
                                                <button type="button" class="btn btn-sm btn-link" data-bs-toggle="modal" data-bs-target="#photoModal" data-photo="{{ asset('storage/' . $attendance->check_in_photo) }}" data-title="Foto Masuk - {{ \Carbon\Carbon::parse($attendance->date)->format('d/m/Y') }}">
                                                    <i class="fas fa-image"></i>
                                                </button>
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>
                                            @if($attendance->check_out_time)
                                                {{ \Carbon\Carbon::parse($attendance->check_out_time)->format('H:i') }}
                                                <button type="button" class="btn btn-sm btn-link" data-bs-toggle="modal" data-bs-target="#photoModal" data-photo="{{ asset('storage/' . $attendance->check_out_photo) }}" data-title="Foto Pulang - {{ \Carbon\Carbon::parse($attendance->date)->format('d/m/Y') }}">
                                                    <i class="fas fa-image"></i>
                                                </button>
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>{{ $attendance->formatted_duration ?? '-' }}</td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#mapModal" 
                                                data-check-in-lat="{{ $attendance->check_in_latitude }}" 
                                                data-check-in-lng="{{ $attendance->check_in_longitude }}"
                                                data-check-out-lat="{{ $attendance->check_out_latitude }}"
                                                data-check-out-lng="{{ $attendance->check_out_longitude }}">
                                                <i class="fas fa-map-marker-alt"></i>
                                            </button>
                                        </td>
                                        <td>{{ $attendance->notes ?? '-' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center">Tidak ada data absensi untuk bulan dan tahun yang dipilih.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    {{ $attendances->appends(['month' => $month, 'year' => $year])->links() }}
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal untuk menampilkan foto -->
<div class="modal fade" id="photoModal" tabindex="-1" aria-labelledby="photoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="photoModalLabel">Foto Absensi</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <img src="" alt="Foto Absensi" class="img-fluid" id="photoModalImage">
            </div>
        </div>
    </div>
</div>

<!-- Modal untuk menampilkan peta -->
<div class="modal fade" id="mapModal" tabindex="-1" aria-labelledby="mapModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="mapModalLabel">Lokasi Absensi</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="map" style="height: 400px;"></div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google_maps.api_key') }}&callback=initMap" async defer></script>
<script>
    // Untuk modal foto
    document.addEventListener('DOMContentLoaded', function () {
        var photoModal = document.getElementById('photoModal');
        photoModal.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget;
            var photo = button.getAttribute('data-photo');
            var title = button.getAttribute('data-title');
            
            var modalTitle = photoModal.querySelector('.modal-title');
            var modalImage = document.getElementById('photoModalImage');
            
            modalTitle.textContent = title;
            modalImage.src = photo;
        });
    });
    
    // Untuk modal peta
    var map, checkInMarker, checkOutMarker;
    
    function initMap() {
        map = new google.maps.Map(document.getElementById('map'), {
            center: { lat: -6.200000, lng: 106.816666 }, // Default Jakarta
            zoom: 15
        });
        
        document.getElementById('mapModal').addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget;
            
            var checkInLat = parseFloat(button.getAttribute('data-check-in-lat'));
            var checkInLng = parseFloat(button.getAttribute('data-check-in-lng'));
            var checkOutLat = parseFloat(button.getAttribute('data-check-out-lat'));
            var checkOutLng = parseFloat(button.getAttribute('data-check-out-lng'));
            
            // Hapus marker lama jika ada
            if (checkInMarker) checkInMarker.setMap(null);
            if (checkOutMarker) checkOutMarker.setMap(null);
            
            // Tambahkan marker untuk check-in
            if (!isNaN(checkInLat) && !isNaN(checkInLng)) {
                var checkInPosition = { lat: checkInLat, lng: checkInLng };
                checkInMarker = new google.maps.Marker({
                    position: checkInPosition,
                    map: map,
                    title: 'Lokasi Absen Masuk',
                    icon: {
                        url: 'http://maps.google.com/mapfiles/ms/icons/green-dot.png'
                    }
                });
                
                // Tambahkan info window
                var checkInInfo = new google.maps.InfoWindow({
                    content: '<div><strong>Absen Masuk</strong></div>'
                });
                
                checkInMarker.addListener('click', function() {
                    checkInInfo.open(map, checkInMarker);
                });
                
                // Center map ke posisi check-in
                map.setCenter(checkInPosition);
            }
            
            // Tambahkan marker untuk check-out jika ada
            if (!isNaN(checkOutLat) && !isNaN(checkOutLng)) {
                var checkOutPosition = { lat: checkOutLat, lng: checkOutLng };
                checkOutMarker = new google.maps.Marker({
                    position: checkOutPosition,
                    map: map,
                    title: 'Lokasi Absen Pulang',
                    icon: {
                        url: 'http://maps.google.com/mapfiles/ms/icons/red-dot.png'
                    }
                });
                
                // Tambahkan info window
                var checkOutInfo = new google.maps.InfoWindow({
                    content: '<div><strong>Absen Pulang</strong></div>'
                });
                
                checkOutMarker.addListener('click', function() {
                    checkOutInfo.open(map, checkOutMarker);
                });
            }
        });
    }
</script>
@endpush
@endsection 