<!DOCTYPE html>
<html lang="fr">

<head>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') - Order Manager</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;500;600;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">

    <style>
        :root {
            --primary-color: #6366f1;
            --primary-dark: #4f46e5;
            --primary-light: #a5b4fc;
            --secondary-color: #f8fafc;
            --success-color: #10b981;
            --danger-color: #ef4444;
            --warning-color: #f59e0b;
            --info-color: #06b6d4;
            --dark-color: #1f2937;
            --light-color: #f9fafb;
            --body-bg: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --card-bg: #ffffff;
            --card-border: #e5e7eb;
            --text-color: #374151;
            --text-muted: #6b7280;
            --sidebar-width: 280px;
            --sidebar-collapsed-width: 80px;
            --header-height: 75px;
            --brand-color: #ff6b35;
            --duplicate-color: #d4a147;
            --duplicate-color-dark: #b8941f;
            --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
            --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
            --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
            --shadow-xl: 0 20px 25px -5px rgb(0 0 0 / 0.1), 0 8px 10px -6px rgb(0 0 0 / 0.1);
            --border-radius: 12px;
            --border-radius-lg: 16px;
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        /* Badge doublé pour les commandes doubles */
        .badge.badge-doublé,
        .badge.bg-doublé {
            background: linear-gradient(135deg, var(--duplicate-color) 0%, var(--duplicate-color-dark) 100%) !important;
            color: white !important;
            border: none !important;
            font-weight: 600;
            padding: 0.375rem 0.75rem;
            border-radius: 8px;
            font-size: 0.75rem;
        }

        /* Badge purple pour les commandes suspendues */
        .badge.bg-purple {
            background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%) !important;
            color: white;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: var(--body-bg);
            color: var(--text-color);
            overflow-x: hidden;
            font-weight: 400;
            line-height: 1.6;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        /* ===== SOLUTION ULTRA-RADICALE POUR LES MODALES ===== */
        .modal-backdrop,
        .modal-backdrop.fade,
        .modal-backdrop.show,
        .modal-backdrop.fade.show {
            display: none !important;
            visibility: hidden !important;
            opacity: 0 !important;
            pointer-events: none !important;
            position: absolute !important;
            top: -9999px !important;
            left: -9999px !important;
            width: 0 !important;
            height: 0 !important;
            z-index: -9999 !important;
        }

        div[class*="backdrop"] {
            display: none !important;
            visibility: hidden !important;
            opacity: 0 !important;
            pointer-events: none !important;
        }

        .modal {
            z-index: 99999 !important;
            position: fixed !important;
            top: 0 !important;
            left: 0 !important;
            right: 0 !important;
            bottom: 0 !important;
            width: 100vw !important;
            height: 100vh !important;
            background: rgba(0, 0, 0, 0.6) !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            padding: 40px 40px 40px calc(var(--sidebar-width) + 60px) !important;
            overflow-y: auto !important;
        }

        .content-expanded~.modal,
        body:has(.sidebar-collapsed) .modal {
            padding: 40px 40px 40px calc(var(--sidebar-collapsed-width) + 60px) !important;
        }

        .modal.fade:not(.show) {
            display: none !important;
        }

        .modal.show {
            display: flex !important;
        }

        .modal-dialog {
            z-index: 99999 !important;
            position: relative !important;
            margin: 0 !important;
            max-width: 95% !important;
            min-width: 500px !important;
            width: auto !important;
            max-height: calc(100vh - 80px) !important;
            display: flex !important;
            flex-direction: column !important;
        }

        .modal-lg .modal-dialog {
            max-width: 90% !important;
            min-width: 700px !important;
        }

        .modal-sm .modal-dialog {
            max-width: 60% !important;
            min-width: 400px !important;
        }

        .modal-content {
            z-index: 99999 !important;
            position: relative !important;
            max-height: calc(100vh - 80px) !important;
            overflow-y: auto !important;
            display: flex !important;
            flex-direction: column !important;
            width: 100% !important;
        }

        .modal-dialog-centered {
            min-height: auto !important;
        }

        .modal-body {
            overflow-y: auto !important;
            max-height: calc(80vh - 200px) !important;
        }

        @media (max-width: 768px) {
            .modal {
                padding: 20px !important;
            }

            .modal-dialog {
                min-width: 90% !important;
                max-width: 95% !important;
            }

            .modal-lg .modal-dialog {
                min-width: 90% !important;
                max-width: 95% !important;
            }

            .modal-sm .modal-dialog {
                min-width: 85% !important;
                max-width: 90% !important;
            }
        }

        @media (max-width: 480px) {
            .modal {
                padding: 15px !important;
            }

            .modal-dialog {
                min-width: 95% !important;
                max-width: 98% !important;
            }

            .modal-lg .modal-dialog,
            .modal-sm .modal-dialog {
                min-width: 95% !important;
                max-width: 98% !important;
            }
        }

        .modal:not(.show) {
            display: none !important;
            visibility: hidden !important;
            opacity: 0 !important;
            pointer-events: none !important;
            background: none !important;
        }

        body:not(.modal-open) {
            overflow: auto !important;
            padding-right: 0 !important;
            margin-right: 0 !important;
        }

        body.modal-open {
            overflow: hidden !important;
            padding-right: 0 !important;
            margin-right: 0 !important;
        }

        /* Page Loader */
        .page-loader {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.95) 0%, rgba(118, 75, 162, 0.95) 100%);
            backdrop-filter: blur(20px);
            z-index: 9999;
            display: none;
            justify-content: center;
            align-items: center;
            transition: opacity 0.5s ease;
            opacity: 0;
        }

        .page-loader.show {
            display: flex;
            opacity: 1;
        }

        .page-loader.fade-out {
            opacity: 0;
        }

        .loader-content {
            text-align: center;
            color: white;
        }

        .loader-logo {
            animation: float 3s ease-in-out infinite;
            margin-bottom: 24px;
        }

        .loader-logo .brand-icon {
            width: 100px;
            height: 100px;
            background: linear-gradient(135deg, var(--brand-color) 0%, #ff8a65 100%);
            border-radius: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            margin: 0 auto 20px;
            box-shadow: var(--shadow-xl);
            position: relative;
            overflow: hidden;
        }

        .loader-text {
            font-size: 1.75rem;
            font-weight: 700;
            margin-bottom: 12px;
            letter-spacing: -0.5px;
        }

        .loader-subtext {
            font-size: 1.1rem;
            opacity: 0.8;
            font-weight: 400;
        }

        .loader-progress {
            width: 200px;
            height: 4px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 2px;
            overflow: hidden;
            margin: 24px auto 0;
        }

        .loader-progress-bar {
            height: 100%;
            background: linear-gradient(90deg, var(--brand-color), #ff8a65);
            border-radius: 2px;
            animation: loading 2s ease-in-out infinite;
        }

        @keyframes float {

            0%,
            100% {
                transform: translateY(0px);
            }

            50% {
                transform: translateY(-10px);
            }
        }

        @keyframes loading {
            0% {
                width: 0%;
            }

            50% {
                width: 70%;
            }

            100% {
                width: 100%;
            }
        }

        body.modal-open .page-loader {
            display: none !important;
            visibility: hidden !important;
            opacity: 0 !important;
        }

        /* ===== SIDEBAR MODERNE AVEC SCROLLBAR ===== */
        .sidebar {
            min-height: 100vh;
            background: linear-gradient(145deg, #667eea 0%, #764ba2 100%);
            backdrop-filter: blur(20px);
            position: fixed;
            top: 0;
            left: 0;
            width: var(--sidebar-width);
            z-index: 1000;
            padding-top: 0;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: var(--shadow-xl);
            border-right: 1px solid rgba(255, 255, 255, 0.1);
            display: flex;
            flex-direction: column;
        }

        .sidebar::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(10px);
            z-index: -1;
        }

        .sidebar-collapsed {
            width: var(--sidebar-collapsed-width);
        }

        /* Brand Section - Supprimé */
        .sidebar-brand {
            display: none;
        }

        .logo-full,
        .logo-mini {
            display: none;
        }

        /* Menu Section avec Scrollbar - Sans brand */
        .sidebar-menu {
            padding: 24px 0;
            list-style: none;
            position: relative;
            z-index: 2;
            flex: 1;
            overflow-y: auto;
            overflow-x: hidden;
            max-height: calc(100vh - 48px);
            /* Ajusté sans brand */
        }

        /* Scrollbar personnalisé pour la sidebar */
        .sidebar-menu::-webkit-scrollbar {
            width: 6px;
        }

        .sidebar-menu::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 3px;
            margin: 5px;
        }

        .sidebar-menu::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.3);
            border-radius: 3px;
            transition: background 0.3s ease;
        }

        .sidebar-menu::-webkit-scrollbar-thumb:hover {
            background: rgba(255, 255, 255, 0.5);
        }

        /* Pour Firefox */
        .sidebar-menu {
            scrollbar-width: thin;
            scrollbar-color: rgba(255, 255, 255, 0.3) rgba(255, 255, 255, 0.1);
        }

        @media (min-width: 769px) {
            .sidebar-collapsed .sidebar-menu {
                overflow: visible;
            }
        }

        .sidebar-item {
            position: relative;
            margin-bottom: 6px;
            padding: 0 16px;
        }

        /* Styles pour les liens normaux */
        .sidebar-link {
            display: flex;
            align-items: center;
            padding: 16px 18px;
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            border-radius: var(--border-radius);
            transition: var(--transition);
            font-weight: 500;
            font-size: 0.95rem;
            position: relative;
            overflow: hidden;
            cursor: pointer;
        }

        .sidebar-link::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.1) 0%, rgba(255, 255, 255, 0.05) 100%);
            transform: translateX(-100%);
            transition: transform 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            z-index: -1;
        }

        .sidebar-link:hover::before,
        .sidebar-link.active::before {
            transform: translateX(0);
        }

        .sidebar-link:hover {
            color: white;
            transform: translateX(6px);
            box-shadow: var(--shadow-md);
        }

        .sidebar-link.active {
            color: white;
            background: rgba(255, 255, 255, 0.15);
            box-shadow: var(--shadow-lg);
            border-left: 4px solid var(--brand-color);
        }

        /* Icônes toujours centralisées */
        .sidebar-icon {
            min-width: 28px;
            width: 28px;
            height: 28px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
            position: relative;
            z-index: 1;
            flex-shrink: 0;
        }

        .sidebar-text {
            margin-left: 14px;
            font-weight: 500;
            position: relative;
            z-index: 1;
            white-space: nowrap;
            overflow: hidden;
            transition: var(--transition);
        }

        .sidebar-badge {
            margin-left: auto;
            padding: 2px 6px;
            border-radius: 10px;
            font-size: 0.7rem;
            font-weight: 600;
            background: rgba(255, 255, 255, 0.2);
            color: white;
            min-width: 18px;
            text-align: center;
            flex-shrink: 0;
            transition: var(--transition);
        }

        /* Mode collapsed - TOUTES les icônes centrées */
        .sidebar-collapsed .sidebar-link {
            padding: 16px 0;
            justify-content: center;
            text-align: center;
        }

        .sidebar-collapsed .sidebar-icon {
            min-width: 48px;
            width: 48px;
            height: 48px;
            margin: 0;
        }

        .sidebar-collapsed .sidebar-text {
            display: none;
            width: 0;
            margin-left: 0;
            opacity: 0;
        }

        .sidebar-collapsed .sidebar-badge {
            display: none;
            opacity: 0;
            width: 0;
            margin-left: 0;
            padding: 0;
            overflow: hidden;
        }

        /* Submenu Section */
        .sidebar-submenu {
            list-style: none;
            padding-left: 0;
            margin-top: 8px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: var(--border-radius);
            overflow: hidden;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            max-height: 0;
            opacity: 0;
        }

        .sidebar-submenu.show {
            max-height: 500px;
            opacity: 1;
            padding: 8px 0;
        }

        /* Submenu en mode collapsed */
        .sidebar-collapsed .sidebar-item {
            position: relative;
        }

        .sidebar-collapsed .sidebar-submenu {
            position: absolute;
            left: calc(100% + 16px);
            top: -8px;
            width: 280px;
            background: linear-gradient(145deg, #667eea 0%, #764ba2 100%);
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-xl);
            backdrop-filter: blur(20px);
            z-index: 1051;
            max-height: none;
            opacity: 0;
            transform: translateX(-10px);
            pointer-events: none;
            visibility: hidden;
            transition: all 0.3s ease;
            border: 1px solid rgba(255, 255, 255, 0.1);
            padding: 16px 0;
            margin-top: 0;
        }

        .sidebar-collapsed .sidebar-item:hover .sidebar-submenu {
            opacity: 1;
            transform: translateX(0);
            pointer-events: all;
            visibility: visible;
        }

        .sidebar-collapsed .sidebar-submenu::before {
            content: '';
            position: absolute;
            left: -8px;
            top: 20px;
            width: 0;
            height: 0;
            border-top: 8px solid transparent;
            border-bottom: 8px solid transparent;
            border-right: 8px solid #667eea;
            z-index: 1;
        }

        .sidebar-submenu-item {
            margin: 4px 12px;
        }

        .sidebar-submenu-link {
            display: flex;
            align-items: center;
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            padding: 12px 18px;
            font-size: 0.9rem;
            border-radius: 10px;
            transition: var(--transition);
            font-weight: 400;
            position: relative;
        }

        .sidebar-submenu-link:hover {
            color: white;
            background: rgba(255, 255, 255, 0.1);
            transform: translateX(6px);
        }

        .sidebar-submenu-link.active {
            color: white;
            background: rgba(255, 255, 255, 0.2);
            box-shadow: var(--shadow-md);
        }

        .sidebar-submenu-link .badge {
            margin-left: auto;
            font-size: 0.6rem;
        }

        .sidebar-submenu-link i {
            width: 20px;
            text-align: center;
            margin-right: 8px;
            flex-shrink: 0;
        }

        /* ===== CONTENT AREA ===== */
        .content {
            margin-left: var(--sidebar-width);
            padding: 0;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            min-height: 100vh;
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            position: relative;
        }

        .content::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background:
                radial-gradient(circle at 20% 20%, rgba(102, 126, 234, 0.1) 0%, transparent 50%),
                radial-gradient(circle at 80% 80%, rgba(118, 75, 162, 0.1) 0%, transparent 50%),
                radial-gradient(circle at 40% 40%, rgba(16, 185, 129, 0.05) 0%, transparent 50%);
            pointer-events: none;
            z-index: 0;
        }

        .content-expanded {
            margin-left: var(--sidebar-collapsed-width);
        }

        /* Modern Header */
        .navbar {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            box-shadow: var(--shadow-sm);
            margin-bottom: 0;
            padding: 0 2rem;
            height: var(--header-height);
            border: none;
            border-bottom: 1px solid var(--card-border);
            position: relative;
            z-index: 1040;
        }

        .navbar .btn {
            background: rgba(102, 126, 234, 0.1);
            border: none;
            color: var(--primary-color);
            width: 48px;
            height: 48px;
            border-radius: var(--border-radius);
            display: flex;
            align-items: center;
            justify-content: center;
            transition: var(--transition);
            position: relative;
            overflow: hidden;
        }

        .navbar .btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: var(--primary-color);
            transform: scale(0);
            border-radius: var(--border-radius);
            transition: transform 0.3s ease;
            z-index: -1;
        }

        .navbar .btn:hover::before {
            transform: scale(1);
        }

        .navbar .btn:hover {
            color: white;
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }

        /* Brand Header */
        .brand-header {
            display: flex;
            align-items: center;
            gap: 12px;
            color: var(--primary-color);
            font-weight: 700;
            font-size: 1.25rem;
        }

        .brand-header .brand-icon {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, var(--brand-color) 0%, #ff8a65 100%);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.1rem;
            box-shadow: var(--shadow-md);
        }

        /* User Profile Section */
        .user-profile {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 8px 16px;
            border-radius: var(--border-radius);
            transition: var(--transition);
            cursor: pointer;
            border: 2px solid transparent;
        }

        .user-profile:hover {
            background: rgba(102, 126, 234, 0.05);
            border-color: rgba(102, 126, 234, 0.2);
            transform: translateY(-1px);
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.1rem;
            box-shadow: var(--shadow-md);
        }

        .user-info {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
        }

        .user-name {
            font-weight: 600;
            font-size: 0.95rem;
            color: var(--text-color);
            line-height: 1.2;
        }

        .user-role {
            font-size: 0.8rem;
            color: var(--text-muted);
            line-height: 1.2;
        }

        /* Modern Dropdown */
        .dropdown-menu {
            border: none;
            border-radius: var(--border-radius-lg);
            box-shadow: var(--shadow-xl);
            padding: 12px;
            background: white;
            z-index: 1050;
            min-width: 280px;
            margin-top: 8px;
            backdrop-filter: blur(20px);
        }

        .dropdown-header {
            padding: 16px 20px;
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
            margin: -12px -12px 12px -12px;
            border-radius: var(--border-radius) var(--border-radius) 0 0;
            color: white;
            font-weight: 600;
        }

        .dropdown-item {
            border-radius: 10px;
            padding: 12px 16px;
            font-weight: 500;
            transition: var(--transition);
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 4px;
            border: 2px solid transparent;
        }

        .dropdown-item:hover,
        .dropdown-item:focus {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
            color: white;
            transform: translateX(4px);
            border-color: var(--primary-light);
        }

        .dropdown-item.text-danger:hover,
        .dropdown-item.text-danger:focus {
            background: linear-gradient(135deg, var(--danger-color) 0%, #dc2626 100%);
            color: white;
        }

        .dropdown-item i {
            width: 20px;
            text-align: center;
        }

        .dropdown-divider {
            margin: 12px 0;
            opacity: 0.2;
            border-color: var(--card-border);
        }

        /* Notification Badge */
        .notification-badge {
            position: relative;
        }

        .notification-badge .badge {
            position: absolute;

            right: -0px;
            background: linear-gradient(135deg, var(--danger-color) 0%, #dc2626 100%);
            color: white;
            border-radius: 50%;
            padding: 4px 6px;
            font-size: 0.5rem;
            font-weight: 600;
            min-width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: var(--shadow-md);
            animation: pulse 2s infinite;
        }

        @keyframes pulse {

            0%,
            100% {
                transform: scale(1);
            }

            50% {
                transform: scale(1.1);
            }
        }

        /* Content Container */
        .main-content {
            padding: 2rem;
            min-height: calc(100vh - var(--header-height));
            position: relative;
            z-index: 1;
        }

        /* Modern Cards */
        .card {
            border: none;
            border-radius: var(--border-radius-lg);
            box-shadow: var(--shadow-sm);
            margin-bottom: 24px;
            overflow: hidden;
            background: var(--card-bg);
            transition: var(--transition);
            border: 1px solid rgba(255, 255, 255, 0.8);
        }

        .card:hover {
            box-shadow: var(--shadow-lg);
            transform: translateY(-4px);
            border-color: var(--primary-light);
        }

        .card-header {
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.05) 0%, rgba(118, 75, 162, 0.05) 100%);
            border-bottom: 1px solid var(--card-border);
            padding: 1.5rem 2rem;
            font-weight: 600;
            border-radius: var(--border-radius-lg) var(--border-radius-lg) 0 0;
        }

        /* Modern Alerts */
        .alert {
            border: none;
            border-radius: var(--border-radius);
            padding: 1.25rem 1.5rem;
            margin-bottom: 1.5rem;
            font-weight: 500;
            box-shadow: var(--shadow-sm);
            position: relative;
            overflow: hidden;
        }

        .alert::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 4px;
            background: currentColor;
            opacity: 0.6;
        }

        .alert-warning {
            background: linear-gradient(135deg, #fef3c7 0%, #fde047 100%);
            color: #92400e;
            border-left: 4px solid var(--warning-color);
        }

        .alert-success {
            background: linear-gradient(135deg, #dcfce7 0%, #86efac 100%);
            color: #166534;
            border-left: 4px solid var(--success-color);
        }

        .alert-danger {
            background: linear-gradient(135deg, #fecaca 0%, #f87171 100%);
            color: #991b1b;
            border-left: 4px solid var(--danger-color);
        }

        .alert-info {
            background: linear-gradient(135deg, #cffafe 0%, #67e8f9 100%);
            color: #0c4a6e;
            border-left: 4px solid var(--info-color);
        }

        /* Alerte spéciale pour les doublons */
        .duplicate-alert {
            background: linear-gradient(135deg, #fef3c7 0%, #fde047 100%);
            border: 1px solid var(--duplicate-color);
            color: #92400e;
            margin-bottom: 1.5rem;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(212, 161, 71, 0.2);
            border-left: 4px solid var(--duplicate-color);
        }

        .duplicate-alert .btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .duplicate-alert .badge {
            animation: pulse 2s infinite;
        }

        /* Modern Footer */
        .footer {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(20px);
            padding: 2rem;
            margin-top: 3rem;
            border-top: 1px solid var(--card-border);
            border-radius: var(--border-radius-lg) var(--border-radius-lg) 0 0;
            position: relative;
            z-index: 1;
        }

        .footer-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .footer-text {
            color: var(--text-muted);
            font-weight: 500;
        }

        .footer-links {
            display: flex;
            gap: 1.5rem;
            align-items: center;
        }

        .footer-link {
            color: var(--text-muted);
            text-decoration: none;
            font-weight: 500;
            transition: var(--transition);
            padding: 0.5rem;
            border-radius: 8px;
        }

        .footer-link:hover {
            color: var(--primary-color);
            background: rgba(102, 126, 234, 0.1);
        }

        /* Animation Classes */
        .animate-fade-in {
            animation: fadeIn 0.6s ease-out;
        }

        .animate-slide-up {
            animation: slideUp 0.6s ease-out;
        }

        .animate-slide-down {
            animation: slideDown 0.6s ease-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-30px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .sidebar {
                width: 0;
                transform: translateX(-100%);
            }

            .sidebar-collapsed {
                width: var(--sidebar-width);
                transform: translateX(0);
            }

            .content {
                margin-left: 0;
            }

            .content-expanded {
                margin-left: 0;
            }

            .main-content {
                padding: 1rem;
            }

            .navbar {
                padding: 0 1rem;
            }

            /* En mode mobile, restaurer le comportement normal */
            .sidebar-collapsed .sidebar-text {
                display: block;
                width: auto;
                margin-left: 14px;
                opacity: 1;
            }

            .sidebar-collapsed .sidebar-badge {
                display: block;
                opacity: 1;
                width: auto;
                margin-left: auto;
                padding: 2px 6px;
            }

            .sidebar-collapsed .sidebar-link {
                padding: 16px 18px;
                justify-content: flex-start;
            }

            .sidebar-collapsed .sidebar-icon {
                min-width: 28px;
                width: 28px;
                height: 28px;
                margin: 0;
            }

            .sidebar-collapsed .logo-mini {
                display: none;
            }

            .sidebar-collapsed .logo-full {
                display: none;
            }

            .sidebar-collapsed .sidebar-submenu {
                position: static;
                width: 100%;
                background: rgba(255, 255, 255, 0.1);
                border-radius: var(--border-radius);
                margin: 8px 0;
                opacity: 1;
                transform: none;
                pointer-events: all;
                box-shadow: none;
                border: none;
                visibility: visible;
                z-index: auto;
            }

            .sidebar-collapsed .sidebar-submenu::before {
                display: none;
            }

            .footer-content {
                flex-direction: column;
                text-align: center;
                gap: 1rem;
            }

            .footer-links {
                justify-content: center;
            }

            .user-info {
                display: none;
            }

            .dropdown-menu {
                min-width: 250px;
            }

            .brand-header {
                font-size: 1.1rem;
            }
        }

        @media (max-width: 480px) {
            .main-content {
                padding: 0.75rem;
            }

            .card {
                margin-bottom: 16px;
            }

            .card-header {
                padding: 1rem;
            }

            .alert {
                padding: 1rem;
                margin-bottom: 1rem;
            }
        }

        /* Enhanced Scrollbar pour tout le site */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }

        ::-webkit-scrollbar-track {
            background: rgba(0, 0, 0, 0.05);
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
            border-radius: 4px;
            box-shadow: inset 0 0 2px rgba(0, 0, 0, 0.2);
        }

        ::-webkit-scrollbar-thumb:hover {
            background: linear-gradient(135deg, var(--primary-dark), var(--primary-color));
        }

        /* Badge Improvements */
        .badge {
            font-weight: 600;
            letter-spacing: 0.5px;
            border-radius: 8px;
            padding: 0.375rem 0.75rem;
        }

        /* Button Improvements */
        .btn {
            border-radius: var(--border-radius);
            font-weight: 500;
            transition: var(--transition);
            border: none;
        }

        .btn:hover {
            transform: translateY(-1px);
            box-shadow: var(--shadow-md);
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
        }

        .btn-success {
            background: linear-gradient(135deg, var(--success-color) 0%, #059669 100%);
        }

        .btn-danger {
            background: linear-gradient(135deg, var(--danger-color) 0%, #dc2626 100%);
        }

        .btn-warning {
            background: linear-gradient(135deg, var(--warning-color) 0%, #d97706 100%);
        }

        .btn-info {
            background: linear-gradient(135deg, var(--info-color) 0%, #0891b2 100%);
        }

        /* Form Improvements */
        .form-control {
            border-radius: var(--border-radius);
            border: 2px solid var(--card-border);
            transition: var(--transition);
            padding: 0.75rem 1rem;
        }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }

        /* Table Improvements */
        .table {
            border-radius: var(--border-radius);
            overflow: hidden;
        }

        .table thead th {
            background: linear-gradient(135deg, var(--secondary-color) 0%, #f1f5f9 100%);
            border: none;
            font-weight: 600;
            color: var(--text-color);
            padding: 1rem;
        }

        .table tbody tr {
            transition: var(--transition);
        }

        .table tbody tr:hover {
            background: rgba(102, 126, 234, 0.05);
            transform: scale(1.001);
        }

        /* Additional Utilities */
        .text-gradient {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .glass-effect {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .shadow-custom {
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }

        /* Loading States */
        .loading {
            position: relative;
            pointer-events: none;
        }

        .loading::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 20px;
            height: 20px;
            margin: -10px 0 0 -10px;
            border: 2px solid transparent;
            border-top: 2px solid var(--primary-color);
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }

        /* Tooltip pour sidebar collapsed */
        .sidebar-collapsed .sidebar-link:not(.mobile-expanded) {
            position: relative;
        }

        .sidebar-collapsed .sidebar-link:not(.mobile-expanded)::after {
            content: attr(data-tooltip);
            position: absolute;
            left: calc(100% + 16px);
            top: 50%;
            transform: translateY(-50%);
            background: rgba(0, 0, 0, 0.8);
            color: white;
            padding: 8px 12px;
            border-radius: 6px;
            font-size: 0.85rem;
            white-space: nowrap;
            opacity: 0;
            pointer-events: none;
            transition: all 0.3s ease;
            z-index: 1052;
        }

        .sidebar-collapsed .sidebar-link:not(.mobile-expanded):hover::after {
            opacity: 1;
            transform: translateY(-50%) translateX(4px);
        }
    </style>

    @yield('css')
</head>

<body>

    <div class="page-loader" id="pageLoader">
        <div class="loader-content">
            <div class="loader-logo">
                <div class="brand-icon">
                    <i class="fas fa-shopping-cart"></i>
                </div>
            </div>
            <div class="loader-text">Order Manager</div>
            <div class="loader-subtext">Chargement en cours...</div>
            <div class="loader-progress">
                <div class="loader-progress-bar"></div>
            </div>
        </div>
    </div>

    <div class="sidebar" id="sidebar">
        <ul class="sidebar-menu">
            <!-- Dashboard -->
            <li class="sidebar-item">
                <a href="{{ route('admin.dashboard') }}"
                    class="sidebar-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}"
                    data-tooltip="Tableau de bord">
                    <div class="sidebar-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <span class="sidebar-text">Tableau de bord</span>
                </a>
            </li>

            <!-- Traitement (avec doublons) -->
            <li class="sidebar-item">
                <a href="#"
                    class="sidebar-link {{ request()->routeIs('admin.process*') || request()->routeIs('admin.duplicates*') ? 'active' : '' }}"
                    data-target="processSubmenu" data-tooltip="Traitement">
                    <div class="sidebar-icon">
                        <i class="fas fa-headset"></i>
                    </div>
                    <span class="sidebar-text">Traitement</span>
                    <span class="sidebar-badge">
                        <i class="fas fa-chevron-down"></i>
                    </span>
                </a>
                <ul class="sidebar-submenu {{ request()->routeIs('admin.process*') || request()->routeIs('admin.duplicates*') ? 'show' : '' }}"
                    id="processSubmenu">
                    <!-- Interface principale -->
                    <li class="sidebar-submenu-item">
                        <a href="{{ route('admin.process.interface') }}"
                            class="sidebar-submenu-link {{ request()->routeIs('admin.process.interface') ? 'active' : '' }}">
                            <i class="fas fa-phone"></i>Interface principale
                        </a>
                    </li>

                    <!-- Commandes doubles -->
                    <li class="sidebar-submenu-item">
                        <a href="{{ route('admin.duplicates.index') }}"
                            class="sidebar-submenu-link {{ request()->routeIs('admin.duplicates.*') ? 'active' : '' }}">
                            <i class="fas fa-copy"></i>Commandes doubles
                            @php
                                $duplicate_count = 0;
                                try {
                                    if (auth('admin')->check()) {
                                        $duplicate_count = \App\Models\Order::where('admin_id', auth('admin')->id())
                                            ->where('is_duplicate', true)
                                            ->where('reviewed_for_duplicates', false)
                                            ->where('status', 'nouvelle')
                                            ->distinct('customer_phone')
                                            ->count('customer_phone');
                                    }
                                } catch (\Exception $e) {
                                    // Colonnes pas encore créées
                                    $duplicate_count = 0;
                                }
                            @endphp
                            @if ($duplicate_count > 0)
                                <span class="badge badge-doublé ms-1">{{ $duplicate_count }}</span>
                            @endif
                        </a>
                    </li>

                    <!-- Examen stock -->
                    <li class="sidebar-submenu-item">
                        <a href="{{ route('admin.process.examination.index') }}"
                            class="sidebar-submenu-link {{ request()->routeIs('admin.process.examination.*') ? 'active' : '' }}">
                            <i class="fas fa-exclamation-triangle"></i>Examen stock
                            <span class="badge bg-warning ms-1" id="examination-count-badge"
                                style="display: none;"></span>
                        </a>
                    </li>

                    <!-- Commandes suspendues -->
                    <li class="sidebar-submenu-item">
                        <a href="{{ route('admin.process.suspended.index') }}"
                            class="sidebar-submenu-link {{ request()->routeIs('admin.process.suspended.*') ? 'active' : '' }}">
                            <i class="fas fa-pause-circle"></i>Commandes suspendues
                            <span class="badge bg-purple ms-1" id="suspended-count-badge" style="display: none;"></span>
                        </a>
                    </li>

                    <!-- Retour en stock -->
                    <li class="sidebar-submenu-item">
                        <a href="{{ route('admin.process.restock.index') }}"
                            class="sidebar-submenu-link {{ request()->routeIs('admin.process.restock.*') ? 'active' : '' }}">
                            <i class="fas fa-box-open"></i>Retour en stock
                            <span class="badge bg-success ms-1" id="restock-count-badge" style="display: none;"></span>
                        </a>
                    </li>
                </ul>
            </li>

            <!-- Produits -->
            <li class="sidebar-item">
                <a href="#" class="sidebar-link {{ request()->routeIs('admin.products*') ? 'active' : '' }}"
                    data-target="productsSubmenu" data-tooltip="Produits">
                    <div class="sidebar-icon">
                        <i class="fas fa-box-open"></i>
                    </div>
                    <span class="sidebar-text">Produits</span>
                    <span class="sidebar-badge">
                        <i class="fas fa-chevron-down"></i>
                    </span>
                </a>
                <ul class="sidebar-submenu {{ request()->routeIs('admin.products*') ? 'show' : '' }}"
                    id="productsSubmenu">
                    <li class="sidebar-submenu-item">
                        <a href="{{ route('admin.products.index') }}"
                            class="sidebar-submenu-link {{ request()->routeIs('admin.products.index') ? 'active' : '' }}">
                            <i class="fas fa-list"></i>Liste des produits
                        </a>
                    </li>
                    <li class="sidebar-submenu-item">
                        <a href="{{ route('admin.products.create') }}"
                            class="sidebar-submenu-link {{ request()->routeIs('admin.products.create') ? 'active' : '' }}">
                            <i class="fas fa-plus"></i>Ajouter un produit
                        </a>
                    </li>
                    @if (auth('admin')->user()->products()->where('needs_review', true)->count() > 0)
                        <li class="sidebar-submenu-item">
                            <a href="{{ route('admin.products.review') }}"
                                class="sidebar-submenu-link {{ request()->routeIs('admin.products.review') ? 'active' : '' }}">
                                <i class="fas fa-eye"></i>Examiner
                                <span
                                    class="badge bg-warning ms-1">{{ auth('admin')->user()->products()->where('needs_review', true)->count() }}</span>
                            </a>
                        </li>
                    @endif
                </ul>
            </li>
            <!-- Livraison -->
            <li class="sidebar-item">
                <a href="#" class="sidebar-link {{ request()->routeIs('admin.delivery.*') ? 'active' : '' }}"
                    data-target="deliverySubmenu" data-tooltip="Livraison">
                    <div class="sidebar-icon">
                        <i class="fas fa-truck"></i>
                    </div>
                    <span class="sidebar-text">Livraison</span>
                    <span class="sidebar-badge">
                        <i class="fas fa-chevron-down"></i>
                    </span>
                </a>
                <ul class="sidebar-submenu {{ request()->routeIs('admin.delivery.*') ? 'show' : '' }}"
                    id="deliverySubmenu">
                    
                    <!-- Configuration Jax Delivery -->
                    <li class="sidebar-submenu-item">
                        <a href="{{ route('admin.delivery.configuration') }}"
                            class="sidebar-submenu-link {{ request()->routeIs('admin.delivery.configuration') ? 'active' : '' }}">
                            <i class="fas fa-cog"></i>Configuration Jax
                        </a>
                    </li>
                    
                    <!-- Préparation d'enlèvement -->
                    <li class="sidebar-submenu-item">
                        <a href="{{ route('admin.delivery.preparation') }}"
                            class="sidebar-submenu-link {{ request()->routeIs('admin.delivery.preparation*') ? 'active' : '' }}">
                            <i class="fas fa-boxes"></i>Préparation
                            @php
                                $availableOrders = \App\Models\Order::where('admin_id', auth('admin')->id())
                                    ->where('status', 'confirmée')
                                    ->whereDoesntHave('shipments')
                                    ->count();
                            @endphp
                            @if($availableOrders > 0)
                                <span class="badge badge-success badge-sm ml-1">
                                    {{ $availableOrders }}
                                </span>
                            @endif
                        </a>
                    </li>
                    
                    <!-- Gestion des enlèvements -->
                    <li class="sidebar-submenu-item">
                        <a href="{{ route('admin.delivery.pickups') }}"
                            class="sidebar-submenu-link {{ request()->routeIs('admin.delivery.pickups*') ? 'active' : '' }}">
                            <i class="fas fa-warehouse"></i>Enlèvements
                            @php
                                $draftPickups = \App\Models\Pickup::where('admin_id', auth('admin')->id())
                                    ->where('status', 'draft')
                                    ->count();
                            @endphp
                            @if($draftPickups > 0)
                                <span class="badge badge-warning badge-sm ml-1">
                                    {{ $draftPickups }}
                                </span>
                            @endif
                        </a>
                    </li>
                    
                    <!-- Gestion des expéditions -->
                    <li class="sidebar-submenu-item">
                        <a href="{{ route('admin.delivery.shipments') }}"
                            class="sidebar-submenu-link {{ request()->routeIs('admin.delivery.shipments*') ? 'active' : '' }}">
                            <i class="fas fa-shipping-fast"></i>Expéditions
                            @php
                                $activeShipments = \App\Models\Shipment::where('admin_id', auth('admin')->id())
                                    ->whereIn('status', ['validated', 'picked_up_by_carrier', 'in_transit'])
                                    ->count();
                            @endphp
                            @if($activeShipments > 0)
                                <span class="badge badge-info badge-sm ml-1">
                                    {{ $activeShipments }}
                                </span>
                            @endif
                        </a>
                    </li>
                    
                    <!-- Statistiques -->
                    <li class="sidebar-submenu-item">
                        <a href="{{ route('admin.delivery.stats') }}"
                            class="sidebar-submenu-link {{ request()->routeIs('admin.delivery.stats*') ? 'active' : '' }}">
                            <i class="fas fa-chart-bar"></i>Statistiques
                        </a>
                    </li>
                </ul>
            </li>

            <!-- Commandes -->
            <li class="sidebar-item">
                <a href="#" class="sidebar-link {{ request()->routeIs('admin.orders*') ? 'active' : '' }}"
                    data-target="ordersSubmenu" data-tooltip="Commandes">
                    <div class="sidebar-icon">
                        <i class="fas fa-shopping-basket"></i>
                    </div>
                    <span class="sidebar-text">Commandes</span>
                    <span class="sidebar-badge">
                        <i class="fas fa-chevron-down"></i>
                    </span>
                </a>
                <ul class="sidebar-submenu {{ request()->routeIs('admin.orders*') ? 'show' : '' }}"
                    id="ordersSubmenu">
                    <li class="sidebar-submenu-item">
                        <a href="{{ route('admin.orders.index') }}"
                            class="sidebar-submenu-link {{ request()->routeIs('admin.orders.index') ? 'active' : '' }}">
                            <i class="fas fa-list"></i>Toutes les commandes
                        </a>
                    </li>
                    <li class="sidebar-submenu-item">
                        <a href="{{ route('admin.orders.create') }}"
                            class="sidebar-submenu-link {{ request()->routeIs('admin.orders.create') ? 'active' : '' }}">
                            <i class="fas fa-plus"></i>Nouvelle commande
                        </a>
                    </li>
                </ul>
            </li>

            <!-- Utilisateurs -->
            <li class="sidebar-item">
                <a href="#"
                    class="sidebar-link {{ request()->routeIs('admin.managers*') || request()->routeIs('admin.employees*') ? 'active' : '' }}"
                    data-target="usersSubmenu" data-tooltip="Utilisateurs">
                    <div class="sidebar-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <span class="sidebar-text">Utilisateurs</span>
                    <span class="sidebar-badge">
                        <i class="fas fa-chevron-down"></i>
                    </span>
                </a>
                <ul class="sidebar-submenu {{ request()->routeIs('admin.managers*') || request()->routeIs('admin.employees*') ? 'show' : '' }}"
                    id="usersSubmenu">
                    <li class="sidebar-submenu-item">
                        <a href="{{ route('admin.managers.index') }}"
                            class="sidebar-submenu-link {{ request()->routeIs('admin.managers*') ? 'active' : '' }}">
                            <i class="fas fa-user-tie"></i>Managers
                        </a>
                    </li>
                    <li class="sidebar-submenu-item">
                        <a href="{{ route('admin.employees.index') }}"
                            class="sidebar-submenu-link {{ request()->routeIs('admin.employees*') ? 'active' : '' }}">
                            <i class="fas fa-user"></i>Employés
                        </a>
                    </li>
                </ul>
            </li>

            <!-- Importation -->
            <li class="sidebar-item">
                <a href="#"
                    class="sidebar-link {{ request()->routeIs('admin.woocommerce*') || request()->routeIs('admin.import*') ? 'active' : '' }}"
                    data-target="importSubmenu" data-tooltip="Importation">
                    <div class="sidebar-icon">
                        <i class="fas fa-cloud-download-alt"></i>
                    </div>
                    <span class="sidebar-text">Importation</span>
                    <span class="sidebar-badge">
                        <i class="fas fa-chevron-down"></i>
                    </span>
                </a>
                <ul class="sidebar-submenu {{ request()->routeIs('admin.woocommerce*') || request()->routeIs('admin.import*') ? 'show' : '' }}"
                    id="importSubmenu">
                    <li class="sidebar-submenu-item">
                        <a href="{{ route('admin.import.index') }}"
                            class="sidebar-submenu-link {{ request()->routeIs('admin.import.index') ? 'active' : '' }}">
                            <i class="fas fa-file-csv"></i>Import CSV/Excel
                        </a>
                    </li>
                    <li class="sidebar-submenu-item">
                        <a href="{{ route('admin.woocommerce.index') }}"
                            class="sidebar-submenu-link {{ request()->routeIs('admin.woocommerce.index') ? 'active' : '' }}">
                            <i class="fab fa-wordpress"></i>WooCommerce
                        </a>
                    </li>
                </ul>
            </li>

            <!-- Historique -->
            <li class="sidebar-item">
                <a href="{{ route('admin.login-history.index') }}"
                    class="sidebar-link {{ request()->routeIs('admin.login-history*') ? 'active' : '' }}"
                    data-tooltip="Historique">
                    <div class="sidebar-icon">
                        <i class="fas fa-history"></i>
                    </div>
                    <span class="sidebar-text">Historique</span>
                </a>
            </li>

            <!-- Paramètres -->
            <li class="sidebar-item">
                <a href="{{ route('admin.settings.index') }}"
                    class="sidebar-link {{ request()->routeIs('admin.settings.index') ? 'active' : '' }}"
                    data-tooltip="Paramètres">
                    <div class="sidebar-icon">
                        <i class="fas fa-cog"></i>
                    </div>
                    <span class="sidebar-text">Paramètres</span>
                </a>
            </li>
        </ul>
    </div>

    <div class="content" id="content">
        <nav class="navbar navbar-expand-lg navbar-light d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center">
                <button class="btn me-3" id="sidebarToggle">
                    <i class="fas fa-bars"></i>
                </button>

                <div class="brand-header">
                    <div class="brand-icon">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                    <span>Order Manager</span>
                </div>
            </div>

            <div class="d-flex align-items-center gap-3">
                @php
                    $pending_products_count = Auth::guard('admin')
                        ->user()
                        ->products()
                        ->where('needs_review', true)
                        ->count();
                @endphp

                @if ($pending_products_count > 0)
                    <div class="dropdown">
                        <button class="btn notification-badge" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-bell"></i>
                            <span class="badge">{{ $pending_products_count }}</span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li>
                                <div class="dropdown-header">
                                    <i class="fas fa-bell me-2"></i>Notifications
                                </div>
                            </li>
                            <li>
                                <a class="dropdown-item" href="{{ route('admin.products.review') }}">
                                    <i class="fas fa-exclamation-triangle text-warning"></i>
                                    <div>
                                        <strong>{{ $pending_products_count }} produit(s) à examiner</strong>
                                        <br><small class="text-muted">Produits créés automatiquement</small>
                                    </div>
                                </a>
                            </li>
                        </ul>
                    </div>
                @endif

                <div class="dropdown">
                    <div class="user-profile" data-bs-toggle="dropdown" aria-expanded="false">
                        <div class="user-avatar">
                            <i class="fas fa-user"></i>
                        </div>
                        <div class="user-info">
                            <div class="user-name">{{ Auth::guard('admin')->user()->name }}</div>
                            <div class="user-role">Administrateur</div>
                        </div>
                        <i class="fas fa-chevron-down ms-2"></i>
                    </div>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li>
                            <div class="dropdown-header">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="user-avatar">
                                        <i class="fas fa-user"></i>
                                    </div>
                                    <div>
                                        <div class="fw-bold">{{ Auth::guard('admin')->user()->name }}</div>
                                        <small class="opacity-75">{{ Auth::guard('admin')->user()->email }}</small>
                                    </div>
                                </div>
                            </div>
                        </li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <li>
                            <a class="dropdown-item" href="{{ route('admin.settings.index') }}">
                                <i class="fas fa-cog"></i>
                                <span>Paramètres</span>
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="{{ route('admin.login-history.index') }}">
                                <i class="fas fa-history"></i>
                                <span>Historique</span>
                            </a>
                        </li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <li>
                            <form action="{{ route('admin.logout') }}" method="POST" class="d-inline w-100">
                                @csrf
                                <button type="submit" class="dropdown-item text-danger w-100">
                                    <i class="fas fa-sign-out-alt"></i>
                                    <span>Déconnexion</span>
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>

        <div class="main-content animate-fade-in">
            @if ($pending_products_count > 0)
                <div class="alert alert-warning alert-dismissible fade show animate-slide-down" role="alert">
                    <div class="d-flex align-items-start">
                        <div class="me-3">
                            <i class="fas fa-exclamation-triangle fa-2x"></i>
                        </div>
                        <div class="flex-grow-1">
                            <h5 class="mb-2">
                                <i class="fas fa-eye me-2"></i>Nouveaux produits à examiner
                            </h5>
                            <p class="mb-2">
                                {{ $pending_products_count }} produit(s) créé(s) automatiquement lors d'importations
                                nécessite(nt) votre attention.
                            </p>
                            <a href="{{ route('admin.products.review') }}" class="btn btn-warning btn-sm">
                                <i class="fas fa-eye me-2"></i>Examiner maintenant
                            </a>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            {{-- Alerte globale pour les commandes doubles --}}
            @php
                $duplicate_count = 0;
                try {
                    if (auth('admin')->check()) {
                        $duplicate_count = \App\Models\Order::where('admin_id', auth('admin')->id())
                            ->where('is_duplicate', true)
                            ->where('reviewed_for_duplicates', false)
                            ->where('status', 'nouvelle')
                            ->distinct('customer_phone')
                            ->count('customer_phone');
                    }
                } catch (\Exception $e) {
                    // Colonnes pas encore créées
                    $duplicate_count = 0;
                }
            @endphp

            @if ($duplicate_count > 0)
                <div class="alert duplicate-alert alert-dismissible fade show animate-slide-down" role="alert">
                    <div class="d-flex align-items-start">
                        <div class="me-3">
                            <i class="fas fa-copy fa-2x" style="color: var(--duplicate-color);"></i>
                        </div>
                        <div class="flex-grow-1">
                            <h5 class="mb-2">
                                <i class="fas fa-exclamation-triangle me-2"
                                    style="color: var(--duplicate-color);"></i>
                                <strong>Commandes en double détectées</strong>
                                <span class="badge badge-doublé ms-1">
                                    {{ $duplicate_count }} {{ $duplicate_count > 1 ? 'clients' : 'client' }}
                                </span>
                            </h5>
                            <p class="mb-2">
                                <strong>{{ $duplicate_count }}</strong>
                                {{ $duplicate_count > 1 ? 'clients ont' : 'client a' }} des commandes en double qui
                                nécessitent votre attention.
                                Ces commandes peuvent être fusionnées ou marquées comme examinées.
                            </p>
                            <div class="d-flex gap-2">
                                <a href="{{ route('admin.duplicates.index') }}" class="btn btn-sm badge-doublé"
                                    style="background: linear-gradient(135deg, var(--duplicate-color) 0%, var(--duplicate-color-dark) 100%); color: white; border: none;">
                                    <i class="fas fa-eye me-2"></i>Examiner maintenant
                                </a>
                                <button type="button" class="btn btn-sm btn-outline-warning"
                                    onclick="quickCheckDuplicates()">
                                    <i class="fas fa-search me-2"></i>Vérifier doublons
                                </button>
                            </div>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"
                        onclick="dismissDuplicateAlert()"></button>
                </div>
            @endif

            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show animate-slide-down" role="alert">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-check-circle me-3 fa-lg"></i>
                        <div class="flex-grow-1">
                            <strong>Succès !</strong> {{ session('success') }}
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show animate-slide-down" role="alert">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-exclamation-circle me-3 fa-lg"></i>
                        <div class="flex-grow-1">
                            <strong>Erreur !</strong> {{ session('error') }}
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if (session('info'))
                <div class="alert alert-info alert-dismissible fade show animate-slide-down" role="alert">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-info-circle me-3 fa-lg"></i>
                        <div class="flex-grow-1">
                            <strong>Information :</strong> {{ session('info') }}
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @yield('content')
        </div>

        <footer class="footer">
            <div class="footer-content">
                <div class="footer-text">
                    © {{ date('Y') }} Order Manager. Tous droits réservés.
                </div>
                <div class="footer-links">
                    <span class="footer-text">Version 2.0</span>
                    <span class="footer-text">•</span>
                    <span class="footer-text">Développé avec <i class="fas fa-heart text-danger"></i></span>
                </div>
            </div>
        </footer>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <script>
        $(document).ready(function() {
            // ===== SOLUTION DÉFINITIVE POUR LES MODALES =====

            function ultimateCleanup() {
                $('.modal-backdrop').remove();
                $('.modal').removeClass('show').hide().css('display', 'none');

                $('body')
                    .removeClass('modal-open')
                    .css({
                        'overflow': '',
                        'padding-right': '',
                        'margin-right': ''
                    })
                    .removeAttr('style');

                console.log('🧹 Nettoyage complet effectué');
            }

            ultimateCleanup();

            $(document).on('show.bs.modal', '.modal', function() {
                // Plus de référence au page loader
            });

            $(document).on('hidden.bs.modal', '.modal', function() {
                setTimeout(ultimateCleanup, 50);
            });

            $(document).on('click', '[data-bs-dismiss="modal"], .modal-backdrop', function() {
                setTimeout(ultimateCleanup, 100);
            });

            $(document).on('keydown', function(e) {
                if (e.key === 'Escape') {
                    setTimeout(ultimateCleanup, 100);
                }
            });

            setInterval(function() {
                if ($('.modal.show').length === 0 && ($('.modal-backdrop').length > 0 || $('body').hasClass(
                        'modal-open'))) {
                    console.log('🔍 Éléments bloquants détectés - nettoyage automatique');
                    ultimateCleanup();
                }
            }, 2000);

            window.ultimateCleanup = ultimateCleanup;

            // ===== SOLUTION DÉFINITIVE POUR LE SIDEBAR =====

            const sidebar = $('#sidebar');
            const content = $('#content');
            const sidebarToggle = $('#sidebarToggle');

            function handleSubmenuDisplay() {
                const isCollapsed = sidebar.hasClass('sidebar-collapsed');
                const isMobile = window.innerWidth <= 768;

                if (isCollapsed && !isMobile) {
                    $('.sidebar-item').each(function() {
                        const item = $(this);
                        const submenu = item.find('.sidebar-submenu');

                        if (submenu.length > 0) {
                            item.off('mouseenter.collapsed mouseleave.collapsed');

                            item.on({
                                'mouseenter.collapsed': function() {
                                    clearTimeout(item.data('hideTimeout'));
                                    submenu.css({
                                        'opacity': '1',
                                        'transform': 'translateX(0)',
                                        'pointer-events': 'all',
                                        'visibility': 'visible'
                                    });
                                },
                                'mouseleave.collapsed': function() {
                                    const hideTimeout = setTimeout(() => {
                                        submenu.css({
                                            'opacity': '0',
                                            'transform': 'translateX(-10px)',
                                            'pointer-events': 'none'
                                        });
                                    }, 300);
                                    item.data('hideTimeout', hideTimeout);
                                }
                            });

                            submenu.on({
                                'mouseenter.collapsed': function() {
                                    clearTimeout(item.data('hideTimeout'));
                                },
                                'mouseleave.collapsed': function() {
                                    const hideTimeout = setTimeout(() => {
                                        submenu.css({
                                            'opacity': '0',
                                            'transform': 'translateX(-10px)',
                                            'pointer-events': 'none'
                                        });
                                    }, 300);
                                    item.data('hideTimeout', hideTimeout);
                                }
                            });
                        }
                    });
                } else {
                    $('.sidebar-item').off('mouseenter.collapsed mouseleave.collapsed');
                    $('.sidebar-submenu').off('mouseenter.collapsed mouseleave.collapsed');
                    $('.sidebar-submenu').css({
                        'opacity': '',
                        'transform': '',
                        'pointer-events': '',
                        'visibility': ''
                    });
                }
            }

            function applySidebarState() {
                const isCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
                const isMobile = window.innerWidth <= 768;

                if (isMobile) {
                    sidebar.removeClass('sidebar-collapsed');
                    content.removeClass('content-expanded');
                } else {
                    if (isCollapsed) {
                        sidebar.addClass('sidebar-collapsed');
                        content.addClass('content-expanded');
                    } else {
                        sidebar.removeClass('sidebar-collapsed');
                        content.removeClass('content-expanded');
                    }
                }

                setTimeout(handleSubmenuDisplay, 100);
            }

            applySidebarState();

            $(window).on('resize', debounce(function() {
                applySidebarState();
            }, 250));

            sidebarToggle.on('click', function() {
                const isMobile = window.innerWidth <= 768;

                if (isMobile) {
                    sidebar.toggleClass('sidebar-collapsed');
                } else {
                    const isNowCollapsed = !sidebar.hasClass('sidebar-collapsed');
                    localStorage.setItem('sidebarCollapsed', isNowCollapsed.toString());

                    sidebar.toggleClass('sidebar-collapsed');
                    content.toggleClass('content-expanded');

                    setTimeout(handleSubmenuDisplay, 100);
                }
            });

            // Gestion des sous-menus
            const menuLinks = $('[data-target]');
            menuLinks.each(function() {
                const link = $(this);
                const targetId = link.attr('data-target');
                const submenu = $('#' + targetId);
                const chevron = link.find('.fa-chevron-down, .fa-chevron-up');

                link.on('click', function(e) {
                    const isCollapsed = sidebar.hasClass('sidebar-collapsed');
                    const isMobile = window.innerWidth <= 768;

                    if (isCollapsed && !isMobile) {
                        return;
                    }

                    e.preventDefault();

                    $('.sidebar-submenu.show').not(submenu).removeClass('show').each(function() {
                        const otherChevron = $(this).prev('[data-target]').find(
                            '.fa-chevron-up');
                        otherChevron.removeClass('fa-chevron-up').addClass(
                            'fa-chevron-down');
                    });

                    submenu.toggleClass('show');

                    if (submenu.hasClass('show')) {
                        chevron.removeClass('fa-chevron-down').addClass('fa-chevron-up');
                    } else {
                        chevron.removeClass('fa-chevron-up').addClass('fa-chevron-down');
                    }

                    const isExpanded = submenu.hasClass('show');
                    link.attr('aria-expanded', isExpanded);
                });
            });

            // Auto-expand current submenu
            $('.sidebar-submenu.show').each(function() {
                const submenu = $(this);
                const parentLink = submenu.prev('[data-target]');
                if (parentLink.length) {
                    parentLink.addClass('active').attr('aria-expanded', 'true');
                    const chevron = parentLink.find('.fa-chevron-down');
                    chevron.removeClass('fa-chevron-down').addClass('fa-chevron-up');
                }
            });

            handleSubmenuDisplay();

            // Auto-hide alerts
            setTimeout(() => {
                $('.alert:not(.alert-warning):not(.duplicate-alert)').each(function() {
                    const alert = $(this);
                    alert.fadeOut(500, function() {
                        alert.remove();
                    });
                });
            }, 5000);

            // Animations avec Intersection Observer
            if ('IntersectionObserver' in window) {
                const observerOptions = {
                    threshold: 0.1,
                    rootMargin: '0px 0px -50px 0px'
                };

                const animationObserver = new IntersectionObserver((entries) => {
                    entries.forEach(entry => {
                        if (entry.isIntersecting) {
                            entry.target.classList.add('animate-slide-up');
                            animationObserver.unobserve(entry.target);
                        }
                    });
                }, observerOptions);

                $('.card, .alert, .footer').each(function() {
                    animationObserver.observe(this);
                });
            }

            // Enhanced keyboard shortcuts
            $(document).on('keydown', function(e) {
                if ((e.ctrlKey || e.metaKey) && e.key === 'b') {
                    e.preventDefault();
                    sidebarToggle.click();
                }

                if (e.key === 'Escape') {
                    $('.modal.show').modal('hide');
                    $('.dropdown-menu.show').dropdown('hide');
                    setTimeout(ultimateCleanup, 100);
                }
            });

            // Enhanced dropdown behavior
            $('.dropdown-toggle').on('show.bs.dropdown', function() {
                $(this).addClass('active');
            }).on('hide.bs.dropdown', function() {
                $(this).removeClass('active');
            });

            // Smooth scrolling
            $('a[href^="#"]').on('click', function(e) {
                const target = $(this.getAttribute('href'));
                if (target.length) {
                    e.preventDefault();
                    $('html, body').animate({
                        scrollTop: target.offset().top - 100
                    }, 500);
                }
            });

            $('[data-bs-toggle="tooltip"]').tooltip();
            $('[data-bs-toggle="popover"]').popover();

            // Performance monitoring
            if ('performance' in window) {
                $(window).on('load', function() {
                    setTimeout(() => {
                        const loadTime = performance.timing.loadEventEnd - performance.timing
                            .navigationStart;
                        console.log('Page loaded in', loadTime, 'ms');
                    }, 0);
                });
            }
        });

        // Utility functions
        function debounce(func, wait) {
            let timeout;
            return function executedFunction(...args) {
                const later = () => {
                    clearTimeout(timeout);
                    func(...args);
                };
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
            };
        }

        // Enhanced error handling
        window.addEventListener('error', function(e) {
            console.error('JavaScript error:', e.error);
        });

        // Enhanced accessibility
        $(document).ready(function() {
            $('a, button, input, select, textarea').on('focus', function() {
                $(this).addClass('focus-visible');
            }).on('blur', function() {
                $(this).removeClass('focus-visible');
            });

            const announcer = $('<div>', {
                'aria-live': 'polite',
                'aria-atomic': 'true',
                'class': 'sr-only'
            }).appendTo('body');

            function announce(message) {
                announcer.text(message);
                setTimeout(() => announcer.empty(), 1000);
            }

            $('.alert-success').each(function() {
                announce('Opération réussie');
            });
        });

        // Fonctions pour les doublons
        function quickCheckDuplicates() {
            const button = event.target;
            const originalHtml = button.innerHTML;

            button.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Vérification...';
            button.disabled = true;

            fetch('{{ route('admin.duplicates.check') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                .then(response => response.json())
                .then(data => {
                    button.innerHTML = originalHtml;
                    button.disabled = false;

                    if (data.success) {
                        showNotification('success', data.message);
                        setTimeout(() => {
                            window.location.reload();
                        }, 2000);
                    } else {
                        showNotification('error', data.message || 'Erreur lors de la vérification');
                    }
                })
                .catch(error => {
                    button.innerHTML = originalHtml;
                    button.disabled = false;
                    showNotification('error', 'Erreur lors de la vérification des doublons');
                    console.error('Erreur:', error);
                });
        }

        function dismissDuplicateAlert() {
            const alertTimestamp = new Date().getTime();
            localStorage.setItem('duplicate_alert_dismissed_{{ auth('admin')->id() ?? 0 }}', alertTimestamp);
        }

        function showNotification(type, message) {
            const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
            const icon = type === 'success' ? 'fas fa-check-circle' : 'fas fa-exclamation-circle';

            const notification = document.createElement('div');
            notification.className = `alert ${alertClass} alert-dismissible fade show position-fixed`;
            notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
            notification.innerHTML = `
                <div class="d-flex align-items-center">
                    <i class="${icon} me-2"></i>
                    <span>${message}</span>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;

            document.body.appendChild(notification);

            setTimeout(() => {
                if (notification.parentNode) {
                    notification.remove();
                }
            }, 5000);
        }

        // Vérifier si l'alerte a été fermée récemment
        document.addEventListener('DOMContentLoaded', function() {
            const adminId = {{ auth('admin')->id() ?? 0 }};
            const dismissedTimestamp = localStorage.getItem('duplicate_alert_dismissed_' + adminId);

            if (dismissedTimestamp) {
                const dismissedTime = new Date(parseInt(dismissedTimestamp));
                const now = new Date();
                const hoursSinceDismissed = (now - dismissedTime) / (1000 * 60 * 60);

                if (hoursSinceDismissed < 24) {
                    const duplicateAlert = document.querySelector('.duplicate-alert');
                    if (duplicateAlert) {
                        duplicateAlert.style.display = 'none';
                    }
                } else {
                    localStorage.removeItem('duplicate_alert_dismissed_' + adminId);
                }
            }
        });

        $(document).ready(function() {
            // Fonction pour charger tous les compteurs de traitement
            function loadProcessingCounts() {
                // Compteur d'examen
                $.get('/admin/process/examination/count')
                    .done(function(data) {
                        const count = data.count || 0;
                        const badge = $('#examination-count-badge');

                        if (count > 0) {
                            badge.text(count).show();
                        } else {
                            badge.hide();
                        }
                    })
                    .fail(function() {
                        $('#examination-count-badge').hide();
                    });

                // Compteur des commandes suspendues
                $.get('/admin/process/suspended/count')
                    .done(function(data) {
                        const count = data.count || 0;
                        const badge = $('#suspended-count-badge');

                        if (count > 0) {
                            badge.text(count).show();
                        } else {
                            badge.hide();
                        }
                    })
                    .fail(function() {
                        $('#suspended-count-badge').hide();
                    });

                // Compteur de retour en stock
                $.get('/admin/process/restock/count')
                    .done(function(data) {
                        const count = data.count || 0;
                        const badge = $('#restock-count-badge');

                        if (count > 0) {
                            badge.text(count).show();
                        } else {
                            badge.hide();
                        }
                    })
                    .fail(function() {
                        $('#restock-count-badge').hide();
                    });
            }

            // Charger les compteurs au démarrage
            loadProcessingCounts();

            // Actualiser les compteurs toutes les 30 secondes
            setInterval(loadProcessingCounts, 30000);
        });
    </script>

    @yield('scripts')
</body>

</html>
