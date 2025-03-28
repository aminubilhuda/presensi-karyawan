@extends('layouts.app')

@section('title', 'Pengaturan Sistem')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Pengaturan Sistem</h1>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-cog me-2"></i> Pengaturan Aplikasi</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.settings.store') }}" method="POST">
                        @csrf
                        
                        <!-- Pengaturan Umum -->
                        <div class="card mb-4">
                            <div class="card-header bg-light">
                                <h6 class="mb-0"><i class="fas fa-globe me-2"></i> Pengaturan Umum</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="app_name" class="form-label">Nama Aplikasi <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control @error('app_name') is-invalid @enderror" id="app_name" name="app_name" value="{{ old('app_name', $settings['app_name']) }}" required>
                                            @error('app_name')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="app_url" class="form-label">URL Aplikasi <span class="text-danger">*</span></label>
                                            <input type="url" class="form-control @error('app_url') is-invalid @enderror" id="app_url" name="app_url" value="{{ old('app_url', $settings['app_url']) }}" required>
                                            @error('app_url')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="app_description" class="form-label">Deskripsi Aplikasi</label>
                                    <textarea class="form-control @error('app_description') is-invalid @enderror" id="app_description" name="app_description" rows="2">{{ old('app_description', $settings['app_description']) }}</textarea>
                                    @error('app_description')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="timezone" class="form-label">Zona Waktu <span class="text-danger">*</span></label>
                                            <select class="form-select @error('timezone') is-invalid @enderror" id="timezone" name="timezone" required>
                                                <option value="Asia/Jakarta" {{ old('timezone', $settings['timezone']) == 'Asia/Jakarta' ? 'selected' : '' }}>Waktu Indonesia Barat (WIB)</option>
                                                <option value="Asia/Makassar" {{ old('timezone', $settings['timezone']) == 'Asia/Makassar' ? 'selected' : '' }}>Waktu Indonesia Tengah (WITA)</option>
                                                <option value="Asia/Jayapura" {{ old('timezone', $settings['timezone']) == 'Asia/Jayapura' ? 'selected' : '' }}>Waktu Indonesia Timur (WIT)</option>
                                            </select>
                                            @error('timezone')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="locale" class="form-label">Bahasa <span class="text-danger">*</span></label>
                                            <select class="form-select @error('locale') is-invalid @enderror" id="locale" name="locale" required>
                                                <option value="id" {{ old('locale', $settings['locale']) == 'id' ? 'selected' : '' }}>Bahasa Indonesia</option>
                                                <option value="en" {{ old('locale', $settings['locale']) == 'en' ? 'selected' : '' }}>English</option>
                                            </select>
                                            @error('locale')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Pengaturan Email -->
                        <div class="card mb-4">
                            <div class="card-header bg-light">
                                <h6 class="mb-0"><i class="fas fa-envelope me-2"></i> Pengaturan Email</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="mail_mailer" class="form-label">Mailer <span class="text-danger">*</span></label>
                                            <select class="form-select @error('mail_mailer') is-invalid @enderror" id="mail_mailer" name="mail_mailer" required>
                                                <option value="smtp" {{ old('mail_mailer', $settings['mail_mailer']) == 'smtp' ? 'selected' : '' }}>SMTP</option>
                                                <option value="mailtrap" {{ old('mail_mailer', $settings['mail_mailer']) == 'mailtrap' ? 'selected' : '' }}>Mailtrap</option>
                                            </select>
                                            @error('mail_mailer')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="mail_host" class="form-label">Host SMTP <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control @error('mail_host') is-invalid @enderror" id="mail_host" name="mail_host" value="{{ old('mail_host', $settings['mail_host']) }}" required>
                                            @error('mail_host')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="mail_port" class="form-label">Port SMTP <span class="text-danger">*</span></label>
                                            <input type="number" class="form-control @error('mail_port') is-invalid @enderror" id="mail_port" name="mail_port" value="{{ old('mail_port', $settings['mail_port']) }}" required>
                                            @error('mail_port')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="mail_encryption" class="form-label">Enkripsi <span class="text-danger">*</span></label>
                                            <select class="form-select @error('mail_encryption') is-invalid @enderror" id="mail_encryption" name="mail_encryption" required>
                                                <option value="tls" {{ old('mail_encryption', $settings['mail_encryption']) == 'tls' ? 'selected' : '' }}>TLS</option>
                                                <option value="ssl" {{ old('mail_encryption', $settings['mail_encryption']) == 'ssl' ? 'selected' : '' }}>SSL</option>
                                            </select>
                                            @error('mail_encryption')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="mail_username" class="form-label">Username SMTP <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('mail_username') is-invalid @enderror" id="mail_username" name="mail_username" value="{{ old('mail_username', $settings['mail_username']) }}" required>
                                    @error('mail_username')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="mail_from_address" class="form-label">Alamat Email Pengirim <span class="text-danger">*</span></label>
                                            <input type="email" class="form-control @error('mail_from_address') is-invalid @enderror" id="mail_from_address" name="mail_from_address" value="{{ old('mail_from_address', $settings['mail_from_address']) }}" required>
                                            @error('mail_from_address')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="mail_from_name" class="form-label">Nama Pengirim <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control @error('mail_from_name') is-invalid @enderror" id="mail_from_name" name="mail_from_name" value="{{ old('mail_from_name', $settings['mail_from_name']) }}" required>
                                            @error('mail_from_name')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle me-2"></i>
                                    Untuk keamanan, password SMTP tidak disimpan di sini. Silakan atur password SMTP di file .env
                                </div>
                            </div>
                        </div>

                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i> Simpan Pengaturan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 