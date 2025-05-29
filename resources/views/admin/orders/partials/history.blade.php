{{-- resources/views/admin/orders/partials/history.blade.php --}}
<div class="history-timeline">
    @forelse($history as $item)
        <div class="history-item animate__animated animate__fadeInUp">
            <div class="history-icon">
                @switch($item->action)
                    @case('création')
                        <i class="fas fa-plus-circle text-success"></i>
                        @break
                    @case('modification')
                        <i class="fas fa-edit text-info"></i>
                        @break
                    @case('confirmation')
                        <i class="fas fa-check-circle text-success"></i>
                        @break
                    @case('annulation')
                        <i class="fas fa-times-circle text-danger"></i>
                        @break
                    @case('datation')
                        <i class="fas fa-calendar-alt text-warning"></i>
                        @break
                    @case('tentative')
                        <i class="fas fa-phone text-primary"></i>
                        @break
                    @case('livraison')
                        <i class="fas fa-truck text-success"></i>
                        @break
                    @case('assignation')
                        <i class="fas fa-user-plus text-info"></i>
                        @break
                    @case('désassignation')
                        <i class="fas fa-user-minus text-warning"></i>
                        @break
                    @case('en_route')
                        <i class="fas fa-shipping-fast text-primary"></i>
                        @break
                    @case('suspension')
                        <i class="fas fa-pause-circle text-warning"></i>
                        @break
                    @case('réactivation')
                        <i class="fas fa-play-circle text-success"></i>
                        @break
                    @default
                        <i class="fas fa-circle text-muted"></i>
                @endswitch
            </div>
            
            <div class="history-content">
                <div class="history-header">
                    <h6 class="history-action">{{ ucfirst($item->action) }}</h6>
                    <small class="history-date">
                        <i class="fas fa-clock me-1"></i>
                        {{ \Carbon\Carbon::parse($item->created_at)->diffForHumans() }}
                        <span class="text-muted">({{ $item->created_at->format('d/m/Y H:i:s') }})</span>
                    </small>
                </div>
                
                @if($item->notes)
                    <div class="history-notes">
                        <i class="fas fa-quote-left me-2 text-muted"></i>
                        {{ $item->notes }}
                    </div>
                @endif
                
                @if($item->changes && is_array(json_decode($item->changes, true)))
                    <div class="history-changes">
                        <div class="changes-header">
                            <i class="fas fa-exchange-alt me-2"></i>
                            <strong>Modifications :</strong>
                        </div>
                        <div class="changes-list">
                            @foreach(json_decode($item->changes, true) as $field => $change)
                                <div class="change-item">
                                    <span class="change-field">{{ ucfirst(str_replace('_', ' ', $field)) }} :</span>
                                    <span class="change-values">
                                        <span class="old-value">{{ $change['old'] ?? 'Non défini' }}</span>
                                        <i class="fas fa-arrow-right mx-2"></i>
                                        <span class="new-value">{{ $change['new'] ?? 'Non défini' }}</span>
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
                
                @if($item->status_before && $item->status_after && $item->status_before !== $item->status_after)
                    <div class="status-change">
                        <i class="fas fa-flag me-2"></i>
                        <span class="status-badge status-{{ $item->status_before }}">{{ ucfirst($item->status_before) }}</span>
                        <i class="fas fa-arrow-right mx-2"></i>
                        <span class="status-badge status-{{ $item->status_after }}">{{ ucfirst($item->status_after) }}</span>
                    </div>
                @endif
                
                <div class="history-meta">
                    @if($item->user_type && $item->user_id)
                        <div class="history-user">
                            <i class="fas fa-user me-1"></i>
                            Par : <strong>{{ $item->getUserName() }}</strong>
                            <span class="user-type">({{ $item->user_type }})</span>
                        </div>
                    @else
                        <div class="history-user">
                            <i class="fas fa-robot me-1"></i>
                            Par : <strong>Système</strong>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @empty
        <div class="empty-history">
            <div class="empty-icon">
                <i class="fas fa-history fa-3x text-muted"></i>
            </div>
            <h5 class="empty-title">Aucun historique</h5>
            <p class="empty-description">Cette commande n'a pas encore d'historique d'actions.</p>
        </div>
    @endforelse
</div>

