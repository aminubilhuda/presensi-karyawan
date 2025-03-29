@extends('layouts.app')

@section('title', 'Dashboard Analitik')

@section('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/chart.js@2.9.4/dist/Chart.min.css">
<style>
    .small-box {
        border-radius: 0.5rem;
        box-shadow: 0 0 1px rgba(0,0,0,.125), 0 1px 3px rgba(0,0,0,.2);
        display: block;
        margin-bottom: 20px;
        position: relative;
    }
    .small-box .inner {
        padding: 10px;
    }
    .small-box .inner h3 {
        font-size: 2.2rem;
        font-weight: 700;
        margin: 0 0 10px;
        padding: 0;
        white-space: nowrap;
    }
    .small-box .icon {
        color: rgba(0,0,0,.15);
        z-index: 0;
    }
    .small-box .icon i {
        font-size: 70px;
        position: absolute;
        right: 15px;
        top: 15px;
        transition: transform .3s linear;
    }
    .small-box:hover .icon i {
        transform: scale(1.1);
    }
    .bg-info {
        background-color: #17a2b8!important;
    }
    .bg-success {
        background-color: #28a745!important;
    }
    .bg-warning {
        background-color: #ffc107!important;
    }
    .bg-danger {
        background-color: #dc3545!important;
    }
</style>
@endsection

@section('content')
<div class="container-fluid">
    <!-- Statistik Pengguna -->
    <div class="row">
        <div class="col-12">
            <h3>Statistik Pengguna</h3>
        </div>
        <div class="col-lg-3 col-md-6">
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
        <div class="col-lg-3 col-md-6">
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
        <div class="col-lg-3 col-md-6">
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
        <div class="col-lg-3 col-md-6">
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

    <!-- Statistik Absensi -->
    <div class="row mt-4">
        <div class="col-12">
            <h3>Statistik Absensi</h3>
        </div>
        <div class="col-lg-3 col-md-6">
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
        <div class="col-lg-3 col-md-6">
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
        <div class="col-lg-3 col-md-6">
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
        <div class="col-lg-3 col-md-6">
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

    <!-- Grafik dan Detail Statistik -->
    <div class="row mt-4">
        <div class="col-lg-8">
            <div class="card">
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
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Statistik Dokumen</h3>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <h4>{{ $documentStats['total'] }}</h4>
                            <span>Total Dokumen</span>
                        </div>
                        <div>
                            <h4>{{ $documentStats['this_month'] }}</h4>
                            <span>Bulan Ini</span>
                        </div>
                    </div>
                    <canvas id="documentChart" height="200"></canvas>
                </div>
            </div>

            <!-- Statistik Tiket Dukungan -->
            <div class="card mt-4">
                <div class="card-header">
                    <h3 class="card-title">Statistik Tiket Dukungan</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-6">
                            <div class="info-box">
                                <span class="info-box-text">Total Tiket</span>
                                <span class="info-box-number">{{ $ticketStats['total'] }}</span>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="info-box">
                                <span class="info-box-text">Tiket Terbuka</span>
                                <span class="info-box-number">{{ $ticketStats['open'] }}</span>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="info-box">
                                <span class="info-box-text">Tiket Diproses</span>
                                <span class="info-box-number">{{ $ticketStats['in_progress'] }}</span>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="info-box">
                                <span class="info-box-text">Tiket Ditutup</span>
                                <span class="info-box-number">{{ $ticketStats['closed'] }}</span>
                            </div>
                        </div>
                    </div>
                    <canvas id="ticketChart" height="150"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@2.9.4/dist/Chart.min.js"></script>
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
                legend: {
                    position: 'right'
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
                legend: {
                    position: 'bottom'
                }
            }
        });
    });
</script>
@endsection 