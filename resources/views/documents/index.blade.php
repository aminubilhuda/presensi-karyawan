@extends('layouts.app')

@section('title', 'Kelola Dokumen')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Daftar Dokumen</h3>
                    <div class="card-tools">
                        <a href="{{ route('documents.create') }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus"></i> Tambah Dokumen
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if($documents->isEmpty())
                        <div class="text-center py-4">
                            <h4>Tidak ada dokumen</h4>
                            <p>Mulai unggah dokumen Anda sekarang.</p>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>Judul</th>
                                        <th>Jenis File</th>
                                        <th>Ukuran</th>
                                        <th>Diunggah Pada</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($documents as $document)
                                        <tr>
                                            <td>{{ $document->title }}</td>
                                            <td>{{ $document->file_type }}</td>
                                            <td>{{ $document->formatted_file_size }}</td>
                                            <td>{{ $document->created_at->format('d M Y H:i') }}</td>
                                            <td>
                                                <div class="btn-group">
                                                    <a href="{{ route('documents.show', $document) }}" class="btn btn-info btn-sm">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="{{ route('documents.download', $document) }}" class="btn btn-success btn-sm">
                                                        <i class="fas fa-download"></i>
                                                    </a>
                                                    <form action="{{ route('documents.destroy', $document) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus dokumen ini?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-danger btn-sm">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-3">
                            {{ $documents->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 