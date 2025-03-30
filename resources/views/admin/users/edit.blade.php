@extends('layouts.app')

@section('title', 'Edit Pengguna')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Edit Pengguna</h1>
        <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-1"></i> Kembali
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('admin.users.update', $user) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="row">
                    <div class="col-md-6">
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
                            <label for="username" class="form-label">Username</label>
                            <input type="text" class="form-control @error('username') is-invalid @enderror" id="username" name="username" value="{{ old('username', $user->username) }}">
                            <small class="form-text text-muted">Username dapat digunakan untuk login sebagai alternatif dari email</small>
                            @error('username')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="phone" class="form-label">Nomor Telepon (WhatsApp)</label>
                            <input type="text" class="form-control @error('phone') is-invalid @enderror" id="phone" name="phone" value="{{ old('phone', $user->phone) }}" placeholder="Contoh: 08123456789">
                            <small class="form-text text-muted">Format: Gunakan awalan 0 atau 62. Contoh: 08123456789 atau 628123456789</small>
                            @error('phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="wa_notifications" name="wa_notifications" value="1" {{ old('wa_notifications', $user->wa_notifications) ? 'checked' : '' }}>
                                <label class="form-check-label" for="wa_notifications">Aktifkan Notifikasi WhatsApp</label>
                            </div>
                            <small class="form-text text-muted">Pengguna akan menerima notifikasi absensi melalui WhatsApp</small>
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">Password (Kosongkan jika tidak ingin mengubah)</label>
                            <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password">
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="password_confirmation" class="form-label">Konfirmasi Password</label>
                            <input type="password" class="form-control" id="password_confirmation" name="password_confirmation">
                        </div>

                        <div class="mb-3">
                            <label for="role_id" class="form-label">Peran</label>
                            <select class="form-select @error('role_id') is-invalid @enderror" id="role_id" name="role_id" required>
                                <option value="">Pilih Peran</option>
                                @foreach($roles as $role)
                                    <option value="{{ $role->id }}" {{ (old('role_id', $user->role_id) == $role->id) ? 'selected' : '' }}>{{ $role->name }}</option>
                                @endforeach
                            </select>
                            @error('role_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="photo" class="form-label">Foto Pengguna</label>
                            <div class="mb-3 text-center">
                                @if($user->photo)
                                    <img src="{{ asset('storage/' . $user->photo) }}" class="img-thumbnail mb-2" style="max-width: 200px; max-height: 200px;" alt="Foto Profil" id="preview-photo">
                                @else
                                    <img src="{{ asset('images/avatar.png') }}" class="img-thumbnail mb-2" style="max-width: 200px; max-height: 200px;" alt="Foto Profil" id="preview-photo">
                                @endif
                            </div>
                            <div class="input-group">
                                <input type="file" class="form-control @error('photo') is-invalid @enderror" id="photo" name="photo" accept="image/*">
                                @if($user->photo)
                                    <button type="button" class="btn btn-outline-danger" id="remove-photo">Hapus Foto</button>
                                    <input type="hidden" name="remove_photo" id="remove_photo_input" value="0">
                                @endif
                            </div>
                            <small class="form-text text-muted">
                                Foto ini akan digunakan untuk verifikasi wajah saat absensi. Upload foto yang jelas menampilkan wajah.
                            </small>
                            @error('photo')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="card mt-4">
                            <div class="card-header bg-light">
                                <h5 class="mb-0">Informasi Tambahan</h5>
                            </div>
                            <div class="card-body">
                                <p><strong>Terakhir Login:</strong> {{ $user->last_login_at ? date('d/m/Y H:i', strtotime($user->last_login_at)) : 'Belum pernah' }}</p>
                                <p><strong>Akun Dibuat:</strong> {{ date('d/m/Y H:i', strtotime($user->created_at)) }}</p>
                                <p><strong>Terakhir Diperbarui:</strong> {{ date('d/m/Y H:i', strtotime($user->updated_at)) }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-grid gap-2 mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i> Perbarui Pengguna
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Preview foto yang akan diupload
    document.getElementById('photo').addEventListener('change', function(e) {
        const reader = new FileReader();
        reader.onload = function(event) {
            document.getElementById('preview-photo').src = event.target.result;
        }
        reader.readAsDataURL(e.target.files[0]);
    });

    // Hapus foto profil
    @if($user->photo)
    document.getElementById('remove-photo').addEventListener('click', function() {
        document.getElementById('preview-photo').src = "{{ asset('images/avatar.png') }}";
        document.getElementById('photo').value = "";
        document.getElementById('remove_photo_input').value = "1";
        this.disabled = true;
    });
    @endif
</script>
@endpush
@endsection 