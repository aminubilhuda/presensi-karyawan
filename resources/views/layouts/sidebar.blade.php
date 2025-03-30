<!-- Sidebar Menu -->
<nav class="mt-2">
    <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
        <!-- Dashboard -->
        <li class="nav-item">
            <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <i class="nav-icon fas fa-tachometer-alt"></i>
                <p>Dashboard</p>
            </a>
        </li>
        
        <!-- Menu Absensi -->
        @if(auth()->user()->hasAnyPermission(['attendance.check-in', 'attendance.check-out', 'attendance.history', 'attendance.qr-generate']))
        <li class="nav-header">ABSENSI</li>
        
        @if(auth()->user()->hasPermission('attendance.check-in'))
        <li class="nav-item">
            <a href="{{ route('attendance.check-in.form') }}" class="nav-link {{ request()->routeIs('attendance.check-in.form') ? 'active' : '' }}">
                <i class="nav-icon fas fa-sign-in-alt"></i>
                <p>Absen Masuk</p>
            </a>
        </li>
        @endif
        
        @if(auth()->user()->hasPermission('attendance.check-out'))
        <li class="nav-item">
            <a href="{{ route('attendance.check-out.form') }}" class="nav-link {{ request()->routeIs('attendance.check-out.form') ? 'active' : '' }}">
                <i class="nav-icon fas fa-sign-out-alt"></i>
                <p>Absen Pulang</p>
            </a>
        </li>
        @endif
        
        @if(auth()->user()->hasPermission('attendance.qr-generate'))
        <li class="nav-item">
            <a href="{{ route('attendance.qr-generate') }}" class="nav-link {{ request()->routeIs('attendance.qr-generate') ? 'active' : '' }}">
                <i class="nav-icon fas fa-qrcode"></i>
                <p>QR Code Absensi</p>
            </a>
        </li>
        @endif
        
        @if(auth()->user()->hasPermission('attendance.history'))
        <li class="nav-item">
            <a href="{{ route('attendance.history') }}" class="nav-link {{ request()->routeIs('attendance.history') ? 'active' : '' }}">
                <i class="nav-icon fas fa-history"></i>
                <p>Riwayat Absensi</p>
            </a>
        </li>
        @endif
        @endif
        
        <!-- Menu Admin - ditampilkan jika memiliki MINIMAL SATU permission admin -->
        @if(auth()->user()->hasAnyPermission([
            'admin.users.view', 'admin.users.create', 'admin.users.edit', 'admin.users.delete',
            'admin.roles.view', 'admin.roles.create', 'admin.roles.edit', 'admin.roles.delete',
            'admin.permissions.manage',
            'admin.attendance.report', 'admin.attendance.export', 'admin.attendance.monitor',
            'admin.leave.view', 'admin.leave.approve', 'admin.leave.reject', 'admin.leave.export',
            'admin.workdays.manage', 'admin.holidays.manage', 'admin.locations.manage',
            'admin.settings.manage',
            'admin.notifications.broadcast', 'admin.notifications.settings'
        ]))
        <li class="nav-header">ADMINISTRASI</li>
        
        <!-- Manajemen Pengguna -->
        @if(auth()->user()->hasPermission('admin.users.view'))
        <li class="nav-item">
            <a href="{{ route('admin.users.index') }}" class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                <i class="nav-icon fas fa-users"></i>
                <p>Manajemen Pengguna</p>
            </a>
        </li>
        @endif
        
        <!-- Laporan Absensi -->
        @if(auth()->user()->hasPermission('admin.attendance.report'))
        <li class="nav-item">
            <a href="{{ route('admin.attendance.report') }}" class="nav-link {{ request()->routeIs('admin.attendance.report') ? 'active' : '' }}">
                <i class="nav-icon fas fa-chart-bar"></i>
                <p>Laporan Absensi</p>
            </a>
        </li>
        @endif
        
        <!-- Monitoring Absensi -->
        @if(auth()->user()->hasPermission('admin.attendance.monitor'))
        <li class="nav-item">
            <a href="{{ route('admin.attendance.monitor') }}" class="nav-link {{ request()->routeIs('admin.attendance.monitor') ? 'active' : '' }}">
                <i class="nav-icon fas fa-desktop"></i>
                <p>Monitoring Absensi</p>
            </a>
        </li>
        @endif
        
        <!-- Manajemen Izin/Cuti -->
        @if(auth()->user()->hasPermission('admin.leave.view'))
        <li class="nav-item">
            <a href="{{ route('admin.leave.index') }}" class="nav-link {{ request()->routeIs('admin.leave.*') ? 'active' : '' }}">
                <i class="nav-icon fas fa-calendar-alt"></i>
                <p>Manajemen Izin/Cuti</p>
            </a>
        </li>
        @endif
        
        <!-- Kelola Peran -->
        @if(auth()->user()->hasPermission('admin.roles.view'))
        <li class="nav-item">
            <a href="{{ route('admin.roles.index') }}" class="nav-link {{ request()->routeIs('admin.roles.*') ? 'active' : '' }}">
                <i class="nav-icon fas fa-user-tag"></i>
                <p>Kelola Peran</p>
            </a>
        </li>
        @endif
        
        <!-- Izin Akses -->
        @if(auth()->user()->hasPermission('admin.permissions.manage'))
        <li class="nav-item">
            <a href="{{ route('admin.permissions.index') }}" class="nav-link {{ request()->routeIs('admin.permissions.*') ? 'active' : '' }}">
                <i class="nav-icon fas fa-key"></i>
                <p>Izin Akses</p>
            </a>
        </li>
        @endif
        
        <!-- Pengaturan System - Tampilkan sub-menu jika minimal 1 permission terkait -->
        @if(auth()->user()->hasAnyPermission(['admin.workdays.manage', 'admin.holidays.manage', 'admin.locations.manage', 'admin.settings.manage']))
        <li class="nav-item has-treeview {{ request()->routeIs('admin.workdays.*') || request()->routeIs('admin.holidays.*') || request()->routeIs('admin.locations.*') || request()->routeIs('admin.settings.*') ? 'menu-open' : '' }}">
            <a href="#" class="nav-link">
                <i class="nav-icon fas fa-cogs"></i>
                <p>
                    Pengaturan
                    <i class="right fas fa-angle-left"></i>
                </p>
            </a>
            <ul class="nav nav-treeview">
                @if(auth()->user()->hasPermission('admin.workdays.manage'))
                <li class="nav-item">
                    <a href="{{ route('admin.workdays.index') }}" class="nav-link {{ request()->routeIs('admin.workdays.*') ? 'active' : '' }}">
                        <i class="far fa-circle nav-icon"></i>
                        <p>Hari Kerja</p>
                    </a>
                </li>
                @endif
                
                @if(auth()->user()->hasPermission('admin.holidays.manage'))
                <li class="nav-item">
                    <a href="{{ route('admin.holidays.index') }}" class="nav-link {{ request()->routeIs('admin.holidays.*') ? 'active' : '' }}">
                        <i class="far fa-circle nav-icon"></i>
                        <p>Hari Libur</p>
                    </a>
                </li>
                @endif
                
                @if(auth()->user()->hasPermission('admin.locations.manage'))
                <li class="nav-item">
                    <a href="{{ route('admin.locations.index') }}" class="nav-link {{ request()->routeIs('admin.locations.*') ? 'active' : '' }}">
                        <i class="far fa-circle nav-icon"></i>
                        <p>Lokasi Absensi</p>
                    </a>
                </li>
                @endif
                
                @if(auth()->user()->hasPermission('admin.settings.manage'))
                <li class="nav-item">
                    <a href="{{ route('admin.settings.index') }}" class="nav-link {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}">
                        <i class="far fa-circle nav-icon"></i>
                        <p>Pengaturan Aplikasi</p>
                    </a>
                </li>
                @endif
            </ul>
        </li>
        @endif
        
        <!-- Notifikasi - hanya untuk admin -->
        @if(auth()->user()->hasAnyPermission(['admin.notifications.broadcast', 'admin.notifications.settings']))
        <li class="nav-item has-treeview {{ request()->routeIs('notifications.*') ? 'menu-open' : '' }}">
            <a href="#" class="nav-link">
                <i class="nav-icon fas fa-bell"></i>
                <p>
                    Notifikasi
                    <i class="right fas fa-angle-left"></i>
                </p>
            </a>
            <ul class="nav nav-treeview">
                @if(auth()->user()->hasPermission('admin.notifications.broadcast'))
                <li class="nav-item">
                    <a href="{{ route('notifications.broadcast') }}" class="nav-link {{ request()->routeIs('notifications.broadcast') ? 'active' : '' }}">
                        <i class="far fa-circle nav-icon"></i>
                        <p>Broadcast Pesan</p>
                    </a>
                </li>
                @endif
                
                @if(auth()->user()->hasPermission('admin.notifications.settings'))
                <li class="nav-item">
                    <a href="{{ route('notifications.settings') }}" class="nav-link {{ request()->routeIs('notifications.settings') ? 'active' : '' }}">
                        <i class="far fa-circle nav-icon"></i>
                        <p>Pengaturan Notifikasi</p>
                    </a>
                </li>
                @endif
            </ul>
        </li>
        @endif
        @endif
        
        <!-- Fitur lainnya dapat ditambahkan di sini -->
    </ul>
</nav> 