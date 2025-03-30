@extends('layouts.app')

@section('title', 'Detail Pengguna')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Detail Pengguna</h1>
        <div>
            <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-warning me-2">
                <i class="fas fa-edit me-1"></i> Edit
            </a>
            <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-1"></i> Kembali
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-body text-center">
                    <img src="{{ $user->photo ? asset('storage/' . $user->photo) : asset('images/avatar.png') }}" 
                         class="rounded-circle img-fluid mb-3 shadow" width="150" height="150" alt="User Avatar">
                    <h4>{{ $user->name }}</h4>
                    <p>
                        <span class="badge bg-primary">{{ $user->role->name }}</span>
                    </p>
                    <div class="mt-3">
                        <a href="mailto:{{ $user->email }}" class="btn btn-sm btn-outline-secondary me-2">
                            <i class="fas fa-envelope me-1"></i> Email
                        </a>
                        @if($user->phone)
                        <a href="https://wa.me/{{ \App\Services\FonnteService::formatWhatsAppNumber($user->phone) }}" target="_blank" class="btn btn-sm btn-outline-success">
                            <i class="fab fa-whatsapp me-1"></i> WhatsApp
                        </a>
                        @endif
                    </div>
                </div>
            </div>
            
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Statistik Bulan Ini</h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-4">
                            <div class="bg-success text-white rounded py-2 mb-2">
                                <h3 class="mb-0">{{ $monthlyStats['present'] }}</h3>
                            </div>
                            <small>Hadir</small>
                        </div>
                        <div class="col-4">
                            <div class="bg-warning text-white rounded py-2 mb-2">
                                <h3 class="mb-0">{{ $monthlyStats['late'] }}</h3>
                            </div>
                            <small>Terlambat</small>
                        </div>
                        <div class="col-4">
                            <div class="bg-danger text-white rounded py-2 mb-2">
                                <h3 class="mb-0">{{ $monthlyStats['absent'] }}</h3>
                            </div>
                            <small>Tidak Hadir</small>
                        </div>
                    </div>
                </div>
                <div class="card-footer text-center">
                    <a href="{{ route('admin.attendances.user', $user->id) }}" class="btn btn-sm btn-primary">
                        <i class="fas fa-calendar-check me-1"></i> Lihat Semua Absensi
                    </a>
                </div>
            </div>
        </div>
        
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Informasi Pengguna</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-borderless">
                            <tbody>
                                <tr>
                                    <th width="200">ID</th>
                                    <td>{{ $user->id }}</td>
                                </tr>
                                <tr>
                                    <th>Nama</th>
                                    <td>{{ $user->name }}</td>
                                </tr>
                                <tr>
                                    <th>Email</th>
                                    <td>{{ $user->email }}</td>
                                </tr>
                                <tr>
                                    <th>Username</th>
                                    <td>{{ $user->username ?: 'Belum diatur' }}</td>
                                </tr>
                                <tr>
                                    <th>Peran</th>
                                    <td>{{ $user->role->name }}</td>
                                </tr>
                                <tr>
                                    <th>Nomor Telepon</th>
                                    <td>{{ $user->phone ?: 'Belum diatur' }}</td>
                                </tr>
                                <tr>
                                    <th>Notifikasi WhatsApp</th>
                                    <td>
                                        @if($user->wa_notifications)
                                            <span class="badge bg-success">Aktif</span>
                                        @else
                                            <span class="badge bg-secondary">Tidak Aktif</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>Email Terverifikasi</th>
                                    <td>{{ $user->email_verified_at ? date('d/m/Y H:i', strtotime($user->email_verified_at)) : 'Belum Terverifikasi' }}</td>
                                </tr>
                                <tr>
                                    <th>Terakhir Login</th>
                                    <td>{{ $user->last_login_at ? date('d/m/Y H:i', strtotime($user->last_login_at)) : 'Belum Pernah' }}</td>
                                </tr>
                                <tr>
                                    <th>Tanggal Dibuat</th>
                                    <td>{{ date('d/m/Y H:i', strtotime($user->created_at)) }}</td>
                                </tr>
                                <tr>
                                    <th>Terakhir Diperbarui</th>
                                    <td>{{ date('d/m/Y H:i', strtotime($user->updated_at)) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Riwayat Absensi Terakhir</h5>
                </div>
                <div class="card-body">
                    @if(count($recentAttendances) > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Tanggal</th>
                                        <th>Status</th>
                                        <th>Masuk</th>
                                        <th>Pulang</th>
                                        <th>Durasi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($recentAttendances as $attendance)
                                        <tr>
                                            <td>{{ \Carbon\Carbon::parse($attendance->date)->format('d/m/Y') }}</td>
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
                                            <td>{{ $attendance->check_in_time && $attendance->check_out_time ? $attendance->formatted_duration : '-' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="alert alert-info">
                            Belum ada data absensi untuk pengguna ini.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 