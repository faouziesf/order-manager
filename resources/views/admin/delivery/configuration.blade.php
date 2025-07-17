@extends('layouts.admin')

@section('title', 'Configuration Jax Delivery')

@push('styles')
<style>
.card {
    box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
    border: none;
}

.card-header {
    background-color: #f8f9fc;
    border-bottom: 1px solid #e3e6f0;
}

.text-gray-800 {
    color: #5a5c69 !important;
}

.text-gray-300 {
    color: #dddfeb !important;
}

.text-gray-600 {
    color: #858796 !important;
}

.border-left-primary {
    border-left: 0.25rem solid #4e73df !important;
}

.border-left-success {
    border-left: 0.25rem solid #1cc88a !important;
}

.border-left-info {
    border-left: 0.25rem solid #36b9cc !important;
}

.border-left-warning {
    border-left: 0.25rem solid #f6c23e !important;
}

.btn-group .btn {
    border-radius: 4px;
    margin-right: 2px;
}

.table th {
    background-color: #f8f9fa;
    border-top: none;
    font-weight: 600;
    color: #495057;
}

.badge {
    font-size: 0.75em;
    padding: 0.375rem 0.5rem;
}

.alert-info {
    background-color: #e3f2fd;
    border-color: #bbdefb;
    color: #1976d2;
}

/* Animation pour les alerts */
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
}

.alert {
    animation: fadeIn 0.3s ease-out;
}

/* Loading states */
.btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.spinner-border-sm {
    width: 1rem;
    height: 1rem;
}

