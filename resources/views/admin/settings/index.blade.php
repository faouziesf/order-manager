@extends('layouts.admin')

@section('title', 'Paramètres')
@section('page-title', 'Configuration du Système')

@section('css')
<style>
    :root {
        --settings-primary: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
        --settings-success: linear-gradient(135deg, #10b981 0%, #059669 100%);
        --settings-warning: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        --settings-danger: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        --glass-bg: rgba(255, 255, 255, 0.95);
        --glass-border: rgba(255, 255, 255, 0.2);
        --shadow-elevated: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        --border-radius-xl: 20px;
        --transition-smooth: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    body {
        background: linear-gradient(135deg, #f0f4ff 0%, #e0e7ff 100%);
        font-family: 'Inter', sans-serif;
    }

    .settings-container {
        background: var(--glass-bg);
        backdrop-filter: blur(20px);
        border-radius: var(--border-radius-xl);
        box-shadow: var(--shadow-elevated);
        border: 1px solid var(--glass-border);
        margin: 1rem;
        min-height: calc(100vh - 140px);
        overflow: hidden;
    }

    .settings-header {
        background: var(--settings-primary);
        color: white;
        padding: 2rem;
        position: relative;
        overflow: hidden;
    }

    .settings-header::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -20%;
        width: 100%;
        height: 200%;
        background: radial-gradient(circle, rgba(255, 255, 255, 0.1) 0%, transparent 70%);
        transform: rotate(15deg);
    }

    .settings-title {
        position: relative;
        z-index: 2;
        display: flex;
        align-items: center;
        gap: 1rem;
        margin: 0;
    }

    .settings-icon {
        width: 60px;
        height: 60px;
        background: rgba(255, 255, 255, 0.1);
        border-radius: 15px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.8rem;
    }

    .settings-content {
        padding: 2rem;
    }

    /* Navigation par onglets */
    .settings-nav {
        display: flex;
        gap: 0.5rem;
        margin-bottom: 2rem;
        background: #f8fafc;
        padding: 0.5rem;
        border-radius: 15px;
        flex-wrap: wrap;
    }

    .nav-tab {
        background: transparent;
        border: none;
        padding: 0.75rem 1.5rem;
        border-radius: 10px;
        font-weight: 600;
        cursor: pointer;
        transition: var(--transition-smooth);
        color: #6b7280;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        white-space: nowrap;
    }

    .nav-tab.active {
        background: white;
        color: #6366f1;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    }

    .nav-tab:hover:not(.active) {
        background: rgba(255, 255, 255, 0.5);
        color: #4b5563;
    }

    /* Sections de paramètres */
    .settings-section {
        display: none;
        animation: fadeIn 0.3s ease-out;
    }

    .settings-section.active {
        display: block;
    }

    .section-card {
        background: white;
        border-radius: 15px;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        border: 1px solid #e5e7eb;
        margin-bottom: 1.5rem;
        overflow: hidden;
    }

    .section-header {
        background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
        padding: 1.5rem;
        border-bottom: 1px solid #e5e7eb;
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .section-icon {
        width: 50px;
        height: 50px;
        background: var(--settings-primary);
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1.3rem;
    }

    .section-title {
        flex: 1;
    }

    .section-title h3 {
        font-size: 1.3rem;
        font-weight: 700;
        color: #374151;
        margin: 0 0 0.25rem 0;
    }

    .section-description {
        color: #6b7280;
        font-size: 0.9rem;
        margin: 0;
    }

    .section-body {
        padding: 1.5rem;
    }

    /* Paramètres individuels */
    .settings-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 1.5rem;
    }

    .setting-item {
        background: #f9fafb;
        border-radius: 12px;
        padding: 1.25rem;
        border: 1px solid #e5e7eb;
        transition: var(--transition-smooth);
    }

    .setting-item:hover {
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        transform: translateY(-2px);
    }

    .setting-label {
        font-weight: 600;
        color: #374151;
        margin-bottom: 0.5rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .setting-description {
        color: #6b7280;
        font-size: 0.85rem;
        margin-bottom: 1rem;
        line-height: 1.4;
    }

    .setting-input {
        width: 100%;
        padding: 0.75rem 1rem;
        border: 2px solid #e5e7eb;
        border-radius: 8px;
        font-size: 0.9rem;
        transition: var(--transition-smooth);
        background: white;
    }

    .setting-input:focus {
        border-color: #6366f1;
        box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
        outline: none;
    }

    .setting-unit {
        color: #6b7280;
        font-size: 0.8rem;
        margin-top: 0.25rem;
    }

    /* Boutons d'action */
    .actions-section {
        background: white;
        border-radius: 15px;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        border: 1px solid #e5e7eb;
        padding: 2rem;
        margin-top: 2rem;
        text-align: center;
    }

    .actions-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1rem;
        margin-top: 1.5rem;
    }

    .action-btn {
        padding: 0.875rem 1.5rem;
        border: none;
        border-radius: 10px;
        font-weight: 600;
        font-size: 0.9rem;
        cursor: pointer;
        transition: var(--transition-smooth);
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        text-decoration: none;
    }

    .action-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
    }

    .btn-save { background: var(--settings-success); color: white; }
    .btn-reset { background: var(--settings-warning); color: white; }
    .btn-export { background: var(--settings-primary); color: white; }
    .btn-import { background: #6b7280; color: white; }

    /* Switches pour les options booléennes */
    .setting-switch {
        position: relative;
        display: inline-block;
        width: 50px;
        height: 24px;
    }

    .setting-switch input {
        opacity: 0;
        width: 0;
        height: 0;
    }

    .slider {
        position: absolute;
        cursor: pointer;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: #ccc;
        border-radius: 24px;
        transition: 0.3s;
    }

    .slider:before {
        position: absolute;
        content: "";
        height: 18px;
        width: 18px;
        left: 3px;
        bottom: 3px;
        background-color: white;
        border-radius: 50%;
        transition: 0.3s;
    }

    input:checked + .slider {
        background: var(--settings-primary);
    }

    input:checked + .slider:before {
        transform: translateX(26px);
    }

    /* Section d'import */
    .import-section {
        background: #f8fafc;
        border-radius: 12px;
        padding: 1.5rem;
        border: 2px dashed #d1d5db;
        text-align: center;
        margin-top: 1rem;
    }

    .file-input {
        margin: 1rem 0;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .settings-content {
            padding: 1rem;
        }
        
        .settings-nav {
            overflow-x: auto;
            flex-wrap: nowrap;
        }
        
        .settings-grid {
            grid-template-columns: 1fr;
        }
        
        .actions-grid {
            grid-template-columns: 1fr;
        }
        
        .section-header {
            flex-direction: column;
            text-align: center;
            gap: 1rem;
        }
    }

    /* Animations */
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .setting-item {
        animation: slideUp 0.3s ease-out;
    }

    @keyframes slideUp {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }

    /* Validation d'erreur */
    .setting-input.error {
        border-color: #ef4444;
        box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1);
    }

    .error-message {
        color: #ef4444;
        font-size: 0.8rem;
        margin-top: 0.25rem;
    }

    /* Indicateurs de valeurs */
    .value-indicator {
        display: inline-block;
        padding: 0.25rem 0.75rem;
        background: #e0e7ff;
        color: #3730a3;
        border-radius: 15px;
        font-size: 0.8rem;
        font-weight: 600;
        margin-left: 0.5rem;
    }

    /* Info tooltips */
    .info-tooltip {
        position: relative;
        cursor: help;
        color: #6b7280;
    }

    .info-tooltip:hover::after {
        content: attr(data-tooltip);
        position: absolute;
        bottom: 100%;
        left: 50%;
        transform: translateX(-50%);
        background: #374151;
        color: white;
        padding: 0.5rem;
        border-radius: 6px;
        font-size: 0.8rem;
        white-space: nowrap;
        z-index: 1000;
        margin-bottom: 5px;
    }
</style>
@endsection

@section('content')
<div class="settings-container">
    <!-- Header -->
    <div class="settings-header">
        <h1 class="settings-title">
            <div class="settings-icon">
                <i class="fas fa-cogs"></i>
            </div>
            <div>
                <div style="font-size: 2rem; font-weight: 700;">Paramètres du Système</div>
                <div style="font-size: 1.1rem; opacity: 0.9;">Configuration des files de traitement et interfaces</div>
            </div>
        </h1>
    </div>

    <!-- Contenu -->
    <div class="settings-content">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i>
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i>
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <!-- Navigation -->
        <div class="settings-nav">
            <button class="nav-tab active" data-section="queues">
                <i class="fas fa-layer-group"></i>
                Files de Traitement
            </button>
            <button class="nav-tab" data-section="interfaces">
                <i class="fas fa-desktop"></i>
                Interfaces
            </button>
            <button class="nav-tab" data-section="automation">
                <i class="fas fa-robot"></i>
                Automatisation
            </button>
            <button class="nav-tab" data-section="performance">
                <i class="fas fa-tachometer-alt"></i>
                Performance
            </button>
            <button class="nav-tab" data-section="advanced">
                <i class="fas fa-tools"></i>
                Avancé
            </button>
        </div>

        <form action="{{ route('admin.settings.store') }}" method="POST" id="settings-form">
            @csrf

            <!-- Section Files de Traitement -->
            <div class="settings-section active" id="section-queues">
                <!-- File Standard -->
                <div class="section-card">
                    <div class="section-header">
                        <div class="section-icon" style="background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);">
                            <i class="fas fa-phone"></i>
                        </div>
                        <div class="section-title">
                            <h3>File Standard</h3>
                            <p class="section-description">Paramètres pour les nouvelles commandes non assignées</p>
                        </div>
                    </div>
                    <div class="section-body">
                        <div class="settings-grid">
                            <div class="setting-item">
                                <label class="setting-label">
                                    <i class="fas fa-redo"></i>
                                    Tentatives quotidiennes maximum
                                    <i class="fas fa-info-circle info-tooltip" data-tooltip="Nombre max d'appels par jour pour une commande"></i>
                                </label>
                                <p class="setting-description">
                                    Nombre maximum d'appels autorisés par jour pour une commande dans la file standard.
                                </p>
                                <input type="number" name="standard_max_daily_attempts" 
                                       value="{{ $settings['standard_max_daily_attempts'] }}" 
                                       class="setting-input" min="1" max="10" required>
                                <div class="setting-unit">tentatives par jour</div>
                            </div>

                            <div class="setting-item">
                                <label class="setting-label">
                                    <i class="fas fa-clock"></i>
                                    Délai entre tentatives
                                </label>
                                <p class="setting-description">
                                    Temps d'attente minimum entre deux tentatives d'appel pour la même commande.
                                </p>
                                <input type="number" name="standard_delay_hours" 
                                       value="{{ $settings['standard_delay_hours'] }}" 
                                       class="setting-input" min="0.5" max="24" step="0.5" required>
                                <div class="setting-unit">heures</div>
                            </div>

                            <div class="setting-item">
                                <label class="setting-label">
                                    <i class="fas fa-ban"></i>
                                    Tentatives totales maximum
                                </label>
                                <p class="setting-description">
                                    Nombre total de tentatives avant de passer la commande en file ancienne.
                                </p>
                                <input type="number" name="standard_max_total_attempts" 
                                       value="{{ $settings['standard_max_total_attempts'] }}" 
                                       class="setting-input" min="1" max="50" required>
                                <div class="setting-unit">tentatives au total</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- File Datée -->
                <div class="section-card">
                    <div class="section-header">
                        <div class="section-icon" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);">
                            <i class="fas fa-calendar-alt"></i>
                        </div>
                        <div class="section-title">
                            <h3>File Datée</h3>
                            <p class="section-description">Paramètres pour les commandes avec rappel programmé</p>
                        </div>
                    </div>
                    <div class="section-body">
                        <div class="settings-grid">
                            <div class="setting-item">
                                <label class="setting-label">
                                    <i class="fas fa-redo"></i>
                                    Tentatives quotidiennes maximum
                                </label>
                                <p class="setting-description">
                                    Nombre maximum d'appels autorisés par jour pour une commande datée.
                                </p>
                                <input type="number" name="dated_max_daily_attempts" 
                                       value="{{ $settings['dated_max_daily_attempts'] }}" 
                                       class="setting-input" min="1" max="10" required>
                                <div class="setting-unit">tentatives par jour</div>
                            </div>

                            <div class="setting-item">
                                <label class="setting-label">
                                    <i class="fas fa-clock"></i>
                                    Délai entre tentatives
                                </label>
                                <p class="setting-description">
                                    Temps d'attente minimum entre deux tentatives pour les commandes datées.
                                </p>
                                <input type="number" name="dated_delay_hours" 
                                       value="{{ $settings['dated_delay_hours'] }}" 
                                       class="setting-input" min="0.5" max="24" step="0.5" required>
                                <div class="setting-unit">heures</div>
                            </div>

                            <div class="setting-item">
                                <label class="setting-label">
                                    <i class="fas fa-ban"></i>
                                    Tentatives totales maximum
                                </label>
                                <p class="setting-description">
                                    Nombre total de tentatives pour les commandes datées avant abandon.
                                </p>
                                <input type="number" name="dated_max_total_attempts" 
                                       value="{{ $settings['dated_max_total_attempts'] }}" 
                                       class="setting-input" min="1" max="20" required>
                                <div class="setting-unit">tentatives au total</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- File Ancienne -->
                <div class="section-card">
                    <div class="section-header">
                        <div class="section-icon" style="background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);">
                            <i class="fas fa-history"></i>
                        </div>
                        <div class="section-title">
                            <h3>File Ancienne</h3>
                            <p class="section-description">Paramètres pour les commandes ayant dépassé le seuil standard</p>
                        </div>
                    </div>
                    <div class="section-body">
                        <div class="settings-grid">
                            <div class="setting-item">
                                <label class="setting-label">
                                    <i class="fas fa-redo"></i>
                                    Tentatives quotidiennes maximum
                                </label>
                                <p class="setting-description">
                                    Nombre maximum d'appels autorisés par jour pour les commandes anciennes.
                                </p>
                                <input type="number" name="old_max_daily_attempts" 
                                       value="{{ $settings['old_max_daily_attempts'] }}" 
                                       class="setting-input" min="1" max="10" required>
                                <div class="setting-unit">tentatives par jour</div>
                            </div>

                            <div class="setting-item">
                                <label class="setting-label">
                                    <i class="fas fa-clock"></i>
                                    Délai entre tentatives
                                </label>
                                <p class="setting-description">
                                    Temps d'attente minimum entre tentatives pour les commandes anciennes.
                                </p>
                                <input type="number" name="old_delay_hours" 
                                       value="{{ $settings['old_delay_hours'] }}" 
                                       class="setting-input" min="1" max="48" step="0.5" required>
                                <div class="setting-unit">heures</div>
                            </div>

                            <div class="setting-item">
                                <label class="setting-label">
                                    <i class="fas fa-ban"></i>
                                    Tentatives totales maximum
                                </label>
                                <p class="setting-description">
                                    Limite totale pour les commandes anciennes. 0 = illimité.
                                </p>
                                <input type="number" name="old_max_total_attempts" 
                                       value="{{ $settings['old_max_total_attempts'] }}" 
                                       class="setting-input" min="0" max="30" required>
                                <div class="setting-unit">tentatives (0 = illimité)</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- File Retour en Stock -->
                <div class="section-card">
                    <div class="section-header">
                        <div class="section-icon" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);">
                            <i class="fas fa-box-open"></i>
                        </div>
                        <div class="section-title">
                            <h3>File Retour en Stock</h3>
                            <p class="section-description">Paramètres pour les commandes réactivées après retour en stock</p>
                        </div>
                    </div>
                    <div class="section-body">
                        <div class="settings-grid">
                            <div class="setting-item">
                                <label class="setting-label">
                                    <i class="fas fa-redo"></i>
                                    Tentatives quotidiennes maximum
                                </label>
                                <p class="setting-description">
                                    Nombre maximum d'appels par jour pour les commandes réactivées.
                                </p>
                                <input type="number" name="restock_max_daily_attempts" 
                                       value="{{ $settings['restock_max_daily_attempts'] }}" 
                                       class="setting-input" min="1" max="10" required>
                                <div class="setting-unit">tentatives par jour</div>
                            </div>

                            <div class="setting-item">
                                <label class="setting-label">
                                    <i class="fas fa-clock"></i>
                                    Délai entre tentatives
                                </label>
                                <p class="setting-description">
                                    Temps d'attente entre tentatives pour les commandes retour en stock.
                                </p>
                                <input type="number" name="restock_delay_hours" 
                                       value="{{ $settings['restock_delay_hours'] }}" 
                                       class="setting-input" min="0.5" max="24" step="0.5" required>
                                <div class="setting-unit">heures</div>
                            </div>

                            <div class="setting-item">
                                <label class="setting-label">
                                    <i class="fas fa-ban"></i>
                                    Tentatives totales maximum
                                </label>
                                <p class="setting-description">
                                    Limite totale pour les commandes retour en stock.
                                </p>
                                <input type="number" name="restock_max_total_attempts" 
                                       value="{{ $settings['restock_max_total_attempts'] }}" 
                                       class="setting-input" min="1" max="20" required>
                                <div class="setting-unit">tentatives au total</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Section Interfaces -->
            <div class="settings-section" id="section-interfaces">
                <!-- Interface d'Examen -->
                <div class="section-card">
                    <div class="section-header">
                        <div class="section-icon" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);">
                            <i class="fas fa-search"></i>
                        </div>
                        <div class="section-title">
                            <h3>Interface d'Examen</h3>
                            <p class="section-description">Configuration de l'interface d'examen des problèmes de stock</p>
                        </div>
                    </div>
                    <div class="section-body">
                        <div class="settings-grid">
                            <div class="setting-item">
                                <label class="setting-label">
                                    <i class="fas fa-sync-alt"></i>
                                    Intervalle de rafraîchissement automatique
                                </label>
                                <p class="setting-description">
                                    Fréquence de mise à jour automatique de la liste des commandes.
                                </p>
                                <input type="number" name="examination_auto_refresh_interval" 
                                       value="{{ $settings['examination_auto_refresh_interval'] }}" 
                                       class="setting-input" min="30" max="600" required>
                                <div class="setting-unit">secondes</div>
                            </div>

                            <div class="setting-item">
                                <label class="setting-label">
                                    <i class="fas fa-list"></i>
                                    Commandes par page maximum
                                </label>
                                <p class="setting-description">
                                    Nombre maximum de commandes affichées simultanément.
                                </p>
                                <input type="number" name="examination_max_orders_per_page" 
                                       value="{{ $settings['examination_max_orders_per_page'] }}" 
                                       class="setting-input" min="10" max="100" required>
                                <div class="setting-unit">commandes</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Interface Commandes Suspendues -->
                <div class="section-card">
                    <div class="section-header">
                        <div class="section-icon" style="background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);">
                            <i class="fas fa-pause-circle"></i>
                        </div>
                        <div class="section-title">
                            <h3>Interface Commandes Suspendues</h3>
                            <p class="section-description">Configuration de l'interface de gestion des commandes suspendues</p>
                        </div>
                    </div>
                    <div class="section-body">
                        <div class="settings-grid">
                            <div class="setting-item">
                                <label class="setting-label">
                                    <i class="fas fa-sync-alt"></i>
                                    Intervalle de vérification automatique
                                </label>
                                <p class="setting-description">
                                    Fréquence de vérification du statut des commandes suspendues.
                                </p>
                                <input type="number" name="suspended_auto_check_interval" 
                                       value="{{ $settings['suspended_auto_check_interval'] }}" 
                                       class="setting-input" min="60" max="1800" required>
                                <div class="setting-unit">secondes</div>
                            </div>

                            <div class="setting-item">
                                <label class="setting-label">
                                    <i class="fas fa-list"></i>
                                    Commandes par page maximum
                                </label>
                                <p class="setting-description">
                                    Nombre maximum de commandes suspendues affichées.
                                </p>
                                <input type="number" name="suspended_max_orders_per_page" 
                                       value="{{ $settings['suspended_max_orders_per_page'] }}" 
                                       class="setting-input" min="10" max="100" required>
                                <div class="setting-unit">commandes</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Section Automatisation -->
            <div class="settings-section" id="section-automation">
                <div class="section-card">
                    <div class="section-header">
                        <div class="section-icon" style="background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%);">
                            <i class="fas fa-robot"></i>
                        </div>
                        <div class="section-title">
                            <h3>Suspension Automatique</h3>
                            <p class="section-description">Configuration des règles de suspension automatique</p>
                        </div>
                    </div>
                    <div class="section-body">
                        <div class="settings-grid">
                            <div class="setting-item">
                                <label class="setting-label">
                                    <i class="fas fa-pause"></i>
                                    Suspension automatique sur problème de stock
                                </label>
                                <p class="setting-description">
                                    Suspendre automatiquement les commandes avec des problèmes de stock détectés.
                                </p>
                                <label class="setting-switch">
                                    <input type="checkbox" name="auto_suspend_on_stock_issue" value="1" 
                                           {{ $settings['auto_suspend_on_stock_issue'] ? 'checked' : '' }}>
                                    <span class="slider"></span>
                                </label>
                            </div>

                            <div class="setting-item">
                                <label class="setting-label">
                                    <i class="fas fa-calendar-times"></i>
                                    Seuil de suspension (jours sans stock)
                                </label>
                                <p class="setting-description">
                                    Suspendre automatiquement après X jours sans stock disponible.
                                </p>
                                <input type="number" name="auto_suspend_threshold_days" 
                                       value="{{ $settings['auto_suspend_threshold_days'] }}" 
                                       class="setting-input" min="1" max="30" required>
                                <div class="setting-unit">jours</div>
                            </div>

                            <div class="setting-item">
                                <label class="setting-label">
                                    <i class="fas fa-bell"></i>
                                    Notifications de retour en stock
                                </label>
                                <p class="setting-description">
                                    Activer les notifications quand des produits reviennent en stock.
                                </p>
                                <label class="setting-switch">
                                    <input type="checkbox" name="restock_notification_enabled" value="1" 
                                           {{ $settings['restock_notification_enabled'] ? 'checked' : '' }}>
                                    <span class="slider"></span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Section Performance -->
            <div class="settings-section" id="section-performance">
                <div class="section-card">
                    <div class="section-header">
                        <div class="section-icon" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);">
                            <i class="fas fa-tachometer-alt"></i>
                        </div>
                        <div class="section-title">
                            <h3>Optimisation des Performances</h3>
                            <p class="section-description">Paramètres pour améliorer les performances du système</p>
                        </div>
                    </div>
                    <div class="section-body">
                        <div class="settings-grid">
                            <div class="setting-item">
                                <label class="setting-label">
                                    <i class="fas fa-database"></i>
                                    Durée du cache de vérification stock
                                </label>
                                <p class="setting-description">
                                    Temps de mise en cache des vérifications de stock pour éviter les requêtes répétées.
                                </p>
                                <input type="number" name="stock_check_cache_duration" 
                                       value="{{ $settings['stock_check_cache_duration'] }}" 
                                       class="setting-input" min="60" max="3600" required>
                                <div class="setting-unit">secondes</div>
                            </div>

                            <div class="setting-item">
                                <label class="setting-label">
                                    <i class="fas fa-tasks"></i>
                                    Limite d'actions groupées
                                </label>
                                <p class="setting-description">
                                    Nombre maximum de commandes traitables en une seule action groupée.
                                </p>
                                <input type="number" name="bulk_action_max_orders" 
                                       value="{{ $settings['bulk_action_max_orders'] }}" 
                                       class="setting-input" min="10" max="500" required>
                                <div class="setting-unit">commandes</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Section Avancé -->
            <div class="settings-section" id="section-advanced">
                <div class="section-card">
                    <div class="section-header">
                        <div class="section-icon" style="background: linear-gradient(135deg, #6b7280 0%, #4b5563 100%);">
                            <i class="fas fa-tools"></i>
                        </div>
                        <div class="section-title">
                            <h3>Gestion Avancée</h3>
                            <p class="section-description">Import/Export et réinitialisation des paramètres</p>
                        </div>
                    </div>
                    <div class="section-body">
                        <div class="import-section">
                            <h4><i class="fas fa-file-import"></i> Importer des Paramètres</h4>
                            <p>Importez une configuration depuis un fichier JSON</p>
                            <input type="file" accept=".json" class="file-input" id="import-file">
                            <button type="button" class="action-btn btn-import" onclick="importSettings()">
                                <i class="fas fa-upload"></i>
                                Importer la Configuration
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="actions-section">
                <h3><i class="fas fa-save"></i> Actions</h3>
                <p>Sauvegardez vos modifications ou restaurez les paramètres par défaut</p>
                
                <div class="actions-grid">
                    <button type="submit" class="action-btn btn-save">
                        <i class="fas fa-save"></i>
                        Sauvegarder les Paramètres
                    </button>
                    
                    <button type="button" class="action-btn btn-export" onclick="exportSettings()">
                        <i class="fas fa-download"></i>
                        Exporter la Configuration
                    </button>
                    
                    <button type="button" class="action-btn btn-reset" onclick="confirmReset()">
                        <i class="fas fa-undo"></i>
                        Réinitialiser par Défaut
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Modal de confirmation pour reset -->
<div class="modal fade" id="resetModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirmer la Réinitialisation</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Êtes-vous sûr de vouloir réinitialiser tous les paramètres aux valeurs par défaut ?</p>
                <p><strong>Cette action ne peut pas être annulée.</strong></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <a href="{{ route('admin.settings.reset') }}" class="btn btn-danger">Réinitialiser</a>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    // Navigation par onglets
    $('.nav-tab').click(function() {
        const section = $(this).data('section');
        
        // Mettre à jour les onglets
        $('.nav-tab').removeClass('active');
        $(this).addClass('active');
        
        // Mettre à jour les sections
        $('.settings-section').removeClass('active');
        $(`#section-${section}`).addClass('active');
    });
    
    // Validation en temps réel
    $('.setting-input').on('input', function() {
        const input = $(this);
        const min = parseFloat(input.attr('min'));
        const max = parseFloat(input.attr('max'));
        const value = parseFloat(input.val());
        
        // Supprimer les classes d'erreur existantes
        input.removeClass('error');
        input.siblings('.error-message').remove();
        
        // Validation
        if (isNaN(value) || value < min || value > max) {
            input.addClass('error');
            input.after(`<div class="error-message">Valeur doit être entre ${min} et ${max}</div>`);
        }
    });
    
    // Prévisualisation des valeurs
    $('.setting-input').on('input', function() {
        const input = $(this);
        const value = input.val();
        
        // Mettre à jour l'indicateur de valeur s'il existe
        let indicator = input.siblings('.value-indicator');
        if (indicator.length === 0) {
            indicator = $('<span class="value-indicator"></span>');
            input.parent().append(indicator);
        }
        indicator.text(`Actuel: ${value}`);
    });
});

