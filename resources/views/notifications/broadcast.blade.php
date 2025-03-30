@extends('layouts.app')

@section('title', 'Broadcast Pesan WhatsApp')

@section('styles')
<!-- Select2 -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap4-theme@1.0.0/dist/select2-bootstrap4.min.css">
@endsection

@section('content')
<div class="container-fluid">
    <!-- Content Header -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Broadcast Pesan WhatsApp</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Broadcast Pesan</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    @if (!config('services.fonnte.enable_notifications'))
        <div class="alert alert-warning alert-dismissible">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
            <h5><i class="icon fas fa-exclamation-triangle"></i> Perhatian!</h5>
            Notifikasi WhatsApp saat ini dinonaktifkan. Untuk mengaktifkannya, silakan kunjungi 
            <a href="{{ route('notifications.settings') }}" class="alert-link">halaman pengaturan notifikasi</a>.
        </div>
    @endif

    <div class="row">
        <div class="col-md-8">
            <div class="card card-primary card-outline">
                <div class="card-header">
                    <h3 class="card-title">Kirim Pesan Broadcast</h3>
                </div>
                <div class="card-body">
                    <form action="{{ route('notifications.send-broadcast') }}" method="POST">
                        @csrf
                        
                        <div class="form-group">
                            <label for="role_id">Pilih Penerima</label>
                            <select class="form-control select2" id="role_id" name="role_id" style="width: 100%;">
                                <option value="">Semua Karyawan</option>
                                @foreach($roles as $role)
                                    <option value="{{ $role->id }}">{{ $role->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="message">Pesan</label>
                            <textarea class="form-control @error('message') is-invalid @enderror" id="message" name="message" rows="5" placeholder="Ketik pesan yang akan dikirim ke semua karyawan" required>{{ old('message') }}</textarea>
                            
                            <small class="form-text text-muted">
                                Pesan akan diawali dengan "📢 PENGUMUMAN" secara otomatis.
                            </small>
                            
                            @error('message')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                        
                        <div class="text-right mt-4">
                            <button type="submit" class="btn btn-primary" {{ !config('services.fonnte.enable_notifications') ? 'disabled' : '' }}>
                                <i class="fas fa-paper-plane mr-2"></i> Kirim Broadcast
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card card-info card-outline">
                <div class="card-header">
                    <h3 class="card-title">Informasi</h3>
                </div>
                <div class="card-body">
                    <div class="callout callout-info">
                        <h5><i class="fas fa-info-circle"></i> Tentang Fitur Ini</h5>
                        <p>Fitur ini memungkinkan Anda untuk mengirim pesan WhatsApp ke semua karyawan atau berdasarkan peran tertentu.</p>
                    </div>
                    
                    <div class="callout callout-warning">
                        <h5><i class="fas fa-bell"></i> Penting</h5>
                        <p>Hanya karyawan yang memiliki nomor telepon terdaftar dan mengaktifkan notifikasi WhatsApp yang akan menerima pesan.</p>
                    </div>
                    
                    <div class="callout callout-success">
                        <h5><i class="fas fa-lightbulb"></i> Tips</h5>
                        <p>Gunakan pesan yang singkat dan jelas. Hindari penggunaan terlalu banyak emoji atau formatting khusus.</p>
                    </div>
                    
                    <div class="card card-outline card-primary">
                        <div class="card-header">
                            <h3 class="card-title">Format Pesan yang Didukung</h3>
                        </div>
                        <div class="card-body p-2">
                            <ul class="pl-3 mb-0">
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
            placeholder: 'Pilih penerima',
            allowClear: true
        });
    });
</script>
@endsection 