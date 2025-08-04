@extends('layouts.admin')

@section('title', 'Gestion des Livraisons')

@section('css')
<style>
    :root {
        --primary: #1e40af;
        --primary-dark: #1e3a8a;
        --success: #10b981;
        --warning: #f59e0b;
        --danger: #ef4444;
        --info: #06b6d4;
        --light: #f8fafc;
        --dark: #374151;
        --border: #e5e7eb;
        --shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        --radius: 12px;
        --transition: all 0.3s ease;
    }

    body {
        background: linear-gradient(135deg, #f1f5f9 0%, #e2e8f0 100%);
        font-family: 'Inter', sans-serif;
    }

    /* ===== CONTAINER PRINCIPAL ===== */
    .delivery-container {
        background: white;
        border-radius: var(--radius);
        box-shadow: var(--shadow);
        margin: 1rem;
        min-height: calc(100vh - 120px);
        overflow: hidden;
    }

    /* ===== HEADER SIMPLIFIÉ ===== */
    .delivery-header {
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
        padding: 2rem;
        color: white;
        position: relative;
    }

    .delivery-header::before {
        content: '';
        position: absolute;
        top: 0;
        right: 0;
        width: 200px;
        height: 200px;
        background: rgba(255, 255, 255, 0.1);
        border-radius: 50%;
        transform: translate(50%, -50%);
    }

    .header-content {
        position: relative;
        z-index: 2;
    }

    .header-title {
        font-size: 2rem;
        font-weight: 700;
        margin-bottom: 0.5rem;
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .header-subtitle {
        opacity: 0.9;
        font-size: 1rem;
        margin-bottom: 1.5rem;
    }

    .header-actions {
        display: flex;
        gap: 1rem;
        flex-wrap: wrap;
    }

    .btn-header {
        background: rgba(255, 255, 255, 0.2);
        color: white;
        border: 1px solid rgba(255, 255, 255, 0.3);
        padding: 0.75rem 1.5rem;
        border-radius: 8px;
        text-decoration: none;
        font-weight: 600;
        transition: var(--transition);
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .btn-header:hover {
        background: rgba(255, 255, 255, 0.3);
        transform: translateY(-2px);
        color: white;
    }

    .btn-header.btn-primary {
        background: white;
        color: var(--primary);
    }

    .btn-header.btn-primary:hover {
        background: #f8fafc;
        color: var(--primary-dark);
    }

    /* ===== STATISTIQUES SIMPLIFIÉES ===== */
    .stats-section {
        padding: 2rem;
        background: #f8fafc;
        border-bottom: 1px solid var(--border);
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1.5rem;
    }

    .stat-card {
        background: white;
        padding: 1.5rem;
        border-radius: var(--radius);
        box-shadow: var(--shadow);
        text-align: center;
        transition: var(--transition);
        border-left: 4px solid transparent;
    }

    .stat-card:hover {
        transform: translateY(-2px);
        box-shadow: var(--shadow-lg);
    }

    .stat-card.stat-primary { border-left-color: var(--primary); }
    .stat-card.stat-success { border-left-color: var(--success); }
    .stat-card.stat-warning { border-left-color: var(--warning); }
    .stat-card.stat-info { border-left-color: var(--info); }

    .stat-number {
        font-size: 2.5rem;
        font-weight: 800;
        color: var(--dark);
        margin-bottom: 0.5rem;
        display: block;
    }

    .stat-label {
        color: #6b7280;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.875rem;
        letter-spacing: 0.05em;
    }

    /* ===== SECTION TRANSPORTEURS SIMPLIFIÉE ===== */
    .carriers-section {
        padding: 2rem;
    }

    .section-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 2rem;
        flex-wrap: wrap;
        gap: 1rem;
    }

    .section-title {
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--dark);
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .carriers-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
        gap: 1.5rem;
    }

    /* ===== CARTE TRANSPORTEUR SIMPLIFIÉE ===== */
    .carrier-card {
        background: white;
        border-radius: var(--radius);
        box-shadow: var(--shadow);
        overflow: hidden;
        transition: var(--transition);
        border: 1px solid var(--border);
    }

    .carrier-card:hover {
        transform: translateY(-2px);
        box-shadow: var(--shadow-lg);
    }

    .carrier-header {
        padding: 1.5rem;
        display: flex;
        align-items: center;
        gap: 1rem;
        position: relative;
    }

    .status-dot {
        position: absolute;
        top: 1rem;
        right: 1rem;
        width: 12px;
        height: 12px;
        border-radius: 50%;
        border: 2px solid white;
    }

    .status-dot.connected { background: var(--success); }
    .status-dot.inactive { background: var(--warning); }
    .status-dot.disconnected { background: var(--danger); }

    .carrier-logo {
        width: 50px;
        height: 50px;
        border-radius: 8px;
        object-fit: contain;
        background: #f3f4f6;
        padding: 8px;
        flex-shrink: 0;
    }

    .carrier-info h3 {
        font-size: 1.2rem;
        font-weight: 700;
        color: var(--dark);
        margin-bottom: 0.25rem;
    }

    .carrier-status {
        padding: 0.25rem 0.75rem;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .status-connected {
        background: #dcfce7;
        color: #166534;
    }

    .status-inactive {
        background: #fef3c7;
        color: #92400e;
    }

    .status-disconnected {
        background: #fee2e2;
        color: #991b1b;
    }

    .carrier-stats {
        padding: 0 1.5rem 1rem;
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 1rem;
        text-align: center;
    }

    .carrier-stat {
        display: flex;
        flex-direction: column;
    }

    .carrier-stat-number {
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--primary);
        margin-bottom: 0.25rem;
    }

    .carrier-stat-label {
        font-size: 0.75rem;
        color: #6b7280;
        font-weight: 500;
    }

    .carrier-actions {
        padding: 1.5rem;
        background: #f9fafb;
        border-top: 1px solid var(--border);
        display: flex;
        gap: 0.75rem;
        flex-wrap: wrap;
    }

    .btn {
        padding: 0.75rem 1rem;
        border-radius: 8px;
        font-weight: 600;
        font-size: 0.875rem;
        text-decoration: none;
        text-align: center;
        transition: var(--transition);
        border: none;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        flex: 1;
    }

    .btn:hover {
        transform: translateY(-1px);
        box-shadow: var(--shadow);
    }

    .btn-success {
        background: var(--success);
        color: white;
    }

    .btn-warning {
        background: var(--warning);
        color: white;
    }

    .btn-primary {
        background: var(--primary);
        color: white;
    }

    .btn-outline {
        background: transparent;
        color: var(--dark);
        border: 2px solid var(--border);
    }

    .btn-outline:hover {
        background: var(--primary);
        color: white;
        border-color: var(--primary);
    }

    /* ===== MODAL SIMPLIFIÉ ===== */
    .modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        z-index: 9999;
        align-items: center;
        justify-content: center;
        padding: 1rem;
    }

    .modal.show {
        display: flex;
    }

    .modal-dialog {
        background: white;
        border-radius: var(--radius);
        box-shadow: var(--shadow-lg);
        width: 100%;
        max-width: 500px;
        max-height: 90vh;
        overflow: hidden;
    }

    .modal-header {
        background: var(--primary);
        color: white;
        padding: 1.5rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .modal-title {
        font-size: 1.25rem;
        font-weight: 700;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .modal-close {
        background: none;
        border: none;
        color: white;
        font-size: 1.5rem;
        cursor: pointer;
        padding: 0.25rem;
        border-radius: 4px;
        transition: var(--transition);
    }

    .modal-close:hover {
        background: rgba(255, 255, 255, 0.2);
    }

    .modal-body {
        padding: 2rem;
        text-align: center;
    }

    .test-loading {
        display: none;
    }

    .test-loading.show {
        display: block;
    }

    .test-result {
        display: none;
    }

    .test-result.show {
        display: block;
    }

    .spinner {
        width: 40px;
        height: 40px;
        border: 3px solid #f3f4f6;
        border-top: 3px solid var(--primary);
        border-radius: 50%;
        animation: spin 1s linear infinite;
        margin: 0 auto 1rem;
    }

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    .alert {
        padding: 1rem;
        border-radius: 8px;
        margin-bottom: 1rem;
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .alert-success {
        background: #dcfce7;
        color: #166534;
        border: 1px solid #bbf7d0;
    }

    .alert-danger {
        background: #fee2e2;
        color: #991b1b;
        border: 1px solid #fecaca;
    }

    .modal-footer {
        padding: 1.5rem;
        background: #f9fafb;
        border-top: 1px solid var(--border);
        display: flex;
        justify-content: flex-end;
        gap: 1rem;
    }

    /* ===== RESPONSIVE MOBILE ===== */
    @media (max-width: 768px) {
        .delivery-header {
            padding: 1.5rem;
        }

        .header-title {
            font-size: 1.5rem;
        }

        .header-actions {
            justify-content: center;
        }

        .btn-header {
            flex: 1;
            justify-content: center;
        }

        .stats-section {
            padding: 1.5rem;
        }

        .stats-grid {
            grid-template-columns: repeat(2, 1fr);
            gap: 1rem;
        }

        .stat-card {
            padding: 1rem;
        }

        .stat-number {
            font-size: 2rem;
        }

        .carriers-section {
            padding: 1.5rem;
        }

        .carriers-grid {
            grid-template-columns: 1fr;
            gap: 1rem;
        }

        .carrier-header {
            padding: 1rem;
        }

        .carrier-actions {
            padding: 1rem;
            flex-direction: column;
        }

        .btn {
            flex: none;
        }

        .section-header {
            flex-direction: column;
            text-align: center;
        }

        .modal-dialog {
            margin: 1rem;
            max-width: none;
        }

        .modal-header,
        .modal-body,
        .modal-footer {
            padding: 1rem;
        }
    }

    @media (max-width: 480px) {
        .delivery-container {
            margin: 0.5rem;
        }

        .stats-grid {
            grid-template-columns: 1fr;
        }

        .carrier-stats {
            grid-template-columns: 1fr;
            gap: 0.5rem;
        }

        .modal {
            padding: 0.5rem;
        }
    }

    /* ===== ANIMATIONS ===== */
    .fade-in {
        animation: fadeIn 0.5s ease-out;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }

    /* ===== LOADING STATE ===== */
    .loading {
        opacity: 0.7;
        pointer-events: none;
    }

    .btn.loading {
        position: relative;
    }

    .btn.loading::after {
        content: '';
        position: absolute;
        top: 50%;
        left: 50%;
        width: 16px;
        height: 16px;
        margin: -8px 0 0 -8px;
        border: 2px solid transparent;
        border-top: 2px solid currentColor;
        border-radius: 50%;
        animation: spin 1s linear infinite;
    }
</style>
@endsection

@section('content')
<div class="delivery-container fade-in">
    <!-- Header Simplifié -->
    <div class="delivery-header">
        <div class="header-content">
            <h1 class="header-title">
                <i class="fas fa-truck"></i>
                Gestion des Livraisons
            </h1>
            <p class="header-subtitle">
                Interface multi-transporteurs • Temps réel • Optimisé mobile
            </p>
            <div class="header-actions">
                <a href="{{ route('admin.delivery.preparation') }}" class="btn-header btn-primary">
                    <i class="fas fa-plus"></i>
                    Nouvel Enlèvement
                </a>
                <a href="{{ route('admin.delivery.configuration') }}" class="btn-header">
                    <i class="fas fa-cog"></i>
                    Configurations
                </a>
                <a href="{{ route('admin.delivery.pickups') }}" class="btn-header">
                    <i class="fas fa-warehouse"></i>
                    Enlèvements
                </a>
            </div>
        </div>
    </div>

    <!-- Statistiques Simplifiées -->
    <div class="stats-section">
        <div class="stats-grid">
            <div class="stat-card stat-primary">
                <span class="stat-number" id="stat-configs">{{ $generalStats['active_configurations'] ?? 0 }}</span>
                <div class="stat-label">Configurations Actives</div>
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
                <div class="stat-label">Total Expéditions</div>
            </div>
        </div>
    </div>

    <!-- Section Transporteurs Simplifiée -->
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
                    <div class="status-dot {{ 
                        $carrierData['status'] === 'connecté' ? 'connected' : 
                        ($carrierData['status'] === 'configuré_inactif' ? 'inactive' : 'disconnected') 
                    }}"></div>
                    
                    <!-- Header avec logo et nom -->
                    <div class="carrier-header">
                        @if(isset($carrierData['config']['logo']))
                            <img src="{{ asset($carrierData['config']['logo']) }}" 
                                 alt="{{ $carrierData['config']['name'] }}" 
                                 class="carrier-logo">
                        @else
                            <div class="carrier-logo">
                                <i class="fas fa-truck" style="color: #6b7280; font-size: 1.5rem;"></i>
                            </div>
                        @endif
                        
                        <div class="carrier-info">
                            <h3>{{ $carrierData['config']['name'] }}</h3>
                            <span class="carrier-status {{ 
                                $carrierData['status'] === 'connecté' ? 'status-connected' : 
                                ($carrierData['status'] === 'configuré_inactif' ? 'status-inactive' : 'status-disconnected') 
                            }}">
                                {{ 
                                    $carrierData['status'] === 'connecté' ? 'Connecté' : 
                                    ($carrierData['status'] === 'configuré_inactif' ? 'Inactif' : 'Non configuré') 
                                }}
                            </span>
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

                    <!-- Actions -->
                    <div class="carrier-actions">
                        @if($carrierData['is_configured'])
                            @if($carrierData['active_configurations']->isNotEmpty())
                                <a href="{{ route('admin.delivery.preparation') }}?carrier={{ $slug }}" 
                                   class="btn btn-success">
                                    <i class="fas fa-plus"></i>
                                    Nouvel Envoi
                                </a>
                                <button type="button" 
                                        class="btn btn-primary"
                                        onclick="testCarrierConnection('{{ $carrierData['active_configurations']->first()->id }}', '{{ $carrierData['config']['name'] }}')">
                                    <i class="fas fa-wifi"></i>
                                    Test Connexion
                                </button>
                            @else
                                <a href="{{ route('admin.delivery.configuration.create') }}?carrier={{ $slug }}" 
                                   class="btn btn-warning">
                                    <i class="fas fa-power-off"></i>
                                    Activer
                                </a>
                            @endif
                            <a href="{{ route('admin.delivery.configuration') }}?filter={{ $slug }}" 
                               class="btn btn-outline">
                                <i class="fas fa-cog"></i>
                                Gérer
                            </a>
                        @else
                            <a href="{{ route('admin.delivery.configuration.create') }}?carrier={{ $slug }}" 
                               class="btn btn-primary" style="flex: 1;">
                                <i class="fas fa-plus"></i>
                                Configurer Maintenant
                            </a>
                        @endif
                    </div>
                </div>
            @empty
                <div class="carrier-card">
                    <div class="carrier-header">
                        <div class="carrier-logo">
                            <i class="fas fa-exclamation-triangle" style="color: #f59e0b; font-size: 1.5rem;"></i>
                        </div>
                        <div class="carrier-info">
                            <h3>Aucun transporteur configuré</h3>
                            <span class="carrier-status status-disconnected">Configuration requise</span>
                        </div>
                    </div>
                    <div class="carrier-actions">
                        <a href="{{ route('admin.delivery.configuration.create') }}" 
                           class="btn btn-primary" style="flex: 1;">
                            <i class="fas fa-plus"></i>
                            Configurer un transporteur
                        </a>
                    </div>
                </div>
            @endforelse
        </div>
    </div>