// Actions des boutons
function confirmReset() {
    $('#resetModal').modal('show');
}

function exportSettings() {
    window.location.href = '{{ route("admin.settings.export") }}';
}

function importSettings() {
    const fileInput = document.getElementById('import-file');
    const file = fileInput.files[0];
    
    if (!file) {
        alert('Veuillez sélectionner un fichier');
        return;
    }
    
    const formData = new FormData();
    formData.append('settings_file', file);
    formData.append('_token', '{{ csrf_token() }}');
    
    fetch('{{ route("admin.settings.import") }}', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Erreur lors de l\'import: ' + data.message);
        }
    })
    .catch(error => {
        alert('Erreur lors de l\'import');
        console.error(error);
    });
}

// Auto-save en brouillon
let autoSaveTimeout;
$('#settings-form input, #settings-form select').on('change', function() {
    clearTimeout(autoSaveTimeout);
    autoSaveTimeout = setTimeout(() => {
        // Sauvegarder en local storage comme brouillon
        const formData = new FormData(document.getElementById('settings-form'));
        const data = {};
        for (let [key, value] of formData.entries()) {
            data[key] = value;
        }
        localStorage.setItem('settings_draft', JSON.stringify(data));
    }, 1000);
});

// Charger le brouillon au chargement
$(document).ready(function() {
    const draft = localStorage.getItem('settings_draft');
    if (draft) {
        const data = JSON.parse(draft);
        Object.keys(data).forEach(key => {
            const input = $(`[name="${key}"]`);
            if (input.attr('type') === 'checkbox') {
                input.prop('checked', data[key] === '1');
            } else {
                input.val(data[key]);
            }
        });
    }
});

// Nettoyer le brouillon après sauvegarde
$('#settings-form').on('submit', function() {
    localStorage.removeItem('settings_draft');
});
</script>
@endsection