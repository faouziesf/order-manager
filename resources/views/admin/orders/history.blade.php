<div class="timeline">
    @if($history->count() > 0)
        <div class="timeline-container">
            @foreach($history as $entry)
                <div class="timeline-item">
                    <div class="timeline-item-content">
                        <span class="timeline-item-date">{{ $entry->created_at->format('d/m/Y H:i') }}</span>
                        <h6 class="timeline-item-title">
                            @switch($entry->action)
                                @case('création')
                                    <span class="text-primary"><i class="fas fa-plus-circle"></i> Création</span>
                                    @break
                                @case('modification')
                                    <span class="text-info"><i class="fas fa-edit"></i> Modification</span>
                                    @break
                                @case('confirmation')
                                    <span class="text-success"><i class="fas fa-check-circle"></i> Confirmation</span>
                                    @break
                                @case('annulation')
                                    <span class="text-danger"><i class="fas fa-times-circle"></i> Annulation</span>
                                    @break
                                @case('datation')
                                    <span class="text-warning"><i class="fas fa-calendar"></i> Datation</span>
                                    @break
                                @case('tentative')
                                    <span class="text-info"><i class="fas fa-phone"></i> Tentative d'appel</span>
                                    @break
                                @case('livraison')
                                    <span class="text-success"><i class="fas fa-truck"></i> Livraison</span>
                                    @break
                                @default
                                    <span class="text-secondary"><i class="fas fa-history"></i> Action</span>
                            @endswitch
                        </h6>
                        <p class="timeline-item-user">
                            Par: <strong>{{ $entry->getUserName() }}</strong>
                        </p>
                        
                        @if($entry->status_before !== $entry->status_after)
                            <p class="timeline-item-status">
                                Statut: 
                                <span class="badge status-{{ $entry->status_before }}">{{ ucfirst($entry->status_before) }}</span>
                                →
                                <span class="badge status-{{ $entry->status_after }}">{{ ucfirst($entry->status_after) }}</span>
                            </p>
                        @endif
                        
                        @if($entry->notes)
                            <div class="timeline-item-notes">
                                <strong>Notes:</strong>
                                <p>{{ $entry->notes }}</p>
                            </div>
                        @endif
                        
                        @if($entry->changes)
                            <div class="timeline-item-changes">
                                <strong>Modifications:</strong>
                                <ul>
                                @foreach(json_decode($entry->changes, true) as $field => $change)
                                    <li>
                                        {{ $field }}: {{ $change['old'] ?? 'Non défini' }} → {{ $change['new'] ?? 'Non défini' }}
                                    </li>
                                @endforeach
                                </ul>
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="text-center text-muted">
            <p>Aucun historique disponible pour cette commande.</p>
        </div>
    @endif
</div>

<style>
    .timeline {
        margin: 20px 0;
        padding: 0;
    }
    
    .timeline-container {
        position: relative;
        padding-left: 20px;
    }
    
    .timeline-container::before {
        content: '';
        position: absolute;
        left: 0;
        top: 0;
        bottom: 0;
        width: 3px;
        background-color: #e0e0e0;
    }
    
    .timeline-item {
        position: relative;
        margin-bottom: 20px;
        padding-left: 25px;
    }
    
    .timeline-item::before {
        content: '';
        position: absolute;
        left: -10px;
        top: 0;
        width: 20px;
        height: 20px;
        border-radius: 50%;
        background-color: #4e73df;
        border: 3px solid white;
        box-shadow: 0 0 0 1px #4e73df;
    }
    
    .timeline-item-content {
        position: relative;
        padding: 15px;
        border-radius: 5px;
        background-color: #f8f9fc;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    }
    
    .timeline-item-date {
        display: block;
        font-size: 0.8rem;
        color: #6c757d;
        margin-bottom: 5px;
    }
    
    .timeline-item-title {
        margin-bottom: 10px;
        font-weight: 600;
    }
    
    .timeline-item-user {
        font-size: 0.9rem;
        margin-bottom: 5px;
    }
    
    .timeline-item-status {
        font-size: 0.9rem;
        margin-bottom: 5px;
    }
    
    .timeline-item-notes {
        margin-top: 10px;
    }
    
    .timeline-item-notes p {
        margin: 5px 0 0;
        font-size: 0.9rem;
        padding: 8px;
        background-color: #fff;
        border-radius: 3px;
        border-left: 3px solid #4e73df;
    }
    
    .timeline-item-changes {
        margin-top: 10px;
        font-size: 0.9rem;
    }
    
    .timeline-item-changes ul {
        margin: 5px 0 0;
        padding-left: 20px;
    }
    
    .status-nouvelle { background-color: #3498db; color: white; }
    .status-confirmée { background-color: #2ecc71; color: white; }
    .status-annulée { background-color: #e74c3c; color: white; }
    .status-datée { background-color: #f39c12; color: white; }
    .status-en_route { background-color: #9b59b6; color: white; }
    .status-livrée { background-color: #27ae60; color: white; }
</style>