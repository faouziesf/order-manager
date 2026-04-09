<!DOCTYPE html>
<html lang="fr" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes, viewport-fit=cover">
    <meta name="theme-color" content="#1e40af">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') - Confirmi</title>

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        /* ══════════════════════════════════════════
           THEME SYSTEM — full contrast in both modes
           ══════════════════════════════════════════ */
        [data-theme="light"] {
            --bg: #f1f5f9;
            --bg-card: #ffffff;
            --bg-card-alt: #f8fafc;
            --bg-hover: #f1f5f9;
            --text: #1e293b;
            --text-secondary: #64748b;
            --text-muted: #94a3b8;
            --border: #e2e8f0;
            --border-light: #f1f5f9;
            --shadow: 0 1px 3px rgba(0,0,0,0.04), 0 1px 2px rgba(0,0,0,0.06);
            --shadow-md: 0 4px 6px -1px rgba(0,0,0,0.07), 0 2px 4px -2px rgba(0,0,0,0.05);
            --shadow-lg: 0 10px 25px -5px rgba(0,0,0,0.08);
            --header-bg: rgba(255,255,255,0.92);
            --table-stripe: #f8fafc;
            --input-bg: #ffffff;
            --input-border: #cbd5e1;
        }
        [data-theme="dark"] {
            --bg: #0f172a;
            --bg-card: #1e293b;
            --bg-card-alt: #1a2332;
            --bg-hover: #253348;
            --text: #f1f5f9;
            --text-secondary: #94a3b8;
            --text-muted: #64748b;
            --border: #334155;
            --border-light: #1e293b;
            --shadow: 0 1px 3px rgba(0,0,0,0.3);
            --shadow-md: 0 4px 6px rgba(0,0,0,0.35);
            --shadow-lg: 0 10px 25px rgba(0,0,0,0.4);
            --header-bg: rgba(15,23,42,0.92);
            --table-stripe: #1a2332;
            --input-bg: #1e293b;
            --input-border: #475569;
            --accent-bg: rgba(30,64,175,0.12);
            --success-bg: rgba(16,185,129,0.12);
            --warning-bg: rgba(245,158,11,0.12);
            --danger-bg: rgba(239,68,68,0.12);
        }

        :root {
            --accent: #1e40af;
            --accent-light: #2563eb;
            --accent-dark: #1e3a8a;
            --accent-bg: #eff6ff;
            --accent-glow: rgba(30, 64, 175, 0.15);
            --success: #10b981;
            --success-bg: #ecfdf5;
            --warning: #f59e0b;
            --warning-bg: #fffbeb;
            --danger: #ef4444;
            --danger-bg: #fef2f2;
            --info: #06b6d4;
            --sidebar-w: 260px;
            --header-h: 62px;
            --radius: 10px;
            --radius-lg: 16px;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: var(--bg);
            color: var(--text);
            min-height: 100vh;
            -webkit-font-smoothing: antialiased;
        }

        /* ═══ SIDEBAR ═══ */
        .sidebar {
            position: fixed; top: 0; left: 0;
            width: var(--sidebar-w); height: 100vh;
            background: linear-gradient(180deg, #0c1a3a 0%, #0f1d44 50%, #0a1628 100%);
            color: #e2e8f0;
            z-index: 1000;
            display: flex; flex-direction: column;
            overflow-y: auto;
            transition: transform 0.3s cubic-bezier(0.4,0,0.2,1);
            -webkit-overflow-scrolling: touch;
            border-right: 1px solid rgba(30, 64, 175, 0.15);
        }

        .sidebar-brand {
            height: var(--header-h);
            padding: 0 1.25rem;
            display: flex; align-items: center; gap: 0.75rem;
            border-bottom: 1px solid rgba(255,255,255,0.06);
            flex-shrink: 0;
            background: rgba(30, 64, 175, 0.08);
        }
        .sidebar-brand .brand-logo {
            height: 28px; width: auto; object-fit: contain;
            filter: brightness(0) invert(1);
        }
        .sidebar-brand .brand-text {
            font-size: 1.05rem; font-weight: 800;
            background: linear-gradient(135deg, #fff, #94a3b8);
            -webkit-background-clip: text; -webkit-text-fill-color: transparent;
        }

        .sidebar-menu { flex: 1; padding: 0.5rem 0; }
        .menu-section {
            padding: 1rem 1.25rem 0.4rem;
            font-size: 0.6rem; font-weight: 700;
            text-transform: uppercase; letter-spacing: 1.2px;
            color: rgba(255,255,255,0.25);
        }
        .menu-link {
            display: flex; align-items: center; gap: 0.75rem;
            padding: 0.6rem 1.25rem;
            color: rgba(255,255,255,0.55);
            text-decoration: none;
            font-size: 0.82rem; font-weight: 500;
            border-left: 3px solid transparent;
            transition: all 0.15s ease;
        }
        .menu-link:hover { background: rgba(30,64,175,0.1); color: rgba(255,255,255,0.9); }
        .menu-link.active {
            background: linear-gradient(135deg, rgba(30,64,175,0.2), rgba(37,99,235,0.12));
            color: #fff;
            border-left-color: var(--accent-light);
            font-weight: 600;
        }
        .menu-link i { width: 18px; text-align: center; font-size: 0.82rem; }
        .menu-badge {
            margin-left: auto;
            background: var(--danger); color: white;
            font-size: 0.6rem; padding: 0.15rem 0.5rem;
            border-radius: 10px; font-weight: 700;
            min-width: 18px; text-align: center;
        }

        .sidebar-footer {
            padding: 0.75rem 1.25rem;
            border-top: 1px solid rgba(255,255,255,0.06);
            flex-shrink: 0;
        }

        /* ═══ HEADER ═══ */
        .main-header {
            position: fixed; top: 0;
            left: var(--sidebar-w); right: 0;
            height: var(--header-h);
            background: var(--header-bg);
            backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px);
            border-bottom: 1px solid var(--border);
            display: flex; align-items: center; justify-content: space-between;
            padding: 0 1.75rem;
            z-index: 999;
        }
        .header-title { font-size: 1.1rem; font-weight: 700; color: var(--text); letter-spacing: -0.01em; }
        .header-actions { display: flex; align-items: center; gap: 0.6rem; }
        .theme-toggle {
            width: 34px; height: 34px; border-radius: 8px;
            border: 1px solid var(--border);
            background: var(--bg-card); color: var(--text-secondary);
            cursor: pointer; display: flex; align-items: center; justify-content: center;
            font-size: 0.85rem; transition: all 0.15s;
        }
        .theme-toggle:hover { background: var(--bg-hover); color: var(--text); }
        .user-pill {
            display: flex; align-items: center; gap: 0.5rem;
            padding: 0.25rem 0.75rem 0.25rem 0.25rem;
            background: var(--bg-card); border: 1px solid var(--border);
            border-radius: 50px; font-size: 0.78rem; font-weight: 600; color: var(--text);
        }
        .user-pill .avatar {
            width: 28px; height: 28px; border-radius: 50%;
            background: linear-gradient(135deg, var(--accent), #6366f1);
            color: white; display: flex; align-items: center; justify-content: center;
            font-size: 0.7rem; font-weight: 700;
        }

        /* ═══ CONTENT ═══ */
        .main-content {
            margin-left: var(--sidebar-w);
            margin-top: var(--header-h);
            padding: 1.5rem;
            min-height: calc(100vh - var(--header-h));
        }

        /* ═══ MOBILE ═══ */
        .mobile-toggle {
            display: none; position: fixed;
            top: 0.65rem; left: 0.75rem; z-index: 1002;
            width: 40px; height: 40px;
            background: var(--accent); color: white; border: none;
            border-radius: var(--radius); cursor: pointer;
            align-items: center; justify-content: center;
            box-shadow: 0 2px 8px rgba(37,99,235,0.3);
        }
        .mobile-overlay {
            display: none; position: fixed; inset: 0;
            background: rgba(0,0,0,0.5); backdrop-filter: blur(2px);
            z-index: 999;
        }
        .bottom-nav {
            display: none; position: fixed;
            bottom: 0; left: 0; right: 0;
            background: var(--bg-card);
            border-top: 1px solid var(--border);
            padding: 0.4rem 0 calc(0.4rem + env(safe-area-inset-bottom));
            z-index: 1001;
            box-shadow: 0 -2px 12px rgba(0,0,0,0.06);
        }
        .bottom-nav-items {
            display: flex; justify-content: space-around;
            align-items: center; max-width: 500px; margin: 0 auto;
        }
        .bottom-nav-item {
            display: flex; flex-direction: column; align-items: center;
            gap: 0.15rem; padding: 0.35rem 0.75rem;
            color: var(--text-muted); text-decoration: none;
            font-size: 0.62rem; font-weight: 600; transition: all 0.15s;
            position: relative;
        }
        .bottom-nav-item i { font-size: 1.1rem; }
        .bottom-nav-item.active { color: var(--accent); }
        .bottom-nav-badge {
            position: absolute; top: 0; right: 0.15rem;
            background: var(--danger); color: white;
            font-size: 0.5rem; padding: 0.1rem 0.3rem;
            border-radius: 10px; font-weight: 700;
            min-width: 14px; text-align: center;
        }

        @media (max-width: 768px) {
            .sidebar { transform: translateX(-100%); }
            .sidebar.active { transform: translateX(0); box-shadow: 0 0 30px rgba(0,0,0,0.3); }
            .mobile-overlay.active { display: block; }
            .main-header { left: 0; padding-left: 4rem; }
            .main-content { margin-left: 0; padding: 1rem; padding-bottom: calc(5rem + env(safe-area-inset-bottom)); }
            .mobile-toggle { display: flex; }
            .bottom-nav { display: block; }
            .user-pill span:not(.avatar):not(.badge) { display: none; }
        }

        /* ═══ REUSABLE COMPONENTS ═══ */
        .stat-card {
            background: var(--bg-card); border-radius: var(--radius-lg);
            padding: 1.25rem; border: 1px solid var(--border);
            box-shadow: var(--shadow); transition: transform 0.2s, box-shadow 0.2s;
        }
        .stat-card:hover { transform: translateY(-2px); box-shadow: var(--shadow-md); }
        .stat-card .stat-icon {
            width: 44px; height: 44px; border-radius: 12px;
            display: flex; align-items: center; justify-content: center; font-size: 1.05rem;
        }
        .stat-card .stat-value { font-size: 1.65rem; font-weight: 800; color: var(--text); line-height: 1; }
        .stat-card .stat-label { font-size: 0.75rem; color: var(--text-secondary); font-weight: 500; margin-top: 2px; }

        .icon-blue   { background: #dbeafe; color: #2563eb; }
        .icon-green  { background: #d1fae5; color: #059669; }
        .icon-orange { background: #fef3c7; color: #d97706; }
        .icon-red    { background: #fee2e2; color: #dc2626; }
        .icon-purple { background: #ede9fe; color: #7c3aed; }
        .icon-cyan   { background: #cffafe; color: #0891b2; }

        [data-theme="dark"] .icon-blue   { background: rgba(37,99,235,0.15); color: #60a5fa; }
        [data-theme="dark"] .icon-green  { background: rgba(5,150,105,0.15); color: #34d399; }
        [data-theme="dark"] .icon-orange { background: rgba(217,119,6,0.15); color: #fbbf24; }
        [data-theme="dark"] .icon-red    { background: rgba(220,38,38,0.15); color: #f87171; }
        [data-theme="dark"] .icon-purple { background: rgba(124,58,237,0.15); color: #a78bfa; }
        [data-theme="dark"] .icon-cyan   { background: rgba(8,145,178,0.15); color: #22d3ee; }

        .content-card {
            background: var(--bg-card); border-radius: var(--radius-lg);
            border: 1px solid var(--border); overflow: hidden;
            box-shadow: var(--shadow); transition: box-shadow 0.2s;
        }
        .content-card:hover { box-shadow: var(--shadow-md); }
        .content-card .card-header-custom {
            padding: 0.9rem 1.25rem;
            border-bottom: 1px solid var(--border);
            display: flex; align-items: center; justify-content: space-between;
            background: var(--bg-card);
        }
        .content-card .card-header-custom h6 { font-weight: 700; margin: 0; color: var(--text); font-size: 0.9rem; }

        .table-modern { margin: 0; color: var(--text); }
        .table-modern th {
            font-size: 0.7rem; font-weight: 600;
            text-transform: uppercase; letter-spacing: 0.5px;
            color: var(--text-secondary);
            border-bottom: 1px solid var(--border);
            padding: 0.65rem 1rem; background: var(--table-stripe);
        }
        .table-modern td {
            padding: 0.65rem 1rem; font-size: 0.82rem;
            vertical-align: middle;
            border-bottom: 1px solid var(--border);
            color: var(--text);
        }
        .table-modern tr:hover td { background: var(--bg-hover); }

        .btn-royal {
            background: linear-gradient(135deg, var(--accent), var(--accent-light));
            border: none; color: white; font-weight: 600; font-size: 0.82rem;
            border-radius: 10px; padding: 0.5rem 1rem;
            box-shadow: 0 2px 8px var(--accent-glow);
            transition: all 0.2s;
        }
        .btn-royal:hover { background: linear-gradient(135deg, var(--accent-dark), var(--accent)); color: white; transform: translateY(-1px); box-shadow: 0 4px 14px var(--accent-glow); }
        .btn-outline-royal {
            border: 1.5px solid var(--accent); color: var(--accent);
            font-weight: 600; font-size: 0.82rem;
            border-radius: 10px; padding: 0.5rem 1rem; background: transparent;
            transition: all 0.2s;
        }
        .btn-outline-royal:hover { background: var(--accent); color: white; box-shadow: 0 2px 8px var(--accent-glow); }

        .badge-status { font-size: 0.68rem; font-weight: 600; padding: 0.25rem 0.6rem; border-radius: 6px; }
        .badge-pending     { background: var(--warning-bg); color: #92400e; }
        .badge-assigned    { background: var(--accent-bg); color: var(--accent); }
        .badge-in-progress { background: #ede9fe; color: #5b21b6; }
        .badge-in_progress { background: #ede9fe; color: #5b21b6; }
        .badge-confirmed   { background: var(--success-bg); color: #065f46; }
        .badge-delivered   { background: #cffafe; color: #155e75; }
        .badge-cancelled   { background: var(--danger-bg); color: #991b1b; }

        [data-theme="dark"] .badge-pending     { color: #fcd34d; }
        [data-theme="dark"] .badge-assigned    { color: #93c5fd; }
        [data-theme="dark"] .badge-in-progress { background: rgba(91,33,182,0.15); color: #c4b5fd; }
        [data-theme="dark"] .badge-in_progress { background: rgba(91,33,182,0.15); color: #c4b5fd; }
        [data-theme="dark"] .badge-confirmed   { color: #6ee7b7; }
        [data-theme="dark"] .badge-delivered   { background: rgba(21,94,117,0.15); color: #67e8f9; }
        [data-theme="dark"] .badge-cancelled   { color: #fca5a5; }

        .form-control, .form-select {
            background: var(--input-bg); color: var(--text);
            border-color: var(--input-border); border-radius: 8px;
            font-size: 0.85rem;
        }
        .form-control:focus, .form-select:focus {
            background: var(--input-bg); color: var(--text);
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(37,99,235,0.1);
        }
        .form-control::placeholder { color: var(--text-muted); }

        .alert { border-radius: var(--radius); font-size: 0.85rem; border: none; }

        @yield('css')
    </style>
    <script>
        (function(){
            var t = localStorage.getItem('confirmi-theme');
            if (t === 'dark' || (!t && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                document.documentElement.setAttribute('data-theme', 'dark');
            }
        })();
    </script>
</head>
<body>
    @include('partials._no-cache')
    @php
        $user = auth('confirmi')->user();
        $isCommercial = $user && $user->isCommercial();
        $isEmployee = $user && $user->isEmployee();
        $isAgent = $user && $user->isAgent();
        $pendingRequestsCount = $isCommercial ? \App\Models\ConfirmiRequest::where('status', 'pending')->count() : 0;
        $pendingOrdersCount = $isCommercial
            ? \App\Models\ConfirmiOrderAssignment::where('status', 'pending')->count()
            : ($isEmployee ? \App\Models\ConfirmiOrderAssignment::where('assigned_to', $user->id)->whereIn('status', ['assigned', 'in_progress'])->count() : 0);
        $pendingEmballageTasks = $isAgent ? \App\Models\EmballageTask::where('assigned_to', $user->id)->whereIn('status', ['pending', 'received', 'packed'])->count() : 0;
    @endphp

    <div class="mobile-overlay" id="mobileOverlay" onclick="closeSidebar()"></div>
    <button class="mobile-toggle" onclick="toggleSidebar()"><i class="fas fa-bars"></i></button>

    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-brand">
            <img src="{{ asset('img/confirmi.png') }}" alt="Confirmi" class="brand-logo">
        </div>

        <nav class="sidebar-menu">
            @if($isEmployee)
                <div class="menu-section">Principal</div>
                <a href="{{ route('confirmi.employee.dashboard') }}" class="menu-link {{ request()->routeIs('confirmi.employee.dashboard') || request()->routeIs('confirmi.dashboard') ? 'active' : '' }}">
                    <i class="fas fa-chart-pie"></i><span>Dashboard</span>
                </a>

                <div class="menu-section">Commandes</div>
                <a href="{{ route('confirmi.employee.orders.index') }}" class="menu-link {{ request()->routeIs('confirmi.employee.orders.index') ? 'active' : '' }}">
                    <i class="fas fa-list"></i><span>Mes commandes</span>
                    @if($pendingOrdersCount > 0)
                        <span class="menu-badge">{{ $pendingOrdersCount }}</span>
                    @endif
                </a>
                <a href="{{ route('confirmi.employee.orders.search') }}" class="menu-link {{ request()->routeIs('confirmi.employee.orders.search') ? 'active' : '' }}">
                    <i class="fas fa-search"></i><span>Rechercher</span>
                </a>
                <a href="{{ route('confirmi.employee.orders.history') }}" class="menu-link {{ request()->routeIs('confirmi.employee.orders.history') ? 'active' : '' }}">
                    <i class="fas fa-clock-rotate-left"></i><span>Historique</span>
                </a>
                <a href="{{ route('confirmi.employee.process.interface') }}" class="menu-link {{ request()->routeIs('confirmi.employee.process.*') ? 'active' : '' }}">
                    <i class="fas fa-headset"></i><span>Traitement</span>
                </a>

                <div class="menu-section">Produits</div>
                <a href="{{ route('confirmi.employee.products.index') }}" class="menu-link {{ request()->routeIs('confirmi.employee.products.*') ? 'active' : '' }}">
                    <i class="fas fa-boxes"></i><span>Catalogue</span>
                </a>
            @elseif($isCommercial)
                <div class="menu-section">Principal</div>
                <a href="{{ route('confirmi.dashboard') }}" class="menu-link {{ request()->routeIs('confirmi.dashboard') ? 'active' : '' }}">
                    <i class="fas fa-chart-pie"></i><span>Dashboard</span>
                </a>

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
            @elseif($isAgent)
                <div class="menu-section">Principal</div>
                <a href="{{ route('confirmi.dashboard') }}" class="menu-link {{ request()->routeIs('confirmi.dashboard') ? 'active' : '' }}">
                    <i class="fas fa-chart-pie"></i><span>Dashboard</span>
                </a>

                <div class="menu-section">Emballage</div>
                <a href="{{ route('confirmi.agent.emballage.interface') }}" class="menu-link {{ request()->routeIs('confirmi.agent.emballage.*') ? 'active' : '' }}">
                    <i class="fas fa-box-open"></i><span>Interface Emballage</span>
                    @if($pendingEmballageTasks > 0)
                        <span class="menu-badge">{{ $pendingEmballageTasks }}</span>
                    @endif
                </a>
            @endif
        </nav>

        <div class="sidebar-footer">
            <form action="{{ route('confirmi.logout') }}" method="POST">
                @csrf
                <button type="submit" class="menu-link w-100 border-0 bg-transparent text-start" style="color: rgba(255,255,255,0.4);">
                    <i class="fas fa-sign-out-alt"></i><span>Déconnexion</span>
                </button>
            </form>
        </div>
    </aside>

    <!-- Header -->
    <header class="main-header">
        <h1 class="header-title">@yield('page-title', 'Dashboard')</h1>
        <div class="header-actions">
            <button class="theme-toggle" onclick="toggleTheme()" title="Changer le thème">
                <i class="fas fa-moon" id="themeIcon"></i>
            </button>
            <div class="user-pill">
                <div class="avatar">{{ strtoupper(substr($user->name, 0, 1)) }}</div>
                <span>{{ $user->name }}</span>
                <span class="badge bg-{{ $isCommercial ? 'primary' : ($isAgent ? 'warning' : 'info') }}" style="font-size:0.58rem;">{{ $isCommercial ? 'Commercial' : ($isAgent ? 'Agent' : 'Employé') }}</span>
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
        @if(session('info'))
            <div class="alert alert-info alert-dismissible fade show" role="alert">
                <i class="fas fa-info-circle me-2"></i>{{ session('info') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @yield('content')
    </main>

    <!-- Bottom Navigation Mobile -->
    <nav class="bottom-nav">
        <div class="bottom-nav-items">
            @if($isEmployee)
                <a href="{{ route('confirmi.employee.dashboard') }}" class="bottom-nav-item {{ request()->routeIs('confirmi.employee.dashboard') || request()->routeIs('confirmi.dashboard') ? 'active' : '' }}">
                    <i class="fas fa-home"></i><span>Accueil</span>
                </a>
                <a href="{{ route('confirmi.employee.orders.index') }}" class="bottom-nav-item {{ request()->routeIs('confirmi.employee.orders.index') ? 'active' : '' }}">
                    <i class="fas fa-list"></i><span>Commandes</span>
                    @if($pendingOrdersCount > 0)<span class="bottom-nav-badge">{{ $pendingOrdersCount }}</span>@endif
                </a>
                <a href="{{ route('confirmi.employee.orders.search') }}" class="bottom-nav-item {{ request()->routeIs('confirmi.employee.orders.search') ? 'active' : '' }}">
                    <i class="fas fa-search"></i><span>Rechercher</span>
                </a>
                <a href="{{ route('confirmi.employee.process.interface') }}" class="bottom-nav-item {{ request()->routeIs('confirmi.employee.process.*') ? 'active' : '' }}">
                    <i class="fas fa-headset"></i><span>Traitement</span>
                </a>
            @elseif($isCommercial)
                <a href="{{ route('confirmi.dashboard') }}" class="bottom-nav-item {{ request()->routeIs('confirmi.dashboard') ? 'active' : '' }}">
                    <i class="fas fa-home"></i><span>Accueil</span>
                </a>
                <a href="{{ route('confirmi.commercial.orders.pending') }}" class="bottom-nav-item {{ request()->routeIs('confirmi.commercial.orders.pending') ? 'active' : '' }}">
                    <i class="fas fa-clock"></i><span>En attente</span>
                    @if($pendingOrdersCount > 0)<span class="bottom-nav-badge">{{ $pendingOrdersCount }}</span>@endif
                </a>
                <a href="{{ route('confirmi.commercial.orders.index') }}" class="bottom-nav-item {{ request()->routeIs('confirmi.commercial.orders.index') ? 'active' : '' }}">
                    <i class="fas fa-list"></i><span>Commandes</span>
                </a>
                <a href="{{ route('confirmi.commercial.employees.index') }}" class="bottom-nav-item {{ request()->routeIs('confirmi.commercial.employees.*') ? 'active' : '' }}">
                    <i class="fas fa-users"></i><span>Équipe</span>
                </a>
            @elseif($isAgent)
                <a href="{{ route('confirmi.dashboard') }}" class="bottom-nav-item {{ request()->routeIs('confirmi.dashboard') ? 'active' : '' }}">
                    <i class="fas fa-home"></i><span>Accueil</span>
                </a>
                <a href="{{ route('confirmi.agent.emballage.interface') }}" class="bottom-nav-item {{ request()->routeIs('confirmi.agent.emballage.*') ? 'active' : '' }}">
                    <i class="fas fa-box-open"></i><span>Emballage</span>
                    @if($pendingEmballageTasks > 0)<span class="bottom-nav-badge">{{ $pendingEmballageTasks }}</span>@endif
                </a>
            @endif
        </div>
    </nav>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function getTheme() {
            return localStorage.getItem('confirmi-theme') ||
                   (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
        }
        function setTheme(t) {
            document.documentElement.setAttribute('data-theme', t);
            localStorage.setItem('confirmi-theme', t);
            var icon = document.getElementById('themeIcon');
            if (icon) icon.className = t === 'dark' ? 'fas fa-sun' : 'fas fa-moon';
        }
        function toggleTheme() { setTheme(getTheme() === 'dark' ? 'light' : 'dark'); }
        setTheme(getTheme());

        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('active');
            document.getElementById('mobileOverlay').classList.toggle('active');
        }
        function closeSidebar() {
            document.getElementById('sidebar').classList.remove('active');
            document.getElementById('mobileOverlay').classList.remove('active');
        }
        if (window.innerWidth <= 768) {
            document.querySelectorAll('.menu-link[href]').forEach(function(l) {
                l.addEventListener('click', closeSidebar);
            });
        }
    </script>
    @yield('scripts')
</body>
</html>
