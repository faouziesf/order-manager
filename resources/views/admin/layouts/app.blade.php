<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Admin Panel') - {{ config('app.name', 'Order Manager') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Toastr CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">

    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8f9fa;
        }

        .sidebar {
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            transition: all 0.3s ease;
            position: fixed;
            top: 0;
            left: 0;
            z-index: 1050;
            transform: translateX(-100%);
        }

        .sidebar.show {
            transform: translateX(0);
        }

        @media (min-width: 992px) {
            .sidebar {
                position: relative;
                transform: translateX(0) !important;
            }

            .main-content {
                margin-left: 0;
            }
        }

        .sidebar-brand {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
        }

        .nav-link {
            color: rgba(255, 255, 255, 0.8) !important;
            border-radius: 8px;
            margin-bottom: 4px;
            padding: 12px 16px;
            transition: all 0.3s ease;
        }

        .nav-link:hover {
            color: white !important;
            background: rgba(255, 255, 255, 0.1);
            transform: translateX(5px);
        }

        .nav-link.active {
            color: white !important;
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(10px);
        }

        .submenu {
            background: rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            margin-top: 8px;
            padding: 8px;
        }

        .submenu .nav-link {
            font-size: 0.875rem;
            padding: 8px 12px;
            color: rgba(255, 255, 255, 0.7) !important;
        }

        .badge-counter {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            border: 1px solid rgba(255, 255, 255, 0.3);
        }

        .header-card {
            background: white;
            border: none;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .content-card {
            background: white;
            border: none;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            border-radius: 12px;
        }

        .alert-dismissible {
            border: none;
            border-radius: 12px;
        }

        .sidebar-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1040;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
        }

        .sidebar-overlay.show {
            opacity: 1;
            visibility: visible;
        }

        .profile-section {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 12px;
        }

        .profile-avatar {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: 2px solid rgba(255, 255, 255, 0.3);
        }

        .main-content {
            min-height: 100vh;
            transition: all 0.3s ease;
        }

        .navbar-brand-text {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            font-weight: 700;
        }

        .status-badge {
            font-size: 0.75rem;
            padding: 4px 8px;
            border-radius: 20px;
        }

        [x-cloak] {
            display: none !important;
        }
    </style>

    @stack('styles')
