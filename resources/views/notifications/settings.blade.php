@extends('layouts.app')

@section('title', 'Pengaturan Notifikasi')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Pengaturan Notifikasi</h1>
    </div>
    
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card shadow-sm border-0">
        <div class="card-body">
            <form action="{{ route('notifications.save-settings') }}" method="POST">
                @csrf
                
                <div class="mb-4">
                    <h5>Pengaturan API Fonnte WhatsApp</h5>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        Untuk menggunakan fitur notifikasi WhatsApp, Anda perlu mendaftarkan akun di <a href="https://fonnte.com" target="_blank">Fonnte</a> dan mendapatkan API key.
                    </div>
                    
                    <div class="mb-3">
                        <label for="fonnte_api_key" class="form-label">API Key Fonnte</label>
                        <input type="text" id="fonnte_api_key" name="fonnte_api_key" class="form-control"
                               value="{{ config('services.fonnte.api_key') }}" placeholder="Masukkan API key dari Fonnte">
                        <div class="form-text">Masukkan API key yang Anda dapatkan dari dashboard Fonnte.</div>
                    </div>
                    
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="enable_notifications" name="enable_notifications" value="1"
                               {{ config('services.fonnte.enable_notifications') ? 'checked' : '' }}>
                        <label class="form-check-label" for="enable_notifications">Aktifkan notifikasi WhatsApp</label>
                    </div>
                    
                    <div class="small text-muted mb-3">
                        Status saat ini: 
                        @if(config('services.fonnte.enable_notifications'))
                            <span class="text-success">Notifikasi aktif</span>
                        @else
                            <span class="text-danger">Notifikasi tidak aktif</span>
                        @endif
                    </div>
                </div>
                
                <h5 class="mb-3">Opsi Notifikasi</h5>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    Saat ini sistem dapat mengirimkan notifikasi WhatsApp untuk:
                    <ul class="mb-0 mt-2">
                        <li>Konfirmasi absen masuk</li>
                        <li>Konfirmasi absen pulang</li>
                        <li>Pengumuman (broadcast) dari admin</li>
                    </ul>
                </div>
                
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    Pastikan Anda telah menguji sistem notifikasi sebelum mengaktifkannya untuk semua pengguna.
                </div>
                
                <div class="d-flex justify-content-end mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i> Simpan Pengaturan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection 