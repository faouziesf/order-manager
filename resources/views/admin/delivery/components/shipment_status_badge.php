@php
    $statusConfig = [
        'created' => [
            'class' => 'bg-secondary',
            'icon' => 'fas fa-plus',
            'label' => 'Créée'
        ],
        'validated' => [
            'class' => 'bg-primary',
            'icon' => 'fas fa-check',
            'label' => 'Validée'
        ],
        'picked_up_by_carrier' => [
            'class' => 'bg-warning',
            'icon' => 'fas fa-truck-pickup',
            'label' => 'Récupérée'
        ],
        'in_transit' => [
            'class' => 'bg-info',
            'icon' => 'fas fa-truck-moving',
            'label' => 'En Transit'
        ],
        'delivered' => [
            'class' => 'bg-success',
            'icon' => 'fas fa-check-circle',
            'label' => 'Livrée'
        ],
        'cancelled' => [
            'class' => 'bg-secondary',
            'icon' => 'fas fa-times',
            'label' => 'Annulée'
        ],
        'in_return' => [
            'class' => 'bg-warning',
            'icon' => 'fas fa-undo',
            'label' => 'En Retour'
        ],
        'anomaly' => [
            'class' => 'bg-danger',
            'icon' => 'fas fa-exclamation-triangle',
            'label' => 'Anomalie'
        ]
    ];
    
    // Déterminer si nous utilisons Alpine.js ou PHP
    $useAlpine = isset($shipment) && is_string($shipment);
@endphp

@if($useAlpine)
    {{-- Version Alpine.js --}}
    <template x-for="(config, status) in {{ json_encode($statusConfig) }}" :key="status">
        <span x-show="{{ $shipment }}.status === status" 
              :class="`badge ${config.class}`">
            <i :class="config.icon" class="me-1"></i>
            <span x-text="config.label"></span>
        </span>
    </template>
@else
    {{-- Version PHP classique --}}
    @php
        $status = $shipment->status ?? 'created';
        $config = $statusConfig[$status] ?? $statusConfig['created'];
    @endphp
    
    <span class="badge {{ $config['class'] }}">
        <i class="{{ $config['icon'] }} me-1"></i>
        {{ $config['label'] }}
    </span>
    
    {{-- Informations supplémentaires si disponibles --}}
    @if(isset($showDetails) && $showDetails && $shipment->carrier_last_status_update)
        <br>
        <small class="text-muted">
            MAJ: {{ $shipment->carrier_last_status_update->diffForHumans() }}
        </small>
    @endif
@endif