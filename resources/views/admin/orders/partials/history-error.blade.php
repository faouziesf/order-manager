{{-- resources/views/admin/orders/partials/history-error.blade.php --}}
<div class="history-error-container">
    <div class="error-icon">
        <i class="fas fa-exclamation-triangle fa-3x text-warning"></i>
    </div>
    <div class="error-content">
        <h5 class="error-title">Erreur de chargement</h5>
        <p class="error-message">
            Impossible de charger l'historique de cette commande pour le moment.
        </p>
        <div class="error-actions">
            <button type="button" class="btn btn-outline-primary btn-sm" onclick="location.reload()">
                <i class="fas fa-sync-alt me-2"></i>Actualiser la page
            </button>
            <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">
                <i class="fas fa-times me-2"></i>Fermer
            </button>
        </div>
    </div>
</div>

<style>
    .history-error-container {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 60px 40px;
        text-align: center;
        min-height: 300px;
    }
    
    .error-icon {
        margin-bottom: 24px;
        opacity: 0.7;
    }
    
    .error-title {
        font-size: 24px;
        font-weight: 600;
        color: #374151;
        margin-bottom: 12px;
    }
    
    .error-message {
        font-size: 16px;
        color: #6b7280;
        line-height: 1.6;
        margin-bottom: 24px;
        max-width: 400px;
    }
    
    .error-actions {
        display: flex;
        gap: 12px;
        justify-content: center;
        flex-wrap: wrap;
    }
    
    @media (max-width: 480px) {
        .history-error-container {
            padding: 40px 20px;
        }
        
        .error-actions {
            flex-direction: column;
            width: 100%;
        }
        
        .error-actions .btn {
            width: 100%;
        }
    }
</style>