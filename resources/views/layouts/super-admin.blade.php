<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0,maximum-scale=5.0,user-scalable=yes">
<meta name="csrf-token" content="{{ csrf_token() }}">
<meta name="theme-color" content="#4f46e5">
<title>@yield('title', 'Super Admin') - Order Manager</title>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
*{margin:0;padding:0;box-sizing:border-box}
:root{
    --sa-primary:#4f46e5;--sa-primary-dark:#4338ca;--sa-primary-light:#6366f1;--sa-primary-50:#eef2ff;--sa-primary-100:#e0e7ff;
    --sa-success:#10b981;--sa-success-light:#d1fae5;--sa-warning:#f59e0b;--sa-warning-light:#fef3c7;--sa-danger:#ef4444;--sa-danger-light:#fee2e2;--sa-info:#06b6d4;--sa-info-light:#cffafe;
    --sa-bg:#f1f5f9;--sa-card:#fff;--sa-border:#e2e8f0;--sa-text:#0f172a;--sa-text-secondary:#64748b;--sa-text-muted:#94a3b8;
    --sa-sidebar-bg:#0f172a;--sa-sidebar-text:#94a3b8;
    --sa-radius:14px;--sa-radius-sm:10px;--sa-shadow:0 1px 3px rgba(0,0,0,.06);--sa-shadow-lg:0 8px 24px rgba(0,0,0,.08);
    --sa-sidebar-w:260px;--sa-header-h:64px;
}
body{font-family:'Plus Jakarta Sans',sans-serif;background:var(--sa-bg);color:var(--sa-text);font-size:14px;line-height:1.6;min-height:100vh}