</div>

<!-- Modal de Test Simplifié -->
<div class="modal" id="testModal">
    <div class="modal-dialog">
        <div class="modal-header">
            <h5 class="modal-title">
                <i class="fas fa-wifi"></i>
                Test de Connexion
            </h5>
            <button type="button" class="modal-close" onclick="closeTestModal()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <div class="modal-body">
            <!-- État de chargement -->
            <div class="test-loading" id="testLoading">
                <div class="spinner"></div>
                <h4>Test en cours...</h4>
                <p>Vérification de la connexion avec <span id="carrierName">le transporteur</span></p>
            </div>

            <!-- Résultat du test -->
            <div class="test-result" id="testResult">
                <div class="alert" id="testAlert">
                    <i class="fas fa-check-circle" id="testIcon"></i>
                    <div>
                        <strong id="testTitle">Test terminé</strong>
                        <p id="testMessage">Résultat du test</p>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="modal-footer">
            <button type="button" class="btn btn-outline" onclick="closeTestModal()">
                Fermer
            </button>
            <button type="button" class="btn btn-primary" id="retestBtn" onclick="retestConnection()" style="display: none;">
                <i class="fas fa-redo"></i>
                Retester
            </button>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
// ===== VARIABLES GLOBALES =====
let currentConfigId = null;
let statsRefreshInterval = null;

