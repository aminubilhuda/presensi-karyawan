@extends('layouts.app')

@section('title', 'Autentikasi Dua Faktor')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Aktivasi Autentikasi Dua Faktor</h3>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="alert alert-info">
                        <p><i class="fas fa-info-circle"></i> Autentikasi dua faktor memberikan lapisan keamanan tambahan untuk akun Anda. Setelah diaktifkan, Anda akan diminta untuk memasukkan kode unik dari aplikasi autentikator setiap kali login.</p>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="text-center mb-4">
                                <h5>1. Pindai Kode QR</h5>
                                <p>Pindai kode QR berikut dengan aplikasi autentikator di ponsel Anda:</p>
                                <div class="qr-code p-3 d-flex justify-content-center">
                                    {!! $qrCode !!}
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-4">
                                <h5>2. Atau masukkan kode secara manual</h5>
                                <p>Jika Anda tidak dapat memindai kode QR, masukkan kode rahasia berikut ke dalam aplikasi Anda:</p>
                                <div class="secret-key p-3 bg-light text-center">
                                    <code class="fs-4">{{ $secret }}</code>
                                </div>
                            </div>
                            
                            <div class="mb-4">
                                <h5>3. Verifikasi kode</h5>
                                <p>Setelah Anda menambahkan akun ke aplikasi autentikator, masukkan kode 6 digit yang tersedia:</p>
                                <form action="{{ route('two-factor.enable') }}" method="POST">
                                    @csrf
                                    <div class="form-group">
                                        <input type="text" name="code" class="form-control @error('code') is-invalid @enderror" placeholder="Masukkan kode 6 digit" maxlength="6" autocomplete="off" required>
                                        @error('code')
                                            <div class="invalid-feedback">
                                                {{ $message }}
                                            </div>
                                        @enderror
                                    </div>
                                    <button type="submit" class="btn btn-primary">Verifikasi & Aktifkan</button>
                                    <a href="{{ route('profile.edit') }}" class="btn btn-secondary">Batal</a>
                                </form>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4">
                        <div class="alert alert-warning">
                            <h5><i class="fas fa-exclamation-triangle"></i> Penting!</h5>
                            <ul>
                                <li>Simpan aplikasi autentikator Anda dengan aman. Anda akan membutuhkannya setiap kali login.</li>
                                <li>Jika Anda kehilangan akses ke aplikasi autentikator, Anda mungkin tidak dapat masuk ke akun Anda.</li>
                                <li>Beberapa aplikasi autentikator populer: Google Authenticator, Microsoft Authenticator, Authy.</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 