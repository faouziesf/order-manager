@extends('layouts.admin')

@section('title', 'Gestion des Livraisons')

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
            background: linear-gradient(135deg, var(--success) 0%, #059669 100%);
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

        .filters-section {
            background: white;
            padding: 1.5rem;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            margin-bottom: 2rem;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            text-align: center;
            transition: transform 0.2s ease;
        }

        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
        }

        .stat-icon {
            width: 48px;
            height: 48px;
            margin: 0 auto 1rem;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: white;
        }

        .stat-icon.pending {
            background: linear-gradient(135deg, var(--warning) 0%, #d97706 100%);
        }

        .stat-icon.processing {
            background: linear-gradient(135deg, var(--primary) 0%, #4f46e5 100%);
        }

        .stat-icon.delivered {
            background: linear-gradient(135deg, var(--success) 0%, #059669 100%);
        }

        .stat-icon.cancelled {
            background: linear-gradient(135deg, var(--danger) 0%, #dc2626 100%);
        }

        .stat-number {
            font-size: 2rem;
            font-weight: 700;
            color: var(--gray-800);
            margin-bottom: 0.25rem;
        }

        .stat-label {
            color: var(--gray-600);
            font-size: 0.875rem;
            font-weight: 500;
        }

        .deliveries-section {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            overflow: hidden;
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

        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            color: var(--gray-600);
        }

        .empty-state i {
            font-size: 4rem;
            color: var(--gray-300);
            margin-bottom: 1.5rem;
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

        .btn-secondary {
            background: linear-gradient(135deg, var(--gray-600) 0%, #4b5563 100%);
            color: white;
        }

        .form-control,
        .form-select {
            border-radius: 8px;
            border: 2px solid var(--gray-200);
            padding: 0.75rem;
            transition: all 0.2s ease;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 0.2rem rgba(99, 102, 241, 0.25);
        }

        .input-group-text {
            background: var(--gray-100);
            border: 2px solid var(--gray-200);
            border-right: none;
            border-radius: 8px 0 0 8px;
        }

        .input-group .form-control {
            border-left: none;
            border-radius: 0 8px 8px 0;
        }

        @media (max-width: 768px) {
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .page-header {
                padding: 1.5rem;
            }

            .header-content .d-flex {
                flex-direction: column;
                gap: 1rem;
            }
        }
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
                            <i class="fas fa-shipping-fast me-3"></i>
                            Gestion des Livraisons
                        </h1>
                        <p style="margin: 0; opacity: 0.8; font-size: 1.125rem;">
                            Suivez et gérez toutes vos livraisons en cours
                        </p>
                    </div>
                    <div>
                        <button type="button" class="btn btn-success">
                            <i class="fas fa-plus"></i>
                            Nouvelle livraison
                        </button>
                        <button type="button" class="btn btn-secondary">
                            <i class="fas fa-download"></i>
                            Exporter
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filtres -->
        <div class="filters-section">
            <h5 style="margin: 0 0 1rem 0; color: var(--gray-800);">
                <i class="fas fa-filter me-2"></i>
                Filtres et recherche
            </h5>
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Statut</label>
                    <select class="form-select">
                        <option value="">Tous les statuts</option>
                        <option value="pending">En attente</option>
                        <option value="processing">En cours</option>
                        <option value="shipped">Expédiée</option>
                        <option value="delivered">Livrée</option>
                        <option value="cancelled">Annulée</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Zone</label>
                    <select class="form-select">
                        <option value="">Toutes les zones</option>
                        <option value="zone1">Zone 1</option>
                        <option value="zone2">Zone 2</option>
                        <option value="zone3">Zone 3</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Date</label>
                    <input type="date" class="form-control">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Recherche</label>
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="fas fa-search"></i>
                        </span>
                        <input type="text" class="form-control" placeholder="Rechercher...">
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistiques -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon pending">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-number">0</div>
                <div class="stat-label">En attente</div>
            </div>

            <div class="stat-card">
                <div class="stat-icon processing">
                    <i class="fas fa-truck"></i>
                </div>
                <div class="stat-number">0</div>
                <div class="stat-label">En cours</div>
            </div>

            <div class="stat-card">
                <div class="stat-icon delivered">
                    <i class="fas fa-check"></i>
                </div>
                <div class="stat-number">0</div>
                <div class="stat-label">Livrées</div>
            </div>

            <div class="stat-card">
                <div class="stat-icon cancelled">
                    <i class="fas fa-times"></i>
                </div>
                <div class="stat-number">0</div>
                <div class="stat-label">Annulées</div>
            </div>
        </div>

        <!-- Section principale -->
        <div class="deliveries-section">
            <div class="section-header">
                <h3 class="section-title">
                    <i class="fas fa-list"></i>
                    Liste des livraisons
                </h3>
            </div>

            <div class="empty-state">
                <i class="fas fa-shipping-fast"></i>
                <h3>Aucune livraison trouvée</h3>
                <p>
                    Commencez par créer votre première livraison ou attendez que des commandes soient passées.
                </p>
                <button type="button" class="btn btn-success">
                    <i class="fas fa-plus"></i>
                    Créer une livraison
                </button>
            </div>
        </div>

    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            console.log('Gestion des livraisons chargée');

            // Ajouter ici la logique JavaScript pour la gestion
            $('.btn-success').click(function() {
                alert('Gestion des livraisons - Fonctionnalité à venir');
            });

            // Gestion des filtres
            $('.form-select, .form-control').change(function() {
                console.log('Filtre modifié:', $(this).val());
                // Implémenter la logique de filtrage
            });
        });
    </script>
@endsection
