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
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
    
    <!-- Custom CSS -->
    <style>
        body {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            padding-top: 56px;
        }

        .navbar {
            position: fixed;
            top: 0;
            right: 0;
            left: 0;
            z-index: 1030;
        }
        
        #sidebar {
                min-width: 250px;
                max-width: 250px;
                min-height: calc(100vh - 56px);
                transition: all 0.3s;
                position: sticky;
                top: 56px; /* Posisi sticky dimulai setelah navbar */
                height: calc(100vh - 56px);
                overflow-y: auto;
                 max-height: calc(100vh - 56px);
        }

        /* Styling untuk scrollbar sidebar (opsional) */
        #sidebar::-webkit-scrollbar {
            width: 5px;
        }

        #sidebar::-webkit-scrollbar-track {
            background: #f1f1f1;
        }

        #sidebar::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 5px;
        }

        #sidebar::-webkit-scrollbar-thumb:hover {
            background: #555;
        }
        
        .content {
            flex: 1;
            width: 100%;
        }
        
        .sidebar-link {
            display: flex;
            align-items: center;
            padding: 10px 15px;
            transition: all 0.3s;
            text-decoration: none;
            color: #495057;
        }
        
        .sidebar-link:hover {
            background-color: #e9ecef;
            color: #212529;
        }
        
        .sidebar-link.active {
            background-color: #0d6efd;
            color: white;
        }
        
        .sidebar-link i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
        }
        
        @media (max-width: 768px) {
            #sidebar {
                margin-left: -250px;
            }
            
            #sidebar.active {
                margin-left: 0;
            }
        }
        
        #map {
            height: 400px;
            width: 100%;
        }
        
        .app-logo {
            max-height: 30px;
            margin-right: 8px;
        }
    </style>
    
    @yield('styles')
