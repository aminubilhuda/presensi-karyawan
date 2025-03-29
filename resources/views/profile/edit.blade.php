@extends('layouts.app')

@section('title', 'Edit Profil')

@section('content')
<div class="container">
    <h1 class="mb-4">Profil Saya</h1>
    
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
    
    <div class="row">
        <div class="col-md-4 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center p-4">
                    <div class="mb-3">
                        <img src="{{ $user->photo ? asset('storage/' . $user->photo) : asset('images/avatar.png') }}" 
                             class="rounded-circle img-thumbnail" width="150" height="150" alt="{{ $user->name }}">
                    </div>
                    <h5 class="card-title">{{ $user->name }}</h5>
                    <p class="text-muted">{{ $user->role->name }}</p>
                    
                    @if($user->phone)
                        <p class="badge bg-success"><i class="fas fa-phone me-1"></i> WA terhubung</p>
                    @else
                        <p class="badge bg-secondary"><i class="fas fa-phone-slash me-1"></i> WA tidak terhubung</p>
                    @endif
                </div>
            </div>
        </div>
        
        <div class="col-md-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Informasi Umum</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        
                        <div class="mb-3">
                            <label for="name" class="form-label">Nama</label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $user->name) }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email', $user->email) }}" required>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="mb-3">
                            <label for="phone" class="form-label">Nomor WhatsApp</label>
                            <div class="input-group">
                                <span class="input-group-text">+62</span>
                                <input type="text" class="form-control @error('phone') is-invalid @enderror" id="phone" name="phone" value="{{ old('phone', $user->phone) }}" placeholder="81234567890 (tanpa angka 0 di depan)" pattern="[1-9][0-9]{8,15}">
                            </div>
                            <div class="form-text">Format: 81234567890 (tanpa angka 0 di depan dan tanpa +62)</div>
                            @error('phone')
                                <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="wa_notifications" name="wa_notifications" value="1" {{ old('wa_notifications', $user->wa_notifications) ? 'checked' : '' }}>
                            <label class="form-check-label" for="wa_notifications">Aktifkan notifikasi WhatsApp untuk absensi</label>
                        </div>
                        
                        <div class="mb-3">
                            <label for="photo" class="form-label">Foto Profil</label>
                            <input type="file" class="form-control @error('photo') is-invalid @enderror" id="photo" name="photo">
                            <div class="form-text">Unggah foto baru untuk mengganti foto profil Anda. Format yang didukung: JPG, PNG (Maks. 2MB)</div>
                            @error('photo')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="text-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i> Simpan Perubahan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Ubah Password</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('profile.password') }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="mb-3">
                            <label for="current_password" class="form-label">Password Saat Ini</label>
                            <input type="password" class="form-control @error('current_password') is-invalid @enderror" id="current_password" name="current_password" required>
                            @error('current_password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="mb-3">
                            <label for="password" class="form-label">Password Baru</label>
                            <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" required>
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="mb-3">
                            <label for="password_confirmation" class="form-label">Konfirmasi Password Baru</label>
                            <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" required>
                        </div>
                        
                        <div class="text-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-key me-2"></i> Ubah Password
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            
            <div class="card border-0 shadow-sm mt-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Autentikasi Dua Faktor</h5>
                </div>
                <div class="card-body">
                    <p class="card-text">
                        Autentikasi dua faktor menambahkan lapisan keamanan tambahan ke akun Anda dengan memerlukan lebih dari sekadar kata sandi untuk masuk.
                    </p>
                    
                    @if(auth()->user()->two_factor_enabled)
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle me-2"></i> Autentikasi dua faktor saat ini <strong>diaktifkan</strong> untuk akun Anda.
                        </div>
                        
                        <form action="{{ route('two-factor.disable') }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger" onclick="return confirm('Apakah Anda yakin ingin menonaktifkan autentikasi dua faktor?')">
                                <i class="fas fa-times-circle me-2"></i> Nonaktifkan Autentikasi Dua Faktor
                            </button>
                        </form>
                    @else
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle me-2"></i> Autentikasi dua faktor saat ini <strong>tidak diaktifkan</strong> untuk akun Anda.
                        </div>
                        
                        <a href="{{ route('two-factor.enable') }}" class="btn btn-success">
                            <i class="fas fa-shield-alt me-2"></i> Aktifkan Autentikasi Dua Faktor
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Hapus karakter non-digit dan 0 di depan
    document.getElementById('phone').addEventListener('input', function(e) {
        let value = e.target.value.replace(/\D/g, '');
        // Hapus angka 0 di depan
        if (value.startsWith('0')) {
            value = value.substring(1);
        }
        e.target.value = value;
    });
</script>
@endpush
@endsection 