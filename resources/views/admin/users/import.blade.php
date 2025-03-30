@extends('layouts.app')

@section('title', 'Import Data Pengguna')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Import Data Pengguna</h1>
        <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left mr-1"></i> Kembali
        </a>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    @if (session('error'))
                        <div class="alert alert-danger">
                            <h5><i class="fas fa-exclamation-triangle mr-2"></i> Terjadi Kesalahan</h5>
                            <p class="mb-0">{{ session('error') }}</p>
                            
                            @if($errors->has('import_errors'))
                                <ul class="mt-3 mb-0">
                                    @foreach($errors->get('import_errors') as $errorGroup)
                                        @foreach($errorGroup as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    @endforeach
                                </ul>
                            @endif
                        </div>
                    @endif

                    @if (session('warning'))
                        <div class="alert alert-warning">
                            <h5><i class="fas fa-exclamation-circle mr-2"></i> Perhatian</h5>
                            <p class="mb-0">{{ session('warning') }}</p>
                        </div>
                    @endif

                    @if (session('info'))
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle mr-2"></i> {{ session('info') }}
                        </div>
                    @endif

                    <form action="{{ route('admin.users.import.process') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        
                        <div class="mb-4">
                            <label for="file" class="form-label">File</label>
                            <input type="file" class="form-control @error('file') is-invalid @enderror" id="file" name="file" accept=".csv,.xlsx,.xls" required>
                            @error('file')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted mt-2 d-block">Upload file CSV atau Excel (XLSX/XLS) dengan format sesuai template</small>
                        </div>
                        
                        <div class="d-flex flex-column">
                            <button type="submit" class="btn btn-primary mb-2">
                                <i class="fas fa-upload mr-1"></i> Import Data
                            </button>
                            <div class="btn-group mb-2">
                                <a href="{{ route('admin.users.import.template') }}" class="btn btn-outline-secondary">
                                    <i class="fas fa-download mr-1"></i> Download Template CSV
                                </a>
                                <a href="{{ route('admin.users.import.template', ['format' => 'xlsx']) }}" class="btn btn-outline-secondary">
                                    <i class="fas fa-download mr-1"></i> Download Template XLSX
                                </a>
                            </div>
                            <a href="{{ asset('templates/template-panduan-import.html') }}" class="btn btn-outline-info" target="_blank">
                                <i class="fas fa-book mr-1"></i> Lihat Panduan Lengkap
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-info-circle mr-1"></i> Petunjuk Import</h5>
                </div>
                <div class="card-body">
                    <ol class="pl-3">
                        <li class="mb-2">Download template CSV dengan mengklik tombol "Download Template CSV"</li>
                        <li class="mb-2">Isi data pengguna sesuai dengan format template</li>
                        <li class="mb-2">Kolom dengan tanda <strong>*</strong> wajib diisi</li>
                        <li class="mb-2">Untuk kolom "Peran", isi dengan nama peran yang sudah ada di sistem</li>
                        <li class="mb-2">Untuk kolom "Notifikasi WA", isi dengan "Ya" atau "Tidak"</li>
                        <li class="mb-2">Jika ingin menggunakan format Excel (XLSX/XLS), Anda dapat membuka file CSV dengan Excel lalu menyimpannya sebagai XLSX/XLS</li>
                        <li class="mb-2">Upload file yang sudah diisi</li>
                        <li class="mb-2">Klik tombol "Import Data" untuk memproses</li>
                    </ol>
                    
                    <div class="alert alert-warning mt-3">
                        <strong>Perhatian!</strong> Pastikan email dan username bersifat unik (belum pernah digunakan)
                    </div>
                    
                    <div class="alert alert-info mt-3 mb-0">
                        <strong>Format yang didukung:</strong> CSV, XLSX dan XLS yang memiliki format sama dengan template. <br>
                        <small>
                            <i class="fas fa-info-circle"></i> <strong>Catatan:</strong> Pastikan data dalam file Anda memiliki format yang sama dengan template yang disediakan.
                            <br><i class="fas fa-exclamation-triangle text-warning"></i> Untuk format XLSX/XLS diperlukan ekstensi PHP ZIP pada server.
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 