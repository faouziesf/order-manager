@extends('layouts.super-admin')

@section('title', 'Détails de l\'Administrateur')

@section('breadcrumb')
    <ol class="breadcrumb breadcrumb-custom">
        <li class="breadcrumb-item"><a href="{{ route('super-admin.dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{{ route('super-admin.admins.index') }}">Administrateurs</a></li>
        <li class="breadcrumb-item active">{{ $admin->name }}</li>
    </ol>
@endsection

@section('page-header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="page-title">Profil Administrateur</h1>
            <p class="page-subtitle">Informations détaillées et statistiques de {{ $admin->name }}</p>
        </div>
        <div class="d-flex gap-2">
            <div class="dropdown">
                <button class="btn btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                    <i class="fas fa-cog me-2"></i>Actions
                </button>
                <ul class="dropdown-menu">
                    <li>
                        <a class="dropdown-item" href="{{ route('super-admin.admins.edit', $admin) }}">
                            <i class="fas fa-edit me-2"></i>Modifier
                        </a>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <button class="dropdown-item" onclick="toggleStatus()">
                            <i class="fas fa-{{ $admin->is_active ? 'times' : 'check' }} me-2"></i>
                            {{ $admin->is_active ? 'Désactiver' : 'Activer' }}
                        </button>
                    </li>
                    <li>
                        <button class="dropdown-item" onclick="extendSubscription()">
                            <i class="fas fa-clock me-2"></i>Prolonger abonnement
                        </button>
                    </li>
                    <li>
                        <button class="dropdown-item" onclick="resetPassword()">
                            <i class="fas fa-key me-2"></i>Réinitialiser mot de passe
                        </button>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <button class="dropdown-item text-danger" onclick="deleteAdmin()">
                            <i class="fas fa-trash me-2"></i>Supprimer
                        </button>
                    </li>
                </ul>
            </div>
            <a href="{{ route('super-admin.admins.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i>Retour à la liste
            </a>
        </div>
    </div>
@endsection

@section('css')
<style>
    .profile-container {
        max-width: 1200px;
        margin: 0 auto;
    }
    
    .profile-header {
        background: linear-gradient(135deg, #4f46e5 0%, #3730a3 100%);
        border-radius: 15px;
        color: white;
        padding: 30px;
        margin-bottom: 30px;
        position: relative;
        overflow: hidden;
    }
    
    .profile-header::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -20%;
        width: 200px;
        height: 200px;
        background: rgba(255, 255, 255, 0.1);
        border-radius: 50%;
    }
    
    .admin-avatar {
        width: 100px;
        height: 100px;
        border-radius: 25px;
        background: rgba(255, 255, 255, 0.2);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 40px;
        font-weight: 600;
        margin-bottom: 20px;
    }
    
    .admin-name {
        font-size: 28px;
        font-weight: 700;
        margin-bottom: 8px;
    }
    
    .admin-subtitle {
        font-size: 16px;
        opacity: 0.9;
        margin-bottom: 20px;
    }
    
    .admin-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 25px;
        font-size: 14px;
    }
    
    .meta-item {
        display: flex;
        align-items: center;
        gap: 8px;
        opacity: 0.9;
    }
    
    .status-badge {
        position: absolute;
        top: 20px;
        right: 20px;
        padding: 8px 15px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .status-active {
        background: rgba(16, 185, 129, 0.2);
        color: #10b981;
        border: 1px solid rgba(16, 185, 129, 0.3);
    }
    
    .status-inactive {
        background: rgba(239, 68, 68, 0.2);
        color: #ef4444;
        border: 1px solid rgba(239, 68, 68, 0.3);
    }
    
    .info-card {
        background: white;
        border-radius: 15px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        margin-bottom: 25px;
        overflow: hidden;
        transition: all 0.3s ease;
    }
    
    .info-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 30px rgba(0, 0, 0, 0.12);
    }
    
    .card-header {
        background: #f8fafc;
        border-bottom: 1px solid #e5e7eb;
        padding: 20px 25px;
    }
    
    .card-title {
        font-size: 18px;
        font-weight: 600;
        color: #111827;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .card-icon {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        background: #4f46e5;
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 16px;
    }
    
    .card-body {
        padding: 25px;
    }
    
    .info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
    }
    
    .info-item {
        display: flex;
        flex-direction: column;
        gap: 5px;
    }
    
    .info-label {
        font-size: 12px;
        font-weight: 600;
        color: #6b7280;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .info-value {
        font-size: 16px;
        font-weight: 600;
        color: #111827;
    }
    
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
        gap: 20px;
    }
    
    .stat-card {
        text-align: center;
        padding: 20px;
        background: linear-gradient(135deg, #f8fafc 0%, #e5e7eb 100%);
        border-radius: 12px;
        transition: all 0.3s ease;
    }
    
    .stat-card:hover {
        transform: translateY(-2px);
    }
    
    .stat-number {
        font-size: 24px;
        font-weight: 700;
        color: #111827;
        margin-bottom: 5px;
    }
    
    .stat-label {
        font-size: 12px;
        color: #6b7280;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .progress-section {
        margin: 25px 0;
    }
    
    .progress-item {
        margin-bottom: 20px;
    }
    
    .progress-header {
        display: flex;
        justify-content: between;
        align-items: center;
        margin-bottom: 8px;
    }
    
    .progress-label {
        font-size: 14px;
        font-weight: 500;
        color: #374151;
    }
    
    .progress-value {
        font-size: 14px;
        font-weight: 600;
        color: #111827;
    }
    
    .progress {
        height: 8px;
        border-radius: 4px;
        background: #e5e7eb;
        overflow: hidden;
    }
    
    .progress-bar {
        border-radius: 4px;
        transition: width 0.6s ease;
    }
    
    .timeline {
        position: relative;
        padding-left: 30px;
    }
    
    .timeline::before {
        content: '';
        position: absolute;
        left: 15px;
        top: 0;
        bottom: 0;
        width: 2px;
        background: #e5e7eb;
    }
    
    .timeline-item {
        position: relative;
        margin-bottom: 25px;
        padding-left: 25px;
    }
    
    .timeline-item::before {
        content: '';
        position: absolute;
        left: -8px;
        top: 5px;
        width: 12px;
        height: 12px;
        border-radius: 50%;
        background: #4f46e5;
        border: 3px solid white;
        box-shadow: 0 0 0 2px #e5e7eb;
    }
    
    .timeline-content {
        background: #f9fafb;
        border-radius: 8px;
        padding: 15px;
    }
    
    .timeline-date {
        font-size: 12px;
        color: #6b7280;
        margin-bottom: 5px;
    }
    
    .timeline-title {
        font-size: 14px;
        font-weight: 600;
        color: #111827;
        margin-bottom: 5px;
    }
    
    .timeline-desc {
        font-size: 13px;
        color: #6b7280;
    }
    
    .subscription-info {
        background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
        border: 1px solid #f59e0b;
        border-radius: 12px;
        padding: 20px;
        margin: 20px 0;
    }
    
    .subscription-title {
        font-size: 16px;
        font-weight: 600;
        color: #92400e;
        margin-bottom: 10px;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    
    .expiry-warning {
        background: linear-gradient(135deg, #fef2f2 0%, #fecaca 100%);
        border: 1px solid #ef4444;
    }
    
    .expiry-warning .subscription-title {
        color: #dc2626;
    }
    
    .chart-container {
        height: 300px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #6b7280;
        background: #f9fafb;
        border-radius: 8px;
    }
    
    .quick-actions {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 15px;
        margin: 20px 0;
    }
    
    .action-btn {
        padding: 15px;
        border: 2px solid #e5e7eb;
        border-radius: 10px;
        background: white;
        text-decoration: none;
        color: #374151;
        text-align: center;
        transition: all 0.3s ease;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 8px;
    }
    
    .action-btn:hover {
        border-color: #4f46e5;
        color: #4f46e5;
        transform: translateY(-2px);
        text-decoration: none;
    }
    
    .action-icon {
        font-size: 20px;
    }
    
    .action-text {
        font-size: 12px;
        font-weight: 500;
    }
</style>
@endsection

@section('content')
    <div class="profile-container">
        <!-- En-tête du profil -->
        <div class="profile-header">
            <div class="status-badge {{ $admin->is_active ? 'status-active' : 'status-inactive' }}">
                {{ $admin->is_active ? 'Actif' : 'Inactif' }}
            </div>
            
            <div class="row align-items-center">
                <div class="col-auto">
                    <div class="admin-avatar">
                        {{ substr($admin->name, 0, 2) }}
                    </div>
                </div>
                <div class="col">
                    <h1 class="admin-name">{{ $admin->name }}</h1>
                    <div class="admin-subtitle">{{ $admin->shop_name }}</div>
                    <div class="admin-meta">
                        <div class="meta-item">
                            <i class="fas fa-envelope"></i>
                            {{ $admin->email }}
                        </div>
                        @if($admin->phone)
                            <div class="meta-item">
                                <i class="fas fa-phone"></i>
                                {{ $admin->phone }}
                            </div>
                        @endif
                        <div class="meta-item">
                            <i class="fas fa-calendar"></i>
                            Membre depuis {{ $admin->created_at->format('M Y') }}
                        </div>
                        <div class="meta-item">
                            <i class="fas fa-fingerprint"></i>
                            ID: {{ $admin->identifier }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistiques principales -->
        <div class="info-card">
            <div class="card-header">
                <h3 class="card-title">
                    <div class="card-icon">
                        <i class="fas fa-chart-bar"></i>
                    </div>
                    Statistiques de performance
                </h3>
            </div>
            <div class="card-body">
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-number">{{ number_format($stats['total_orders']) }}</div>
                        <div class="stat-label">Commandes totales</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number">{{ number_format($stats['total_revenue']) }}€</div>
                        <div class="stat-label">Revenus générés</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number">{{ $totalManagers }}</div>
                        <div class="stat-label">Managers actifs</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number">{{ $totalEmployees }}</div>
                        <div class="stat-label">Employés actifs</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number">{{ $stats['active_hours'] }}</div>
                        <div class="stat-label">Heures d'activité</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number">{{ $stats['avg_orders_per_day'] }}</div>
                        <div class="stat-label">Commandes/jour</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <!-- Informations détaillées -->
            <div class="col-lg-8">
                <!-- Informations du compte -->
                <div class="info-card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <div class="card-icon">
                                <i class="fas fa-user"></i>
                            </div>
                            Informations du compte
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="info-grid">
                            <div class="info-item">
                                <div class="info-label">Nom complet</div>
                                <div class="info-value">{{ $admin->name }}</div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">Email</div>
                                <div class="info-value">{{ $admin->email }}</div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">Téléphone</div>
                                <div class="info-value">{{ $admin->phone ?: 'Non renseigné' }}</div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">Boutique</div>
                                <div class="info-value">{{ $admin->shop_name }}</div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">Identifiant</div>
                                <div class="info-value">{{ $admin->identifier }}</div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">Date de création</div>
                                <div class="info-value">{{ $admin->created_at->format('d/m/Y à H:i') }}</div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">Dernière connexion</div>
                                <div class="info-value">
                                    {{ $stats['last_login'] ? $stats['last_login']->diffForHumans() : 'Jamais connecté' }}
                                </div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">Statut</div>
                                <div class="info-value">
                                    <span class="badge bg-{{ $admin->is_active ? 'success' : 'danger' }}">
                                        {{ $admin->is_active ? 'Actif' : 'Inactif' }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Utilisation des ressources -->
                <div class="info-card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <div class="card-icon">
                                <i class="fas fa-chart-pie"></i>
                            </div>
                            Utilisation des ressources
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="progress-section">
                            <div class="progress-item">
                                <div class="progress-header d-flex justify-content-between">
                                    <span class="progress-label">Managers</span>
                                    <span class="progress-value">{{ $totalManagers }} / {{ $admin->max_managers }}</span>
                                </div>
                                <div class="progress">
                                    <div class="progress-bar bg-primary" 
                                         style="width: {{ $admin->max_managers > 0 ? ($totalManagers / $admin->max_managers) * 100 : 0 }}%">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="progress-item">
                                <div class="progress-header d-flex justify-content-between">
                                    <span class="progress-label">Employés</span>
                                    <span class="progress-value">{{ $totalEmployees }} / {{ $admin->max_employees }}</span>
                                </div>
                                <div class="progress">
                                    <div class="progress-bar bg-success" 
                                         style="width: {{ $admin->max_employees > 0 ? ($totalEmployees / $admin->max_employees) * 100 : 0 }}%">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="progress-item">
                                <div class="progress-header d-flex justify-content-between">
                                    <span class="progress-label">Utilisation globale</span>
                                    <span class="progress-value">{{ $stats['usage_percentage'] }}%</span>
                                </div>
                                <div class="progress">
                                    <div class="progress-bar bg-warning" 
                                         style="width: {{ $stats['usage_percentage'] }}%">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Graphique d'activité -->
                <div class="info-card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <div class="card-icon">
                                <i class="fas fa-chart-line"></i>
                            </div>
                            Activité récente
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <div class="text-center">
                                <i class="fas fa-chart-line fa-3x mb-3"></i>
                                <p>Graphique d'activité</p>
                                <small class="text-muted">Données des 30 derniers jours</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Panneau latéral -->
            <div class="col-lg-4">
                <!-- Abonnement -->
                <div class="info-card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <div class="card-icon">
                                <i class="fas fa-crown"></i>
                            </div>
                            Abonnement
                        </h3>
                    </div>
                    <div class="card-body">
                        @if($admin->expiry_date && $admin->expiry_date->isPast())
                            <div class="subscription-info expiry-warning">
                                <div class="subscription-title">
                                    <i class="fas fa-exclamation-triangle"></i>
                                    Abonnement expiré
                                </div>
                                <p class="mb-0">Expiré le {{ $admin->expiry_date->format('d/m/Y') }}</p>
                            </div>
                        @elseif($admin->expiry_date && $admin->expiry_date->diffInDays() <= 7)
                            <div class="subscription-info">
                                <div class="subscription-title">
                                    <i class="fas fa-clock"></i>
                                    Expire bientôt
                                </div>
                                <p class="mb-0">Expire le {{ $admin->expiry_date->format('d/m/Y') }}</p>
                            </div>
                        @endif
                        
                        <div class="info-grid">
                            <div class="info-item">
                                <div class="info-label">Type</div>
                                <div class="info-value">
                                    <span class="badge bg-primary">{{ ucfirst($admin->subscription_type ?? 'Trial') }}</span>
                                </div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">Statut</div>
                                <div class="info-value">{{ $stats['subscription_status'] }}</div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">Expiration</div>
                                <div class="info-value">
                                    {{ $admin->expiry_date ? $admin->expiry_date->format('d/m/Y') : 'Illimité' }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Actions rapides -->
                <div class="info-card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <div class="card-icon">
                                <i class="fas fa-tools"></i>
                            </div>
                            Actions rapides
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="quick-actions">
                            <a href="{{ route('super-admin.admins.edit', $admin) }}" class="action-btn">
                                <div class="action-icon">
                                    <i class="fas fa-edit"></i>
                                </div>
                                <div class="action-text">Modifier</div>
                            </a>
                            
                            <button type="button" class="action-btn" onclick="toggleStatus()">
                                <div class="action-icon">
                                    <i class="fas fa-{{ $admin->is_active ? 'times' : 'check' }}"></i>
                                </div>
                                <div class="action-text">{{ $admin->is_active ? 'Désactiver' : 'Activer' }}</div>
                            </button>
                            
                            <button type="button" class="action-btn" onclick="extendSubscription()">
                                <div class="action-icon">
                                    <i class="fas fa-clock"></i>
                                </div>
                                <div class="action-text">Prolonger</div>
                            </button>
                            
                            <button type="button" class="action-btn" onclick="resetPassword()">
                                <div class="action-icon">
                                    <i class="fas fa-key"></i>
                                </div>
                                <div class="action-text">Reset MDP</div>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Activité récente -->
                <div class="info-card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <div class="card-icon">
                                <i class="fas fa-history"></i>
                            </div>
                            Historique
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="timeline">
                            <div class="timeline-item">
                                <div class="timeline-content">
                                    <div class="timeline-date">{{ $admin->created_at->format('d/m/Y') }}</div>
                                    <div class="timeline-title">Compte créé</div>
                                    <div class="timeline-desc">Inscription sur la plateforme</div>
                                </div>
                            </div>
                            
                            @if($stats['last_login'])
                                <div class="timeline-item">
                                    <div class="timeline-content">
                                        <div class="timeline-date">{{ $stats['last_login']->format('d/m/Y') }}</div>
                                        <div class="timeline-title">Dernière connexion</div>
                                        <div class="timeline-desc">{{ $stats['last_login']->diffForHumans() }}</div>
                                    </div>
                                </div>
                            @endif
                            
                            @if($admin->updated_at != $admin->created_at)
                                <div class="timeline-item">
                                    <div class="timeline-content">
                                        <div class="timeline-date">{{ $admin->updated_at->format('d/m/Y') }}</div>
                                        <div class="timeline-title">Profil modifié</div>
                                        <div class="timeline-desc">Dernière modification du profil</div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modals -->
    <!-- Modal pour prolonger l'abonnement -->
    <div class="modal fade" id="extendModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Prolonger l'abonnement</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="extendForm" action="{{ route('super-admin.admins.extend-subscription', $admin) }}" method="POST">
                        @csrf
                        @method('PATCH')
                        <div class="mb-3">
                            <label class="form-label">Nombre de mois</label>
                            <select class="form-select" name="months" required>
                                <option value="1">1 mois</option>
                                <option value="3">3 mois</option>
                                <option value="6">6 mois</option>
                                <option value="12">12 mois</option>
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="button" class="btn btn-primary" onclick="document.getElementById('extendForm').submit()">
                        Prolonger
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Animation des barres de progression
    setTimeout(() => {
        document.querySelectorAll('.progress-bar').forEach(bar => {
            bar.style.transition = 'width 1s ease-in-out';
        });
    }, 500);
});

function toggleStatus() {
    const isActive = {{ $admin->is_active ? 'true' : 'false' }};
    const action = isActive ? 'désactiver' : 'activer';
    
    if (confirm(`Êtes-vous sûr de vouloir ${action} cet administrateur ?`)) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '{{ route('super-admin.admins.toggle-active', $admin) }}';
        
        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = '{{ csrf_token() }}';
        form.appendChild(csrfToken);
        
        const methodInput = document.createElement('input');
        methodInput.type = 'hidden';
        methodInput.name = '_method';
        methodInput.value = 'PATCH';
        form.appendChild(methodInput);
        
        document.body.appendChild(form);
        form.submit();
    }
}

function extendSubscription() {
    new bootstrap.Modal(document.getElementById('extendModal')).show();
}

function resetPassword() {
    if (confirm('Êtes-vous sûr de vouloir réinitialiser le mot de passe ? Un nouveau mot de passe sera envoyé par email.')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '{{ route('super-admin.admins.reset-password', $admin) }}';
        
        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = '{{ csrf_token() }}';
        form.appendChild(csrfToken);
        
        document.body.appendChild(form);
        form.submit();
    }
}

function deleteAdmin() {
    if (confirm('Êtes-vous sûr de vouloir supprimer cet administrateur ? Cette action est irréversible.')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '{{ route('super-admin.admins.destroy', $admin) }}';
        
        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = '{{ csrf_token() }}';
        form.appendChild(csrfToken);
        
        const methodInput = document.createElement('input');
        methodInput.type = 'hidden';
        methodInput.name = '_method';
        methodInput.value = 'DELETE';
        form.appendChild(methodInput);
        
        document.body.appendChild(form);
        form.submit();
    }
}
</script>
@endsection