/* === SIDEBAR === */
.sa-sidebar{position:fixed;top:0;left:0;width:var(--sa-sidebar-w);height:100vh;background:var(--sa-sidebar-bg);color:var(--sa-sidebar-text);z-index:1000;display:flex;flex-direction:column;transition:transform .3s ease}
.sa-sidebar-head{padding:20px;border-bottom:1px solid rgba(255,255,255,.08)}
.sa-sidebar-brand{display:flex;align-items:center;gap:12px;text-decoration:none;color:#fff}
.sa-sidebar-brand-icon{width:38px;height:38px;background:linear-gradient(135deg,var(--sa-primary),var(--sa-primary-light));border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:1.1rem;font-weight:800}
.sa-sidebar-brand span{font-size:1.05rem;font-weight:800;letter-spacing:-.3px}
.sa-sidebar-brand small{display:block;font-size:.7rem;font-weight:500;color:var(--sa-sidebar-text);margin-top:1px}
.sa-sidebar-nav{flex:1;overflow-y:auto;padding:16px 12px}
.sa-sidebar-nav::-webkit-scrollbar{width:4px}
.sa-sidebar-nav::-webkit-scrollbar-thumb{background:rgba(255,255,255,.15);border-radius:2px}
.sa-nav-section{font-size:.65rem;text-transform:uppercase;letter-spacing:.08em;color:rgba(255,255,255,.35);padding:16px 12px 6px;font-weight:700}
.sa-nav-link{display:flex;align-items:center;gap:11px;padding:9px 12px;border-radius:var(--sa-radius-sm);color:var(--sa-sidebar-text);text-decoration:none;font-size:.8125rem;font-weight:500;transition:all .15s;margin-bottom:2px;position:relative}
.sa-nav-link:hover{background:rgba(255,255,255,.06);color:#e2e8f0}
.sa-nav-link.active{background:rgba(99,102,241,.18);color:#fff;font-weight:600}
.sa-nav-link i{width:18px;text-align:center;font-size:.85rem;opacity:.7}
.sa-nav-link.active i{opacity:1;color:var(--sa-primary-light)}
.sa-nav-badge{margin-left:auto;min-width:20px;height:20px;border-radius:10px;font-size:.65rem;font-weight:700;display:flex;align-items:center;justify-content:center;padding:0 6px}
.sa-nav-badge-danger{background:var(--sa-danger);color:#fff}
.sa-nav-badge-warning{background:var(--sa-warning);color:#fff}
.sa-nav-badge-info{background:var(--sa-primary-light);color:#fff}
.sa-sidebar-foot{padding:16px;border-top:1px solid rgba(255,255,255,.08)}
.sa-sidebar-user{display:flex;align-items:center;gap:10px;padding:8px;border-radius:var(--sa-radius-sm)}
.sa-sidebar-avatar{width:36px;height:36px;border-radius:10px;background:linear-gradient(135deg,var(--sa-primary),var(--sa-primary-light));display:flex;align-items:center;justify-content:center;font-weight:700;font-size:.8rem;color:#fff}
.sa-sidebar-user-info{flex:1;min-width:0}
.sa-sidebar-user-info strong{display:block;font-size:.8rem;color:#fff;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.sa-sidebar-user-info small{font-size:.7rem;color:var(--sa-sidebar-text)}
.sa-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:999}

/* === HEADER === */
.sa-header{position:fixed;top:0;left:var(--sa-sidebar-w);right:0;height:var(--sa-header-h);background:var(--sa-card);border-bottom:1px solid var(--sa-border);display:flex;align-items:center;justify-content:space-between;padding:0 24px;z-index:998;transition:left .3s}
.sa-header-left{display:flex;align-items:center;gap:16px}
.sa-header-burger{display:none;width:40px;height:40px;border:none;background:var(--sa-bg);border-radius:var(--sa-radius-sm);cursor:pointer;font-size:1.1rem;color:var(--sa-text)}
.sa-header h1{font-size:1.15rem;font-weight:700;color:var(--sa-text)}
.sa-header-actions{display:flex;align-items:center;gap:12px}
.sa-logout-btn{display:inline-flex;align-items:center;gap:8px;padding:8px 16px;background:var(--sa-danger-light);color:var(--sa-danger);border:none;border-radius:var(--sa-radius-sm);font-size:.8rem;font-weight:600;cursor:pointer;transition:all .15s;font-family:inherit}
.sa-logout-btn:hover{background:var(--sa-danger);color:#fff}

/* === CONTENT === */
.sa-content{margin-left:var(--sa-sidebar-w);padding-top:var(--sa-header-h);min-height:100vh;transition:margin-left .3s}
.sa-page{padding:24px}

/* === COMPONENTS === */
.sa-card{background:var(--sa-card);border:1px solid var(--sa-border);border-radius:var(--sa-radius);padding:24px;box-shadow:var(--sa-shadow)}
.sa-card-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;gap:12px;flex-wrap:wrap}
.sa-card-title{font-size:1rem;font-weight:700;color:var(--sa-text)}

.sa-stat{background:var(--sa-card);border:1px solid var(--sa-border);border-radius:var(--sa-radius);padding:20px;box-shadow:var(--sa-shadow);display:flex;align-items:center;gap:16px}
.sa-stat-icon{width:48px;height:48px;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:1.25rem;flex-shrink:0}
.sa-stat-icon-primary{background:var(--sa-primary-50);color:var(--sa-primary)}
.sa-stat-icon-success{background:var(--sa-success-light);color:var(--sa-success)}
.sa-stat-icon-warning{background:var(--sa-warning-light);color:var(--sa-warning)}
.sa-stat-icon-danger{background:var(--sa-danger-light);color:var(--sa-danger)}
.sa-stat-icon-info{background:var(--sa-info-light);color:var(--sa-info)}
.sa-stat-value{font-size:1.5rem;font-weight:800;color:var(--sa-text);line-height:1.2}
.sa-stat-label{font-size:.75rem;color:var(--sa-text-secondary);font-weight:500}

.sa-grid{display:grid;gap:20px}
.sa-grid-2{grid-template-columns:repeat(2,1fr)}
.sa-grid-3{grid-template-columns:repeat(3,1fr)}
.sa-grid-4{grid-template-columns:repeat(4,1fr)}

.sa-table-wrap{overflow-x:auto;-webkit-overflow-scrolling:touch}
.sa-table{width:100%;border-collapse:collapse}
.sa-table th{padding:10px 16px;text-align:left;font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:var(--sa-text-secondary);background:var(--sa-bg);border-bottom:1px solid var(--sa-border)}
.sa-table td{padding:12px 16px;border-bottom:1px solid var(--sa-border);font-size:.8125rem;color:var(--sa-text)}
.sa-table tr:hover td{background:rgba(99,102,241,.02)}
.sa-table tr:last-child td{border-bottom:none}

.sa-badge{display:inline-flex;align-items:center;padding:3px 10px;border-radius:20px;font-size:.7rem;font-weight:600}
.sa-badge-success{background:var(--sa-success-light);color:#065f46}
.sa-badge-danger{background:var(--sa-danger-light);color:#991b1b}
.sa-badge-warning{background:var(--sa-warning-light);color:#92400e}
.sa-badge-info{background:var(--sa-info-light);color:#155e75}
.sa-badge-primary{background:var(--sa-primary-50);color:var(--sa-primary-dark)}
.sa-badge-muted{background:#f1f5f9;color:#64748b}

.sa-btn{display:inline-flex;align-items:center;gap:8px;padding:8px 18px;border-radius:var(--sa-radius-sm);font-size:.8125rem;font-weight:600;border:none;cursor:pointer;transition:all .15s;font-family:inherit;text-decoration:none}
.sa-btn-primary{background:var(--sa-primary);color:#fff}
.sa-btn-primary:hover{background:var(--sa-primary-dark);color:#fff}
.sa-btn-success{background:var(--sa-success);color:#fff}
.sa-btn-success:hover{background:#059669;color:#fff}
.sa-btn-danger{background:var(--sa-danger);color:#fff}
.sa-btn-danger:hover{background:#dc2626;color:#fff}
.sa-btn-warning{background:var(--sa-warning);color:#fff}
.sa-btn-outline{background:transparent;border:1px solid var(--sa-border);color:var(--sa-text-secondary)}
.sa-btn-outline:hover{border-color:var(--sa-primary);color:var(--sa-primary);background:var(--sa-primary-50)}
.sa-btn-sm{padding:5px 12px;font-size:.75rem}
.sa-btn-icon{width:32px;height:32px;padding:0;justify-content:center}

.sa-input{width:100%;padding:9px 14px;border:1px solid var(--sa-border);border-radius:var(--sa-radius-sm);font-size:.8125rem;font-family:inherit;transition:all .15s;background:var(--sa-card);color:var(--sa-text)}
.sa-input:focus{outline:none;border-color:var(--sa-primary);box-shadow:0 0 0 3px rgba(99,102,241,.1)}
.sa-select{appearance:none;background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%2364748b' stroke-width='2'%3E%3Cpolyline points='6,9 12,15 18,9'/%3E%3C/svg%3E");background-repeat:no-repeat;background-position:right 12px center;padding-right:32px}

.sa-avatar{width:36px;height:36px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:.8rem;color:#fff;flex-shrink:0}
.sa-avatar-primary{background:linear-gradient(135deg,var(--sa-primary),var(--sa-primary-light))}
.sa-avatar-success{background:linear-gradient(135deg,var(--sa-success),#34d399)}

.sa-empty{text-align:center;padding:40px 20px;color:var(--sa-text-secondary)}
.sa-empty i{font-size:2.5rem;margin-bottom:12px;opacity:.3}
.sa-empty p{font-size:.875rem}

.sa-alert{padding:14px 18px;border-radius:var(--sa-radius-sm);font-size:.8125rem;display:flex;align-items:center;gap:10px;margin-bottom:16px}
.sa-alert-success{background:var(--sa-success-light);color:#065f46;border:1px solid #a7f3d0}
.sa-alert-danger{background:var(--sa-danger-light);color:#991b1b;border:1px solid #fecaca}
.sa-alert-warning{background:var(--sa-warning-light);color:#92400e;border:1px solid #fde68a}
.sa-alert-info{background:var(--sa-primary-50);color:var(--sa-primary-dark);border:1px solid var(--sa-primary-100)}

.sa-pagination{display:flex;align-items:center;justify-content:center;gap:4px;margin-top:20px;flex-wrap:wrap}
.sa-pagination a,.sa-pagination span{padding:6px 12px;border-radius:8px;font-size:.8rem;font-weight:500;text-decoration:none;border:1px solid var(--sa-border);color:var(--sa-text-secondary)}
.sa-pagination a:hover{border-color:var(--sa-primary);color:var(--sa-primary)}
.sa-pagination .active span{background:var(--sa-primary);color:#fff;border-color:var(--sa-primary)}
.sa-pagination .disabled span{opacity:.4}

.sa-form-group{margin-bottom:16px}
.sa-form-label{display:block;font-size:.8rem;font-weight:600;color:var(--sa-text);margin-bottom:6px}
.sa-form-hint{font-size:.7rem;color:var(--sa-text-muted);margin-top:4px}

.sa-modal-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:2000;align-items:center;justify-content:center;padding:16px}
.sa-modal-overlay.show{display:flex}
.sa-modal{background:var(--sa-card);border-radius:var(--sa-radius);padding:24px;width:100%;max-width:480px;box-shadow:var(--sa-shadow-lg)}
.sa-modal-title{font-size:1.1rem;font-weight:700;margin-bottom:16px}

/* === RESPONSIVE === */
@media(max-width:1024px){.sa-grid-4{grid-template-columns:repeat(2,1fr)}.sa-grid-3{grid-template-columns:repeat(2,1fr)}}
@media(max-width:768px){
    .sa-sidebar{transform:translateX(-100%)}.sa-sidebar.open{transform:translateX(0)}.sa-overlay.open{display:block}
    .sa-header{left:0}.sa-header-burger{display:flex;align-items:center;justify-content:center}
    .sa-content{margin-left:0}.sa-page{padding:16px}
    .sa-grid-4,.sa-grid-3,.sa-grid-2{grid-template-columns:1fr}
    .sa-stat{padding:16px}.sa-stat-value{font-size:1.25rem}
    .sa-card{padding:16px;border-radius:12px}
    .sa-table th,.sa-table td{padding:10px 12px;font-size:.75rem}
    .sa-btn{padding:7px 14px;font-size:.75rem}
}
@media(max-width:480px){.sa-header h1{font-size:.95rem}.sa-page{padding:12px}}
</style>
@yield('css')
</head>
<body>
    @include('partials._no-cache')

    @php
        $saUser = auth('super-admin')->user();
        $saName = $saUser ? $saUser->name : 'Admin';
        $saInitial = $saUser ? strtoupper(substr($saUser->name,0,1)) : 'S';
        $pendingRequests = \App\Models\ConfirmiRequest::where('status','pending')->count();
        $unpaidBilling = \App\Models\ConfirmiBilling::where('is_paid',false)->count();
        $inactiveAdmins = \App\Models\Admin::where('role','admin')->where('is_active',false)->count();
        $pendingEmballage = \App\Models\EmballageTask::where('status','pending')->count();
    @endphp

    <aside class="sa-sidebar" id="sidebar">
        <div class="sa-sidebar-head">
            <a href="{{ route('super-admin.dashboard') }}" class="sa-sidebar-brand">
                <div class="sa-sidebar-brand-icon">OM</div>
                <div><span>Order Manager</span><small>Super Admin</small></div>
            </a>
        </div>
        <nav class="sa-sidebar-nav">
            <div class="sa-nav-section">Principal</div>
            <a href="{{ route('super-admin.dashboard') }}" class="sa-nav-link {{ request()->routeIs('super-admin.dashboard*') ? 'active' : '' }}">
                <i class="fas fa-chart-pie"></i><span>Dashboard</span>
            </a>
            <a href="{{ route('super-admin.admins.index') }}" class="sa-nav-link {{ request()->routeIs('super-admin.admins*') ? 'active' : '' }}">
                <i class="fas fa-building"></i><span>Administrateurs</span>
                @if($inactiveAdmins > 0)<span class="sa-nav-badge sa-nav-badge-danger">{{ $inactiveAdmins }}</span>@endif
            </a>

            <div class="sa-nav-section">Confirmi</div>
            <a href="{{ route('super-admin.confirmi-users.index') }}" class="sa-nav-link {{ request()->routeIs('super-admin.confirmi-users*') ? 'active' : '' }}">
                <i class="fas fa-users-cog"></i><span>Utilisateurs</span>
            </a>
            <a href="{{ route('super-admin.confirmi-requests.index') }}" class="sa-nav-link {{ request()->routeIs('super-admin.confirmi-requests*') ? 'active' : '' }}">
                <i class="fas fa-inbox"></i><span>Demandes</span>
                @if($pendingRequests > 0)<span class="sa-nav-badge sa-nav-badge-warning">{{ $pendingRequests }}</span>@endif
            </a>
            <a href="{{ route('super-admin.confirmi-billing.index') }}" class="sa-nav-link {{ request()->routeIs('super-admin.confirmi-billing*') ? 'active' : '' }}">
                <i class="fas fa-file-invoice-dollar"></i><span>Facturation</span>
                @if($unpaidBilling > 0)<span class="sa-nav-badge sa-nav-badge-info">{{ $unpaidBilling }}</span>@endif
            </a>
            <a href="{{ route('super-admin.emballage.index') }}" class="sa-nav-link {{ request()->routeIs('super-admin.emballage*') ? 'active' : '' }}">
                <i class="fas fa-box"></i><span>Emballage</span>
                @if($pendingEmballage > 0)<span class="sa-nav-badge sa-nav-badge-warning">{{ $pendingEmballage }}</span>@endif
            </a>

            <div class="sa-nav-section">Système</div>
            <a href="{{ route('super-admin.settings.index') }}" class="sa-nav-link {{ request()->routeIs('super-admin.settings*') ? 'active' : '' }}">
                <i class="fas fa-cog"></i><span>Paramètres</span>
            </a>
        </nav>
        <div class="sa-sidebar-foot">
            <div class="sa-sidebar-user">
                <div class="sa-sidebar-avatar">{{ $saInitial }}</div>
                <div class="sa-sidebar-user-info"><strong>{{ $saName }}</strong><small>Super Admin</small></div>
            </div>
        </div>
    </aside>

    <div class="sa-overlay" id="overlay" onclick="closeSidebar()"></div>

    <header class="sa-header">
        <div class="sa-header-left">
            <button class="sa-header-burger" onclick="toggleSidebar()"><i class="fas fa-bars"></i></button>
            <h1>@yield('page-title', 'Dashboard')</h1>
        </div>
        <div class="sa-header-actions">
            <form action="{{ route('super-admin.logout') }}" method="POST" style="display:inline">
                @csrf
                <button type="submit" class="sa-logout-btn"><i class="fas fa-sign-out-alt"></i><span>Déconnexion</span></button>
            </form>
        </div>
    </header>

    <div class="sa-content">
        <div class="sa-page">
            @if(session('success'))<div class="sa-alert sa-alert-success"><i class="fas fa-check-circle"></i>{{ session('success') }}</div>@endif
            @if(session('error'))<div class="sa-alert sa-alert-danger"><i class="fas fa-exclamation-circle"></i>{{ session('error') }}</div>@endif
            @if(session('info'))<div class="sa-alert sa-alert-info"><i class="fas fa-info-circle"></i>{{ session('info') }}</div>@endif
            @if(session('warning'))<div class="sa-alert sa-alert-warning"><i class="fas fa-exclamation-triangle"></i>{{ session('warning') }}</div>@endif
            @yield('content')
        </div>
    </div>

    <script>
    function toggleSidebar(){document.getElementById('sidebar').classList.toggle('open');document.getElementById('overlay').classList.toggle('open')}
    function closeSidebar(){document.getElementById('sidebar').classList.remove('open');document.getElementById('overlay').classList.remove('open')}
    if(window.innerWidth<=768){document.querySelectorAll('.sa-nav-link').forEach(l=>l.addEventListener('click',closeSidebar))}
    </script>
    @yield('scripts')
</body>
</html>
