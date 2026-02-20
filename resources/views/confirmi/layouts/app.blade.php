<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') - Confirmi</title>

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        :root {
            --royal-blue: #1e3a8a;
            --royal-blue-light: #2563eb;
            --royal-blue-dark: #1e40af;
            --royal-blue-50: #eff6ff;
            --royal-blue-100: #dbeafe;
            --royal-blue-200: #bfdbfe;
            --royal-blue-600: #2563eb;
            --royal-blue-700: #1d4ed8;
            --royal-blue-800: #1e40af;
            --royal-blue-900: #1e3a8a;
            --sidebar-width: 260px;
            --header-height: 60px;
            --bg-main: #f0f4ff;
            --text-dark: #0f172a;
            --text-muted: #64748b;
            --border: #e2e8f0;
            --white: #ffffff;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--bg-main);
            color: var(--text-dark);
            min-height: 100vh;
        }

        /* ===== SIDEBAR ===== */
        .sidebar {
            position: fixed;
            top: 0; left: 0;
            width: var(--sidebar-width);
            height: 100vh;
            background: linear-gradient(180deg, var(--royal-blue-900) 0%, var(--royal-blue-dark) 100%);
            color: white;
            z-index: 1000;
            transition: transform 0.3s ease;
            display: flex;
            flex-direction: column;
            overflow-y: auto;
        }

        .sidebar-brand {
            height: var(--header-height);
            padding: 0 1.25rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }

        .sidebar-brand .brand-icon {
            width: 36px; height: 36px;
            background: rgba(255,255,255,0.15);
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.1rem;
        }

        .sidebar-brand .brand-text {
            font-size: 1.1rem;
            font-weight: 700;
            letter-spacing: -0.3px;
        }

        .sidebar-menu { flex: 1; padding: 0.75rem 0; }

        .menu-section {
            padding: 0.5rem 1.25rem 0.25rem;
            font-size: 0.65rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: rgba(255,255,255,0.4);
            margin-top: 0.5rem;
        }

        .menu-link {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.6rem 1.25rem;
            color: rgba(255,255,255,0.7);
            text-decoration: none;
            font-size: 0.85rem;
            font-weight: 500;
            border-radius: 0;
            transition: all 0.2s;
            border-left: 3px solid transparent;
        }

        .menu-link:hover {
            background: rgba(255,255,255,0.08);
            color: white;
        }

        .menu-link.active {
            background: rgba(255,255,255,0.12);
            color: white;
            border-left-color: var(--royal-blue-200);
        }

        .menu-link i { width: 18px; text-align: center; font-size: 0.9rem; }

        .menu-badge {
            margin-left: auto;
            background: var(--danger);
            color: white;
            font-size: 0.65rem;
            padding: 0.15rem 0.5rem;
            border-radius: 10px;
            font-weight: 600;
        }

        .sidebar-footer {
            padding: 0.75rem 1.25rem;
            border-top: 1px solid rgba(255,255,255,0.1);
        }

        /* ===== HEADER ===== */
        .main-header {
            position: fixed;
            top: 0;
            left: var(--sidebar-width);
            right: 0;
            height: var(--header-height);
            background: var(--white);
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 1.5rem;
            z-index: 999;
        }

        .header-title {
            font-size: 1.15rem;
            font-weight: 700;
            color: var(--text-dark);
        }

        .header-actions {
            display: flex; align-items: center; gap: 1rem;
        }

        .user-pill {
            display: flex; align-items: center; gap: 0.5rem;
            padding: 0.35rem 0.75rem;
            background: var(--royal-blue-50);
            border-radius: 50px;
            font-size: 0.8rem;
            font-weight: 600;
            color: var(--royal-blue-700);
        }

        .user-pill .avatar {
            width: 28px; height: 28px;
            border-radius: 50%;
            background: var(--royal-blue-600);
            color: white;
            display: flex; align-items: center; justify-content: center;
            font-size: 0.75rem; font-weight: 700;
        }

        /* ===== CONTENT ===== */
        .main-content {
            margin-left: var(--sidebar-width);
            margin-top: var(--header-height);
            padding: 1.5rem;
            min-height: calc(100vh - var(--header-height));
        }

        /* ===== MOBILE ===== */
        .mobile-toggle {
            display: none;
            position: fixed;
            top: 0.75rem; left: 0.75rem;
            z-index: 1002;
            width: 40px; height: 40px;
            background: var(--royal-blue-600);
            color: white; border: none;
            border-radius: 10px;
            cursor: pointer;
            align-items: center; justify-content: center;
        }

        .mobile-overlay {
            display: none;
            position: fixed; top: 0; left: 0;
            width: 100%; height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 999;
        }

        @media (max-width: 768px) {
            .sidebar { transform: translateX(-100%); }
            .sidebar.active { transform: translateX(0); box-shadow: 0 0 30px rgba(0,0,0,0.3); }
            .mobile-overlay.active { display: block; }
            .main-header { left: 0; padding-left: 4rem; }
            .main-content { margin-left: 0; padding: 1rem; }
            .mobile-toggle { display: flex; }
        }

        /* ===== CARDS ===== */
        .stat-card {
            background: var(--white);
            border-radius: 12px;
            padding: 1.25rem;
            border: 1px solid var(--border);
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(30,58,138,0.08);
        }

        .stat-card .stat-icon {
            width: 42px; height: 42px;
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.1rem;
        }

        .stat-card .stat-value {
            font-size: 1.75rem;
            font-weight: 800;
            color: var(--text-dark);
            line-height: 1;
        }

        .stat-card .stat-label {
            font-size: 0.78rem;
            color: var(--text-muted);
            font-weight: 500;
        }

        .icon-blue { background: var(--royal-blue-100); color: var(--royal-blue-600); }
        .icon-green { background: #d1fae5; color: #059669; }
        .icon-orange { background: #fef3c7; color: #d97706; }
        .icon-red { background: #fee2e2; color: #dc2626; }
        .icon-purple { background: #ede9fe; color: #7c3aed; }

        /* ===== TABLE ===== */
        .content-card {
            background: var(--white);
            border-radius: 12px;
            border: 1px solid var(--border);
            overflow: hidden;
        }

        .content-card .card-header-custom {
            padding: 1rem 1.25rem;
            border-bottom: 1px solid var(--border);
            display: flex; align-items: center; justify-content: space-between;
        }

        .content-card .card-header-custom h6 {
            font-weight: 700; margin: 0;
        }

        .table-modern {
            margin: 0;
        }

        .table-modern th {
            font-size: 0.72rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: var(--text-muted);
            border-bottom: 1px solid var(--border);
            padding: 0.75rem 1rem;
            background: #fafbfd;
        }

        .table-modern td {
            padding: 0.75rem 1rem;
            font-size: 0.85rem;
            vertical-align: middle;
            border-bottom: 1px solid #f1f5f9;
        }

        /* ===== BUTTONS ===== */
        .btn-royal {
            background: var(--royal-blue-600);
            border-color: var(--royal-blue-600);
            color: white;
            font-weight: 600;
            font-size: 0.85rem;
            border-radius: 8px;
            padding: 0.5rem 1rem;
        }

        .btn-royal:hover {
            background: var(--royal-blue-700);
            border-color: var(--royal-blue-700);
            color: white;
        }

        .btn-outline-royal {
            border: 1.5px solid var(--royal-blue-600);
            color: var(--royal-blue-600);
            font-weight: 600;
            font-size: 0.85rem;
            border-radius: 8px;
            padding: 0.5rem 1rem;
            background: transparent;
        }

        .btn-outline-royal:hover {
            background: var(--royal-blue-600);
            color: white;
        }

        /* ===== BADGES ===== */
        .badge-status {
            font-size: 0.7rem;
            font-weight: 600;
            padding: 0.3rem 0.65rem;
            border-radius: 6px;
        }

        .badge-pending { background: #fef3c7; color: #92400e; }
        .badge-assigned { background: var(--royal-blue-100); color: var(--royal-blue-800); }
        .badge-in-progress { background: #ede9fe; color: #5b21b6; }
        .badge-confirmed { background: #d1fae5; color: #065f46; }
        .badge-delivered { background: #cffafe; color: #155e75; }
        .badge-cancelled { background: #fee2e2; color: #991b1b; }

        /* ===== ALERTS ===== */
        .alert { border-radius: 10px; font-size: 0.85rem; border: none; }

        @yield('css')
    </style>
</head>
<body>
    @php
        $user = auth('confirmi')->user();
        $isCommercial = $user && $user->isCommercial();
        $isEmployee = $user && $user->isEmployee();
        $pendingRequestsCount = $isCommercial ? \App\Models\ConfirmiRequest::where('status', 'pending')->count() : 0;
        $pendingOrdersCount = $isCommercial
            ? \App\Models\ConfirmiOrderAssignment::where('status', 'pending')->count()
            : \App\Models\ConfirmiOrderAssignment::where('assigned_to', $user->id)->whereIn('status', ['assigned', 'in_progress'])->count();
    @endphp

    <div class="mobile-overlay" id="mobileOverlay" onclick="closeSidebar()"></div>
    <button class="mobile-toggle" onclick="toggleSidebar()"><i class="fas fa-bars"></i></button>

    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-brand">
            <div class="brand-icon"><i class="fas fa-headset"></i></div>
            <span class="brand-text">Confirmi</span>
        </div>

        <nav class="sidebar-menu">
            <div class="menu-section">Principal</div>
            <a href="{{ route('confirmi.dashboard') }}" class="menu-link {{ request()->routeIs('confirmi.dashboard') ? 'active' : '' }}">
                <i class="fas fa-chart-pie"></i><span>Dashboard</span>
            </a>

            @if($isCommercial)
                <div class="menu-section">Commercial</div>
                <a href="{{ route('confirmi.commercial.orders.pending') }}" class="menu-link {{ request()->routeIs('confirmi.commercial.orders.pending') ? 'active' : '' }}">
                    <i class="fas fa-clock"></i><span>En attente</span>
                    @if($pendingOrdersCount > 0)
                        <span class="menu-badge">{{ $pendingOrdersCount }}</span>
                    @endif
                </a>
                <a href="{{ route('confirmi.commercial.orders.index') }}" class="menu-link {{ request()->routeIs('confirmi.commercial.orders.index') ? 'active' : '' }}">
                    <i class="fas fa-list-check"></i><span>Toutes les commandes</span>
                </a>
                <a href="{{ route('confirmi.commercial.admins') }}" class="menu-link {{ request()->routeIs('confirmi.commercial.admins') ? 'active' : '' }}">
                    <i class="fas fa-building"></i><span>Clients Confirmi</span>
                </a>

                <div class="menu-section">Gestion</div>
                <a href="{{ route('confirmi.commercial.employees.index') }}" class="menu-link {{ request()->routeIs('confirmi.commercial.employees.*') ? 'active' : '' }}">
                    <i class="fas fa-users"></i><span>Employés</span>
                </a>
                <a href="{{ route('confirmi.commercial.requests.index') }}" class="menu-link {{ request()->routeIs('confirmi.commercial.requests.*') ? 'active' : '' }}">
                    <i class="fas fa-inbox"></i><span>Demandes</span>
                    @if($pendingRequestsCount > 0)
                        <span class="menu-badge">{{ $pendingRequestsCount }}</span>
                    @endif
                </a>
            @endif

            @if($isEmployee)
                <div class="menu-section">Mes Commandes</div>
                <a href="{{ route('confirmi.employee.orders.queue') }}" class="menu-link {{ request()->routeIs('confirmi.employee.orders.process') ? 'active' : '' }}">
                    <i class="fas fa-headset"></i><span>Démarrer traitement</span>
                    @if($pendingOrdersCount > 0)
                        <span class="menu-badge">{{ $pendingOrdersCount }}</span>
                    @endif
                </a>
                <a href="{{ route('confirmi.employee.orders.index') }}" class="menu-link {{ request()->routeIs('confirmi.employee.orders.index') ? 'active' : '' }}">
                    <i class="fas fa-list"></i><span>Toutes mes commandes</span>
                </a>
                <a href="{{ route('confirmi.employee.orders.history') }}" class="menu-link {{ request()->routeIs('confirmi.employee.orders.history') ? 'active' : '' }}">
                    <i class="fas fa-history"></i><span>Historique</span>
                </a>
            @endif
        </nav>

        <div class="sidebar-footer">
            <form action="{{ route('confirmi.logout') }}" method="POST">
                @csrf
                <button type="submit" class="menu-link w-100 border-0 bg-transparent text-start" style="color: rgba(255,255,255,0.6);">
                    <i class="fas fa-sign-out-alt"></i><span>Déconnexion</span>
                </button>
            </form>
        </div>
    </aside>

    <!-- Header -->
    <header class="main-header">
        <h1 class="header-title">@yield('page-title', 'Dashboard')</h1>
        <div class="header-actions">
            <div class="user-pill">
                <div class="avatar">{{ strtoupper(substr($user->name, 0, 1)) }}</div>
                <span>{{ $user->name }}</span>
                <span class="badge bg-{{ $isCommercial ? 'primary' : 'info' }}" style="font-size:0.6rem;">{{ $isCommercial ? 'Commercial' : 'Employé' }}</span>
            </div>
        </div>
    </header>

    <!-- Content -->
    <main class="main-content">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @yield('content')
    </main>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('active');
            document.getElementById('mobileOverlay').classList.toggle('active');
        }
        function closeSidebar() {
            document.getElementById('sidebar').classList.remove('active');
            document.getElementById('mobileOverlay').classList.remove('active');
        }
        if (window.innerWidth <= 768) {
            document.querySelectorAll('.menu-link[href]').forEach(l => l.addEventListener('click', closeSidebar));
        }
    </script>
    @yield('scripts')
</body>
</html>
