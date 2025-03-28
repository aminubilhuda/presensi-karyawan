@extends('layouts.app')

@section('title', 'Kelola Hari Libur')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Kelola Hari Libur</h1>
        <a href="{{ route('admin.holidays.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i> Tambah Hari Libur
        </a>
    </div>

    <!-- Filter Tahun -->
    <div class="card mb-4">
        <div class="card-body">
            <form action="{{ route('admin.holidays.index') }}" method="GET" class="d-flex flex-wrap align-items-end">
                <div class="me-3 mb-2">
                    <label for="year" class="form-label">Tahun</label>
                    <select class="form-select" id="year" name="year" onchange="this.form.submit()">
                        @foreach($years as $y)
                            <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endforeach
                    </select>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama</th>
                            <th>Tanggal</th>
                            <th>Deskripsi</th>
                            <th>Tindakan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($holidays as $index => $holiday)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $holiday->name }}</td>
                                <td>{{ $holiday->date->format('d F Y') }}</td>
                                <td>{{ $holiday->description ?: '-' }}</td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('admin.holidays.edit', $holiday) }}" class="btn btn-sm btn-warning">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('admin.holidays.destroy', $holiday) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus hari libur ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center">Tidak ada data hari libur untuk tahun {{ $year }}.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <div class="card mt-4">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">Informasi Hari Libur</h5>
        </div>
        <div class="card-body">
            <div class="alert alert-info">
                <p class="mb-0">
                    <i class="fas fa-info-circle me-2"></i>
                    Hari libur digunakan untuk menandai hari tertentu yang tidak dihitung sebagai hari kerja,
                    seperti hari libur nasional, cuti bersama, atau hari libur khusus sekolah.
                    Pada hari yang ditandai sebagai hari libur, sistem tidak akan menghitung ketidakhadiran
                    sebagai ketidakhadiran tanpa keterangan.
                </p>
            </div>
            <div class="alert alert-warning">
                <h5><i class="fas fa-exclamation-triangle me-2"></i>Catatan Penting:</h5>
                <ul class="mb-0">
                    <li>Pastikan untuk menambahkan semua hari libur nasional yang relevan</li>
                    <li>Tambahkan juga hari libur khusus sekolah seperti liburan semester</li>
                    <li>Hari libur berlaku untuk semua pengguna sistem</li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection 