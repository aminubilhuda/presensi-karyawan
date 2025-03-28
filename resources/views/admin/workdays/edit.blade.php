@extends('layouts.app')

@section('title', 'Edit Hari Kerja')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Edit Hari Kerja</h1>
        <a href="{{ route('admin.workdays.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-1"></i> Kembali
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('admin.workdays.update', $workday) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label for="day" class="form-label">Nama Hari</label>
                    <select class="form-select @error('day') is-invalid @enderror" id="day" name="day" required>
                        <option value="">Pilih Hari</option>
                        <option value="Senin" {{ old('day', $workday->day) == 'Senin' ? 'selected' : '' }}>Senin</option>
                        <option value="Selasa" {{ old('day', $workday->day) == 'Selasa' ? 'selected' : '' }}>Selasa</option>
                        <option value="Rabu" {{ old('day', $workday->day) == 'Rabu' ? 'selected' : '' }}>Rabu</option>
                        <option value="Kamis" {{ old('day', $workday->day) == 'Kamis' ? 'selected' : '' }}>Kamis</option>
                        <option value="Jumat" {{ old('day', $workday->day) == 'Jumat' ? 'selected' : '' }}>Jumat</option>
                        <option value="Sabtu" {{ old('day', $workday->day) == 'Sabtu' ? 'selected' : '' }}>Sabtu</option>
                        <option value="Minggu" {{ old('day', $workday->day) == 'Minggu' ? 'selected' : '' }}>Minggu</option>
                    </select>
                    @error('day')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3 form-check">
                    <input type="checkbox" class="form-check-input" id="is_active" name="is_active" value="1" {{ old('is_active', $workday->is_active) ? 'checked' : '' }}>
                    <label class="form-check-label" for="is_active">Aktif</label>
                </div>

                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i> Perbarui
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection 