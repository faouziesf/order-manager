<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <title>@yield('title', 'Admin Panel') - {{ config('app.name', 'Order Manager') }}</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />
    
    <!-- Scripts -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    
    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/heroicons/2.0.18/outline/heroicons-outline.min.css">
    
    <style>
        body { font-family: 'Inter', sans-serif; }
        .sidebar-transition { transition: all 0.3s ease-in-out; }
    </style>
    
    @stack('styles')
</head>
<body class="bg-gray-50 font-sans antialiased" x-data="{ sidebarOpen: false }">
    <div class="min-h-screen flex">
        <!-- Sidebar -->
        <div class="sidebar-transition" 
             :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
             class="fixed inset-y-0 left-0 z-50 w-64 bg-white shadow-lg transform lg:translate-x-0 lg:static lg:inset-0">
            
            <div class="flex items-center justify-center h-16 px-4 bg-gradient-to-r from-blue-600 to-purple-600">
                <h1 class="text-xl font-bold text-white">{{ auth('admin')->user()->shop_name ?? 'Admin Panel' }}</h1>
            </div>
            
            <nav class="mt-8">
                <div class="px-4 space-y-2">
                    <!-- Dashboard -->
                    <a href="{{ route('admin.dashboard') }}" 
                       class="flex items-center px-4 py-3 text-gray-700 rounded-lg hover:bg-blue-50 hover:text-blue-700 transition-colors {{ request()->routeIs('admin.dashboard') ? 'bg-blue-50 text-blue-700' : '' }}">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                        </svg>
                        Dashboard
                    </a>
                    
                    <!-- Gestion des utilisateurs -->
                    <div x-data="{ open: {{ request()->routeIs('admin.managers.*') || request()->routeIs('admin.employees.*') ? 'true' : 'false' }} }">
                        <button @click="open = !open" 
                                class="flex items-center justify-between w-full px-4 py-3 text-gray-700 rounded-lg hover:bg-blue-50 hover:text-blue-700 transition-colors {{ request()->routeIs('admin.managers.*') || request()->routeIs('admin.employees.*') ? 'bg-blue-50 text-blue-700' : '' }}">
                            <div class="flex items-center">
                                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                                </svg>
                                Gestion Utilisateurs
                            </div>
                            <svg class="w-4 h-4 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                        
                        <div x-show="open" x-transition class="ml-4 mt-2 space-y-1">
                            <a href="{{ route('admin.managers.index') }}" 
                               class="flex items-center px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-gray-100 hover:text-gray-900 transition-colors {{ request()->routeIs('admin.managers.*') ? 'bg-gray-100 text-gray-900' : '' }}">
                                <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                                Managers
                                <span class="ml-auto bg-blue-100 text-blue-600 text-xs px-2 py-1 rounded-full">
                                    {{ auth('admin')->user()->managers()->count() }}/{{ auth('admin')->user()->max_managers }}
                                </span>
                            </a>
                            
                            <a href="{{ route('admin.employees.index') }}" 
                               class="flex items-center px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-gray-100 hover:text-gray-900 transition-colors {{ request()->routeIs('admin.employees.*') ? 'bg-gray-100 text-gray-900' : '' }}">
                                <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                </svg>
                                Employés
                                <span class="ml-auto bg-green-100 text-green-600 text-xs px-2 py-1 rounded-full">
                                    {{ auth('admin')->user()->employees()->count() }}/{{ auth('admin')->user()->max_employees }}
                                </span>
                            </a>
                        </div>
                    </div>
                    
                    <!-- Autres liens existants -->
                    <a href="{{ route('admin.products.index') }}" 
                       class="flex items-center px-4 py-3 text-gray-700 rounded-lg hover:bg-blue-50 hover:text-blue-700 transition-colors {{ request()->routeIs('admin.products.*') ? 'bg-blue-50 text-blue-700' : '' }}">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                        </svg>
                        Produits
                    </a>
                    
                    <a href="{{ route('admin.orders.index') }}" 
                       class="flex items-center px-4 py-3 text-gray-700 rounded-lg hover:bg-blue-50 hover:text-blue-700 transition-colors {{ request()->routeIs('admin.orders.*') ? 'bg-blue-50 text-blue-700' : '' }}">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        Commandes
                    </a>
                </div>
            </nav>
            
            <!-- Profile section -->
            <div class="absolute bottom-0 w-full p-4 border-t">
                <div class="flex items-center space-x-3">
                    <div class="w-8 h-8 bg-gradient-to-r from-blue-500 to-purple-600 rounded-full flex items-center justify-center">
                        <span class="text-white text-sm font-semibold">{{ substr(auth('admin')->user()->name, 0, 1) }}</span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-900 truncate">{{ auth('admin')->user()->name }}</p>
                        <p class="text-xs text-gray-500 truncate">{{ auth('admin')->user()->email }}</p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Main content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Top navigation -->
            <header class="bg-white shadow-sm border-b">
                <div class="flex items-center justify-between px-6 py-4">
                    <div class="flex items-center">
                        <button @click="sidebarOpen = !sidebarOpen" class="lg:hidden p-2 rounded-md text-gray-600 hover:text-gray-900 hover:bg-gray-100">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                            </svg>
                        </button>
                        
                        <div class="ml-4 lg:ml-0">
                            <h1 class="text-2xl font-semibold text-gray-900">@yield('page-title', 'Dashboard')</h1>
                            @hasSection('breadcrumb')
                                <nav class="flex mt-1" aria-label="Breadcrumb">
                                    <ol class="flex items-center space-x-2 text-sm text-gray-500">
                                        @yield('breadcrumb')
                                    </ol>
                                </nav>
                            @endhasSection
                        </div>
                    </div>
                    
                    <div class="flex items-center space-x-4">
                        <!-- Account Status -->
                        <div class="flex items-center space-x-2 text-sm">
                            @if(auth('admin')->user()->expiry_date)
                                <span class="px-2 py-1 {{ auth('admin')->user()->expiry_date->isPast() ? 'bg-red-100 text-red-700' : (auth('admin')->user()->expiry_date->diffInDays() <= 7 ? 'bg-yellow-100 text-yellow-700' : 'bg-green-100 text-green-700') }} rounded-full">
                                    Expire: {{ auth('admin')->user()->expiry_date->format('d/m/Y') }}
                                </span>
                            @endif
                            
                            <span class="px-2 py-1 {{ auth('admin')->user()->is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }} rounded-full">
                                {{ auth('admin')->user()->is_active ? 'Actif' : 'Inactif' }}
                            </span>
                        </div>
                        
                        <!-- Logout -->
                        <form method="POST" action="{{ route('admin.logout') }}">
                            @csrf
                            <button type="submit" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:text-gray-900 hover:bg-gray-100 rounded-lg transition-colors">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                                </svg>
                                Déconnexion
                            </button>
                        </form>
                    </div>
                </div>
            </header>
            
            <!-- Page content -->
            <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50">
                <div class="container mx-auto px-6 py-8">
                    <!-- Flash Messages -->
                    @if(session('success'))
                        <div class="mb-6 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg" x-data="{ show: true }" x-show="show">
                            <div class="flex justify-between items-center">
                                <span>{{ session('success') }}</span>
                                <button @click="show = false" class="text-green-500 hover:text-green-700">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    @endif
                    
                    @if(session('error'))
                        <div class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg" x-data="{ show: true }" x-show="show">
                            <div class="flex justify-between items-center">
                                <span>{{ session('error') }}</span>
                                <button @click="show = false" class="text-red-500 hover:text-red-700">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    @endif
                    
                    @yield('content')
                </div>
            </main>
        </div>
    </div>
    
    <!-- Overlay for mobile sidebar -->
    <div x-show="sidebarOpen" 
         @click="sidebarOpen = false"
         x-transition:enter="transition-opacity ease-linear duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition-opacity ease-linear duration-300"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-40 bg-black bg-opacity-25 lg:hidden"></div>
    
    @stack('scripts')
</body>
</html>