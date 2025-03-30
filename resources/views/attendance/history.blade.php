@extends('layouts.app')

@section('title', 'Riwayat Absensi')

@section('styles')
<!-- Select2 -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap4-theme@1.0.0/dist/select2-bootstrap4.min.css">
<style>
    .select2-container {
        min-width: 150px;
        width: auto !important;
    }
    
    .filter-form {
        display: flex;
        gap: 10px;
        align-items: center;
        flex-wrap: wrap;
    }
    
    @media (max-width: 576px) {
        .filter-form {
            flex-direction: column;
            align-items: stretch;
        }
        
        .select2-container {
            width: 100% !important;
        }
    }
</style>
@endsection

@section('content')
<div class="container">
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center flex-wrap">
            <h5 class="mb-0">Riwayat Absensi</h5>
            
            <form action="{{ route('attendance.history') }}" method="GET" class="filter-form">
                <select name="month" class="form-select select2">
                    @php
                    $monthNames = [
                        1 => 'Januari',
                        2 => 'Februari',
                        3 => 'Maret',
                        4 => 'April',
                        5 => 'Mei',
                        6 => 'Juni',
                        7 => 'Juli',
                        8 => 'Agustus',
                        9 => 'September',
                        10 => 'Oktober',
                        11 => 'November',
                        12 => 'Desember'
                    ];
                    @endphp
                    
                    @foreach($monthNames as $monthNumber => $monthName)
                        <option value="{{ $monthNumber }}" {{ $month == $monthNumber ? 'selected' : '' }}>
                            {{ $monthName }}
                        </option>
                    @endforeach
                </select>
                
                <select name="year" class="form-select select2">
                    @for ($i = now()->year; $i >= now()->year - 5; $i--)
                        <option value="{{ $i }}" {{ $year == $i ? 'selected' : '' }}>
                            {{ $i }}
                        </option>
                    @endfor
                </select>
                
                <button type="submit" class="btn btn-primary">Filter</button>
            </form>
        </div>
        
        <div class="card-body">
            @if ($attendances->isEmpty())
                <div class="text-center py-5">
                    <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                    <p>Tidak ada data absensi untuk periode yang dipilih.</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>Status</th>
                                <th>Jam Masuk</th>
                                <th>Jam Pulang</th>
                                <th>Foto Masuk</th>
                                <th>Foto Pulang</th>
                                <th>Catatan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($attendances as $attendance)
                                <tr>
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
                                    <td>{{ $attendance->check_in_time ? \Carbon\Carbon::parse($attendance->check_in_time)->format('H:i') : '-' }}</td>
                                    <td>{{ $attendance->check_out_time ? \Carbon\Carbon::parse($attendance->check_out_time)->format('H:i') : '-' }}</td>
                                    <td>
                                        @if ($attendance->check_in_photo)
                                            <a href="{{ asset('storage/' . $attendance->check_in_photo) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-image"></i> Lihat
                                            </a>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>
                                        @if ($attendance->check_out_photo)
                                            <a href="{{ asset('storage/' . $attendance->check_out_photo) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-image"></i> Lihat
                                            </a>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>{{ $attendance->notes ?: '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                <div class="d-flex justify-content-center mt-4">
                    {{ $attendances->appends(['month' => $month, 'year' => $year])->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@section('scripts')
<!-- Select2 -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(function () {
        //Initialize Select2 Elements
        $('.select2').select2({
            theme: 'bootstrap4',
            minimumResultsForSearch: -1, // Menyembunyikan search box
            width: 'resolve' // Gunakan lebar yang diatur oleh CSS
        });
    });
</script>
@endsection 