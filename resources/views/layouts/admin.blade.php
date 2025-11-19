<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin') - Confirmi Space</title>

    <!-- Fonts & Icons -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --primary: #4f46e5;
            --primary-dark: #4338ca;
            --sidebar-width: 280px;
            --header-height: 64px;
            --bg-light: #f8fafc;
            --text-dark: #1e293b;
            --border-color: #e2e8f0;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--bg-light);
            color: var(--text-dark);
        }

        /* ============= SIDEBAR ============= */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: var(--sidebar-width);
            height: 100vh;
            background: white;
            border-right: 1px solid var(--border-color);
            display: flex;
            flex-direction: column;
            z-index: 1000;
            transition: transform 0.3s ease;
            overflow-y: auto;
        }

        .sidebar-brand {
            height: var(--header-height);
            padding: 0 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            border-bottom: 1px solid var(--border-color);
            flex-shrink: 0;
        }

        .sidebar-brand i {
            font-size: 1.5rem;
            color: var(--primary);
        }

        .sidebar-brand-text {
            font-size: 1.125rem;
            font-weight: 700;
            color: var(--text-dark);
        }

        .sidebar-menu {
            flex: 1;
            padding: 1rem 0;
        }

        .menu-item {
            margin: 0.25rem 0.75rem;
        }

        .menu-link {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem 1rem;
            color: #64748b;
            text-decoration: none;
            border-radius: 8px;
            transition: all 0.2s;
            font-size: 0.9rem;
            font-weight: 500;
            cursor: pointer;
        }

        .menu-link:hover {
            background: #f1f5f9;
            color: var(--primary);
        }

        .menu-link.active {
            background: #eef2ff;
            color: var(--primary);
        }

        .menu-link i {
            width: 20px;
            text-align: center;
        }

        .menu-chevron {
            margin-left: auto;
            transition: transform 0.2s;
        }

        .menu-link.expanded .menu-chevron {
            transform: rotate(180deg);
        }

        /* Sous-menus */
        .submenu {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease;
        }

        .submenu.show {
            max-height: 500px;
        }

        .submenu-item {
            margin: 0.25rem 0;
        }

        .submenu-link {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.625rem 1rem 0.625rem 3rem;
            color: #64748b;
            text-decoration: none;
            border-radius: 8px;
            transition: all 0.2s;
            font-size: 0.85rem;
            font-weight: 500;
        }

        .submenu-link:hover {
            background: #f1f5f9;
            color: var(--primary);
        }

        .submenu-link.active {
            background: #eef2ff;
            color: var(--primary);
        }

        /* ============= HEADER ============= */
        .main-header {
            position: fixed;
            top: 0;
            left: var(--sidebar-width);
            right: 0;
            height: var(--header-height);
            background: white;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 2rem;
            z-index: 999;
        }

        .header-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--text-dark);
        }

        .user-menu {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            cursor: pointer;
            transition: background 0.2s;
        }

        .user-menu:hover {
            background: var(--bg-light);
        }

        .user-avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: var(--primary);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
        }

        .user-info {
            display: flex;
            flex-direction: column;
        }

        .user-name {
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--text-dark);
        }

        .user-role {
            font-size: 0.75rem;
            color: #64748b;
        }

        /* ============= MAIN CONTENT ============= */
        .main-content {
            margin-left: var(--sidebar-width);
            margin-top: var(--header-height);
            padding: 2rem;
            min-height: calc(100vh - var(--header-height));
        }

        /* ============= MOBILE ============= */
        .mobile-toggle {
            display: none;
            position: fixed;
            top: 1rem;
            left: 1rem;
            z-index: 1002;
            width: 44px;
            height: 44px;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            align-items: center;
            justify-content: center;
        }

        .mobile-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 999;
        }

        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }

            .sidebar.active {
                transform: translateX(0);
                box-shadow: 0 0 20px rgba(0, 0, 0, 0.2);
            }

            .mobile-overlay.active {
                display: block;
            }

            .main-header {
                left: 0;
                padding: 0 1rem 0 4.5rem;
            }

            .main-content {
                margin-left: 0;
                margin-top: var(--header-height);
                padding: 1rem;
            }

            .mobile-toggle {
                display: flex;
            }

            .user-info {
                display: none;
            }

            .header-title {
                font-size: 1.125rem;
            }
        }

        @media (max-width: 480px) {
            .main-content {
                padding: 0.75rem;
            }

            .header-title {
                font-size: 1rem;
            }
        }

        /* ============= UTILITIES ============= */
        .card {
            background: white;
            border-radius: 12px;
            border: 1px solid var(--border-color);
            margin-bottom: 1.5rem;
        }

        .btn-primary {
            background: var(--primary);
            border-color: var(--primary);
        }

        .btn-primary:hover {
            background: var(--primary-dark);
            border-color: var(--primary-dark);
        }
    </style>

    @yield('css')