</head>
<body x-data="{ sidebarOpen: false }" class="bg-light">
    <!-- Sidebar -->
    <div class="sidebar" :class="{ 'show': sidebarOpen }" style="width: 280px;" x-cloak>
        <!-- Brand -->
        <div class="sidebar-brand d-flex align-items-center justify-content-center p-4">
            <h4 class="text-white mb-0 fw-bold">
                <i class="fas fa-store me-2"></i>
                {{ auth('admin')->user()->shop_name ?? 'Admin Panel' }}
            </h4>
        </div>

        <!-- Navigation -->
        <nav class="px-3 pb-4" style="height: calc(100vh - 120px); overflow-y: auto;">
            <ul class="nav flex-column">
                <!-- Dashboard -->
                <li class="nav-item">
                    <a href="{{ route('admin.dashboard') }}"
                       class="nav-link d-flex align-items-center {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                        <i class="fas fa-tachometer-alt me-3"></i>
                        Dashboard
                    </a>
                </li>

                @if(auth('admin')->user()->role === \App\Models\Admin::ROLE_EMPLOYEE)
                    {{-- MENU EMPLOYEE: Seulement Traitement et Commandes --}}

                    <!-- Interface de traitement -->
                    <li class="nav-item">
                        <a href="{{ route('admin.process.interface') }}"
                           class="nav-link d-flex align-items-center {{ request()->routeIs('admin.process.*') ? 'active' : '' }}">
                            <i class="fas fa-tasks me-3"></i>
                            Traitement
                        </a>
                    </li>

                    <!-- Commandes -->
                    <li class="nav-item" x-data="{ open: {{ request()->routeIs('admin.orders.*') ? 'true' : 'false' }} }">
                        <a href="#" @click.prevent="open = !open"
                           class="nav-link d-flex align-items-center justify-content-between {{ request()->routeIs('admin.orders.*') ? 'active' : '' }}">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-shopping-cart me-3"></i>
                                Commandes
                            </div>
                            <i class="fas fa-chevron-down transition-transform" :class="{ 'rotate-180': open }"></i>
                        </a>

                        <div x-show="open" x-transition class="submenu">
                            <ul class="nav flex-column">
                                <li class="nav-item">
                                    <a href="{{ route('admin.orders.index') }}"
                                       class="nav-link d-flex align-items-center {{ request()->routeIs('admin.orders.index') ? 'active' : '' }}">
                                        <i class="fas fa-list me-2"></i>
                                        Mes commandes
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('admin.orders.create') }}"
                                       class="nav-link d-flex align-items-center {{ request()->routeIs('admin.orders.create') ? 'active' : '' }}">
                                        <i class="fas fa-plus me-2"></i>
                                        Créer commande
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>

                @elseif(auth('admin')->user()->role === \App\Models\Admin::ROLE_MANAGER)
                    {{-- MENU MANAGER: Toutes les commandes de l'admin --}}

                    <!-- Commandes -->
                    <li class="nav-item" x-data="{ open: {{ request()->routeIs('admin.orders.*') || request()->routeIs('admin.process.*') ? 'true' : 'false' }} }">
                        <a href="#" @click.prevent="open = !open"
                           class="nav-link d-flex align-items-center justify-content-between {{ request()->routeIs('admin.orders.*') || request()->routeIs('admin.process.*') ? 'active' : '' }}">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-shopping-cart me-3"></i>
                                Commandes
                            </div>
                            <i class="fas fa-chevron-down transition-transform" :class="{ 'rotate-180': open }"></i>
                        </a>

                        <div x-show="open" x-transition class="submenu">
                            <ul class="nav flex-column">
                                <li class="nav-item">
                                    <a href="{{ route('admin.orders.index') }}"
                                       class="nav-link d-flex align-items-center {{ request()->routeIs('admin.orders.index') ? 'active' : '' }}">
                                        <i class="fas fa-list me-2"></i>
                                        Toutes les commandes
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('admin.process.interface') }}"
                                       class="nav-link d-flex align-items-center {{ request()->routeIs('admin.process.*') ? 'active' : '' }}">
                                        <i class="fas fa-tasks me-2"></i>
                                        Traitement
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('admin.orders.create') }}"
                                       class="nav-link d-flex align-items-center {{ request()->routeIs('admin.orders.create') ? 'active' : '' }}">
                                        <i class="fas fa-plus me-2"></i>
                                        Créer commande
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>

                @else
                    {{-- MENU ADMIN COMPLET --}}

                    <!-- Gestion des utilisateurs -->
                    <li class="nav-item" x-data="{ open: {{ request()->routeIs('admin.managers.*') || request()->routeIs('admin.employees.*') ? 'true' : 'false' }} }">
                        <a href="#" @click.prevent="open = !open"
                           class="nav-link d-flex align-items-center justify-content-between {{ request()->routeIs('admin.managers.*') || request()->routeIs('admin.employees.*') ? 'active' : '' }}">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-users me-3"></i>
                                Gestion Utilisateurs
                            </div>
                            <i class="fas fa-chevron-down transition-transform" :class="{ 'rotate-180': open }"></i>
                        </a>

                        <div x-show="open" x-transition class="submenu">
                            <ul class="nav flex-column">
                                <li class="nav-item">
                                    <a href="{{ route('admin.managers.index') }}"
                                       class="nav-link d-flex align-items-center justify-content-between {{ request()->routeIs('admin.managers.*') ? 'active' : '' }}">
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-user-tie me-2"></i>
                                            Managers
                                        </div>
                                        <span class="badge badge-counter rounded-pill">
                                            {{ \App\Models\Admin::where('role', \App\Models\Admin::ROLE_MANAGER)->where('created_by', auth('admin')->id())->count() }}/{{ auth('admin')->user()->max_managers }}
                                        </span>
                                    </a>
                                </li>

                                <li class="nav-item">
                                    <a href="{{ route('admin.employees.index') }}"
                                       class="nav-link d-flex align-items-center justify-content-between {{ request()->routeIs('admin.employees.*') ? 'active' : '' }}">
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-users me-2"></i>
                                            Employés
                                        </div>
                                        <span class="badge badge-counter rounded-pill">
                                            {{ \App\Models\Admin::where('role', \App\Models\Admin::ROLE_EMPLOYEE)->where('created_by', auth('admin')->id())->count() }}/{{ auth('admin')->user()->max_employees }}
                                        </span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>

                    <!-- Produits -->
                    <li class="nav-item" x-data="{ open: {{ request()->routeIs('admin.products.*') ? 'true' : 'false' }} }">
                        <a href="#" @click.prevent="open = !open"
                           class="nav-link d-flex align-items-center justify-content-between {{ request()->routeIs('admin.products.*') ? 'active' : '' }}">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-box me-3"></i>
                                Produits
                            </div>
                            <i class="fas fa-chevron-down transition-transform" :class="{ 'rotate-180': open }"></i>
                        </a>

                        <div x-show="open" x-transition class="submenu">
                            <ul class="nav flex-column">
                                <li class="nav-item">
                                    <a href="{{ route('admin.products.index') }}"
                                       class="nav-link d-flex align-items-center {{ request()->routeIs('admin.products.index') ? 'active' : '' }}">
                                        <i class="fas fa-list me-2"></i>
                                        Liste des produits
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('admin.products.create') }}"
                                       class="nav-link d-flex align-items-center {{ request()->routeIs('admin.products.create') ? 'active' : '' }}">
                                        <i class="fas fa-plus me-2"></i>
                                        Ajouter un produit
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>

                    <!-- Commandes -->
                    <li class="nav-item" x-data="{ open: {{ request()->routeIs('admin.orders.*') || request()->routeIs('admin.process.*') ? 'true' : 'false' }} }">
                        <a href="#" @click.prevent="open = !open"
                           class="nav-link d-flex align-items-center justify-content-between {{ request()->routeIs('admin.orders.*') || request()->routeIs('admin.process.*') ? 'active' : '' }}">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-shopping-cart me-3"></i>
                                Commandes
                            </div>
                            <i class="fas fa-chevron-down transition-transform" :class="{ 'rotate-180': open }"></i>
                        </a>

                        <div x-show="open" x-transition class="submenu">
                            <ul class="nav flex-column">
                                <li class="nav-item">
                                    <a href="{{ route('admin.orders.index') }}"
                                       class="nav-link d-flex align-items-center {{ request()->routeIs('admin.orders.index') ? 'active' : '' }}">
                                        <i class="fas fa-list me-2"></i>
                                        Toutes les commandes
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('admin.process.interface') }}"
                                       class="nav-link d-flex align-items-center {{ request()->routeIs('admin.process.*') ? 'active' : '' }}">
                                        <i class="fas fa-tasks me-2"></i>
                                        Traitement
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('admin.orders.create') }}"
                                       class="nav-link d-flex align-items-center {{ request()->routeIs('admin.orders.create') ? 'active' : '' }}">
                                        <i class="fas fa-plus me-2"></i>
                                        Créer commande
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>

                    <!-- Livraison — Masafa Express -->
                    <li class="nav-item">
                        <a href="{{ route('admin.delivery.index') }}"
                           class="nav-link d-flex align-items-center {{ request()->routeIs('admin.delivery.*') ? 'active' : '' }}">
                            <i class="fas fa-truck me-3"></i>
                            Livraison
                            <span class="ms-auto badge" style="background:linear-gradient(135deg,#0f4c81,#1a73c8);font-size:.65rem;padding:.2rem .45rem;border-radius:4px;">Masafa</span>
                        </a>
                    </li>

                    <!-- Intégrations -->
                    <li class="nav-item" x-data="{ open: {{ request()->routeIs('admin.woocommerce.*') || request()->routeIs('admin.import.*') ? 'true' : 'false' }} }">
                        <a href="#" @click.prevent="open = !open"
                           class="nav-link d-flex align-items-center justify-content-between {{ request()->routeIs('admin.woocommerce.*') || request()->routeIs('admin.import.*') ? 'active' : '' }}">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-plug me-3"></i>
                                Intégrations
                            </div>
                            <i class="fas fa-chevron-down transition-transform" :class="{ 'rotate-180': open }"></i>
                        </a>

                        <div x-show="open" x-transition class="submenu">
                            <ul class="nav flex-column">
                                <li class="nav-item">
                                    <a href="{{ route('admin.woocommerce.index') }}"
                                       class="nav-link d-flex align-items-center {{ request()->routeIs('admin.woocommerce.*') ? 'active' : '' }}">
                                        <i class="fab fa-wordpress me-2"></i>
                                        WooCommerce
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('admin.import.index') }}"
                                       class="nav-link d-flex align-items-center {{ request()->routeIs('admin.import.*') ? 'active' : '' }}">
                                        <i class="fas fa-file-import me-2"></i>
                                        Import CSV/XML
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>

                    <!-- Paramètres -->
                    <li class="nav-item">
                        <a href="{{ route('admin.settings.index') }}"
                           class="nav-link d-flex align-items-center {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}">
                            <i class="fas fa-cog me-3"></i>
                            Paramètres
                        </a>
                    </li>
                @endif
            </ul>
        </nav>

        <!-- Profile section -->
        <div class="position-absolute bottom-0 w-100 p-3">
            <div class="profile-section p-3">
                <div class="d-flex align-items-center">
                    <div class="profile-avatar rounded-circle d-flex align-items-center justify-content-center me-3">
                        <span class="text-white fw-bold">{{ substr(auth('admin')->user()->name, 0, 1) }}</span>
                    </div>
                    <div class="flex-grow-1 text-truncate">
                        <div class="text-white fw-medium text-truncate">{{ auth('admin')->user()->name }}</div>
                        <div class="text-white-50 small text-truncate">
                            @if(auth('admin')->user()->role === \App\Models\Admin::ROLE_ADMIN)
                                <i class="fas fa-crown me-1"></i> Administrateur
                            @elseif(auth('admin')->user()->role === \App\Models\Admin::ROLE_MANAGER)
                                <i class="fas fa-user-tie me-1"></i> Manager
                            @else
                                <i class="fas fa-user me-1"></i> Employé
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Sidebar Overlay -->
    <div class="sidebar-overlay" :class="{ 'show': sidebarOpen }" @click="sidebarOpen = false" x-cloak></div>

    <!-- Main content -->
    <div class="main-content" style="margin-left: 0;">
        <div class="d-lg-flex">
            <div style="width: 280px;" class="d-none d-lg-block"></div>
            <div class="flex-grow-1">
                <!-- Top navigation -->
                <header class="header-card mb-4">
                    <div class="container-fluid">
                        <div class="d-flex align-items-center justify-content-between py-3">
                            <div class="d-flex align-items-center">
                                <button @click="sidebarOpen = !sidebarOpen" class="btn btn-link d-lg-none p-2 text-muted">
                                    <i class="fas fa-bars fa-lg"></i>
                                </button>

                                <div class="ms-3 ms-lg-0">
                                    <h2 class="navbar-brand-text mb-0">@yield('page-title', 'Dashboard')</h2>
                                    @hasSection('breadcrumb')
                                        <nav aria-label="breadcrumb">
                                            <ol class="breadcrumb mb-0 small text-muted">
                                                @yield('breadcrumb')
                                            </ol>
                                        </nav>
                                    @endhasSection
                                </div>
                            </div>

                            <div class="d-flex align-items-center">
                                <!-- Account Status -->
                                <div class="d-flex align-items-center me-3">
                                    @if(auth('admin')->user()->expiry_date && auth('admin')->user()->role === \App\Models\Admin::ROLE_ADMIN)
                                        <span class="status-badge me-2 {{ auth('admin')->user()->expiry_date->isPast() ? 'bg-danger text-white' : (auth('admin')->user()->expiry_date->diffInDays() <= 7 ? 'bg-warning text-dark' : 'bg-success text-white') }}">
                                            Expire: {{ auth('admin')->user()->expiry_date->format('d/m/Y') }}
                                        </span>
                                    @endif

                                    <span class="status-badge {{ auth('admin')->user()->is_active ? 'bg-success text-white' : 'bg-danger text-white' }}">
                                        {{ auth('admin')->user()->is_active ? 'Actif' : 'Inactif' }}
                                    </span>
                                </div>

                                <!-- Logout -->
                                <form method="POST" action="{{ route('admin.logout') }}">
                                    @csrf
                                    <button type="submit" class="btn btn-outline-secondary btn-sm d-flex align-items-center">
                                        <i class="fas fa-sign-out-alt me-2"></i>
                                        Déconnexion
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </header>

                <!-- Page content -->
                <main class="container-fluid">
                    <!-- Flash Messages -->
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert" x-data="{ show: true }" x-show="show">
                            <i class="fas fa-check-circle me-2"></i>
                            {{ session('success') }}
                            <button type="button" class="btn-close" @click="show = false" aria-label="Close"></button>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert" x-data="{ show: true }" x-show="show">
                            <i class="fas fa-exclamation-circle me-2"></i>
                            {{ session('error') }}
                            <button type="button" class="btn-close" @click="show = false" aria-label="Close"></button>
                        </div>
                    @endif

                    @if(session('warning'))
                        <div class="alert alert-warning alert-dismissible fade show" role="alert" x-data="{ show: true }" x-show="show">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            {{ session('warning') }}
                            <button type="button" class="btn-close" @click="show = false" aria-label="Close"></button>
                        </div>
                    @endif

                    @if(session('info'))
                        <div class="alert alert-info alert-dismissible fade show" role="alert" x-data="{ show: true }" x-show="show">
                            <i class="fas fa-info-circle me-2"></i>
                            {{ session('info') }}
                            <button type="button" class="btn-close" @click="show = false" aria-label="Close"></button>
                        </div>
                    @endif

                    @yield('content')
                </main>
            </div>
        </div>
    </div>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- Toastr JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

    <script>
        // Configuration Toastr
        toastr.options = {
            "closeButton": true,
            "debug": false,
            "newestOnTop": true,
            "progressBar": true,
            "positionClass": "toast-top-right",
            "preventDuplicates": false,
            "onclick": null,
            "showDuration": "300",
            "hideDuration": "1000",
            "timeOut": "5000",
            "extendedTimeOut": "1000",
            "showEasing": "swing",
            "hideEasing": "linear",
            "showMethod": "fadeIn",
            "hideMethod": "fadeOut"
        };

        // Auto-hide alerts après 5 secondes
        setTimeout(function() {
            $('.alert').fadeOut('slow');
        }, 5000);

        // Fermer la sidebar en cliquant en dehors (mobile)
        $(document).ready(function() {
            // Smooth transitions pour les chevrons
            $('.nav-link').on('click', function() {
                const chevron = $(this).find('.fa-chevron-down');
                if (chevron.length) {
                    chevron.toggleClass('rotate-180');
                }
            });
        });
    </script>

    @stack('scripts')
</body>
</html>
