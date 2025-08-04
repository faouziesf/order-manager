@extends('layouts.admin')

@section('title', 'Modifier Configuration - ' . $config->integration_name)

@section('css')
<style>
    :root {
        --primary: #1e40af;
        --primary-dark: #1e3a8a;
        --primary-light: #3b82f6;
        --success: #059669;
        --warning: #d97706;
        --danger: #dc2626;
        --info: #0891b2;
        --light: #f8fafc;
        --dark: #374151;
        --border: #e5e7eb;
        --shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        --shadow-lg: 0 4px 6px rgba(0, 0, 0, 0.15);
        --radius: 8px;
        --transition: all 0.2s ease;
    }

    body {
        background: linear-gradient(135deg, #f1f5f9 0%, #e2e8f0 100%);
        font-family: 'Inter', system-ui, -apple-system, sans-serif;
        font-size: 14px;
    }

    /* ===== CONTAINER PRINCIPAL ===== */
    .config-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 1rem;
    }

    /* ===== HEADER COMPACT ===== */
    .config-header {
        background: white;
        border-radius: var(--radius);
        box-shadow: var(--shadow);
        padding: 1.5rem;
        margin-bottom: 1.5rem;
        border-left: 4px solid var(--primary);
    }

    .header-content {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 1rem;
    }

    .header-info h1 {
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--dark);
        margin-bottom: 0.25rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .header-info p {
        color: #6b7280;
        margin: 0;
        font-size: 0.9rem;
    }

    .header-actions {
        display: flex;
        gap: 0.75rem;
        flex-wrap: wrap;
    }

    .status-badge {
        padding: 0.5rem 1rem;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 0.4rem;
    }

    .status-active {
        background: linear-gradient(135deg, #dcfce7, #bbf7d0);
        color: #166534;
    }

    .status-inactive {
        background: linear-gradient(135deg, #fef3c7, #fde68a);
        color: #92400e;
    }

    /* ===== LAYOUT PRINCIPAL ===== */
    .config-layout {
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 1.5rem;
    }

    /* ===== FORMULAIRE PRINCIPAL ===== */
    .config-form {
        background: white;
        border-radius: var(--radius);
        box-shadow: var(--shadow);
        overflow: hidden;
    }

    .form-header {
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
        color: white;
        padding: 1rem 1.5rem;
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .carrier-logo {
        width: 40px;
        height: 40px;
        object-fit: contain;
        background: rgba(255, 255, 255, 0.2);
        border-radius: 6px;
        padding: 6px;
        border: 1px solid rgba(255, 255, 255, 0.3);
    }

    .form-body {
        padding: 1.5rem;
    }

    .form-group {
        margin-bottom: 1.5rem;
    }

    .form-label {
        display: block;
        font-weight: 600;
        color: var(--dark);
        margin-bottom: 0.5rem;
        font-size: 0.9rem;
    }

    .form-control {
        width: 100%;
        padding: 0.75rem;
        border: 2px solid var(--border);
        border-radius: var(--radius);
        font-size: 0.9rem;
        transition: var(--transition);
        background: white;
    }

    .form-control:focus {
        outline: none;
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(30, 64, 175, 0.1);
    }

    .form-control.is-invalid {
        border-color: var(--danger);
    }

    .input-group {
        display: flex;
        align-items: stretch;
    }

    .input-group .form-control {
        border-top-right-radius: 0;
        border-bottom-right-radius: 0;
        border-right: none;
    }

    .input-group-append {
        display: flex;
    }

    .input-group-text {
        background: #f3f4f6;
        border: 2px solid var(--border);
        border-left: none;
        border-top-right-radius: var(--radius);
        border-bottom-right-radius: var(--radius);
        padding: 0.75rem;
        cursor: pointer;
        transition: var(--transition);
    }

    .input-group-text:hover {
        background: #e5e7eb;
    }

    .form-text {
        font-size: 0.8rem;
        color: #6b7280;
        margin-top: 0.25rem;
    }

    .invalid-feedback {
        color: var(--danger);
        font-size: 0.8rem;
        margin-top: 0.25rem;
    }

    .alert {
        padding: 1rem;
        border-radius: var(--radius);
        margin-bottom: 1.5rem;
        display: flex;
        align-items: center;
        gap: 0.75rem;
        border: none;
    }

    .alert-success {
        background: linear-gradient(135deg, #dcfce7, #bbf7d0);
        color: #166534;
    }

    .alert-danger {
        background: linear-gradient(135deg, #fee2e2, #fecaca);
        color: #991b1b;
    }

    /* ===== INFO CARD ===== */
    .info-card {
        background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
        border-radius: var(--radius);
        padding: 1.5rem;
        border: 1px solid var(--border);
    }

    .info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1rem;
    }

    .info-item {
        display: flex;
        flex-direction: column;
        gap: 0.25rem;
    }

    .info-label {
        font-weight: 600;
        color: var(--dark);
        font-size: 0.8rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .info-value {
        color: #6b7280;
        font-size: 0.9rem;
    }

    /* ===== ACTIONS FOOTER ===== */
    .form-actions {
        background: #f9fafb;
        padding: 1.5rem;
        border-top: 1px solid var(--border);
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 1rem;
    }

    .btn {
        padding: 0.75rem 1.5rem;
        border-radius: var(--radius);
        font-weight: 600;
        font-size: 0.9rem;
        text-decoration: none;
        text-align: center;
        transition: var(--transition);
        border: none;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
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
        background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
        transition: left 0.5s ease;
    }

    .btn:hover::before {
        left: 100%;
    }

    .btn:hover {
        transform: translateY(-1px);
        text-decoration: none;
    }

    .btn:disabled {
        opacity: 0.6;
        cursor: not-allowed;
        transform: none !important;
    }

    .btn-primary {
        background: linear-gradient(135deg, var(--primary), var(--primary-dark));
        color: white;
        box-shadow: 0 2px 4px rgba(30, 64, 175, 0.3);
    }

    .btn-success {
        background: linear-gradient(135deg, var(--success), #047857);
        color: white;
        box-shadow: 0 2px 4px rgba(5, 150, 105, 0.3);
    }

    .btn-warning {
        background: linear-gradient(135deg, var(--warning), #b45309);
        color: white;
        box-shadow: 0 2px 4px rgba(217, 119, 6, 0.3);
    }

    .btn-danger {
        background: linear-gradient(135deg, var(--danger), #b91c1c);
        color: white;
        box-shadow: 0 2px 4px rgba(220, 38, 38, 0.3);
    }

    .btn-outline {
        background: white;
        color: var(--dark);
        border: 2px solid var(--border);
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    }

    .btn-outline:hover {
        background: var(--primary);
        color: white;
        border-color: var(--primary);
    }

    /* ===== SIDEBAR ===== */
    .sidebar {
        display: flex;
        flex-direction: column;
        gap: 1.5rem;
    }

    .sidebar-card {
        background: white;
        border-radius: var(--radius);
        box-shadow: var(--shadow);
        overflow: hidden;
    }

    .sidebar-header {
        background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
        padding: 1rem 1.5rem;
        border-bottom: 1px solid var(--border);
    }

    .sidebar-title {
        font-size: 1rem;
        font-weight: 700;
        color: var(--dark);
        margin: 0;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .sidebar-body {
        padding: 1.5rem;
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 1rem;
        text-align: center;
    }

    .stat-item {
        padding: 1rem;
        background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
        border-radius: var(--radius);
        border: 1px solid var(--border);
    }

    .stat-number {
        font-size: 1.5rem;
        font-weight: 800;
        color: var(--primary);
        margin-bottom: 0.25rem;
    }

    .stat-label {
        color: #6b7280;
        font-size: 0.8rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .action-list {
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
    }

    .info-list {
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
    }

    .info-link {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        color: var(--dark);
        text-decoration: none;
        transition: var(--transition);
        font-size: 0.9rem;
    }

    .info-link:hover {
        color: var(--primary);
        text-decoration: none;
    }

    .limits-info {
        background: #f8fafc;
        border-radius: var(--radius);
        padding: 1rem;
        margin-top: 1rem;
        border-left: 3px solid var(--info);
    }

    .limits-title {
        font-weight: 600;
        color: var(--dark);
        margin-bottom: 0.5rem;
        font-size: 0.9rem;
    }

    .limits-list {
        color: #6b7280;
        font-size: 0.8rem;
        line-height: 1.6;
    }

    /* ===== NOTIFICATIONS TOAST ===== */
    .toast {
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 10000;
        min-width: 300px;
        max-width: 400px;
        box-shadow: var(--shadow-lg);
        border-radius: var(--radius);
        overflow: hidden;
        animation: slideInRight 0.3s ease;
    }

    .toast-success {
        background: linear-gradient(135deg, var(--success), #047857);
        color: white;
    }

    .toast-danger {
        background: linear-gradient(135deg, var(--danger), #b91c1c);
        color: white;
    }

    .toast-warning {
        background: linear-gradient(135deg, var(--warning), #b45309);
        color: white;
    }

    .toast-content {
        padding: 1rem;
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    @keyframes slideInRight {
        from { transform: translateX(100%); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }

    @keyframes slideOutRight {
        from { transform: translateX(0); opacity: 1; }
        to { transform: translateX(100%); opacity: 0; }
    }

    /* ===== RESPONSIVE ===== */
    @media (max-width: 768px) {
        .config-container {
            padding: 0.5rem;
        }

        .config-header {
            padding: 1rem;
        }

        .header-content {
            flex-direction: column;
            align-items: stretch;
        }

        .header-actions {
            justify-content: center;
        }

        .config-layout {
            grid-template-columns: 1fr;
            gap: 1rem;
        }

        .form-body {
            padding: 1rem;
        }

        .form-actions {
            padding: 1rem;
            flex-direction: column;
            align-items: stretch;
        }

        .form-actions > div {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }

        .stats-grid {
            grid-template-columns: 1fr;
        }

        .info-grid {
            grid-template-columns: 1fr;
        }

        .sidebar-body {
            padding: 1rem;
        }
    }

    /* ===== LOADING STATES ===== */
    .loading {
        position: relative;
        pointer-events: none;
        opacity: 0.7;
    }

    .loading::after {
        content: '';
        position: absolute;
        top: 50%;
        left: 50%;
        width: 20px;
        height: 20px;
        margin: -10px 0 0 -10px;
        border: 2px solid transparent;
        border-top: 2px solid currentColor;
        border-radius: 50%;
        animation: spin 0.8s linear infinite;
    }

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
</style>
@endsection

@section('content')
<div class="config-container">
    <!-- Header -->
    <div class="config-header">
        <div class="header-content">
            <div class="header-info">
                <h1>
                    <i class="fas fa-edit text-primary"></i>
                    Modifier la Configuration
                </h1>
                <p>{{ $config->integration_name }} • {{ $carrier['name'] }}</p>
            </div>
            <div class="header-actions">
                <a href="{{ route('admin.delivery.configuration') }}" class="btn btn-outline">
                    <i class="fas fa-arrow-left"></i>
                    Retour
                </a>
                <div class="status-badge {{ $config->is_active ? 'status-active' : 'status-inactive' }}">
                    <i class="fas fa-{{ $config->is_active ? 'check-circle' : 'pause-circle' }}"></i>
                    {{ $config->is_active ? 'Actif' : 'Inactif' }}
                </div>
            </div>
        </div>
    </div>

    <div class="config-layout">
        <!-- Formulaire Principal -->
        <div class="config-form">
            <div class="form-header">
                @if(isset($carrier['logo']))
                    <img src="{{ asset($carrier['logo']) }}" 
                         alt="{{ $carrier['name'] }}" 
                         class="carrier-logo"
                         onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                    <div class="carrier-logo" style="display: none;">
                        <i class="fas fa-truck"></i>
                    </div>
                @else
                    <div class="carrier-logo">
                        <i class="fas fa-truck"></i>
                    </div>
                @endif
                
                <div>
                    <h3 style="margin: 0; font-size: 1.1rem; font-weight: 700;">{{ $carrier['name'] }}</h3>
                    <small style="opacity: 0.8;">Configuration ID: {{ $config->id }}</small>
                </div>
            </div>

            <form id="configForm" action="{{ route('admin.delivery.configuration.update', $config) }}" method="POST">
                @csrf
                @method('PATCH')
                
                <div class="form-body">
                    @if(session('success'))
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle"></i>
                            <span>{{ session('success') }}</span>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-triangle"></i>
                            <span>{{ session('error') }}</span>
                        </div>
                    @endif

                    <div class="form-group">
                        <label for="integration_name" class="form-label">
                            <i class="fas fa-tag text-primary"></i>
                            Nom de la Configuration <span style="color: var(--danger);">*</span>
                        </label>
                        <input type="text" 
                               class="form-control @error('integration_name') is-invalid @enderror" 
                               id="integration_name" 
                               name="integration_name" 
                               value="{{ old('integration_name', $config->integration_name) }}"
                               required>
                        <div class="form-text">Nom unique pour identifier cette configuration</div>
                        @error('integration_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    @if($config->carrier_slug === 'jax_delivery')
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                            <div class="form-group">
                                <label for="username" class="form-label">
                                    <i class="fas fa-user text-primary"></i>
                                    Numéro de Compte <span style="color: var(--danger);">*</span>
                                </label>
                                <input type="text" 
                                       class="form-control @error('username') is-invalid @enderror" 
                                       id="username" 
                                       name="username" 
                                       value="{{ old('username', $config->username) }}"
                                       required>
                                @error('username')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="form-group">
                                <label for="password" class="form-label">
                                    <i class="fas fa-key text-primary"></i>
                                    Token API JWT
                                </label>
                                <div class="input-group">
                                    <input type="password" 
                                           class="form-control @error('password') is-invalid @enderror" 
                                           id="password" 
                                           name="password" 
                                           placeholder="Laisser vide pour conserver">
                                    <div class="input-group-append">
                                        <div class="input-group-text" onclick="togglePassword()">
                                            <i class="fas fa-eye" id="toggleIcon"></i>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-text">Remplir seulement pour changer le token</div>
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    @elseif($config->carrier_slug === 'mes_colis')
                        <div class="form-group">
                            <label for="username" class="form-label">
                                <i class="fas fa-key text-primary"></i>
                                Token API <span style="color: var(--danger);">*</span>
                            </label>
                            <div class="input-group">
                                <input type="text" 
                                       class="form-control @error('username') is-invalid @enderror" 
                                       id="username" 
                                       name="username" 
                                       value="{{ old('username', $config->username) }}"
                                       required>
                                <div class="input-group-append">
                                    <div class="input-group-text" onclick="togglePassword()">
                                        <i class="fas fa-eye" id="toggleIcon"></i>
                                    </div>
                                </div>
                            </div>
                            @error('username')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    @endif

                    <!-- Informations Configuration -->
                    <div class="info-card">
                        <h6 style="margin-bottom: 1rem; color: var(--dark); font-weight: 700;">
                            <i class="fas fa-info-circle text-primary"></i>
                            Informations de la Configuration
                        </h6>
                        <div class="info-grid">
                            <div class="info-item">
                                <div class="info-label">Statut</div>
                                <div class="info-value">
                                    <span class="status-badge {{ $config->is_active ? 'status-active' : 'status-inactive' }}" style="padding: 0.25rem 0.5rem; font-size: 0.75rem;">
                                        <i class="fas fa-{{ $config->is_active ? 'check' : 'pause' }}"></i>
                                        {{ $config->is_active ? 'Actif' : 'Inactif' }}
                                    </span>
                                </div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">Environnement</div>
                                <div class="info-value">{{ ucfirst($config->environment) }}</div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">Créée le</div>
                                <div class="info-value">{{ $config->created_at->format('d/m/Y à H:i') }}</div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">Modifiée le</div>
                                <div class="info-value">{{ $config->updated_at->format('d/m/Y à H:i') }}</div>
                            </div>
                        </div>


                    </div>
                </div>

                <div class="form-actions">
                    <div>
                        <button type="button" 
                                class="btn btn-danger"
                                onclick="deleteConfig()"
                                id="deleteBtn">
                            <i class="fas fa-trash"></i>
                            Supprimer
                        </button>
                    </div>
                    
                    <div style="display: flex; gap: 0.75rem; flex-wrap: wrap;">
                        <button type="button" 
                                class="btn btn-{{ $config->is_active ? 'warning' : 'success' }}"
                                onclick="toggleStatus()"
                                id="toggleBtn">
                            <i class="fas fa-{{ $config->is_active ? 'pause' : 'play' }}"></i>
                            {{ $config->is_active ? 'Désactiver' : 'Activer' }}
                        </button>
                        
                        <button type="submit" 
                                class="btn btn-primary"
                                id="saveBtn">
                            <i class="fas fa-save"></i>
                            Sauvegarder
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Sidebar -->
        <div class="sidebar">
            <!-- Statistiques -->
            <div class="sidebar-card">
                <div class="sidebar-header">
                    <h3 class="sidebar-title">
                        <i class="fas fa-chart-bar text-primary"></i>
                        Statistiques
                    </h3>
                </div>
                <div class="sidebar-body">
                    <div class="stats-grid">
                        <div class="stat-item">
                            <div class="stat-number">{{ $config->pickups()->count() }}</div>
                            <div class="stat-label">Enlèvements</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-number">{{ $config->shipments()->count() }}</div>
                            <div class="stat-label">Expéditions</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Actions Rapides -->
            <div class="sidebar-card">
                <div class="sidebar-header">
                    <h3 class="sidebar-title">
                        <i class="fas fa-rocket text-success"></i>
                        Actions Rapides
                    </h3>
                </div>
                <div class="sidebar-body">
                    <div class="action-list">
                        @if($config->is_active)
                            <a href="{{ route('admin.delivery.preparation') }}?config_id={{ $config->id }}" 
                               class="btn btn-success">
                                <i class="fas fa-plus"></i>
                                Nouvel Enlèvement
                            </a>
                        @endif
                        
                        <a href="{{ route('admin.delivery.pickups') }}?config_id={{ $config->id }}" 
                           class="btn btn-outline">
                            <i class="fas fa-boxes"></i>
                            Voir Enlèvements
                        </a>
                        
                        <a href="{{ route('admin.delivery.shipments') }}?config_id={{ $config->id }}" 
                           class="btn btn-outline">
                            <i class="fas fa-shipping-fast"></i>
                            Voir Expéditions
                        </a>
                    </div>
                </div>
            </div>

            <!-- Informations Transporteur -->
            <div class="sidebar-card">
                <div class="sidebar-header">
                    <h3 class="sidebar-title">
                        <i class="fas fa-info-circle text-info"></i>
                        Transporteur
                    </h3>
                </div>
                <div class="sidebar-body">
                    <div class="info-list">
                        @if(isset($carrier['website']))
                            <a href="{{ $carrier['website'] }}" target="_blank" class="info-link">
                                <i class="fas fa-globe text-primary"></i>
                                Site Web
                            </a>
                        @endif
                        
                        @if(isset($carrier['support_phone']))
                            <a href="tel:{{ $carrier['support_phone'] }}" class="info-link">
                                <i class="fas fa-phone text-success"></i>
                                {{ $carrier['support_phone'] }}
                            </a>
                        @endif
                        
                        @if(isset($carrier['support_email']))
                            <a href="mailto:{{ $carrier['support_email'] }}" class="info-link">
                                <i class="fas fa-envelope text-info"></i>
                                Support
                            </a>
                        @endif
                    </div>

                    <div class="limits-info">
                        <div class="limits-title">
                            <i class="fas fa-exclamation-triangle text-warning"></i>
                            Limites
                        </div>
                        <div class="limits-list">
                            • Poids max: {{ $carrier['limits']['max_weight'] ?? 'N/A' }} kg<br>
                            • COD max: {{ number_format($carrier['limits']['max_cod_amount'] ?? 0, 0, '.', ' ') }} TND
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
// ===== CONFIGURATION =====
const CONFIG = {
    id: {{ $config->id }},
    isActive: {{ $config->is_active ? 'true' : 'false' }},
    csrfToken: document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
};

// ===== INITIALISATION =====
document.addEventListener('DOMContentLoaded', function() {
    console.log('🚀 Config Edit Initialized');
    setupFormHandler();
});

// ===== TOGGLE PASSWORD VISIBILITY =====
function togglePassword() {
    const input = document.getElementById('password') || document.getElementById('username');
    const icon = document.getElementById('toggleIcon');
    
    if (input.type === 'password') {
        input.type = 'text';
        icon.className = 'fas fa-eye-slash';
    } else {
        input.type = 'password';
        icon.className = 'fas fa-eye';
    }
}

// ===== GESTION FORMULAIRE =====
function setupFormHandler() {
    const form = document.getElementById('configForm');
    form.addEventListener('submit', handleFormSubmit);
}

async function handleFormSubmit(e) {
    e.preventDefault();
    
    const btn = document.getElementById('saveBtn');
    const originalText = btn.innerHTML;
    
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sauvegarde...';
    
    try {
        const formData = new FormData(e.target);
        
        const response = await fetch(e.target.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': CONFIG.csrfToken
            }
        });
        
        const data = await response.json();
        
        if (data.success) {
            showToast('success', data.message || 'Configuration mise à jour !');
            setTimeout(() => window.location.reload(), 2000);
        } else {
            throw new Error(data.message || 'Erreur de sauvegarde');
        }
    } catch (error) {
        showToast('danger', error.message);
        btn.disabled = false;
        btn.innerHTML = originalText;
    }
}

// ===== TOGGLE STATUS =====
async function toggleStatus() {
    const btn = document.getElementById('toggleBtn');
    const originalText = btn.innerHTML;
    const action = CONFIG.isActive ? 'désactiver' : 'activer';
    
    if (!confirm(`Voulez-vous vraiment ${action} cette configuration ?`)) {
        return;
    }
    
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Mise à jour...';
    
    try {
        const response = await fetch(`/admin/delivery/configuration/${CONFIG.id}/toggle`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': CONFIG.csrfToken
            }
        });
        
        const data = await response.json();
        
        if (data.success) {
            showToast('success', data.message);
            setTimeout(() => window.location.reload(), 1500);
        } else {
            throw new Error(data.message || 'Impossible de changer le statut');
        }
    } catch (error) {
        showToast('danger', error.message);
        btn.disabled = false;
        btn.innerHTML = originalText;
    }
}

// ===== DELETE CONFIG =====
async function deleteConfig() {
    const btn = document.getElementById('deleteBtn');
    const originalText = btn.innerHTML;
    
    if (!confirm('⚠️ Voulez-vous vraiment supprimer cette configuration ?\n\nCette action est irréversible !')) {
        return;
    }
    
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Suppression...';
    
    try {
        const response = await fetch(`/admin/delivery/configuration/${CONFIG.id}`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': CONFIG.csrfToken
            }
        });
        
        const data = await response.json();
        
        if (data.success) {
            showToast('success', 'Configuration supprimée !');
            setTimeout(() => {
                window.location.href = '{{ route("admin.delivery.configuration") }}';
            }, 2000);
        } else {
            throw new Error(data.error || 'Impossible de supprimer');
        }
    } catch (error) {
        showToast('danger', error.message);
        btn.disabled = false;
        btn.innerHTML = originalText;
    }
}

// ===== NOTIFICATIONS TOAST =====
function showToast(type, message) {
    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    
    toast.innerHTML = `
        <div class="toast-content">
            <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'warning' ? 'exclamation-triangle' : 'times-circle'}"></i>
            <span>${message}</span>
        </div>
    `;
    
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.style.animation = 'slideOutRight 0.3s ease';
        setTimeout(() => toast.remove(), 300);
    }, 4000);
}

console.log('✅ Config Edit Scripts Loaded');
</script>
@endsection