<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Gestion des Commandes') - {{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <style>
        [x-cloak] { display: none !important; }
        body { 
            font-family: 'Poppins', sans-serif;
            background-color: #f9fafb;
        }
        .sidebar-icon {
            @apply w-6 h-6 text-center transition-all duration-200;
        }
        .sidebar-text {
            @apply ml-3 text-sm font-medium transition-all duration-200;
        }
        .nav-item {
            @apply flex items-center p-3 rounded-lg transition-all duration-200;
        }
        .nav-item-active {
            @apply bg-indigo-50 text-indigo-600;
        }
        .nav-item-inactive {
            @apply text-gray-600 hover:bg-indigo-50 hover:text-indigo-600;
        }
        .submenu {
            @apply pl-10 mt-1 space-y-1;
        }
        .submenu-item {
            @apply flex items-center p-2 text-xs rounded-lg transition-all duration-200;
        }
        .submenu-item-active {
            @apply bg-indigo-50 text-indigo-600;
        }
        .submenu-item-inactive {
            @apply text-gray-500 hover:bg-indigo-50 hover:text-indigo-600;
        }
    </style>
    
    @yield('css')
</head>
<body class="antialiased min-h-screen bg-gray-50 text-gray-800">
    <div class="flex h-screen overflow-hidden" x-data="{ sidebarOpen: false }">
        <!-- Sidebar -->
        <div x-cloak 
             :class="{'translate-x-0 shadow-lg': sidebarOpen, '-translate-x-full': !sidebarOpen}" 
             class="fixed inset-y-0 left-0 z-30 w-72 overflow-y-auto bg-white border-r border-gray-100 
                   transition-all duration-300 ease-in-out lg:translate-x-0 lg:static lg:shadow-none">
            
            <!-- Logo -->
            <div class="flex items-center justify-between h-16 px-6 border-b border-gray-100">
                <a href="#" class="text-xl font-bold text-indigo-600 flex items-center space-x-2">
                    <i class="fas fa-box-open"></i>
                    <span>OrderMgr</span>
                </a>
                <button @click="sidebarOpen = false" class="p-2 rounded-md text-gray-400 hover:text-gray-600 lg:hidden">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <!-- Navigation -->
            <nav class="px-4 py-6">
                <ul>
                    <!-- Dashboard -->
                    <li class="mb-2">
                        <a href="{{ route('admin.dashboard') }}" 
                           class="nav-item {{ request()->routeIs('admin.dashboard') ? 'nav-item-active' : 'nav-item-inactive' }}">
                            <i class="fas fa-tachometer-alt sidebar-icon"></i>
                            <span class="sidebar-text">Tableau de bord</span>
                        </a>
                    </li>
                    
                    <!-- Products with submenu -->
                    <li class="mb-2" x-data="{ open: {{ request()->routeIs('admin.products.*') ? 'true' : 'false' }} }">
                        <div @click="open = !open" 
                             class="nav-item {{ request()->routeIs('admin.products.*') ? 'nav-item-active' : 'nav-item-inactive' }} cursor-pointer">
                            <i class="fas fa-box sidebar-icon"></i>
                            <span class="sidebar-text">Produits</span>
                            <i class="fas fa-chevron-down ml-auto transform transition-transform duration-200"
                               :class="{'rotate-180': open}"></i>
                        </div>
                        <ul x-show="open" 
                            x-transition:enter="transition ease-out duration-200" 
                            x-transition:enter-start="opacity-0 transform -translate-y-2" 
                            x-transition:enter-end="opacity-100 transform translate-y-0"
                            class="submenu" x-cloak>
                            <li>
                                <a href="{{ route('admin.products.index') }}" 
                                   class="submenu-item {{ request()->routeIs('admin.products.index') ? 'submenu-item-active' : 'submenu-item-inactive' }}">
                                    <i class="fas fa-list sidebar-icon text-xs"></i>
                                    <span class="sidebar-text">Liste des produits</span>
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('admin.products.create') }}" 
                                   class="submenu-item {{ request()->routeIs('admin.products.create') ? 'submenu-item-active' : 'submenu-item-inactive' }}">
                                    <i class="fas fa-plus sidebar-icon text-xs"></i>
                                    <span class="sidebar-text">Ajouter un produit</span>
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('admin.products.categories') }}" 
                                   class="submenu-item {{ request()->routeIs('admin.products.categories') ? 'submenu-item-active' : 'submenu-item-inactive' }}">
                                    <i class="fas fa-tags sidebar-icon text-xs"></i>
                                    <span class="sidebar-text">Catégories</span>
                                </a>
                            </li>
                        </ul>
                    </li>
                    
                    <!-- Orders with submenu -->
                    <li class="mb-2" x-data="{ open: {{ request()->routeIs('admin.orders.*') ? 'true' : 'false' }} }">
                        <div @click="open = !open" 
                             class="nav-item {{ request()->routeIs('admin.orders.*') ? 'nav-item-active' : 'nav-item-inactive' }} cursor-pointer">
                            <i class="fas fa-shopping-cart sidebar-icon"></i>
                            <span class="sidebar-text">Commandes</span>
                            <i class="fas fa-chevron-down ml-auto transform transition-transform duration-200"
                               :class="{'rotate-180': open}"></i>
                        </div>
                        <ul x-show="open" 
                            x-transition:enter="transition ease-out duration-200" 
                            x-transition:enter-start="opacity-0 transform -translate-y-2" 
                            x-transition:enter-end="opacity-100 transform translate-y-0"
                            class="submenu" x-cloak>
                            <li>
                                <a href="{{ route('admin.orders.index') }}" 
                                   class="submenu-item {{ request()->routeIs('admin.orders.index') ? 'submenu-item-active' : 'submenu-item-inactive' }}">
                                    <i class="fas fa-list sidebar-icon text-xs"></i>
                                    <span class="sidebar-text">Toutes les commandes</span>
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('admin.orders.pending') }}" 
                                   class="submenu-item {{ request()->routeIs('admin.orders.pending') ? 'submenu-item-active' : 'submenu-item-inactive' }}">
                                    <i class="fas fa-clock sidebar-icon text-xs"></i>
                                    <span class="sidebar-text">Commandes en attente</span>
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('admin.orders.processing') }}" 
                                   class="submenu-item {{ request()->routeIs('admin.orders.processing') ? 'submenu-item-active' : 'submenu-item-inactive' }}">
                                    <i class="fas fa-spinner sidebar-icon text-xs"></i>
                                    <span class="sidebar-text">En traitement</span>
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('admin.orders.completed') }}" 
                                   class="submenu-item {{ request()->routeIs('admin.orders.completed') ? 'submenu-item-active' : 'submenu-item-inactive' }}">
                                    <i class="fas fa-check sidebar-icon text-xs"></i>
                                    <span class="sidebar-text">Commandes livrées</span>
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('admin.orders.cancelled') }}" 
                                   class="submenu-item {{ request()->routeIs('admin.orders.cancelled') ? 'submenu-item-active' : 'submenu-item-inactive' }}">
                                    <i class="fas fa-times sidebar-icon text-xs"></i>
                                    <span class="sidebar-text">Commandes annulées</span>
                                </a>
                            </li>
                        </ul>
                    </li>
                    
                    <!-- Customers -->
                    <li class="mb-2" x-data="{ open: {{ request()->routeIs('admin.customers.*') ? 'true' : 'false' }} }">
                        <div @click="open = !open" 
                             class="nav-item {{ request()->routeIs('admin.customers.*') ? 'nav-item-active' : 'nav-item-inactive' }} cursor-pointer">
                            <i class="fas fa-users sidebar-icon"></i>
                            <span class="sidebar-text">Clients</span>
                            <i class="fas fa-chevron-down ml-auto transform transition-transform duration-200"
                               :class="{'rotate-180': open}"></i>
                        </div>
                        <ul x-show="open" 
                            x-transition:enter="transition ease-out duration-200" 
                            x-transition:enter-start="opacity-0 transform -translate-y-2" 
                            x-transition:enter-end="opacity-100 transform translate-y-0"
                            class="submenu" x-cloak>
                            <li>
                                <a href="{{ route('admin.customers.index') }}" 
                                   class="submenu-item {{ request()->routeIs('admin.customers.index') ? 'submenu-item-active' : 'submenu-item-inactive' }}">
                                    <i class="fas fa-list sidebar-icon text-xs"></i>
                                    <span class="sidebar-text">Liste des clients</span>
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('admin.customers.create') }}" 
                                   class="submenu-item {{ request()->routeIs('admin.customers.create') ? 'submenu-item-active' : 'submenu-item-inactive' }}">
                                    <i class="fas fa-user-plus sidebar-icon text-xs"></i>
                                    <span class="sidebar-text">Ajouter un client</span>
                                </a>
                            </li>
                        </ul>
                    </li>
                    
                    <!-- Analytics -->
                    <li class="mb-2">
                        <a href="{{ route('admin.analytics') }}" 
                           class="nav-item {{ request()->routeIs('admin.analytics') ? 'nav-item-active' : 'nav-item-inactive' }}">
                            <i class="fas fa-chart-line sidebar-icon"></i>
                            <span class="sidebar-text">Analytiques</span>
                        </a>
                    </li>
                </ul>
                
                <!-- Settings Section -->
                <div class="mt-8">
                    <p class="px-4 text-xs font-semibold text-gray-400 uppercase tracking-wider">Paramètres</p>
                    <ul class="mt-3">
                        <!-- Settings with submenu -->
                        <li class="mb-2" x-data="{ open: {{ request()->routeIs('admin.settings.*') ? 'true' : 'false' }} }">
                            <div @click="open = !open" 
                                 class="nav-item {{ request()->routeIs('admin.settings.*') ? 'nav-item-active' : 'nav-item-inactive' }} cursor-pointer">
                                <i class="fas fa-cog sidebar-icon"></i>
                                <span class="sidebar-text">Paramètres</span>
                                <i class="fas fa-chevron-down ml-auto transform transition-transform duration-200"
                                   :class="{'rotate-180': open}"></i>
                            </div>
                            <ul x-show="open" 
                                x-transition:enter="transition ease-out duration-200" 
                                x-transition:enter-start="opacity-0 transform -translate-y-2" 
                                x-transition:enter-end="opacity-100 transform translate-y-0"
                                class="submenu" x-cloak>
                                <li>
                                    <a href="{{ route('admin.settings.general') }}" 
                                       class="submenu-item {{ request()->routeIs('admin.settings.general') ? 'submenu-item-active' : 'submenu-item-inactive' }}">
                                        <i class="fas fa-sliders-h sidebar-icon text-xs"></i>
                                        <span class="sidebar-text">Généraux</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('admin.settings.users') }}" 
                                       class="submenu-item {{ request()->routeIs('admin.settings.users') ? 'submenu-item-active' : 'submenu-item-inactive' }}">
                                        <i class="fas fa-users-cog sidebar-icon text-xs"></i>
                                        <span class="sidebar-text">Utilisateurs</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('admin.settings.appearance') }}" 
                                       class="submenu-item {{ request()->routeIs('admin.settings.appearance') ? 'submenu-item-active' : 'submenu-item-inactive' }}">
                                        <i class="fas fa-paint-brush sidebar-icon text-xs"></i>
                                        <span class="sidebar-text">Apparence</span>
                                    </a>
                                </li>
                            </ul>
                        </li>
                        
                        <!-- Profile -->
                        <li class="mb-2">
                            <a href="{{ route('admin.profile') }}" 
                               class="nav-item {{ request()->routeIs('admin.profile') ? 'nav-item-active' : 'nav-item-inactive' }}">
                                <i class="fas fa-user sidebar-icon"></i>
                                <span class="sidebar-text">Profil</span>
                            </a>
                        </li>
                        
                        <!-- Logout -->
                        <li>
                            <form method="POST" action="{{ route('admin.logout') }}" class="w-full">
                                @csrf
                                <button type="submit" class="w-full nav-item nav-item-inactive text-left">
                                    <i class="fas fa-sign-out-alt sidebar-icon"></i>
                                    <span class="sidebar-text">Déconnexion</span>
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
            </nav>
        </div>

        <!-- Main content -->
        <div class="relative flex flex-col flex-1 overflow-y-auto overflow-x-hidden">
            <!-- Top navigation -->
            <header class="sticky top-0 bg-white border-b border-gray-100 z-30">
                <div class="px-4 sm:px-6">
                    <div class="flex items-center justify-between h-16">
                        <div class="flex items-center">
                            <!-- Mobile menu button -->
                            <button @click="sidebarOpen = true" class="p-2 rounded-md text-gray-400 hover:text-gray-600 lg:hidden">
                                <i class="fas fa-bars"></i>
                            </button>
                            
                            <!-- Page Title -->
                            <h1 class="text-xl font-semibold text-gray-800 ml-4 hidden sm:block">
                                @yield('page-heading', 'Tableau de bord')
                            </h1>
                        </div>
                        
                        <!-- Search Bar -->
                        <div class="hidden md:block flex-1 max-w-md mx-4">
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-search text-gray-400"></i>
                                </div>
                                <input type="text" class="block w-full pl-10 pr-3 py-2 border border-gray-200 rounded-lg 
                                             focus:ring-2 focus:ring-indigo-100 focus:border-indigo-300 
                                             text-sm bg-gray-50" placeholder="Rechercher...">
                            </div>
                        </div>
                        
                        <div class="flex items-center space-x-3">
                            <!-- Notifications -->
                            <div class="relative" x-data="{ isOpen: false }">
                                <button @click="isOpen = !isOpen" 
                                        class="p-2 text-gray-500 rounded-full hover:text-indigo-600 hover:bg-indigo-50 transition-colors duration-200">
                                    <i class="fas fa-bell"></i>
                                </button>
                                
                                <!-- Notification Dropdown -->
                                <div x-show="isOpen" 
                                     @click.away="isOpen = false"
                                     x-transition:enter="transition ease-out duration-100"
                                     x-transition:enter-start="transform opacity-0 scale-95"
                                     x-transition:enter-end="transform opacity-100 scale-100"
                                     x-transition:leave="transition ease-in duration-75"
                                     x-transition:leave-start="transform opacity-100 scale-100"
                                     x-transition:leave-end="transform opacity-0 scale-95"
                                     class="absolute right-0 w-72 mt-2 bg-white rounded-lg shadow-lg border border-gray-100 z-50"
                                     x-cloak>
                                    <div class="p-2 border-b border-gray-100">
                                        <p class="text-sm font-medium text-gray-700">Notifications</p>
                                    </div>
                                    <div class="max-h-64 overflow-y-auto p-2">
                                        <p class="text-sm text-gray-500 p-4 text-center">Aucune notification pour le moment</p>
                                    </div>
                                    <div class="p-2 border-t border-gray-100">
                                        <a href="#" class="block text-center text-xs text-indigo-600 hover:text-indigo-700">
                                            Voir toutes les notifications
                                        </a>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Profile Dropdown -->
                            <div class="relative" x-data="{ isOpen: false }">
                                <button @click="isOpen = !isOpen" class="flex items-center text-sm rounded-full focus:outline-none">
                                    <span class="hidden md:inline-block mr-2 font-medium text-gray-700">
                                        {{ Auth::guard('admin')->user()->name }}
                                    </span>
                                    <div class="w-8 h-8 rounded-full bg-indigo-600 text-white flex items-center justify-center">
                                        {{ substr(Auth::guard('admin')->user()->name, 0, 1) }}
                                    </div>
                                </button>
                                
                                <div x-show="isOpen" 
                                     @click.away="isOpen = false"
                                     x-transition:enter="transition ease-out duration-100"
                                     x-transition:enter-start="transform opacity-0 scale-95"
                                     x-transition:enter-end="transform opacity-100 scale-100"
                                     x-transition:leave="transition ease-in duration-75"
                                     x-transition:leave-start="transform opacity-100 scale-100"
                                     x-transition:leave-end="transform opacity-0 scale-95"
                                     class="absolute right-0 z-50 mt-2 w-48 rounded-lg shadow-lg bg-white ring-1 ring-black ring-opacity-5"
                                     x-cloak>
                                    <div class="py-1">
                                        <a href="{{ route('admin.profile') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-indigo-50 hover:text-indigo-600">
                                            <i class="fas fa-user-circle mr-2 text-gray-400"></i>
                                            Profil
                                        </a>
                                        <a href="{{ route('admin.settings.general') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-indigo-50 hover:text-indigo-600">
                                            <i class="fas fa-cog mr-2 text-gray-400"></i>
                                            Paramètres
                                        </a>
                                        <div class="border-t border-gray-100 my-1"></div>
                                        <form method="POST" action="{{ route('admin.logout') }}">
                                            @csrf
                                            <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-indigo-50 hover:text-indigo-600">
                                                <i class="fas fa-sign-out-alt mr-2 text-gray-400"></i>
                                                Déconnexion
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </header>
            
            <!-- Page content -->
            <main class="flex-1 p-4 sm:p-6 lg:p-8">
                <!-- Breadcrumbs -->
                <div class="pb-4 hidden sm:block">
                    <nav class="flex" aria-label="Breadcrumb">
                        <ol class="inline-flex items-center space-x-1 md:space-x-3">
                            <li class="inline-flex items-center">
                                <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center text-sm font-medium text-gray-500 hover:text-indigo-600">
                                    <i class="fas fa-home mr-2"></i>
                                    Accueil
                                </a>
                            </li>
                            @yield('breadcrumbs')
                        </ol>
                    </nav>
                </div>
                
                <!-- Mobile page title (visible on small screens) -->
                <h1 class="text-xl font-semibold text-gray-800 mb-4 sm:hidden">
                    @yield('page-heading', 'Tableau de bord')
                </h1>
                
                <!-- Session Status -->
                @if (session('success'))
                    <div x-data="{ show: true }" 
                         x-show="show" 
                         x-init="setTimeout(() => show = false, 5000)" 
                         class="mb-6 bg-green-50 border-l-4 border-green-400 text-green-700 p-4 rounded-md" 
                         role="alert">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <i class="fas fa-check-circle text-green-400"></i>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium">{{ session('success') }}</p>
                            </div>
                            <div class="ml-auto pl-3">
                                <div class="-mx-1.5 -my-1.5">
                                    <button @click="show = false" class="inline-flex text-green-500 hover:text-green-600">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
                
                @if (session('error'))
                    <div x-data="{ show: true }" 
                         x-show="show" 
                         x-init="setTimeout(() => show = false, 5000)" 
                         class="mb-6 bg-red-50 border-l-4 border-red-400 text-red-700 p-4 rounded-md" 
                         role="alert">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <i class="fas fa-exclamation-circle text-red-400"></i>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium">{{ session('error') }}</p>
                            </div>
                            <div class="ml-auto pl-3">
                                <div class="-mx-1.5 -my-1.5">
                                    <button @click="show = false" class="inline-flex text-red-500 hover:text-red-600">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
                
                <!-- Content -->
                <div class="bg-white rounded-lg shadow-sm p-4 sm:p-6 mb-6">
                    @yield('content')
                </div>
                
                <!-- Footer -->
                <footer class="mt-auto py-4">
                    <div class="text-center text-xs text-gray-500">
                        <p>&copy; {{ date('Y') }} OrderMgr. Tous droits réservés.</p>
                    </div>
                </footer>
            </main>
        </div>
    </div>
    
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    @yield('js')
</body>
</html>