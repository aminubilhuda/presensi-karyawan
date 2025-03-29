@extends('layouts.app')

@section('title', 'Tiket Dukungan')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Daftar Tiket Dukungan</h3>
                    <div class="card-tools">
                        <a href="{{ route('support.create') }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus"></i> Buat Tiket Baru
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if($tickets->isEmpty())
                        <div class="text-center py-4">
                            <h4>Tidak ada tiket dukungan</h4>
                            <p>Buat tiket dukungan jika Anda memerlukan bantuan.</p>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>No. Tiket</th>
                                        <th>Subjek</th>
                                        <th>Prioritas</th>
                                        <th>Status</th>
                                        <th>Tanggal Dibuat</th>
                                        @if(auth()->user()->isAdmin())
                                            <th>Pengguna</th>
                                        @endif
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($tickets as $ticket)
                                        <tr>
                                            <td>{{ $ticket->ticket_id }}</td>
                                            <td>{{ $ticket->subject }}</td>
                                            <td>
                                                @if($ticket->priority == 'high')
                                                    <span class="badge badge-danger">Tinggi</span>
                                                @elseif($ticket->priority == 'medium')
                                                    <span class="badge badge-warning">Sedang</span>
                                                @else
                                                    <span class="badge badge-info">Rendah</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($ticket->status == 'open')
                                                    <span class="badge badge-success">Terbuka</span>
                                                @elseif($ticket->status == 'in_progress')
                                                    <span class="badge badge-primary">Diproses</span>
                                                @else
                                                    <span class="badge badge-secondary">Ditutup</span>
                                                @endif
                                            </td>
                                            <td>{{ $ticket->created_at->format('d M Y H:i') }}</td>
                                            @if(auth()->user()->isAdmin())
                                                <td>{{ $ticket->user->name }}</td>
                                            @endif
                                            <td>
                                                <a href="{{ route('support.show', $ticket) }}" class="btn btn-info btn-sm">
                                                    <i class="fas fa-eye"></i> Lihat
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-3">
                            {{ $tickets->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 