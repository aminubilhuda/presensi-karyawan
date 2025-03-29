<!-- Notifikasi -->
<li class="nav-item">
    <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#collapseNotifications"
        aria-expanded="false" aria-controls="collapseNotifications">
        <i class="fas fa-bell fa-fw"></i>
        <span>Notifikasi</span>
    </a>
    <div id="collapseNotifications" class="collapse" aria-labelledby="headingNotifications"
        data-bs-parent="#accordionSidebar">
        <div class="bg-white py-2 collapse-inner rounded">
            <a class="collapse-item" href="{{ route('notifications.broadcast') }}">
                <i class="fas fa-paper-plane fa-fw me-2"></i> Broadcast WhatsApp
            </a>
            <a class="collapse-item" href="{{ route('notifications.settings') }}">
                <i class="fas fa-cog fa-fw me-2"></i> Pengaturan
            </a>
        </div>
    </div>
</li> 