/* Responsive improvements */
@media (max-width: 768px) {
    .btn-group {
        display: flex;
        flex-direction: column;
    }
    
    .btn-group .btn {
        margin-bottom: 2px;
        margin-right: 0;
    }
    
    .card-body {
        padding: 1rem;
    }
    
    .table-responsive {
        font-size: 0.875rem;
    }
}
</style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-md-8">
            <h1 class="h3 mb-0">Configuration Jax Delivery</h1>
            <p class="text-muted">Gérez vos configurations de livraison Jax Delivery</p>
        </div>
        <div class="col-md-4 text-right">
            <a href="{{ route('admin.delivery.configuration.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Nouvelle Configuration
            </a>
        </div>
    </div>

    <!-- Messages Flash -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>
            {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <!-- Statistiques rapides -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-left-primary">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Configurations
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['total_configs'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-cog fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-left-success">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Configurations Actives
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['active_configs'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-left-info">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Configurations Testées
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['tested_configs'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-vial fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-left-warning">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Tokens Expirés
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['expired_tokens'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Liste des configurations -->
    <div class="card shadow">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Configurations Jax Delivery</h6>
        </div>
        <div class="card-body">
            @if($configurations->count() > 0)
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>Nom d'Intégration</th>
                                <th>Environnement</th>
                                <th>Statut</th>
                                <th>Token Expire</th>
                                <th>Date Création</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($configurations as $config)
                            <tr>
                                <td>
                                    <strong>{{ $config->integration_name }}</strong>
                                    <br>
                                    <small class="text-muted">ID: {{ $config->id }}</small>
                                </td>
                                <td>
                                    <span class="badge badge-{{ $config->environment === 'prod' ? 'success' : 'warning' }}">
                                        {{ ucfirst($config->environment) }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge {{ $config->status_info['badge_class'] }}">
                                        {{ $config->status_info['badge_text'] }}
                                    </span>
                                    @if(!$config->is_active)
                                        <br><small class="text-muted">Inactif</small>
                                    @endif
                                </td>
                                <td>
                                    @if($config->expires_at)
                                        {{ $config->expires_at->format('d/m/Y H:i') }}
                                        <br>
                                        <small class="text-muted">
                                            ({{ $config->expires_at->diffForHumans() }})
                                        </small>
                                    @else
                                        <span class="text-muted">Non défini</span>
                                    @endif
                                </td>
                                <td>
                                    {{ $config->created_at->format('d/m/Y H:i') }}
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <button type="button" class="btn btn-sm btn-outline-primary" 
                                                onclick="testConnection({{ $config->id }})" 
                                                title="Tester la connexion">
                                            <i class="fas fa-vial"></i>
                                        </button>
                                        <a href="{{ route('admin.delivery.configuration.edit', $config) }}" 
                                           class="btn btn-sm btn-outline-secondary" 
                                           title="Modifier">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button type="button" class="btn btn-sm btn-outline-{{ $config->is_active ? 'warning' : 'success' }}" 
                                                onclick="toggleConfig({{ $config->id }})" 
                                                title="{{ $config->is_active ? 'Désactiver' : 'Activer' }}">
                                            <i class="fas fa-{{ $config->is_active ? 'pause' : 'play' }}"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-danger" 
                                                onclick="deleteConfig({{ $config->id }})" 
                                                title="Supprimer">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-truck fa-3x text-gray-300 mb-3"></i>
                    <h5 class="text-gray-600">Aucune configuration</h5>
                    <p class="text-muted">Créez votre première configuration Jax Delivery pour commencer.</p>
                    <a href="{{ route('admin.delivery.configuration.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Créer une Configuration
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Vérifier que les librairies sont disponibles
    if (typeof toastr === 'undefined') {
        console.warn('Toastr n\'est pas chargé, utilisation d\'alert de fallback');
        window.toastr = {
            success: function(msg) { 
                console.log('Success: ' + msg);
                alert('Succès: ' + msg); 
            },
            error: function(msg) { 
                console.error('Error: ' + msg);
                alert('Erreur: ' + msg); 
            },
            warning: function(msg) { 
                console.warn('Warning: ' + msg);
                alert('Attention: ' + msg); 
            }
        };
    }

    // Configuration de toastr si disponible
    if (typeof toastr !== 'undefined' && toastr.options) {
        toastr.options = {
            closeButton: true,
            debug: false,
            newestOnTop: true,
            progressBar: true,
            positionClass: "toast-top-right",
            preventDuplicates: false,
            onclick: null,
            showDuration: "300",
            hideDuration: "1000",
            timeOut: "5000",
            extendedTimeOut: "1000",
            showEasing: "swing",
            hideEasing: "linear",
            showMethod: "fadeIn",
            hideMethod: "fadeOut"
        };
    }

    // Fermer les alerts automatiquement après 5 secondes
    $('.alert').each(function() {
        const alert = $(this);
        setTimeout(() => {
            alert.fadeOut();
        }, 5000);
    });
});

// Test de connexion
function testConnection(configId) {
    const btn = event.target.closest('button');
    const originalHtml = btn.innerHTML;
    
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
    btn.disabled = true;
    
    fetch(`{{ route('admin.delivery.configuration.test', ':id') }}`.replace(':id', configId), {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json',
            'Accept': 'application/json',
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Erreur réseau: ' + response.status);
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            toastr.success(data.message);
            setTimeout(() => location.reload(), 1500);
        } else {
            toastr.error(data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        toastr.error('Erreur lors du test de connexion: ' + error.message);
    })
    .finally(() => {
        btn.innerHTML = originalHtml;
        btn.disabled = false;
    });
}

// Activer/Désactiver configuration
function toggleConfig(configId) {
    const btn = event.target.closest('button');
    const originalHtml = btn.innerHTML;
    
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
    btn.disabled = true;
    
    fetch(`{{ route('admin.delivery.configuration.toggle', ':id') }}`.replace(':id', configId), {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json',
            'Accept': 'application/json',
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Erreur réseau: ' + response.status);
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            toastr.success(data.message);
            setTimeout(() => location.reload(), 1500);
        } else {
            toastr.error(data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        toastr.error('Erreur lors de la modification: ' + error.message);
    })
    .finally(() => {
        btn.innerHTML = originalHtml;
        btn.disabled = false;
    });
}

// Supprimer configuration
function deleteConfig(configId) {
    if (confirm('Êtes-vous sûr de vouloir supprimer cette configuration ?\n\nCette action est irréversible et supprimera également tous les enlèvements associés.')) {
        const btn = event.target.closest('button');
        const originalHtml = btn.innerHTML;
        
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
        btn.disabled = true;
        
        fetch(`{{ route('admin.delivery.configuration.delete', ':id') }}`.replace(':id', configId), {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
                'Accept': 'application/json',
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Erreur réseau: ' + response.status);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                toastr.success(data.message);
                setTimeout(() => location.reload(), 1500);
            } else {
                toastr.error(data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            toastr.error('Erreur lors de la suppression: ' + error.message);
        })
        .finally(() => {
            btn.innerHTML = originalHtml;
            btn.disabled = false;
        });
    }
}
</script>
@endpush