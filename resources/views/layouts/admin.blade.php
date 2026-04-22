<!DOCTYPE html>
<html lang="fr" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <!-- PWA Meta Tags -->
    <meta name="theme-color" content="#4f46e5" id="themeColorMeta">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <title>@yield('title', 'Admin') - Order Manager</title>

    <!-- Fonts & Icons -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="manifest" href="{{ asset('manifest.json') }}">

    <script>
        // Apply theme BEFORE render to prevent flash of wrong theme
        (function(){
            var t = localStorage.getItem('om-theme');
            if (t === 'dark' || (!t && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                document.documentElement.setAttribute('data-theme', 'dark');
            } else {
                document.documentElement.setAttribute('data-theme', 'light');
            }
        })();
    </script>

    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        /* ============= THEME SYSTEM ============= */
        :root, [data-theme="light"] {
            --primary: #6366f1;
            --primary-dark: #4f46e5;
            --primary-light: #818cf8;
            --primary-50: #eef2ff;
            --primary-100: #e0e7ff;
            --primary-200: #c7d2fe;

            --sidebar-bg: #0f172a;
            --sidebar-text: #94a3b8;
            --sidebar-text-active: #ffffff;
            --sidebar-hover: rgba(255,255,255,0.06);
            --sidebar-active: rgba(99,102,241,0.18);
            --sidebar-active-text: #c7d2fe;
            --sidebar-border: rgba(255,255,255,0.06);
            --sidebar-brand-bg: rgba(255,255,255,0.04);

            --header-bg: rgba(255,255,255,0.85);
            --header-border: #e5e7eb;
            --header-text: #1f2937;

            --bg: #f1f5f9;
            --bg-card: #ffffff;
            --bg-card-hover: #f8fafc;
            --bg-muted: #f1f5f9;

            --text: #1e293b;
            --text-secondary: #64748b;
            --text-muted: #94a3b8;

            --border: #e2e8f0;
            --border-light: #f1f5f9;

            --success: #10b981;
            --success-light: #ecfdf5;
            --danger: #ef4444;
            --danger-light: #fef2f2;
            --warning: #f59e0b;
            --warning-light: #fffbeb;
            --info: #3b82f6;
            --info-light: #eff6ff;

            --shadow-sm: 0 1px 2px rgba(0,0,0,0.04);
            --shadow: 0 1px 3px rgba(0,0,0,0.06), 0 1px 2px rgba(0,0,0,0.04);
            --shadow-md: 0 4px 6px -1px rgba(0,0,0,0.07), 0 2px 4px -1px rgba(0,0,0,0.04);
            --shadow-lg: 0 10px 15px -3px rgba(0,0,0,0.08), 0 4px 6px -2px rgba(0,0,0,0.04);
            --shadow-xl: 0 20px 25px -5px rgba(0,0,0,0.08), 0 10px 10px -5px rgba(0,0,0,0.03);

            --sidebar-width: 264px;
            --header-height: 64px;
            --radius: 14px;
            --radius-sm: 10px;
            --radius-lg: 18px;
            --radius-xl: 24px;
            --transition: all 0.2s ease;

            /* Bootstrap overrides for theme awareness */
            --bs-body-bg: var(--bg);
            --bs-body-color: var(--text);
            --bs-card-bg: var(--bg-card);
            --bs-border-color: var(--border);

            /* Aliases for backwards compatibility with page-level CSS */
            --border-color: var(--border);
            --text-color: var(--text);
            --card-bg: var(--bg-card);
        }

        html[data-theme="dark"] {
            --primary: #818cf8;
            --primary-dark: #6366f1;
            --primary-light: #a5b4fc;
            --primary-50: rgba(99,102,241,0.1);
            --primary-100: rgba(99,102,241,0.15);
            --primary-200: rgba(99,102,241,0.2);

            --sidebar-bg: #0c1222;
            --sidebar-text: #64748b;
            --sidebar-text-active: #f1f5f9;
            --sidebar-hover: rgba(255,255,255,0.05);
            --sidebar-active: rgba(129,140,248,0.15);
            --sidebar-active-text: #a5b4fc;
            --sidebar-border: rgba(255,255,255,0.05);
            --sidebar-brand-bg: rgba(255,255,255,0.03);

            --header-bg: rgba(17,24,39,0.85);
            --header-border: #1f2937;
            --header-text: #f9fafb;

            --bg: #0f172a;
            --bg-card: #1e293b;
            --bg-card-hover: #334155;
            --bg-muted: #1e293b;

            --text: #f1f5f9;
            --text-secondary: #94a3b8;
            --text-muted: #64748b;

            --border: #334155;
            --border-light: #1e293b;

            --success: #34d399;
            --success-light: rgba(16,185,129,0.15);
            --danger: #f87171;
            --danger-light: rgba(239,68,68,0.15);
            --warning: #fbbf24;
            --warning-light: rgba(245,158,11,0.15);
            --info: #60a5fa;
            --info-light: rgba(59,130,246,0.15);

            --shadow-sm: 0 1px 2px rgba(0,0,0,0.3);
            --shadow: 0 1px 3px rgba(0,0,0,0.4), 0 1px 2px rgba(0,0,0,0.3);
            --shadow-md: 0 4px 6px -1px rgba(0,0,0,0.4), 0 2px 4px -1px rgba(0,0,0,0.3);
            --shadow-lg: 0 10px 15px -3px rgba(0,0,0,0.5), 0 4px 6px -2px rgba(0,0,0,0.3);
            --shadow-xl: 0 20px 25px -5px rgba(0,0,0,0.5), 0 10px 10px -5px rgba(0,0,0,0.3);

            /* Aliases for backwards compatibility with page-level CSS */
            --border-color: var(--border);
            --text-color: var(--text);
            --card-bg: var(--bg-card);

            --bs-body-bg: var(--bg);
            --bs-body-color: var(--text);
            --bs-card-bg: var(--bg-card);
            --bs-border-color: var(--border);
        }

        /* ============= DARK MODE: Bootstrap & HTML overrides ============= */
        html[data-theme="dark"] body { background: var(--bg) !important; color: var(--text) !important; }
        html[data-theme="dark"] .card,
        html[data-theme="dark"] .card-body { background: var(--bg-card) !important; color: var(--text) !important; border-color: var(--border) !important; }
        html[data-theme="dark"] .table { color: var(--text) !important; --bs-table-bg: transparent; --bs-table-color: var(--text); }
        html[data-theme="dark"] .table thead th,
        html[data-theme="dark"] .table-light { background: var(--bg-muted) !important; color: var(--text-secondary) !important; border-color: var(--border) !important; }
        html[data-theme="dark"] .table td,
        html[data-theme="dark"] .table th { border-color: var(--border) !important; color: var(--text) !important; }
        html[data-theme="dark"] .table-hover tbody tr:hover { background: var(--bg-card-hover) !important; }
        html[data-theme="dark"] .form-control,
        html[data-theme="dark"] .form-select { background: var(--bg-muted) !important; color: var(--text) !important; border-color: var(--border) !important; }
        html[data-theme="dark"] .form-control:focus,
        html[data-theme="dark"] .form-select:focus { border-color: var(--primary) !important; box-shadow: 0 0 0 3px var(--primary-50) !important; }
        html[data-theme="dark"] .form-control::placeholder { color: var(--text-muted) !important; }
        html[data-theme="dark"] .modal-content { background: var(--bg-card) !important; color: var(--text) !important; border-color: var(--border) !important; }
        html[data-theme="dark"] .modal-header,
        html[data-theme="dark"] .modal-footer { border-color: var(--border) !important; }
        html[data-theme="dark"] .dropdown-menu { background: var(--bg-card) !important; border-color: var(--border) !important; }
        html[data-theme="dark"] .dropdown-item { color: var(--text) !important; }
        html[data-theme="dark"] .dropdown-item:hover { background: var(--bg-muted) !important; }
        html[data-theme="dark"] .btn-outline-primary { color: var(--primary) !important; border-color: var(--primary) !important; }
        html[data-theme="dark"] .btn-outline-primary:hover { background: var(--primary) !important; color: #fff !important; }
        html[data-theme="dark"] .btn-outline-secondary { color: var(--text-secondary) !important; border-color: var(--border) !important; }
        html[data-theme="dark"] .btn-close { filter: invert(1); }
        html[data-theme="dark"] .text-dark { color: var(--text) !important; }
        html[data-theme="dark"] .text-muted { color: var(--text-secondary) !important; }
        html[data-theme="dark"] .fw-bold, html[data-theme="dark"] .fw-semibold { color: var(--text); }
        html[data-theme="dark"] .bg-light { background: var(--bg-muted) !important; }
        html[data-theme="dark"] .border { border-color: var(--border) !important; }
        html[data-theme="dark"] .list-group-item { background: var(--bg-card) !important; color: var(--text) !important; border-color: var(--border) !important; }
        html[data-theme="dark"] .page-link { background: var(--bg-card) !important; color: var(--text) !important; border-color: var(--border) !important; }
        html[data-theme="dark"] .page-item.active .page-link { background: var(--primary) !important; border-color: var(--primary) !important; color: #fff !important; }
        html[data-theme="dark"] .input-group-text { background: var(--bg-muted) !important; color: var(--text-secondary) !important; border-color: var(--border) !important; }
        html[data-theme="dark"] label { color: var(--text) !important; }
        html[data-theme="dark"] h1, html[data-theme="dark"] h2, html[data-theme="dark"] h3,
        html[data-theme="dark"] h4, html[data-theme="dark"] h5, html[data-theme="dark"] h6 { color: var(--text) !important; }
        html[data-theme="dark"] p { color: var(--text-secondary); }
        html[data-theme="dark"] strong { color: var(--text); }
        html[data-theme="dark"] .nav-tabs { border-color: var(--border) !important; }
        html[data-theme="dark"] .nav-tabs .nav-link { color: var(--text-secondary) !important; }
        html[data-theme="dark"] .nav-tabs .nav-link.active { background: var(--bg-card) !important; color: var(--text) !important; border-color: var(--border) var(--border) var(--bg-card) !important; }
        html[data-theme="dark"] .accordion-button { background: var(--bg-card) !important; color: var(--text) !important; }
        html[data-theme="dark"] .accordion-body { background: var(--bg-card) !important; color: var(--text) !important; }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: var(--bg);
            color: var(--text);
            -webkit-font-smoothing: antialiased;
        }

        /* ============= SIDEBAR ============= */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: var(--sidebar-width);
            height: 100vh;
            background: var(--sidebar-bg);
            display: flex;
            flex-direction: column;
            z-index: 1000;
            transition: transform 0.3s cubic-bezier(0.4,0,0.2,1);
            overflow-y: auto;
            overflow-x: hidden;
            scrollbar-width: thin;
            scrollbar-color: rgba(255,255,255,0.08) transparent;
        }

        .sidebar::-webkit-scrollbar { width: 4px; }
        .sidebar::-webkit-scrollbar-track { background: transparent; }
        .sidebar::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.08); border-radius: 4px; }

        .sidebar-brand {
            height: var(--header-height);
            padding: 0 1.25rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            border-bottom: 1px solid var(--sidebar-border);
            flex-shrink: 0;
            background: var(--sidebar-brand-bg);
        }

        .sidebar-brand-logo {
            height: 32px;
            width: auto;
            object-fit: contain;
            filter: brightness(0) invert(1);
        }

        .sidebar-brand-text {
            font-size: 0.75rem;
            font-weight: 600;
            color: var(--sidebar-text);
            letter-spacing: -0.01em;
            opacity: 0.7;
        }

        .sidebar-section {
            padding: 1.25rem 0.75rem 0.35rem;
            font-size: 0.6rem;
            font-weight: 700;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            color: var(--sidebar-text);
            opacity: 0.45;
        }

        .sidebar-menu { flex: 1; padding: 0.5rem 0; }

        .menu-item { margin: 2px 0.5rem; }

        .menu-link {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.6rem 0.85rem;
            color: var(--sidebar-text);
            text-decoration: none;
            border-radius: var(--radius-sm);
            transition: background 0.2s, color 0.2s;
            font-size: 0.85rem;
            font-weight: 500;
            cursor: pointer;
            border: none;
            background: none;
            width: 100%;
            text-align: left;
        }

        .menu-link:hover { background: var(--sidebar-hover); color: var(--sidebar-text-active); }
        .menu-link.active {
            background: var(--sidebar-active);
            color: var(--sidebar-active-text);
            font-weight: 600;
        }

        .menu-link i:first-child { width: 18px; text-align: center; font-size: 0.9rem; flex-shrink: 0; }

        .menu-chevron { margin-left: auto; font-size: 0.65rem; transition: transform 0.25s; opacity: 0.5; }
        .menu-link.expanded .menu-chevron { transform: rotate(180deg); }

        .submenu { max-height: 0; overflow: hidden; transition: max-height 0.3s ease; }
        .submenu.show { max-height: 500px; }

        .submenu-link {
            display: flex;
            align-items: center;
            gap: 0.6rem;
            padding: 0.45rem 0.75rem 0.45rem 2.6rem;
            color: var(--sidebar-text);
            text-decoration: none;
            border-radius: var(--radius-sm);
            transition: background 0.2s, color 0.2s;
            font-size: 0.8rem;
            font-weight: 500;
            margin: 1px 0;
            position: relative;
        }

        .submenu-link::before {
            content: '';
            position: absolute;
            left: 1.5rem;
            top: 50%;
            width: 5px;
            height: 5px;
            border-radius: 50%;
            background: var(--sidebar-text);
            opacity: 0.3;
            transform: translateY(-50%);
            transition: background 0.2s, opacity 0.2s;
        }

        .submenu-link:hover { color: var(--sidebar-text-active); background: var(--sidebar-hover); }
        .submenu-link:hover::before { opacity: 0.6; }
        .submenu-link.active { color: var(--sidebar-active-text); }
        .submenu-link.active::before { background: var(--primary-light); opacity: 1; }
        .submenu-link i { font-size: 0; width: 0; overflow: hidden; }

        .menu-badge {
            margin-left: auto;
            font-size: 0.55rem;
            padding: 0.2rem 0.5rem;
            border-radius: 6px;
            font-weight: 700;
            color: #fff;
            letter-spacing: 0.02em;
        }

        .menu-badge-purple { background: linear-gradient(135deg, #7c3aed, #a78bfa); }
        .menu-badge-green { background: var(--success); }
        .menu-badge-yellow { background: var(--warning); color: #1f2937; }

        /* ============= HEADER ============= */
        .main-header {
            position: fixed;
            top: 0;
            left: var(--sidebar-width);
            right: 0;
            height: var(--header-height);
            background: var(--header-bg);
            border-bottom: 1px solid var(--header-border);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 1.75rem;
            z-index: 999;
            transition: left 0.3s;
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
        }

        .header-left { display: flex; align-items: center; gap: 1rem; }

        .header-title {
            font-size: 1.1rem;
            font-weight: 700;
            color: var(--header-text);
        }

        .header-actions { display: flex; align-items: center; gap: 0.6rem; }

        /* Theme Toggle — modern pill switch */
        .theme-toggle {
            width: 36px;
            height: 36px;
            border-radius: var(--radius-sm);
            border: 1px solid var(--border);
            background: var(--bg-card);
            color: var(--text-secondary);
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.9rem;
            transition: all 0.15s;
            padding: 0;
            -webkit-appearance: none;
            appearance: none;
            touch-action: manipulation;
            -webkit-tap-highlight-color: transparent;
        }
        .theme-toggle:hover { background: var(--bg-muted); color: var(--text); }

        /* User Menu + Dropdown */
        .user-menu {
            position: relative;
            display: flex;
            align-items: center;
            gap: 0.6rem;
            padding: 0.3rem 0.75rem 0.3rem 0.3rem;
            border-radius: var(--radius-xl);
            background: var(--bg-muted);
            cursor: pointer;
            text-decoration: none;
            transition: background 0.2s;
            user-select: none;
        }

        .user-menu:hover { background: var(--primary-50); }

        .user-dropdown {
            position: absolute;
            top: calc(100% + 8px);
            right: 0;
            min-width: 200px;
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            box-shadow: var(--shadow-lg);
            z-index: 1060;
            overflow: hidden;
            opacity: 0;
            visibility: hidden;
            transform: translateY(-6px);
            transition: opacity 0.18s, transform 0.18s, visibility 0.18s;
        }
        .user-menu.open .user-dropdown {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }
        .user-dropdown-header {
            padding: 0.85rem 1rem;
            border-bottom: 1px solid var(--border);
        }
        .user-dropdown-header .ud-name { font-size: 0.85rem; font-weight: 700; color: var(--text); }
        .user-dropdown-header .ud-role { font-size: 0.7rem; color: var(--text-muted); margin-top: 1px; }
        .user-dropdown-item {
            display: flex;
            align-items: center;
            gap: 0.65rem;
            padding: 0.6rem 1rem;
            font-size: 0.82rem;
            font-weight: 500;
            color: var(--text);
            text-decoration: none;
            transition: background 0.15s;
            cursor: pointer;
            border: none;
            background: none;
            width: 100%;
            text-align: left;
            font-family: inherit;
        }
        .user-dropdown-item:hover { background: var(--bg-muted); color: var(--text); }
        .user-dropdown-item i { width: 16px; text-align: center; color: var(--text-secondary); font-size: 0.8rem; }
        .user-dropdown-divider { height: 1px; background: var(--border); margin: 0.25rem 0; }
        .user-dropdown-item.logout-item { color: var(--danger); }
        .user-dropdown-item.logout-item i { color: var(--danger); }

        .user-avatar {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 0.8rem;
        }

        .user-info { display: flex; flex-direction: column; }
        .user-name { font-size: 0.8rem; font-weight: 600; color: var(--text); line-height: 1.2; }
        .user-role { font-size: 0.65rem; color: var(--text-muted); }

        /* ============= MAIN CONTENT ============= */
        .main-content {
            margin-left: var(--sidebar-width);
            margin-top: var(--header-height);
            padding: 1.75rem;
            min-height: calc(100vh - var(--header-height));
            transition: margin 0.3s;
        }

        /* ============= MOBILE ============= */
        .mobile-toggle {
            display: none;
            position: fixed;
            top: 0.8rem;
            left: 0.75rem;
            z-index: 1001;
            width: 40px;
            height: 40px;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: var(--radius-sm);
            cursor: pointer;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
            box-shadow: var(--shadow-md);
            pointer-events: auto;
            touch-action: manipulation;
            -webkit-tap-highlight-color: transparent;
            transition: opacity 0.2s, visibility 0.2s;
        }
        /* Masquer le burger quand la sidebar est visible pour ne pas couvrir le logo */
        body.sidebar-open .mobile-toggle {
            opacity: 0;
            visibility: hidden;
            pointer-events: none;
        }

        .mobile-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 999;
            backdrop-filter: blur(4px);
            cursor: pointer;
            touch-action: manipulation;
            -webkit-tap-highlight-color: transparent;
        }

        @media (max-width: 768px) {
            .sidebar { transform: translateX(-100%); }
            .sidebar.active { transform: translateX(0); box-shadow: 0 0 30px rgba(0,0,0,0.3); }
            .mobile-overlay.active { display: block; }
            .main-header { left: 0; padding: 0 1rem 0 4rem; }
            .main-content { margin-left: 0; padding: 1rem; }
            .mobile-toggle { display: flex; }
            .user-info { display: none; }
        }

        @media (max-width: 480px) {
            .main-content { padding: 0.75rem; }
            .header-title { font-size: 0.95rem; }
        }

        /* ============= ALERTS ============= */
        .alert {
            border: none;
            border-radius: var(--radius-sm);
            font-size: 0.875rem;
            font-weight: 500;
            border-left: 4px solid;
        }

        .alert-success { background: var(--success-light); color: var(--success); border-left-color: var(--success); }
        .alert-danger { background: var(--danger-light); color: var(--danger); border-left-color: var(--danger); }

        /* ============= GLOBAL UTILITIES ============= */
        .card {
            background: var(--bg-card);
            border-radius: var(--radius);
            border: 1px solid var(--border);
            margin-bottom: 1.5rem;
            box-shadow: var(--shadow-sm);
            color: var(--text);
        }
        .card-header { background: var(--bg-muted); border-bottom-color: var(--border); color: var(--text); }
        .card-body { color: var(--text); }

        .btn-primary { background: var(--primary); border-color: var(--primary); }
        .btn-primary:hover { background: var(--primary-dark); border-color: var(--primary-dark); }

        .text-muted { color: var(--text-secondary) !important; }
        .text-dark { color: var(--text) !important; }
        .bg-light { background: var(--bg-muted) !important; }

        .form-control, .form-select {
            background: var(--bg-card);
            color: var(--text);
            border-color: var(--border);
        }
        .form-control:focus, .form-select:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px var(--primary-50);
            background: var(--bg-card);
            color: var(--text);
        }
        .form-label { color: var(--text); }

        .table { color: var(--text); }
        .table thead th { background: var(--bg-muted); color: var(--text-secondary); border-color: var(--border); }
        .table td { border-color: var(--border); color: var(--text); }

        /* Theme transition — only on explicit properties, not wildcard */
        .sidebar, .main-header, .main-content, body,
        .card, .form-control, .form-select, .btn,
        .theme-toggle, .menu-link, .submenu-link,
        .alert, .table, .badge, .modal-content {
            transition: background-color 0.3s ease, border-color 0.3s ease, color 0.2s ease;
        }
    </style>

    @yield('css')
</head>
<body>
    @include('partials._no-cache')

    @php
        $user = auth('admin')->user();
        $userName = $user ? $user->name : 'Admin';
        $userInitial = $user ? strtoupper(substr($user->name, 0, 1)) : 'A';
        $userRole = $user ? $user->role : 'admin';
        $isAdmin = $user && $user->isAdmin();
        $isManager = $user && $user->isManager();
        $isEmployee = $user && $user->isEmployee();
    @endphp

    <!-- Mobile Overlay -->
    <div class="mobile-overlay" id="mobileOverlay" onclick="closeSidebar()"></div>

    <!-- Mobile Toggle -->
    <button class="mobile-toggle" onclick="toggleSidebar()">
        <i class="fas fa-bars"></i>
    </button>

    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-brand">
            <img src="{{ asset('img/confirmi.png') }}" alt="Confirmi" class="sidebar-brand-logo" style="height:46px;max-width:170px;">
        </div>

        <nav class="sidebar-menu">
                <div class="sidebar-section">Principal</div>
                <div class="menu-item">
                    <a href="{{ route('admin.dashboard') }}" class="menu-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                        <i class="fas fa-home"></i><span>Dashboard</span>
                    </a>
                </div>

                {{-- ═══════ TRAITEMENT (can_process_orders) ═══════ --}}
                @if($user->can('can_process_orders'))
                <div class="sidebar-section">Gestion</div>
                <div class="menu-item">
                    <div class="menu-link {{ request()->routeIs('admin.process*') ? 'active expanded' : '' }}" onclick="toggleSubmenu(this)">
                        <i class="fas fa-phone"></i><span>Traitement</span>
                        <i class="fas fa-chevron-down menu-chevron"></i>
                    </div>
                    <div class="submenu {{ request()->routeIs('admin.process*') ? 'show' : '' }}">
                        <a href="{{ route('admin.process.interface') }}" class="submenu-link {{ request()->routeIs('admin.process.interface') ? 'active' : '' }}">
                            <i class="fas fa-headset"></i>Interface
                        </a>
                        <a href="{{ route('admin.process.examination.index') }}" class="submenu-link {{ request()->routeIs('admin.process.examination*') ? 'active' : '' }}">
                            <i class="fas fa-search"></i>Examen Stock
                        </a>
                        <a href="{{ route('admin.process.suspended.index') }}" class="submenu-link {{ request()->routeIs('admin.process.suspended*') ? 'active' : '' }}">
                            <i class="fas fa-pause-circle"></i>Suspendues
                        </a>
                        <a href="{{ route('admin.process.restock.index') }}" class="submenu-link {{ request()->routeIs('admin.process.restock*') ? 'active' : '' }}">
                            <i class="fas fa-undo"></i>Retour Stock
                        </a>
                    </div>
                </div>
                @endif

                {{-- ═══════ COMMANDES (can_manage_orders) ═══════ --}}
                @if($user->can('can_manage_orders'))
                <div class="menu-item">
                    <a href="{{ route('admin.orders.index') }}" class="menu-link {{ request()->routeIs('admin.orders.index') ? 'active' : '' }}">
                        <i class="fas fa-list"></i><span>Commandes</span>
                    </a>
                </div>
                <div class="menu-item">
                    <a href="{{ route('admin.orders.create') }}" class="menu-link {{ request()->routeIs('admin.orders.create') ? 'active' : '' }}">
                        <i class="fas fa-plus"></i><span>Nouvelle Commande</span>
                    </a>
                </div>
                @endif

                {{-- ═══════ PRODUITS (can_manage_products) ═══════ --}}
                @if($user->can('can_manage_products'))
                <div class="menu-item">
                    <div class="menu-link {{ request()->routeIs('admin.products*') ? 'active expanded' : '' }}" onclick="toggleSubmenu(this)">
                        <i class="fas fa-box"></i><span>Produits</span>
                        <i class="fas fa-chevron-down menu-chevron"></i>
                    </div>
                    <div class="submenu {{ request()->routeIs('admin.products*') ? 'show' : '' }}">
                        <a href="{{ route('admin.products.index') }}" class="submenu-link {{ request()->routeIs('admin.products.index') ? 'active' : '' }}">
                            <i class="fas fa-list"></i>Liste
                        </a>
                        <a href="{{ route('admin.products.create') }}" class="submenu-link {{ request()->routeIs('admin.products.create') ? 'active' : '' }}">
                            <i class="fas fa-plus-circle"></i>Créer
                        </a>
                        <a href="{{ route('admin.products.review') }}" class="submenu-link {{ request()->routeIs('admin.products.review') ? 'active' : '' }}">
                            <i class="fas fa-eye"></i>Review
                        </a>
                    </div>
                </div>
                @endif

                {{-- ═══════ SERVICES ═══════ --}}
                @if($user->can('can_manage_delivery') || $user->can('can_import'))
                <div class="sidebar-section">Services</div>
                @endif

                {{-- ═══════ LIVRAISON (can_manage_delivery) ═══════ --}}
                @if($user->can('can_manage_delivery'))
                <div class="menu-item">
                    <div class="menu-link {{ request()->routeIs('admin.kolixy*') ? 'active expanded' : '' }}" onclick="toggleSubmenu(this)">
                        <i class="fas fa-truck"></i><span>Livraison</span>
                        <span class="menu-badge menu-badge-purple">Kolixy</span>
                        <i class="fas fa-chevron-down menu-chevron"></i>
                    </div>
                    <div class="submenu {{ request()->routeIs('admin.kolixy*') ? 'show' : '' }}">
                        <a href="{{ route('admin.kolixy.dashboard') }}" class="submenu-link {{ request()->routeIs('admin.kolixy.dashboard') ? 'active' : '' }}">
                            <i class="fas fa-chart-line"></i>Dashboard
                        </a>
                        <a href="{{ route('admin.kolixy.configuration') }}" class="submenu-link {{ request()->routeIs('admin.kolixy.configuration') ? 'active' : '' }}">
                            <i class="fas fa-cog"></i>Configuration
                        </a>
                        <a href="{{ route('admin.kolixy.verification') }}" class="submenu-link {{ request()->routeIs('admin.kolixy.verification') ? 'active' : '' }}">
                            <i class="fas fa-clipboard-check"></i>Vérification
                        </a>
                        <a href="{{ route('admin.kolixy.imprimer-bl') }}" class="submenu-link {{ request()->routeIs('admin.kolixy.imprimer-bl') ? 'active' : '' }}">
                            <i class="fas fa-print"></i>Imprimer BL
                        </a>
                        <a href="{{ route('admin.kolixy.envoyer-commande') }}" class="submenu-link {{ request()->routeIs('admin.kolixy.envoyer-commande') ? 'active' : '' }}">
                            <i class="fas fa-paper-plane"></i>Envoyer
                        </a>
                    </div>
                </div>
                @endif

                {{-- ═══════ CONFIRMI (admin only) ═══════ --}}
                @if($isAdmin)
                <div class="menu-item">
                    <a href="{{ route('admin.confirmi.index') }}" class="menu-link {{ request()->routeIs('admin.confirmi*') ? 'active' : '' }}">
                        <i class="fas fa-headset"></i><span>Confirmi</span>
                        @if($user->confirmi_status === 'active')
                            <span class="menu-badge menu-badge-green">Actif</span>
                        @elseif($user->confirmi_status === 'pending')
                            <span class="menu-badge menu-badge-yellow">En cours</span>
                        @endif
                    </a>
                </div>
                @endif

                {{-- ═══════ IMPORTATION (can_import) ═══════ --}}
                @if($user->can('can_import'))
                <div class="menu-item">
                    <div class="menu-link {{ request()->routeIs('admin.woocommerce*') || request()->routeIs('admin.import*') || request()->routeIs('admin.shopify*') || request()->routeIs('admin.prestashop*') || request()->routeIs('admin.wix*') ? 'active expanded' : '' }}" onclick="toggleSubmenu(this)">
                        <i class="fas fa-download"></i><span>Importation</span>
                        <i class="fas fa-chevron-down menu-chevron"></i>
                    </div>
                    <div class="submenu {{ request()->routeIs('admin.woocommerce*') || request()->routeIs('admin.import*') || request()->routeIs('admin.shopify*') || request()->routeIs('admin.prestashop*') || request()->routeIs('admin.wix*') ? 'show' : '' }}">
                        <a href="{{ route('admin.woocommerce.index') }}" class="submenu-link {{ request()->routeIs('admin.woocommerce.index') ? 'active' : '' }}">
                            <i class="fab fa-wordpress"></i>WooCommerce
                        </a>
                        <a href="{{ route('admin.shopify.index') }}" class="submenu-link {{ request()->routeIs('admin.shopify.index') ? 'active' : '' }}">
                            <i class="fab fa-shopify"></i>Shopify
                        </a>
                        <a href="{{ route('admin.prestashop.index') }}" class="submenu-link {{ request()->routeIs('admin.prestashop.index') ? 'active' : '' }}">
                            <i class="fas fa-shopping-bag"></i>PrestaShop
                        </a>
                        <a href="{{ route('admin.wix.index') }}" class="submenu-link {{ request()->routeIs('admin.wix.index') ? 'active' : '' }}">
                            <i class="fas fa-sitemap"></i>Wix
                        </a>
                        <a href="{{ route('admin.import.index') }}" class="submenu-link {{ request()->routeIs('admin.import*') ? 'active' : '' }}">
                            <i class="fas fa-file-csv"></i>CSV
                        </a>
                    </div>
                </div>
                @endif

                @if($user->can('can_import'))
                <div class="menu-item">
                    <a href="{{ route('admin.google-sheets.index') }}" class="menu-link {{ request()->routeIs('admin.google-sheets*') ? 'active' : '' }}">
                        <i class="fas fa-table"></i><span>Google Sheets</span>
                    </a>
                </div>
                @endif

                {{-- ═══════ ADMINISTRATION (can_manage_users / can_manage_settings) ═══════ --}}
                @if($user->can('can_manage_users') || $user->can('can_manage_settings'))
                <div class="sidebar-section">Administration</div>
                @endif

                @if($user->can('can_manage_users'))
                <div class="menu-item">
                    <a href="{{ route('admin.employees.index') }}" class="menu-link {{ request()->routeIs('admin.employees*') ? 'active' : '' }}">
                        <i class="fas fa-users"></i><span>Employés</span>
                    </a>
                </div>
                <div class="menu-item">
                    <a href="{{ route('admin.managers.index') }}" class="menu-link {{ request()->routeIs('admin.managers*') ? 'active' : '' }}">
                        <i class="fas fa-user-tie"></i><span>Managers</span>
                    </a>
                </div>
                @endif

                @if($user->can('can_manage_settings'))
                <div class="menu-item">
                    <a href="{{ route('admin.settings.index') }}" class="menu-link {{ request()->routeIs('admin.settings*') ? 'active' : '' }}">
                        <i class="fas fa-cog"></i><span>Paramètres</span>
                    </a>
                </div>
                @endif

        </nav>
    </aside>

    <!-- Header -->
    <header class="main-header">
        <div class="header-left">
            <h1 class="header-title">@yield('page-title', 'Dashboard')</h1>
        </div>

        <div class="header-actions">
            @if(session()->has('impersonator_super_admin_id'))
                <form action="{{ route('admin.impersonation.stop') }}" method="POST">
                    @csrf
                    <button type="submit" class="theme-toggle" style="width:auto;padding:0.5rem 0.8rem;border-radius:10px;background:#f59e0b;color:#fff;display:inline-flex;align-items:center;gap:6px;white-space:nowrap" title="Retour Super Admin">
                        <i class="fas fa-right-from-bracket"></i> Retour Super Admin
                    </button>
                </form>
            @endif

            <button class="theme-toggle" onclick="toggleTheme()" title="Changer le thème" aria-label="Changer le thème" id="adminThemeBtn">
                <i class="fas fa-moon" id="adminThemeIcon"></i>
            </button>

            <div class="user-menu" onclick="toggleUserMenu(this)" id="adminUserMenu">
                <div class="user-avatar">{{ $userInitial }}</div>
                <div class="user-info">
                    <div class="user-name">{{ $userName }}</div>
                    <div class="user-role">
                        @if($isAdmin) Admin
                        @elseif($isManager) Manager
                        @else Employé
                        @endif
                    </div>
                </div>
                <i class="fas fa-chevron-down" style="font-size:0.6rem;color:var(--text-muted);margin-left:2px;"></i>
                <div class="user-dropdown">
                    <div class="user-dropdown-header">
                        <div class="ud-name">{{ $userName }}</div>
                        <div class="ud-role">@if($isAdmin) Admin @elseif($isManager) Manager @else Employé @endif</div>
                    </div>
                    <a href="{{ route('admin.profile') }}" class="user-dropdown-item">
                        <i class="fas fa-user-circle"></i> Mon profil
                    </a>
                    <div class="user-dropdown-divider"></div>
                    <form action="{{ route('admin.logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="user-dropdown-item logout-item">
                            <i class="fas fa-sign-out-alt"></i> Déconnexion
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </header>

    <!-- Scripts (chargés avant le contenu pour supporter jQuery inline) -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Main Content -->
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

    <script>
        // Theme System — reliable dark/light toggle
        function getTheme() {
            var saved = localStorage.getItem('om-theme');
            if (saved) return saved;
            // No saved preference: use current attribute (set by IIFE in <head>)
            return document.documentElement.getAttribute('data-theme') || 'light';
        }

        function setTheme(theme) {
            document.documentElement.setAttribute('data-theme', theme);
            localStorage.setItem('om-theme', theme);
            var meta = document.getElementById('themeColorMeta');
            if (meta) meta.content = theme === 'dark' ? '#111827' : '#4f46e5';
            var icon = document.getElementById('adminThemeIcon');
            if (icon) icon.className = theme === 'dark' ? 'fas fa-sun' : 'fas fa-moon';
        }

        function toggleTheme() {
            setTheme(getTheme() === 'light' ? 'dark' : 'light');
        }

        // Sidebar
        function toggleSidebar() {
            var sidebar = document.getElementById('sidebar');
            var overlay = document.getElementById('mobileOverlay');
            var isOpen = sidebar.classList.toggle('active');
            overlay.classList.toggle('active', isOpen);
            document.body.classList.toggle('sidebar-open', isOpen);
        }

        function closeSidebar() {
            var sidebar = document.getElementById('sidebar');
            var overlay = document.getElementById('mobileOverlay');
            sidebar.classList.remove('active');
            overlay.classList.remove('active');
            document.body.classList.remove('sidebar-open');
        }

        function toggleSubmenu(el) {
            el.classList.toggle('expanded');
            var sub = el.nextElementSibling;
            if (sub) sub.classList.toggle('show');
        }

        // Close sidebar on link click (mobile)
        if (window.innerWidth <= 768) {
            document.querySelectorAll('.submenu-link, .menu-link[href]').forEach(function(link) {
                link.addEventListener('click', closeSidebar);
            });
        }

        // User dropdown
        function toggleUserMenu(el) {
            el.classList.toggle('open');
        }
        document.addEventListener('click', function(e) {
            var menu = document.getElementById('adminUserMenu');
            if (menu && !menu.contains(e.target)) {
                menu.classList.remove('open');
            }
        });

        // Init theme icon on page load
        (function() {
            var icon = document.getElementById('adminThemeIcon');
            if (icon) icon.className = getTheme() === 'dark' ? 'fas fa-sun' : 'fas fa-moon';
        })();
    </script>

    @yield('scripts')
</body>
</html>
