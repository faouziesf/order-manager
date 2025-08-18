@extends('layouts.admin')

@section('title', 'Configuration des Transporteurs')

@push('head')
<meta name="csrf-token" content="{{ csrf_token() }}">
@endpush

@section('css')
<style>
    body {
        background: #f8fafc;
        font-family: 'Inter', sans-serif;
    }

    .config-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 1rem;
    }

    /* ===== HEADER ===== */
    .page-header {
        background: white;
        border-radius: 6px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        padding: 1rem;
        margin-bottom: 1rem;
    }

    .header-title {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 0.75rem;
    }

    .title-section h1 {
        font-size: 1.25rem;
        font-weight: 600;
        color: #1f2937;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 0.4rem;
    }

    .title-section p {
        color: #6b7280;
        margin: 0.25rem 0 0 0;
        font-size: 0.8rem;
    }

    .header-actions {
        display: flex;
        gap: 0.5rem;
    }

    .btn {
        padding: 0.375rem 0.75rem;
        border-radius: 4px;
        font-weight: 500;
        font-size: 0.8rem;
        text-decoration: none;
        border: none;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 0.3rem;
        transition: all 0.2s;
    }

    .btn:hover:not(:disabled) {
        transform: translateY(-1px);
        text-decoration: none;
    }

    .btn:disabled {
        opacity: 0.6;
        cursor: not-allowed;
        transform: none;
    }

    .btn-primary {
        background: #1d4ed8;
        color: white;
    }

    .btn-primary:hover:not(:disabled) {
        background: #1e3a8a;
        color: white;
    }

    .btn-outline {
        background: white;
        color: #374151;
        border: 1px solid #d1d5db;
    }

    .btn-outline:hover:not(:disabled) {
        background: #f3f4f6;
        color: #374151;
    }

    .btn-success {
        background: #059669;
        color: white;
    }

    .btn-success:hover:not(:disabled) {
        background: #047857;
        color: white;
    }

    /* ===== STATISTIQUES ===== */
    .stats-row {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 0.75rem;
        margin-bottom: 1rem;
    }

    .stat-card {
        background: white;
        border-radius: 6px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        padding: 0.75rem;
        text-align: center;
    }

    .stat-number {
        font-size: 1.5rem;
        font-weight: 600;
        color: #1d4ed8;
        margin: 0;
    }

    .stat-label {
        color: #6b7280;
        font-size: 0.75rem;
        margin: 0.2rem 0 0 0;
    }

    /* ===== TRANSPORTEURS ===== */
    .carrier-card {
        background: white;
        border-radius: 6px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        margin-bottom: 1rem;
        overflow: hidden;
    }

    .carrier-header {
        padding: 0.75rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .carrier-header.jax_delivery {
        background: linear-gradient(135deg, #1d4ed8, #1e3a8a);
        color: white;
    }

    .carrier-header.mes_colis {
        background: linear-gradient(135deg, #059669, #047857);
        color: white;
    }

    .carrier-info {
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .carrier-logo {
        width: 40px;
        height: 40px;
        background: rgba(255,255,255,0.15);
        border-radius: 6px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
        position: relative;
        overflow: hidden;
    }

    .carrier-logo img {
        width: 100%;
        height: 100%;
        object-fit: contain;
        border-radius: 4px;
    }

    .carrier-logo .fallback-icon {
        color: rgba(255,255,255,0.9);
    }

    .carrier-details h3 {
        font-size: 1rem;
        font-weight: 600;
        margin: 0;
    }

    .carrier-details .count {
        font-size: 0.75rem;
        opacity: 0.9;
        margin: 0.1rem 0 0 0;
    }

    .btn-add-config {
        background: rgba(255,255,255,0.15);
        color: white;
        border: 1px solid rgba(255,255,255,0.3);
        padding: 0.3rem 0.6rem;
        border-radius: 4px;
        font-size: 0.75rem;
        font-weight: 500;
    }

    .btn-add-config:hover:not(:disabled) {
        background: rgba(255,255,255,0.25);
        color: white;
    }

    /* ===== TABLEAU ===== */
    .configs-table {
        width: 100%;
        border-collapse: collapse;
        background: white;
        font-size: 0.8rem;
    }

    .configs-table th {
        background: #f9fafb;
        padding: 0.5rem;
        font-weight: 600;
        font-size: 0.75rem;
        color: #374151;
        text-align: left;
        border-bottom: 1px solid #e5e7eb;
    }

    .configs-table td {
        padding: 0.75rem 0.5rem;
        border-bottom: 1px solid #f3f4f6;
        vertical-align: middle;
    }

    .configs-table tbody tr:hover {
        background: #f9fafb;
    }

    .config-info {
        display: flex;
        align-items: center;
        gap: 0.4rem;
    }

    .status-dot {
        width: 6px;
        height: 6px;
        border-radius: 50%;
        flex-shrink: 0;
    }

    .status-dot.active {
        background: #10b981;
    }

    .status-dot.inactive {
        background: #9ca3af;
    }

    .status-dot.invalid {
        background: #ef4444;
    }

    .config-name {
        font-weight: 600;
        color: #1f2937;
        margin: 0;
        font-size: 0.8rem;
    }

    .config-date {
        font-size: 0.7rem;
        color: #6b7280;
        margin: 0;
    }

    .credential-info {
        font-size: 0.75rem;
        color: #6b7280;
    }

    .credential-preview {
        background: #f3f4f6;
        padding: 0.15rem 0.3rem;
        border-radius: 3px;
        font-family: monospace;
        font-size: 0.7rem;
        display: inline-block;
        margin-top: 0.15rem;
    }

    .status-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
        padding: 0.25rem 0.5rem;
        border-radius: 10px;
        font-size: 0.7rem;
        font-weight: 600;
    }

    .status-badge.active {
        background: #dcfce7;
        color: #166534;
    }

    .status-badge.inactive {
        background: #f3f4f6;
        color: #6b7280;
    }

    .status-badge.invalid {
        background: #fee2e2;
        color: #991b1b;
    }

    .actions {
        display: flex;
        gap: 0.25rem;
    }

    .btn-action {
        width: 28px;
        height: 28px;
        padding: 0;
        border-radius: 4px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.75rem;
        border: 1px solid transparent;
        cursor: pointer;
    }

    .btn-test {
        background: #dbeafe;
        color: #1d4ed8;
        border-color: #bfdbfe;
    }

    .btn-test:hover:not(:disabled) {
        background: #bfdbfe;
    }

    .btn-toggle.active {
        background: #fef3c7;
        color: #d97706;
        border-color: #fed7aa;
    }

    .btn-toggle.inactive {
        background: #dcfce7;
        color: #059669;
        border-color: #bbf7d0;
    }

    .btn-edit {
        background: #f3f4f6;
        color: #6b7280;
        border-color: #e5e7eb;
    }

    .btn-edit:hover:not(:disabled) {
        background: #e5e7eb;
    }

    .btn-delete {
        background: #fee2e2;
        color: #dc2626;
        border-color: #fecaca;
    }

    .btn-delete:hover:not(:disabled) {
        background: #fecaca;
    }

    /* ===== ÉTAT VIDE ===== */
    .empty-state {
        text-align: center;
        padding: 2rem 1rem;
        background: white;
    }

    .empty-icon {
        width: 50px;
        height: 50px;
        background: #f3f4f6;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 0.75rem;
        font-size: 1.2rem;
        color: #6b7280;
    }

    .empty-state h3 {
        font-size: 1rem;
        font-weight: 600;
        color: #1f2937;
        margin-bottom: 0.4rem;
    }

    .empty-state p {
        color: #6b7280;
        margin-bottom: 1rem;
        font-size: 0.8rem;
    }

    /* ===== LOADING ===== */
    .loading {
        opacity: 0.6;
        pointer-events: none;
    }

    .spinner {
        display: inline-block;
        width: 12px;
        height: 12px;
        border: 2px solid transparent;
        border-top: 2px solid currentColor;
        border-radius: 50%;
        animation: spin 1s linear infinite;
    }

    @keyframes spin {
        to { transform: rotate(360deg); }
    }

    /* ===== RESPONSIVE ===== */
    @media (max-width: 768px) {
        .config-container {
            padding: 0.5rem;
        }

        .header-title {
            flex-direction: column;
            align-items: stretch;
        }

        .header-actions {
            justify-content: stretch;
        }

        .btn {
            flex: 1;
            justify-content: center;
        }

        .carrier-header {
            flex-direction: column;
            gap: 1rem;
            text-align: center;
        }

        .configs-table {
            font-size: 0.8rem;
        }

        .configs-table th,
        .configs-table td {
            padding: 0.5rem;
        }

        .actions {
            flex-direction: column;
        }
    }

    /* ===== ANIMATIONS ===== */
    .fade-in {
        animation: fadeIn 0.3s ease-out;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style>
@endsection

@section('content')
<div class="config-container fade-in" x-data="configurationManager">
    <!-- Header -->
    <div class="page-header">
        <div class="header-title">
            <div class="title-section">
                <h1>
                    <i class="fas fa-cogs text-primary"></i>
                    Configuration Transporteurs
                </h1>
                <p>Gérez vos liaisons JAX Delivery et Mes Colis Express</p>
            </div>
            <div class="header-actions">
                <a href="{{ route('admin.delivery.index') }}" class="btn btn-outline">
                    <i class="fas fa-arrow-left"></i>
                    Retour
                </a>
                <div class="dropdown">
                    <button class="btn btn-success dropdown-toggle" type="button" data-bs-toggle="dropdown">
                        <i class="fas fa-plus"></i>
                        Nouvelle Config
                    </button>
                    <ul class="dropdown-menu">
                        <li>
                            <a class="dropdown-item" href="{{ route('admin.delivery.configuration.create') }}?carrier=jax_delivery">
                                <i class="fas fa-truck me-2 text-primary"></i>
                                JAX Delivery
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="{{ route('admin.delivery.configuration.create') }}?carrier=mes_colis">
                                <i class="fas fa-shipping-fast me-2 text-success"></i>
                                Mes Colis Express
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistiques -->
    <div class="stats-row">
        <div class="stat-card">
            <h2 class="stat-number">{{ $generalStats['total_configurations'] ?? 0 }}</h2>
            <p class="stat-label">Configurations totales</p>
        </div>
        <div class="stat-card">
            <h2 class="stat-number">{{ $generalStats['active_configurations'] ?? 0 }}</h2>
            <p class="stat-label">Configurations actives</p>
        </div>
        <div class="stat-card">
            <h2 class="stat-number">{{ $generalStats['total_pickups'] ?? 0 }}</h2>
            <p class="stat-label">Pickups créés</p>
        </div>
        <div class="stat-card">
            <h2 class="stat-number">{{ $generalStats['total_shipments'] ?? 0 }}</h2>
            <p class="stat-label">Expéditions</p>
        </div>
    </div>

    <!-- Transporteurs -->
    @if(empty($carriersData))
        <div class="carrier-card">
            <div class="empty-state">
                <div class="empty-icon">
                    <i class="fas fa-cogs"></i>
                </div>
                <h3>Aucune configuration</h3>
                <p>Créez votre première configuration de transporteur pour commencer</p>
                <a href="{{ route('admin.delivery.configuration.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i>
                    Première configuration
                </a>
            </div>
        </div>
    @else
        @foreach($carriersData as $carrierSlug => $carrierData)
            <div class="carrier-card">
                <!-- Header transporteur -->
                <div class="carrier-header {{ $carrierSlug }}">
                    <div class="carrier-info">
                        <div class="carrier-logo">
                            @if($carrierSlug === 'jax_delivery')
                                <img src="https://jax-delivery.com/images/logo-jax.png" 
                                     alt="JAX Delivery" 
                                     onerror="this.style.display='none'; this.nextElementSibling.style.display='flex'">
                                <i class="fas fa-truck fallback-icon" style="display: none;"></i>
                            @else
                                <img src="https://mescolis.tn/assets/img/logo-mescolis.png" 
                                     alt="Mes Colis" 
                                     onerror="this.style.display='none'; this.nextElementSibling.style.display='flex'">
                                <i class="fas fa-shipping-fast fallback-icon" style="display: none;"></i>
                            @endif
                        </div>
                        <div class="carrier-details">
                            <h3>{{ $carrierData['config']['name'] ?? ($carrierSlug === 'jax_delivery' ? 'JAX Delivery' : 'Mes Colis Express') }}</h3>
                            <p class="count">{{ $carrierData['configurations']->count() }} configuration(s) • {{ $carrierData['active_configurations']->count() }} active(s)</p>
                        </div>
                    </div>
                    <a href="{{ route('admin.delivery.configuration.create') }}?carrier={{ $carrierSlug }}" 
                       class="btn-add-config">
                        <i class="fas fa-plus"></i>
                        Ajouter
                    </a>
                </div>

                <!-- Tableau des configurations -->
                @if($carrierData['configurations']->isNotEmpty())
                    <table class="configs-table">
                        <thead>
                            <tr>
                                <th>Configuration</th>
                                <th>Identifiants</th>
                                <th>Statut</th>
                                <th>Dernière activité</th>
                                <th style="text-align: center;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($carrierData['configurations'] as $config)
                                <tr>
                                    <td>
                                        <div class="config-info">
                                            <div class="status-dot {{ $config->is_active ? ($config->is_valid ? 'active' : 'invalid') : 'inactive' }}"></div>
                                            <div>
                                                <h4 class="config-name">{{ $config->integration_name }}</h4>
                                                <p class="config-date">Créé le {{ $config->created_at->format('d/m/Y') }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="credential-info">
                                            @if($carrierSlug === 'jax_delivery')
                                                <div>Compte: {{ $config->username ?? 'Non défini' }}</div>
                                                <div class="credential-preview">Token: {{ !empty($config->password) ? '••••••••' : 'Non défini' }}</div>
                                            @else
                                                <div>Token d'accès</div>
                                                <div class="credential-preview">
                                                    @if(!empty($config->password))
                                                        {{ Str::limit($config->password, 8) }}•••
                                                    @elseif(!empty($config->username))
                                                        {{ Str::limit($config->username, 8) }}••• (ancien)
                                                    @else
                                                        Non défini
                                                    @endif
                                                </div>
                                            @endif
                                            <div style="font-size: 0.75rem; margin-top: 0.2rem;">
                                                Env: {{ ucfirst($config->environment) }}
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="status-badge {{ $config->is_active ? ($config->is_valid ? 'active' : 'invalid') : 'inactive' }}">
                                            <i class="fas fa-{{ $config->is_active ? ($config->is_valid ? 'check' : 'exclamation-triangle') : 'pause' }}"></i>
                                            {{ $config->is_active ? ($config->is_valid ? 'Active' : 'Invalide') : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td>
                                        <span style="font-size: 0.8rem; color: #6b7280;">
                                            {{ $config->updated_at->diffForHumans() }}
                                        </span>
                                    </td>
                                    <td style="text-align: center;">
                                        <div class="actions">
                                            <button class="btn-action btn-test" 
                                                    @click="testConnection({{ $config->id }})"
                                                    :disabled="loading"
                                                    title="Tester la connexion">
                                                <i class="fas fa-plug"></i>
                                            </button>
                                            
                                            <button class="btn-action btn-toggle {{ $config->is_active ? 'active' : 'inactive' }}" 
                                                    @click="toggleConfig({{ $config->id }})"
                                                    :disabled="loading"
                                                    title="{{ $config->is_active ? 'Désactiver' : 'Activer' }}">
                                                <i class="fas fa-{{ $config->is_active ? 'pause' : 'play' }}"></i>
                                            </button>
                                            
                                            <a href="{{ route('admin.delivery.configuration.edit', $config) }}" 
                                               class="btn-action btn-edit"
                                               title="Modifier">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            
                                            <button class="btn-action btn-delete" 
                                                    @click="deleteConfig({{ $config->id }}, '{{ addslashes($config->integration_name) }}')"
                                                    :disabled="loading"
                                                    title="Supprimer">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <div class="empty-state">
                        <div class="empty-icon">
                            <i class="fas fa-{{ $carrierSlug === 'jax_delivery' ? 'truck' : 'shipping-fast' }}"></i>
                        </div>
                        <h3>Aucune configuration {{ $carrierData['config']['name'] ?? 'pour ce transporteur' }}</h3>
                        <p>Créez votre première configuration pour ce transporteur</p>
                        <a href="{{ route('admin.delivery.configuration.create') }}?carrier={{ $carrierSlug }}" class="btn btn-primary">
                            <i class="fas fa-plus"></i>
                            Créer une configuration
                        </a>
                    </div>
                @endif
            </div>
        @endforeach
    @endif
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('configurationManager', () => ({
        loading: false,

        init() {
            // Vérifier que le token CSRF est disponible
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            if (!csrfToken) {
                console.error('Token CSRF non trouvé. Assurez-vous que <meta name="csrf-token" content="{{ csrf_token() }}"> est présent dans le <head>');
            }
        },

        async testConnection(configId) {
            if (this.loading) return;
            
            this.loading = true;
            
            try {
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                if (!csrfToken) {
                    throw new Error('Token CSRF non trouvé');
                }

                const response = await fetch(`{{ url('/admin/delivery/configuration') }}/${configId}/test`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                const data = await response.json();

                if (data.success) {
                    this.showToast('success', 'Test de connexion réussi');
                } else {
                    this.showToast('error', data.message || 'Test de connexion échoué');
                }
            } catch (error) {
                console.error('Erreur:', error);
                this.showToast('error', 'Erreur lors du test de connexion');
            } finally {
                this.loading = false;
            }
        },

        async toggleConfig(configId) {
            if (this.loading) return;
            
            this.loading = true;
            
            try {
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                if (!csrfToken) {
                    throw new Error('Token CSRF non trouvé');
                }

                const response = await fetch(`{{ url('/admin/delivery/configuration') }}/${configId}/toggle`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                const data = await response.json();

                if (data.success) {
                    this.showToast('success', data.message);
                    setTimeout(() => window.location.reload(), 1200);
                } else {
                    this.showToast('error', data.message || 'Erreur lors du changement de statut');
                }
            } catch (error) {
                console.error('Erreur:', error);
                this.showToast('error', 'Erreur de communication');
            } finally {
                this.loading = false;
            }
        },

        async deleteConfig(configId, configName) {
            if (this.loading) return;
            
            if (!confirm(`Êtes-vous sûr de vouloir supprimer la configuration "${configName}" ?\n\nCette action est irréversible.`)) {
                return;
            }

            this.loading = true;

            try {
                // Récupérer le token CSRF
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                if (!csrfToken) {
                    throw new Error('Token CSRF non trouvé');
                }

                const response = await fetch(`{{ url('/admin/delivery/configuration') }}/${configId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                let data;
                try {
                    data = await response.json();
                } catch (e) {
                    throw new Error('Réponse invalide du serveur');
                }

                if (response.ok && data.success) {
                    this.showToast('success', data.message || 'Configuration supprimée avec succès');
                    setTimeout(() => window.location.reload(), 1200);
                } else if (data.message) {
                    this.showToast('error', data.message);
                } else {
                    throw new Error(`Erreur HTTP ${response.status}`);
                }
            } catch (error) {
                console.error('Erreur suppression:', error);
                this.showToast('error', `Erreur lors de la suppression: ${error.message}`);
            } finally {
                this.loading = false;
            }
        },

        showToast(type, message) {
            // Supprimer les anciens toasts
            const existingToasts = document.querySelectorAll('.toast-notification');
            existingToasts.forEach(toast => toast.remove());

            const toast = document.createElement('div');
            toast.className = 'toast-notification';
            toast.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                z-index: 10000;
                min-width: 300px;
                max-width: 450px;
                padding: 1rem;
                border-radius: 8px;
                font-size: 0.9rem;
                font-weight: 600;
                color: white;
                background: ${type === 'success' ? '#059669' : '#dc2626'};
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
                animation: slideInRight 0.3s ease;
                display: flex;
                align-items: center;
                gap: 0.5rem;
            `;
            
            toast.innerHTML = `
                <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-triangle'}"></i>
                <span>${message}</span>
            `;
            
            document.body.appendChild(toast);
            
            setTimeout(() => {
                toast.style.animation = 'slideOutRight 0.3s ease';
                setTimeout(() => toast.remove(), 300);
            }, 3500);
        }
    }));
});
</script>

<style>
@keyframes slideInRight {
    from { transform: translateX(100%); opacity: 0; }
    to { transform: translateX(0); opacity: 1; }
}

@keyframes slideOutRight {
    from { transform: translateX(0); opacity: 1; }
    to { transform: translateX(100%); opacity: 0; }
}
</style>
@endsection