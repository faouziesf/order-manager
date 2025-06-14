@extends('layouts.admin')

@section('title', 'Configuration Livraison')

@push('head')
    <meta name="csrf-token" content="{{ csrf_token() }}">
@endpush

@section('css')
    <style>
        :root {
            --primary: #6366f1;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
            --info: #3b82f6;
            --gray-100: #f3f4f6;
            --gray-200: #e5e7eb;
            --gray-600: #4b5563;
            --gray-800: #1f2937;
            --white: #ffffff;
            --shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            --border-radius: 12px;
        }

        .page-header {
            background: linear-gradient(135deg, var(--primary) 0%, #4f46e5 100%);
            color: white;
            padding: 2rem;
            border-radius: var(--border-radius);
            margin-bottom: 2rem;
            box-shadow: var(--shadow-lg);
            position: relative;
            overflow: hidden;
        }

        .page-header::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -20%;
            width: 100%;
            height: 200%;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.1) 0%, transparent 70%);
            transform: rotate(15deg);
        }

        .header-content {
            position: relative;
            z-index: 2;
        }

        .config-section {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            overflow: hidden;
            margin-bottom: 2rem;
        }

        .section-header {
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid var(--gray-200);
        }

        .section-title {
            font-size: 1.125rem;
            font-weight: 600;
            color: var(--gray-800);
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin: 0;
        }

        .connection-status {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .connection-status.connected {
            background: rgba(16, 185, 129, 0.1);
            border: 1px solid rgba(16, 185, 129, 0.3);
            color: var(--success);
        }

        .connection-status.disconnected {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.3);
            color: var(--danger);
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: var(--gray-800);
        }

        .form-control {
            width: 100%;
            padding: 0.75rem;
            border: 2px solid var(--gray-200);
            border-radius: 8px;
            transition: all 0.2s ease;
            font-size: 0.875rem;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 0.2rem rgba(99, 102, 241, 0.25);
        }

        .btn {
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            border: none;
            font-weight: 500;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.875rem;
            transition: all 0.2s ease;
            margin-right: 0.5rem;
        }

        .btn:hover {
            transform: translateY(-1px);
            box-shadow: var(--shadow);
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary) 0%, #4f46e5 100%);
            color: white;
        }

        .btn-success {
            background: linear-gradient(135deg, var(--success) 0%, #059669 100%);
            color: white;
        }

        .btn-danger {
            background: linear-gradient(135deg, var(--danger) 0%, #dc2626 100%);
            color: white;
        }

        .btn-secondary {
            background: linear-gradient(135deg, var(--gray-600) 0%, #4b5563 100%);
            color: white;
        }

        .btn-info {
            background: linear-gradient(135deg, var(--info) 0%, #0369a1 100%);
            color: white;
        }

        .btn-warning {
            background: linear-gradient(135deg, var(--warning) 0%, #d97706 100%);
            color: white;
        }

        .btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }

        .alert {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .alert-success {
            background: rgba(16, 185, 129, 0.1);
            border: 1px solid rgba(16, 185, 129, 0.3);
            color: var(--success);
        }

        .alert-danger {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.3);
            color: var(--danger);
        }

        .alert-info {
            background: rgba(59, 130, 246, 0.1);
            border: 1px solid rgba(59, 130, 246, 0.3);
            color: var(--info);
        }

        .config-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
            padding: 1.5rem;
        }

        .config-item {
            padding: 1.5rem;
            border: 2px solid var(--gray-200);
            border-radius: var(--border-radius);
            transition: all 0.2s ease;
        }

        .config-item:hover {
            border-color: var(--primary);
            box-shadow: var(--shadow);
        }

        .config-item h5 {
            margin: 0 0 1rem 0;
            color: var(--gray-800);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .config-item p {
            color: var(--gray-600);
            font-size: 0.875rem;
            margin-bottom: 1rem;
        }

        .spinner {
            display: inline-block;
            width: 1rem;
            height: 1rem;
            border: 2px solid transparent;
            border-top: 2px solid currentColor;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }

        .token-info {
            background: var(--gray-100);
            padding: 1rem;
            border-radius: 8px;
            font-family: monospace;
            font-size: 0.75rem;
            word-break: break-all;
            margin-top: 1rem;
        }

        @media (max-width: 768px) {
            .config-grid {
                grid-template-columns: 1fr;
            }

            .page-header {
                padding: 1.5rem;
            }
        }

        .card {
            border: 2px solid var(--gray-200);
            border-radius: var(--border-radius);
            margin-bottom: 1.5rem;
            overflow: hidden;
            transition: all 0.2s ease;
        }

        .card:hover {
            border-color: var(--primary);
            box-shadow: var(--shadow);
        }

        .card-header {
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            padding: 1rem 1.5rem;
            border-bottom: 1px solid var(--gray-200);
        }

        .card-body {
            padding: 1.5rem;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 0.2rem rgba(99, 102, 241, 0.25);
        }

        .text-muted {
            color: var(--gray-600) !important;
            font-size: 0.75rem;
    </style>
@endsection

@section('content')
    <div class="container-fluid" style="max-width: 1400px; margin: 0 auto; padding: 1rem;">

        <!-- En-tête -->
        <div class="page-header">
            <div class="header-content">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <h1 style="margin: 0 0 0.5rem 0; font-size: 2rem; font-weight: 700;">
                            <i class="fas fa-truck me-3"></i>
                            Configuration des Livraisons
                        </h1>
                        <p style="margin: 0; opacity: 0.8; font-size: 1.125rem;">
                            Connectez-vous à votre service de livraison FParcel
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Section de connexion FParcel -->
        <div class="config-section">
            <div class="section-header">
                <h3 class="section-title">
                    <i class="fas fa-plug"></i>
                    Connexion au service FParcel
                </h3>
            </div>

            <div style="padding: 1.5rem;">
                <!-- Statut de connexion -->
                <div id="connection-status" class="connection-status disconnected">
                    <i class="fas fa-times-circle"></i>
                    <span>Non connecté au service FParcel</span>
                </div>

                <!-- Alertes -->
                <div id="alerts-container"></div>

                <!-- Formulaire de connexion -->
                <div id="connection-form">
                    <form id="fparcel-connect-form">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label" for="fparcel_username">
                                        <i class="fas fa-user me-2"></i>Nom d'utilisateur FParcel
                                    </label>
                                    <input type="text" class="form-control" id="fparcel_username" name="username"
                                        required autocomplete="username" placeholder="Votre nom d'utilisateur FParcel">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label" for="fparcel_password">
                                        <i class="fas fa-lock me-2"></i>Mot de passe FParcel
                                    </label>
                                    <input type="password" class="form-control" id="fparcel_password" name="password"
                                        required autocomplete="current-password" placeholder="Votre mot de passe FParcel">
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">
                                <i class="fas fa-server me-2"></i>Environnement
                            </label>
                            <div>
                                <label style="margin-right: 1rem; font-weight: normal;">
                                    <input type="radio" name="environment" value="test" checked
                                        style="margin-right: 0.5rem;">
                                    Test (http://fparcel.net:59)
                                </label>
                                <label style="font-weight: normal;">
                                    <input type="radio" name="environment" value="prod" style="margin-right: 0.5rem;">
                                    Production (https://admin.fparcel.net)
                                </label>
                            </div>
                            <small class="text-muted">Utilisez l'environnement de test pour les essais, production pour
                                l'utilisation réelle.</small>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary" id="connect-btn">
                                <i class="fas fa-plug"></i>
                                Se connecter
                            </button>
                            <button type="button" class="btn btn-secondary" onclick="testConnection()">
                                <i class="fas fa-vial"></i>
                                Tester la connexion
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Informations de connexion (visible après connexion) -->
                <div id="connection-info" style="display: none;">
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i>
                        Connecté avec succès au service FParcel
                    </div>

                    <div class="d-flex gap-2 mb-3">
                        <button type="button" class="btn btn-danger" onclick="disconnect()">
                            <i class="fas fa-times"></i>
                            Se déconnecter
                        </button>
                        <button type="button" class="btn btn-info" onclick="refreshToken()">
                            <i class="fas fa-refresh"></i>
                            Actualiser le token
                        </button>
                    </div>

                    <div class="token-info">
                        <strong>Token actuel:</strong>
                        <div id="current-token">Chargement...</div>
                        <div style="margin-top: 0.5rem; font-size: 0.7rem; color: var(--gray-600);">
                            <strong>Dernière mise à jour:</strong> <span id="token-updated">-</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Configuration des paramètres (visible après connexion) -->
        <div class="config-section" id="delivery-settings" style="display: none;">
            <div class="section-header">
                <h3 class="section-title">
                    <i class="fas fa-cogs"></i>
                    Paramètres de livraison
                </h3>
            </div>

            <div class="config-grid">
                <div class="config-item">
                    <h5>
                        <i class="fas fa-dollar-sign text-success"></i>
                        Modes de règlement
                    </h5>
                    <p>Synchronisez et configurez les modes de règlement disponibles</p>
                    <button class="btn btn-success" onclick="syncPaymentMethods()">
                        <i class="fas fa-sync"></i>
                        Synchroniser
                    </button>
                </div>

                <div class="config-item">
                    <h5>
                        <i class="fas fa-building text-info"></i>
                        Points de dépôt
                    </h5>
                    <p>Gérez la liste des agences et points de dépôt</p>
                    <button class="btn btn-info" onclick="syncDropPoints()">
                        <i class="fas fa-download"></i>
                        Synchroniser
                    </button>
                </div>

                <div class="config-item">
                    <h5>
                        <i class="fas fa-exclamation-triangle text-warning"></i>
                        Motifs d'anomalies
                    </h5>
                    <p>Synchronisez la liste des motifs d'anomalies</p>
                    <button class="btn btn-warning" onclick="syncAnomalyReasons()">
                        <i class="fas fa-list"></i>
                        Synchroniser
                    </button>
                </div>

                <div class="config-item">
                    <h5>
                        <i class="fas fa-tag text-primary"></i>
                        Étiquettes
                    </h5>
                    <p>Configurez les paramètres d'impression des étiquettes</p>
                    <button class="btn btn-primary" onclick="configureLabels()">
                        <i class="fas fa-print"></i>
                        Configurer
                    </button>
                </div>

                <div class="config-item">
                    <h5>
                        <i class="fas fa-map-marker-alt text-danger"></i>
                        Zones de livraison
                    </h5>
                    <p>Configurez vos zones de livraison et les tarifs associés</p>
                    <button class="btn btn-danger" onclick="configureZones()">
                        <i class="fas fa-cog"></i>
                        Configurer
                    </button>
                </div>

                <div class="config-item">
                    <h5>
                        <i class="fas fa-clock text-secondary"></i>
                        Paramètres généraux
                    </h5>
                    <p>Configurez les horaires de livraison et autres paramètres</p>
                    <button class="btn btn-secondary" onclick="configureGeneral()">
                        <i class="fas fa-sliders-h"></i>
                        Paramètres
                    </button>
                </div>
            </div>
        </div>
        <div class="config-section" id="api-parameters" style="display: none;">
            <div class="section-header">
                <h3 class="section-title">
                    <i class="fas fa-code"></i>
                    Configuration des paramètres d'envoi
                </h3>
            </div>

            <div style="padding: 1.5rem;">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i>
                    <span>Configurez les valeurs par défaut qui seront utilisées lors de la création des positions de
                        livraison via l'API FParcel.</span>
                </div>

                <form id="api-parameters-form">
                    <!-- Informations Expéditeur -->
                    <div class="card mb-4"
                        style="border: 2px solid var(--gray-200); border-radius: var(--border-radius);">
                        <div class="card-header"
                            style="background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%); padding: 1rem 1.5rem; border-bottom: 1px solid var(--gray-200);">
                            <h5 class="mb-0"
                                style="color: var(--gray-800); display: flex; align-items: center; gap: 0.5rem;">
                                <i class="fas fa-user-tie text-primary"></i>
                                Informations Expéditeur (par défaut)
                            </h5>
                        </div>
                        <div class="card-body" style="padding: 1.5rem;">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label" for="enl_contact_nom">
                                            <i class="fas fa-user me-2"></i>Nom de l'expéditeur
                                        </label>
                                        <input type="text" class="form-control" id="enl_contact_nom"
                                            name="enl_contact_nom" placeholder="Nom de l'expéditeur">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label" for="enl_contact_prenom">
                                            <i class="fas fa-user me-2"></i>Prénom de l'expéditeur
                                        </label>
                                        <input type="text" class="form-control" id="enl_contact_prenom"
                                            name="enl_contact_prenom" placeholder="Prénom de l'expéditeur">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label class="form-label" for="enl_adresse">
                                            <i class="fas fa-map-marker-alt me-2"></i>Adresse d'enlèvement
                                        </label>
                                        <textarea class="form-control" id="enl_adresse" name="enl_adresse" rows="2"
                                            placeholder="Adresse complète d'enlèvement"></textarea>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="form-label" for="enl_code_postal">
                                            <i class="fas fa-mail-bulk me-2"></i>Code postal
                                        </label>
                                        <input type="text" class="form-control" id="enl_code_postal"
                                            name="enl_code_postal" placeholder="Code postal">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="form-label" for="enl_portable">
                                            <i class="fas fa-mobile-alt me-2"></i>Téléphone portable
                                        </label>
                                        <input type="tel" class="form-control" id="enl_portable" name="enl_portable"
                                            placeholder="Numéro de téléphone">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="form-label" for="enl_mail">
                                            <i class="fas fa-envelope me-2"></i>Email
                                        </label>
                                        <input type="email" class="form-control" id="enl_mail" name="enl_mail"
                                            placeholder="Adresse email">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Paramètres de livraison -->
                    <div class="card mb-4"
                        style="border: 2px solid var(--gray-200); border-radius: var(--border-radius);">
                        <div class="card-header"
                            style="background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%); padding: 1rem 1.5rem; border-bottom: 1px solid var(--gray-200);">
                            <h5 class="mb-0"
                                style="color: var(--gray-800); display: flex; align-items: center; gap: 0.5rem;">
                                <i class="fas fa-truck text-success"></i>
                                Paramètres de livraison par défaut
                            </h5>
                        </div>
                        <div class="card-body" style="padding: 1.5rem;">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label" for="default_mr_code">
                                            <i class="fas fa-credit-card me-2"></i>Mode de règlement par défaut
                                        </label>
                                        <select class="form-control" id="default_mr_code" name="default_mr_code">
                                            <option value="">Sélectionner un mode de règlement</option>
                                        </select>
                                        <small class="text-muted">Les modes de règlement sont synchronisés depuis
                                            FParcel</small>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label" for="default_pos_allow_open">
                                            <i class="fas fa-box-open me-2"></i>Autoriser l'ouverture du colis
                                        </label>
                                        <select class="form-control" id="default_pos_allow_open"
                                            name="default_pos_allow_open">
                                            <option value="0">Non - Ouverture interdite</option>
                                            <option value="1">Oui - Ouverture autorisée</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label" for="default_pos_valid">
                                            <i class="fas fa-check-circle me-2"></i>Validation automatique
                                        </label>
                                        <select class="form-control" id="default_pos_valid" name="default_pos_valid">
                                            <option value="0">Non - Position temporaire</option>
                                            <option value="1" selected>Oui - Position validée</option>
                                        </select>
                                        <small class="text-muted">Si "Non", la position devra être validée
                                            manuellement</small>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label" for="default_nb_piece">
                                            <i class="fas fa-boxes me-2"></i>Nombre de pièces par défaut
                                        </label>
                                        <input type="number" class="form-control" id="default_nb_piece"
                                            name="default_nb_piece" value="1" min="1">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Horaires de livraison -->
                    <div class="card mb-4"
                        style="border: 2px solid var(--gray-200); border-radius: var(--border-radius);">
                        <div class="card-header"
                            style="background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%); padding: 1rem 1.5rem; border-bottom: 1px solid var(--gray-200);">
                            <h5 class="mb-0"
                                style="color: var(--gray-800); display: flex; align-items: center; gap: 0.5rem;">
                                <i class="fas fa-clock text-warning"></i>
                                Horaires de disponibilité par défaut
                            </h5>
                        </div>
                        <div class="card-body" style="padding: 1.5rem;">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label" for="default_time_from">
                                            <i class="fas fa-clock me-2"></i>Disponible à partir de
                                        </label>
                                        <input type="time" class="form-control" id="default_time_from"
                                            name="default_time_from" value="08:00">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label" for="default_time_to">
                                            <i class="fas fa-clock me-2"></i>Disponible jusqu'à
                                        </label>
                                        <input type="time" class="form-control" id="default_time_to"
                                            name="default_time_to" value="18:00">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Paramètres avancés -->
                    <div class="card mb-4"
                        style="border: 2px solid var(--gray-200); border-radius: var(--border-radius);">
                        <div class="card-header"
                            style="background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%); padding: 1rem 1.5rem; border-bottom: 1px solid var(--gray-200);">
                            <h5 class="mb-0"
                                style="color: var(--gray-800); display: flex; align-items: center; gap: 0.5rem;">
                                <i class="fas fa-cogs text-info"></i>
                                Paramètres avancés
                            </h5>
                        </div>
                        <div class="card-body" style="padding: 1.5rem;">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label" for="default_poids">
                                            <i class="fas fa-weight me-2"></i>Poids par défaut (kg)
                                        </label>
                                        <input type="number" step="0.1" class="form-control" id="default_poids"
                                            name="default_poids" placeholder="Poids en kg">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label" for="default_valeur">
                                            <i class="fas fa-euro-sign me-2"></i>Valeur déclarée par défaut (€)
                                        </label>
                                        <input type="number" step="0.01" class="form-control" id="default_valeur"
                                            name="default_valeur" placeholder="Valeur en euros">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label class="form-label" for="default_pos_link_img">
                                            <i class="fas fa-image me-2"></i>URL de l'image par défaut
                                        </label>
                                        <input type="url" class="form-control" id="default_pos_link_img"
                                            name="default_pos_link_img" placeholder="https://exemple.com/image.jpg">
                                        <small class="text-muted">URL de l'image représentant le contenu du colis</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Boutons d'action -->
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i>
                            Sauvegarder la configuration
                        </button>
                        <button type="button" class="btn btn-secondary" onclick="loadDefaultParameters()">
                            <i class="fas fa-sync"></i>
                            Recharger
                        </button>
                        <button type="button" class="btn btn-info" onclick="testApiParameters()">
                            <i class="fas fa-vial"></i>
                            Tester la configuration
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            console.log('Configuration des livraisons chargée');

            // Vérifier le statut de connexion au chargement
            checkConnectionStatus();
        });

        // Vérifier le statut de connexion
        function checkConnectionStatus() {
            $.get('{{ route('admin.delivery.status') }}')
                .done(function(response) {
                    if (response.connected) {
                        showConnectedState(response);
                    } else {
                        showDisconnectedState();
                    }
                })
                .fail(function() {
                    showDisconnectedState();
                });
        }

        // Afficher l'état connecté
        function showConnectedState(data) {
            $('#connection-status')
                .removeClass('disconnected')
                .addClass('connected')
                .html('<i class="fas fa-check-circle"></i><span>Connecté au service FParcel</span>');

            $('#connection-form').hide();
            $('#connection-info').show();
            $('#delivery-settings').show();

            if (data.token) {
                $('#current-token').text(data.token);
            }
            if (data.updated_at) {
                $('#token-updated').text(new Date(data.updated_at).toLocaleString('fr-FR'));
            }
        }

        // Afficher l'état déconnecté
        function showDisconnectedState() {
            $('#connection-status')
                .removeClass('connected')
                .addClass('disconnected')
                .html('<i class="fas fa-times-circle"></i><span>Non connecté au service FParcel</span>');

            $('#connection-form').show();
            $('#connection-info').hide();
            $('#delivery-settings').hide();
        }

        // Gérer la soumission du formulaire de connexion
        $('#fparcel-connect-form').on('submit', function(e) {
            e.preventDefault();

            const $btn = $('#connect-btn');
            const originalText = $btn.html();

            // Désactiver le bouton et afficher le spinner
            $btn.prop('disabled', true).html('<span class="spinner"></span> Connexion...');

            // Effacer les alertes précédentes
            $('#alerts-container').empty();

            $.post('{{ route('admin.delivery.connect') }}', {
                    username: $('#fparcel_username').val(),
                    password: $('#fparcel_password').val(),
                    environment: $('input[name="environment"]:checked').val(),
                    _token: $('meta[name="csrf-token"]').attr('content')
                })
                .done(function(response) {
                    if (response.success) {
                        showAlert('success', 'Connexion réussie ! Token récupéré et sauvegardé.');
                        showConnectedState(response.data);
                    } else {
                        showAlert('danger', response.message || 'Erreur lors de la connexion');
                    }
                })
                .fail(function(xhr) {
                    let message = 'Erreur lors de la connexion au service';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        message = xhr.responseJSON.message;
                    }
                    showAlert('danger', message);
                })
                .always(function() {
                    $btn.prop('disabled', false).html(originalText);
                });
        });

        // Tester la connexion
        function testConnection() {
            const username = $('#fparcel_username').val();
            const password = $('#fparcel_password').val();
            const environment = $('input[name="environment"]:checked').val();

            if (!username || !password) {
                showAlert('danger', 'Veuillez saisir votre nom d\'utilisateur et mot de passe');
                return;
            }

            $('#alerts-container').empty();
            showAlert('info', 'Test de connexion en cours...');

            $.post('{{ route('admin.delivery.test') }}', {
                    username: username,
                    password: password,
                    environment: environment,
                    _token: $('meta[name="csrf-token"]').attr('content')
                })
                .done(function(response) {
                    if (response.success) {
                        showAlert('success', 'Test de connexion réussi ! Les identifiants sont valides.');
                    } else {
                        showAlert('danger', response.message || 'Test de connexion échoué');
                    }
                })
                .fail(function() {
                    showAlert('danger', 'Erreur lors du test de connexion');
                });
        }

        // Se déconnecter
        function disconnect() {
            if (!confirm('Êtes-vous sûr de vouloir vous déconnecter ?')) {
                return;
            }

            $.post('{{ route('admin.delivery.disconnect') }}', {
                    _token: $('meta[name="csrf-token"]').attr('content')
                })
                .done(function(response) {
                    if (response.success) {
                        showAlert('success', 'Déconnexion réussie');
                        showDisconnectedState();
                    } else {
                        showAlert('danger', response.message || 'Erreur lors de la déconnexion');
                    }
                })
                .fail(function() {
                    showAlert('danger', 'Erreur lors de la déconnexion');
                });
        }

        // Actualiser le token
        function refreshToken() {
            $.post('{{ route('admin.delivery.refresh-token') }}', {
                    _token: $('meta[name="csrf-token"]').attr('content')
                })
                .done(function(response) {
                    if (response.success) {
                        showAlert('success', 'Token actualisé avec succès');
                        $('#current-token').text(response.data.token);
                        $('#token-updated').text(new Date().toLocaleString('fr-FR'));
                    } else {
                        showAlert('danger', response.message || 'Erreur lors de l\'actualisation du token');
                    }
                })
                .fail(function() {
                    showAlert('danger', 'Erreur lors de l\'actualisation du token');
                });
        }

        // Fonctions de configuration
        function syncPaymentMethods() {
            showAlert('info', 'Synchronisation des modes de règlement...');

            $.post('{{ route('admin.delivery.sync-payment-methods') }}', {
                    _token: $('meta[name="csrf-token"]').attr('content')
                })
                .done(function(response) {
                    if (response.success) {
                        showAlert('success', `${response.count} modes de règlement synchronisés`);
                    } else {
                        showAlert('danger', response.message || 'Erreur lors de la synchronisation');
                    }
                })
                .fail(function() {
                    showAlert('danger', 'Erreur lors de la synchronisation des modes de règlement');
                });
        }

        function syncDropPoints() {
            showAlert('info', 'Synchronisation des points de dépôt...');

            $.post('{{ route('admin.delivery.sync-drop-points') }}', {
                    _token: $('meta[name="csrf-token"]').attr('content')
                })
                .done(function(response) {
                    if (response.success) {
                        showAlert('success', `${response.count} points de dépôt synchronisés`);
                    } else {
                        showAlert('danger', response.message || 'Erreur lors de la synchronisation');
                    }
                })
                .fail(function() {
                    showAlert('danger', 'Erreur lors de la synchronisation des points de dépôt');
                });
        }

        function syncAnomalyReasons() {
            showAlert('info', 'Synchronisation des motifs d\'anomalies...');

            $.post('{{ route('admin.delivery.sync-anomaly-reasons') }}', {
                    _token: $('meta[name="csrf-token"]').attr('content')
                })
                .done(function(response) {
                    if (response.success) {
                        showAlert('success', `${response.count} motifs d'anomalies synchronisés`);
                    } else {
                        showAlert('danger', response.message || 'Erreur lors de la synchronisation');
                    }
                })
                .fail(function() {
                    showAlert('danger', 'Erreur lors de la synchronisation des motifs d\'anomalies');
                });
        }

        function configureZones() {
            showAlert('info', 'Configuration des zones de livraison - Fonctionnalité à venir');
        }

        function configureLabels() {
            showAlert('info', 'Configuration des étiquettes - Fonctionnalité à venir');
        }

        function configureGeneral() {
            showAlert('info', 'Configuration générale - Fonctionnalité à venir');
        }

        // Afficher une alerte
        function showAlert(type, message) {
            const alertClass = `alert-${type}`;
            const iconClass = type === 'success' ? 'fa-check-circle' :
                type === 'danger' ? 'fa-exclamation-circle' :
                type === 'info' ? 'fa-info-circle' : 'fa-exclamation-triangle';

            const alert = $(`
        <div class="alert ${alertClass}">
            <i class="fas ${iconClass}"></i>
            <span>${message}</span>
        </div>
    `);

            $('#alerts-container').empty().append(alert);

            // Auto-hide success alerts
            if (type === 'success') {
                setTimeout(() => {
                    alert.fadeOut();
                }, 5000);
            }
        }

        function loadPaymentMethods() {
            $.get('{{ route('admin.delivery.payment-methods') }}')
                .done(function(response) {
                    if (response.success) {
                        const select = $('#default_mr_code');
                        select.empty().append('<option value="">Sélectionner un mode de règlement</option>');

                        response.data.forEach(function(method) {
                            select.append(`<option value="${method.code}">${method.name}</option>`);
                        });
                    }
                })
                .fail(function() {
                    showAlert('warning', 'Impossible de charger les modes de règlement');
                });
        }

        // Enhanced showConnectedState function
        function showConnectedState(data) {
            $('#connection-status')
                .removeClass('disconnected')
                .addClass('connected')
                .html('<i class="fas fa-check-circle"></i><span>Connecté au service FParcel</span>');

            $('#connection-form').hide();
            $('#connection-info').show();
            $('#delivery-settings').show();
            $('#api-parameters').show(); // Show the new parameters section

            if (data.token) {
                $('#current-token').text(data.token);
            }
            if (data.updated_at) {
                $('#token-updated').text(new Date(data.updated_at).toLocaleString('fr-FR'));
            }

            // Load payment methods and default parameters
            loadPaymentMethods();
            loadDefaultParameters();
        }

        // Enhanced showDisconnectedState function
        function showDisconnectedState() {
            $('#connection-status')
                .removeClass('connected')
                .addClass('disconnected')
                .html('<i class="fas fa-times-circle"></i><span>Non connecté au service FParcel</span>');

            $('#connection-form').show();
            $('#connection-info').hide();
            $('#delivery-settings').hide();
            $('#api-parameters').hide(); // Hide the parameters section
        }

        // Save API parameters
        $('#api-parameters-form').on('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(this);
            formData.append('_token', $('meta[name="csrf-token"]').attr('content'));

            $.post('{{ route('admin.delivery.save-parameters') }}', formData)
                .done(function(response) {
                    if (response.success) {
                        showAlert('success', 'Configuration sauvegardée avec succès');
                    } else {
                        showAlert('danger', response.message || 'Erreur lors de la sauvegarde');
                    }
                })
                .fail(function() {
                    showAlert('danger', 'Erreur lors de la sauvegarde de la configuration');
                });
        });

        // Load default parameters
        function loadDefaultParameters() {
            $.get('{{ route('admin.delivery.get-parameters') }}')
                .done(function(response) {
                    if (response.success && response.data) {
                        const data = response.data;
                        Object.keys(data).forEach(function(key) {
                            const element = document.getElementById(key);
                            if (element) {
                                element.value = data[key] || '';
                            }
                        });
                        showAlert('success', 'Configuration chargée');
                    }
                })
                .fail(function() {
                    showAlert('warning', 'Impossible de charger la configuration');
                });
        }

        // Test API parameters
        function testApiParameters() {
            const formData = new FormData(document.getElementById('api-parameters-form'));
            formData.append('_token', $('meta[name="csrf-token"]').attr('content'));

            showAlert('info', 'Test de la configuration en cours...');

            $.post('{{ route('admin.delivery.test-parameters') }}', formData)
                .done(function(response) {
                    if (response.success) {
                        showAlert('success', 'Configuration valide ! Prêt pour la création de positions.');
                    } else {
                        showAlert('danger', response.message || 'Configuration invalide');
                    }
                })
                .fail(function() {
                    showAlert('danger', 'Erreur lors du test de la configuration');
                });
        }

        // Enhanced syncPaymentMethods function
        function syncPaymentMethods() {
            showAlert('info', 'Synchronisation des modes de règlement...');

            $.post('{{ route('admin.delivery.sync-payment-methods') }}', {
                    _token: $('meta[name="csrf-token"]').attr('content')
                })
                .done(function(response) {
                    if (response.success) {
                        showAlert('success', `${response.count} modes de règlement synchronisés`);
                        loadPaymentMethods(); // Reload the payment methods dropdown
                    } else {
                        showAlert('danger', response.message || 'Erreur lors de la synchronisation');
                    }
                })
                .fail(function() {
                    showAlert('danger', 'Erreur lors de la synchronisation des modes de règlement');
                });
        }
    </script>
@endsection
