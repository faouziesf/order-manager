@extends('layouts.admin')

@section('title', 'Modifier Configuration - ' . $config->integration_name)

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
        max-width: 1400px;
        margin: 0 auto;
        padding: 1.5rem;
    }

    /* ===== HEADER MODERNE ===== */
    .config-header {
        background: white;
        border-radius: var(--radius);
        box-shadow: var(--shadow);
        padding: 2rem;
        margin-bottom: 2rem;
        border-left: 4px solid var(--primary);
        position: relative;
        overflow: hidden;
    }

    .config-header::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -10%;
        width: 150px;
        height: 150px;
        background: linear-gradient(135deg, rgba(30, 64, 175, 0.1), rgba(59, 130, 246, 0.05));
        border-radius: 50%;
        animation: float 8s ease-in-out infinite;
    }

    @keyframes float {
        0%, 100% { transform: translateY(0px) rotate(0deg); }
        50% { transform: translateY(-15px) rotate(180deg); }
    }

    .header-content {
        position: relative;
        z-index: 2;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 1.5rem;
    }

    .header-info h1 {
        font-size: 1.75rem;
        font-weight: 800;
        color: var(--dark);
        margin-bottom: 0.5rem;
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .header-info p {
        color: #6b7280;
        margin: 0;
        font-size: 1rem;
        font-weight: 500;
    }

    .header-actions {
        display: flex;
        gap: 1rem;
        flex-wrap: wrap;
        align-items: center;
    }

    .status-badge {
        padding: 0.75rem 1.25rem;
        border-radius: 25px;
        font-size: 0.85rem;
        font-weight: 700;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }

    .status-active {
        background: linear-gradient(135deg, #dcfce7, #bbf7d0);
        color: #166534;
        border: 1px solid #86efac;
    }

    .status-inactive {
        background: linear-gradient(135deg, #fef3c7, #fde68a);
        color: #92400e;
        border: 1px solid #fbbf24;
    }

    /* ===== LAYOUT PRINCIPAL ===== */
    .config-layout {
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 2rem;
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
        padding: 1.5rem 2rem;
        display: flex;
        align-items: center;
        gap: 1rem;
        position: relative;
    }

    .form-header::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -20%;
        width: 100px;
        height: 100px;
        background: rgba(255, 255, 255, 0.1);
        border-radius: 50%;
    }

    .carrier-logo {
        width: 48px;
        height: 48px;
        object-fit: contain;
        background: rgba(255, 255, 255, 0.2);
        border-radius: 8px;
        padding: 8px;
        border: 1px solid rgba(255, 255, 255, 0.3);
        position: relative;
        z-index: 2;
    }

    .form-header-info {
        position: relative;
        z-index: 2;
    }

    .form-header h3 {
        margin: 0;
        font-size: 1.25rem;
        font-weight: 700;
    }

    .form-header small {
        opacity: 0.8;
        font-size: 0.85rem;
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

    .form-control.is-invalid, .form-control.error {
        border-color: var(--danger);
        background-color: #fef2f2;
    }

    .form-control.is-valid, .form-control.success {
        border-color: var(--success);
        background-color: #ecfdf5;
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
        background: linear-gradient(135deg, #f3f4f6, #e5e7eb);
        border: 2px solid var(--border);
        border-left: none;
        border-top-right-radius: 8px;
        border-bottom-right-radius: 8px;
        padding: 1rem;
        cursor: pointer;
        transition: var(--transition);
        display: flex;
        align-items: center;
        justify-content: center;
        min-width: 48px;
    }

    .input-group-text:hover {
        background: linear-gradient(135deg, #e5e7eb, #d1d5db);
        transform: scale(1.05);
    }

    .form-text, .form-help {
        font-size: 0.8rem;
        color: #6b7280;
        margin-top: 0.5rem;
        display: flex;
        align-items: flex-start;
        gap: 0.5rem;
        line-height: 1.4;
    }

    .invalid-feedback, .form-error {
        color: var(--danger);
        font-size: 0.85rem;
        margin-top: 0.75rem;
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

    .valid-feedback, .form-success {
        color: var(--success);
        font-size: 0.85rem;
        margin-top: 0.75rem;
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

    .alert {
        padding: 1.25rem;
        border-radius: 8px;
        margin-bottom: 1.5rem;
        display: flex;
        align-items: center;
        gap: 0.75rem;
        border: none;
        font-weight: 500;
    }

    .alert-success {
        background: linear-gradient(135deg, #dcfce7, #bbf7d0);
        color: #166534;
        border-left: 4px solid var(--success);
    }

    .alert-danger {
        background: linear-gradient(135deg, #fee2e2, #fecaca);
        color: #991b1b;
        border-left: 4px solid var(--danger);
    }

    /* ===== INFO CARD ===== */
    .info-card {
        background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
        border-radius: var(--radius);
        padding: 2rem;
        border: 1px solid var(--border);
        margin-top: 1.5rem;
    }

    .info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1.5rem;
    }

    .info-item {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }

    .info-label {
        font-weight: 700;
        color: var(--dark);
        font-size: 0.8rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .info-value {
        color: #6b7280;
        font-size: 0.95rem;
        font-weight: 500;
    }

    /* ===== ACTIONS FOOTER ===== */
    .form-actions {
        background: linear-gradient(135deg, #f9fafb, #f3f4f6);
        padding: 2rem;
        border-top: 1px solid var(--border);
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
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

    .btn:disabled {
        opacity: 0.6;
        cursor: not-allowed;
        transform: none !important;
    }

    .btn-primary {
        background: linear-gradient(135deg, var(--primary), var(--primary-dark));
        color: white;
        box-shadow: 0 4px 15px rgba(30, 64, 175, 0.3);
    }

    .btn-primary:hover {
        box-shadow: 0 8px 25px rgba(30, 64, 175, 0.4);
    }

    .btn-success {
        background: linear-gradient(135deg, var(--success), #047857);
        color: white;
        box-shadow: 0 4px 15px rgba(16, 185, 129, 0.3);
    }

    .btn-warning {
        background: linear-gradient(135deg, var(--warning), #d97706);
        color: white;
        box-shadow: 0 4px 15px rgba(245, 158, 11, 0.3);
    }

    .btn-danger {
        background: linear-gradient(135deg, var(--danger), #dc2626);
        color: white;
        box-shadow: 0 4px 15px rgba(239, 68, 68, 0.3);
    }

    .btn-outline {
        background: white;
        color: var(--dark);
        border: 2px solid var(--border);
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
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
        transition: var(--transition);
    }

    .sidebar-card:hover {
        transform: translateY(-2px);
        box-shadow: var(--shadow-lg);
    }

    .sidebar-header {
        background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
        padding: 1.25rem 1.5rem;
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
        padding: 1.25rem;
        background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
        border-radius: 8px;
        border: 1px solid var(--border);
        transition: var(--transition);
    }

    .stat-item:hover {
        transform: scale(1.05);
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    }

    .stat-number {
        font-size: 1.75rem;
        font-weight: 800;
        color: var(--primary);
        margin-bottom: 0.25rem;
        display: block;
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
        gap: 1rem;
    }

    .info-link {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        color: var(--dark);
        text-decoration: none;
        transition: var(--transition);
        font-size: 0.9rem;
        padding: 0.5rem;
        border-radius: 6px;
    }

    .info-link:hover {
        color: var(--primary);
        background: #f8fafc;
        text-decoration: none;
        transform: translateX(5px);
    }

    .limits-info {
        background: linear-gradient(135deg, #f0f9ff, #e0f2fe);
        border-radius: 8px;
        padding: 1.25rem;
        margin-top: 1.5rem;
        border-left: 3px solid var(--info);
    }

    .limits-title {
        font-weight: 600;
        color: var(--dark);
        margin-bottom: 0.75rem;
        font-size: 0.9rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .limits-list {
        color: #6b7280;
        font-size: 0.85rem;
        line-height: 1.6;
    }

    /* ===== MODAL ===== */
    .test-modal {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        z-index: 10000;
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0;
        visibility: hidden;
        transition: var(--transition);
        backdrop-filter: blur(5px);
    }

    .test-modal.show {
        opacity: 1;
        visibility: visible;
    }

    .test-modal-content {
        background: white;
        border-radius: var(--radius);
        padding: 2rem;
        max-width: 450px;
        width: 90%;
        box-shadow: var(--shadow-lg);
        transform: scale(0.9);
        transition: var(--transition);
    }

    .test-modal.show .test-modal-content {
        transform: scale(1);
    }

    /* ===== NOTIFICATIONS ===== */
    .toast {
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 10001;
        min-width: 350px;
        max-width: 450px;
        box-shadow: var(--shadow-lg);
        border-radius: 8px;
        overflow: hidden;
        animation: slideInRight 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        backdrop-filter: blur(10px);
    }

    .toast-success {
        background: linear-gradient(135deg, var(--success), #047857);
        color: white;
    }

    .toast-danger {
        background: linear-gradient(135deg, var(--danger), #dc2626);
        color: white;
    }

    .toast-warning {
        background: linear-gradient(135deg, var(--warning), #d97706);
        color: white;
    }

    .toast-content {
        padding: 1.25rem;
        display: flex;
        align-items: center;
        gap: 0.75rem;
        font-weight: 600;
        font-size: 0.9rem;
    }

    @keyframes slideInRight {
        from { transform: translateX(100%); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }

    @keyframes slideOutRight {
        from { transform: translateX(0); opacity: 1; }
        to { transform: translateX(100%); opacity: 0; }
    }

    /* ===== ANIMATIONS ===== */
    .fade-in {
        animation: fadeIn 0.6s cubic-bezier(0.4, 0, 0.2, 1);
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
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

    /* ===== RESPONSIVE ===== */
    @media (max-width: 768px) {
        .config-container {
            padding: 1rem;
        }

        .config-header {
            padding: 1.5rem;
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
            padding: 1.5rem;
        }

        .form-actions {
            padding: 1.5rem;
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
                    <i class="fas fa-edit text-primary"></i>
                    Modifier la Configuration
                </h1>
                <p>{{ $config->integration_name }} • {{ $carrier['name'] }}</p>
            </div>
            <div class="header-actions">
                <a href="{{ route('admin.delivery.configuration') }}" class="btn btn-outline">
                    <i class="fas fa-arrow-left"></i>
                    Retour à la liste
                </a>
                <div class="status-badge {{ $config->is_active ? 'status-active' : 'status-inactive' }}">
                    <i class="fas fa-{{ $config->is_active ? 'check-circle' : 'pause-circle' }}"></i>
                    {{ $config->is_active ? 'Configuration Active' : 'Configuration Inactive' }}
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
                
                <div class="form-header-info">
                    <h3>{{ $carrier['name'] }}</h3>
                    <small>Configuration ID: {{ $config->id }} • Créée le {{ $config->created_at->format('d/m/Y') }}</small>
                </div>
            </div>

            <form id="configForm" novalidate>
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
                        <div class="form-text">
                            <i class="fas fa-info-circle"></i>
                            Nom unique pour identifier cette configuration dans votre système
                        </div>
                        @error('integration_name')
                            <div class="invalid-feedback">
                                <i class="fas fa-exclamation-triangle"></i>
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    @if($config->carrier_slug === 'jax_delivery')
                        <!-- Configuration JAX Delivery -->
                        <div class="two-columns">
                            <div class="form-group">
                                <label for="username" class="form-label">
                                    <i class="fas fa-user-circle text-primary"></i>
                                    Numéro de Compte JAX <span style="color: var(--danger);">*</span>
                                </label>
                                <input type="text" 
                                       class="form-control @error('username') is-invalid @enderror" 
                                       id="username" 
                                       name="username" 
                                       value="{{ old('username', $config->username) }}"
                                       placeholder="Ex: 2304"
                                       required>
                                <div class="form-text">
                                    <i class="fas fa-info-circle"></i>
                                    Numéro de compte fourni par JAX
                                </div>
                                @error('username')
                                    <div class="invalid-feedback">
                                        <i class="fas fa-exclamation-triangle"></i>
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                            
                            <div class="form-group">
                                <label for="password" class="form-label">
                                    <i class="fas fa-key text-primary"></i>
                                    Token JWT JAX
                                </label>
                                <div class="input-group">
                                    <input type="password" 
                                           class="form-control @error('password') is-invalid @enderror" 
                                           id="password" 
                                           name="password" 
                                           placeholder="Laisser vide pour conserver le token actuel"
                                           maxlength="500">
                                    <div class="input-group-append">
                                        <div class="input-group-text" onclick="togglePassword()">
                                            <i class="fas fa-eye" id="toggleIcon"></i>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-text">
                                    <i class="fas fa-info-circle"></i>
                                    Remplir seulement pour changer le token
                                </div>
                                @error('password')
                                    <div class="invalid-feedback">
                                        <i class="fas fa-exclamation-triangle"></i>
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                        </div>
                    @elseif($config->carrier_slug === 'mes_colis')
                        <!-- 🔥 CORRECTION : Configuration Mes Colis corrigée -->
                        <div class="form-group">
                            <label for="password" class="form-label">
                                <i class="fas fa-key text-primary"></i>
                                Token API Mes Colis <span style="color: var(--danger);">*</span>
                            </label>
                            <div class="input-group">
                                <input type="password" 
                                       class="form-control @error('password') is-invalid @enderror" 
                                       id="password" 
                                       name="password" 
                                       value="{{ old('password', $config->password ?: $config->username) }}"
                                       placeholder="Token d'accès Mes Colis"
                                       maxlength="500"
                                       required>
                                <div class="input-group-append">
                                    <div class="input-group-text" onclick="togglePasswordEdit()">
                                        <i class="fas fa-eye" id="toggleIconEdit"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="form-text">
                                <i class="fas fa-info-circle"></i>
                                Token d'authentification (x-access-token)
                                @if(method_exists($config, 'getConfigFormat') && $config->getConfigFormat() === 'ancien')
                                    <br><span style="color: var(--warning);"><i class="fas fa-exclamation-triangle"></i> Configuration en ancien format - sera migrée lors de la sauvegarde</span>
                                @endif
                            </div>
                            @error('password')
                                <div class="invalid-feedback">
                                    <i class="fas fa-exclamation-triangle"></i>
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>
                        
                        <!-- Champ username vide pour Mes Colis (pour rétrocompatibilité) -->
                        <input type="hidden" name="username" value="">
                    @endif

                    <!-- Environnement -->
                    <div class="form-group">
                        <label for="environment" class="form-label">
                            <i class="fas fa-server text-primary"></i>
                            Environnement <span style="color: var(--danger);">*</span>
                        </label>
                        <select class="form-control" id="environment" name="environment" required>
                            <option value="test" {{ old('environment', $config->environment) === 'test' ? 'selected' : '' }}>
                                Test / Sandbox
                            </option>
                            <option value="production" {{ old('environment', $config->environment) === 'production' ? 'selected' : '' }}>
                                Production
                            </option>
                        </select>
                        <div class="form-text">
                            <i class="fas fa-info-circle"></i>
                            Environnement d'exécution de la configuration
                        </div>
                    </div>

                    <!-- Statut actif -->
                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-toggle-on text-primary"></i>
                            Statut de la Configuration
                        </label>
                        <div style="display: flex; align-items: center; gap: 0.75rem; margin-top: 0.5rem;">
                            <input type="checkbox" 
                                   id="is_active" 
                                   name="is_active" 
                                   value="1"
                                   {{ old('is_active', $config->is_active) ? 'checked' : '' }}
                                   style="width: 18px; height: 18px;">
                            <label for="is_active" style="margin: 0; font-weight: 500; color: #374151;">
                                Configuration active et utilisable
                            </label>
                        </div>
                        <div class="form-text">
                            <i class="fas fa-info-circle"></i>
                            Une configuration active peut être utilisée pour créer des enlèvements
                        </div>
                    </div>

                    <!-- Informations Configuration -->
                    <div class="info-card">
                        <h6 style="margin-bottom: 1.5rem; color: var(--dark); font-weight: 700; font-size: 1rem;">
                            <i class="fas fa-info-circle text-primary"></i>
                            Informations de la Configuration
                        </h6>
                        <div class="info-grid">
                            <div class="info-item">
                                <div class="info-label">Statut Actuel</div>
                                <div class="info-value">
                                    <span class="status-badge {{ $config->is_active ? 'status-active' : 'status-inactive' }}" style="padding: 0.5rem 0.75rem; font-size: 0.8rem;">
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
                                <div class="info-label">Dernière modification</div>
                                <div class="info-value">{{ $config->updated_at->format('d/m/Y à H:i') }}</div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">Format de Configuration</div>
                                <div class="info-value">
                                    @if(method_exists($config, 'getConfigFormat'))
                                        {{ ucfirst($config->getConfigFormat()) }}
                                    @else
                                        Standard
                                    @endif
                                </div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">Validité</div>
                                <div class="info-value">
                                    <span class="status-badge {{ $config->is_valid ? 'status-active' : 'status-inactive' }}" style="padding: 0.25rem 0.5rem; font-size: 0.75rem;">
                                        {{ $config->is_valid ? 'Valide' : 'Invalide' }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-actions">
                    <div style="display: flex; gap: 0.75rem;">
                        <button type="button" 
                                class="btn btn-danger"
                                onclick="confirmDelete()"
                                id="deleteBtn">
                            <i class="fas fa-trash"></i>
                            Supprimer
                        </button>
                    </div>
                    
                    <div style="display: flex; gap: 0.75rem; flex-wrap: wrap;">
                        <button type="button" 
                                class="btn btn-warning"
                                onclick="testConnection()"
                                id="testBtn">
                            <i class="fas fa-flask"></i>
                            Tester Connexion
                        </button>
                        
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
                        Statistiques d'utilisation
                    </h3>
                </div>
                <div class="sidebar-body">
                    <div class="stats-grid">
                        <div class="stat-item">
                            <div class="stat-number">{{ $config->pickups()->count() }}</div>
                            <div class="stat-label">Enlèvements créés</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-number">{{ $config->shipments()->count() }}</div>
                            <div class="stat-label">Expéditions traitées</div>
                        </div>
                    </div>
                    
                    <div style="margin-top: 1.5rem; padding-top: 1.5rem; border-top: 1px solid var(--border);">
                        <div style="text-align: center; color: #6b7280; font-size: 0.85rem;">
                            <i class="fas fa-clock"></i>
                            Dernière activité: {{ $config->updated_at->diffForHumans() }}
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
                                Créer un Enlèvement
                            </a>
                        @endif
                        
                        <a href="{{ route('admin.delivery.pickups') }}?config_id={{ $config->id }}" 
                           class="btn btn-outline">
                            <i class="fas fa-boxes"></i>
                            Voir les Enlèvements
                        </a>
                        
                        <a href="{{ route('admin.delivery.shipments') }}?config_id={{ $config->id }}" 
                           class="btn btn-outline">
                            <i class="fas fa-shipping-fast"></i>
                            Voir les Expéditions
                        </a>

                        <button type="button" 
                                class="btn btn-outline"
                                onclick="diagnosticConfig()">
                            <i class="fas fa-stethoscope"></i>
                            Diagnostic Complet
                        </button>
                    </div>
                </div>
            </div>

            <!-- Informations Transporteur -->
            <div class="sidebar-card">
                <div class="sidebar-header">
                    <h3 class="sidebar-title">
                        <i class="fas fa-info-circle text-info"></i>
                        Informations Transporteur
                    </h3>
                </div>
                <div class="sidebar-body">
                    <div class="info-list">
                        @if($config->carrier_slug === 'jax_delivery')
                            <a href="https://jax-delivery.com" target="_blank" class="info-link">
                                <i class="fas fa-globe text-primary"></i>
                                Site web JAX Delivery
                            </a>
                            <a href="tel:+21671234567" class="info-link">
                                <i class="fas fa-phone text-success"></i>
                                Support technique
                            </a>
                        @elseif($config->carrier_slug === 'mes_colis')
                            <a href="https://mescolis.tn" target="_blank" class="info-link">
                                <i class="fas fa-globe text-primary"></i>
                                Site web Mes Colis
                            </a>
                            <a href="mailto:support@mescolis.tn" class="info-link">
                                <i class="fas fa-envelope text-info"></i>
                                Support par email
                            </a>
                        @endif
                    </div>

                    <div class="limits-info">
                        <div class="limits-title">
                            <i class="fas fa-exclamation-triangle text-warning"></i>
                            Limites & Contraintes
                        </div>
                        <div class="limits-list">
                            @if($config->carrier_slug === 'jax_delivery')
                                • Poids maximum: 30 kg<br>
                                • COD maximum: 5000 TND<br>
                                • Délai standard: 24-48h<br>
                                • 24 gouvernorats couverts
                            @elseif($config->carrier_slug === 'mes_colis')
                                • Poids maximum: 25 kg<br>
                                • COD maximum: 3000 TND<br>
                                • Délai standard: 48-72h<br>
                                • 24 gouvernorats couverts
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de test de connexion -->
<div class="test-modal" id="testModal">
    <div class="test-modal-content">
        <div id="testContent">
            <div style="text-align: center; padding: 1rem;">
                <i class="fas fa-spinner fa-spin" style="font-size: 2rem; color: var(--primary);"></i>
                <h4 style="margin: 1rem 0 0.5rem 0;">Test de connexion en cours...</h4>
                <p style="color: #6b7280; margin: 0;">Vérification des paramètres de connexion</p>
            </div>
        </div>
        <div style="margin-top: 1.5rem; text-align: center;">
            <button type="button" class="btn btn-outline" onclick="closeTestModal()">Fermer</button>
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
    carrierSlug: '{{ $config->carrier_slug }}',
    updateUrl: '{{ route("admin.delivery.configuration.update", $config) }}',
    testUrl: '{{ route("admin.delivery.configuration.test", $config) }}',
    toggleUrl: '{{ route("admin.delivery.configuration.toggle", $config) }}',
    deleteUrl: '{{ route("admin.delivery.configuration.delete", $config) }}',
    diagnosticUrl: '{{ route("admin.delivery.configuration.diagnostic", $config) }}',
    redirectUrl: '{{ route("admin.delivery.configuration") }}',
    csrfToken: document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
};

// ===== VARIABLES GLOBALES =====
let isSubmitting = false;
let isTestingConnection = false;

// ===== INITIALISATION =====
document.addEventListener('DOMContentLoaded', function() {
    console.log('🚀 Config Edit Initialized', CONFIG);
    setupFormHandler();
    setupValidation();
});

// ===== TOGGLE PASSWORD VISIBILITY =====
function togglePassword() {
    const input = document.getElementById('password');
    const icon = document.getElementById('toggleIcon');
    
    if (!input || !icon) return;
    
    if (input.type === 'password') {
        input.type = 'text';
        icon.className = 'fas fa-eye-slash';
    } else {
        input.type = 'password';
        icon.className = 'fas fa-eye';
    }
}

function togglePasswordEdit() {
    const input = document.getElementById('password');
    const icon = document.getElementById('toggleIconEdit');
    
    if (!input || !icon) return;
    
    if (input.type === 'password') {
        input.type = 'text';
        icon.className = 'fas fa-eye-slash';
    } else {
        input.type = 'password';
        icon.className = 'fas fa-eye';
    }
}

// ===== VALIDATION =====
function setupValidation() {
    const integrationName = document.getElementById('integration_name');
    if (integrationName) {
        integrationName.addEventListener('input', function() {
            if (this.value.length < 3) {
                setFieldError(this, 'Le nom doit contenir au moins 3 caractères');
            } else {
                setFieldSuccess(this, 'Nom valide');
            }
        });
    }
    
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
            } else {
                setFieldSuccess(this, 'Numéro de compte valide');
            }
        });
    }
    
    if (password) {
        password.addEventListener('input', function() {
            if (this.value.trim() && this.value.length < 50) {
                setFieldError(this, 'Token JWT trop court');
            } else if (this.value.trim() && !isValidJwtFormat(this.value)) {
                setFieldError(this, 'Format JWT invalide');
            } else if (this.value.trim()) {
                setFieldSuccess(this, 'Format JWT valide');
            } else {
                clearFieldError(this);
            }
        });
    }
}

function setupMesColisValidation() {
    // 🔥 CORRECTION : Valider le champ password pour Mes Colis
    const password = document.getElementById('password');
    
    if (password) {
        password.addEventListener('input', function() {
            if (!this.value.trim()) {
                setFieldError(this, 'Token d\'accès requis');
            } else if (this.value.length < 10) {
                setFieldError(this, 'Token d\'accès trop court');
            } else {
                setFieldSuccess(this, 'Token d\'accès valide');
            }
        });
    }
}

function isValidJwtFormat(token) {
    const parts = token.split('.');
    return parts.length === 3 && parts.every(part => part.length > 10);
}

function setFieldError(field, message) {
    field.classList.remove('is-valid', 'success');
    field.classList.add('is-invalid', 'error');
    
    removeFieldMessages(field);
    
    const errorDiv = document.createElement('div');
    errorDiv.className = 'invalid-feedback form-error';
    errorDiv.innerHTML = `<i class="fas fa-exclamation-triangle"></i> <span>${message}</span>`;
    field.parentNode.appendChild(errorDiv);
}

function setFieldSuccess(field, message) {
    field.classList.remove('is-invalid', 'error');
    field.classList.add('is-valid', 'success');
    
    removeFieldMessages(field);
    
    const successDiv = document.createElement('div');
    successDiv.className = 'valid-feedback form-success';
    successDiv.innerHTML = `<i class="fas fa-check-circle"></i> <span>${message}</span>`;
    field.parentNode.appendChild(successDiv);
}

function clearFieldError(field) {
    field.classList.remove('is-invalid', 'is-valid', 'error', 'success');
    removeFieldMessages(field);
}

function removeFieldMessages(field) {
    const parent = field.parentNode;
    const existingError = parent.querySelector('.invalid-feedback, .form-error');
    const existingSuccess = parent.querySelector('.valid-feedback, .form-success');
    
    if (existingError) existingError.remove();
    if (existingSuccess) existingSuccess.remove();
}

// ===== GESTION FORMULAIRE =====
function setupFormHandler() {
    const form = document.getElementById('configForm');
    form.addEventListener('submit', handleFormSubmit);
}

async function handleFormSubmit(e) {
    e.preventDefault();
    
    if (isSubmitting) return;
    
    const btn = document.getElementById('saveBtn');
    const originalText = btn.innerHTML;
    
    isSubmitting = true;
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sauvegarde...';
    
    try {
        const formData = new FormData(e.target);
        
        // Debug des données
        console.log('🔍 FormData debug:');
        for (let [key, value] of formData.entries()) {
            console.log(`${key}: ${value}`);
        }
        
        const response = await fetch(CONFIG.updateUrl, {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': CONFIG.csrfToken,
                'Accept': 'application/json'
            }
        });
        
        const data = await response.json();
        
        console.log('🔥 Response:', response.status, data);
        
        if (response.ok && data.success) {
            showToast('success', data.message || 'Configuration mise à jour !');
            
            // Redirection si fournie
            if (data.redirect) {
                setTimeout(() => window.location.href = data.redirect, 2000);
            } else {
                setTimeout(() => window.location.reload(), 2000);
            }
        } else {
            handleFormErrors(data);
        }
    } catch (error) {
        console.error('❌ Form submit error:', error);
        showToast('danger', 'Erreur de sauvegarde: ' + error.message);
    } finally {
        isSubmitting = false;
        btn.disabled = false;
        btn.innerHTML = originalText;
    }
}

function handleFormErrors(data) {
    console.log('❌ Form errors:', data);
    
    if (data.errors) {
        Object.keys(data.errors).forEach(fieldName => {
            const field = document.querySelector(`[name="${fieldName}"]`);
            if (field) {
                setFieldError(field, data.errors[fieldName][0]);
            }
        });
        showToast('danger', 'Veuillez corriger les erreurs dans le formulaire');
    } else {
        showToast('danger', data.message || data.error || 'Erreur de sauvegarde');
    }
}

// ===== TEST CONNEXION =====
async function testConnection() {
    if (isTestingConnection) return;
    
    isTestingConnection = true;
    updateTestButton(true);
    showTestModal();
    
    try {
        const response = await fetch(CONFIG.testUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': CONFIG.csrfToken,
                'Accept': 'application/json'
            }
        });
        
        const data = await response.json();
        
        console.log('🧪 Test result:', response.status, data);
        
        if (response.ok && data.success) {
            showTestResult('success', 'Connexion réussie !', data.message || 'Configuration valide');
        } else {
            showTestResult('error', 'Connexion échouée', data.message || 'Vérifiez vos paramètres');
        }
        
    } catch (error) {
        console.error('❌ Test connection error:', error);
        showTestResult('error', 'Erreur de test', 'Impossible de tester la connexion');
    } finally {
        isTestingConnection = false;
        updateTestButton(false);
    }
}

function showTestModal() {
    const modal = document.getElementById('testModal');
    modal.classList.add('show');
    
    const content = document.getElementById('testContent');
    content.innerHTML = `
        <div style="text-align: center; padding: 1rem;">
            <i class="fas fa-spinner fa-spin" style="font-size: 2rem; color: var(--primary);"></i>
            <h4 style="margin: 1rem 0 0.5rem 0;">Test de connexion en cours...</h4>
            <p style="color: #6b7280; margin: 0;">Vérification avec ${CONFIG.carrierSlug === 'jax_delivery' ? 'JAX Delivery' : 'Mes Colis Express'}</p>
        </div>
    `;
}

function showTestResult(type, title, message) {
    const content = document.getElementById('testContent');
    const icon = type === 'success' ? 'check-circle' : 'exclamation-triangle';
    const color = type === 'success' ? 'var(--success)' : 'var(--danger)';
    
    content.innerHTML = `
        <div style="text-align: center; padding: 1rem;">
            <i class="fas fa-${icon}" style="font-size: 2rem; color: ${color};"></i>
            <h4 style="margin: 1rem 0 0.5rem 0; color: ${color};">${title}</h4>
            <p style="color: #6b7280; margin: 0;">${message}</p>
        </div>
    `;
}

function closeTestModal() {
    const modal = document.getElementById('testModal');
    modal.classList.remove('show');
}

function updateTestButton(loading) {
    const btn = document.getElementById('testBtn');
    if (!btn) return;
    
    if (loading) {
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Test...';
    } else {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-flask"></i> Tester Connexion';
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
        const response = await fetch(CONFIG.toggleUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': CONFIG.csrfToken,
                'Accept': 'application/json'
            }
        });
        
        const data = await response.json();
        
        if (response.ok && data.success) {
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
async function confirmDelete() {
    const btn = document.getElementById('deleteBtn');
    const originalText = btn.innerHTML;
    
    if (!confirm('⚠️ ATTENTION !\n\nVoulez-vous vraiment supprimer cette configuration ?\n\n• Tous les enlèvements associés seront perdus\n• Cette action est irréversible\n\nTapez "SUPPRIMER" pour confirmer:')) {
        return;
    }
    
    const confirmation = prompt('Tapez "SUPPRIMER" pour confirmer la suppression:');
    if (confirmation !== 'SUPPRIMER') {
        showToast('warning', 'Suppression annulée');
        return;
    }
    
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Suppression...';
    
    try {
        const response = await fetch(CONFIG.deleteUrl, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': CONFIG.csrfToken,
                'Accept': 'application/json'
            }
        });
        
        const data = await response.json();
        
        if (response.ok && data.success) {
            showToast('success', 'Configuration supprimée !');
            setTimeout(() => {
                window.location.href = CONFIG.redirectUrl;
            }, 2000);
        } else {
            throw new Error(data.error || data.message || 'Impossible de supprimer');
        }
    } catch (error) {
        showToast('danger', error.message);
        btn.disabled = false;
        btn.innerHTML = originalText;
    }
}

// ===== DIAGNOSTIC =====
async function diagnosticConfig() {
    try {
        showToast('info', 'Diagnostic en cours...');
        
        const response = await fetch(CONFIG.diagnosticUrl, {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': CONFIG.csrfToken
            }
        });
        
        const data = await response.json();
        
        if (response.ok && data.success) {
            console.log('Diagnostic Results:', data);
            showToast('success', 'Diagnostic terminé - voir la console pour les détails');
        } else {
            showToast('danger', 'Erreur diagnostic: ' + (data.error || 'Erreur inconnue'));
        }
    } catch (error) {
        showToast('danger', 'Erreur diagnostic: ' + error.message);
    }
}

// ===== NOTIFICATIONS TOAST =====
function showToast(type, message) {
    // Supprimer les toasts existants
    const existingToasts = document.querySelectorAll('.toast');
    existingToasts.forEach(toast => toast.remove());
    
    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    
    const iconMap = {
        'success': 'check-circle',
        'danger': 'exclamation-circle',
        'warning': 'exclamation-triangle',
        'info': 'info-circle'
    };
    
    toast.innerHTML = `
        <div class="toast-content">
            <i class="fas fa-${iconMap[type]}"></i>
            <span>${message}</span>
        </div>
    `;
    
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.style.animation = 'slideOutRight 0.4s cubic-bezier(0.4, 0, 0.2, 1)';
        setTimeout(() => toast.remove(), 400);
    }, 5000);
}

// ===== FERMETURE MODAL AU CLIC EXTÉRIEUR =====
document.addEventListener('click', function(event) {
    const modal = document.getElementById('testModal');
    if (event.target === modal) {
        closeTestModal();
    }
});

console.log('✅ Config Edit Scripts Loaded');
</script>
@endsection