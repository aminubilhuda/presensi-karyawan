<!-- Sidebar Menu -->
<nav class="mt-2">
    <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
        <li class="nav-item">
            <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <i class="nav-icon fas fa-tachometer-alt"></i>
                <p>Dashboard</p>
            </a>
        </li>
        
        @if(auth()->user()->role->name == 'Admin')
        // ... existing code ...
        @endif
        
        @if(in_array(auth()->user()->role->name, ['Guru', 'Staf TU']))
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
        @endif
        
        // ... existing code ...
    </ul>
</nav> 