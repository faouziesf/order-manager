{{-- resources/views/admin/orders/partials/duplicate-indicator.blade.php --}}
@if($order->is_duplicate || $order->priority === 'doublons')
<div class="duplicate-indicator-component" data-order-id="{{ $order->id }}" data-phone="{{ $order->customer_phone }}">
    @if($order->is_duplicate && !$order->reviewed_for_duplicates)
        {{-- Doublon non examiné - Alerte rouge --}}
        <div class="duplicate-badge duplicate-unreviewed" title="Doublon non examiné - Action requise">
            <i class="fas fa-exclamation-triangle"></i>
            <span>Doublon non examiné</span>
        </div>
        <div class="duplicate-actions">
            <button type="button" class="btn-duplicate-action btn-view" onclick="viewDuplicateDetails('{{ $order->customer_phone }}')" title="Voir les détails des doublons">
                <i class="fas fa-eye"></i>
            </button>
            <button type="button" class="btn-duplicate-action btn-merge" onclick="quickMerge('{{ $order->customer_phone }}')" title="Fusion rapide">
                <i class="fas fa-compress-arrows-alt"></i>
            </button>
        </div>
    @elseif($order->is_duplicate && $order->reviewed_for_duplicates)
        {{-- Doublon examiné - Information bleue --}}
        <div class="duplicate-badge duplicate-reviewed" title="Doublon examiné">
            <i class="fas fa-check-circle"></i>
            <span>Doublon examiné</span>
        </div>
    @elseif($order->priority === 'doublons')
        {{-- Priorité doublons mais pas encore marqué formellement --}}
        <div class="duplicate-badge duplicate-priority" title="Priorité doublons - Vérification nécessaire">
            <i class="fas fa-copy"></i>
            <span>À vérifier</span>
        </div>
        <div class="duplicate-actions">
            <button type="button" class="btn-duplicate-action btn-check" onclick="checkDuplicatesForPhone('{{ $order->customer_phone }}')" title="Vérifier les doublons">
                <i class="fas fa-search"></i>
            </button>
        </div>
    @endif
</div>

<style>
.duplicate-indicator-component {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin: 0.25rem 0;
}

.duplicate-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.375rem;
    padding: 0.25rem 0.625rem;
    border-radius: 6px;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.025em;
    white-space: nowrap;
    border: 1px solid;
}

.duplicate-badge.duplicate-unreviewed {
    background: rgba(239, 68, 68, 0.1);
    color: #dc2626;
    border-color: rgba(239, 68, 68, 0.3);
    animation: pulse-red 2s infinite;
}

.duplicate-badge.duplicate-reviewed {
    background: rgba(59, 130, 246, 0.1);
    color: #2563eb;
    border-color: rgba(59, 130, 246, 0.3);
}

.duplicate-badge.duplicate-priority {
    background: rgba(212, 161, 71, 0.1);
    color: #d4a147;
    border-color: rgba(212, 161, 71, 0.3);
}

.duplicate-actions {
    display: flex;
    gap: 0.25rem;
}

.btn-duplicate-action {
    width: 24px;
    height: 24px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.75rem;
    transition: all 0.2s ease;
    opacity: 0.8;
}

.btn-duplicate-action:hover {
    opacity: 1;
    transform: scale(1.1);
}

.btn-duplicate-action.btn-view {
    background: #3b82f6;
    color: white;
}

.btn-duplicate-action.btn-merge {
    background: #10b981;
    color: white;
}

.btn-duplicate-action.btn-check {
    background: #f59e0b;
    color: white;
}

@keyframes pulse-red {
    0%, 100% {
        box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.4);
    }
    50% {
        box-shadow: 0 0 0 4px rgba(239, 68, 68, 0.1);
    }
}

/* Version compacte pour les listes denses */
.duplicate-indicator-component.compact .duplicate-badge {
    padding: 0.125rem 0.375rem;
    font-size: 0.625rem;
}

.duplicate-indicator-component.compact .duplicate-badge span {
    display: none;
}

.duplicate-indicator-component.compact .btn-duplicate-action {
    width: 20px;
    height: 20px;
    font-size: 0.625rem;
}

/* Version inline pour les tableaux */
.duplicate-indicator-component.inline {
    flex-direction: column;
    align-items: flex-start;
    gap: 0.25rem;
}

/* Responsive */
@media (max-width: 768px) {
    .duplicate-badge span {
        display: none;
    }
    
    .duplicate-actions {
        flex-direction: column;
        gap: 0.125rem;
    }
}
</style>

<script>
// Fonctions globales pour les actions sur les doublons
window.viewDuplicateDetails = function(phone) {
    window.open(`/admin/duplicates/detail/${encodeURIComponent(phone)}`, '_blank');
};

window.quickMerge = function(phone) {
    if (!confirm('Fusionner rapidement toutes les commandes doubles de ce client ?')) {
        return;
    }
    
    $.post('/admin/duplicates/merge', {
        customer_phone: phone,
        note: 'Fusion rapide depuis la liste',
        _token: $('meta[name="csrf-token"]').attr('content')
    })
    .done(function(response) {
        if (response.success) {
            showNotification('success', 'Fusion réalisée avec succès !');
            // Recharger la page ou mettre à jour l'affichage
            if (typeof refreshOrdersList === 'function') {
                refreshOrdersList();
            } else {
                location.reload();
            }
        } else {
            showNotification('error', response.message || 'Erreur lors de la fusion');
        }
    })
    .fail(function() {
        showNotification('error', 'Erreur lors de la fusion');
    });
};

window.checkDuplicatesForPhone = function(phone) {
    $.post('/admin/duplicates/check', {
        customer_phone: phone,
        _token: $('meta[name="csrf-token"]').attr('content')
    })
    .done(function(response) {
        if (response.success) {
            showNotification('success', response.message);
            if (typeof refreshOrdersList === 'function') {
                refreshOrdersList();
            } else {
                location.reload();
            }
        } else {
            showNotification('error', response.message || 'Erreur lors de la vérification');
        }
    })
    .fail(function() {
        showNotification('error', 'Erreur lors de la vérification');
    });
};

// Fonction utilitaire pour afficher des notifications
window.showNotification = function(type, message) {
    // Créer une notification toast simple
    const notification = $(`
        <div class="notification toast-${type}" style="
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
            padding: 1rem;
            border-radius: 8px;
            color: white;
            font-weight: 600;
            min-width: 300px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            background: ${type === 'success' ? '#10b981' : '#ef4444'};
        ">
            <i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'}" style="margin-right: 0.5rem;"></i>
            ${message}
        </div>
    `);
    
    $('body').append(notification);
    
    // Auto-masquer après 4 secondes
    setTimeout(() => {
        notification.fadeOut(() => notification.remove());
    }, 4000);
};
</script>
@endif