@extends('layouts.app')

@section('title', 'Kelola Hari Kerja')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Kelola Hari Kerja</h1>
        <a href="{{ route('admin.workdays.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i> Tambah Hari Kerja
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Hari</th>
                            <th>Status</th>
                            <th>Tindakan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($workdays as $index => $workday)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $workday->day }}</td>
                                <td>
                                    @if($workday->is_active)
                                        <span class="badge bg-success">Aktif</span>
                                    @else
                                        <span class="badge bg-danger">Tidak Aktif</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('admin.workdays.edit', $workday) }}" class="btn btn-sm btn-warning">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('admin.workdays.destroy', $workday) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus hari kerja ini?')">
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
                                <td colspan="4" class="text-center">Tidak ada data hari kerja.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <div class="card mt-4">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">Informasi Hari Kerja</h5>
        </div>
        <div class="card-body">
            <div class="alert alert-info">
                <p class="mb-0">
                    <i class="fas fa-info-circle me-2"></i>
                    Hari kerja digunakan untuk menentukan hari di mana karyawan dapat melakukan absensi.
                    Hari yang tidak ditandai sebagai hari kerja tidak akan dihitung sebagai hari masuk
                    dan tidak akan dihitung dalam laporan kehadiran.
                </p>
            </div>
            <div class="alert alert-warning">
                <h5><i class="fas fa-exclamation-triangle me-2"></i>Catatan Penting:</h5>
                <ul class="mb-0">
                    <li>Pastikan untuk mengatur hari kerja sesuai dengan kebijakan sekolah</li>
                    <li>Hari yang tidak aktif akan dianggap sebagai hari libur</li>
                    <li>Perubahan status hari kerja akan berlaku mulai hari berikutnya</li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection 