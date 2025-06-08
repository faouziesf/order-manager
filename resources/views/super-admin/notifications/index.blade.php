@extends('layouts.super-admin')

@section('title', 'Notifications')

@section('css')
<style>
    .notification-item {
        transition: all 0.3s ease;
        border-left: 4px solid transparent;
        cursor: pointer;
    }
    
    .notification-item:hover {
        background-color: #f8f9fc;
        transform: translateX(2px);
    }
    
    .notification-item.unread {
        background-color: #f8f9ff;
        border-left-color: #4e73df;
    }
    
    .notification-item.unread .notification-title {
        font-weight: 600;
    }
    
    .notification-priority-high {
        border-left-color: #e74a3b !important;
    }
    
    .notification-priority-medium {
        border-left-color: #f6c23e !important;
    }
    
    .notification-priority-low {
        border-left-color: #36b9cc !important;
    }
    
    .notification-icon {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 16px;
    }
    
    .notification-icon.high {
        background-color: rgba(231, 74, 59, 0.1);
        color: #e74a3b;
    }
    
    .notification-icon.medium {
        background-color: rgba(246, 194, 62, 0.1);
        color: #f6c23e;
    }
    
    .notification-icon.low {
        background-color: rgba(54, 185, 204, 0.1);
        color: #36b9cc;
    }
    
    .notification-time {
        font-size: 0.75rem;
        color: #858796;
    }
    
    .notification-title {
        font-size: 0.875rem;
        color: #5a5c69;
        margin-bottom: 0.25rem;
    }
    
    .notification-message {
        font-size: 0.8rem;
        color: #858796;
        line-height: 1.4;
    }
    
    .stats-card {
        border: none;
        box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
    }
    
    .filter-pills .btn {
        border-radius: 50px;
        margin-right: 0.5rem;
        margin-bottom: 0.5rem;
    }
    
    .notification-actions {
        opacity: 0;
        transition: opacity 0.3s ease;
    }
    
    .notification-item:hover .notification-actions {
        opacity: 1;
    }
    
    .batch-actions {
        background-color: #f8f9fc;
        border-radius: 0.35rem;
        padding: 15px;
        margin-bottom: 20px;
        display: none;
    }
    
    .batch-actions.show {
        display: block;
    }
</style>
@endsection

