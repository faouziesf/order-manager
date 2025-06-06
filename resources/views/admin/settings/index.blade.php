@extends('layouts.admin')

@section('title', 'Paramètres')
@section('page-title', 'Configuration du Système')

@section('css')
<style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    :root {
        --primary: #6366f1;
        --primary-dark: #4f46e5;
        --success: #10b981;
        --warning: #f59e0b;
        --danger: #ef4444;
        --info: #3b82f6;
        --gray-50: #f9fafb;
        --gray-100: #f3f4f6;
        --gray-200: #e5e7eb;
        --gray-300: #d1d5db;
        --gray-400: #9ca3af;
        --gray-500: #6b7280;
        --gray-600: #4b5563;
        --gray-700: #374151;
        --gray-800: #1f2937;
        --gray-900: #111827;
        --white: #ffffff;
        --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
        --shadow: 0 1px 3px 0 rgb(0 0 0 / 0.1), 0 1px 2px -1px rgb(0 0 0 / 0.1);
        --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
        --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
        --radius: 0.5rem;
        --radius-lg: 0.75rem;
        --radius-xl: 1rem;
    }

    body {
        background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
        color: var(--gray-700);
        line-height: 1.6;
    }

    .container {
        max-width: 1400px;
        margin: 0 auto;
        padding: 1.5rem;
    }

    /* Header Compact */
    .header {
        background: var(--white);
        border-radius: var(--radius-xl);
        box-shadow: var(--shadow);
        padding: 1.5rem 2rem;
        margin-bottom: 1.5rem;
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .header-icon {
        width: 48px;
        height: 48px;
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
        border-radius: var(--radius-lg);
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--white);
        font-size: 1.25rem;
    }

    .header-content h1 {
        font-size: 1.75rem;
        font-weight: 700;
        color: var(--gray-900);
        margin-bottom: 0.25rem;
    }

    .header-content p {
        color: var(--gray-500);
        font-size: 0.9rem;
    }

    /* Navigation améliorée */
    .nav-container {
        background: var(--white);
        border-radius: var(--radius-xl);
        box-shadow: var(--shadow);
        padding: 0.5rem;
        margin-bottom: 1.5rem;
        overflow-x: auto;
    }

    .nav-tabs {
        display: flex;
        gap: 0.25rem;
        min-width: max-content;
    }

    .nav-tab {
        background: transparent;
        border: none;
        padding: 0.75rem 1.25rem;
        border-radius: var(--radius-lg);
        font-weight: 500;
        font-size: 0.875rem;
        cursor: pointer;
        transition: all 0.2s ease;
        color: var(--gray-600);
        display: flex;
        align-items: center;
        gap: 0.5rem;
        white-space: nowrap;
        position: relative;
    }

    .nav-tab:hover {
        background: var(--gray-50);
        color: var(--gray-800);
    }

    .nav-tab.active {
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
        color: var(--white);
        box-shadow: var(--shadow-md);
    }

    .nav-tab i {
        font-size: 1rem;
    }

    /* Sections optimisées */
    .section {
        display: none;
        animation: fadeIn 0.3s ease-out;
    }

    .section.active {
        display: block;
    }

    .section-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
        gap: 1.5rem;
    }

    /* Cards modernisées */
    .settings-card {
        background: var(--white);
        border-radius: var(--radius-xl);
        box-shadow: var(--shadow);
        border: 1px solid var(--gray-200);
        overflow: hidden;
        transition: all 0.2s ease;
    }

    .settings-card:hover {
        box-shadow: var(--shadow-lg);
        transform: translateY(-2px);
    }

    .card-header {
        background: linear-gradient(135deg, var(--gray-50) 0%, var(--gray-100) 100%);
        padding: 1.25rem 1.5rem;
        border-bottom: 1px solid var(--gray-200);
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .card-icon {
        width: 40px;
        height: 40px;
        border-radius: var(--radius);
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--white);
        font-size: 1.1rem;
        flex-shrink: 0;
    }

    .card-title {
        flex: 1;
    }

    .card-title h3 {
        font-size: 1.125rem;
        font-weight: 600;
        color: var(--gray-900);
        margin-bottom: 0.25rem;
    }

    .card-subtitle {
        color: var(--gray-500);
        font-size: 0.8rem;
    }

    .card-body {
        padding: 1.5rem;
    }

    /* Grid des paramètres optimisé */
    .settings-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 1.25rem;
    }

    .setting-item {
        background: var(--gray-50);
        border-radius: var(--radius-lg);
        padding: 1.25rem;
        border: 1px solid var(--gray-200);
        transition: all 0.2s ease;
    }

    .setting-item:hover {
        background: var(--white);
        box-shadow: var(--shadow);
    }

    .setting-header {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        margin-bottom: 0.75rem;
    }

    .setting-icon {
        color: var(--primary);
        font-size: 1rem;
    }

    .setting-label {
        font-weight: 600;
        color: var(--gray-800);
        font-size: 0.95rem;
        flex: 1;
    }

    .tooltip-icon {
        color: var(--gray-400);
        cursor: help;
        font-size: 0.875rem;
        position: relative;
    }

    .tooltip-icon:hover::after {
        content: attr(data-tooltip);
        position: absolute;
        bottom: 100%;
        left: 50%;
        transform: translateX(-50%);
        background: var(--gray-800);
        color: var(--white);
        padding: 0.5rem;
        border-radius: 6px;
        font-size: 0.8rem;
        white-space: nowrap;
        z-index: 1000;
        margin-bottom: 5px;
    }

    .setting-description {
        color: var(--gray-600);
        font-size: 0.8rem;
        margin-bottom: 1rem;
        line-height: 1.4;
    }

    /* Inputs modernisés */
    .input-group {
        position: relative;
    }

    .setting-input {
        width: 100%;
        padding: 0.75rem 1rem;
        border: 2px solid var(--gray-200);
        border-radius: var(--radius);
        font-size: 0.875rem;
        transition: all 0.2s ease;
        background: var(--white);
        color: var(--gray-800);
    }

    .setting-input:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgb(99 102 241 / 0.1);
        outline: none;
    }

    .setting-input.error {
        border-color: var(--danger);
        box-shadow: 0 0 0 3px rgb(239 68 68 / 0.1);
    }

    .input-unit {
        position: absolute;
        right: 1rem;
        top: 50%;
        transform: translateY(-50%);
        color: var(--gray-400);
        font-size: 0.75rem;
        pointer-events: none;
    }

    .error-message {
        color: var(--danger);
        font-size: 0.75rem;
        margin-top: 0.5rem;
    }

    /* Switch amélioré */
    .switch-container {
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .switch {
        position: relative;
        width: 44px;
        height: 24px;
    }

    .switch input {
        opacity: 0;
        width: 0;
        height: 0;
    }

    .switch-slider {
        position: absolute;
        cursor: pointer;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: var(--gray-300);
        border-radius: 24px;
        transition: 0.3s;
    }

    .switch-slider:before {
        position: absolute;
        content: "";
        height: 18px;
        width: 18px;
        left: 3px;
        bottom: 3px;
        background-color: var(--white);
        border-radius: 50%;
        transition: 0.3s;
        box-shadow: var(--shadow-sm);
    }

    input:checked + .switch-slider {
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
    }

    input:checked + .switch-slider:before {
        transform: translateX(20px);
    }

    .switch-label {
        font-size: 0.875rem;
        color: var(--gray-600);
    }

    /* Actions optimisées */
    .actions-section {
        background: var(--white);
        border-radius: var(--radius-xl);
        box-shadow: var(--shadow);
        border: 1px solid var(--gray-200);
        padding: 2rem;
        margin-top: 2rem;
    }

    .actions-header {
        text-align: center;
        margin-bottom: 1.5rem;
    }

    .actions-header h3 {
        font-size: 1.25rem;
        font-weight: 600;
        color: var(--gray-900);
        margin-bottom: 0.5rem;
    }

    .actions-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1rem;
    }

    .action-btn {
        padding: 0.875rem 1.5rem;
        border: none;
        border-radius: var(--radius-lg);
        font-weight: 600;
        font-size: 0.875rem;
        cursor: pointer;
        transition: all 0.2s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        text-decoration: none;
        position: relative;
        overflow: hidden;
    }

    .action-btn:hover {
        transform: translateY(-2px);
        box-shadow: var(--shadow-lg);
    }

    .action-btn:active {
        transform: translateY(0);
    }

    .btn-save {
        background: linear-gradient(135deg, var(--success) 0%, #059669 100%);
        color: var(--white);
    }

    .btn-export {
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
        color: var(--white);
    }

    .btn-reset {
        background: linear-gradient(135deg, var(--warning) 0%, #d97706 100%);
        color: var(--white);
    }

    .btn-import {
        background: linear-gradient(135deg, var(--gray-600) 0%, var(--gray-700) 100%);
        color: var(--white);
    }

    /* Import section */
    .import-section {
        background: var(--gray-50);
        border: 2px dashed var(--gray-300);
        border-radius: var(--radius-lg);
        padding: 1.5rem;
        text-align: center;
        margin-top: 1rem;
    }

    .import-section h4 {
        color: var(--gray-800);
        margin-bottom: 0.5rem;
    }

    .file-input {
        margin: 1rem 0;
        padding: 0.5rem;
        border: 1px solid var(--gray-300);
        border-radius: var(--radius);
        background: var(--white);
    }

    /* Alerts */
    .alert {
        padding: 1rem 1.25rem;
        border-radius: var(--radius-lg);
        margin-bottom: 1.5rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.875rem;
    }

    .alert-success {
        background: rgb(16 185 129 / 0.1);
        color: #065f46;
        border: 1px solid rgb(16 185 129 / 0.2);
    }

    .alert-danger {
        background: rgb(239 68 68 / 0.1);
        color: #991b1b;
        border: 1px solid rgb(239 68 68 / 0.2);
    }

    .alert-dismissible .btn-close {
        background: none;
        border: none;
        color: inherit;
        opacity: 0.7;
        cursor: pointer;
        margin-left: auto;
    }

    /* Modal */
    .modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        z-index: 1000;
        align-items: center;
        justify-content: center;
    }

    .modal.show {
        display: flex;
    }

    .modal-dialog {
        width: 90%;
        max-width: 400px;
    }

    .modal-content {
        background: var(--white);
        border-radius: var(--radius-xl);
        box-shadow: var(--shadow-lg);
        overflow: hidden;
    }

    .modal-header {
        padding: 1.5rem;
        border-bottom: 1px solid var(--gray-200);
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .modal-title {
        font-size: 1.125rem;
        font-weight: 600;
        color: var(--gray-900);
    }

    .btn-close {
        background: none;
        border: none;
        font-size: 1.25rem;
        cursor: pointer;
        color: var(--gray-400);
        width: 24px;
        height: 24px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .modal-body {
        padding: 1.5rem;
    }

    .modal-footer {
        padding: 1rem 1.5rem;
        background: var(--gray-50);
        display: flex;
        gap: 0.75rem;
        justify-content: flex-end;
    }

    .btn {
        padding: 0.5rem 1rem;
        border: none;
        border-radius: var(--radius);
        font-weight: 500;
        font-size: 0.875rem;
        cursor: pointer;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    .btn-secondary {
        background: var(--gray-300);
        color: var(--gray-700);
    }

    .btn-danger {
        background: linear-gradient(135deg, var(--danger) 0%, #dc2626 100%);
        color: var(--white);
    }

    /* Responsive */
    @media (max-width: 768px) {
        .container {
            padding: 1rem;
        }

        .header {
            padding: 1rem 1.25rem;
            flex-direction: column;
            text-align: center;
            gap: 0.75rem;
        }

        .header-content h1 {
            font-size: 1.5rem;
        }

        .section-grid {
            grid-template-columns: 1fr;
        }

        .settings-grid {
            grid-template-columns: 1fr;
        }

        .actions-grid {
            grid-template-columns: 1fr;
        }

        .nav-container {
            padding: 0.25rem;
        }

        .nav-tab {
            padding: 0.625rem 1rem;
            font-size: 0.8rem;
        }
    }

    /* Animations */
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }

    /* Value indicators */
    .value-indicator {
        display: inline-block;
        padding: 0.25rem 0.75rem;
        background: rgb(99 102 241 / 0.1);
        color: var(--primary-dark);
        border-radius: var(--radius);
        font-size: 0.75rem;
        font-weight: 500;
        margin-left: 0.5rem;
    }

    /* Icon colors */
    .icon-blue { background: linear-gradient(135deg, var(--info) 0%, #1d4ed8 100%); }
    .icon-orange { background: linear-gradient(135deg, var(--warning) 0%, #d97706 100%); }
    .icon-red { background: linear-gradient(135deg, var(--danger) 0%, #dc2626 100%); }
    .icon-green { background: linear-gradient(135deg, var(--success) 0%, #059669 100%); }
    .icon-purple { background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%); }
    .icon-cyan { background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%); }
    .icon-gray { background: linear-gradient(135deg, var(--gray-600) 0%, var(--gray-700) 100%); }
</style>
@endsection

@section('content')
<div class="container">
    <!-- Header compact -->
    <div class="header">
        <div class="header-icon">
            <i class="fas fa-cogs"></i>
        </div>
        <div class="header-content">
            <h1>Paramètres du Système</h1>
            <p>Configuration des files de traitement et interfaces</p>
        </div>
    </div>

    <!-- Alerts -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle"></i>
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert">&times;</button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle"></i>
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert">&times;</button>
        </div>
    @endif

    <!-- Navigation modernisée -->
    <div class="nav-container">
        <div class="nav-tabs">
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
    </div>

    <form action="{{ route('admin.settings.store') }}" method="POST" id="settings-form">
        @csrf

        <!-- Section Files de Traitement -->
        <div class="section active" id="section-queues">
            <div class="section-grid">
                <!-- File Standard -->
                <div class="settings-card">
                    <div class="card-header">
                        <div class="card-icon icon-blue">
                            <i class="fas fa-phone"></i>
                        </div>
                        <div class="card-title">
                            <h3>File Standard</h3>
                            <p class="card-subtitle">Nouvelles commandes non assignées</p>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="settings-grid">
                            <div class="setting-item">
                                <div class="setting-header">
                                    <i class="setting-icon fas fa-redo"></i>
                                    <span class="setting-label">Tentatives quotidiennes</span>
                                    <i class="tooltip-icon fas fa-info-circle" data-tooltip="Nombre max d'appels par jour"></i>
                                </div>
                                <p class="setting-description">Limite quotidienne d'appels pour une commande</p>
                                <div class="input-group">
                                    <input type="number" name="standard_max_daily_attempts" 
                                           value="{{ $settings['standard_max_daily_attempts'] }}" 
                                           class="setting-input" min="1" max="10" required>
                                    <span class="input-unit">par jour</span>
                                </div>
                            </div>

                            <div class="setting-item">
                                <div class="setting-header">
                                    <i class="setting-icon fas fa-clock"></i>
                                    <span class="setting-label">Délai entre tentatives</span>
                                </div>
                                <p class="setting-description">Temps d'attente entre deux appels</p>
                                <div class="input-group">
                                    <input type="number" name="standard_delay_hours" 
                                           value="{{ $settings['standard_delay_hours'] }}" 
                                           class="setting-input" min="0.5" max="24" step="0.5" required>
                                    <span class="input-unit">heures</span>
                                </div>
                            </div>

                            <div class="setting-item">
                                <div class="setting-header">
                                    <i class="setting-icon fas fa-ban"></i>
                                    <span class="setting-label">Total maximum</span>
                                </div>
                                <p class="setting-description">Limite totale avant passage en file ancienne</p>
                                <div class="input-group">
                                    <input type="number" name="standard_max_total_attempts" 
                                           value="{{ $settings['standard_max_total_attempts'] }}" 
                                           class="setting-input" min="1" max="50" required>
                                    <span class="input-unit">tentatives</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- File Datée -->
                <div class="settings-card">
                    <div class="card-header">
                        <div class="card-icon icon-orange">
                            <i class="fas fa-calendar-alt"></i>
                        </div>
                        <div class="card-title">
                            <h3>File Datée</h3>
                            <p class="card-subtitle">Commandes avec rappel programmé</p>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="settings-grid">
                            <div class="setting-item">
                                <div class="setting-header">
                                    <i class="setting-icon fas fa-redo"></i>
                                    <span class="setting-label">Tentatives quotidiennes</span>
                                </div>
                                <p class="setting-description">Limite quotidienne pour commandes datées</p>
                                <div class="input-group">
                                    <input type="number" name="dated_max_daily_attempts" 
                                           value="{{ $settings['dated_max_daily_attempts'] }}" 
                                           class="setting-input" min="1" max="10" required>
                                    <span class="input-unit">par jour</span>
                                </div>
                            </div>

                            <div class="setting-item">
                                <div class="setting-header">
                                    <i class="setting-icon fas fa-clock"></i>
                                    <span class="setting-label">Délai entre tentatives</span>
                                </div>
                                <p class="setting-description">Temps d'attente pour commandes datées</p>
                                <div class="input-group">
                                    <input type="number" name="dated_delay_hours" 
                                           value="{{ $settings['dated_delay_hours'] }}" 
                                           class="setting-input" min="0.5" max="24" step="0.5" required>
                                    <span class="input-unit">heures</span>
                                </div>
                            </div>

                            <div class="setting-item">
                                <div class="setting-header">
                                    <i class="setting-icon fas fa-ban"></i>
                                    <span class="setting-label">Total maximum</span>
                                </div>
                                <p class="setting-description">Limite totale pour commandes datées</p>
                                <div class="input-group">
                                    <input type="number" name="dated_max_total_attempts" 
                                           value="{{ $settings['dated_max_total_attempts'] }}" 
                                           class="setting-input" min="1" max="20" required>
                                    <span class="input-unit">tentatives</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- File Ancienne -->
                <div class="settings-card">
                    <div class="card-header">
                        <div class="card-icon icon-red">
                            <i class="fas fa-history"></i>
                        </div>
                        <div class="card-title">
                            <h3>File Ancienne</h3>
                            <p class="card-subtitle">Commandes ayant dépassé le seuil</p>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="settings-grid">
                            <div class="setting-item">
                                <div class="setting-header">
                                    <i class="setting-icon fas fa-redo"></i>
                                    <span class="setting-label">Tentatives quotidiennes</span>
                                </div>
                                <p class="setting-description">Limite quotidienne pour commandes anciennes</p>
                                <div class="input-group">
                                    <input type="number" name="old_max_daily_attempts" 
                                           value="{{ $settings['old_max_daily_attempts'] }}" 
                                           class="setting-input" min="1" max="10" required>
                                    <span class="input-unit">par jour</span>
                                </div>
                            </div>

                            <div class="setting-item">
                                <div class="setting-header">
                                    <i class="setting-icon fas fa-clock"></i>
                                    <span class="setting-label">Délai entre tentatives</span>
                                </div>
                                <p class="setting-description">Temps d'attente prolongé</p>
                                <div class="input-group">
                                    <input type="number" name="old_delay_hours" 
                                           value="{{ $settings['old_delay_hours'] }}" 
                                           class="setting-input" min="1" max="48" step="0.5" required>
                                    <span class="input-unit">heures</span>
                                </div>
                            </div>

                            <div class="setting-item">
                                <div class="setting-header">
                                    <i class="setting-icon fas fa-ban"></i>
                                    <span class="setting-label">Total maximum</span>
                                </div>
                                <p class="setting-description">Limite totale (0 = illimité)</p>
                                <div class="input-group">
                                    <input type="number" name="old_max_total_attempts" 
                                           value="{{ $settings['old_max_total_attempts'] }}" 
                                           class="setting-input" min="0" max="30" required>
                                    <span class="input-unit">tentatives</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- File Retour en Stock -->
                <div class="settings-card">
                    <div class="card-header">
                        <div class="card-icon icon-green">
                            <i class="fas fa-box-open"></i>
                        </div>
                        <div class="card-title">
                            <h3>File Retour en Stock</h3>
                            <p class="card-subtitle">Commandes réactivées</p>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="settings-grid">
                            <div class="setting-item">
                                <div class="setting-header">
                                    <i class="setting-icon fas fa-redo"></i>
                                    <span class="setting-label">Tentatives quotidiennes</span>
                                </div>
                                <p class="setting-description">Limite pour commandes réactivées</p>
                                <div class="input-group">
                                    <input type="number" name="restock_max_daily_attempts" 
                                           value="{{ $settings['restock_max_daily_attempts'] }}" 
                                           class="setting-input" min="1" max="10" required>
                                    <span class="input-unit">par jour</span>
                                </div>
                            </div>

                            <div class="setting-item">
                                <div class="setting-header">
                                    <i class="setting-icon fas fa-clock"></i>
                                    <span class="setting-label">Délai entre tentatives</span>
                                </div>
                                <p class="setting-description">Temps d'attente après retour stock</p>
                                <div class="input-group">
                                    <input type="number" name="restock_delay_hours" 
                                           value="{{ $settings['restock_delay_hours'] }}" 
                                           class="setting-input" min="0.5" max="24" step="0.5" required>
                                    <span class="input-unit">heures</span>
                                </div>
                            </div>

                            <div class="setting-item">
                                <div class="setting-header">
                                    <i class="setting-icon fas fa-ban"></i>
                                    <span class="setting-label">Total maximum</span>
                                </div>
                                <p class="setting-description">Limite totale retour en stock</p>
                                <div class="input-group">
                                    <input type="number" name="restock_max_total_attempts" 
                                           value="{{ $settings['restock_max_total_attempts'] }}" 
                                           class="setting-input" min="1" max="20" required>
                                    <span class="input-unit">tentatives</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Section Interfaces -->
        <div class="section" id="section-interfaces">
            <div class="section-grid">
                <div class="settings-card">
                    <div class="card-header">
                        <div class="card-icon icon-orange">
                            <i class="fas fa-search"></i>
                        </div>
                        <div class="card-title">
                            <h3>Interface d'Examen</h3>
                            <p class="card-subtitle">Configuration de l'examen des problèmes</p>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="settings-grid">
                            <div class="setting-item">
                                <div class="setting-header">
                                    <i class="setting-icon fas fa-sync-alt"></i>
                                    <span class="setting-label">Rafraîchissement auto</span>
                                </div>
                                <p class="setting-description">Fréquence de mise à jour automatique</p>
                                <div class="input-group">
                                    <input type="number" name="examination_auto_refresh_interval" 
                                           value="{{ $settings['examination_auto_refresh_interval'] }}" 
                                           class="setting-input" min="30" max="600" required>
                                    <span class="input-unit">secondes</span>
                                </div>
                            </div>

                            <div class="setting-item">
                                <div class="setting-header">
                                    <i class="setting-icon fas fa-list"></i>
                                    <span class="setting-label">Commandes par page</span>
                                </div>
                                <p class="setting-description">Nombre max de commandes affichées</p>
                                <div class="input-group">
                                    <input type="number" name="examination_max_orders_per_page" 
                                           value="{{ $settings['examination_max_orders_per_page'] }}" 
                                           class="setting-input" min="10" max="100" required>
                                    <span class="input-unit">commandes</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="settings-card">
                    <div class="card-header">
                        <div class="card-icon icon-purple">
                            <i class="fas fa-pause-circle"></i>
                        </div>
                        <div class="card-title">
                            <h3>Commandes Suspendues</h3>
                            <p class="card-subtitle">Configuration de la gestion des suspensions</p>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="settings-grid">
                            <div class="setting-item">
                                <div class="setting-header">
                                    <i class="setting-icon fas fa-sync-alt"></i>
                                    <span class="setting-label">Vérification auto</span>
                                </div>
                                <p class="setting-description">Fréquence de vérification du statut</p>
                                <div class="input-group">
                                    <input type="number" name="suspended_auto_check_interval" 
                                           value="{{ $settings['suspended_auto_check_interval'] }}" 
                                           class="setting-input" min="60" max="1800" required>
                                    <span class="input-unit">secondes</span>
                                </div>
                            </div>

                            <div class="setting-item">
                                <div class="setting-header">
                                    <i class="setting-icon fas fa-list"></i>
                                    <span class="setting-label">Commandes par page</span>
                                </div>
                                <p class="setting-description">Nombre max de commandes suspendues</p>
                                <div class="input-group">
                                    <input type="number" name="suspended_max_orders_per_page" 
                                           value="{{ $settings['suspended_max_orders_per_page'] }}" 
                                           class="setting-input" min="10" max="100" required>
                                    <span class="input-unit">commandes</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Section Automatisation -->
        <div class="section" id="section-automation">
            <div class="section-grid">
                <div class="settings-card">
                    <div class="card-header">
                        <div class="card-icon icon-cyan">
                            <i class="fas fa-robot"></i>
                        </div>
                        <div class="card-title">
                            <h3>Suspension Automatique</h3>
                            <p class="card-subtitle">Règles de suspension automatique</p>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="settings-grid">
                            <div class="setting-item">
                                <div class="setting-header">
                                    <i class="setting-icon fas fa-pause"></i>
                                    <span class="setting-label">Suspension sur problème stock</span>
                                </div>
                                <p class="setting-description">Suspendre automatiquement les commandes problématiques</p>
                                <div class="switch-container">
                                    <label class="switch">
                                        <input type="checkbox" name="auto_suspend_on_stock_issue" value="1" 
                                               {{ $settings['auto_suspend_on_stock_issue'] ? 'checked' : '' }}>
                                        <span class="switch-slider"></span>
                                    </label>
                                    <span class="switch-label">Activé</span>
                                </div>
                            </div>

                            <div class="setting-item">
                                <div class="setting-header">
                                    <i class="setting-icon fas fa-calendar-times"></i>
                                    <span class="setting-label">Seuil de suspension</span>
                                </div>
                                <p class="setting-description">Jours sans stock avant suspension auto</p>
                                <div class="input-group">
                                    <input type="number" name="auto_suspend_threshold_days" 
                                           value="{{ $settings['auto_suspend_threshold_days'] }}" 
                                           class="setting-input" min="1" max="30" required>
                                    <span class="input-unit">jours</span>
                                </div>
                            </div>

                            <div class="setting-item">
                                <div class="setting-header">
                                    <i class="setting-icon fas fa-bell"></i>
                                    <span class="setting-label">Notifications retour stock</span>
                                </div>
                                <p class="setting-description">Notifier quand les produits reviennent</p>
                                <div class="switch-container">
                                    <label class="switch">
                                        <input type="checkbox" name="restock_notification_enabled" value="1" 
                                               {{ $settings['restock_notification_enabled'] ? 'checked' : '' }}>
                                        <span class="switch-slider"></span>
                                    </label>
                                    <span class="switch-label">Activé</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Section Performance -->
        <div class="section" id="section-performance">
            <div class="section-grid">
                <div class="settings-card">
                    <div class="card-header">
                        <div class="card-icon icon-green">
                            <i class="fas fa-tachometer-alt"></i>
                        </div>
                        <div class="card-title">
                            <h3>Optimisation Performances</h3>
                            <p class="card-subtitle">Paramètres pour améliorer les performances</p>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="settings-grid">
                            <div class="setting-item">
                                <div class="setting-header">
                                    <i class="setting-icon fas fa-database"></i>
                                    <span class="setting-label">Cache vérification stock</span>
                                </div>
                                <p class="setting-description">Durée de mise en cache des vérifications</p>
                                <div class="input-group">
                                    <input type="number" name="stock_check_cache_duration" 
                                           value="{{ $settings['stock_check_cache_duration'] }}" 
                                           class="setting-input" min="60" max="3600" required>
                                    <span class="input-unit">secondes</span>
                                </div>
                            </div>

                            <div class="setting-item">
                                <div class="setting-header">
                                    <i class="setting-icon fas fa-tasks"></i>
                                    <span class="setting-label">Actions groupées max</span>
                                </div>
                                <p class="setting-description">Limite d'actions groupées simultanées</p>
                                <div class="input-group">
                                    <input type="number" name="bulk_action_max_orders" 
                                           value="{{ $settings['bulk_action_max_orders'] }}" 
                                           class="setting-input" min="10" max="500" required>
                                    <span class="input-unit">commandes</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Section Avancé -->
        <div class="section" id="section-advanced">
            <div class="section-grid">
                <div class="settings-card">
                    <div class="card-header">
                        <div class="card-icon icon-gray">
                            <i class="fas fa-tools"></i>
                        </div>
                        <div class="card-title">
                            <h3>Gestion Avancée</h3>
                            <p class="card-subtitle">Import/Export et réinitialisation</p>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="import-section">
                            <h4><i class="fas fa-file-import"></i> Importer Configuration</h4>
                            <p>Restaurer des paramètres depuis un fichier JSON</p>
                            <input type="file" accept=".json" class="file-input" id="import-file">
                            <button type="button" class="action-btn btn-import" onclick="importSettings()">
                                <i class="fas fa-upload"></i>
                                Importer
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <!-- Actions -->
    <div class="actions-section">
        <div class="actions-header">
            <h3><i class="fas fa-save"></i> Actions</h3>
            <p>Gérez vos paramètres et configurations</p>
        </div>
        
        <div class="actions-grid">
            <button type="submit" form="settings-form" class="action-btn btn-save">
                <i class="fas fa-save"></i>
                Sauvegarder
            </button>
            
            <button type="button" class="action-btn btn-export" onclick="exportSettings()">
                <i class="fas fa-download"></i>
                Exporter
            </button>
            
            <button type="button" class="action-btn btn-reset" onclick="confirmReset()">
                <i class="fas fa-undo"></i>
                Réinitialiser
            </button>
        </div>
    </div>
</div>

<!-- Modal de confirmation pour reset -->
<div class="modal fade" id="resetModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirmer la Réinitialisation</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal">&times;</button>
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
    // Navigation entre sections
    $('.nav-tab').click(function() {
        const section = $(this).data('section');
        
        // Mettre à jour les onglets
        $('.nav-tab').removeClass('active');
        $(this).addClass('active');
        
        // Mettre à jour les sections
        $('.section').removeClass('active');
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

    // Gestion des alerts auto-dismiss
    $('.alert-dismissible .btn-close').click(function() {
        $(this).parent('.alert').fadeOut();
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

// Support pour Bootstrap modal si jQuery n'est pas disponible
if (typeof $ === 'undefined') {
    // Version vanilla JavaScript pour les modals
    function confirmReset() {
        document.getElementById('resetModal').classList.add('show');
        document.getElementById('resetModal').style.display = 'flex';
    }
    
    // Fermeture du modal
    document.querySelectorAll('[data-bs-dismiss="modal"]').forEach(btn => {
        btn.addEventListener('click', function() {
            const modal = this.closest('.modal');
            modal.classList.remove('show');
            modal.style.display = 'none';
        });
    });
    
    // Fermeture en cliquant à l'extérieur
    document.querySelectorAll('.modal').forEach(modal => {
        modal.addEventListener('click', function(e) {
            if (e.target === this) {
                this.classList.remove('show');
                this.style.display = 'none';
            }
        });
    });
}
</script>
@endsection