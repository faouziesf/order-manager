@extends('layouts.admin')

@section('title', 'Nouvelle Configuration - ' . $carrier['name'])

@section('css')
<style>
    :root {
        --primary: #1e40af;
        --primary-dark: #1e3a8a;
        --primary-light: #3b82f6;
        --success: #10b981;
        --warning: #f59e0b;
        --danger: #ef4444;
        --info: #06b6d4;
        --light: #f8fafc;
        --dark: #1f2937;
        --border: #e5e7eb;
        --shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        --shadow-lg: 0 10px 25px rgba(0, 0, 0, 0.15);
        --radius: 12px;
        --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    body {
        background: linear-gradient(135deg, #f1f5f9 0%, #e2e8f0 100%);
        font-family: 'Inter', system-ui, sans-serif;
    }

    /* ===== CONTAINER PRINCIPAL ===== */
    .config-container {
        background: white;
        border-radius: var(--radius);
        box-shadow: var(--shadow);
        margin: 1rem;
        min-height: calc(100vh - 100px);
        overflow: hidden;
    }

    /* ===== HEADER MODERNE ===== */
    .config-header {
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
        padding: 2rem;
        color: white;
        position: relative;
        overflow: hidden;
    }

    .config-header::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -20%;
        width: 200px;
        height: 200px;
        background: rgba(255, 255, 255, 0.1);
        border-radius: 50%;
        animation: float 6s ease-in-out infinite;
    }

    @keyframes float {
        0%, 100% { transform: translateY(0px) rotate(0deg); }
        50% { transform: translateY(-20px) rotate(180deg); }
    }

    .header-content {
        position: relative;
        z-index: 2;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 1rem;
    }

    .header-info h1 {
        font-size: 1.8rem;
        font-weight: 800;
        margin-bottom: 0.5rem;
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .header-info p {
        opacity: 0.9;
        margin: 0;
        font-size: 1rem;
        font-weight: 500;
    }

    .btn-back {
        background: rgba(255, 255, 255, 0.2);
        color: white;
        border: 2px solid rgba(255, 255, 255, 0.3);
        padding: 0.75rem 1.25rem;
        border-radius: 8px;
        text-decoration: none;
        font-weight: 600;
        font-size: 0.9rem;
        transition: var(--transition);
        display: flex;
        align-items: center;
        gap: 0.5rem;
        backdrop-filter: blur(10px);
    }

    .btn-back:hover {
        background: rgba(255, 255, 255, 0.3);
        color: white;
        text-decoration: none;
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
    }

    /* ===== LAYOUT GRID ===== */
    .form-section {
        padding: 2rem;
    }

    .form-grid {
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 2rem;
    }

    /* ===== FORMULAIRE PRINCIPAL ===== */
    .form-main {
        background: white;
        border-radius: var(--radius);
        border: 1px solid var(--border);
        overflow: hidden;
        box-shadow: var(--shadow);
    }

    .form-header {
        background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
        padding: 1.5rem;
        border-bottom: 1px solid var(--border);
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .carrier-logo {
        width: 56px;
        height: 56px;
        background: linear-gradient(135deg, #f3f4f6, #e5e7eb);
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        object-fit: contain;
        padding: 12px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }

    .carrier-info h3 {
        font-size: 1.25rem;
        font-weight: 700;
        color: var(--dark);
        margin-bottom: 0.25rem;
    }

    .carrier-info p {
        color: #6b7280;
        font-size: 0.9rem;
        margin: 0;
    }

    .form-body {
        padding: 2rem;
    }

    .form-group {
        margin-bottom: 2rem;
    }

    .form-label {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-weight: 600;
        color: var(--dark);
        margin-bottom: 0.75rem;
        font-size: 0.9rem;
    }

    .form-label .required {
        color: var(--danger);
        font-size: 1.1rem;
    }

    .form-control {
        width: 100%;
        padding: 1rem;
        border: 2px solid var(--border);
        border-radius: 8px;
        font-size: 0.9rem;
        transition: var(--transition);
        background: white;
        font-family: inherit;
    }

    .form-control:focus {
        outline: none;
        border-color: var(--primary);
        box-shadow: 0 0 0 4px rgba(30, 64, 175, 0.1);
        transform: translateY(-1px);
    }

    .form-control.error {
        border-color: var(--danger);
        background-color: #fef2f2;
    }

    .form-control.success {
        border-color: var(--success);
        background-color: #ecfdf5;
    }

    .form-help {
        margin-top: 0.5rem;
        font-size: 0.8rem;
        color: #6b7280;
        display: flex;
        align-items: flex-start;
        gap: 0.5rem;
        line-height: 1.4;
    }

    .form-error {
        margin-top: 0.75rem;
        font-size: 0.85rem;
        color: var(--danger);
        background: #fef2f2;
        padding: 0.75rem;
        border-radius: 8px;
        border-left: 4px solid var(--danger);
        display: flex;
        align-items: flex-start;
        gap: 0.5rem;
        font-weight: 500;
        animation: slideInDown 0.3s ease;
    }

    .form-success {
        margin-top: 0.75rem;
        font-size: 0.85rem;
        color: var(--success);
        background: #ecfdf5;
        padding: 0.75rem;
        border-radius: 8px;
        border-left: 4px solid var(--success);
        display: flex;
        align-items: flex-start;
        gap: 0.5rem;
        font-weight: 500;
        animation: slideInDown 0.3s ease;
    }

    @keyframes slideInDown {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
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

    .input-addon {
        background: linear-gradient(135deg, #f3f4f6, #e5e7eb);
        border: 2px solid var(--border);
        border-left: none;
        border-top-right-radius: 8px;
        border-bottom-right-radius: 8px;
        padding: 1rem;
        display: flex;
        align-items: center;
        cursor: pointer;
        transition: var(--transition);
        min-width: 48px;
        justify-content: center;
    }

    .input-addon:hover {
        background: linear-gradient(135deg, #e5e7eb, #d1d5db);
        transform: scale(1.05);
    }

    /* ===== BOUTONS ===== */
    .form-actions {
        padding: 2rem;
        background: linear-gradient(135deg, #f9fafb, #f3f4f6);
        border-top: 1px solid var(--border);
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 1rem;
    }

    .btn {
        padding: 0.875rem 1.75rem;
        border-radius: 8px;
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
        min-width: 140px;
    }

    .btn:hover {
        transform: translateY(-2px);
        text-decoration: none;
    }

    .btn-primary {
        background: linear-gradient(135deg, var(--primary), var(--primary-light));
        color: white;
        box-shadow: 0 4px 15px rgba(30, 64, 175, 0.3);
    }

    .btn-primary:hover {
        box-shadow: 0 8px 25px rgba(30, 64, 175, 0.4);
    }

    .btn-secondary {
        background: linear-gradient(135deg, #6b7280, #4b5563);
        color: white;
        box-shadow: 0 4px 15px rgba(107, 114, 128, 0.3);
    }

    .btn:disabled {
        opacity: 0.6;
        cursor: not-allowed;
        transform: none !important;
    }

    .btn.loading i {
        animation: spin 1s linear infinite;
    }

    @keyframes spin {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }

    /* ===== SIDEBAR ===== */
    .help-sidebar {
        display: flex;
        flex-direction: column;
        gap: 1.5rem;
    }

    .help-card {
        background: white;
        border-radius: var(--radius);
        border: 1px solid var(--border);
        overflow: hidden;
        box-shadow: var(--shadow);
        transition: var(--transition);
    }

    .help-card:hover {
        transform: translateY(-2px);
        box-shadow: var(--shadow-lg);
    }

    .help-header {
        background: linear-gradient(135deg, var(--primary), var(--primary-light));
        color: white;
        padding: 1rem 1.5rem;
        font-weight: 600;
        font-size: 0.9rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .help-body {
        padding: 1.5rem;
    }

    .help-list {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .help-list li {
        padding: 0.75rem 0;
        font-size: 0.85rem;
        color: #6b7280;
        display: flex;
        align-items: center;
        gap: 0.75rem;
        border-bottom: 1px solid #f3f4f6;
        transition: var(--transition);
    }

    .help-list li:hover {
        color: var(--primary);
        background: #f8fafc;
        margin: 0 -1.5rem;
        padding-left: 1.5rem;
        padding-right: 1.5rem;
    }

    .help-list li:last-child {
        border-bottom: none;
    }

    .spec-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 1rem;
        margin-top: 1.5rem;
    }

    .spec-item {
        background: linear-gradient(135deg, #f8fafc, #f1f5f9);
        padding: 1rem;
        border-radius: 8px;
        text-align: center;
        border: 1px solid #e5e7eb;
        transition: var(--transition);
    }

    .spec-item:hover {
        transform: scale(1.05);
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    }

    .spec-value {
        font-size: 1.25rem;
        font-weight: 800;
        color: var(--primary);
        display: block;
        margin-bottom: 0.25rem;
    }

    .spec-label {
        color: #6b7280;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    /* ===== NOTIFICATIONS ===== */
    .notification {
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 10001;
        min-width: 350px;
        max-width: 450px;
        padding: 1.25rem;
        border-radius: 8px;
        color: white;
        font-weight: 600;
        font-size: 0.9rem;
        box-shadow: var(--shadow-lg);
        animation: slideInRight 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        display: flex;
        align-items: center;
        gap: 0.75rem;
        backdrop-filter: blur(10px);
    }

    .notification.success { 
        background: linear-gradient(135deg, var(--success), #047857); 
    }
    .notification.error { 
        background: linear-gradient(135deg, var(--danger), #dc2626); 
    }
    .notification.warning { 
        background: linear-gradient(135deg, var(--warning), #d97706); 
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
            margin: 0.5rem;
        }

        .config-header {
            padding: 1.5rem;
        }

        .header-content {
            flex-direction: column;
            text-align: center;
        }

        .header-info h1 {
            font-size: 1.5rem;
        }

        .form-section {
            padding: 1rem;
        }

        .form-grid {
            grid-template-columns: 1fr;
            gap: 1rem;
        }

        .form-body {
            padding: 1.5rem;
        }

        .form-actions {
            padding: 1.5rem;
            flex-direction: column;
        }

        .form-actions .btn {
            width: 100%;
        }

        .spec-grid {
            grid-template-columns: 1fr;
        }
    }

    /* ===== ANIMATIONS ===== */
    .fade-in {
        animation: fadeIn 0.6s cubic-bezier(0.4, 0, 0.2, 1);
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }

    /* ===== CHAMPS SPÉCIALISÉS ===== */
    .two-columns {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1.5rem;
    }

    @media (max-width: 768px) {
        .two-columns {
            grid-template-columns: 1fr;
        }
    }
</style>
@endsection

@section('content')
<div class="config-container fade-in">
    <!-- Header -->
    <div class="config-header">
        <div class="header-content">
            <div class="header-info">
                <h1>
                    <i class="fas fa-plus-circle"></i>
                    Nouvelle Configuration
                </h1>
                <p>Configuration de {{ $carrier['name'] }} pour vos expéditions</p>
            </div>
            <a href="{{ route('admin.delivery.configuration') }}" class="btn-back">
                <i class="fas fa-arrow-left"></i>
                Retour à la liste
            </a>
        </div>
    </div>

    <!-- Formulaire -->
    <div class="form-section">
        <div class="form-grid">
            <!-- Formulaire principal -->
            <div class="form-main">
                <div class="form-header">
                    @if(isset($carrier['logo']))
                        <img src="{{ asset($carrier['logo']) }}" 
                             alt="{{ $carrier['name'] }}" 
                             class="carrier-logo"
                             onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                    @endif
                    <div class="carrier-logo" style="{{ isset($carrier['logo']) ? 'display: none;' : '' }}">
                        <i class="fas fa-truck" style="color: #6b7280; font-size: 1.5rem;"></i>
                    </div>
                    
                    <div class="carrier-info">
                        <h3>{{ $carrier['name'] }}</h3>
                        <p>{{ $carrier['description'] ?? 'Configuration des paramètres de connexion' }}</p>
                    </div>
                </div>

                <form id="configForm" novalidate>
                    @csrf
                    <input type="hidden" name="carrier_slug" value="{{ $carrierSlug }}">
                    
                    <div class="form-body">
                        <!-- Nom de la configuration -->
                        <div class="form-group">
                            <label for="integration_name" class="form-label">
                                <i class="fas fa-tag"></i>
                                Nom de la Configuration <span class="required">*</span>
                            </label>
                            <input type="text" 
                                   class="form-control" 
                                   id="integration_name" 
                                   name="integration_name" 
                                   value="{{ old('integration_name') }}"
                                   placeholder="Ex: Boutique Principale, Entrepôt Tunis..."
                                   required>
                            <div class="form-help">
                                <i class="fas fa-info-circle"></i>
                                Nom unique pour identifier cette configuration dans votre système
                            </div>
                        </div>

                        @if($carrierSlug === 'jax_delivery')
                            <!-- Configuration JAX Delivery -->
                            <div class="two-columns">
                                <div class="form-group">
                                    <label for="username" class="form-label">
                                        <i class="fas fa-user-circle"></i>
                                        Numéro de Compte JAX <span class="required">*</span>
                                    </label>
                                    <input type="text" 
                                           class="form-control" 
                                           id="username" 
                                           name="username" 
                                           value="{{ old('username') }}"
                                           placeholder="Ex: 2304"
                                           required>
                                    <div class="form-help">
                                        <i class="fas fa-info-circle"></i>
                                        Numéro de compte fourni lors de votre inscription JAX Delivery
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="password" class="form-label">
                                        <i class="fas fa-key"></i>
                                        Token JWT JAX <span class="required">*</span>
                                    </label>
                                    <div class="input-group">
                                        <input type="password" 
                                               class="form-control" 
                                               id="password" 
                                               name="password" 
                                               value="{{ old('password') }}"
                                               placeholder="eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..."
                                               maxlength="500"
                                               required>
                                        <div class="input-addon" onclick="togglePassword()">
                                            <i class="fas fa-eye" id="passwordToggle"></i>
                                        </div>
                                    </div>
                                    <div class="form-help">
                                        <i class="fas fa-info-circle"></i>
                                        Token JWT généré dans votre espace client JAX (Bearer Token)
                                    </div>
                                </div>
                            </div>

                        @elseif($carrierSlug === 'mes_colis')
                            <!-- 🔥 CORRECTION : Configuration Mes Colis Unifiée -->
                            <div class="form-group">
                                <label for="username" class="form-label">
                                    <i class="fas fa-key"></i>
                                    Token d'Accès Mes Colis <span class="required">*</span>
                                </label>
                                <div class="input-group">
                                    <input type="text" 
                                           class="form-control" 
                                           id="username" 
                                           name="username" 
                                           value="{{ old('username') }}"
                                           placeholder="Votre token d'accès Mes Colis (ex: OL6B3FUA526SMLMBN7U3QZ1UMW5YW91D)"
                                           maxlength="500"
                                           required>
                                    <div class="input-addon" onclick="togglePasswordMesColis()">
                                        <i class="fas fa-eye" id="passwordToggleMesColis"></i>
                                    </div>
                                </div>
                                <div class="form-help">
                                    <i class="fas fa-info-circle"></i>
                                    Token d'authentification fourni par Mes Colis Express (x-access-token)
                                </div>
                            </div>
                            
                            <!-- Champ password optionnel pour Mes Colis -->
                            <input type="hidden" name="password" value="">
                        @endif

                        <!-- Environnement -->
                        <div class="form-group">
                            <label for="environment" class="form-label">
                                <i class="fas fa-server"></i>
                                Environnement <span class="required">*</span>
                            </label>
                            <select class="form-control" id="environment" name="environment" required>
                                <option value="test" {{ old('environment', 'test') === 'test' ? 'selected' : '' }}>
                                    Test / Sandbox
                                </option>
                                <option value="production" {{ old('environment') === 'production' ? 'selected' : '' }}>
                                    Production
                                </option>
                            </select>
                            <div class="form-help">
                                <i class="fas fa-info-circle"></i>
                                Commencez en mode Test puis basculez en Production une fois validé
                            </div>
                        </div>

                        <!-- Statut actif -->
                        <div class="form-group">
                            <label class="form-label">
                                <i class="fas fa-toggle-on"></i>
                                Statut de la Configuration
                            </label>
                            <div style="display: flex; align-items: center; gap: 0.75rem; margin-top: 0.5rem;">
                                <input type="checkbox" 
                                       id="is_active" 
                                       name="is_active" 
                                       value="1"
                                       {{ old('is_active', true) ? 'checked' : '' }}
                                       style="width: 18px; height: 18px;">
                                <label for="is_active" style="margin: 0; font-weight: 500; color: #374151;">
                                    Activer cette configuration immédiatement
                                </label>
                            </div>
                            <div class="form-help">
                                <i class="fas fa-info-circle"></i>
                                Une configuration active peut être utilisée pour créer des enlèvements
                            </div>
                        </div>
                    </div>

                    <div class="form-actions">
                        <a href="{{ route('admin.delivery.configuration') }}" class="btn btn-secondary">
                            <i class="fas fa-times"></i>
                            Annuler
                        </a>
                        
                        <div style="display: flex; gap: 0.75rem;">
                            <button type="submit" class="btn btn-primary" id="submitBtn">
                                <i class="fas fa-save"></i>
                                Créer la Configuration
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Sidebar d'aide -->
            <div class="help-sidebar">
                <div class="help-card">
                    <div class="help-header">
                        <i class="fas fa-info-circle"></i>
                        {{ $carrier['name'] }} - Caractéristiques
                    </div>
                    <div class="help-body">
                        @if($carrierSlug === 'jax_delivery')
                            <ul class="help-list">
                                <li>
                                    <i class="fas fa-check-circle" style="color: var(--success);"></i>
                                    Couverture nationale complète
                                </li>
                                <li>
                                    <i class="fas fa-check-circle" style="color: var(--success);"></i>
                                    Authentification JWT sécurisée
                                </li>
                                <li>
                                    <i class="fas fa-check-circle" style="color: var(--success);"></i>
                                    Suivi temps réel des colis
                                </li>
                                <li>
                                    <i class="fas fa-check-circle" style="color: var(--success);"></i>
                                    API de pickups groupés
                                </li>
                                <li>
                                    <i class="fas fa-check-circle" style="color: var(--success);"></i>
                                    Support COD intégré
                                </li>
                            </ul>
                            
                            <div class="spec-grid">
                                <div class="spec-item">
                                    <span class="spec-value">30 kg</span>
                                    <div class="spec-label">Poids Maximum</div>
                                </div>
                                <div class="spec-item">
                                    <span class="spec-value">5000 TND</span>
                                    <div class="spec-label">COD Maximum</div>
                                </div>
                                <div class="spec-item">
                                    <span class="spec-value">24h</span>
                                    <div class="spec-label">Délai Standard</div>
                                </div>
                                <div class="spec-item">
                                    <span class="spec-value">24</span>
                                    <div class="spec-label">Gouvernorats</div>
                                </div>
                            </div>
                        @elseif($carrierSlug === 'mes_colis')
                            <ul class="help-list">
                                <li>
                                    <i class="fas fa-check-circle" style="color: var(--success);"></i>
                                    Livraisons express en Tunisie
                                </li>
                                <li>
                                    <i class="fas fa-check-circle" style="color: var(--success);"></i>
                                    API moderne et simple
                                </li>
                                <li>
                                    <i class="fas fa-check-circle" style="color: var(--success);"></i>
                                    Interface utilisateur intuitive
                                </li>
                                <li>
                                    <i class="fas fa-check-circle" style="color: var(--success);"></i>
                                    Support gouvernorats complets
                                </li>
                                <li>
                                    <i class="fas fa-check-circle" style="color: var(--success);"></i>
                                    Suivi des statuts en temps réel
                                </li>
                            </ul>
                            
                            <div class="spec-grid">
                                <div class="spec-item">
                                    <span class="spec-value">25 kg</span>
                                    <div class="spec-label">Poids Maximum</div>
                                </div>
                                <div class="spec-item">
                                    <span class="spec-value">3000 TND</span>
                                    <div class="spec-label">COD Maximum</div>
                                </div>
                                <div class="spec-item">
                                    <span class="spec-value">48h</span>
                                    <div class="spec-label">Délai Standard</div>
                                </div>
                                <div class="spec-item">
                                    <span class="spec-value">24</span>
                                    <div class="spec-label">Gouvernorats</div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="help-card">
                    <div class="help-header">
                        <i class="fas fa-headset"></i>
                        Support & Documentation
                    </div>
                    <div class="help-body">
                        <div style="display: flex; flex-direction: column; gap: 1rem;">
                            @if($carrierSlug === 'jax_delivery')
                                <a href="https://jax-delivery.com" target="_blank" class="info-link" style="text-decoration: none; color: inherit;">
                                    <i class="fas fa-globe" style="color: var(--primary);"></i>
                                    Site web JAX Delivery
                                </a>
                                <a href="tel:+21671234567" class="info-link" style="text-decoration: none; color: inherit;">
                                    <i class="fas fa-phone" style="color: var(--success);"></i>
                                    Support technique
                                </a>
                            @elseif($carrierSlug === 'mes_colis')
                                <a href="https://mescolis.tn" target="_blank" class="info-link" style="text-decoration: none; color: inherit;">
                                    <i class="fas fa-globe" style="color: var(--primary);"></i>
                                    Site web Mes Colis
                                </a>
                                <a href="mailto:support@mescolis.tn" class="info-link" style="text-decoration: none; color: inherit;">
                                    <i class="fas fa-envelope" style="color: var(--info);"></i>
                                    Support par email
                                </a>
                            @endif
                            
                            <div style="background: #f8fafc; padding: 1rem; border-radius: 8px; border-left: 3px solid var(--info);">
                                <div style="font-weight: 600; color: var(--dark); margin-bottom: 0.5rem; font-size: 0.85rem;">
                                    <i class="fas fa-lightbulb" style="color: var(--warning);"></i>
                                    Conseil
                                </div>
                                <div style="font-size: 0.8rem; color: #6b7280; line-height: 1.4;">
                                    Testez toujours votre configuration avant de l'utiliser en production.
                                </div>
                            </div>
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
    submitUrl: '{{ route("admin.delivery.configuration.store") }}',
    redirectUrl: '{{ route("admin.delivery.configuration") }}',
    carrierSlug: '{{ $carrierSlug }}',
    csrfToken: document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
};

// ===== VARIABLES GLOBALES =====
let isSubmitting = false;

// ===== INITIALISATION =====
document.addEventListener('DOMContentLoaded', function() {
    console.log('🚀 Configuration creation page initialized');
    
    if (!CONFIG.csrfToken) {
        console.error('❌ CSRF token not found');
        showNotification('error', 'Erreur de sécurité. Veuillez recharger la page.');
        return;
    }
    
    setupForm();
    setupValidation();
});

// ===== GESTION DU FORMULAIRE =====
function setupForm() {
    const form = document.getElementById('configForm');
    if (!form) return;
    
    form.addEventListener('submit', handleSubmit);
    
    // Validation temps réel
    const inputs = form.querySelectorAll('input[required], select[required]');
    inputs.forEach(input => {
        input.addEventListener('blur', validateField);
        input.addEventListener('input', debounce(validateField, 300));
    });
}

function setupValidation() {
    // Validation du nom de configuration
    const integrationName = document.getElementById('integration_name');
    if (integrationName) {
        integrationName.addEventListener('input', function() {
            if (this.value.length < 3) {
                setFieldError(this, 'Le nom doit contenir au moins 3 caractères');
            } else if (this.value.length > 100) {
                setFieldError(this, 'Le nom ne peut pas dépasser 100 caractères');
            } else {
                setFieldSuccess(this, 'Nom valide');
            }
        });
    }
    
    // Validation spécifique selon le transporteur
    if (CONFIG.carrierSlug === 'jax_delivery') {
        setupJaxValidation();
    } else if (CONFIG.carrierSlug === 'mes_colis') {
        setupMesColisValidation();
    }
}

function setupJaxValidation() {
    const username = document.getElementById('username');
    const password = document.getElementById('password');
    
    if (username) {
        username.addEventListener('input', function() {
            if (!this.value.trim()) {
                setFieldError(this, 'Numéro de compte requis');
            } else if (this.value.length < 2) {
                setFieldError(this, 'Numéro de compte trop court');
            } else {
                setFieldSuccess(this, 'Numéro de compte valide');
            }
        });
    }
    
    if (password) {
        password.addEventListener('input', function() {
            if (!this.value.trim()) {
                setFieldError(this, 'Token JWT requis');
            } else if (this.value.length < 50) {
                setFieldError(this, 'Token JWT trop court');
            } else if (this.value.length > 500) {
                setFieldError(this, 'Token trop long (maximum 500 caractères)');
            } else if (!isValidJwtFormat(this.value)) {
                setFieldError(this, 'Format JWT invalide (doit contenir 3 parties séparées par des points)');
            } else {
                setFieldSuccess(this, 'Format JWT valide');
            }
        });
    }
}

function setupMesColisValidation() {
    const username = document.getElementById('username');
    
    if (username) {
        username.addEventListener('input', function() {
            if (!this.value.trim()) {
                setFieldError(this, 'Token d\'accès requis');
            } else if (this.value.length < 10) {
                setFieldError(this, 'Token d\'accès trop court (minimum 10 caractères)');
            } else if (this.value.length > 500) {
                setFieldError(this, 'Token trop long (maximum 500 caractères)');
            } else {
                setFieldSuccess(this, 'Token d\'accès valide');
            }
        });
    }
}

function isValidJwtFormat(token) {
    if (!token) return false;
    const parts = token.split('.');
    return parts.length === 3 && parts.every(part => part.length > 10);
}

// ===== GESTION DES ERREURS DE CHAMPS =====
function validateField(event) {
    const field = event.target;
    
    if (!field.value.trim() && field.hasAttribute('required')) {
        setFieldError(field, 'Ce champ est requis');
        return false;
    }
    
    clearFieldError(field);
    return true;
}

function setFieldError(field, message) {
    field.classList.remove('success');
    field.classList.add('error');
    
    removeFieldMessages(field);
    
    const errorDiv = document.createElement('div');
    errorDiv.className = 'form-error';
    errorDiv.innerHTML = `<i class="fas fa-exclamation-triangle"></i> <span>${message}</span>`;
    field.parentNode.appendChild(errorDiv);
}

function setFieldSuccess(field, message) {
    field.classList.remove('error');
    field.classList.add('success');
    
    removeFieldMessages(field);
    
    const successDiv = document.createElement('div');
    successDiv.className = 'form-success';
    successDiv.innerHTML = `<i class="fas fa-check-circle"></i> <span>${message}</span>`;
    field.parentNode.appendChild(successDiv);
}

function clearFieldError(field) {
    if (typeof field === 'object' && field.target) {
        field = field.target;
    }
    
    field.classList.remove('error', 'success');
    removeFieldMessages(field);
}

function removeFieldMessages(field) {
    const parent = field.parentNode;
    const existingError = parent.querySelector('.form-error');
    const existingSuccess = parent.querySelector('.form-success');
    
    if (existingError) existingError.remove();
    if (existingSuccess) existingSuccess.remove();
}

// ===== SOUMISSION DU FORMULAIRE =====
async function handleSubmit(event) {
    event.preventDefault();
    
    if (isSubmitting) return;
    
    const form = event.target;
    const formData = new FormData(form);
    
    // Validation finale
    if (!validateForm(form)) {
        showNotification('error', 'Veuillez corriger les erreurs dans le formulaire');
        return;
    }
    
    // Debug des données
    console.log('🔍 FormData debug:');
    for (let [key, value] of formData.entries()) {
        console.log(`${key}: ${value}`);
    }
    
    isSubmitting = true;
    updateSubmitButton(true);
    
    try {
        const response = await fetch(CONFIG.submitUrl, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': CONFIG.csrfToken,
                'Accept': 'application/json'
            },
            body: formData
        });
        
        const data = await response.json();
        
        console.log('📥 Response:', response.status, data);
        
        if (response.ok && data.success) {
            showNotification('success', data.message || 'Configuration créée avec succès !');
            
            // Redirection après 2 secondes
            setTimeout(() => {
                if (data.redirect) {
                    window.location.href = data.redirect;
                } else {
                    window.location.href = CONFIG.redirectUrl;
                }
            }, 2000);
        } else {
            handleFormErrors(data);
        }
        
    } catch (error) {
        console.error('❌ Submit error:', error);
        showNotification('error', 'Erreur de connexion. Veuillez réessayer.');
    } finally {
        isSubmitting = false;
        updateSubmitButton(false);
    }
}

function validateForm(form) {
    const requiredFields = form.querySelectorAll('input[required], select[required]');
    let isValid = true;
    
    requiredFields.forEach(field => {
        if (!field.value.trim()) {
            setFieldError(field, 'Ce champ est requis');
            isValid = false;
        }
    });
    
    return isValid;
}

function handleFormErrors(data) {
    console.log('❌ Form errors:', data);
    
    if (data.errors) {
        // Erreurs de validation Laravel
        Object.keys(data.errors).forEach(fieldName => {
            const field = document.querySelector(`[name="${fieldName}"]`);
            if (field) {
                setFieldError(field, data.errors[fieldName][0]);
            }
        });
        showNotification('error', 'Veuillez corriger les erreurs dans le formulaire');
    } else {
        const message = data.error || data.message || 'Erreur lors de la création de la configuration';
        showNotification('error', message);
    }
}

function updateSubmitButton(loading) {
    const btn = document.getElementById('submitBtn');
    if (!btn) return;
    
    if (loading) {
        btn.disabled = true;
        btn.classList.add('loading');
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Création en cours...';
    } else {
        btn.disabled = false;
        btn.classList.remove('loading');
        btn.innerHTML = '<i class="fas fa-save"></i> Créer la Configuration';
    }
}

// ===== GESTION DU MOT DE PASSE =====
function togglePassword() {
    const passwordInput = document.getElementById('password');
    const toggleIcon = document.getElementById('passwordToggle');
    
    if (!passwordInput || !toggleIcon) return;
    
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        toggleIcon.className = 'fas fa-eye-slash';
    } else {
        passwordInput.type = 'password';
        toggleIcon.className = 'fas fa-eye';
    }
}

// ===== GESTION SPÉCIFIQUE MES COLIS =====
function togglePasswordMesColis() {
    const usernameInput = document.getElementById('username');
    const toggleIcon = document.getElementById('passwordToggleMesColis');
    
    if (!usernameInput || !toggleIcon) return;
    
    if (usernameInput.type === 'password') {
        usernameInput.type = 'text';
        toggleIcon.className = 'fas fa-eye-slash';
    } else {
        usernameInput.type = 'password';
        toggleIcon.className = 'fas fa-eye';
    }
}

// ===== UTILITAIRES =====
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

// ===== NOTIFICATIONS =====
function showNotification(type, message) {
    // Supprimer les notifications existantes
    const existingNotifications = document.querySelectorAll('.notification');
    existingNotifications.forEach(notif => notif.remove());
    
    const notification = document.createElement('div');
    notification.className = `notification ${type}`;
    
    const icon = type === 'success' ? 'check-circle' : 
                 type === 'error' ? 'exclamation-circle' : 'info-circle';
    
    notification.innerHTML = `
        <i class="fas fa-${icon}"></i>
        <span>${message}</span>
    `;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.style.animation = 'slideOutRight 0.4s cubic-bezier(0.4, 0, 0.2, 1)';
        setTimeout(() => notification.remove(), 400);
    }, 5000);
}

console.log('✅ Configuration creation scripts loaded');
</script>
@endsection