@extends('layouts.app')

@section('title', 'Ajukan Izin Baru')

@section('styles')
<!-- Datepicker -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
@endsection

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Form Pengajuan Izin</h5>
                </div>
                
                <div class="card-body">
                    <form action="{{ route('leave.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        
                        <div class="mb-3">
                            <label for="type" class="form-label">Jenis Izin <span class="text-danger">*</span></label>
                            <select name="type" id="type" class="form-select @error('type') is-invalid @enderror" required>
                                <option value="" selected disabled>Pilih jenis izin</option>
                                <option value="izin" {{ old('type') == 'izin' ? 'selected' : '' }}>Izin</option>
                                <option value="sakit" {{ old('type') == 'sakit' ? 'selected' : '' }}>Sakit</option>
                            </select>
                            @error('type')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="start_date" class="form-label">Tanggal Mulai <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control datepicker @error('start_date') is-invalid @enderror" 
                                           id="start_date" name="start_date" value="{{ old('start_date') }}" placeholder="YYYY-MM-DD" required>
                                    @error('start_date')
                                        <div class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="end_date" class="form-label">Tanggal Selesai <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control datepicker @error('end_date') is-invalid @enderror" 
                                           id="end_date" name="end_date" value="{{ old('end_date') }}" placeholder="YYYY-MM-DD" required>
                                    @error('end_date')
                                        <div class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="reason" class="form-label">Alasan <span class="text-danger">*</span></label>
                            <textarea class="form-control @error('reason') is-invalid @enderror" id="reason" 
                                      name="reason" rows="4" required>{{ old('reason') }}</textarea>
                            @error('reason')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                            <small class="text-muted">Alasan izin/cuti. Maksimal 500 karakter.</small>
                        </div>
                        
                        <div class="mb-4">
                            <label for="attachment" class="form-label">Lampiran (Opsional)</label>
                            <input type="file" class="form-control @error('attachment') is-invalid @enderror" 
                                   id="attachment" name="attachment">
                            @error('attachment')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                            <small class="text-muted">Unggah bukti seperti surat dokter (untuk sakit), surat undangan, atau dokumen lainnya. Format: JPEG, PNG, PDF, DOC. Maks. 2MB.</small>
                        </div>
                        
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('leave.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left mr-1"></i> Kembali
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-paper-plane mr-1"></i> Ajukan Permohonan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<!-- Flatpickr -->
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Inisialisasi datepicker
        const today = new Date().toISOString().split('T')[0];
        
        flatpickr("#start_date", {
            dateFormat: "Y-m-d",
            minDate: today,
            onChange: function(selectedDates, dateStr, instance) {
                // Update tanggal minimal untuk end_date
                endDatePicker.set("minDate", dateStr);
            }
        });
        
        const endDatePicker = flatpickr("#end_date", {
            dateFormat: "Y-m-d",
            minDate: today
        });
    });
</script>
@endsection 