// ===== INITIALISATION =====
document.addEventListener('DOMContentLoaded', function() {
    console.log('🚀 Page delivery initialisée');
    
    // Démarrer le rafraîchissement des stats
    startStatsRefresh();
    
    // Animation des compteurs au chargement
    animateCounters();
    
    // Gestion des clics sur les cartes
    setupCardAnimations();
    
    console.log('✅ Initialisation terminée');
});

// ===== GESTION DES STATISTIQUES =====
function startStatsRefresh() {
    // Rafraîchir immédiatement
    refreshStats();
    
    // Puis toutes les 30 secondes
    statsRefreshInterval = setInterval(refreshStats, 30000);
    
    console.log('📊 Rafraîchissement des stats démarré');
}

async function refreshStats() {
    try {
        const response = await fetch('{{ route("admin.delivery.api.general-stats") }}', {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        });
        
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}`);
        }
        
        const data = await response.json();
        
        if (data.success && data.general_stats) {
            updateStatsDisplay(data.general_stats);
            console.log('📈 Stats mises à jour:', data.general_stats);
        } else {
            console.warn('⚠️ Réponse stats invalide:', data);
        }
    } catch (error) {
        console.error('❌ Erreur rafraîchissement stats:', error);
        // Continuer silencieusement sans alerter l'utilisateur
    }
}

function updateStatsDisplay(stats) {
    // Mettre à jour chaque statistique avec animation
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
        // Animation simple de compteur
        const duration = 1000;
        const steps = 20;
        const stepValue = (newValue - currentValue) / steps;
        const stepDuration = duration / steps;
        
        let currentStep = 0;
        const interval = setInterval(() => {
            currentStep++;
            const value = Math.round(currentValue + (stepValue * currentStep));
            element.textContent = currentStep === steps ? newValue : value;
            
            if (currentStep >= steps) {
                clearInterval(interval);
            }
        }, stepDuration);
        
        // Effet visuel de mise à jour
        element.parentElement.classList.add('stat-updated');
        setTimeout(() => {
            element.parentElement.classList.remove('stat-updated');
        }, 2000);
    }
}

// ===== ANIMATION DES COMPTEURS AU CHARGEMENT =====
function animateCounters() {
    document.querySelectorAll('.stat-number').forEach(counter => {
        const target = parseInt(counter.textContent) || 0;
        if (target === 0) return;
        
        let current = 0;
        const increment = target / 30;
        const timer = setInterval(() => {
            current += increment;
            if (current >= target) {
                current = target;
                clearInterval(timer);
            }
            counter.textContent = Math.floor(current);
        }, 50);
    });
}

// ===== ANIMATIONS DES CARTES =====
function setupCardAnimations() {
    const cards = document.querySelectorAll('.carrier-card, .stat-card');
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '0';
                entry.target.style.transform = 'translateY(20px)';
                entry.target.style.transition = 'all 0.6s ease';
                
                setTimeout(() => {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }, 100);
                
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.1 });
    
    cards.forEach(card => observer.observe(card));
}

// ===== GESTION DU MODAL DE TEST =====
function testCarrierConnection(configId, carrierName) {
    currentConfigId = configId;
    
    // Réinitialiser le modal
    resetTestModal();
    
    // Mettre à jour le nom du transporteur
    document.getElementById('carrierName').textContent = carrierName;
    
    // Afficher le modal
    showTestModal();
    
    // Démarrer le test
    startConnectionTest(configId);
}

function showTestModal() {
    const modal = document.getElementById('testModal');
    modal.classList.add('show');
    document.body.style.overflow = 'hidden';
}

function closeTestModal() {
    const modal = document.getElementById('testModal');
    modal.classList.remove('show');
    document.body.style.overflow = '';
    
    // Réinitialiser le modal après fermeture
    setTimeout(resetTestModal, 300);
}

function resetTestModal() {
    // Masquer tous les états
    document.getElementById('testLoading').classList.remove('show');
    document.getElementById('testResult').classList.remove('show');
    document.getElementById('retestBtn').style.display = 'none';
    
    // Afficher l'état de chargement
    document.getElementById('testLoading').classList.add('show');
}

async function startConnectionTest(configId) {
    try {
        console.log('🔄 Démarrage test connexion config:', configId);
        
        const response = await fetch(`/admin/delivery/configuration/${configId}/test`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        });
        
        const data = await response.json();
        
        console.log('📝 Résultat test:', data);
        
        // Afficher le résultat
        showTestResult(data.success, data.message || data.error || 'Test terminé', data.details);
        
    } catch (error) {
        console.error('❌ Erreur test connexion:', error);
        showTestResult(false, 'Erreur de connexion: ' + error.message);
    }
}

function showTestResult(success, message, details = null) {
    // Masquer le chargement
    document.getElementById('testLoading').classList.remove('show');
    
    // Configurer le résultat
    const alert = document.getElementById('testAlert');
    const icon = document.getElementById('testIcon');
    const title = document.getElementById('testTitle');
    const messageEl = document.getElementById('testMessage');
    const retestBtn = document.getElementById('retestBtn');
    
    if (success) {
        alert.className = 'alert alert-success';
        icon.className = 'fas fa-check-circle';
        title.textContent = 'Connexion réussie !';
        messageEl.textContent = message;
        
        // Rafraîchir la page après 3 secondes pour voir les changements
        setTimeout(() => {
            window.location.reload();
        }, 3000);
    } else {
        alert.className = 'alert alert-danger';
        icon.className = 'fas fa-exclamation-triangle';
        title.textContent = 'Échec de connexion';
        messageEl.textContent = message;
        
        // Afficher le bouton de retest
        retestBtn.style.display = 'inline-flex';
    }
    
    // Afficher le résultat
    document.getElementById('testResult').classList.add('show');
}

function retestConnection() {
    if (currentConfigId) {
        resetTestModal();
        startConnectionTest(currentConfigId);
    }
}

// ===== GESTION DES ERREURS GLOBALES =====
window.addEventListener('error', function(e) {
    console.error('🚨 Erreur JavaScript:', e.error);
});

// ===== FERMETURE MODAL PAR ESCAPE =====
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeTestModal();
    }
});

// ===== FERMETURE MODAL PAR CLIC EXTÉRIEUR =====
document.getElementById('testModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeTestModal();
    }
});

// ===== NOTIFICATIONS TOAST =====
function showToast(type, message) {
    const toast = document.createElement('div');
    toast.className = `alert alert-${type}`;
    toast.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 10000;
        min-width: 300px;
        box-shadow: var(--shadow-lg);
        animation: slideInRight 0.3s ease;
    `;
    
    toast.innerHTML = `
        <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-triangle'}"></i>
        <span>${message}</span>
    `;
    
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.style.animation = 'slideOutRight 0.3s ease';
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

// ===== NETTOYAGE À LA FERMETURE =====
window.addEventListener('beforeunload', function() {
    if (statsRefreshInterval) {
        clearInterval(statsRefreshInterval);
    }
});

console.log('✅ Scripts delivery chargés et opérationnels');
</script>

<style>
/* Animations pour les notifications */
@keyframes slideInRight {
    from { transform: translateX(100%); opacity: 0; }
    to { transform: translateX(0); opacity: 1; }
}

@keyframes slideOutRight {
    from { transform: translateX(0); opacity: 1; }
    to { transform: translateX(100%); opacity: 0; }
}

/* Effet de mise à jour des stats */
.stat-updated {
    animation: pulse 0.6s ease;
}

@keyframes pulse {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.05); }
}
</style>
@endsection