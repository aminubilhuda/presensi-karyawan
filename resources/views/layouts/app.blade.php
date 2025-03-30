<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', App\Models\Setting::getValue('app_name', 'Sistem Absensi Sekolah')) - {{ App\Models\Setting::getValue('app_name', 'Sistem Absensi Sekolah') }}</title>
    <meta name="description" content="{{ App\Models\Setting::getValue('app_description', 'Aplikasi manajemen absensi karyawan') }}">
    
    @php
        $favicon = App\Models\Setting::getValue('app_favicon', null);
    @endphp
    
    @if($favicon)
        <link rel="icon" href="{{ asset('storage/' . $favicon) }}" type="image/x-icon">
        <link rel="shortcut icon" href="{{ asset('storage/' . $favicon) }}" type="image/x-icon">
    @endif
    
    <!-- Google Font: Source Sans Pro -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <!-- AdminLTE CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    
    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
    
    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <!-- Face-api.js Library -->
    <script src="{{ asset('js/face-api/face-api.min.js') }}"></script>
    
    <!-- Custom CSS -->
    <style>
        #current-time {
            font-size: 0.9rem;
        }
        #map {
            height: 400px;
            width: 100%;
        }
        .app-logo {
            max-height: 33px;
            margin-right: 5px;
        }
    </style>
    
    @yield('styles')