@section('content')
    <!-- En-tête -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Centre de Notifications</h1>
            <p class="text-muted mb-0">Gérez toutes vos notifications système et alertes</p>
        </div>
        <div>
            <button class="btn btn-outline-primary" id="markAllRead">
                <i class="fas fa-check-double"></i> Tout marquer lu
            </button>
            <button class="btn btn-primary" id="refreshNotifications">
                <i class="fas fa-sync-alt"></i> Actualiser
            </button>
        </div>
    </div>

    <!-- Statistiques -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stats-card h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Notifications
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['total'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-bell fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stats-card h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Non Lues
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['unread'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-exclamation-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stats-card h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                Importantes
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['important'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stats-card h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Aujourd'hui
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['today'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar-day fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtres et actions -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <div class="filter-pills">
                        <a href="{{ route('super-admin.notifications.index') }}" 
                           class="btn btn-sm {{ $filter === 'all' ? 'btn-primary' : 'btn-outline-primary' }}">
                            Toutes ({{ $stats['total'] }})
                        </a>
                        <a href="{{ route('super-admin.notifications.index', ['filter' => 'unread']) }}" 
                           class="btn btn-sm {{ $filter === 'unread' ? 'btn-warning' : 'btn-outline-warning' }}">
                            Non lues ({{ $stats['unread'] }})
                        </a>
                        <a href="{{ route('super-admin.notifications.index', ['filter' => 'important']) }}" 
                           class="btn btn-sm {{ $filter === 'important' ? 'btn-danger' : 'btn-outline-danger' }}">
                            Importantes ({{ $stats['important'] }})
                        </a>
                        <a href="{{ route('super-admin.notifications.index', ['filter' => 'system']) }}" 
                           class="btn btn-sm {{ $filter === 'system' ? 'btn-info' : 'btn-outline-info' }}">
                            Système
                        </a>
                        <a href="{{ route('super-admin.notifications.index', ['filter' => 'admin']) }}" 
                           class="btn btn-sm {{ $filter === 'admin' ? 'btn-success' : 'btn-outline-success' }}">
                            Admins
                        </a>
                    </div>
                </div>
                <div class="col-md-4 text-end">
                    <button class="btn btn-outline-secondary btn-sm" id="selectAll">
                        <i class="fas fa-check-square"></i> Sélectionner tout
                    </button>
                    <button class="btn btn-outline-danger btn-sm" id="bulkDelete">
                        <i class="fas fa-trash"></i> Supprimer sélection
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Actions groupées -->
    <div class="batch-actions" id="batchActions">
        <div class="d-flex justify-content-between align-items-center">
            <span class="text-muted">
                <span id="selectedCount">0</span> notification(s) sélectionnée(s)
            </span>
            <div>
                <button class="btn btn-sm btn-outline-primary" id="batchMarkRead">
                    <i class="fas fa-check"></i> Marquer comme lues
                </button>
                <button class="btn btn-sm btn-outline-danger" id="batchDeleteSelected">
                    <i class="fas fa-trash"></i> Supprimer
                </button>
                <button class="btn btn-sm btn-outline-secondary" id="cancelSelection">
                    <i class="fas fa-times"></i> Annuler
                </button>
            </div>
        </div>
    </div>

    <!-- Liste des notifications -->
    <div class="card">
        <div class="card-header">
            <h6 class="card-title mb-0">
                Notifications
                @if($filter !== 'all')
                    - {{ ucfirst($filter) }}
                @endif
            </h6>
        </div>
        <div class="card-body p-0">
            @forelse($notifications as $notification)
                <div class="notification-item p-3 border-bottom {{ $notification->is_read ? '' : 'unread' }} notification-priority-{{ $notification->priority }}" 
                     data-id="{{ $notification->id }}">
                    <div class="row align-items-center">
                        <div class="col-auto">
                            <input type="checkbox" class="form-check-input notification-checkbox" value="{{ $notification->id }}">
                        </div>
                        <div class="col-auto">
                            <div class="notification-icon {{ $notification->priority }}">
                                <i class="{{ $notification->icon }}"></i>
                            </div>
                        </div>
                        <div class="col">
                            <div class="notification-title">{{ $notification->title }}</div>
                            <div class="notification-message">{{ $notification->message }}</div>
                            @if($notification->admin)
                                <small class="text-info">
                                    <i class="fas fa-user me-1"></i>{{ $notification->admin->name }} ({{ $notification->admin->shop_name }})
                                </small>
                            @endif
                        </div>
                        <div class="col-auto">
                            <div class="notification-time">{{ $notification->time_ago }}</div>
                            <span class="badge badge-{{ $notification->color_class }}">{{ ucfirst($notification->priority) }}</span>
                        </div>
                        <div class="col-auto">
                            <div class="notification-actions">
                                @if(!$notification->is_read)
                                    <button class="btn btn-sm btn-outline-primary mark-read" data-id="{{ $notification->id }}">
                                        <i class="fas fa-check"></i>
                                    </button>
                                @endif
                                <button class="btn btn-sm btn-outline-danger delete-notification" data-id="{{ $notification->id }}">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center py-5">
                    <i class="fas fa-bell-slash fa-3x text-gray-300 mb-3"></i>
                    <h5 class="text-gray-500">Aucune notification</h5>
                    <p class="text-muted">
                        @if($filter === 'unread')
                            Toutes vos notifications ont été lues.
                        @else
                            Vous n'avez pas encore de notifications.
                        @endif
                    </p>
                </div>
            @endforelse
        </div>
        
        @if($notifications->hasPages())
            <div class="card-footer">
                {{ $notifications->links() }}
            </div>
        @endif
    </div>
@endsection

@section('js')
<script>
document.addEventListener("DOMContentLoaded", function() {
    let selectedNotifications = [];
    
    // Actualisation automatique toutes les 5 minutes
    setInterval(updateUnreadCount, 300000);
    
    // Gestionnaires d'événements
    setupEventHandlers();
    
    function setupEventHandlers() {
        // Marquer toutes comme lues
        document.getElementById('markAllRead').addEventListener('click', function() {
            if (confirm('Marquer toutes les notifications comme lues ?')) {
                fetch('{{ route('super-admin.notifications.mark-all-read') }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json'
                    }
                }).then(() => {
                    location.reload();
                });
            }
        });

        // Actualiser les notifications
        document.getElementById('refreshNotifications').addEventListener('click', function() {
            location.reload();
        });

        // Sélectionner tout
        document.getElementById('selectAll').addEventListener('click', function() {
            const checkboxes = document.querySelectorAll('.notification-checkbox');
            const allChecked = Array.from(checkboxes).every(cb => cb.checked);
            
            checkboxes.forEach(checkbox => {
                checkbox.checked = !allChecked;
                updateSelection(checkbox);
            });
        });

        // Gestion des cases à cocher
        document.querySelectorAll('.notification-checkbox').forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                updateSelection(this);
            });
        });

        // Marquer une notification comme lue
        document.querySelectorAll('.mark-read').forEach(button => {
            button.addEventListener('click', function(e) {
                e.stopPropagation();
                const notificationId = this.dataset.id;
                markAsRead(notificationId);
            });
        });

        // Supprimer une notification
        document.querySelectorAll('.delete-notification').forEach(button => {
            button.addEventListener('click', function(e) {
                e.stopPropagation();
                const notificationId = this.dataset.id;
                if (confirm('Supprimer cette notification ?')) {
                    deleteNotification(notificationId);
                }
            });
        });

        // Clic sur une notification pour la marquer comme lue
        document.querySelectorAll('.notification-item').forEach(item => {
            item.addEventListener('click', function() {
                if (this.classList.contains('unread')) {
                    const notificationId = this.dataset.id;
                    markAsRead(notificationId);
                }
            });
        });

        // Actions groupées
        document.getElementById('batchMarkRead').addEventListener('click', function() {
            if (selectedNotifications.length > 0) {
                batchMarkAsRead(selectedNotifications);
            }
        });

        document.getElementById('batchDeleteSelected').addEventListener('click', function() {
            if (selectedNotifications.length > 0 && confirm(`Supprimer ${selectedNotifications.length} notification(s) ?`)) {
                batchDelete(selectedNotifications);
            }
        });

        document.getElementById('cancelSelection').addEventListener('click', function() {
            clearSelection();
        });
    }

    function updateSelection(checkbox) {
        const notificationId = parseInt(checkbox.value);
        
        if (checkbox.checked) {
            if (!selectedNotifications.includes(notificationId)) {
                selectedNotifications.push(notificationId);
            }
        } else {
            selectedNotifications = selectedNotifications.filter(id => id !== notificationId);
        }
        
        updateBatchActions();
    }

    function updateBatchActions() {
        const batchActions = document.getElementById('batchActions');
        const selectedCount = document.getElementById('selectedCount');
        
        selectedCount.textContent = selectedNotifications.length;
        
        if (selectedNotifications.length > 0) {
            batchActions.classList.add('show');
        } else {
            batchActions.classList.remove('show');
        }
    }

    function clearSelection() {
        selectedNotifications = [];
        document.querySelectorAll('.notification-checkbox').forEach(cb => cb.checked = false);
        updateBatchActions();
    }

    function markAsRead(notificationId) {
        fetch(`{{ route('super-admin.notifications.mark-read', '') }}/${notificationId}`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json'
            }
        }).then(response => {
            if (response.ok) {
                const item = document.querySelector(`[data-id="${notificationId}"]`);
                item.classList.remove('unread');
                const button = item.querySelector('.mark-read');
                if (button) button.remove();
                updateUnreadCount();
            }
        });
    }

    function deleteNotification(notificationId) {
        fetch(`{{ route('super-admin.notifications.index') }}/${notificationId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json'
            }
        }).then(response => {
            if (response.ok) {
                document.querySelector(`[data-id="${notificationId}"]`).remove();
                updateUnreadCount();
            }
        });
    }

    function batchMarkAsRead(notificationIds) {
        Promise.all(notificationIds.map(id => 
            fetch(`{{ route('super-admin.notifications.mark-read', '') }}/${id}`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json'
                }
            })
        )).then(() => {
            location.reload();
        });
    }

    function batchDelete(notificationIds) {
        Promise.all(notificationIds.map(id => 
            fetch(`{{ route('super-admin.notifications.index') }}/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json'
                }
            })
        )).then(() => {
            location.reload();
        });
    }

    function updateUnreadCount() {
        fetch('{{ route('super-admin.notifications.api.unread-count') }}')
            .then(response => response.json())
            .then(data => {
                // Mettre à jour le compteur dans la barre de navigation
                const badge = document.querySelector('.notification-badge');
                if (badge) {
                    badge.textContent = data.count;
                    badge.style.display = data.count > 0 ? 'inline' : 'none';
                }
            });
    }
});
</script>
@endsection