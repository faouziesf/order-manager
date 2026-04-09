@props([
    'status',
    'label' => null,
    'map' => 'order',
])

@php
    $maps = [
        'order' => [
            'nouveau'    => ['color' => '#3b82f6', 'bg' => '#eff6ff', 'label' => 'Nouveau'],
            'pending'    => ['color' => '#f59e0b', 'bg' => '#fffbeb', 'label' => 'En attente'],
            'confirmed'  => ['color' => '#10b981', 'bg' => '#f0fdf4', 'label' => 'Confirmée'],
            'cancelled'  => ['color' => '#ef4444', 'bg' => '#fef2f2', 'label' => 'Annulée'],
            'delivered'  => ['color' => '#6d28d9', 'bg' => '#faf5ff', 'label' => 'Livrée'],
            'returned'   => ['color' => '#f97316', 'bg' => '#fff7ed', 'label' => 'Retournée'],
            'suspended'  => ['color' => '#64748b', 'bg' => '#f8fafc', 'label' => 'Suspendue'],
            'scheduled'  => ['color' => '#06b6d4', 'bg' => '#ecfeff', 'label' => 'Planifiée'],
            'in_progress'=> ['color' => '#0ea5e9', 'bg' => '#f0f9ff', 'label' => 'En cours'],
            'at_kolixy'  => ['color' => '#6d28d9', 'bg' => '#faf5ff', 'label' => 'Chez Kolixy'],
            'shipped'    => ['color' => '#0284c7', 'bg' => '#f0f9ff', 'label' => 'Expédiée'],
        ],
        'assignment' => [
            'assigned'    => ['color' => '#3b82f6', 'bg' => '#eff6ff', 'label' => 'Assignée'],
            'in_progress' => ['color' => '#f59e0b', 'bg' => '#fffbeb', 'label' => 'En cours'],
            'confirmed'   => ['color' => '#10b981', 'bg' => '#f0fdf4', 'label' => 'Confirmée'],
            'cancelled'   => ['color' => '#ef4444', 'bg' => '#fef2f2', 'label' => 'Annulée'],
            'scheduled'   => ['color' => '#06b6d4', 'bg' => '#ecfeff', 'label' => 'Planifiée'],
            'completed'   => ['color' => '#059669', 'bg' => '#ecfdf5', 'label' => 'Terminée'],
        ],
        'kolixy' => [
            'sent'       => ['color' => '#3b82f6', 'bg' => '#eff6ff', 'label' => 'Envoyée'],
            'picked_up'  => ['color' => '#f59e0b', 'bg' => '#fffbeb', 'label' => 'Ramassée'],
            'in_transit' => ['color' => '#8b5cf6', 'bg' => '#faf5ff', 'label' => 'En transit'],
            'delivered'  => ['color' => '#10b981', 'bg' => '#f0fdf4', 'label' => 'Livrée'],
            'returned'   => ['color' => '#ef4444', 'bg' => '#fef2f2', 'label' => 'Retournée'],
            'failed'     => ['color' => '#dc2626', 'bg' => '#fef2f2', 'label' => 'Échouée'],
        ],
    ];

    $statusMap = $maps[$map] ?? $maps['order'];
    $info = $statusMap[$status] ?? ['color' => '#64748b', 'bg' => '#f8fafc', 'label' => ucfirst(str_replace('_', ' ', $status))];
    $displayLabel = $label ?? $info['label'];
@endphp

<span style="display:inline-flex;align-items:center;gap:.3rem;padding:.2rem .65rem;border-radius:20px;font-size:.75rem;font-weight:600;color:{{ $info['color'] }};background:{{ $info['bg'] }};white-space:nowrap;"
    {{ $attributes }}>
    <i class="fas fa-circle" style="font-size:.35rem;"></i>
    {{ $displayLabel }}
</span>
