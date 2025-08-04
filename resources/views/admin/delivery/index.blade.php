@extends('layouts.admin')

@section('title', 'Centre de Livraison')

@section('css')
<style>
    :root {
        --royal-blue: #1e3a8a;
        --royal-blue-light: #3b82f6;
        --royal-blue-lighter: #60a5fa;
        --success: #10b981;
        --warning: #f59e0b;
        --danger: #ef4444;
        --info: #06b6d4;
        --light: #f8fafc;
        --dark: #1f2937;
        --border: #e5e7eb;
        --shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        --shadow-md: 0 4px 6px rgba(0, 0, 0, 0.1);
        --radius: 8px;
        --transition: all 0.2s ease;
    }

    body {
        background: linear-gradient(135deg, #f1f5f9 0%, #e2e8f0 100%);
        font-family: 'Inter', system-ui, sans-serif;
    }

    /* ===== CONTAINER PRINCIPAL ===== */
    .delivery-dashboard {
        background: white;
        border-radius: var(--radius);
        box-shadow: var(--shadow);
        margin: 0.5rem;
        min-height: calc(100vh - 70px);
        overflow: hidden;
    }

    /* ===== HEADER MODERNE ===== */
    .dashboard-header {
        background: linear-gradient(135deg, var(--royal-blue) 0%, var(--royal-blue-light) 100%);
        padding: 1.25rem;
        color: white;
        position: relative;
        overflow: hidden;
    }

    .dashboard-header::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -20%;
        width: 200px;
        height: 200px;
        background: rgba(255, 255, 255, 0.1);
        border-radius: 50%;
        transform: scale(1.5);
    }

    .header-content {
        position: relative;
        z-index: 2;
    }

    .header-title {
        font-size: 1.5rem;
        font-weight: 700;
        margin-bottom: 0.5rem;
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .header-subtitle {
        opacity: 0.9;
        font-size: 0.875rem;
        margin-bottom: 1rem;
    }

    .header-actions {
        display: flex;
        gap: 0.5rem;
        flex-wrap: wrap;
    }

    .btn-header {
        background: rgba(255, 255, 255, 0.2);
        color: white;
        border: 1px solid rgba(255, 255, 255, 0.3);
        padding: 0.5rem 0.875rem;
        border-radius: 6px;
        text-decoration: none;
        font-weight: 600;
        font-size: 0.8rem;
        transition: var(--transition);
        display: flex;
        align-items: center;
        gap: 0.4rem;
        backdrop-filter: blur(10px);
    }

    .btn-header:hover {
        background: rgba(255, 255, 255, 0.3);
        transform: translateY(-1px);
        color: white;
        text-decoration: none;
    }

    .btn-header.btn-primary {
        background: white;
        color: var(--royal-blue);
    }

    .btn-header.btn-primary:hover {
        background: #f8fafc;
        color: var(--royal-blue);
    }

    /* ===== STATISTIQUES ULTRA-COMPACTES ===== */
    .stats-section {
        padding: 1rem;
        background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
        border-bottom: 1px solid var(--border);
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
        gap: 0.75rem;
    }

    .stat-card {
        background: white;
        padding: 0.875rem;
        border-radius: var(--radius);
        box-shadow: var(--shadow);
        text-align: center;
        transition: var(--transition);
        border-left: 3px solid transparent;
        min-height: 70px;
        display: flex;
        flex-direction: column;
        justify-content: center;
        position: relative;
        overflow: hidden;
    }

    .stat-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 2px;
        background: linear-gradient(90deg, transparent, var(--royal-blue-lighter), transparent);
        opacity: 0;
        transition: var(--transition);
    }

    .stat-card:hover {
        transform: translateY(-2px);
        box-shadow: var(--shadow-md);
    }

    .stat-card:hover::before {
        opacity: 1;
    }

    .stat-card.stat-primary { border-left-color: var(--royal-blue); }
    .stat-card.stat-success { border-left-color: var(--success); }
    .stat-card.stat-warning { border-left-color: var(--warning); }
    .stat-card.stat-info { border-left-color: var(--info); }

    .stat-number {
        font-size: 1.75rem;
        font-weight: 800;
        color: var(--dark);
        margin-bottom: 0.25rem;
        display: block;
        line-height: 1;
    }

    .stat-label {
        color: #6b7280;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.7rem;
        letter-spacing: 0.05em;
        line-height: 1.1;
    }

    /* ===== SECTION TRANSPORTEURS MODERNE ===== */
    .carriers-section {
        padding: 1.25rem;
    }

    .section-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.25rem;
        flex-wrap: wrap;
        gap: 1rem;
    }

    .section-title {
        font-size: 1.125rem;
        font-weight: 700;
        color: var(--dark);
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .carriers-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
        gap: 1rem;
    }

    /* ===== CARTE TRANSPORTEUR ULTRA-MODERNE ===== */
    .carrier-card {
        background: white;
        border-radius: var(--radius);
        box-shadow: var(--shadow);
        overflow: hidden;
        transition: var(--transition);
        border: 1px solid var(--border);
        position: relative;
        background: linear-gradient(135deg, #ffffff 0%, #f9fafb 100%);
    }

    .carrier-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 25px rgba(30, 58, 138, 0.15);
    }

    .status-indicator {
        position: absolute;
        top: 0;
        right: 0;
        padding: 0.25rem 0.75rem;
        font-size: 0.7rem;
        font-weight: 600;
        text-transform: uppercase;
        border-bottom-left-radius: var(--radius);
        z-index: 10;
    }

    .status-indicator.connected {
        background: var(--success);
        color: white;
    }

    .status-indicator.inactive {
        background: var(--warning);
        color: white;
    }

    .status-indicator.disconnected {
        background: var(--danger);
        color: white;
    }

    .carrier-header {
        padding: 1rem;
        display: flex;
        align-items: center;
        gap: 0.75rem;
        border-bottom: 1px solid #f3f4f6;
    }

    .carrier-logo {
        width: 36px;
        height: 36px;
        border-radius: 6px;
        object-fit: contain;
        background: #f3f4f6;
        padding: 6px;
        flex-shrink: 0;
    }

    .carrier-info h3 {
        font-size: 0.95rem;
        font-weight: 700;
        color: var(--dark);
        margin-bottom: 0.25rem;
        line-height: 1.2;
    }

    .carrier-info p {
        font-size: 0.75rem;
        color: #6b7280;
        margin: 0;
    }

    .carrier-stats {
        padding: 0.75rem 1rem;
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 0.5rem;
        text-align: center;
        background: #f9fafb;
    }

    .carrier-stat {
        display: flex;
        flex-direction: column;
        padding: 0.25rem;
    }

    .carrier-stat-number {
        font-size: 1.125rem;
        font-weight: 700;
        color: var(--royal-blue);
        margin-bottom: 0.125rem;
        line-height: 1;
    }

    .carrier-stat-label {
        font-size: 0.65rem;
        color: #6b7280;
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .carrier-actions {
        padding: 0.875rem;
        display: flex;
        gap: 0.5rem;
        flex-wrap: wrap;
    }

    .btn {
        padding: 0.5rem 0.75rem;
        border-radius: 6px;
        font-weight: 600;
        font-size: 0.75rem;
        text-decoration: none;
        text-align: center;
        transition: var(--transition);
        border: none;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.375rem;
        flex: 1;
        min-width: 0;
        position: relative;
        overflow: hidden;
    }

    .btn::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
        transition: left 0.5s;
    }

    .btn:hover::before {
        left: 100%;
    }

    .btn:hover {
        transform: translateY(-1px);
        text-decoration: none;
    }

    .btn-success {
        background: linear-gradient(135deg, var(--success), #059669);
        color: white;
    }

    .btn-warning {
        background: linear-gradient(135deg, var(--warning), #d97706);
        color: white;
    }

    .btn-primary {
        background: linear-gradient(135deg, var(--royal-blue), var(--royal-blue-light));
        color: white;
    }

    .btn-outline {
        background: transparent;
        color: var(--dark);
        border: 1px solid var(--border);
    }

    .btn-outline:hover {
        background: var(--royal-blue);
        color: white;
        border-color: var(--royal-blue);
    }

    /* ===== ÉTAT VIDE STYLÉ ===== */
    .empty-state {
        text-align: center;
        padding: 2rem;
        color: #6b7280;
        background: linear-gradient(135deg, #f9fafb 0%, #f3f4f6 100%);
        border-radius: var(--radius);
        border: 2px dashed #d1d5db;
    }

    .empty-state i {
        font-size: 2.5rem;
        margin-bottom: 1rem;
        opacity: 0.5;
        color: var(--royal-blue);
    }

    .empty-state h3 {
        margin-bottom: 0.5rem;
        color: var(--dark);
        font-size: 1rem;
    }

    .empty-state p {
        margin-bottom: 1.5rem;
        font-size: 0.875rem;
    }

    /* ===== RESPONSIVE ULTRA-OPTIMISÉ ===== */
    @media (max-width: 768px) {
        .delivery-dashboard {
            margin: 0.25rem;
            border-radius: 0;
        }

        .dashboard-header {
            padding: 1rem;
        }

        .header-title {
            font-size: 1.25rem;
        }

        .header-actions {
            justify-content: stretch;
        }

        .btn-header {
            flex: 1;
            justify-content: center;
            font-size: 0.75rem;
            padding: 0.5rem;
        }

        .stats-section {
            padding: 0.75rem;
        }

        .stats-grid {
            grid-template-columns: repeat(2, 1fr);
            gap: 0.5rem;
        }

        .stat-card {
            padding: 0.75rem;
            min-height: 60px;
        }

        .stat-number {
            font-size: 1.5rem;
        }

        .carriers-section {
            padding: 0.75rem;
        }

        .carriers-grid {
            grid-template-columns: 1fr;
            gap: 0.75rem;
        }

        .carrier-header {
            padding: 0.75rem;
        }

        .carrier-logo {
            width: 32px;
            height: 32px;
        }

        .carrier-actions {
            padding: 0.75rem;
            flex-direction: column;
        }

        .btn {
            flex: none;
        }
    }

    @media (max-width: 480px) {
        .stats-grid {
            grid-template-columns: 1fr;
        }

        .carrier-stats {
            grid-template-columns: 1fr;
            gap: 0.25rem;
            text-align: left;
        }

        .carrier-stat {
            flex-direction: row;
            align-items: center;
            justify-content: space-between;
            padding: 0.375rem;
            background: white;
            border-radius: 4px;
        }

        .btn-header {
            font-size: 0.7rem;
            padding: 0.4rem;
        }

        .empty-state {
            padding: 1.5rem;
        }

        .empty-state i {
            font-size: 2rem;
        }
    }

    /* ===== ANIMATIONS FLUIDES ===== */
    .fade-in {
        animation: fadeIn 0.4s ease-out;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .stat-updated {
        animation: pulse 0.6s ease;
    }

    @keyframes pulse {
        0%, 100% { transform: scale(1); }
        50% { transform: scale(1.05); box-shadow: 0 0 20px rgba(30, 58, 138, 0.3); }
    }

    /* ===== NOTIFICATIONS TOAST ===== */
    .toast {
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 10000;
        min-width: 280px;
        max-width: 400px;
        padding: 0.75rem;
        border-radius: 6px;
        color: white;
        font-weight: 600;
        font-size: 0.875rem;
        box-shadow: var(--shadow-md);
        animation: slideInRight 0.3s ease;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .toast.success { background: var(--success); }
    .toast.warning { background: var(--warning); }
    .toast.danger { background: var(--danger); }
    .toast.info { background: var(--info); }

    @keyframes slideInRight {
        from { transform: translateX(100%); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }

    @keyframes slideOutRight {
        from { transform: translateX(0); opacity: 1; }
        to { transform: translateX(100%); opacity: 0; }
    }
</style>
@endsection

@section('content')
<div class="delivery-dashboard fade-in">
    <!-- Header Moderne -->
    <div class="dashboard-header">
        <div class="header-content">
            <h1 class="header-title">
                <i class="fas fa-shipping-fast"></i>
                Centre de Livraison
            </h1>
            <p class="header-subtitle">
                Tableau de bord multi-transporteurs • Interface moderne et responsive
            </p>
            <div class="header-actions">
                <a href="{{ route('admin.delivery.preparation') }}" class="btn-header btn-primary">
                    <i class="fas fa-plus"></i>
                    Nouvelle Expédition
                </a>
                <a href="{{ route('admin.delivery.configuration') }}" class="btn-header">
                    <i class="fas fa-cog"></i>
                    Configuration
                </a>
                <a href="{{ route('admin.delivery.pickups') }}" class="btn-header">
                    <i class="fas fa-warehouse"></i>
                    Enlèvements
                </a>
                <a href="{{ route('admin.delivery.shipments') }}" class="btn-header">
                    <i class="fas fa-box"></i>
                    Expéditions
                </a>
            </div>
        </div>
    </div>

    <!-- Statistiques Ultra-Compactes -->
    <div class="stats-section">
        <div class="stats-grid">
            <div class="stat-card stat-primary">
                <span class="stat-number" id="stat-configs">{{ $generalStats['active_configurations'] ?? 0 }}</span>
                <div class="stat-label">Actives</div>
            </div>
            <div class="stat-card stat-warning">
                <span class="stat-number" id="stat-pending">{{ $generalStats['pending_pickups'] ?? 0 }}</span>
                <div class="stat-label">En Attente</div>
            </div>
            <div class="stat-card stat-info">
                <span class="stat-number" id="stat-transit">{{ $generalStats['active_shipments'] ?? 0 }}</span>
                <div class="stat-label">En Transit</div>
            </div>
            <div class="stat-card stat-success">
                <span class="stat-number" id="stat-total">{{ $generalStats['total_shipments'] ?? 0 }}</span>
                <div class="stat-label">Total</div>
            </div>
        </div>
    </div>

    <!-- Section Transporteurs -->
    <div class="carriers-section">
        <div class="section-header">
            <h2 class="section-title">
                <i class="fas fa-truck-moving"></i>
                Transporteurs Disponibles
            </h2>
        </div>

        <div class="carriers-grid">
            @forelse($carriersData ?? [] as $slug => $carrierData)
                <div class="carrier-card">
                    <!-- Indicateur de statut -->
                    <div class="status-indicator {{ 
                        $carrierData['status'] === 'connecté' ? 'connected' : 
                        ($carrierData['status'] === 'configuré_inactif' ? 'inactive' : 'disconnected') 
                    }}">
                        {{ 
                            $carrierData['status'] === 'connecté' ? 'Actif' : 
                            ($carrierData['status'] === 'configuré_inactif' ? 'Inactif' : 'Non configuré') 
                        }}
                    </div>
                    
                    <!-- Header -->
                    <div class="carrier-header">
                        @if(isset($carrierData['config']['logo']))
                            <img src="{{ asset($carrierData['config']['logo']) }}" 
                                 alt="{{ $carrierData['config']['name'] }}" 
                                 class="carrier-logo">
                        @else
                            <div class="carrier-logo">
                                <i class="fas fa-truck" style="color: #6b7280; font-size: 1rem;"></i>
                            </div>
                        @endif
                        
                        <div class="carrier-info">
                            <h3>{{ $carrierData['config']['name'] }}</h3>
                            <p>{{ $carrierData['config']['description'] ?? 'Service de livraison' }}</p>
                        </div>
                    </div>

                    <!-- Statistiques -->
                    <div class="carrier-stats">
                        <div class="carrier-stat">
                            <div class="carrier-stat-number">{{ $carrierData['stats']['configurations'] }}</div>
                            <div class="carrier-stat-label">Configs</div>
                        </div>
                        <div class="carrier-stat">
                            <div class="carrier-stat-number">{{ $carrierData['stats']['pickups'] }}</div>
                            <div class="carrier-stat-label">Pickups</div>
                        </div>
                        <div class="carrier-stat">
                            <div class="carrier-stat-number">{{ $carrierData['stats']['shipments'] }}</div>
                            <div class="carrier-stat-label">Envois</div>
                        </div>
                    </div>

                    <!-- Actions Simplifiées -->
                    <div class="carrier-actions">
                        @if($carrierData['is_configured'])
                            @if($carrierData['active_configurations']->isNotEmpty())
                                <a href="{{ route('admin.delivery.preparation') }}?carrier={{ $slug }}" 
                                   class="btn btn-success">
                                    <i class="fas fa-plus"></i>
                                    Expédier
                                </a>
                                <a href="{{ route('admin.delivery.configuration') }}?filter={{ $slug }}" 
                                   class="btn btn-outline">
                                    <i class="fas fa-cog"></i>
                                    Gérer
                                </a>
                            @else
                                <a href="{{ route('admin.delivery.configuration') }}?filter={{ $slug }}" 
                                   class="btn btn-warning">
                                    <i class="fas fa-power-off"></i>
                                    Activer
                                </a>
                            @endif
                        @else
                            <a href="{{ route('admin.delivery.configuration.create') }}?carrier={{ $slug }}" 
                               class="btn btn-primary" style="flex: 1;">
                                <i class="fas fa-plus"></i>
                                Configurer
                            </a>
                        @endif
                    </div>
                </div>
            @empty
                <div class="empty-state">
                    <i class="fas fa-truck"></i>
                    <h3>Aucun transporteur configuré</h3>
                    <p>Configurez votre premier transporteur pour commencer les expéditions</p>
                    <a href="{{ route('admin.delivery.configuration.create') }}" 
                       class="btn btn-primary">
                        <i class="fas fa-plus"></i>
                        Configurer maintenant
                    </a>
                </div>
            @endforelse
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
// ===== CONFIGURATION GLOBALE =====
const DELIVERY_CONFIG = {
    apiRoutes: {
        generalStats: '{{ route("admin.delivery.api.general-stats") }}'
    },
    refreshInterval: 30000,
    csrfToken: document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
};

// ===== VARIABLES GLOBALES =====
let statsRefreshInterval = null;

// ===== INITIALISATION =====
document.addEventListener('DOMContentLoaded', function() {
    console.log('🚀 Delivery Dashboard Initialized');
    
    if (!DELIVERY_CONFIG.csrfToken) {
        console.error('❌ CSRF token not found');
        showToast('danger', 'Erreur de sécurité. Veuillez recharger la page.');
        return;
    }
    
    initializeApp();
});

function initializeApp() {
    try {
        startStatsRefresh();
        animateCounters();
        setupEventListeners();
        console.log('✅ Application initialized successfully');
    } catch (error) {
        console.error('❌ Initialization error:', error);
        showToast('danger', 'Erreur d\'initialisation');
    }
}

// ===== GESTION DES STATISTIQUES =====
function startStatsRefresh() {
    refreshStats();
    statsRefreshInterval = setInterval(refreshStats, DELIVERY_CONFIG.refreshInterval);
    console.log('📊 Stats refresh started');
}

async function refreshStats() {
    try {
        const response = await fetch(DELIVERY_CONFIG.apiRoutes.generalStats, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': DELIVERY_CONFIG.csrfToken
            }
        });
        
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}`);
        }
        
        const data = await response.json();
        
        if (data.success && data.general_stats) {
            updateStatsDisplay(data.general_stats);
        }
    } catch (error) {
        console.warn('⚠️ Stats refresh failed:', error);
    }
}

function updateStatsDisplay(stats) {
    updateStatWithAnimation('stat-configs', stats.active_configurations || 0);
    updateStatWithAnimation('stat-pending', stats.pending_pickups || 0);
    updateStatWithAnimation('stat-transit', stats.active_shipments || 0);
    updateStatWithAnimation('stat-total', stats.total_shipments || 0);
}

function updateStatWithAnimation(elementId, newValue) {
    const element = document.getElementById(elementId);
    if (!element) return;
    
    const currentValue = parseInt(element.textContent) || 0;
    
    if (currentValue !== newValue) {
        animateNumber(element, currentValue, newValue);
        element.parentElement.classList.add('stat-updated');
        setTimeout(() => {
            element.parentElement.classList.remove('stat-updated');
        }, 600);
    }
}

function animateNumber(element, from, to) {
    const duration = 600;
    const steps = 20;
    const stepValue = (to - from) / steps;
    const stepDuration = duration / steps;
    
    let currentStep = 0;
    const interval = setInterval(() => {
        currentStep++;
        const value = currentStep === steps ? to : Math.round(from + (stepValue * currentStep));
        element.textContent = value;
        
        if (currentStep >= steps) {
            clearInterval(interval);
        }
    }, stepDuration);
}

// ===== ANIMATION DES COMPTEURS AU CHARGEMENT =====
function animateCounters() {
    document.querySelectorAll('.stat-number').forEach(counter => {
        const target = parseInt(counter.textContent) || 0;
        if (target > 0) {
            animateNumber(counter, 0, target);
        }
    });
}

// ===== GESTION DES ÉVÉNEMENTS =====
function setupEventListeners() {
    // Gestion des erreurs globales
    window.addEventListener('error', function(e) {
        console.error('🚨 JavaScript error:', e.error);
    });
    
    // Nettoyage avant fermeture
    window.addEventListener('beforeunload', function() {
        if (statsRefreshInterval) {
            clearInterval(statsRefreshInterval);
        }
    });

    // Animation des cartes au scroll
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.animation = 'fadeIn 0.6s ease-out';
            }
        });
    }, { threshold: 0.1 });

    document.querySelectorAll('.carrier-card, .stat-card').forEach(card => {
        observer.observe(card);
    });
}

// ===== NOTIFICATIONS TOAST AMÉLIORÉES =====
function showToast(type, message) {
    const toast = document.createElement('div');
    toast.className = `toast ${type}`;
    
    const icon = type === 'success' ? 'check-circle' : 
                 type === 'warning' ? 'exclamation-triangle' : 
                 type === 'danger' ? 'exclamation-circle' : 'info-circle';
    
    toast.innerHTML = `
        <i class="fas fa-${icon}"></i>
        <span>${message}</span>
    `;
    
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.style.animation = 'slideOutRight 0.3s ease';
        setTimeout(() => toast.remove(), 300);
    }, 4000);
}

// ===== FONCTIONS UTILITAIRES =====
function formatNumber(num) {
    return new Intl.NumberFormat('fr-FR').format(num);
}

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

console.log('✅ Delivery Dashboard Scripts Loaded');
</script>
@endsection