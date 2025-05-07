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
    
    <!-- Custom CSS -->
    <style>
        :root {
            --primary-color: #4e73df;
            --secondary-color: #858796;
            --success-color: #1cc88a;
            --danger-color: #e74a3b;
            --warning-color: #f6c23e;
            --info-color: #36b9cc;
        }
        
        body {
            font-family: 'Nunito', sans-serif;
            background-color: #f8f9fc;
        }
        
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
            padding-top: 20px;
            transition: all 0.3s;
        }
        
        .sidebar-collapsed {
            width: 70px;
        }
        
        .sidebar-brand {
            padding: 15px;
            text-align: center;
            color: white;
            margin-bottom: 20px;
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
        
        .content {
            margin-left: 250px;
            padding: 20px;
            transition: all 0.3s;
        }
        
        .content-expanded {
            margin-left: 70px;
        }
        
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
        }
        
        .card-header {
            background-color: #f8f9fc;
            border-bottom: 1px solid #e3e6f0;
            padding: 1rem;
        }
        
        .card-title {
            margin-bottom: 0;
            color: #4e73df;
            font-weight: 600;
        }
        
        .card-body {
            padding: 1rem;
        }
        
        .stats-card {
            border-left: 4px solid;
            border-radius: 0.35rem;
        }
        
        .stats-card-primary {
            border-left-color: var(--primary-color);
        }
        
        .stats-card-success {
            border-left-color: var(--success-color);
        }
        
        .stats-card-info {
            border-left-color: var(--info-color);
        }
        
        .stats-card-warning {
            border-left-color: var(--warning-color);
        }
        
        .stats-card-danger {
            border-left-color: var(--danger-color);
        }
        
        .stats-card-icon {
            font-size: 2rem;
            opacity: 0.4;
        }
        
        .stats-card-number {
            font-size: 1.5rem;
            font-weight: 700;
            color: #5a5c69;
        }
        
        .stats-card-label {
            text-transform: uppercase;
            font-size: 0.7rem;
            font-weight: 700;
            color: #b7b9cc;
        }
        
        .footer {
            background-color: white;
            padding: 1rem;
            text-align: center;
            margin-top: 20px;
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
        }
    </style>
    
    @yield('css')
</head>
<body>
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
                <a href="{{ route('admin.products.index') }}" class="sidebar-link {{ request()->routeIs('admin.products*') ? 'active' : '' }}">
                    <div class="sidebar-icon">
                        <i class="fas fa-box-open"></i>
                    </div>
                    <span class="sidebar-text">Produits</span>
                </a>
            </li>
            
            <!-- Autres éléments du menu à ajouter plus tard -->
        </ul>
    </div>
    
    <!-- Content -->
    <div class="content" id="content">
        <!-- Navbar -->
        <nav class="navbar navbar-expand navbar-light">
            <button class="btn" id="sidebarToggle">
                <i class="fas fa-bars"></i>
            </button>
            
            <div class="ms-auto">
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <span class="me-2 d-none d-lg-inline text-gray-600 small">{{ Auth::guard('admin')->user()->name }}</span>
                            <i class="fas fa-user-circle fa-fw"></i>
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
                    </li>
                </ul>
            </div>
        </nav>
        
        <!-- Page Content -->
        <div class="container-fluid">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            
            @if(session('error'))
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
            // Sidebar Toggle
            const sidebarToggle = document.getElementById('sidebarToggle');
            const sidebar = document.getElementById('sidebar');
            const content = document.getElementById('content');
            
            sidebarToggle.addEventListener('click', function() {
                sidebar.classList.toggle('sidebar-collapsed');
                content.classList.toggle('content-expanded');
            });
            
            // Auto-hide alerts after 5 seconds
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                setTimeout(function() {
                    const bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                }, 5000);
            });
        });
    </script>
    
    @yield('scripts')
</body>
</html>