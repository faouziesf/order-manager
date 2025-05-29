<div class="order-history">
    <div class="mb-3">
        <h6 class="text-primary mb-2">
            <i class="fas fa-info-circle me-2"></i>Commande #{{ str_pad($order->id, 6, '0', STR_PAD_LEFT) }}
        </h6>
        <div class="row text-sm">
            <div class="col-md-6">
                <strong>Client:</strong> {{ $order->customer_name ?: 'Non renseigné' }}<br>
                <strong>Téléphone:</strong> {{ $order->customer_phone }}
            </div>
            <div class="col-md-6">
                <strong>Statut actuel:</strong> 
                <span class="badge bg-{{ $order->status === 'nouvelle' ? 'secondary' : ($order->status === 'confirmée' ? 'success' : ($order->status === 'annulée' ? 'danger' : 'warning')) }}">
                    {{ ucfirst($order->status) }}
                </span><br>
                <strong>Tentatives:</strong> {{ $order->attempts_count ?? 0 }}
            </div>
        </div>
    </div>

    @if($history->isEmpty())
        <div class="text-center py-4">
            <i class="fas fa-history fa-3x text-muted mb-3"></i>
            <p class="text-muted">Aucun historique disponible pour cette commande.</p>
        </div>
    @else
        <div class="timeline">
            @foreach($history as $entry)
                <div class="timeline-item">
                    <div class="timeline-marker">
                        <i class="fas {{ 
                            $entry->action === 'création' ? 'fa-plus' : (
                            $entry->action === 'tentative' ? 'fa-phone' : (
                            $entry->action === 'confirmation' ? 'fa-check' : (
                            $entry->action === 'annulation' ? 'fa-times' : (
                            $entry->action === 'datation' ? 'fa-calendar' : (
                            $entry->action === 'livraison' ? 'fa-gift' : (
                            $entry->action === 'assignation' ? 'fa-user-plus' : (
                            $entry->action === 'désassignation' ? 'fa-user-minus' : 'fa-edit')))))))
                        }}"></i>
                    </div>
                    <div class="timeline-content">
                        <div class="timeline-header">
                            <h6 class="timeline-title mb-1">
                                {{ ucfirst($entry->action) }}
                                @if($entry->status_after && $entry->status_after !== $entry->status_before)
                                    <span class="text-muted">
                                        ({{ $entry->status_before ?? 'Aucun' }} → {{ $entry->status_after }})
                                    </span>
                                @endif
                            </h6>
                            <small class="text-muted">
                                <i class="fas fa-clock me-1"></i>
                                {{ $entry->created_at->format('d/m/Y à H:i') }}
                                @if($entry->user_type && ($entry->admin || $entry->manager || $entry->employee))
                                    • Par {{ $entry->getUserName() }}
                                @endif
                            </small>
                        </div>
                        
                        @if($entry->notes)
                            <div class="timeline-notes mt-2">
                                <div class="alert alert-light py-2 px-3 mb-0">
                                    <i class="fas fa-comment me-2 text-primary"></i>
                                    {{ $entry->notes }}
                                </div>
                            </div>
                        @endif

                        @if($entry->changes)
                            @php $changes = json_decode($entry->changes, true) @endphp
                            @if($changes && is_array($changes))
                                <div class="timeline-changes mt-2">
                                    <small class="text-muted">Modifications:</small>
                                    <ul class="list-unstyled mt-1 mb-0">
                                        @foreach($changes as $field => $change)
                                            @if(is_array($change) && isset($change['old'], $change['new']))
                                                <li class="small">
                                                    <strong>{{ ucfirst(str_replace('_', ' ', $field)) }}:</strong>
                                                    <span class="text-danger">{{ $change['old'] ?: 'Vide' }}</span> 
                                                    →
                                                    <span class="text-success">{{ $change['new'] ?: 'Vide' }}</span>
                                                </li>
                                            @endif
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>

<style>
.timeline {
    position: relative;
    padding-Left: 30px;
}

.timeline::before {
    content: '';
    position: absolute;
    left: 15px;
    top: 0;
    bottom: 0;
    width: 2px;
    background: linear-gradient(to bottom, #e5e7eb, #d1d5db);
}

.timeline-item {
    position: relative;
    margin-bottom: 24px;
}

.timeline-marker {
    position: absolute;
    left: -22px;
    top: 0;
    width: 30px;
    height: 30px;
    border-radius: 50%;
    background: white;
    border: 3px solid #6366f1;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #6366f1;
    font-size: 0.8rem;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.timeline-content {
    background: white;
    border-radius: 8px;
    padding: 16px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    border-left: 3px solid #e5e7eb;
}

.timeline-title {
    color: #374151;
    font-weight: 600;
}

.timeline-notes .alert {
    border-left: 3px solid #6366f1;
}

.timeline-changes {
    font-size: 0.85rem;
}

.timeline-changes ul li {
    padding: 2px 0;
    border-bottom: 1px solid #f3f4f6;
}

.timeline-changes ul li:last-child {
    border-bottom: none;
}

/* Couleurs spécifiques selon l'action */
.timeline-item:has(.fa-plus) .timeline-marker { border-color: #10b981; color: #10b981; }
.timeline-item:has(.fa-phone) .timeline-marker { border-color: #f59e0b; color: #f59e0b; }
.timeline-item:has(.fa-check) .timeline-marker { border-color: #10b981; color: #10b981; }
.timeline-item:has(.fa-times) .timeline-marker { border-color: #ef4444; color: #ef4444; }
.timeline-item:has(.fa-calendar) .timeline-marker { border-color: #8b5cf6; color: #8b5cf6; }
.timeline-item:has(.fa-gift) .timeline-marker { border-color: #06b6d4; color: #06b6d4; }
.timeline-item:has(.fa-user-plus) .timeline-marker { border-color: #10b981; color: #10b981; }
.timeline-item:has(.fa-user-minus) .timeline-marker { border-color: #ef4444; color: #ef4444; }
</style>