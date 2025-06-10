@extends('layouts.super-admin')

@section('title', 'Notifications')

@section('breadcrumb')
    <ol class="breadcrumb breadcrumb-custom">
        <li class="breadcrumb-item"><a href="{{ route('super-admin.dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item active">Notifications</li>
    </ol>
@endsection

@section('page-header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="page-title">Centre de Notifications</h1>
            <p class="page-subtitle">Gérez toutes vos notifications système et administrateurs</p>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-outline-secondary" onclick="markAllAsRead()">
                <i class="fas fa-check-double me-2"></i>Tout marquer comme lu
            </button>
            <button class="btn btn-primary" onclick="refreshNotifications()">
                <i class="fas fa-sync-alt me-2"></i>Actualiser
            </button>
        </div>
    </div>
@endsection

@section('css')
<style>
    .notifications-container {
        background: #f8fafc;
        min-height: 100vh;
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }

    .stat-card {
        background: white;
        border-radius: 12px;
        padding: 25px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        transition: all 0.3s ease;
        border-left: 4px solid #e5e7eb;
    }

    .stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
    }

    .stat-card.total { border-left-color: #3b82f6; }
    .stat-card.unread { border-left-color: #ef4444; }
    .stat-card.important { border-left-color: #f59e0b; }
    .stat-card.today { border-left-color: #10b981; }

    .stat-icon {
        width: 50px;
        height: 50px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        margin-bottom: 15px;
    }

    .stat-card.total .stat-icon { background: #dbeafe; color: #3b82f6; }
    .stat-card.unread .stat-icon { background: #fee2e2; color: #ef4444; }
    .stat-card.important .stat-icon { background: #fef3c7; color: #f59e0b; }
    .stat-card.today .stat-icon { background: #d1fae5; color: #10b981; }

    .stat-number {
        font-size: 28px;
        font-weight: 700;
        color: #111827;
        line-height: 1;
    }

    .stat-label {
        color: #6b7280;
        font-size: 14px;
        font-weight: 500;
        margin-top: 5px;
    }

    .notifications-panel {
        background: white;
        border-radius: 15px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        overflow: hidden;
    }

    .panel-header {
        background: linear-gradient(135deg, #4f46e5 0%, #3730a3 100%);
        color: white;
        padding: 25px 30px;
    }

    .panel-title {
        font-size: 20px;
        font-weight: 600;
        margin: 0;
    }

    .filters-section {
        padding: 25px 30px;
        border-bottom: 1px solid #e5e7eb;
        background: #f9fafb;
    }

    .filters-grid {
        display: flex;
        gap: 15px;
        align-items: center;
        flex-wrap: wrap;
    }

    .filter-btn {
        padding: 8px 16px;
        border: 2px solid #e5e7eb;
        background: white;
        border-radius: 25px;
        color: #6b7280;
        text-decoration: none;
        font-size: 14px;
        font-weight: 500;
        transition: all 0.3s ease;
        cursor: pointer;
    }

    .filter-btn:hover,
    .filter-btn.active {
        border-color: #4f46e5;
        background: #4f46e5;
        color: white;
        text-decoration: none;
    }

    .notifications-list {
        max-height: 600px;
        overflow-y: auto;
    }

    .notification-item {
        display: flex;
        align-items: flex-start;
        padding: 20px 30px;
        border-bottom: 1px solid #f3f4f6;
        transition: all 0.3s ease;
        cursor: pointer;
        position: relative;
    }

    .notification-item:hover {
        background: #f9fafb;
    }

    .notification-item.unread {
        background: #fef7ff;
        border-left: 4px solid #4f46e5;
    }

    .notification-item.unread::before {
        content: '';
        position: absolute;
        top: 25px;
        left: 10px;
        width: 8px;
        height: 8px;
        background: #4f46e5;
        border-radius: 50%;
    }

    .notification-icon {
        width: 45px;
        height: 45px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 15px;
        font-size: 18px;
        flex-shrink: 0;
    }

    .icon-admin { background: #dbeafe; color: #3b82f6; }
    .icon-system { background: #f3f4f6; color: #6b7280; }
    .icon-security { background: #fee2e2; color: #ef4444; }
    .icon-backup { background: #d1fae5; color: #10b981; }
    .icon-warning { background: #fef3c7; color: #f59e0b; }

    .notification-content {
        flex: 1;
        min-width: 0;
    }

    .notification-header {
        display: flex;
        justify-content: between;
        align-items: flex-start;
        margin-bottom: 8px;
    }

    .notification-title {
        font-size: 16px;
        font-weight: 600;
        color: #111827;
        margin: 0;
        line-height: 1.3;
    }

    .notification-time {
        font-size: 12px;
        color: #9ca3af;
        margin-left: auto;
        flex-shrink: 0;
    }

    .notification-message {
        color: #6b7280;
        font-size: 14px;
        line-height: 1.4;
        margin-bottom: 8px;
    }

    .notification-meta {
        display: flex;
        gap: 10px;
        align-items: center;
    }

    .priority-badge {
        padding: 3px 8px;
        border-radius: 12px;
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
    }

    .priority-high { background: #fee2e2; color: #991b1b; }
    .priority-medium { background: #fef3c7; color: #92400e; }
    .priority-low { background: #dbeafe; color: #1e40af; }

    .notification-actions {
        margin-left: 15px;
        opacity: 0;
        transition: opacity 0.3s ease;
    }

    .notification-item:hover .notification-actions {
        opacity: 1;
    }

    .action-btn {
        width: 32px;
        height: 32px;
        border: none;
        background: #f3f4f6;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 14px;
        color: #6b7280;
        cursor: pointer;
        transition: all 0.3s ease;
        margin-bottom: 5px;
    }

    .action-btn:hover {
        background: #e5e7eb;
        color: #374151;
    }

    .action-btn.delete:hover {
        background: #fee2e2;
        color: #dc2626;
    }

    .empty-state {
        text-align: center;
        padding: 60px 30px;
        color: #6b7280;
    }

    .empty-icon {
        font-size: 64px;
        color: #d1d5db;
        margin-bottom: 20px;
    }

    .loading-spinner {
        display: none;
        text-align: center;
        padding: 40px;
    }

    .spinner {
        width: 40px;
        height: 40px;
        border: 4px solid #f3f4f6;
        border-top: 4px solid #4f46e5;
        border-radius: 50%;
        animation: spin 1s linear infinite;
        margin: 0 auto 15px;
    }

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    .toast-container {
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 1000;
    }

    .toast-notification {
        background: white;
        border-radius: 12px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
        padding: 20px;
        margin-bottom: 10px;
        border-left: 4px solid #4f46e5;
        min-width: 350px;
        animation: slideIn 0.3s ease-out;
    }

    @keyframes slideIn {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }

    .pagination-wrapper {
        padding: 25px 30px;
        background: #f9fafb;
        border-top: 1px solid #e5e7eb;
    }

    @media (max-width: 768px) {
        .stats-grid {
            grid-template-columns: repeat(2, 1fr);
        }
        
        .filters-grid {
            flex-direction: column;
            align-items: stretch;
        }
        
        .filter-btn {
            text-align: center;
        }
        
        .notification-item {
            flex-direction: column;
            align-items: stretch;
        }
        
        .notification-actions {
            opacity: 1;
            margin-left: 0;
            margin-top: 15px;
            display: flex;
            gap: 10px;
        }
    }
</style>
@endsection

@section('content')
    <div class="notifications-container">
        <!-- Statistiques -->
        <div class="stats-grid">
            <div class="stat-card total">
                <div class="stat-icon">
                    <i class="fas fa-bell"></i>
                </div>
                <div class="stat-number" id="totalCount">{{ $stats['total'] ?? 0 }}</div>
                <div class="stat-label">Total des notifications</div>
            </div>
            
            <div class="stat-card unread">
                <div class="stat-icon">
                    <i class="fas fa-envelope"></i>
                </div>
                <div class="stat-number" id="unreadCount">{{ $stats['unread'] ?? 0 }}</div>
                <div class="stat-label">Non lues</div>
            </div>
            
            <div class="stat-card important">
                <div class="stat-icon">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <div class="stat-number" id="importantCount">{{ $stats['important'] ?? 0 }}</div>
                <div class="stat-label">Importantes</div>
            </div>
            
            <div class="stat-card today">
                <div class="stat-icon">
                    <i class="fas fa-calendar-day"></i>
                </div>
                <div class="stat-number" id="todayCount">{{ $stats['today'] ?? 0 }}</div>
                <div class="stat-label">Aujourd'hui</div>
            </div>
        </div>

        <!-- Panel des notifications -->
        <div class="notifications-panel">
            <div class="panel-header">
                <h2 class="panel-title">
                    <i class="fas fa-inbox me-2"></i>
                    Notifications
                    <small class="ms-2 opacity-75" id="currentFilterText">
                        {{ $filter === 'all' ? 'Toutes' : ucfirst($filter) }}
                    </small>
                </h2>
            </div>

            <!-- Filtres -->
            <div class="filters-section">
                <div class="filters-grid">
                    <span class="text-muted fw-medium">Filtrer par :</span>
                    <a href="{{ route('super-admin.notifications.index', ['filter' => 'all']) }}" 
                       class="filter-btn {{ $filter === 'all' ? 'active' : '' }}">
                        <i class="fas fa-list me-1"></i>Toutes
                    </a>
                    <a href="{{ route('super-admin.notifications.index', ['filter' => 'unread']) }}" 
                       class="filter-btn {{ $filter === 'unread' ? 'active' : '' }}">
                        <i class="fas fa-envelope me-1"></i>Non lues
                    </a>
                    <a href="{{ route('super-admin.notifications.index', ['filter' => 'important']) }}" 
                       class="filter-btn {{ $filter === 'important' ? 'active' : '' }}">
                        <i class="fas fa-star me-1"></i>Importantes
                    </a>
                    <a href="{{ route('super-admin.notifications.index', ['filter' => 'system']) }}" 
                       class="filter-btn {{ $filter === 'system' ? 'active' : '' }}">
                        <i class="fas fa-cog me-1"></i>Système
                    </a>
                    <a href="{{ route('super-admin.notifications.index', ['filter' => 'admin']) }}" 
                       class="filter-btn {{ $filter === 'admin' ? 'active' : '' }}">
                        <i class="fas fa-users me-1"></i>Administrateurs
                    </a>
                </div>
            </div>

            <!-- Liste des notifications -->
            <div class="notifications-list" id="notificationsList">
                <div class="loading-spinner" id="loadingSpinner">
                    <div class="spinner"></div>
                    <div class="text-muted">Chargement des notifications...</div>
                </div>

                @forelse($notifications ?? [] as $notification)
                    <div class="notification-item {{ $notification->read_at ? '' : 'unread' }}" 
                         data-id="{{ $notification->id }}"
                         onclick="markAsRead({{ $notification->id }})">
                        
                        <div class="notification-icon icon-{{ $notification->type }}">
                            <i class="{{ $notification->icon }}"></i>
                        </div>
                        
                        <div class="notification-content">
                            <div class="notification-header">
                                <h4 class="notification-title">{{ $notification->title }}</h4>
                                <span class="notification-time">{{ $notification->time_ago }}</span>
                            </div>
                            
                            <p class="notification-message">{{ $notification->message }}</p>
                            
                            <div class="notification-meta">
                                <span class="priority-badge priority-{{ $notification->priority }}">
                                    {{ ucfirst($notification->priority) }}
                                </span>
                                
                                @if($notification->admin)
                                    <span class="text-muted">
                                        <i class="fas fa-user me-1"></i>{{ $notification->admin->name }}
                                    </span>
                                @endif
                            </div>
                        </div>
                        
                        <div class="notification-actions">
                            @if(!$notification->read_at)
                                <button class="action-btn" 
                                        onclick="event.stopPropagation(); markAsRead({{ $notification->id }})"
                                        title="Marquer comme lu">
                                    <i class="fas fa-check"></i>
                                </button>
                            @endif
                            
                            <button class="action-btn delete" 
                                    onclick="event.stopPropagation(); deleteNotification({{ $notification->id }})"
                                    title="Supprimer">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                @empty
                    <div class="empty-state">
                        <div class="empty-icon">
                            <i class="fas fa-bell-slash"></i>
                        </div>
                        <h3>Aucune notification</h3>
                        <p>Vous n'avez aucune notification pour le moment.</p>
                    </div>
                @endforelse
            </div>

            <!-- Pagination -->
            @if(isset($notifications) && $notifications->hasPages())
                <div class="pagination-wrapper">
                    {{ $notifications->links() }}
                </div>
            @endif
        </div>
    </div>

    <!-- Container pour les toasts -->
    <div class="toast-container" id="toastContainer"></div>
@endsection

@section('js')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Actualisation automatique toutes les 30 secondes
    setInterval(updateNotificationCounts, 30000);
    
    // Marquer une notification comme lue
    window.markAsRead = function(notificationId) {
        fetch(`{{ route('super-admin.notifications.mark-read', '') }}/${notificationId}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                const item = document.querySelector(`[data-id="${notificationId}"]`);
                if (item) {
                    item.classList.remove('unread');
                    const actionBtn = item.querySelector('.action-btn:not(.delete)');
                    if (actionBtn) {
                        actionBtn.remove();
                    }
                }
                updateNotificationCounts();
                showToast('Notification marquée comme lue', 'success');
            }
        })
        .catch(error => {
            console.error('Erreur:', error);
            showToast('Erreur lors de la mise à jour', 'error');
        });
    };
    
    // Supprimer une notification
    window.deleteNotification = function(notificationId) {
        if (!confirm('Êtes-vous sûr de vouloir supprimer cette notification ?')) {
            return;
        }
        
        fetch(`{{ route('super-admin.notifications.destroy', '') }}/${notificationId}`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => {
            if (response.ok) {
                const item = document.querySelector(`[data-id="${notificationId}"]`);
                if (item) {
                    item.style.transition = 'all 0.3s ease';
                    item.style.opacity = '0';
                    item.style.transform = 'translateX(100%)';
                    setTimeout(() => item.remove(), 300);
                }
                updateNotificationCounts();
                showToast('Notification supprimée', 'success');
            } else {
                throw new Error('Erreur de suppression');
            }
        })
        .catch(error => {
            console.error('Erreur:', error);
            showToast('Erreur lors de la suppression', 'error');
        });
    };
    
    // Marquer toutes les notifications comme lues
    window.markAllAsRead = function() {
        fetch('{{ route('super-admin.notifications.mark-all-read') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => {
            if (response.ok) {
                // Mettre à jour l'interface
                document.querySelectorAll('.notification-item.unread').forEach(item => {
                    item.classList.remove('unread');
                    const actionBtn = item.querySelector('.action-btn:not(.delete)');
                    if (actionBtn) {
                        actionBtn.remove();
                    }
                });
                updateNotificationCounts();
                showToast('Toutes les notifications ont été marquées comme lues', 'success');
            } else {
                throw new Error('Erreur lors de la mise à jour');
            }
        })
        .catch(error => {
            console.error('Erreur:', error);
            showToast('Erreur lors de la mise à jour', 'error');
        });
    };
    
    // Actualiser les notifications
    window.refreshNotifications = function() {
        const spinner = document.getElementById('loadingSpinner');
        const list = document.getElementById('notificationsList');
        
        if (spinner) spinner.style.display = 'block';
        
        // Simuler le rechargement
        setTimeout(() => {
            location.reload();
        }, 500);
    };
    
    // Mettre à jour les compteurs
    function updateNotificationCounts() {
        fetch('{{ route('super-admin.notifications.api.unread-count') }}')
        .then(response => response.json())
        .then(data => {
            const unreadElement = document.getElementById('unreadCount');
            if (unreadElement) {
                unreadElement.textContent = data.count || 0;
            }
        })
        .catch(error => console.error('Erreur lors de la mise à jour des compteurs:', error));
    }
    
    // Afficher un toast
    function showToast(message, type = 'info') {
        const container = document.getElementById('toastContainer');
        const toast = document.createElement('div');
        toast.className = 'toast-notification';
        
        const icon = type === 'success' ? 'check-circle' : type === 'error' ? 'times-circle' : 'info-circle';
        const color = type === 'success' ? '#10b981' : type === 'error' ? '#ef4444' : '#3b82f6';
        
        toast.innerHTML = `
            <div class="d-flex align-items-center">
                <i class="fas fa-${icon} me-2" style="color: ${color}"></i>
                <span>${message}</span>
                <button class="btn-close ms-auto" onclick="this.parentElement.parentElement.remove()"></button>
            </div>
        `;
        
        container.appendChild(toast);
        
        // Supprimer automatiquement après 5 secondes
        setTimeout(() => {
            if (toast.parentElement) {
                toast.style.animation = 'slideOut 0.3s ease-out';
                setTimeout(() => toast.remove(), 300);
            }
        }, 5000);
    }
    
    // Gestion du scroll infini (optionnel)
    const notificationsList = document.getElementById('notificationsList');
    if (notificationsList) {
        notificationsList.addEventListener('scroll', function() {
            if (this.scrollTop + this.clientHeight >= this.scrollHeight - 100) {
                // Charger plus de notifications si nécessaire
                // loadMoreNotifications();
            }
        });
    }
});

// CSS pour l'animation de sortie des toasts
const style = document.createElement('style');
style.textContent = `
    @keyframes slideOut {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(100%);
            opacity: 0;
        }
    }
`;
document.head.appendChild(style);
</script>
@endsection