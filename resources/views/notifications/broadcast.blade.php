@extends('layouts.app')

@section('title', 'Broadcast Pesan WhatsApp')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Broadcast Pesan WhatsApp</h1>
    </div>

    @if (!config('services.fonnte.enable_notifications'))
        <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <strong>Perhatian:</strong> Notifikasi WhatsApp saat ini dinonaktifkan. Untuk mengaktifkannya, silakan kunjungi 
            <a href="{{ route('notifications.settings') }}" class="alert-link">halaman pengaturan notifikasi</a>.
        </div>
    @endif

    <div class="row">
        <div class="col-md-8">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Kirim Pesan Broadcast</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('notifications.send-broadcast') }}" method="POST">
                        @csrf
                        
                        <div class="mb-3">
                            <label for="role_id" class="form-label">Pilih Penerima</label>
                            <select class="form-select" id="role_id" name="role_id">
                                <option value="">Semua Karyawan</option>
                                @foreach($roles as $role)
                                    <option value="{{ $role->id }}">{{ $role->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="message" class="form-label">Pesan</label>
                            <textarea class="form-control @error('message') is-invalid @enderror" id="message" name="message" rows="5" placeholder="Ketik pesan yang akan dikirim ke semua karyawan" required>{{ old('message') }}</textarea>
                            
                            <div class="form-text">
                                Pesan akan diawali dengan "📢 PENGUMUMAN" secara otomatis.
                            </div>
                            
                            @error('message')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="d-flex justify-content-end mt-4">
                            <button type="submit" class="btn btn-primary" {{ !config('services.fonnte.enable_notifications') ? 'disabled' : '' }}>
                                <i class="fas fa-paper-plane me-2"></i> Kirim Broadcast
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Informasi</h5>
                </div>
                <div class="card-body">
                    <p>
                        <i class="fas fa-info-circle text-info me-2"></i>
                        Fitur ini memungkinkan Anda untuk mengirim pesan WhatsApp ke semua karyawan atau berdasarkan peran tertentu.
                    </p>
                    
                    <p>
                        <i class="fas fa-bell text-warning me-2"></i>
                        <strong>Penting:</strong> Hanya karyawan yang memiliki nomor telepon terdaftar dan mengaktifkan notifikasi WhatsApp yang akan menerima pesan.
                    </p>
                    
                    <p>
                        <i class="fas fa-lightbulb text-success me-2"></i>
                        <strong>Tips:</strong> Gunakan pesan yang singkat dan jelas. Hindari penggunaan terlalu banyak emoji atau formatting khusus.
                    </p>
                    
                    <hr>
                    
                    <h6>Format Pesan yang Didukung:</h6>
                    <ul class="small">
                        <li>*teks* untuk <strong>bold</strong></li>
                        <li>_teks_ untuk <em>italic</em></li>
                        <li>~teks~ untuk <del>strikethrough</del></li>
                        <li>```teks``` untuk kode</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 