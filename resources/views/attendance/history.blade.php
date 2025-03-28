@extends('layouts.app')

@section('title', 'Riwayat Absensi')

@section('content')
<div class="container">
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Riwayat Absensi</h5>
            
            <form action="{{ route('attendance.history') }}" method="GET" class="d-flex gap-2">
                <select name="month" class="form-select">
                    @for ($i = 1; $i <= 12; $i++)
                        <option value="{{ $i }}" {{ $month == $i ? 'selected' : '' }}>
                            {{ \Carbon\Carbon::create(null, $i, 1)->format('F') }}
                        </option>
                    @endfor
                </select>
                
                <select name="year" class="form-select">
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