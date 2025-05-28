<!DOCTYPE html>
<html lang="fr">

<head>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') - Order Manager</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">

    <!-- Custom CSS -->
    <style>
        :root {
            --primary-color: #5a6acf;
            --secondary-color: #f8f9fc;
            --success-color: #42ba96;
            --danger-color: #df4759;
            --warning-color: #ffc107;
            --info-color: #467fd0;
            --dark-color: #2c3e50;
            --light-color: #f8f9fa;
            --body-bg: #f5f7fb;
            --card-bg: #ffffff;
            --card-border: #edf2f9;
            --text-color: #4a5568;
            --sidebar-width: 250px;
            --sidebar-collapsed-width: 70px;
            --header-height: 60px;
            --brand-color: #ffa500;
            /* Couleur orange pour le logo */
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: var(--body-bg);
            color: var(--text-color);
            overflow-x: hidden;
        }

        /* Sidebar */
        .sidebar {
            min-height: 100vh;
            background-color: #4e73df;
            background-image: linear-gradient(180deg, #4e73df 10%, #224abe 100%);
            background-size: cover;
            position: fixed;
            top: 0;
            left: 0;
            width: 250px;
            z-index: 1;
            padding-top: 0;
            /* Modifié pour le logo */
            transition: all 0.3s;
        }

        .sidebar-collapsed {
            width: 70px;
        }

        /* Modification pour le style du logo */
        .sidebar-brand {
            background-color: #f0f0f0;
            margin: 0;
            padding: 15px;
            text-align: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
        }

        .sidebar-brand a {
            color: #333;
            font-weight: bold;
            text-decoration: none;
        }

        .sidebar-brand .fas {
            color: #333;
        }

        .logo-full {
            display: block;
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

        .sidebar-menu {
            padding: 0;
            list-style: none;
        }

        .sidebar-item {
            position: relative;
            margin-bottom: 5px;
        }

        .sidebar-link {
            display: flex;
            align-items: center;
            padding: 15px;
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            transition: all 0.3s;
        }

        .sidebar-link:hover {
            color: white;
            background-color: rgba(255, 255, 255, 0.1);
        }

        .sidebar-link.active {
            color: white;
            background-color: rgba(255, 255, 255, 0.2);
        }

        .sidebar-icon {
            min-width: 30px;
            text-align: center;
            font-size: 1.1rem;
        }

        .sidebar-text {
            margin-left: 10px;
            font-weight: 500;
        }

        .sidebar-collapsed .sidebar-text {
            display: none;
        }

        /* Submenu */
        .sidebar-submenu {
            list-style: none;
            padding-left: 0;
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease-out;
        }

        .sidebar-submenu.show {
            max-height: 200px;
        }

        .sidebar-collapsed .sidebar-submenu {
            position: absolute;
            left: 70px;
            top: 0;
            width: 180px;
            background-color: #4e73df;
            border-radius: 0 4px 4px 0;
            display: none;
            max-height: none;
            box-shadow: 2px 2px 5px rgba(0, 0, 0, 0.2);
        }

        .sidebar-collapsed .sidebar-item:hover .sidebar-submenu {
            display: block;
        }

        .sidebar-submenu-item {
            margin: 5px 0;
        }

        .sidebar-submenu-link {
            display: block;
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            padding: 8px 15px 8px 55px;
            font-size: 0.9rem;
            transition: all 0.3s;
        }

        .sidebar-collapsed .sidebar-submenu-link {
            padding: 8px 15px;
        }

        .sidebar-submenu-link:hover {
            color: white;
            background-color: rgba(255, 255, 255, 0.1);
        }

        .sidebar-submenu-link.active {
            color: white;
            background-color: rgba(255, 255, 255, 0.2);
        }

        /* Content */
        .content {
            margin-left: 250px;
            padding: 20px;
            transition: all 0.3s;
        }

        .content-expanded {
            margin-left: 70px;
        }

        /* Header Navbar */
        .navbar {
            background-color: white;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
            margin-bottom: 20px;
            padding: 0.75rem 1rem;
        }

        .card {
            border: none;
            border-radius: 0.35rem;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
            margin-bottom: 20px;
            overflow: hidden;
        }

        .card-header {
            background-color: #f8f9fc;
            border-bottom: 1px solid #e3e6f0;
            padding: 1rem;
        }

        /* Footer */
        .footer {
            background-color: white;
            padding: 1rem;
            text-align: center;
            margin-top: 20px;
        }

        /* Animation de chargement */
        .page-loader {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(255, 255, 255, 0.9);
            z-index: 9999;
            display: none;
            /* Changed from flex to none by default */
            justify-content: center;
            align-items: center;
            transition: opacity 0.5s ease;
            opacity: 0;
            /* Start hidden */
        }

        .page-loader.show {
            display: flex;
            opacity: 1;
        }

        .page-loader.fade-out {
            opacity: 0;
        }

        .loader-logo {
            animation: moveUpDown 1.2s infinite alternate;
            transform-origin: center;
        }

        @keyframes moveUpDown {
            0% {
                transform: translateY(20px) scale(0.8);
                opacity: 0.5;
            }

            100% {
                transform: translateY(-20px) scale(1);
                opacity: 1;
            }
        }

        @media (max-width: 768px) {
            .sidebar {
                width: 0;
                padding-top: 60px;
            }

            .content {
                margin-left: 0;
            }

            .sidebar-collapsed {
                width: 250px;
            }

            .content-expanded {
                margin-left: 0;
            }

            .sidebar-collapsed .sidebar-text {
                display: inline;
            }

            .sidebar-collapsed .logo-mini {
                display: none;
            }

            .sidebar-collapsed .logo-full {
                display: block;
            }

            .sidebar-collapsed .sidebar-submenu {
                position: static;
                width: 100%;
                display: block;
                box-shadow: none;
            }

            .sidebar-collapsed .sidebar-submenu-link {
                padding: 8px 15px 8px 55px;
            }
        }
    </style>

    @yield('css')
</head>

<body>

    <!-- Animation de chargement -->
    <div class="page-loader" id="pageLoader">
        <div class="loader-logo">
            <i class="fas fa-shopping-cart" style="font-size: 50px; color: #4e73df;"></i>
            <div style="text-align: center; margin-top: 10px; font-weight: bold; color: #4e73df;">
                Order Manager
            </div>
        </div>
    </div>

    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <div class="sidebar-brand">
            <a href="{{ route('admin.dashboard') }}" class="logo-full">
                <i class="fas fa-shopping-cart me-2"></i>
                Order Manager
            </a>
            <a href="{{ route('admin.dashboard') }}" class="logo-mini">
                <i class="fas fa-shopping-cart" style="font-size: 24px;"></i>
            </a>
        </div>



        <ul class="sidebar-menu">
            <li class="sidebar-item">
                <a href="{{ route('admin.dashboard') }}"
                    class="sidebar-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    <div class="sidebar-icon">
                        <i class="fas fa-tachometer-alt"></i>
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
                            Liste des produits
                        </a>
                    </li>
                    <li class="sidebar-submenu-item">
                        <a href="{{ route('admin.products.create') }}"
                            class="sidebar-submenu-link {{ request()->routeIs('admin.products.create') ? 'active' : '' }}">
                            Ajouter un produit
                        </a>
                    </li>
                </ul>
            </li>
            <!-- Ajoutez ce code après la section des produits dans la barre latérale -->
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
                            Liste des commandes
                        </a>
                    </li>
                    <li class="sidebar-submenu-item">
                        <a href="{{ route('admin.orders.create') }}"
                            class="sidebar-submenu-link {{ request()->routeIs('admin.orders.create') ? 'active' : '' }}">
                            Ajouter une commande
                        </a>
                    </li>
                </ul>
            </li>

            <!-- Après la section des commandes dans le menu -->
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
                            Import CSV/Excel
                        </a>
                    </li>
                    <li class="sidebar-submenu-item">
                        <a href="{{ route('admin.woocommerce.index') }}"
                            class="sidebar-submenu-link {{ request()->routeIs('admin.woocommerce.index') ? 'active' : '' }}">
                            WooCommerce
                        </a>
                    </li>
                </ul>
            </li>

            <!-- AJOUTEZ CETTE SECTION DANS VOTRE MENU SIDEBAR APRÈS LA SECTION "Commandes" -->

            <!-- Gestion des Utilisateurs -->
            <li class="sidebar-item">
                <a href="#" class="sidebar-link {{ request()->routeIs('admin.managers*') || request()->routeIs('admin.employees*') || request()->routeIs('admin.login-history*') ? 'active' : '' }}"
                    data-target="usersSubmenu">
                    <div class="sidebar-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <span class="sidebar-text">Gestion Utilisateurs</span>
                </a>
                <ul class="sidebar-submenu {{ request()->routeIs('admin.managers*') || request()->routeIs('admin.employees*') || request()->routeIs('admin.login-history*') ? 'show' : '' }}" 
                    id="usersSubmenu">
                    <li class="sidebar-submenu-item">
                        <a href="{{ route('admin.managers.index') }}"
                            class="sidebar-submenu-link {{ request()->routeIs('admin.managers*') ? 'active' : '' }}">
                            <i class="fas fa-user-tie me-2"></i>
                            Managers
                            <span class="badge bg-primary ms-auto">
                                {{ auth('admin')->user()->managers()->count() }}/{{ auth('admin')->user()->max_managers }}
                            </span>
                        </a>
                    </li>
                    <li class="sidebar-submenu-item">
                        <a href="{{ route('admin.employees.index') }}"
                            class="sidebar-submenu-link {{ request()->routeIs('admin.employees*') ? 'active' : '' }}">
                            <i class="fas fa-user-friends me-2"></i>
                            Employés
                            <span class="badge bg-success ms-auto">
                                {{ auth('admin')->user()->employees()->count() }}/{{ auth('admin')->user()->max_employees }}
                            </span>
                        </a>
                    </li>
                    <li class="sidebar-submenu-item">
                        <a href="{{ route('admin.login-history.index') }}"
                            class="sidebar-submenu-link {{ request()->routeIs('admin.login-history*') ? 'active' : '' }}">
                            <i class="fas fa-history me-2"></i>
                            Historique Connexions
                        </a>
                    </li>
                </ul>
            </li>            

            <!-- Lien vers la page des paramètres -->
            <li class="sidebar-item">
                <a href="{{ route('admin.settings.index') }}"
                    class="sidebar-link {{ request()->routeIs('admin.settings.index') ? 'active' : '' }}">
                    <div class="sidebar-icon">
                        <i class="fas fa-cog"></i>
                    </div>
                    <span class="sidebar-text">Paramètres</span>
                </a>
            </li>

            <!-- Autres éléments du menu à ajouter plus tard -->
        </ul>
    </div>

    <!-- Content -->
    <div class="content" id="content">
        <!-- Header Navbar -->
        <nav class="navbar navbar-expand navbar-light">
            <button class="btn" id="sidebarToggle">
                <i class="fas fa-bars"></i>
            </button>

            <div class="ms-auto">
                <div class="dropdown">
                    <a class="btn dropdown-toggle" href="#" role="button" id="userDropdown"
                        data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-user-circle me-1"></i>
                        <span class="d-none d-md-inline">{{ Auth::guard('admin')->user()->name }}</span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                        <li>
                            <form action="{{ route('admin.logout') }}" method="POST">
                                @csrf
                                <button type="submit" class="dropdown-item">
                                    <i class="fas fa-sign-out-alt fa-sm fa-fw me-2 text-gray-400"></i>
                                    Déconnexion
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>

        <!-- Notification pour les nouveaux produits -->
        @php
            $pending_products_count = Auth::guard('admin')->user()->products()->where('needs_review', true)->count();
        @endphp

        @if ($pending_products_count > 0)
            <div class="alert alert-warning alert-dismissible fade show mb-4" role="alert">
                <div class="d-flex align-items-center">
                    <div class="alert-icon me-3">
                        <i class="fas fa-exclamation-triangle fa-2x"></i>
                    </div>
                    <div>
                        <h5 class="mb-1">Nouveaux produits à examiner</h5>
                        <p class="mb-0">
                            {{ $pending_products_count }} produit(s) créé(s) automatiquement lors d'importations
                            nécessite(nt) votre attention.
                            <a href="{{ route('admin.products.review') }}" class="alert-link">Examiner maintenant</a>
                        </p>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <!-- Page Content -->
        <div class="container-fluid">
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @yield('content')
        </div>

        <!-- Footer -->
        <footer class="footer">
            <div class="container">
                <span class="text-muted">© {{ date('Y') }} Order Manager. Tous droits réservés.</span>
            </div>
        </footer>
    </div>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- Custom JS -->
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const sidebar = document.getElementById('sidebar');
            const content = document.getElementById('content');
            const sidebarToggle = document.getElementById('sidebarToggle');
            const pageLoader = document.getElementById('pageLoader');

            // Loader management
            let loaderTimeout;

            function showLoader() {
                if (pageLoader) {
                    clearTimeout(loaderTimeout);
                    pageLoader.classList.remove('fade-out');
                    pageLoader.classList.add('show');
                    pageLoader.style.display = 'flex';
                    pageLoader.style.opacity = '1';
                }
            }

            function hideLoader() {
                if (pageLoader) {
                    clearTimeout(loaderTimeout);
                    pageLoader.classList.add('fade-out');
                    pageLoader.style.opacity = '0';

                    setTimeout(function() {
                        pageLoader.classList.remove('show');
                        pageLoader.style.display = 'none';
                    }, 500);
                }
            }

            // Force hide loader immediately on page load
            hideLoader();

            // Fonction pour appliquer l'état de la barre latérale
            function applySidebarState() {
                const isCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';

                if (isCollapsed) {
                    sidebar.classList.add('sidebar-collapsed');
                    content.classList.add('content-expanded');
                } else {
                    sidebar.classList.remove('sidebar-collapsed');
                    content.classList.remove('content-expanded');
                }
            }

            // Appliquer l'état sauvegardé au chargement de la page
            applySidebarState();

            // Sidebar Toggle avec sauvegarde dans localStorage
            sidebarToggle.addEventListener('click', function() {
                const isNowCollapsed = !sidebar.classList.contains('sidebar-collapsed');
                localStorage.setItem('sidebarCollapsed', isNowCollapsed.toString());

                sidebar.classList.toggle('sidebar-collapsed');
                content.classList.toggle('content-expanded');
            });

            // Menu Toggle - Non collapsé uniquement
            const menuLinks = document.querySelectorAll('.sidebar-link[data-target]');
            menuLinks.forEach(function(link) {
                link.addEventListener('click', function(e) {
                    if (!sidebar.classList.contains('sidebar-collapsed')) {
                        e.preventDefault();
                        const targetId = this.getAttribute('data-target');
                        const submenu = document.getElementById(targetId);

                        if (submenu.classList.contains('show')) {
                            submenu.classList.remove('show');
                        } else {
                            submenu.classList.add('show');
                        }
                    }
                });
            });

            // Auto-expand current submenu
            const currentSubmenu = document.querySelector('.sidebar-submenu.show');
            if (currentSubmenu) {
                const parentLink = currentSubmenu.previousElementSibling;
                if (parentLink && parentLink.hasAttribute('data-target')) {
                    parentLink.classList.add('active');
                }
            }

            // Show loader for navigation links
            document.addEventListener('click', function(e) {
                const target = e.target.closest('a');
                if (target &&
                    target.href &&
                    target.href.indexOf(window.location.origin) === 0 &&
                    !target.hasAttribute('data-bs-toggle') &&
                    !target.classList.contains('sidebar-link') &&
                    !target.classList.contains('sidebar-submenu-link') &&
                    !target.closest('.dropdown-menu') &&
                    !target.classList.contains('alert-link') &&
                    !target.classList.contains('btn-close') &&
                    target.getAttribute('href') !== '#' &&
                    target.getAttribute('href') !== 'javascript:void(0)') {

                    showLoader();

                    // Safety timeout to hide loader if navigation fails
                    loaderTimeout = setTimeout(hideLoader, 5000);
                }
            });

            // Show loader for form submissions
            const forms = document.querySelectorAll('form:not([data-no-loader])');
            forms.forEach(function(form) {
                form.addEventListener('submit', function(e) {
                    showLoader();

                    // Safety timeout for forms
                    loaderTimeout = setTimeout(hideLoader, 10000);
                });
            });

            // Hide loader when page becomes visible again
            document.addEventListener('visibilitychange', function() {
                if (!document.hidden) {
                    hideLoader();
                }
            });

            // Hide loader on window load (backup)
            window.addEventListener('load', function() {
                hideLoader();
            });

            // Ultimate fallback - force hide after 2 seconds
            setTimeout(hideLoader, 2000);
        });

        // Additional backup - hide loader as soon as script runs
        (function() {
            const loader = document.getElementById('pageLoader') || document.querySelector('.page-loader');
            if (loader) {
                loader.style.display = 'none';
                loader.style.opacity = '0';
            }
        })();
    </script>

    @yield('scripts')
</body>

</html>
