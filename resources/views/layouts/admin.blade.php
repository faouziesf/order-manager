<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') - Order Manager</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Animate.css -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    
    <!-- Custom CSS -->
    <style>
        :root {
            --primary-color: #5a6acf;
            --primary-light: #eaecf8;
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
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background-color: var(--body-bg);
            color: var(--text-color);
            overflow-x: hidden;
            transition: opacity 0.3s ease-in-out;
        }
        
        /* Preloader */
        .preloader {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: #ffffff;
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 9999;
            transition: opacity 0.5s ease, visibility 0.5s ease;
        }
        
        .preloader.hidden {
            opacity: 0;
            visibility: hidden;
        }
        
        .preloader-content {
            text-align: center;
            transform: translateY(0);
            transition: transform 0.5s ease-in-out;
        }
        
        .preloader.hidden .preloader-content {
            transform: translateY(-50px);
        }
        
        .preloader img {
            width: 120px;
            margin-bottom: 20px;
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }
        
        /* Sidebar */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            bottom: 0;
            width: var(--sidebar-width);
            background-color: var(--card-bg);
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.05);
            z-index: 100;
            transition: all 0.3s ease;
            padding-top: var(--header-height);
        }
        
        .sidebar-collapsed {
            width: var(--sidebar-collapsed-width);
        }
        
        .sidebar-brand {
            position: fixed;
            top: 0;
            left: 0;
            height: var(--header-height);
            width: var(--sidebar-width);
            background-color: var(--primary-color);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
            z-index: 101;
        }
        
        .sidebar-collapsed .sidebar-brand {
            width: var(--sidebar-collapsed-width);
        }
        
        .sidebar-brand h3 {
            margin: 0;
            font-size: 1.2rem;
            font-weight: 600;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        .sidebar-menu {
            list-style: none;
            padding: 0;
            margin: 15px 0;
            overflow-y: auto;
            height: calc(100vh - var(--header-height) - 20px);
        }
        
        .sidebar-item {
            margin-bottom: 5px;
        }
        
        .sidebar-link {
            display: flex;
            align-items: center;
            padding: 12px 15px;
            color: var(--text-color);
            text-decoration: none;
            border-radius: 5px;
            margin: 0 10px;
            transition: all 0.3s;
            white-space: nowrap;
            overflow: hidden;
        }
        
        .sidebar-link:hover {
            background-color: var(--primary-light);
            color: var(--primary-color);
        }
        
        .sidebar-link.active {
            background-color: var(--primary-light);
            color: var(--primary-color);
            font-weight: 500;
        }
        
        .sidebar-icon {
            min-width: 30px;
            text-align: center;
            font-size: 1.1rem;
            margin-right: 10px;
            transition: margin 0.3s;
        }
        
        .sidebar-collapsed .sidebar-icon {
            margin-right: 0;
        }
        
        .sidebar-text {
            opacity: 1;
            transition: opacity 0.3s;
        }
        
        .sidebar-collapsed .sidebar-text {
            opacity: 0;
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
        
        .sidebar-submenu-item {
            margin: 5px 0;
        }
        
        .sidebar-submenu-link {
            display: block;
            color: var(--text-color);
            text-decoration: none;
            padding: 8px 15px 8px 55px;
            font-size: 0.9rem;
            transition: all 0.3s;
            white-space: nowrap;
            overflow: hidden;
            border-radius: 5px;
            margin: 0 10px;
        }
        
        .sidebar-submenu-link:hover {
            background-color: var(--primary-light);
            color: var(--primary-color);
        }
        
        .sidebar-submenu-link.active {
            background-color: var(--primary-light);
            color: var(--primary-color);
            font-weight: 500;
        }
        
        .has-submenu::after {
            content: "\f107";
            font-family: "Font Awesome 5 Free";
            font-weight: 900;
            position: absolute;
            right: 20px;
            top: 50%;
            transform: translateY(-50%);
            transition: transform 0.3s;
        }
        
        .has-submenu.active::after {
            transform: translateY(-50%) rotate(180deg);
        }
        
        /* Content */
        .content {
            margin-left: var(--sidebar-width);
            padding: 80px 20px 20px;
            min-height: 100vh;
            transition: all 0.3s ease;
        }
        
        .content-expanded {
            margin-left: var(--sidebar-collapsed-width);
        }
        
        /* Header Navbar */
        .header-navbar {
            position: fixed;
            top: 0;
            right: 0;
            left: var(--sidebar-width);
            height: var(--header-height);
            background-color: var(--card-bg);
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.05);
            transition: left 0.3s ease;
            z-index: 99;
            padding: 0 20px;
            display: flex;
            align-items: center;
        }
        
        .content-expanded + .header-navbar {
            left: var(--sidebar-collapsed-width);
        }
        
        .sidebar-toggle {
            background: none;
            border: none;
            color: var(--text-color);
            font-size: 1.2rem;
            cursor: pointer;
            padding: 5px;
            transition: all 0.3s;
            width: 40px;
            height: 40px;
            border-radius: 50%;
        }
        
        .sidebar-toggle:hover {
            background-color: var(--primary-light);
            color: var(--primary-color);
        }
        
        /* Card */
        .card {
            border: 1px solid var(--card-border);
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.03);
            margin-bottom: 20px;
            background-color: var(--card-bg);
            overflow: hidden;
        }
        
        .card-header {
            background-color: var(--card-bg);
            border-bottom: 1px solid var(--card-border);
            padding: 1rem;
        }
        
        .card-title {
            margin-bottom: 0;
            color: var(--dark-color);
            font-weight: 600;
            font-size: 1rem;
        }
        
        .card-body {
            padding: 1rem;
        }
        
        /* Form controls */
        .form-control, .form-select {
            border-radius: 5px;
            border: 1px solid var(--card-border);
            padding: 0.5rem 0.75rem;
            font-size: 0.9rem;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.25rem rgba(90, 106, 207, 0.25);
        }
        
        /* Buttons */
        .btn {
            border-radius: 5px;
            font-size: 0.9rem;
            padding: 0.5rem 1rem;
            transition: all 0.2s;
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .btn-primary:hover {
            background-color: rgba(90, 106, 207, 0.9);
            border-color: rgba(90, 106, 207, 0.9);
        }
        
        .btn-outline-primary {
            color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .btn-outline-primary:hover {
            background-color: var(--primary-color);
            color: white;
        }
        
        /* Tables */
        .table {
            margin-bottom: 0;
        }
        
        .table th {
            font-weight: 600;
            color: var(--dark-color);
            border-top: none;
        }
        
        .table td {
            vertical-align: middle;
        }
        
        /* Badges */
        .badge {
            padding: 0.4em 0.6em;
            font-weight: 500;
        }
        
        /* Footer */
        .footer {
            text-align: center;
            padding: 1rem;
            color: var(--text-color);
            font-size: 0.9rem;
            margin-top: 20px;
        }
        
        /* Responsive */
        @media (max-width: 992px) {
            .sidebar {
                transform: translateX(-100%);
            }
            
            .sidebar.show {
                transform: translateX(0);
            }
            
            .content, .header-navbar {
                margin-left: 0 !important;
                left: 0 !important;
            }
            
            .sidebar-brand {
                width: 100%;
            }
        }
        
        /* Animation classes */
        .fade-in {
            animation: fadeIn 0.5s;
        }
        
        .slide-in-left {
            animation: slideInLeft 0.5s;
        }
        
        .slide-in-right {
            animation: slideInRight 0.5s;
        }
        
        .slide-up {
            animation: slideUp 0.5s;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        @keyframes slideInLeft {
            from { transform: translateX(-50px); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        
        @keyframes slideInRight {
            from { transform: translateX(50px); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        
        @keyframes slideUp {
            from { transform: translateY(20px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
    </style>
    
    @yield('css')
</head>
<body>
    <!-- Preloader -->
    <div class="preloader">
        <div class="preloader-content">
            <!-- Vous pouvez remplacer ceci par votre logo -->
            <div class="text-primary mb-2" style="font-size: 40px;">
                <i class="fas fa-shopping-cart"></i>
            </div>
            <h2 class="text-primary">Order Manager</h2>
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Chargement...</span>
            </div>
        </div>
    </div>

    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <div class="sidebar-brand">
            <h3>Order Manager</h3>
        </div>
        
        <ul class="sidebar-menu">
            <li class="sidebar-item">
                <a href="{{ route('admin.dashboard') }}" class="sidebar-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    <div class="sidebar-icon">
                        <i class="fas fa-tachometer-alt"></i>
                    </div>
                    <span class="sidebar-text">Tableau de bord</span>
                </a>
            </li>
            
            <li class="sidebar-item">
                <a href="#" class="sidebar-link has-submenu {{ request()->routeIs('admin.products*') ? 'active' : '' }}" id="productsMenu">
                    <div class="sidebar-icon">
                        <i class="fas fa-box-open"></i>
                    </div>
                    <span class="sidebar-text">Produits</span>
                </a>
                <ul class="sidebar-submenu {{ request()->routeIs('admin.products*') ? 'show' : '' }}">
                    <li class="sidebar-submenu-item">
                        <a href="{{ route('admin.products.index') }}" class="sidebar-submenu-link {{ request()->routeIs('admin.products.index') ? 'active' : '' }}">
                            Liste des produits
                        </a>
                    </li>
                    <li class="sidebar-submenu-item">
                        <a href="{{ route('admin.products.create') }}" class="sidebar-submenu-link {{ request()->routeIs('admin.products.create') ? 'active' : '' }}">
                            Ajouter un produit
                        </a>
                    </li>
                </ul>
            </li>
            
            <!-- Autres éléments du menu à ajouter plus tard -->
        </ul>
    </div>
    
    <!-- Content -->
    <div class="content" id="content">
        <!-- Header Navbar -->
        <nav class="header-navbar">
            <button class="sidebar-toggle" id="sidebarToggle">
                <i class="fas fa-bars"></i>
            </button>
            
            <div class="ms-auto">
                <div class="dropdown">
                    <a class="btn dropdown-toggle" href="#" role="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
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
        
        <!-- Page Content -->
        <div class="container-fluid fade-in">
            <!-- Les messages flash ne sont affichés qu'ici, pas dans les vues enfants -->
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show my-3" role="alert">
                    <i class="fas fa-check-circle me-2"></i>
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show my-3" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            
            @yield('content')
        </div>
        
        <!-- Footer -->
        <footer class="footer">
            <div class="container">
                <span>© {{ date('Y') }} Order Manager. Tous droits réservés.</span>
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
            // Preloader
            setTimeout(function() {
                document.querySelector('.preloader').classList.add('hidden');
                document.body.style.overflow = 'auto'; // Réactiver le défilement
            }, 800);
            
            // Sidebar Toggle
            const sidebarToggle = document.getElementById('sidebarToggle');
            const sidebar = document.getElementById('sidebar');
            const content = document.getElementById('content');
            const headerNavbar = document.querySelector('.header-navbar');
            
            sidebarToggle.addEventListener('click', function() {
                sidebar.classList.toggle('sidebar-collapsed');
                content.classList.toggle('content-expanded');
                headerNavbar.classList.toggle('content-expanded');
            });
            
            // Auto-hide alerts after 5 seconds
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                setTimeout(function() {
                    const bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                }, 5000);
            });
            
            // Submenu Toggle
            const submenus = document.querySelectorAll('.has-submenu');
            submenus.forEach(function(submenu) {
                submenu.addEventListener('click', function(e) {
                    e.preventDefault();
                    this.classList.toggle('active');
                    const submenuList = this.nextElementSibling;
                    submenuList.classList.toggle('show');
                });
            });
            
            // Auto-expand current submenu
            const currentSubmenu = document.querySelector('.sidebar-submenu.show');
            if (currentSubmenu) {
                const parentLink = currentSubmenu.previousElementSibling;
                if (parentLink.classList.contains('has-submenu')) {
                    parentLink.classList.add('active');
                }
            }
            
            // Animation pour les éléments de la page
            document.querySelectorAll('.slide-up').forEach(function(element, index) {
                setTimeout(function() {
                    element.classList.add('animate__animated', 'animate__fadeInUp');
                }, 100 * index);
            });
            
            document.querySelectorAll('.slide-in-left').forEach(function(element, index) {
                setTimeout(function() {
                    element.classList.add('animate__animated', 'animate__fadeInLeft');
                }, 100 * index);
            });
            
            document.querySelectorAll('.slide-in-right').forEach(function(element, index) {
                setTimeout(function() {
                    element.classList.add('animate__animated', 'animate__fadeInRight');
                }, 100 * index);
            });
        });
        
        // Afficher le préchargeur lors de la navigation
        window.addEventListener('beforeunload', function() {
            document.querySelector('.preloader').classList.remove('hidden');
        });
    </script>
    
    @yield('scripts')
</body>
</html>