</head>
<body>

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
            <i class="fas fa-cube"></i>
            <span class="sidebar-brand-text">Confirmi Space</span>
        </div>

        <nav class="sidebar-menu">
            @if($isEmployee)
                {{-- MENU SIMPLIFIÉ POUR LES EMPLOYÉS --}}
                <div class="menu-item">
                    <a href="{{ route('admin.process.interface') }}" class="menu-link {{ request()->routeIs('admin.process.interface') ? 'active' : '' }}">
                        <i class="fas fa-headset"></i>
                        <span>Traitement</span>
                    </a>
                </div>

                <div class="menu-item">
                    <a href="{{ route('admin.orders.index') }}" class="menu-link {{ request()->routeIs('admin.orders.index') ? 'active' : '' }}">
                        <i class="fas fa-list"></i>
                        <span>Liste des Commandes</span>
                    </a>
                </div>

                <div class="menu-item">
                    <a href="{{ route('admin.orders.create') }}" class="menu-link {{ request()->routeIs('admin.orders.create') ? 'active' : '' }}">
                        <i class="fas fa-plus"></i>
                        <span>Créer une Commande</span>
                    </a>
                </div>

                <div class="menu-item">
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="menu-link w-100 border-0 bg-transparent text-start">
                            <i class="fas fa-sign-out-alt"></i>
                            <span>Déconnexion</span>
                        </button>
                    </form>
                </div>
            @else
                {{-- MENU COMPLET POUR ADMIN ET MANAGER --}}
                <div class="menu-item">
                    <a href="{{ route('admin.dashboard') }}" class="menu-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                        <i class="fas fa-home"></i>
                        <span>Dashboard</span>
                    </a>
                </div>

                <!-- Traitement avec sous-menus -->
                <div class="menu-item">
                    <div class="menu-link {{ request()->routeIs('admin.process*') ? 'active expanded' : '' }}" onclick="toggleSubmenu(this)">
                        <i class="fas fa-phone"></i>
                        <span>Traitement</span>
                        <i class="fas fa-chevron-down menu-chevron"></i>
                    </div>
                    <div class="submenu {{ request()->routeIs('admin.process*') ? 'show' : '' }}">
                        <div class="submenu-item">
                            <a href="{{ route('admin.process.interface') }}" class="submenu-link {{ request()->routeIs('admin.process.interface') ? 'active' : '' }}">
                                <i class="fas fa-headset"></i>Interface de Traitement
                            </a>
                        </div>
                        <div class="submenu-item">
                            <a href="{{ route('admin.process.examination.index') }}" class="submenu-link {{ request()->routeIs('admin.process.examination*') ? 'active' : '' }}">
                                <i class="fas fa-search"></i>Examen Stock
                            </a>
                        </div>
                        <div class="submenu-item">
                            <a href="{{ route('admin.process.suspended.index') }}" class="submenu-link {{ request()->routeIs('admin.process.suspended*') ? 'active' : '' }}">
                                <i class="fas fa-pause-circle"></i>Commandes Suspendues
                            </a>
                        </div>
                        <div class="submenu-item">
                            <a href="{{ route('admin.process.restock.index') }}" class="submenu-link {{ request()->routeIs('admin.process.restock*') ? 'active' : '' }}">
                                <i class="fas fa-undo"></i>Retour en Stock
                            </a>
                        </div>
                    </div>
                </div>

                <div class="menu-item">
                    <a href="{{ route('admin.orders.index') }}" class="menu-link {{ request()->routeIs('admin.orders.index') ? 'active' : '' }}">
                        <i class="fas fa-list"></i>
                        <span>Commandes</span>
                    </a>
                </div>

                <div class="menu-item">
                    <a href="{{ route('admin.orders.create') }}" class="menu-link {{ request()->routeIs('admin.orders.create') ? 'active' : '' }}">
                        <i class="fas fa-plus"></i>
                        <span>Nouvelle Commande</span>
                    </a>
                </div>

                <!-- Produits avec sous-menus -->
                <div class="menu-item">
                    <div class="menu-link {{ request()->routeIs('admin.products*') ? 'active expanded' : '' }}" onclick="toggleSubmenu(this)">
                        <i class="fas fa-box"></i>
                        <span>Produits</span>
                        <i class="fas fa-chevron-down menu-chevron"></i>
                    </div>
                    <div class="submenu {{ request()->routeIs('admin.products*') ? 'show' : '' }}">
                        <div class="submenu-item">
                            <a href="{{ route('admin.products.index') }}" class="submenu-link {{ request()->routeIs('admin.products.index') ? 'active' : '' }}">
                                <i class="fas fa-list"></i>Liste des Produits
                            </a>
                        </div>
                        <div class="submenu-item">
                            <a href="{{ route('admin.products.create') }}" class="submenu-link {{ request()->routeIs('admin.products.create') ? 'active' : '' }}">
                                <i class="fas fa-plus-circle"></i>Créer un Produit
                            </a>
                        </div>
                        <div class="submenu-item">
                            <a href="{{ route('admin.products.review') }}" class="submenu-link {{ request()->routeIs('admin.products.review') ? 'active' : '' }}">
                                <i class="fas fa-eye"></i>Review Produits
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Livraison avec sous-menus -->
                <div class="menu-item">
                    <div class="menu-link {{ request()->routeIs('admin.delivery*') ? 'active expanded' : '' }}" onclick="toggleSubmenu(this)">
                        <i class="fas fa-truck"></i>
                        <span>Livraison</span>
                        <i class="fas fa-chevron-down menu-chevron"></i>
                    </div>
                    <div class="submenu {{ request()->routeIs('admin.delivery*') ? 'show' : '' }}">
                        <div class="submenu-item">
                            <a href="{{ route('admin.delivery.shipments') }}" class="submenu-link {{ request()->routeIs('admin.delivery.shipments') ? 'active' : '' }}">
                                <i class="fas fa-shipping-fast"></i>Expéditions
                            </a>
                        </div>
                        <div class="submenu-item">
                            <a href="{{ route('admin.delivery.preparation') }}" class="submenu-link {{ request()->routeIs('admin.delivery.preparation') ? 'active' : '' }}">
                                <i class="fas fa-box-open"></i>Préparation
                            </a>
                        </div>
                        <div class="submenu-item">
                            <a href="{{ route('admin.delivery.configuration') }}" class="submenu-link {{ request()->routeIs('admin.delivery.configuration') ? 'active' : '' }}">
                                <i class="fas fa-cog"></i>Configuration
                            </a>
                        </div>
                        <div class="submenu-item">
                            <a href="{{ route('admin.delivery.stats') }}" class="submenu-link {{ request()->routeIs('admin.delivery.stats') ? 'active' : '' }}">
                                <i class="fas fa-chart-bar"></i>Statistiques
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Importation avec sous-menus -->
                <div class="menu-item">
                    <div class="menu-link {{ request()->routeIs('admin.woocommerce*') || request()->routeIs('admin.import*') || request()->routeIs('admin.shopify*') || request()->routeIs('admin.prestashop*') ? 'active expanded' : '' }}" onclick="toggleSubmenu(this)">
                        <i class="fas fa-download"></i>
                        <span>Importation</span>
                        <i class="fas fa-chevron-down menu-chevron"></i>
                    </div>
                    <div class="submenu {{ request()->routeIs('admin.woocommerce*') || request()->routeIs('admin.import*') || request()->routeIs('admin.shopify*') || request()->routeIs('admin.prestashop*') ? 'show' : '' }}">
                        <div class="submenu-item">
                            <a href="{{ route('admin.woocommerce.index') }}" class="submenu-link {{ request()->routeIs('admin.woocommerce.index') ? 'active' : '' }}">
                                <i class="fab fa-wordpress"></i>WooCommerce
                            </a>
                        </div>
                        <div class="submenu-item">
                            <a href="{{ route('admin.shopify.index') }}" class="submenu-link {{ request()->routeIs('admin.shopify.index') ? 'active' : '' }}">
                                <i class="fab fa-shopify"></i>Shopify
                            </a>
                        </div>
                        <div class="submenu-item">
                            <a href="{{ route('admin.prestashop.index') }}" class="submenu-link {{ request()->routeIs('admin.prestashop.index') ? 'active' : '' }}">
                                <i class="fas fa-shopping-bag"></i>PrestaShop
                            </a>
                        </div>
                        <div class="submenu-item">
                            <a href="{{ route('admin.import.index') }}" class="submenu-link {{ request()->routeIs('admin.import*') ? 'active' : '' }}">
                                <i class="fas fa-file-csv"></i>Importation CSV
                            </a>
                        </div>
                    </div>
                </div>

                @if($isAdmin)
                <div class="menu-item">
                    <a href="{{ route('admin.employees.index') }}" class="menu-link {{ request()->routeIs('admin.employees*') ? 'active' : '' }}">
                        <i class="fas fa-users"></i>
                        <span>Employés</span>
                    </a>
                </div>

                <div class="menu-item">
                    <a href="{{ route('admin.managers.index') }}" class="menu-link {{ request()->routeIs('admin.managers*') ? 'active' : '' }}">
                        <i class="fas fa-user-tie"></i>
                        <span>Managers</span>
                    </a>
                </div>

                <div class="menu-item">
                    <a href="{{ route('admin.settings.index') }}" class="menu-link {{ request()->routeIs('admin.settings*') ? 'active' : '' }}">
                        <i class="fas fa-cog"></i>
                        <span>Paramètres</span>
                    </a>
                </div>
                @endif

                <div class="menu-item">
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="menu-link w-100 border-0 bg-transparent text-start">
                            <i class="fas fa-sign-out-alt"></i>
                            <span>Déconnexion</span>
                        </button>
                    </form>
                </div>
            @endif
        </nav>
    </aside>

    <!-- Header -->
    <header class="main-header">
        <h1 class="header-title">@yield('page-title', 'Dashboard')</h1>

        <div class="header-actions">
            <div class="user-menu">
                <div class="user-avatar">{{ $userInitial }}</div>
                <div class="user-info">
                    <div class="user-name">{{ $userName }}</div>
                    <div class="user-role">
                        @if($isAdmin) Administrateur
                        @elseif($isManager) Manager
                        @else Employé
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </header>

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

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('mobileOverlay');
            sidebar.classList.toggle('active');
            overlay.classList.toggle('active');
        }

        function closeSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('mobileOverlay');
            sidebar.classList.remove('active');
            overlay.classList.remove('active');
        }

        function toggleSubmenu(element) {
            element.classList.toggle('expanded');
            const submenu = element.nextElementSibling;
            submenu.classList.toggle('show');
        }

        // Close sidebar on link click (mobile)
        if (window.innerWidth <= 768) {
            document.querySelectorAll('.submenu-link, .menu-link[href]').forEach(link => {
                link.addEventListener('click', closeSidebar);
            });
        }
    </script>

    @yield('scripts')
</body>
</html>
