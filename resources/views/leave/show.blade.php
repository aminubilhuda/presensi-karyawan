@extends('layouts.app')

@section('title', 'Detail Permohonan Izin #' . $leave->id)

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Detail Permohonan Izin</h5>
                    
                    <a href="{{ route('leave.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-arrow-left mr-1"></i> Kembali
                    </a>
                </div>
                
                <div class="card-body">
                    <div class="bg-light p-3 rounded mb-3">
                        <div class="row align-items-center">
                            <div class="col-md-6">
                                <h6>ID Permohonan: #{{ $leave->id }}</h6>
                                <p class="mb-0 text-muted">Diajukan pada: {{ $leave->created_at->format('d M Y, H:i') }}</p>
                            </div>
                            <div class="col-md-6 text-md-end mt-2 mt-md-0">
                                <span class="badge 
                                    {{ $leave->status == 'pending' ? 'bg-warning' : 
                                    ($leave->status == 'approved' ? 'bg-success' : 'bg-danger') }} 
                                    fs-6 px-3 py-2">
                                    {{ $leave->status == 'pending' ? 'Menunggu Persetujuan' : 
                                        ($leave->status == 'approved' ? 'Disetujui' : 'Ditolak') }}
                                </span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h6 class="border-bottom pb-2">Informasi Permohonan</h6>
                            <dl class="row mb-0">
                                <dt class="col-sm-4">Jenis</dt>
                                <dd class="col-sm-8">
                                    <span class="badge {{ $leave->type == 'sakit' ? 'bg-primary' : 'bg-info' }}">
                                        {{ ucfirst($leave->type) }}
                                    </span>
                                </dd>
                                
                                <dt class="col-sm-4">Periode</dt>
                                <dd class="col-sm-8">
                                    {{ $leave->start_date->format('d M Y') }} - {{ $leave->end_date->format('d M Y') }}
                                </dd>
                                
                                <dt class="col-sm-4">Durasi</dt>
                                <dd class="col-sm-8">{{ $leave->duration }} hari</dd>
                            </dl>
                        </div>
                        
                        <div class="col-md-6">
                            <h6 class="border-bottom pb-2">Status Permohonan</h6>
                            <dl class="row mb-0">
                                @if ($leave->isApproved() || $leave->isRejected())
                                    <dt class="col-sm-5">Diproses oleh</dt>
                                    <dd class="col-sm-7">{{ $leave->approver->name ?? 'Admin' }}</dd>
                                    
                                    <dt class="col-sm-5">Diproses pada</dt>
                                    <dd class="col-sm-7">{{ $leave->approved_at ? $leave->approved_at->format('d M Y, H:i') : '-' }}</dd>
                                @else
                                    <dt class="col-sm-5">Status</dt>
                                    <dd class="col-sm-7">Menunggu persetujuan admin</dd>
                                @endif
                            </dl>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <h6 class="border-bottom pb-2">Alasan Permohonan</h6>
                        <p class="mb-0">{{ $leave->reason }}</p>
                    </div>
                    
                    @if ($leave->attachment)
                        <div class="mb-4">
                            <h6 class="border-bottom pb-2">Lampiran</h6>
                            <div class="mt-2">
                                @php
                                    $extension = pathinfo($leave->attachment, PATHINFO_EXTENSION);
                                    $isPDF = strtolower($extension) === 'pdf';
                                    $isDoc = in_array(strtolower($extension), ['doc', 'docx']);
                                    $isImage = in_array(strtolower($extension), ['jpg', 'jpeg', 'png']);
                                @endphp
                                
                                @if ($isImage)
                                    <div class="mb-2">
                                        <img src="{{ asset('storage/' . $leave->attachment) }}" alt="Lampiran" class="img-fluid rounded" style="max-height: 300px;">
                                    </div>
                                @endif
                                
                                <a href="{{ asset('storage/' . $leave->attachment) }}" class="btn btn-sm btn-outline-primary" target="_blank">
                                    <i class="fas {{ $isPDF ? 'fa-file-pdf' : ($isDoc ? 'fa-file-word' : 'fa-file') }} mr-1"></i>
                                    Lihat Lampiran
                                </a>
                            </div>
                        </div>
                    @endif
                    
                    @if ($leave->admin_notes)
                        <div class="mb-4">
                            <h6 class="border-bottom pb-2">Catatan Admin</h6>
                            <div class="alert {{ $leave->isApproved() ? 'alert-success' : 'alert-danger' }} mb-0">
                                {{ $leave->admin_notes }}
                            </div>
                        </div>
                    @endif
                    
                    @if ($leave->isPending())
                        <div class="mt-4 d-flex justify-content-between">
                            <button type="button" class="btn btn-outline-secondary" onclick="window.history.back()">
                                <i class="fas fa-arrow-left mr-1"></i> Kembali
                            </button>
                            
                            <form action="{{ route('leave.cancel', $leave) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger" onclick="return confirm('Apakah Anda yakin ingin membatalkan pengajuan izin ini?')">
                                    <i class="fas fa-times mr-1"></i> Batalkan Permohonan
                                </button>
                            </form>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 