<style>
    .history-timeline {
        position: relative;
        padding: 20px 0;
    }
    
    .history-timeline::before {
        content: '';
        position: absolute;
        left: 30px;
        top: 0;
        bottom: 0;
        width: 2px;
        background: linear-gradient(to bottom, #e5e7eb, #d1d5db);
    }
    
    .history-item {
        position: relative;
        margin-bottom: 24px;
        padding-left: 70px;
        animation-delay: calc(var(--i) * 0.1s);
    }
    
    .history-icon {
        position: absolute;
        left: 20px;
        top: 0;
        width: 40px;
        height: 40px;
        background: white;
        border: 3px solid #e5e7eb;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 16px;
        z-index: 1;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }
    
    .history-content {
        background: white;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        padding: 20px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        transition: all 0.3s ease;
        position: relative;
    }
    
    .history-content:hover {
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
        transform: translateY(-2px);
    }
    
    .history-content::before {
        content: '';
        position: absolute;
        left: -8px;
        top: 20px;
        width: 0;
        height: 0;
        border-top: 8px solid transparent;
        border-bottom: 8px solid transparent;
        border-right: 8px solid white;
    }
    
    .history-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 12px;
        flex-wrap: wrap;
        gap: 8px;
    }
    
    .history-action {
        font-size: 16px;
        font-weight: 600;
        color: #374151;
        margin: 0;
        text-transform: capitalize;
    }
    
    .history-date {
        color: #6b7280;
        font-size: 12px;
        white-space: nowrap;
    }
    
    .history-notes {
        background: #f8fafc;
        border-left: 4px solid #6366f1;
        padding: 12px 16px;
        margin: 12px 0;
        border-radius: 0 8px 8px 0;
        font-style: italic;
        color: #4b5563;
        line-height: 1.5;
    }
    
    .history-changes {
        margin: 12px 0;
        padding: 12px;
        background: #fef3c7;
        border-radius: 8px;
        border: 1px solid #f59e0b;
    }
    
    .changes-header {
        display: flex;
        align-items: center;
        font-weight: 600;
        color: #92400e;
        margin-bottom: 8px;
        font-size: 14px;
    }
    
    .changes-list {
        font-size: 13px;
    }
    
    .change-item {
        margin-bottom: 6px;
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 8px;
    }
    
    .change-field {
        font-weight: 600;
        color: #92400e;
        min-width: 120px;
    }
    
    .change-values {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
    }
    
    .old-value {
        background: #fee2e2;
        color: #dc2626;
        padding: 2px 8px;
        border-radius: 4px;
        font-family: 'JetBrains Mono', monospace;
        font-size: 12px;
    }
    
    .new-value {
        background: #dcfce7;
        color: #16a34a;
        padding: 2px 8px;
        border-radius: 4px;
        font-family: 'JetBrains Mono', monospace;
        font-size: 12px;
    }
    
    .status-change {
        display: flex;
        align-items: center;
        gap: 8px;
        margin: 12px 0;
        padding: 8px 12px;
        background: #f3f4f6;
        border-radius: 8px;
        flex-wrap: wrap;
    }
    
    .status-badge {
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .status-nouvelle { background: rgba(107, 114, 128, 0.2); color: #6b7280; }
    .status-confirmée { background: rgba(16, 185, 129, 0.2); color: #10b981; }
    .status-annulée { background: rgba(239, 68, 68, 0.2); color: #ef4444; }
    .status-datée { background: rgba(245, 158, 11, 0.2); color: #f59e0b; }
    .status-en_route { background: rgba(6, 182, 212, 0.2); color: #06b6d4; }
    .status-livrée { background: rgba(139, 92, 246, 0.2); color: #8b5cf6; }
    
    .history-meta {
        margin-top: 12px;
        padding-top: 12px;
        border-top: 1px solid #f3f4f6;
    }
    
    .history-user {
        font-size: 12px;
        color: #6b7280;
        display: flex;
        align-items: center;
        gap: 4px;
    }
    
    .user-type {
        background: #e5e7eb;
        color: #6b7280;
        padding: 2px 6px;
        border-radius: 4px;
        font-size: 10px;
        text-transform: uppercase;
        font-weight: 600;
    }
    
    .empty-history {
        text-align: center;
        padding: 60px 20px;
        color: #6b7280;
    }
    
    .empty-icon {
        margin-bottom: 20px;
        opacity: 0.5;
    }
    
    .empty-title {
        font-size: 20px;
        font-weight: 600;
        margin-bottom: 8px;
        color: #374151;
    }
    
    .empty-description {
        font-size: 14px;
        margin: 0;
        max-width: 300px;
        margin: 0 auto;
        line-height: 1.5;
    }
    
    /* Responsive */
    @media (max-width: 768px) {
        .history-timeline::before {
            left: 20px;
        }
        
        .history-item {
            padding-left: 50px;
        }
        
        .history-icon {
            left: 10px;
            width: 30px;
            height: 30px;
            font-size: 14px;
        }
        
        .history-content::before {
            left: -6px;
            border-right-width: 6px;
            border-top-width: 6px;
            border-bottom-width: 6px;
        }
        
        .history-header {
            flex-direction: column;
            align-items: flex-start;
        }
        
        .change-item {
            flex-direction: column;
            align-items: flex-start;
        }
        
        .change-field {
            min-width: auto;
        }
        
        .status-change {
            flex-direction: column;
            align-items: flex-start;
        }
    }
</style>