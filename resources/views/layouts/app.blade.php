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
                    <div class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                        <h6 class="dropdown-header">{{ Auth::user()->name }}</h6>
                        <a class="dropdown-item" href="{{ route('profile.edit') }}">
                            <i class="bi bi-person-circle me-2"></i>Profil
                        </a>
                        <a class="dropdown-item" href="{{ route('face.register.form') }}">
                            <i class="bi bi-camera-fill me-2"></i>Pendaftaran Wajah
                        </a>
                        <div class="dropdown-divider"></div>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="dropdown-item text-danger">
                                <i class="bi bi-box-arrow-right me-2"></i>Logout
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
                        <small class="text-muted">{{ auth()->user()->role->name }}</small>
                    </div>
                </div>

                <!-- Sidebar Menu -->
                @include('layouts.sidebar')
            </div>
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