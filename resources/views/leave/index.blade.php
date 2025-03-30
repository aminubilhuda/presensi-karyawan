@extends('layouts.app')

@section('title', 'Pengajuan Izin')

@section('styles')
<!-- Datepicker -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
@endsection

@section('content')
<div class="container">
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center flex-wrap">
            <h5 class="mb-0">Pengajuan Izin</h5>
            
            <a href="{{ route('leave.create') }}" class="btn btn-primary">
                <i class="fas fa-plus-circle mr-1"></i> Ajukan Izin Baru
            </a>
        </div>
        
        <div class="card-body">
            @if ($leaveRequests->isEmpty())
                <div class="text-center py-5">
                    <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                    <p>Belum ada pengajuan izin.</p>
                    <a href="{{ route('leave.create') }}" class="btn btn-outline-primary">
                        <i class="fas fa-plus-circle mr-1"></i> Ajukan Izin Baru
                    </a>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Tanggal Diajukan</th>
                                <th>Periode</th>
                                <th>Durasi</th>
                                <th>Jenis</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($leaveRequests as $leave)
                                <tr>
                                    <td>#{{ $leave->id }}</td>
                                    <td>{{ $leave->created_at->format('d M Y, H:i') }}</td>
                                    <td>
                                        {{ $leave->start_date->format('d M Y') }} - 
                                        {{ $leave->end_date->format('d M Y') }}
                                    </td>
                                    <td>{{ $leave->duration }} hari</td>
                                    <td>
                                        <span class="badge {{ $leave->type == 'sakit' ? 'bg-primary' : 'bg-info' }}">
                                            {{ ucfirst($leave->type) }}
                                        </span>
                                    </td>
                                    <td>
                                        @if ($leave->status == 'pending')
                                            <span class="badge bg-warning">Menunggu</span>
                                        @elseif ($leave->status == 'approved')
                                            <span class="badge bg-success">Disetujui</span>
                                        @else
                                            <span class="badge bg-danger">Ditolak</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="{{ route('leave.show', $leave) }}" class="btn btn-sm btn-info">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            
                                            @if ($leave->status == 'pending')
                                                <form action="{{ route('leave.cancel', $leave) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Apakah Anda yakin ingin membatalkan pengajuan izin ini?')">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                <div class="d-flex justify-content-center mt-4">
                    {{ $leaveRequests->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection 