@extends('layouts.app')

@section('title', 'Dashboard Analitik')

@section('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.css">
@endsection

@section('content')
<div class="container-fluid">
    <!-- Content Header -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Dashboard Analitik</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Analitik</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Statistik Pengguna -->
    <div class="row">
        <div class="col-12">
            <div class="card card-primary card-outline">
                <div class="card-header">
                    <h3 class="card-title">Statistik Pengguna</h3>
                </div>
                <div class="card-body p-0">
                    <div class="row m-0">
                        <div class="col-lg-3 col-md-6 col-sm-6">
                            <div class="small-box bg-info">
                                <div class="inner">
                                    <h3>{{ $userStats['total'] }}</h3>
                                    <p>Total Pengguna</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-users"></i>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6 col-sm-6">
                            <div class="small-box bg-success">
                                <div class="inner">
                                    <h3>{{ $userStats['active'] ?? 0 }}</h3>
                                    <p>Pengguna Aktif</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-user-check"></i>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6 col-sm-6">
                            <div class="small-box bg-warning">
                                <div class="inner">
                                    <h3>{{ $userStats['new_this_month'] }}</h3>
                                    <p>Pengguna Baru Bulan Ini</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-user-plus"></i>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6 col-sm-6">
                            <div class="small-box bg-danger">
                                <div class="inner">
                                    <h3>{{ count($userStats['by_role'] ?? []) }}</h3>
                                    <p>Jenis Peran</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-user-tag"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistik Absensi -->
    <div class="row">
        <div class="col-12">
            <div class="card card-primary card-outline">
                <div class="card-header">
                    <h3 class="card-title">Statistik Absensi</h3>
                </div>
                <div class="card-body p-0">
                    <div class="row m-0">
                        <div class="col-lg-3 col-md-6 col-sm-6">
                            <div class="small-box bg-info">
                                <div class="inner">
                                    <h3>{{ $attendanceStats['today']['total'] }}</h3>
                                    <p>Total Absensi Hari Ini</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-calendar-check"></i>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6 col-sm-6">
                            <div class="small-box bg-success">
                                <div class="inner">
                                    <h3>{{ $attendanceStats['today']['present'] }}</h3>
                                    <p>Hadir Hari Ini</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-check-circle"></i>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6 col-sm-6">
                            <div class="small-box bg-danger">
                                <div class="inner">
                                    <h3>{{ $attendanceStats['today']['absent'] }}</h3>
                                    <p>Tidak Hadir Hari Ini</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-times-circle"></i>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6 col-sm-6">
                            <div class="small-box bg-warning">
                                <div class="inner">
                                    <h3>{{ $attendanceStats['today']['late'] }}</h3>
                                    <p>Terlambat Hari Ini</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-clock"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Grafik dan Detail Statistik -->
    <div class="row">
        <div class="col-lg-8">
            <div class="card card-primary card-outline">
                <div class="card-header">
                    <h3 class="card-title">Tren Absensi 30 Hari Terakhir</h3>
                </div>
                <div class="card-body">
                    <canvas id="attendanceChart" height="300"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <!-- Statistik Dokumen -->
            <div class="card card-info card-outline">
                <div class="card-header">
                    <h3 class="card-title">Statistik Dokumen</h3>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-6 text-center">
                            <div class="info-box bg-light">
                                <div class="info-box-content">
                                    <span class="info-box-text text-muted">Total Dokumen</span>
                                    <span class="info-box-number text-muted">{{ $documentStats['total'] }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 text-center">
                            <div class="info-box bg-light">
                                <div class="info-box-content">
                                    <span class="info-box-text text-muted">Bulan Ini</span>
                                    <span class="info-box-number text-muted">{{ $documentStats['this_month'] }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <canvas id="documentChart" height="200"></canvas>
                </div>
            </div>

            <!-- Statistik Tiket Dukungan -->
            <div class="card card-warning card-outline">
                <div class="card-header">
                    <h3 class="card-title">Statistik Tiket Dukungan</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-6">
                            <div class="description-block border-right">
                                <span class="description-percentage text-success">
                                    <i class="fas fa-ticket-alt"></i>
                                </span>
                                <h5 class="description-header">{{ $ticketStats['total'] }}</h5>
                                <span class="description-text">TOTAL TIKET</span>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="description-block">
                                <span class="description-percentage text-warning">
                                    <i class="fas fa-spinner"></i>
                                </span>
                                <h5 class="description-header">{{ $ticketStats['open'] }}</h5>
                                <span class="description-text">TIKET TERBUKA</span>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-6">
                            <div class="description-block border-right">
                                <span class="description-percentage text-primary">
                                    <i class="fas fa-tasks"></i>
                                </span>
                                <h5 class="description-header">{{ $ticketStats['in_progress'] }}</h5>
                                <span class="description-text">TIKET DIPROSES</span>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="description-block">
                                <span class="description-percentage text-success">
                                    <i class="fas fa-check-circle"></i>
                                </span>
                                <h5 class="description-header">{{ $ticketStats['closed'] }}</h5>
                                <span class="description-text">TIKET DITUTUP</span>
                            </div>
                        </div>
                    </div>
                    <div class="mt-3">
                        <canvas id="ticketChart" height="150"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Chart untuk Tren Absensi
        var attendanceData = @json($attendanceStats['daily_trend'] ?? []);
        var labels = attendanceData.map(function(item) {
            return new Date(item.attendance_date).toLocaleDateString('id-ID', {
                day: 'numeric', 
                month: 'short'
            });
        });
        
        var presentData = attendanceData.map(function(item) {
            return item.present_count;
        });
        
        var absentData = attendanceData.map(function(item) {
            return item.absent_count;
        });
        
        var lateData = attendanceData.map(function(item) {
            return item.late_count;
        });
        
        var attendanceCtx = document.getElementById('attendanceChart').getContext('2d');
        new Chart(attendanceCtx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Hadir',
                        data: presentData,
                        backgroundColor: 'rgba(40, 167, 69, 0.2)',
                        borderColor: 'rgba(40, 167, 69, 1)',
                        borderWidth: 1,
                        tension: 0.4
                    },
                    {
                        label: 'Tidak Hadir',
                        data: absentData,
                        backgroundColor: 'rgba(220, 53, 69, 0.2)',
                        borderColor: 'rgba(220, 53, 69, 1)',
                        borderWidth: 1,
                        tension: 0.4
                    },
                    {
                        label: 'Terlambat',
                        data: lateData,
                        backgroundColor: 'rgba(255, 193, 7, 0.2)',
                        borderColor: 'rgba(255, 193, 7, 1)',
                        borderWidth: 1,
                        tension: 0.4
                    }
                ]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                },
                plugins: {
                    legend: {
                        position: 'top',
                    }
                }
            }
        });
        
        // Chart untuk Jenis Dokumen
        var documentTypes = @json($documentStats['by_type'] ?? []);
        var documentLabels = Object.keys(documentTypes);
        var documentCounts = Object.values(documentTypes);
        
        var documentCtx = document.getElementById('documentChart').getContext('2d');
        new Chart(documentCtx, {
            type: 'doughnut',
            data: {
                labels: documentLabels,
                datasets: [{
                    data: documentCounts,
                    backgroundColor: [
                        'rgba(54, 162, 235, 0.7)',
                        'rgba(255, 99, 132, 0.7)',
                        'rgba(255, 206, 86, 0.7)',
                        'rgba(75, 192, 192, 0.7)',
                        'rgba(153, 102, 255, 0.7)',
                        'rgba(255, 159, 64, 0.7)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'right'
                    }
                }
            }
        });
        
        // Chart untuk Tiket Dukungan
        var ticketPriorities = @json($ticketStats['by_priority'] ?? []);
        var ticketLabels = Object.keys(ticketPriorities).map(function(priority) {
            return priority === 'low' ? 'Rendah' : (priority === 'medium' ? 'Sedang' : 'Tinggi');
        });
        var ticketCounts = Object.values(ticketPriorities);
        
        var ticketCtx = document.getElementById('ticketChart').getContext('2d');
        new Chart(ticketCtx, {
            type: 'pie',
            data: {
                labels: ticketLabels,
                datasets: [{
                    data: ticketCounts,
                    backgroundColor: [
                        'rgba(23, 162, 184, 0.7)',
                        'rgba(255, 193, 7, 0.7)',
                        'rgba(220, 53, 69, 0.7)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    });
</script>
@endsection 