</head>
<body>
    <!-- Header -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container-fluid">
            <button type="button" id="sidebarCollapse" class="btn btn-primary">
                <i class="fas fa-bars"></i>
            </button>
            
            @php
                $logo = App\Models\Setting::getValue('app_logo', null);
                $appName = App\Models\Setting::getValue('app_name', 'Sistem Absensi Sekolah');
            @endphp
            
            <a class="navbar-brand ms-3" href="{{ route('home') }}">
                @if($logo)
                    <img src="{{ asset('storage/' . $logo) }}" alt="{{ $appName }}" class="app-logo">
                @endif
                {{ $appName }}
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <div class="text-light d-flex align-items-center">
                            <i class="far fa-clock me-2"></i>
                            <span id="current-time" class="fw-semibold"></span>
                        </div>
                    </li>
                </ul>
                <ul class="navbar-nav ms-auto">
                    @auth
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                                <img src="{{ auth()->user()->photo ? asset('storage/' . auth()->user()->photo) : asset('images/avatar.png') }}" 
                                     class="rounded-circle me-1" width="30" height="30" alt="Avatar"> 
                                {{ auth()->user()->name }}
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="{{ route('profile.edit') }}"><i class="fas fa-user me-2"></i>Profil</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="dropdown-item">
                                            <i class="fas fa-sign-out-alt me-2"></i>Keluar
                                        </button>
                                    </form>
                                </li>
                            </ul>
                        </li>
                    @else
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('login') }}">Masuk</a>
                        </li>
                    @endauth
                </ul>
            </div>
        </div>
    </nav>

    <div class="container-fluid p-0">
        <div class="row">
            <!-- Sidebar -->
            @auth
                <div id="sidebar" class="bg-light">
                    <div class="pt-3">
                        <ul class="nav flex-column">
                            <li class="nav-item">
                                <a href="{{ route('dashboard') }}" class="sidebar-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                                    <i class="fas fa-tachometer-alt"></i> Dashboard
                                </a>
                            </li>
                            
                            @if(auth()->user()->isAdmin())
                                <li class="nav-item">
                                    <a href="{{ route('dashboard.analytics') }}" class="sidebar-link {{ request()->routeIs('dashboard.analytics') ? 'active' : '' }}">
                                        <i class="fas fa-chart-line"></i> Dashboard Analitik
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('admin.users.index') }}" class="sidebar-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                                        <i class="fas fa-users"></i> Kelola Pengguna
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('admin.attendance.report') }}" class="sidebar-link {{ request()->routeIs('admin.attendance.report') ? 'active' : '' }}">
                                        <i class="fas fa-chart-bar"></i> Laporan Absensi
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('admin.attendance.monitor') }}" class="sidebar-link {{ request()->routeIs('admin.attendance.monitor') ? 'active' : '' }}">
                                        <i class="fas fa-desktop"></i> Monitoring Absensi
                                    </a>
                                </li>
                                <li class="nav-item dropdown">
                                    <a href="#" class="sidebar-link" data-bs-toggle="dropdown">
                                        <i class="fas fa-bell"></i> Notifikasi WhatsApp
                                    </a>
                                    <ul class="dropdown-menu">
                                        <li>
                                            <a href="{{ route('notifications.broadcast') }}" class="dropdown-item">
                                                <i class="fas fa-paper-plane me-2"></i> Broadcast Pesan
                                            </a>
                                        </li>
                                        <li>
                                            <a href="{{ route('notifications.settings') }}" class="dropdown-item">
                                                <i class="fas fa-cog me-2"></i> Pengaturan
                                            </a>
                                        </li>
                                    </ul>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('calendar.index') }}" class="sidebar-link {{ request()->routeIs('calendar.index') ? 'active' : '' }}">
                                        <i class="fas fa-calendar-alt"></i> Kalender
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('documents.index') }}" class="sidebar-link {{ request()->routeIs('documents.*') ? 'active' : '' }}">
                                        <i class="fas fa-file-alt"></i> Manajemen Dokumen
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('support.index') }}" class="sidebar-link {{ request()->routeIs('support.*') ? 'active' : '' }}">
                                        <i class="fas fa-ticket-alt"></i> Tiket Dukungan
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('admin.workdays.index') }}" class="sidebar-link {{ request()->routeIs('admin.workdays.*') ? 'active' : '' }}">
                                        <i class="fas fa-calendar-week"></i> Hari Kerja
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('admin.holidays.index') }}" class="sidebar-link {{ request()->routeIs('admin.holidays.*') ? 'active' : '' }}">
                                        <i class="fas fa-calendar-day"></i> Hari Libur
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('admin.locations.index') }}" class="sidebar-link {{ request()->routeIs('admin.locations.*') ? 'active' : '' }}">
                                        <i class="fas fa-map-marker-alt"></i> Lokasi Absensi
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('admin.settings.index') }}" class="sidebar-link {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}">
                                        <i class="fas fa-cogs"></i> Pengaturan
                                    </a>
                                </li>
                            @else
                                <li class="nav-item">
                                    <a href="{{ route('attendance.check-in') }}" class="sidebar-link {{ request()->routeIs('attendance.check-in') ? 'active' : '' }}">
                                        <i class="fas fa-sign-in-alt"></i> Absen Masuk
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('attendance.check-out') }}" class="sidebar-link {{ request()->routeIs('attendance.check-out') ? 'active' : '' }}">
                                        <i class="fas fa-sign-out-alt"></i> Absen Pulang
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('attendance.history') }}" class="sidebar-link {{ request()->routeIs('attendance.history') ? 'active' : '' }}">
                                        <i class="fas fa-history"></i> Riwayat Absensi
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('calendar.index') }}" class="sidebar-link {{ request()->routeIs('calendar.index') ? 'active' : '' }}">
                                        <i class="fas fa-calendar-alt"></i> Kalender
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('documents.index') }}" class="sidebar-link {{ request()->routeIs('documents.*') ? 'active' : '' }}">
                                        <i class="fas fa-file-alt"></i> Dokumen Saya
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('support.index') }}" class="sidebar-link {{ request()->routeIs('support.*') ? 'active' : '' }}">
                                        <i class="fas fa-headset"></i> Layanan Dukungan
                                    </a>
                                </li>
                            @endif
                        </ul>
                    </div>
                </div>
            @endauth

            <!-- Content -->
            <main class="content p-4 {{ auth()->check() ? 'col' : 'col-12' }}">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show">
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif
                    @yield('content')
            </main>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-dark text-white text-center py-3 mt-auto">
        <div class="container">
            <p class="mb-0">&copy; {{ date('Y') }} Sistem Absensi Sekolah. Hak Cipta Dilindungi.</p>
        </div>
    </footer>

    <!-- Bootstrap JS dengan Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    
    <script>
        // Toggle sidebar
        document.getElementById('sidebarCollapse').addEventListener('click', function() {
            document.getElementById('sidebar').classList.toggle('active');
        });
        
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