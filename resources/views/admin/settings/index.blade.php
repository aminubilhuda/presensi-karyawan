@extends('layouts.app')

@section('title', 'Pengaturan')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-cogs me-2"></i> Pengaturan Sistem</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.settings.update') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        
                        <!-- Pengaturan Aplikasi -->
                        <div class="mb-4">
                            <h6 class="border-bottom pb-2 mb-3">Pengaturan Aplikasi</h6>
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="app_name" class="form-label">Nama Aplikasi</label>
                                    <input type="text" class="form-control @error('app_name') is-invalid @enderror" 
                                           id="app_name" name="app_name" 
                                           value="{{ old('app_name', $settings['app_name'] ?? 'Sistem Absensi Sekolah') }}" required>
                                    @error('app_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">Nama aplikasi yang akan ditampilkan di browser dan halaman</small>
                                </div>
                                
                                <div class="col-md-6">
                                    <label for="app_description" class="form-label">Deskripsi Aplikasi</label>
                                    <input type="text" class="form-control @error('app_description') is-invalid @enderror" 
                                           id="app_description" name="app_description" 
                                           value="{{ old('app_description', $settings['app_description'] ?? 'Aplikasi manajemen absensi karyawan') }}">
                                    @error('app_description')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">Deskripsi singkat aplikasi</small>
                                </div>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="app_logo" class="form-label">Logo Aplikasi</label>
                                    
                                    @if($settings['app_logo'])
                                        <div class="mb-2">
                                            <img src="{{ asset('storage/' . $settings['app_logo']) }}" 
                                                 alt="Logo Aplikasi" class="img-thumbnail" style="max-height: 100px;">
                                            <div class="form-check mt-1">
                                                <input class="form-check-input" type="checkbox" id="remove_logo" name="remove_logo" value="1">
                                                <label class="form-check-label" for="remove_logo">
                                                    Hapus logo
                                                </label>
                                            </div>
                                        </div>
                                    @endif
                                    
                                    <input type="file" class="form-control @error('app_logo') is-invalid @enderror" 
                                           id="app_logo" name="app_logo" accept="image/*">
                                    @error('app_logo')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">Format: JPG, PNG, JPEG, SVG (maks. 2MB)</small>
                                </div>
                                
                                <div class="col-md-6">
                                    <label for="app_favicon" class="form-label">Favicon Aplikasi</label>
                                    
                                    @if($settings['app_favicon'])
                                        <div class="mb-2">
                                            <img src="{{ asset('storage/' . $settings['app_favicon']) }}" 
                                                 alt="Favicon Aplikasi" class="img-thumbnail" style="max-height: 50px;">
                                            <div class="form-check mt-1">
                                                <input class="form-check-input" type="checkbox" id="remove_favicon" name="remove_favicon" value="1">
                                                <label class="form-check-label" for="remove_favicon">
                                                    Hapus favicon
                                                </label>
                                            </div>
                                        </div>
                                    @endif
                                    
                                    <input type="file" class="form-control @error('app_favicon') is-invalid @enderror" 
                                           id="app_favicon" name="app_favicon" accept="image/x-icon,image/png">
                                    @error('app_favicon')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">Format: ICO, PNG (maks. 1MB)</small>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Jam Kerja -->
                        <div class="mb-4">
                            <h6 class="border-bottom pb-2 mb-3">Pengaturan Jam Kerja</h6>
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="check_in_time" class="form-label">Jam Masuk</label>
                                    <input type="time" class="form-control @error('check_in_time') is-invalid @enderror" 
                                           id="check_in_time" name="check_in_time" 
                                           value="{{ old('check_in_time', $settings['check_in_time'] ?? '08:00') }}" required>
                                    @error('check_in_time')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">Jam masuk yang diizinkan untuk absensi</small>
                                </div>
                                
                                <div class="col-md-6">
                                    <label for="check_out_time" class="form-label">Jam Pulang</label>
                                    <input type="time" class="form-control @error('check_out_time') is-invalid @enderror" 
                                           id="check_out_time" name="check_out_time" 
                                           value="{{ old('check_out_time', $settings['check_out_time'] ?? '16:00') }}" required>
                                    @error('check_out_time')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">Jam pulang yang diizinkan untuk absensi</small>
                                </div>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="late_threshold" class="form-label">Batas Keterlambatan (Menit)</label>
                                    <input type="number" class="form-control @error('late_threshold') is-invalid @enderror" 
                                           id="late_threshold" name="late_threshold" 
                                           value="{{ old('late_threshold', $settings['late_threshold'] ?? 15) }}" required>
                                    @error('late_threshold')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">Batas waktu keterlambatan dalam menit setelah jam masuk</small>
                                </div>
                                
                                <div class="col-md-6">
                                    <label for="early_leave_threshold" class="form-label">Batas Pulang Cepat (Menit)</label>
                                    <input type="number" class="form-control @error('early_leave_threshold') is-invalid @enderror" 
                                           id="early_leave_threshold" name="early_leave_threshold" 
                                           value="{{ old('early_leave_threshold', $settings['early_leave_threshold'] ?? 15) }}" required>
                                    @error('early_leave_threshold')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">Batas waktu pulang cepat dalam menit sebelum jam pulang</small>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Pengaturan Lokasi -->
                        <div class="mb-4">
                            <h6 class="border-bottom pb-2 mb-3">Pengaturan Lokasi Absensi</h6>
                            
                            <div class="mb-3">
                                <label for="default_radius" class="form-label">Radius Default (Meter)</label>
                                <input type="number" class="form-control @error('default_radius') is-invalid @enderror" 
                                       id="default_radius" name="default_radius" 
                                       value="{{ old('default_radius', $settings['default_radius'] ?? 100) }}" required>
                                @error('default_radius')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Radius default untuk lokasi absensi baru</small>
                            </div>
                        </div>
                        
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i> Simpan Pengaturan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 