</head>
<body class="hold-transition sidebar-mini layout-fixed">
    <div class="wrapper">
        <!-- Navbar -->
        <nav class="main-header navbar navbar-expand navbar-white navbar-light">
            <!-- Left navbar links -->
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
                </li>
                <li class="nav-item d-none d-sm-inline-block">
                    <a href="{{ route('home') }}" class="nav-link">Beranda</a>
                </li>
            </ul>

            <!-- Right navbar links -->
            <ul class="navbar-nav ml-auto">
                <li class="nav-item">
                    <div class="nav-link">
                        <i class="far fa-clock mr-2"></i>
                        <span id="current-time" class="font-weight-semibold"></span>
                    </div>
                </li>
                @auth
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown">
                        <img src="{{ auth()->user()->photo ? asset('storage/' . auth()->user()->photo) : asset('images/avatar.png') }}" 
                             class="rounded-circle mr-1" width="30" height="30" alt="Avatar"> 
                        {{ auth()->user()->name }}
                    </a>
                    <div class="dropdown-menu dropdown-menu-right">
                        <a class="dropdown-item" href="{{ route('profile.edit') }}">
                            <i class="fas fa-user mr-2"></i>Profil
                        </a>
                        <div class="dropdown-divider"></div>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="dropdown-item">
                                <i class="fas fa-sign-out-alt mr-2"></i>Keluar
                            </button>
                        </form>
                    </div>
                </li>
                @else
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('login') }}">Masuk</a>
                </li>
                @endauth
            </ul>
        </nav>
        <!-- /.navbar -->

        <!-- Main Sidebar Container -->
        @auth
        <aside class="main-sidebar sidebar-light-primary elevation-2">
            <!-- Brand Logo -->
            @php
                $logo = App\Models\Setting::getValue('app_logo', null);
                $appName = App\Models\Setting::getValue('app_name', 'Sistem Absensi');
            @endphp
            
            <a href="{{ route('home') }}" class="brand-link">
                @if($logo)
                    <img src="{{ asset('storage/' . $logo) }}" alt="{{ $appName }}" class="brand-image">
                @else
                    <i class="fas fa-fingerprint brand-image"></i>
                @endif
                <span class="brand-text font-weight-light">{{ $appName }}</span>
            </a>

            <!-- Sidebar -->
            <div class="sidebar">
                <!-- Sidebar user (optional) -->
                <div class="user-panel mt-3 pb-3 mb-3 d-flex">
                    <div class="image">
                        <img src="{{ auth()->user()->photo ? asset('storage/' . auth()->user()->photo) : asset('images/avatar.png') }}" 
                             class="rounded-circle elevation-1" alt="User Image">
                    </div>
                    <div class="info">
                        <a href="{{ route('profile.edit') }}" class="d-block">{{ auth()->user()->name }}</a>
                        <small class="text-muted">{{ auth()->user()->isAdmin() ? 'Administrator' : 'Karyawan' }}</small>
                    </div>
                </div>

                <!-- Sidebar Menu -->
                <nav class="mt-2">
                    <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                        <li class="nav-item">
                            <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-tachometer-alt"></i>
                                <p>Dashboard</p>
                            </a>
                        </li>
                        
                        @if(auth()->user()->isAdmin())
                            <li class="nav-item">
                                <a href="{{ route('dashboard.analytics') }}" class="nav-link {{ request()->routeIs('dashboard.analytics') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-chart-line"></i>
                                    <p>Dashboard Analitik</p>
                                </a>
                            </li>
                            
                            <li class="nav-header">MANAJEMEN</li>
                            <li class="nav-item">
                                <a href="{{ route('admin.users.index') }}" class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-users"></i>
                                    <p>Kelola Pengguna</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('admin.roles.index') }}" class="nav-link {{ request()->routeIs('admin.roles.*') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-user-tag"></i>
                                    <p>Manajemen Peran</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('admin.attendance.report') }}" class="nav-link {{ request()->routeIs('admin.attendance.report') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-chart-bar"></i>
                                    <p>Laporan Absensi</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('admin.attendance.monitor') }}" class="nav-link {{ request()->routeIs('admin.attendance.monitor') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-desktop"></i>
                                    <p>Monitoring Absensi</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('admin.leave.index') }}" class="nav-link {{ request()->routeIs('admin.leave.*') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-calendar-minus"></i>
                                    <p>Kelola Perizinan</p>
                                </a>
                            </li>
                            
                            <li class="nav-header">NOTIFIKASI</li>
                            <li class="nav-item">
                                <a href="{{ route('notifications.broadcast') }}" class="nav-link {{ request()->routeIs('notifications.broadcast') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-paper-plane"></i>
                                    <p>Broadcast Pesan</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('notifications.settings') }}" class="nav-link {{ request()->routeIs('notifications.settings') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-bell"></i>
                                    <p>Pengaturan Notifikasi</p>
                                </a>
                            </li>
                            
                            <li class="nav-header">SISTEM</li>
                            <li class="nav-item">
                                <a href="{{ route('calendar.index') }}" class="nav-link {{ request()->routeIs('calendar.index') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-calendar-alt"></i>
                                    <p>Kalender</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('documents.index') }}" class="nav-link {{ request()->routeIs('documents.*') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-file-alt"></i>
                                    <p>Manajemen Dokumen</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('support.index') }}" class="nav-link {{ request()->routeIs('support.*') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-ticket-alt"></i>
                                    <p>Tiket Dukungan</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('admin.workdays.index') }}" class="nav-link {{ request()->routeIs('admin.workdays.*') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-calendar-week"></i>
                                    <p>Hari Kerja</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('admin.holidays.index') }}" class="nav-link {{ request()->routeIs('admin.holidays.*') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-calendar-day"></i>
                                    <p>Hari Libur</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('admin.locations.index') }}" class="nav-link {{ request()->routeIs('admin.locations.*') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-map-marker-alt"></i>
                                    <p>Lokasi Absensi</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('admin.settings.index') }}" class="nav-link {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-cogs"></i>
                                    <p>Pengaturan</p>
                                </a>
                            </li>
                        @else
                            <li class="nav-header">ABSENSI</li>
                            <li class="nav-item">
                                <a href="{{ route('attendance.check-in.form') }}" class="nav-link {{ request()->routeIs('attendance.check-in.form') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-sign-in-alt"></i>
                                    <p>Absen Masuk</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('attendance.check-out.form') }}" class="nav-link {{ request()->routeIs('attendance.check-out.form') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-sign-out-alt"></i>
                                    <p>Absen Pulang</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('attendance.qr-generate') }}" class="nav-link {{ request()->routeIs('attendance.qr-generate') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-qrcode"></i>
                                    <p>QR Code Absensi</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('attendance.history') }}" class="nav-link {{ request()->routeIs('attendance.history') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-history"></i>
                                    <p>Riwayat Absensi</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('leave.index') }}" class="nav-link {{ request()->routeIs('leave.*') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-calendar-minus"></i>
                                    <p>Pengajuan Izin</p>
                                </a>
                            </li>
                            
                            <li class="nav-header">LAINNYA</li>
                            <li class="nav-item">
                                <a href="{{ route('calendar.index') }}" class="nav-link {{ request()->routeIs('calendar.index') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-calendar-alt"></i>
                                    <p>Kalender</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('documents.index') }}" class="nav-link {{ request()->routeIs('documents.*') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-file-alt"></i>
                                    <p>Dokumen Saya</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('support.index') }}" class="nav-link {{ request()->routeIs('support.*') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-headset"></i>
                                    <p>Layanan Dukungan</p>
                                </a>
                            </li>
                        @endif
                    </ul>
                </nav>
                <!-- /.sidebar-menu -->
            </div>
            <!-- /.sidebar -->
        </aside>
        @endauth

        <!-- Content Wrapper. Contains page content -->
        <div class="content-wrapper {{ !auth()->check() ? 'ml-0' : '' }}">
            <!-- Content Header (Page header) -->
            <div class="content-header">
                <div class="container-fluid">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible">
                            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                            {{ session('success') }}
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible">
                            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                            {{ session('error') }}
                        </div>
                    @endif
                </div>
            </div>
            <!-- /.content-header -->

            <!-- Main content -->
            <section class="content">
                <div class="container-fluid">
                    @yield('content')
                </div>
            </section>
            <!-- /.content -->
        </div>
        <!-- /.content-wrapper -->

        <!-- Footer -->
        <footer class="main-footer">
            <div class="float-right d-none d-sm-block">
                <b>Versi</b> 1.0.0
            </div>
            <strong>&copy; {{ date('Y') }} {{ App\Models\Setting::getValue('app_name', 'Sistem Absensi Sekolah') }}.</strong> Hak Cipta Dilindungi.
        </footer>
    </div>
    <!-- ./wrapper -->

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    
    <!-- Bootstrap 4 -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- AdminLTE App -->
    <script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>
    
    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    
    <script>
        // Live clock
        function updateClock() {
            const now = new Date();
            
            // Format tanggal
            const days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
            const months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
            
            const day = days[now.getDay()];
            const date = now.getDate();
            const month = months[now.getMonth()];
            const year = now.getFullYear();
            
            // Format waktu
            let hours = now.getHours();
            let minutes = now.getMinutes();
            let seconds = now.getSeconds();
            
            // Tambahkan leading zero jika perlu
            hours = hours < 10 ? '0' + hours : hours;
            minutes = minutes < 10 ? '0' + minutes : minutes;
            seconds = seconds < 10 ? '0' + seconds : seconds;
            
            // Gabungkan format
            const timeString = `${day}, ${date} ${month} ${year} - ${hours}:${minutes}:${seconds} WIB`;
            
            document.getElementById('current-time').textContent = timeString;
            setTimeout(updateClock, 1000);
        }
        
        updateClock();
    </script>
    
    @yield('scripts')
    @stack('scripts')
</body>
</html> 