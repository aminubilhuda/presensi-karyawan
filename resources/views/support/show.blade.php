@extends('layouts.app')

@section('title', 'Detail Tiket #' . $support->ticket_id)

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-10 mx-auto">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Tiket #{{ $support->ticket_id }}</h3>
                    <div class="card-tools">
                        <a href="{{ route('support.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> Kembali ke Daftar
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="d-flex justify-content-between">
                                <h4>{{ $support->subject }}</h4>
                                <div>
                                    @if($support->status == 'open')
                                        <span class="badge badge-success">Terbuka</span>
                                    @elseif($support->status == 'in_progress')
                                        <span class="badge badge-primary">Diproses</span>
                                    @else
                                        <span class="badge badge-secondary">Ditutup</span>
                                    @endif
                                    
                                    @if($support->priority == 'high')
                                        <span class="badge badge-danger">Prioritas Tinggi</span>
                                    @elseif($support->priority == 'medium')
                                        <span class="badge badge-warning">Prioritas Sedang</span>
                                    @else
                                        <span class="badge badge-info">Prioritas Rendah</span>
                                    @endif
                                </div>
                            </div>
                            <hr>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-8">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title">Pesan</h5>
                                </div>
                                <div class="card-body">
                                    <p>{{ $support->message }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title">Informasi Tiket</h5>
                                </div>
                                <div class="card-body">
                                    <table class="table table-bordered">
                                        <tr>
                                            <th>ID Tiket</th>
                                            <td>{{ $support->ticket_id }}</td>
                                        </tr>
                                        <tr>
                                            <th>Dibuat Oleh</th>
                                            <td>{{ $support->user->name }}</td>
                                        </tr>
                                        <tr>
                                            <th>Status</th>
                                            <td>
                                                @if($support->status == 'open')
                                                    <span class="badge badge-success">Terbuka</span>
                                                @elseif($support->status == 'in_progress')
                                                    <span class="badge badge-primary">Diproses</span>
                                                @else
                                                    <span class="badge badge-secondary">Ditutup</span>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Prioritas</th>
                                            <td>
                                                @if($support->priority == 'high')
                                                    <span class="badge badge-danger">Tinggi</span>
                                                @elseif($support->priority == 'medium')
                                                    <span class="badge badge-warning">Sedang</span>
                                                @else
                                                    <span class="badge badge-info">Rendah</span>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Tanggal Dibuat</th>
                                            <td>{{ $support->created_at->format('d M Y H:i') }}</td>
                                        </tr>
                                        @if($support->status == 'closed')
                                        <tr>
                                            <th>Tanggal Ditutup</th>
                                            <td>{{ $support->closed_at->format('d M Y H:i') }}</td>
                                        </tr>
                                        @endif
                                        @if($support->assigned_to)
                                        <tr>
                                            <th>Ditugaskan Kepada</th>
                                            <td>{{ $support->assignedTo->name }}</td>
                                        </tr>
                                        @endif
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-md-12">
                            @if($support->status != 'closed')
                                @if(auth()->user()->isAdmin())
                                    <form action="{{ route('support.update', $support) }}" method="POST" class="mb-3">
                                        @csrf
                                        @method('PUT')
                                        <div class="card">
                                            <div class="card-header">
                                                <h5 class="card-title">Update Status</h5>
                                            </div>
                                            <div class="card-body">
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label for="status">Status</label>
                                                            <select name="status" id="status" class="form-control">
                                                                <option value="open" {{ $support->status == 'open' ? 'selected' : '' }}>Terbuka</option>
                                                                <option value="in_progress" {{ $support->status == 'in_progress' ? 'selected' : '' }}>Diproses</option>
                                                                <option value="closed">Tutup</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label for="assigned_to">Tugaskan Kepada</label>
                                                            <select name="assigned_to" id="assigned_to" class="form-control">
                                                                <option value="">-- Pilih Admin --</option>
                                                                @foreach(\App\Models\User::where('role', 'Admin')->get() as $admin)
                                                                    <option value="{{ $admin->id }}" {{ $support->assigned_to == $admin->id ? 'selected' : '' }}>{{ $admin->name }}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                                <button type="submit" class="btn btn-primary">
                                                    <i class="fas fa-save"></i> Update Tiket
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                @endif

                                <form action="{{ route('support.close', $support) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-warning" onclick="return confirm('Apakah Anda yakin ingin menutup tiket ini?')">
                                        <i class="fas fa-times-circle"></i> Tutup Tiket
                                    </button>
                                </form>
                            @else
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle"></i> Tiket ini sudah ditutup pada {{ $support->closed_at->format('d M Y H:i') }}.
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 