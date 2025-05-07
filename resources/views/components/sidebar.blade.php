<ul class="sidebar-menu">
    <li class="sidebar-item">
        <a href="{{ route('super-admin.dashboard') }}" class="sidebar-link {{ request()->routeIs('super-admin.dashboard') ? 'active' : '' }}">
            <div class="sidebar-icon">
                <i class="fas fa-tachometer-alt"></i>
            </div>
            <span class="sidebar-text">Tableau de bord</span>
        </a>
    </li>
    
    <li class="sidebar-item">
        <a href="{{ route('super-admin.admins.index') }}" class="sidebar-link {{ request()->routeIs('super-admin.admins*') ? 'active' : '' }}">
            <div class="sidebar-icon">
                <i class="fas fa-users-cog"></i>
            </div>
            <span class="sidebar-text">Administrateurs</span>
        </a>
    </li>
    
    <li class="sidebar-item">
        <a href="{{ route('super-admin.settings.index') }}" class="sidebar-link {{ request()->routeIs('super-admin.settings*') ? 'active' : '' }}">
            <div class="sidebar-icon">
                <i class="fas fa-cogs"></i>
            </div>
            <span class="sidebar-text">Paramètres</span>
        </a>
    </li>
</ul>