@extends('layouts.app')

@section('title', $document->title)

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Detail Dokumen</h3>
                    <div class="card-tools">
                        <a href="{{ route('documents.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> Kembali
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-12">
                            <table class="table table-bordered">
                                <tr>
                                    <th style="width: 30%">Judul</th>
                                    <td>{{ $document->title }}</td>
                                </tr>
                                <tr>
                                    <th>Deskripsi</th>
                                    <td>{{ $document->description ?: 'Tidak ada deskripsi' }}</td>
                                </tr>
                                <tr>
                                    <th>Nama File</th>
                                    <td>{{ $document->file_name }}</td>
                                </tr>
                                <tr>
                                    <th>Tipe File</th>
                                    <td>{{ $document->file_type }}</td>
                                </tr>
                                <tr>
                                    <th>Ukuran File</th>
                                    <td>{{ $document->formatted_file_size }}</td>
                                </tr>
                                <tr>
                                    <th>Tanggal Unggah</th>
                                    <td>{{ $document->created_at->format('d M Y H:i') }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-md-12">
                            <div class="d-flex justify-content-between">
                                <a href="{{ route('documents.download', $document) }}" class="btn btn-primary">
                                    <i class="fas fa-download"></i> Unduh Dokumen
                                </a>
                                
                                <form action="{{ route('documents.destroy', $document) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus dokumen ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger">
                                        <i class="fas fa-trash"></i> Hapus Dokumen
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 