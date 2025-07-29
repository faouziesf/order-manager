@php
    $statusConfig = [
        'draft' => [
            'class' => 'bg-secondary',
            'icon' => 'fas fa-edit',
            'label' => 'Brouillon'
        ],
        'validated' => [
            'class' => 'bg-success',
            'icon' => 'fas fa-check',
            'label' => 'Validé'
        ],
        'picked_up' => [
            'class' => 'bg-info',
            'icon' => 'fas fa-truck',
            'label' => 'Récupéré'
        ],
        'problem' => [
            'class' => 'bg-danger',
            'icon' => 'fas fa-exclamation-triangle',
            'label' => 'Problème'
        ]
    ];
    
    // Si nous utilisons Alpine.js, nous récupérons le statut depuis la variable pickup
    $useAlpine = isset($pickup) && is_string($pickup);
@endphp

@if($useAlpine)
    {{-- Version Alpine.js --}}
    <template x-for="(config, status) in {{ json_encode($statusConfig) }}" :key="status">
        <span x-show="{{ $pickup }}.status === status" 
              :class="`badge ${config.class}`">
            <i :class="config.icon" class="me-1"></i>
            <span x-text="config.label"></span>
        </span>
    </template>
@else
    {{-- Version PHP classique --}}
    @php
        $status = $pickup->status ?? 'draft';
        $config = $statusConfig[$status] ?? $statusConfig['draft'];
    @endphp
    
    <span class="badge {{ $config['class'] }}">
        <i class="{{ $config['icon'] }} me-1"></i>
        {{ $config['label'] }}
    </span>
@endif