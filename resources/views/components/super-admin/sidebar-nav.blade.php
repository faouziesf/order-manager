<div class="nav-section">
    <div class="nav-section-title">Principal</div>
    
    <div class="nav-item">
        <a href="{{ route('super-admin.dashboard') }}" class="nav-link {{ request()->routeIs('super-admin.dashboard') ? 'active' : '' }}">
            <div class="nav-icon">
                <i class="fas fa-tachometer-alt"></i>
            </div>
            <span class="nav-text">Tableau de bord</span>
        </a>
    </div>
    
    <div class="nav-item">
        <a href="{{ route('super-admin.admins.index') }}" class="nav-link {{ request()->routeIs('super-admin.admins.*') ? 'active' : '' }}">
            <div class="nav-icon">
                <i class="fas fa-users"></i>
            </div>
            <span class="nav-text">Administrateurs</span>
            @php
                $pendingAdmins = \App\Models\Admin::where('is_active', false)->count();
            @endphp
            @if($pendingAdmins > 0)
                <span class="nav-badge">{{ $pendingAdmins }}</span>
            @endif
        </a>
    </div>
</div>

<div class="nav-section">
    <div class="nav-section-title">Analytics</div>
    
    <div class="nav-item">
        <a href="{{ route('super-admin.analytics.index') }}" class="nav-link {{ request()->routeIs('super-admin.analytics.*') ? 'active' : '' }}">
            <div class="nav-icon">
                <i class="fas fa-chart-line"></i>
            </div>
            <span class="nav-text">Analytics</span>
        </a>
    </div>
    
    <div class="nav-item">
        <a href="{{ route('super-admin.reports.index') }}" class="nav-link {{ request()->routeIs('super-admin.reports.*') ? 'active' : '' }}">
            <div class="nav-icon">
                <i class="fas fa-file-alt"></i>
            </div>
            <span class="nav-text">Rapports</span>
        </a>
    </div>
</div>

<div class="nav-section">
    <div class="nav-section-title">Communication</div>
    
    <div class="nav-item">
        <a href="{{ route('super-admin.notifications.index') }}" class="nav-link {{ request()->routeIs('super-admin.notifications.*') ? 'active' : '' }}">
            <div class="nav-icon">
                <i class="fas fa-bell"></i>
            </div>
            <span class="nav-text">Notifications</span>
            @php
                $unreadNotifications = \App\Models\SuperAdminNotification::whereNull('read_at')->count();
            @endphp
            @if($unreadNotifications > 0)
                <span class="nav-badge">{{ $unreadNotifications }}</span>
            @endif
        </a>
    </div>
</div>

<div class="nav-section">
    <div class="nav-section-title">Système</div>
    
    <div class="nav-item">
        <a href="{{ route('super-admin.system.index') }}" class="nav-link {{ request()->routeIs('super-admin.system.*') ? 'active' : '' }}">
            <div class="nav-icon">
                <i class="fas fa-server"></i>
            </div>
            <span class="nav-text">Monitoring</span>
        </a>
    </div>
    
    <div class="nav-item">
        <a href="{{ route('super-admin.settings.index') }}" class="nav-link {{ request()->routeIs('super-admin.settings.*') ? 'active' : '' }}">
            <div class="nav-icon">
                <i class="fas fa-cog"></i>
            </div>
            <span class="nav-text">Paramètres</span>
        </a>
    </div>
</div>

<div class="nav-section">
    <div class="nav-section-title">Actions rapides</div>
    
    <div class="nav-item">
        <a href="{{ route('super-admin.admins.create') }}" class="nav-link">
            <div class="nav-icon">
                <i class="fas fa-plus"></i>
            </div>
            <span class="nav-text">Nouvel Admin</span>
        </a>
    </div>
    
    <div class="nav-item">
        <a href="{{ route('super-admin.system.backups') }}" class="nav-link">
            <div class="nav-icon">
                <i class="fas fa-download"></i>
            </div>
            <span class="nav-text">Sauvegardes</span>
        </a>
    </div>
</div>