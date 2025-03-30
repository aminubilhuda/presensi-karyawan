@extends('layouts.app')

@section('title', 'Kelola Perizinan')

@section('styles')
<!-- Select2 -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap4-theme@1.0.0/dist/select2-bootstrap4.min.css">
<!-- Datepicker -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<style>
    .select2-container {
        min-width: 150px;
        width: 100% !important;
    }
    
    .filter-row {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
    }
    
    .filter-col {
        flex: 1;
        min-width: 150px;
    }
    
    .badge-lg {
        font-size: 14px;
        padding: 6px 10px;
    }
    
    @media (max-width: 768px) {
        .filter-col {
            min-width: 100%;
        }
    }
</style>
@endsection

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header bg-white">
            <h5 class="mb-0">Kelola Perizinan</h5>
        </div>
        
        <div class="card-body">
            <form action="{{ route('admin.leave.index') }}" method="GET" class="mb-4">
                <div class="filter-row mb-3">
                    <div class="filter-col">
                        <label for="user_id" class="form-label">Pengguna</label>
                        <select name="user_id" id="user_id" class="form-select select2">
                            <option value="">Semua Pengguna</option>
                            @foreach ($users as $user)
                                <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                                    {{ $user->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="filter-col">
                        <label for="status" class="form-label">Status</label>
                        <select name="status" id="status" class="form-select select2">
                            <option value="">Semua Status</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Menunggu</option>
                            <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Disetujui</option>
                            <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Ditolak</option>
                        </select>
                    </div>
                    
                    <div class="filter-col">
                        <label for="type" class="form-label">Jenis</label>
                        <select name="type" id="type" class="form-select select2">
                            <option value="">Semua Jenis</option>
                            <option value="izin" {{ request('type') == 'izin' ? 'selected' : '' }}>Izin</option>
                            <option value="sakit" {{ request('type') == 'sakit' ? 'selected' : '' }}>Sakit</option>
                        </select>
                    </div>
                </div>
                
                <div class="filter-row mb-3">
                    <div class="filter-col">
                        <label for="date_from" class="form-label">Dari Tanggal</label>
                        <input type="text" class="form-control datepicker" id="date_from" name="date_from" 
                               value="{{ request('date_from') }}" placeholder="YYYY-MM-DD">
                    </div>
                    
                    <div class="filter-col">
                        <label for="date_to" class="form-label">Sampai Tanggal</label>
                        <input type="text" class="form-control datepicker" id="date_to" name="date_to" 
                               value="{{ request('date_to') }}" placeholder="YYYY-MM-DD">
                    </div>
                    
                    <div class="filter-col d-flex align-items-end">
                        <div class="w-100">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-search mr-1"></i> Filter
                            </button>
                        </div>
                    </div>
                </div>
            </form>
            
            <div class="d-flex justify-content-between mb-3 flex-wrap">
                <div class="mb-2">
                    <span class="badge bg-warning badge-lg me-2">
                        <i class="fas fa-clock mr-1"></i> Menunggu: {{ $leaveRequests->where('status', 'pending')->count() }}
                    </span>
                    <span class="badge bg-success badge-lg me-2">
                        <i class="fas fa-check mr-1"></i> Disetujui: {{ $leaveRequests->where('status', 'approved')->count() }}
                    </span>
                    <span class="badge bg-danger badge-lg">
                        <i class="fas fa-times mr-1"></i> Ditolak: {{ $leaveRequests->where('status', 'rejected')->count() }}
                    </span>
                </div>
                
                <div class="mb-2">
                    <a href="{{ route('admin.leave.export', request()->all()) }}" class="btn btn-success btn-sm">
                        <i class="fas fa-file-excel mr-1"></i> Export Excel
                    </a>
                </div>
            </div>
            
            @if ($leaveRequests->isEmpty())
                <div class="text-center py-5">
                    <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                    <p>Tidak ada data permohonan izin yang ditemukan.</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Pengguna</th>
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
                                    <td>{{ $leave->user->name }}</td>
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
                                            <a href="{{ route('admin.leave.show', $leave) }}" class="btn btn-sm btn-info">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            
                                            @if ($leave->status == 'pending')
                                                <button type="button" class="btn btn-sm btn-success" data-toggle="modal" data-target="#approveModal{{ $leave->id }}">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                                
                                                <button type="button" class="btn btn-sm btn-danger" data-toggle="modal" data-target="#rejectModal{{ $leave->id }}">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                
                                <!-- Modal Approve -->
                                <div class="modal fade" id="approveModal{{ $leave->id }}" tabindex="-1" role="dialog" aria-labelledby="approveModalLabel{{ $leave->id }}" aria-hidden="true">
                                    <div class="modal-dialog" role="document">
                                        <div class="modal-content">
                                            <form action="{{ route('admin.leave.approve', $leave) }}" method="POST">
                                                @csrf
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="approveModalLabel{{ $leave->id }}">Setujui Permohonan Izin</h5>
                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                        <span aria-hidden="true">&times;</span>
                                                    </button>
                                                </div>
                                                <div class="modal-body">
                                                    <p>Anda akan menyetujui permohonan izin #{{ $leave->id }} dari {{ $leave->user->name }}.</p>
                                                    
                                                    <div class="form-group mb-0">
                                                        <label for="admin_notes_approve_{{ $leave->id }}">Catatan (Opsional)</label>
                                                        <textarea class="form-control" id="admin_notes_approve_{{ $leave->id }}" name="admin_notes" rows="3" placeholder="Tambahkan catatan jika diperlukan"></textarea>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                                                    <button type="submit" class="btn btn-success">Setujui</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Modal Reject -->
                                <div class="modal fade" id="rejectModal{{ $leave->id }}" tabindex="-1" role="dialog" aria-labelledby="rejectModalLabel{{ $leave->id }}" aria-hidden="true">
                                    <div class="modal-dialog" role="document">
                                        <div class="modal-content">
                                            <form action="{{ route('admin.leave.reject', $leave) }}" method="POST">
                                                @csrf
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="rejectModalLabel{{ $leave->id }}">Tolak Permohonan Izin</h5>
                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                        <span aria-hidden="true">&times;</span>
                                                    </button>
                                                </div>
                                                <div class="modal-body">
                                                    <p>Anda akan menolak permohonan izin #{{ $leave->id }} dari {{ $leave->user->name }}.</p>
                                                    
                                                    <div class="form-group mb-0">
                                                        <label for="admin_notes_reject_{{ $leave->id }}">Alasan Penolakan <span class="text-danger">*</span></label>
                                                        <textarea class="form-control" id="admin_notes_reject_{{ $leave->id }}" name="admin_notes" rows="3" placeholder="Berikan alasan penolakan" required></textarea>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                                                    <button type="submit" class="btn btn-danger">Tolak</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                <div class="d-flex justify-content-center mt-4">
                    {{ $leaveRequests->appends(request()->query())->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@section('scripts')
<!-- Select2 -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<!-- Flatpickr -->
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
    $(function () {
        // Initialize Select2
        $('.select2').select2({
            theme: 'bootstrap4'
        });
        
        // Initialize datepicker
        flatpickr(".datepicker", {
            dateFormat: "Y-m-d"
        });
    });
</script>
@endsection 