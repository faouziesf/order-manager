<div class="card shadow border-0 h-100 position-relative">
    <!-- Indicateur de statut -->
    <div class="status-indicator {{ $carrier['status'] === 'connecté' ? 'connected' : ($carrier['status'] === 'configuré_inactif' ? 'inactive' : 'disconnected') }}"></div>
    
    <div class="card-body">
        <!-- Header avec logo et nom -->
        <div class="d-flex align-items-center mb-3">
            <div class="me-3">
                @if(isset($carrier['config']['logo']))
                    <img src="{{ asset($carrier['config']['logo']) }}" 
                         alt="{{ $carrier['config']['name'] }}" 
                         class="carrier-logo">
                @else
                    <div class="carrier-logo d-flex align-items-center justify-content-center">
                        <i class="fas fa-truck fa-2x text-muted"></i>
                    </div>
                @endif
            </div>
            <div class="flex-grow-1">
                <h5 class="card-title mb-1">{{ $carrier['config']['name'] }}</h5>
                <span class="{{ 
                    $carrier['status'] === 'connecté' ? 'badge bg-success' : 
                    ($carrier['status'] === 'configuré_inactif' ? 'badge bg-warning' : 'badge bg-secondary') 
                }}">
                    {{ 
                        $carrier['status'] === 'connecté' ? 'Connecté' : 
                        ($carrier['status'] === 'configuré_inactif' ? 'Inactif' : 'Non configuré') 
                    }}
                </span>
            </div>
        </div>

        <!-- Description -->
        @if(isset($carrier['config']['description']))
            <p class="text-muted small mb-3">{{ $carrier['config']['description'] }}</p>
        @endif

        <!-- Statistiques -->
        <div class="row text-center mb-3">
            <div class="col-4">
                <div class="h6 mb-0 text-primary">{{ $carrier['stats']['configurations'] }}</div>
                <small class="text-muted">Configs</small>
            </div>
            <div class="col-4">
                <div class="h6 mb-0 text-success">{{ $carrier['stats']['pickups'] }}</div>
                <small class="text-muted">Pickups</small>
            </div>
            <div class="col-4">
                <div class="h6 mb-0 text-info">{{ $carrier['stats']['shipments'] }}</div>
                <small class="text-muted">Envois</small>
            </div>
        </div>

        <!-- Configurations actives -->
        @if($carrier['active_configurations']->isNotEmpty())
            <div class="mb-3">
                <small class="text-muted d-block mb-1">Configurations actives :</small>
                @foreach($carrier['active_configurations'] as $config)
                    <span class="badge bg-light text-dark me-1 mb-1">
                        {{ $config->integration_name }}
                    </span>
                @endforeach
            </div>
        @endif

        <!-- Actions -->
        <div class="d-grid gap-2">
            @if($carrier['is_configured'])
                <div class="btn-group" role="group">
                    @if($carrier['active_configurations']->isNotEmpty())
                        <a href="{{ route('admin.delivery.preparation') }}?carrier={{ $slug }}" 
                           class="btn btn-success btn-sm">
                            <i class="fas fa-plus me-1"></i>
                            Nouvel Envoi
                        </a>
                        <button type="button" 
                                class="btn btn-outline-primary btn-sm"
                                @click="testCarrierConnection('{{ $slug }}', {{ $carrier['active_configurations']->first()->id }})">
                            <i class="fas fa-wifi me-1"></i>
                            Tester
                        </button>
                    @else
                        <a href="{{ route('admin.delivery.configuration.create') }}?carrier={{ $slug }}" 
                           class="btn btn-warning btn-sm">
                            <i class="fas fa-power-off me-1"></i>
                            Activer
                        </a>
                    @endif
                    <a href="{{ route('admin.delivery.configuration') }}?filter={{ $slug }}" 
                       class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-cog me-1"></i>
                        Gérer
                    </a>
                </div>
            @else
                <a href="{{ route('admin.delivery.configuration.create') }}?carrier={{ $slug }}" 
                   class="btn btn-primary btn-sm">
                    <i class="fas fa-plus me-1"></i>
                    Configurer
                </a>
            @endif
        </div>
    </div>

    <!-- Footer avec infos supplémentaires -->
    @if(isset($carrier['config']['website']) || isset($carrier['config']['support_phone']))
        <div class="card-footer bg-transparent border-top-0 pt-0">
            <div class="d-flex justify-content-between align-items-center">
                @if(isset($carrier['config']['website']))
                    <a href="{{ $carrier['config']['website'] }}" 
                       target="_blank" 
                       class="text-muted small">
                        <i class="fas fa-external-link-alt me-1"></i>
                        Site web
                    </a>
                @endif
                
                @if(isset($carrier['config']['support_phone']))
                    <span class="text-muted small">
                        <i class="fas fa-phone me-1"></i>
                        {{ $carrier['config']['support_phone'] }}
                    </span>
                @endif
            </div>
        </div>
    @endif
</div>