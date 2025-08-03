@extends('layouts.admin')

@section('title', 'Gestion des Livraisons')

@section('css')
<style>
    :root {
        --delivery-primary: linear-gradient(135deg, #1e40af 0%, #1e3a8a 100%);
        --delivery-success: linear-gradient(135deg, #10b981 0%, #059669 100%);
        --delivery-warning: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        --delivery-danger: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        --delivery-info: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%);
        --glass-bg: rgba(255, 255, 255, 0.95);
        --glass-border: rgba(255, 255, 255, 0.2);
        --shadow-elevated: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
        --border-radius-lg: 16px;
        --border-radius-xl: 20px;
        --transition-smooth: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        --spacing-xs: 0.5rem;
        --spacing-sm: 0.75rem;
        --spacing-md: 1rem;
        --spacing-lg: 1.5rem;
        --spacing-xl: 2rem;
    }

    body {
        background: linear-gradient(135deg, #f1f5f9 0%, #e2e8f0 100%);
        font-family: 'Inter', sans-serif;
    }

    /* ===== CONTAINER PRINCIPAL COMPACT ===== */
    .delivery-container {
        background: var(--glass-bg);
        backdrop-filter: blur(20px);
        border-radius: var(--border-radius-xl);
        box-shadow: var(--shadow-elevated);
        border: 1px solid var(--glass-border);
        margin: 0.25rem;
        min-height: calc(100vh - 80px);
        overflow: hidden;
    }

    /* ===== HEADER COMPACT ===== */
    .delivery-header {
        background: var(--delivery-primary);
        padding: var(--spacing-lg) var(--spacing-xl);
        position: relative;
        overflow: hidden;
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: var(--spacing-md);
        min-height: 100px;
    }

    .delivery-header::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -20%;
        width: 100%;
        height: 200%;
        background: radial-gradient(circle, rgba(255, 255, 255, 0.08) 0%, transparent 70%);
        transform: rotate(15deg);
    }

    .delivery-icon {
        color: white;
        font-size: 2.5rem;
        position: relative;
        z-index: 2;
        display: flex;
        align-items: center;
        justify-content: center;
        width: 60px;
        height: 60px;
        background: rgba(255, 255, 255, 0.1);
        border-radius: var(--border-radius-lg);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.2);
    }

    .delivery-title {
        position: relative;
        z-index: 2;
        flex: 1;
        min-width: 250px;
    }

    .delivery-title h1 {
        color: white;
        font-size: 2rem;
        font-weight: 700;
        margin: 0;
        text-shadow: 0 2px 4px rgba(0,0,0,0.2);
        line-height: 1.2;
    }

    .delivery-subtitle {
        color: rgba(255, 255, 255, 0.9);
        font-size: 0.95rem;
        margin-top: 0.25rem;
        font-weight: 500;
    }

    .delivery-actions {
        position: relative;
        z-index: 2;
        display: flex;
        gap: var(--spacing-sm);
        flex-wrap: wrap;
    }

    .delivery-actions .btn {
        background: rgba(255, 255, 255, 0.15);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.25);
        color: white;
        font-weight: 600;
        padding: 0.6rem 1.2rem;
        border-radius: 12px;
        transition: var(--transition-smooth);
        display: flex;
        align-items: center;
        gap: 0.4rem;
        font-size: 0.9rem;
    }

    .delivery-actions .btn:hover {
        background: rgba(255, 255, 255, 0.25);
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        color: white;
    }

    .delivery-actions .btn-primary {
        background: rgba(255, 255, 255, 0.9);
        color: var(--primary-color);
    }

    .delivery-actions .btn-primary:hover {
        background: white;
        color: var(--primary-color);
    }

    /* ===== STATISTICS CARDS COMPACT ===== */
    .stats-section {
        padding: var(--spacing-lg);
        background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: var(--spacing-md);
    }

    .stat-card {
        background: white;
        border-radius: var(--border-radius-lg);
        padding: var(--spacing-lg);
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        border: 1px solid #e5e7eb;
        transition: var(--transition-smooth);
        position: relative;
        overflow: hidden;
    }

    .stat-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 3px;
        z-index: 1;
    }

    .stat-card.stat-primary::before { background: var(--delivery-primary); }
    .stat-card.stat-success::before { background: var(--delivery-success); }
    .stat-card.stat-info::before { background: var(--delivery-info); }
    .stat-card.stat-warning::before { background: var(--delivery-warning); }

    .stat-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 20px -5px rgba(0, 0, 0, 0.15);
    }

    .stat-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: var(--spacing-sm);
    }

    .stat-icon {
        width: 45px;
        height: 45px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
        color: white;
        flex-shrink: 0;
    }

    .stat-icon.icon-primary { background: var(--delivery-primary); }
    .stat-icon.icon-success { background: var(--delivery-success); }
    .stat-icon.icon-info { background: var(--delivery-info); }
    .stat-icon.icon-warning { background: var(--delivery-warning); }

    .stat-number {
        font-size: 2rem;
        font-weight: 800;
        color: #374151;
        line-height: 1;
        margin-bottom: 0.25rem;
    }

    .stat-label {
        color: #6b7280;
        font-weight: 600;
        font-size: 0.8rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    /* ===== CARRIERS SECTION OPTIMIZED ===== */
    .carriers-section {
        padding: var(--spacing-lg);
    }

    .section-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: var(--spacing-lg);
        flex-wrap: wrap;
        gap: var(--spacing-md);
    }

    .section-title {
        font-size: 1.5rem;
        font-weight: 700;
        color: #374151;
        display: flex;
        align-items: center;
        gap: var(--spacing-sm);
        margin: 0;
    }

    .section-subtitle {
        color: #6b7280;
        margin-top: 0.25rem;
        font-size: 0.9rem;
    }

    .carriers-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: var(--spacing-lg);
    }

    /* ===== CARRIER CARD COMPACT ===== */
    .carrier-card {
        background: white;
        border-radius: var(--border-radius-lg);
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        border: 1px solid #e5e7eb;
        overflow: hidden;
        transition: var(--transition-smooth);
        position: relative;
    }

    .carrier-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 20px -5px rgba(0, 0, 0, 0.15);
    }

    .status-indicator {
        position: absolute;
        top: var(--spacing-sm);
        right: var(--spacing-sm);
        width: 10px;
        height: 10px;
        border-radius: 50%;
        z-index: 2;
    }

    .status-indicator.connected {
        background: #10b981;
        box-shadow: 0 0 0 2px rgba(16, 185, 129, 0.3);
        animation: pulse-success 2s infinite;
    }

    .status-indicator.inactive {
        background: #f59e0b;
        box-shadow: 0 0 0 2px rgba(245, 158, 11, 0.3);
    }

    .status-indicator.disconnected {
        background: #ef4444;
        box-shadow: 0 0 0 2px rgba(239, 68, 68, 0.3);
    }

    @keyframes pulse-success {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.5; }
    }

    .carrier-header {
        padding: var(--spacing-lg);
        display: flex;
        align-items: center;
        gap: var(--spacing-md);
    }

    .carrier-logo {
        width: 45px;
        height: 45px;
        border-radius: 10px;
        object-fit: contain;
        background: #f8fafc;
        padding: 6px;
        border: 1px solid #e5e7eb;
        flex-shrink: 0;
    }

    .carrier-info {
        flex: 1;
        min-width: 0;
    }

    .carrier-name {
        font-size: 1.1rem;
        font-weight: 700;
        color: #374151;
        margin-bottom: 0.25rem;
        line-height: 1.2;
    }

    .carrier-status {
        padding: 0.2rem 0.6rem;
        border-radius: 15px;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        display: inline-block;
    }

    .status-connected {
        background: linear-gradient(135deg, #dcfce7 0%, #bbf7d0 100%);
        color: #166534;
    }

    .status-inactive {
        background: linear-gradient(135deg, #fef3c7 0%, #fde047 100%);
        color: #92400e;
    }

    .status-disconnected {
        background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
        color: #991b1b;
    }

    .carrier-description {
        padding: 0 var(--spacing-lg);
        color: #6b7280;
        font-size: 0.85rem;
        margin-bottom: var(--spacing-md);
        line-height: 1.4;
    }

    .carrier-stats {
        padding: 0 var(--spacing-lg) var(--spacing-md);
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: var(--spacing-sm);
        text-align: center;
    }

    .carrier-stat {
        display: flex;
        flex-direction: column;
        gap: 0.15rem;
    }

    .carrier-stat-number {
        font-size: 1.25rem;
        font-weight: 700;
        line-height: 1;
    }

    .carrier-stat-number.text-primary { color: #1e40af; }
    .carrier-stat-number.text-success { color: #10b981; }
    .carrier-stat-number.text-info { color: #06b6d4; }

    .carrier-stat-label {
        font-size: 0.7rem;
        color: #6b7280;
        font-weight: 500;
    }

    .carrier-configs {
        padding: 0 var(--spacing-lg) var(--spacing-md);
    }

    .configs-label {
        font-size: 0.75rem;
        color: #6b7280;
        margin-bottom: var(--spacing-xs);
        font-weight: 500;
    }

    .config-badges {
        display: flex;
        flex-wrap: wrap;
        gap: 0.4rem;
    }

    .config-badge {
        padding: 0.2rem 0.6rem;
        background: #f3f4f6;
        color: #374151;
        border-radius: 12px;
        font-size: 0.75rem;
        font-weight: 500;
        border: 1px solid #e5e7eb;
    }

    .carrier-actions {
        padding: var(--spacing-md) var(--spacing-lg);
        background: #f9fafb;
        border-top: 1px solid #e5e7eb;
    }

    .btn-group {
        display: flex;
        gap: 0.4rem;
        width: 100%;
    }

    .btn-group .btn {
        flex: 1;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.4rem;
        padding: 0.6rem 0.8rem;
        border-radius: 8px;
        font-weight: 600;
        font-size: 0.8rem;
        transition: var(--transition-smooth);
        border: none;
        text-decoration: none;
        min-height: 36px;
    }

    .btn-success {
        background: var(--delivery-success);
        color: white;
    }

    .btn-warning {
        background: var(--delivery-warning);
        color: white;
    }

    .btn-primary {
        background: var(--delivery-primary);
        color: white;
    }

    .btn-outline-primary {
        background: transparent;
        color: #1e40af;
        border: 1.5px solid #1e40af;
    }

    .btn-outline-secondary {
        background: transparent;
        color: #6b7280;
        border: 1.5px solid #6b7280;
    }

    .btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 15px rgba(0,0,0,0.15);
    }

    .btn-outline-primary:hover {
        background: #1e40af;
        color: white;
    }

    .btn-outline-secondary:hover {
        background: #6b7280;
        color: white;
    }

    .carrier-footer {
        padding: var(--spacing-sm) var(--spacing-lg);
        background: #f9fafb;
        border-top: 1px solid #e5e7eb;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: var(--spacing-xs);
    }

    .footer-link {
        color: #6b7280;
        text-decoration: none;
        font-size: 0.8rem;
        display: flex;
        align-items: center;
        gap: 0.25rem;
        transition: var(--transition-smooth);
    }

    .footer-link:hover {
        color: #1e40af;
    }

    /* ===== QUICK ACTIONS COMPACT ===== */
    .quick-actions-section {
        padding: 0 var(--spacing-lg) var(--spacing-lg);
    }

    .quick-actions {
        background: white;
        border-radius: var(--border-radius-lg);
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        border: 1px solid #e5e7eb;
        overflow: hidden;
    }

    .quick-actions-header {
        padding: var(--spacing-md) var(--spacing-lg);
        background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
        border-bottom: 1px solid #e5e7eb;
    }

    .quick-actions-title {
        font-size: 1.1rem;
        font-weight: 700;
        color: #374151;
        display: flex;
        align-items: center;
        gap: var(--spacing-sm);
        margin: 0;
    }

    .quick-actions-body {
        padding: var(--spacing-md);
    }

    .action-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: var(--spacing-sm);
    }

    .action-item {
        display: flex;
        align-items: center;
        gap: var(--spacing-md);
        padding: var(--spacing-md);
        color: #374151;
        text-decoration: none;
        border-radius: 10px;
        transition: var(--transition-smooth);
        border: 1px solid #f3f4f6;
        background: #fafafa;
    }

    .action-item:hover {
        background: rgba(30, 64, 175, 0.05);
        border-color: #1e40af;
        transform: translateY(-2px);
        color: #1e40af;
        box-shadow: 0 4px 12px rgba(30, 64, 175, 0.15);
    }

    .action-icon {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1rem;
        flex-shrink: 0;
    }

    .action-icon.icon-success { background: var(--delivery-success); }
    .action-icon.icon-primary { background: var(--delivery-primary); }
    .action-icon.icon-info { background: var(--delivery-info); }
    .action-icon.icon-secondary { background: linear-gradient(135deg, #6b7280 0%, #4b5563 100%); }

    .action-text {
        flex: 1;
        min-width: 0;
    }

    .action-text .action-title {
        font-weight: 600;
        margin-bottom: 0.15rem;
        font-size: 0.9rem;
        line-height: 1.2;
    }

    .action-text .action-desc {
        font-size: 0.8rem;
        color: #6b7280;
        line-height: 1.3;
    }

    /* ===== MODAL COMPACT ===== */
    .modal-content {
        border: none !important;
        border-radius: var(--border-radius-lg) !important;
        box-shadow: var(--shadow-elevated) !important;
        overflow: hidden !important;
    }

    .modal-header {
        background: var(--delivery-primary);
        color: white;
        border: none !important;
        padding: var(--spacing-lg) !important;
    }

    .modal-title {
        font-weight: 700;
        display: flex;
        align-items: center;
        gap: var(--spacing-sm);
        font-size: 1.1rem;
    }

    .modal-body {
        padding: var(--spacing-lg) !important;
    }

    .modal-footer {
        border: none !important;
        padding: var(--spacing-lg) !important;
        background: #f9fafb !important;
    }

    /* ===== RESPONSIVE OPTIMIZED ===== */
    @media (max-width: 1200px) {
        .carriers-grid {
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        }
        
        .action-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 768px) {
        .delivery-header {
            flex-direction: column;
            text-align: center;
            padding: var(--spacing-md);
            gap: var(--spacing-sm);
        }

        .delivery-title h1 {
            font-size: 1.75rem;
        }

        .delivery-subtitle {
            font-size: 0.9rem;
        }

        .delivery-actions {
            width: 100%;
            justify-content: center;
        }

        .delivery-actions .btn {
            flex: 1;
            min-width: 120px;
        }

        .stats-grid {
            grid-template-columns: repeat(2, 1fr);
            gap: var(--spacing-sm);
        }

        .stat-card {
            padding: var(--spacing-md);
        }

        .stat-number {
            font-size: 1.5rem;
        }

        .stat-icon {
            width: 35px;
            height: 35px;
            font-size: 1rem;
        }

        .carriers-grid {
            grid-template-columns: 1fr;
            gap: var(--spacing-md);
        }

        .section-header {
            flex-direction: column;
            text-align: center;
            gap: var(--spacing-sm);
        }

        .section-title {
            font-size: 1.25rem;
        }

        .carrier-header {
            padding: var(--spacing-md);
        }

        .carrier-stats {
            grid-template-columns: 3fr;
            gap: var(--spacing-xs);
        }

        .carrier-stat-number {
            font-size: 1rem;
        }

        .carrier-stat-label {
            font-size: 0.65rem;
        }

        .btn-group {
            flex-direction: column;
            gap: var(--spacing-xs);
        }

        .carrier-footer {
            flex-direction: column;
            text-align: center;
            gap: var(--spacing-xs);
        }

        .action-grid {
            grid-template-columns: 1fr;
            gap: var(--spacing-xs);
        }

        .action-item {
            padding: var(--spacing-sm);
        }

        .action-icon {
            width: 35px;
            height: 35px;
            font-size: 0.9rem;
        }

        .action-text .action-title {
            font-size: 0.85rem;
        }

        .action-text .action-desc {
            font-size: 0.75rem;
        }
    }

    @media (max-width: 480px) {
        .delivery-container {
            margin: 0.125rem;
        }

        .stats-grid {
            grid-template-columns: 1fr;
        }

        .carriers-section,
        .stats-section,
        .quick-actions-section {
            padding: var(--spacing-md);
        }

        .delivery-header {
            padding: var(--spacing-sm);
        }

        .delivery-title h1 {
            font-size: 1.5rem;
        }

        .delivery-actions .btn {
            font-size: 0.8rem;
            padding: 0.5rem 1rem;
        }

        .carrier-card {
            margin-bottom: var(--spacing-sm);
        }
    }

    /* ===== ANIMATIONS OPTIMIZED ===== */
    .fade-in {
        animation: fadeIn 0.4s ease-out;
    }

    .slide-up {
        animation: slideUp 0.4s ease-out;
    }

    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }

    @keyframes slideUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* ===== LOADING STATES ===== */
    .btn.loading {
        position: relative;
        pointer-events: none;
        opacity: 0.7;
    }

    .btn.loading::after {
        content: '';
        position: absolute;
        top: 50%;
        left: 50%;
        width: 14px;
        height: 14px;
        margin: -7px 0 0 -7px;
        border: 2px solid transparent;
        border-top: 2px solid currentColor;
        border-radius: 50%;
        animation: spin 1s linear infinite;
    }

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    /* ===== PERFORMANCE OPTIMIZATIONS ===== */
    .carrier-card,
    .stat-card,
    .action-item {
        will-change: transform;
    }

    .carrier-card:hover,
    .stat-card:hover,
    .action-item:hover {
        will-change: auto;
    }

    /* ===== SCROLLBAR MINIMAL ===== */
    ::-webkit-scrollbar {
        width: 4px;
        height: 4px;
    }

    ::-webkit-scrollbar-track {
        background: #f1f5f9;
        border-radius: 2px;
    }

    ::-webkit-scrollbar-thumb {
        background: linear-gradient(135deg, #1e40af 0%, #1e3a8a 100%);
        border-radius: 2px;
    }
</style>
@endsection

@section('content')
<div class="delivery-container" x-data="deliveryIndex">
    <!-- Header compact -->
    <div class="delivery-header fade-in">
        <div class="delivery-icon">
            <i class="fas fa-truck"></i>
        </div>
        
        <div class="delivery-title">
            <h1>Gestion des Livraisons</h1>
            <p class="delivery-subtitle">Multi-transporteurs • Temps réel</p>
        </div>
        
        <div class="delivery-actions">
            <a href="{{ route('admin.delivery.preparation') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i>
                Nouvel Enlèvement
            </a>
            <a href="{{ route('admin.delivery.configuration') }}" class="btn">
                <i class="fas fa-cog"></i>
                Configurations
            </a>
        </div>
    </div>

    <!-- Statistiques compactes -->
    <div class="stats-section">
        <div class="stats-grid">
            <div class="stat-card stat-primary slide-up">
                <div class="stat-header">
                    <div>
                        <div class="stat-number" x-text="stats.active_configurations">{{ $generalStats['active_configurations'] ?? 0 }}</div>
                        <div class="stat-label">Configurations</div>
                    </div>
                    <div class="stat-icon icon-primary">
                        <i class="fas fa-cog"></i>
                    </div>
                </div>
            </div>

            <div class="stat-card stat-success slide-up">
                <div class="stat-header">
                    <div>
                        <div class="stat-number" x-text="stats.pending_pickups">{{ $generalStats['pending_pickups'] ?? 0 }}</div>
                        <div class="stat-label">En Attente</div>
                    </div>
                    <div class="stat-icon icon-success">
                        <i class="fas fa-box"></i>
                    </div>
                </div>
            </div>

            <div class="stat-card stat-info slide-up">
                <div class="stat-header">
                    <div>
                        <div class="stat-number" x-text="stats.active_shipments">{{ $generalStats['active_shipments'] ?? 0 }}</div>
                        <div class="stat-label">En Transit</div>
                    </div>
                    <div class="stat-icon icon-info">
                        <i class="fas fa-truck-moving"></i>
                    </div>
                </div>
            </div>

            <div class="stat-card stat-warning slide-up">
                <div class="stat-header">
                    <div>
                        <div class="stat-number" x-text="stats.total_shipments">{{ $generalStats['total_shipments'] ?? 0 }}</div>
                        <div class="stat-label">Total</div>
                    </div>
                    <div class="stat-icon icon-warning">
                        <i class="fas fa-chart-line"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Section transporteurs optimisée -->
    <div class="carriers-section">
        <div class="section-header">
            <div>
                <h2 class="section-title">
                    <i class="fas fa-truck-moving"></i>
                    Transporteurs
                </h2>
                <p class="section-subtitle">Intégrations multi-canal</p>
            </div>
        </div>

        <div class="carriers-grid">
            @foreach($carriersData ?? [] as $slug => $carrierData)
                <div class="carrier-card slide-up">
                    <!-- Indicateur de statut -->
                    <div class="status-indicator {{ $carrierData['status'] === 'connecté' ? 'connected' : ($carrierData['status'] === 'configuré_inactif' ? 'inactive' : 'disconnected') }}"></div>
                    
                    <!-- Header avec logo et nom -->
                    <div class="carrier-header">
                        @if(isset($carrierData['config']['logo']))
                            <img src="{{ asset($carrierData['config']['logo']) }}" 
                                 alt="{{ $carrierData['config']['name'] }}" 
                                 class="carrier-logo">
                        @else
                            <div class="carrier-logo d-flex align-items-center justify-content-center">
                                <i class="fas fa-truck text-muted"></i>
                            </div>
                        @endif
                        
                        <div class="carrier-info">
                            <h3 class="carrier-name">{{ $carrierData['config']['name'] }}</h3>
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

                    <!-- Description compacte -->
                    @if(isset($carrierData['config']['description']))
                        <div class="carrier-description">{{ Str::limit($carrierData['config']['description'], 80) }}</div>
                    @endif

                    <!-- Statistiques compactes -->
                    <div class="carrier-stats">
                        <div class="carrier-stat">
                            <div class="carrier-stat-number text-primary">{{ $carrierData['stats']['configurations'] }}</div>
                            <div class="carrier-stat-label">Configs</div>
                        </div>
                        <div class="carrier-stat">
                            <div class="carrier-stat-number text-success">{{ $carrierData['stats']['pickups'] }}</div>
                            <div class="carrier-stat-label">Pickups</div>
                        </div>
                        <div class="carrier-stat">
                            <div class="carrier-stat-number text-info">{{ $carrierData['stats']['shipments'] }}</div>
                            <div class="carrier-stat-label">Envois</div>
                        </div>
                    </div>

                    <!-- Configurations actives -->
                    @if($carrierData['active_configurations']->isNotEmpty())
                        <div class="carrier-configs">
                            <div class="configs-label">Configurations :</div>
                            <div class="config-badges">
                                @foreach($carrierData['active_configurations']->take(2) as $config)
                                    <span class="config-badge">{{ Str::limit($config->integration_name, 15) }}</span>
                                @endforeach
                                @if($carrierData['active_configurations']->count() > 2)
                                    <span class="config-badge">+{{ $carrierData['active_configurations']->count() - 2 }}</span>
                                @endif
                            </div>
                        </div>
                    @endif

                    <!-- Actions compactes -->
                    <div class="carrier-actions">
                        @if($carrierData['is_configured'])
                            <div class="btn-group">
                                @if($carrierData['active_configurations']->isNotEmpty())
                                    <a href="{{ route('admin.delivery.preparation') }}?carrier={{ $slug }}" 
                                       class="btn btn-success">
                                        <i class="fas fa-plus"></i>
                                        Nouvel Envoi
                                    </a>
                                    <button type="button" 
                                            class="btn btn-outline-primary"
                                            @click="testCarrierConnection('{{ $slug }}', {{ $carrierData['active_configurations']->first()->id }})">
                                        <i class="fas fa-wifi"></i>
                                        Test
                                    </button>
                                @else
                                    <a href="{{ route('admin.delivery.configuration.create') }}?carrier={{ $slug }}" 
                                       class="btn btn-warning">
                                        <i class="fas fa-power-off"></i>
                                        Activer
                                    </a>
                                @endif
                                <a href="{{ route('admin.delivery.configuration') }}?filter={{ $slug }}" 
                                   class="btn btn-outline-secondary">
                                    <i class="fas fa-cog"></i>
                                    Gérer
                                </a>
                            </div>
                        @else
                            <a href="{{ route('admin.delivery.configuration.create') }}?carrier={{ $slug }}" 
                               class="btn btn-primary w-100">
                                <i class="fas fa-plus"></i>
                                Configurer
                            </a>
                        @endif
                    </div>

                    <!-- Footer minimal -->
                    @if(isset($carrierData['config']['website']) || isset($carrierData['config']['support_phone']))
                        <div class="carrier-footer">
                            @if(isset($carrierData['config']['website']))
                                <a href="{{ $carrierData['config']['website'] }}" 
                                   target="_blank" 
                                   class="footer-link">
                                    <i class="fas fa-external-link-alt"></i>
                                    Site
                                </a>
                            @endif
                            
                            @if(isset($carrierData['config']['support_phone']))
                                <span class="footer-link">
                                    <i class="fas fa-phone"></i>
                                    Support
                                </span>
                            @endif
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    </div>

    <!-- Actions rapides compactes -->
    <div class="quick-actions-section">
        <div class="quick-actions slide-up">
            <div class="quick-actions-header">
                <h3 class="quick-actions-title">
                    <i class="fas fa-bolt"></i>
                    Actions Rapides
                </h3>
            </div>
            <div class="quick-actions-body">
                <div class="action-grid">
                    <a href="{{ route('admin.delivery.preparation') }}" class="action-item">
                        <div class="action-icon icon-success">
                            <i class="fas fa-plus"></i>
                        </div>
                        <div class="action-text">
                            <div class="action-title">Créer Enlèvement</div>
                            <div class="action-desc">Nouvelles expéditions</div>
                        </div>
                    </a>

                    <a href="{{ route('admin.delivery.pickups') }}" class="action-item">
                        <div class="action-icon icon-primary">
                            <i class="fas fa-box"></i>
                        </div>
                        <div class="action-text">
                            <div class="action-title">Gérer Enlèvements</div>
                            <div class="action-desc">Valider et suivre</div>
                        </div>
                    </a>

                    <a href="{{ route('admin.delivery.shipments') }}" class="action-item">
                        <div class="action-icon icon-info">
                            <i class="fas fa-truck"></i>
                        </div>
                        <div class="action-text">
                            <div class="action-title">Suivre Expéditions</div>
                            <div class="action-desc">Tracking temps réel</div>
                        </div>
                    </a>

                    <a href="{{ route('admin.delivery.configuration') }}" class="action-item">
                        <div class="action-icon icon-secondary">
                            <i class="fas fa-cog"></i>
                        </div>
                        <div class="action-text">
                            <div class="action-title">Configurer APIs</div>
                            <div class="action-desc">Gérer transporteurs</div>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de test compact -->
<div class="modal fade" id="testConnectionModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content" x-data="connectionTest">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-wifi"></i>
                    Test Connexion
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            
            <div class="modal-body">
                <!-- État du test -->
                <div x-show="testInProgress" class="text-center py-3">
                    <div class="spinner-border text-primary mb-2" role="status"></div>
                    <h6>Test en cours...</h6>
                    <small x-text="testMessage"></small>
                </div>

                <!-- Résultat -->
                <div x-show="testCompleted && !testInProgress">
                    <div x-show="testResult?.success" class="alert alert-success">
                        <i class="fas fa-check-circle me-2"></i>
                        <strong>Connexion réussie !</strong>
                    </div>

                    <div x-show="!testResult?.success" class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Échec connexion</strong>
                        <p x-text="testResult?.error" class="mb-0 mt-1"></p>
                    </div>
                </div>
            </div>
            
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    Fermer
                </button>
                <button x-show="testCompleted" class="btn btn-primary" @click="retestConnection()">
                    Retester
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
<script>
document.addEventListener('alpine:init', () => {
    // Composant principal optimisé
    Alpine.data('deliveryIndex', () => ({
        stats: @json($generalStats ?? []),
        
        init() {
            this.loadRealtimeStats();
            setInterval(() => this.loadRealtimeStats(), 30000);
        },

        async loadRealtimeStats() {
            try {
                const response = await fetch('{{ route("admin.delivery.api.stats") }}');
                const data = await response.json();
                this.stats = data.general_stats;
            } catch (error) {
                console.error('Erreur stats:', error);
            }
        },

        testCarrierConnection(carrierSlug, configId) {
            const modal = new bootstrap.Modal(document.getElementById('testConnectionModal'));
            modal.show();
            this.$dispatch('start-test', { configId });
        }
    }));

    // Composant test optimisé
    Alpine.data('connectionTest', () => ({
        testInProgress: false,
        testCompleted: false,
        testResult: null,
        testMessage: '',

        init() {
            this.$el.addEventListener('start-test', (e) => {
                this.startTest(e.detail.configId);
            });
        },

        async startTest(configId) {
            this.testInProgress = true;
            this.testCompleted = false;
            this.testMessage = 'Connexion...';

            try {
                await new Promise(resolve => setTimeout(resolve, 1000));
                
                const response = await fetch(`/admin/delivery/configuration/${configId}/test`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });

                const data = await response.json();
                this.testResult = data;
                this.testCompleted = true;
            } catch (error) {
                this.testResult = {
                    success: false,
                    error: 'Erreur réseau'
                };
                this.testCompleted = true;
            } finally {
                this.testInProgress = false;
            }
        },

        retestConnection() {
            // Logique de re-test
        }
    }));
});

// Optimisations performance
$(document).ready(function() {
    // Animation intersection observer optimisé
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('fade-in');
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.1 });

    $('.carrier-card, .stat-card').each(function() {
        observer.observe(this);
    });

    // Animation compteurs optimisée
    $('.stat-number').each(function() {
        const $this = $(this);
        const countTo = parseInt($this.text()) || 0;
        
        if (countTo > 0) {
            let current = 0;
            const increment = countTo / 30;
            const timer = setInterval(() => {
                current += increment;
                if (current >= countTo) {
                    current = countTo;
                    clearInterval(timer);
                }
                $this.text(Math.floor(current));
            }, 50);
        }
    });

    console.log('✅ Page livraison optimisée chargée');
});
</script>
@endsection