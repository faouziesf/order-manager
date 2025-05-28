<!DOCTYPE html>
<html lang="fr">

<head>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') - Order Manager</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Animate.css -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">

    <!-- Custom CSS -->
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
            --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
            --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
            --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
            --shadow-xl: 0 20px 25px -5px rgb(0 0 0 / 0.1), 0 8px 10px -6px rgb(0 0 0 / 0.1);
            --border-radius: 12px;
            --border-radius-lg: 16px;
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
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

        /* ===== MODERN SIDEBAR ===== */
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

        /* Brand Section */
        .sidebar-brand {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(20px);
            margin: 0;
            padding: 24px 20px;
            text-align: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            position: relative;
            z-index: 2;
        }

        .sidebar-brand a {
            color: white;
            font-weight: 700;
            text-decoration: none;
            font-size: 1.25rem;
            letter-spacing: -0.5px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            transition: var(--transition);
        }

        .sidebar-brand a:hover {
            transform: scale(1.02);
        }

        .sidebar-brand .brand-icon {
            width: 48px;
            height: 48px;
            background: linear-gradient(135deg, var(--brand-color) 0%, #ff8a65 100%);
            border-radius: var(--border-radius);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.3rem;
            box-shadow: var(--shadow-md);
            position: relative;
            overflow: hidden;
        }

        .sidebar-brand .brand-icon::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: linear-gradient(45deg, transparent, rgba(255,255,255,0.3), transparent);
            transform: rotate(45deg);
            animation: shine 3s infinite;
        }

        @keyframes shine {
            0% { transform: translateX(-100%) translateY(-100%) rotate(45deg); }
            50% { transform: translateX(100%) translateY(100%) rotate(45deg); }
            100% { transform: translateX(-100%) translateY(-100%) rotate(45deg); }
        }

        .logo-full {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .logo-mini {
            display: none;
        }

        .sidebar-collapsed .logo-full {
            display: none;
        }

        .sidebar-collapsed .logo-mini {
            display: block;
        }

        /* Menu Section */
        .sidebar-menu {
            padding: 24px 0;
            list-style: none;
            position: relative;
            z-index: 2;
            height: calc(100vh - 140px);
            overflow-y: auto;
            overflow-x: hidden;
        }

        .sidebar-menu::-webkit-scrollbar {
            width: 4px;
        }

        .sidebar-menu::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.1);
        }

        .sidebar-menu::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.3);
            border-radius: 2px;
        }

        .sidebar-item {
            position: relative;
            margin-bottom: 6px;
            padding: 0 16px;
        }

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

        .sidebar-icon {
            min-width: 28px;
            text-align: center;
            font-size: 1.1rem;
            position: relative;
            z-index: 1;
        }

        .sidebar-text {
            margin-left: 14px;
            font-weight: 500;
            position: relative;
            z-index: 1;
        }

        .sidebar-collapsed .sidebar-text {
            display: none;
        }

        /* Submenu Section */
        .sidebar-submenu {
            list-style: none;
            padding-left: 0;
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            margin-top: 8px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: var(--border-radius);
        }

        .sidebar-submenu.show {
            max-height: 400px;
            padding: 8px 0;
        }

        .sidebar-collapsed .sidebar-submenu {
            position: absolute;
            left: var(--sidebar-collapsed-width);
            top: 0;
            width: 220px;
            background: linear-gradient(145deg, #667eea 0%, #764ba2 100%);
            border-radius: 0 var(--border-radius) var(--border-radius) 0;
            display: none;
            max-height: none;
            box-shadow: var(--shadow-xl);
            backdrop-filter: blur(20px);
            padding: 16px 0;
            margin-top: 0;
            z-index: 1001;
        }

        .sidebar-collapsed .sidebar-item:hover .sidebar-submenu {
            display: block;
            animation: slideInRight 0.3s ease;
        }

        @keyframes slideInRight {
            from {
                opacity: 0;
                transform: translateX(-20px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
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

        /* ===== MODERN CONTENT AREA ===== */
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
            z-index: 100;
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
            top: -8px;
            right: -8px;
            background: linear-gradient(135deg, var(--danger-color) 0%, #dc2626 100%);
            color: white;
            border-radius: 50%;
            padding: 4px 6px;
            font-size: 0.7rem;
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
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.1); }
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

        /* Modern Page Loader */
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
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
        }

        @keyframes loading {
            0% { width: 0%; }
            50% { width: 70%; }
            100% { width: 100%; }
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
            from { opacity: 0; }
            to { opacity: 1; }
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

            .sidebar-collapsed .sidebar-text {
                display: inline;
            }

            .sidebar-collapsed .logo-mini {
                display: none;
            }

            .sidebar-collapsed .logo-full {
                display: flex;
            }

            .sidebar-collapsed .sidebar-submenu {
                position: static;
                width: 100%;
                display: block;
                box-shadow: none;
                background: rgba(255, 255, 255, 0.1);
                border-radius: var(--border-radius);
                margin: 8px 0;
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

        /* Enhanced Scrollbar */
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
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>

    @yield('css')
</head>

<body>

    <!-- Modern Page Loader -->
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

    <!-- Modern Sidebar -->
    <div class="sidebar" id="sidebar">
        <div class="sidebar-brand">
            <a href="{{ route('admin.dashboard') }}" class="logo-full">
                <div class="brand-icon">
                    <i class="fas fa-shopping-cart"></i>
                </div>
                <span>Order Manager</span>
            </a>
            <a href="{{ route('admin.dashboard') }}" class="logo-mini">
                <div class="brand-icon">
                    <i class="fas fa-shopping-cart"></i>
                </div>
            </a>
        </div>

        <ul class="sidebar-menu">
            <li class="sidebar-item">
                <a href="{{ route('admin.dashboard') }}"
                    class="sidebar-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    <div class="sidebar-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <span class="sidebar-text">Tableau de bord</span>
                </a>
            </li>

            <!-- Section Traitement -->
            <li class="sidebar-item">
                <a href="{{ route('admin.process.interface') }}"
                    class="sidebar-link {{ request()->routeIs('admin.process.interface') ? 'active' : '' }}">
                    <div class="sidebar-icon">
                        <i class="fas fa-headset"></i>
                    </div>
                    <span class="sidebar-text">Traitement</span>
                </a>
            </li>

            <!-- Section Produits -->
            <li class="sidebar-item">
                <a href="#" class="sidebar-link {{ request()->routeIs('admin.products*') ? 'active' : '' }}"
                    data-target="productsSubmenu">
                    <div class="sidebar-icon">
                        <i class="fas fa-box-open"></i>
                    </div>
                    <span class="sidebar-text">Produits</span>
                </a>
                <ul class="sidebar-submenu {{ request()->routeIs('admin.products*') ? 'show' : '' }}"
                    id="productsSubmenu">
                    <li class="sidebar-submenu-item">
                        <a href="{{ route('admin.products.index') }}"
                            class="sidebar-submenu-link {{ request()->routeIs('admin.products.index') ? 'active' : '' }}">
                            <i class="fas fa-list me-2"></i>Liste des produits
                        </a>
                    </li>
                    <li class="sidebar-submenu-item">
                        <a href="{{ route('admin.products.create') }}"
                            class="sidebar-submenu-link {{ request()->routeIs('admin.products.create') ? 'active' : '' }}">
                            <i class="fas fa-plus me-2"></i>Ajouter un produit
                        </a>
                    </li>
                    @if(auth('admin')->user()->products()->where('needs_review', true)->count() > 0)
                    <li class="sidebar-submenu-item">
                        <a href="{{ route('admin.products.review') }}"
                            class="sidebar-submenu-link {{ request()->routeIs('admin.products.review') ? 'active' : '' }}">
                            <i class="fas fa-eye me-2"></i>Examiner 
                            <span class="badge bg-warning ms-1">{{ auth('admin')->user()->products()->where('needs_review', true)->count() }}</span>
                        </a>
                    </li>
                    @endif
                </ul>
            </li>

            <!-- Section Commandes -->
            <li class="sidebar-item">
                <a href="#" class="sidebar-link {{ request()->routeIs('admin.orders*') ? 'active' : '' }}"
                    data-target="ordersSubmenu">
                    <div class="sidebar-icon">
                        <i class="fas fa-shopping-basket"></i>
                    </div>
                    <span class="sidebar-text">Commandes</span>
                </a>
                <ul class="sidebar-submenu {{ request()->routeIs('admin.orders*') ? 'show' : '' }}" id="ordersSubmenu">
                    <li class="sidebar-submenu-item">
                        <a href="{{ route('admin.orders.index') }}"
                            class="sidebar-submenu-link {{ request()->routeIs('admin.orders.index') ? 'active' : '' }}">
                            <i class="fas fa-list me-2"></i>Liste des commandes
                        </a>
                    </li>
                    <li class="sidebar-submenu-item">
                        <a href="{{ route('admin.orders.create') }}"
                            class="sidebar-submenu-link {{ request()->routeIs('admin.orders.create') ? 'active' : '' }}">
                            <i class="fas fa-plus me-2"></i>Ajouter une commande
                        </a>
                    </li>
                </ul>
            </li>

            <!-- Section Utilisateurs -->
            <li class="sidebar-item">
                <a href="#" class="sidebar-link {{ request()->routeIs('admin.managers*') || request()->routeIs('admin.employees*') ? 'active' : '' }}"
                    data-target="usersSubmenu">
                    <div class="sidebar-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <span class="sidebar-text">Utilisateurs</span>
                </a>
                <ul class="sidebar-submenu {{ request()->routeIs('admin.managers*') || request()->routeIs('admin.employees*') ? 'show' : '' }}" id="usersSubmenu">
                    <li class="sidebar-submenu-item">
                        <a href="{{ route('admin.managers.index') }}"
                            class="sidebar-submenu-link {{ request()->routeIs('admin.managers*') ? 'active' : '' }}">
                            <i class="fas fa-user-tie me-2"></i>Managers
                        </a>
                    </li>
                    <li class="sidebar-submenu-item">
                        <a href="{{ route('admin.employees.index') }}"
                            class="sidebar-submenu-link {{ request()->routeIs('admin.employees*') ? 'active' : '' }}">
                            <i class="fas fa-user me-2"></i>Employés
                        </a>
                    </li>
                </ul>
            </li>

            <!-- Section Importation -->
            <li class="sidebar-item">
                <a href="#"
                    class="sidebar-link {{ request()->routeIs('admin.woocommerce*') || request()->routeIs('admin.import*') ? 'active' : '' }}"
                    data-target="importSubmenu">
                    <div class="sidebar-icon">
                        <i class="fas fa-cloud-download-alt"></i>
                    </div>
                    <span class="sidebar-text">Importation</span>
                </a>
                <ul class="sidebar-submenu {{ request()->routeIs('admin.woocommerce*') || request()->routeIs('admin.import*') ? 'show' : '' }}"
                    id="importSubmenu">
                    <li class="sidebar-submenu-item">
                        <a href="{{ route('admin.import.index') }}"
                            class="sidebar-submenu-link {{ request()->routeIs('admin.import.index') ? 'active' : '' }}">
                            <i class="fas fa-file-csv me-2"></i>Import CSV/Excel
                        </a>
                    </li>
                    <li class="sidebar-submenu-item">
                        <a href="{{ route('admin.woocommerce.index') }}"
                            class="sidebar-submenu-link {{ request()->routeIs('admin.woocommerce.index') ? 'active' : '' }}">
                            <i class="fab fa-wordpress me-2"></i>WooCommerce
                        </a>
                    </li>
                </ul>
            </li>

            <!-- Section Historique -->
            <li class="sidebar-item">
                <a href="{{ route('admin.login-history.index') }}"
                    class="sidebar-link {{ request()->routeIs('admin.login-history*') ? 'active' : '' }}">
                    <div class="sidebar-icon">
                        <i class="fas fa-history"></i>
                    </div>
                    <span class="sidebar-text">Historique</span>
                </a>
            </li>

            <!-- Section Paramètres -->
            <li class="sidebar-item">
                <a href="{{ route('admin.settings.index') }}"
                    class="sidebar-link {{ request()->routeIs('admin.settings.index') ? 'active' : '' }}">
                    <div class="sidebar-icon">
                        <i class="fas fa-cog"></i>
                    </div>
                    <span class="sidebar-text">Paramètres</span>
                </a>
            </li>
        </ul>
    </div>

    <!-- Main Content Area -->
    <div class="content" id="content">
        <!-- Modern Header Navbar -->
        <nav class="navbar navbar-expand-lg navbar-light d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center">
                <button class="btn me-3" id="sidebarToggle">
                    <i class="fas fa-bars"></i>
                </button>
                
                <div class="d-none d-md-block">
                    <h5 class="mb-0 text-gradient">@yield('page-title', 'Tableau de bord')</h5>
                </div>
            </div>

            <div class="d-flex align-items-center gap-3">
                <!-- Notifications -->
                @php
                    $pending_products_count = Auth::guard('admin')->user()->products()->where('needs_review', true)->count();
                @endphp
                
                @if($pending_products_count > 0)
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

                <!-- User Profile -->
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
                        <li><hr class="dropdown-divider"></li>
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
                        <li><hr class="dropdown-divider"></li>
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

        <!-- Main Content Container -->
        <div class="main-content animate-fade-in">
            <!-- Global Alert for New Products -->
            @if($pending_products_count > 0)
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
                                {{ $pending_products_count }} produit(s) créé(s) automatiquement lors d'importations nécessite(nt) votre attention.
                            </p>
                            <a href="{{ route('admin.products.review') }}" class="btn btn-warning btn-sm">
                                <i class="fas fa-eye me-2"></i>Examiner maintenant
                            </a>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <!-- Success/Error Messages -->
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

            <!-- Page Content -->
            @yield('content')
        </div>

        <!-- Modern Footer -->
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

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <!-- Custom JS -->
    <script>
        $(document).ready(function() {
            // Enhanced Sidebar Management
            const sidebar = $('#sidebar');
            const content = $('#content');
            const sidebarToggle = $('#sidebarToggle');
            const pageLoader = $('#pageLoader');

            // Loader management with enhanced UX
            let loaderTimeout;
            let isLoading = false;

            function showLoader(delay = 0) {
                if (isLoading) return;
                isLoading = true;
                
                setTimeout(() => {
                    if (pageLoader.length && isLoading) {
                        clearTimeout(loaderTimeout);
                        pageLoader.removeClass('fade-out').addClass('show');
                    }
                }, delay);
            }

            function hideLoader() {
                if (!isLoading) return;
                isLoading = false;
                
                if (pageLoader.length) {
                    clearTimeout(loaderTimeout);
                    pageLoader.addClass('fade-out');
                    
                    setTimeout(() => {
                        pageLoader.removeClass('show').css('display', 'none');
                    }, 500);
                }
            }

            // Initialize - hide loader immediately
            hideLoader();

            // Enhanced sidebar state management
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
            }

            // Apply saved state on page load
            applySidebarState();

            // Handle window resize
            $(window).on('resize', debounce(applySidebarState, 250));

            // Enhanced sidebar toggle with animation
            sidebarToggle.on('click', function() {
                const isMobile = window.innerWidth <= 768;
                
                if (isMobile) {
                    sidebar.toggleClass('sidebar-collapsed');
                } else {
                    const isNowCollapsed = !sidebar.hasClass('sidebar-collapsed');
                    localStorage.setItem('sidebarCollapsed', isNowCollapsed.toString());
                    
                    sidebar.toggleClass('sidebar-collapsed');
                    content.toggleClass('content-expanded');
                }
            });

            // Enhanced menu toggle with improved UX
            const menuLinks = $('[data-target]');
            menuLinks.each(function() {
                const link = $(this);
                const targetId = link.attr('data-target');
                const submenu = $('#' + targetId);
                
                link.on('click', function(e) {
                    if (!sidebar.hasClass('sidebar-collapsed') || window.innerWidth <= 768) {
                        e.preventDefault();
                        
                        // Close other submenus
                        $('.sidebar-submenu.show').not(submenu).removeClass('show');
                        
                        // Toggle current submenu
                        submenu.toggleClass('show');
                        
                        // Update aria-expanded for accessibility
                        const isExpanded = submenu.hasClass('show');
                        link.attr('aria-expanded', isExpanded);
                    }
                });
            });

            // Auto-expand current submenu
            $('.sidebar-submenu.show').each(function() {
                const submenu = $(this);
                const parentLink = submenu.prev('[data-target]');
                if (parentLink.length) {
                    parentLink.addClass('active').attr('aria-expanded', 'true');
                }
            });

            // Enhanced loader for navigation with smart detection
            $(document).on('click', 'a', function(e) {
                const target = $(this);
                const href = target.attr('href');
                
                // Skip if it's not a navigation link
                if (!href || 
                    href === '#' || 
                    href === 'javascript:void(0)' ||
                    href.startsWith('mailto:') ||
                    href.startsWith('tel:') ||
                    target.hasClass('btn-close') ||
                    target.hasClass('alert-link') ||
                    target.closest('.dropdown-menu').length ||
                    target.hasClass('sidebar-link') ||
                    target.hasClass('sidebar-submenu-link') ||
                    target.attr('data-bs-toggle') ||
                    target.attr('target') === '_blank') {
                    return;
                }
                
                // Show loader with small delay to avoid flashing
                showLoader(100);
                loaderTimeout = setTimeout(hideLoader, 5000);
            });

            // Enhanced loader for forms
            $('form:not([data-no-loader])').on('submit', function() {
                showLoader();
                loaderTimeout = setTimeout(hideLoader, 10000);
            });

            // Auto-hide alerts with smooth animation
            setTimeout(() => {
                $('.alert:not(.alert-warning)').each(function() {
                    const alert = $(this);
                    alert.fadeOut(500, function() {
                        alert.remove();
                    });
                });
            }, 5000);

            // Enhanced animations with Intersection Observer
            if ('IntersectionObserver' in window) {
                const observerOptions = {
                    threshold: 0.1,
                    rootMargin: '0px 0px -50px 0px'
                };

                const observer = new IntersectionObserver((entries) => {
                    entries.forEach(entry => {
                        if (entry.isIntersecting) {
                            entry.target.classList.add('animate-slide-up');
                            observer.unobserve(entry.target);
                        }
                    });
                }, observerOptions);

                // Observe cards and other elements
                $('.card, .alert, .footer').each(function() {
                    observer.observe(this);
                });
            }

            // Cleanup functions
            $(window).on('load', hideLoader);
            $(document).on('visibilitychange', function() {
                if (!document.hidden) hideLoader();
            });

            // Ultimate fallback
            setTimeout(hideLoader, 3000);

            // Enhanced keyboard shortcuts
            $(document).on('keydown', function(e) {
                // Ctrl/Cmd + B to toggle sidebar
                if ((e.ctrlKey || e.metaKey) && e.key === 'b') {
                    e.preventDefault();
                    sidebarToggle.click();
                }
                
                // Escape to close modals and dropdowns
                if (e.key === 'Escape') {
                    $('.modal.show').modal('hide');
                    $('.dropdown-menu.show').dropdown('hide');
                }
            });

            // Enhanced dropdown behavior
            $('.dropdown-toggle').on('show.bs.dropdown', function() {
                $(this).addClass('active');
            }).on('hide.bs.dropdown', function() {
                $(this).removeClass('active');
            });

            // Add smooth scrolling to anchor links
            $('a[href^="#"]').on('click', function(e) {
                const target = $(this.getAttribute('href'));
                if (target.length) {
                    e.preventDefault();
                    $('html, body').animate({
                        scrollTop: target.offset().top - 100
                    }, 500);
                }
            });

            // Enhanced user experience improvements
            $('[data-bs-toggle="tooltip"]').tooltip();
            $('[data-bs-toggle="popover"]').popover();

            // Performance monitoring
            if ('performance' in window) {
                $(window).on('load', function() {
                    setTimeout(() => {
                        const loadTime = performance.timing.loadEventEnd - performance.timing.navigationStart;
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

        // Prevent flash of unstyled content
        (function() {
            const loader = document.getElementById('pageLoader');
            if (loader) {
                loader.style.display = 'none';
                loader.style.opacity = '0';
            }
        })();

        // Enhanced error handling
        window.addEventListener('error', function(e) {
            console.error('JavaScript error:', e.error);
        });

        // Enhanced accessibility
        $(document).ready(function() {
            // Add focus indicators for keyboard navigation
            $('a, button, input, select, textarea').on('focus', function() {
                $(this).addClass('focus-visible');
            }).on('blur', function() {
                $(this).removeClass('focus-visible');
            });

            // Announce dynamic content changes to screen readers
            const announcer = $('<div>', {
                'aria-live': 'polite',
                'aria-atomic': 'true',
                'class': 'sr-only'
            }).appendTo('body');

            function announce(message) {
                announcer.text(message);
                setTimeout(() => announcer.empty(), 1000);
            }

            // Example usage for success messages
            $('.alert-success').each(function() {
                announce('Opération réussie');
            });
        });
    </script>

    @yield('scripts')
</body>

</html>