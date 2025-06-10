{{-- Composant notification bell pour la navbar --}}
@props([
    'size' => 'md',
    'showCount' => true,
    'autoRefresh' => true,
    'refreshInterval' => 30
])

<div class="notification-bell-wrapper" data-auto-refresh="{{ $autoRefresh }}" data-refresh-interval="{{ $refreshInterval }}">
    <div class="dropdown">
        <button class="notification-bell-trigger btn btn-link p-0" 
                type="button" 
                id="notificationBellDropdown" 
                data-bs-toggle="dropdown" 
                aria-expanded="false"
                title="Notifications">
            <div class="notification-bell-icon">
                <i class="fas fa-bell"></i>
                @if($showCount)
                    @php
                        $unreadCount = \App\Models\SuperAdminNotification::whereNull('read_at')->count();
                    @endphp
                    @if($unreadCount > 0)
                        <span class="notification-bell-badge" id="notificationBellBadge">{{ $unreadCount }}</span>
                    @else
                        <span class="notification-bell-badge" id="notificationBellBadge" style="display: none;">0</span>
                    @endif
                @endif
            </div>
        </button>
        
        <div class="dropdown-menu dropdown-menu-end notification-bell-dropdown" 
             aria-labelledby="notificationBellDropdown">
            
            <!-- Header -->
            <div class="notification-bell-header">
                <h6 class="mb-0">
                    <i class="fas fa-bell me-2"></i>
                    Notifications
                </h6>
                <div class="notification-bell-actions">
                    <button class="btn btn-sm btn-link text-white p-1" 
                            onclick="markAllNotificationsAsRead()" 
                            title="Tout marquer comme lu">
                        <i class="fas fa-check-double"></i>
                    </button>
                    <button class="btn btn-sm btn-link text-white p-1" 
                            onclick="refreshNotificationBell()" 
                            title="Actualiser">
                        <i class="fas fa-sync-alt"></i>
                    </button>
                </div>
            </div>
            
            <!-- Quick filters -->
            <div class="notification-bell-filters">
                <button class="filter-tab active" data-filter="all">Toutes</button>
                <button class="filter-tab" data-filter="unread">Non lues</button>
                <button class="filter-tab" data-filter="important">Importantes</button>
            </div>
            
            <!-- Loading state -->
            <div class="notification-bell-loading" id="notificationBellLoading">
                <div class="d-flex align-items-center justify-content-center py-4">
                    <div class="spinner-border spinner-border-sm me-2" role="status"></div>
                    <span>Chargement...</span>
                </div>
            </div>
            
            <!-- Notifications list -->
            <div class="notification-bell-list" id="notificationBellList" style="display: none;">
                <!-- Les notifications seront chargées ici via JavaScript -->
            </div>
            
            <!-- Empty state -->
            <div class="notification-bell-empty" id="notificationBellEmpty" style="display: none;">
                <div class="text-center py-4">
                    <i class="fas fa-bell-slash text-muted mb-2" style="font-size: 2rem;"></i>
                    <p class="text-muted mb-0">Aucune notification</p>
                </div>
            </div>
            
            <!-- Footer -->
            <div class="notification-bell-footer">
                <a href="{{ route('super-admin.notifications.index') }}" class="btn btn-primary btn-sm w-100">
                    <i class="fas fa-list me-1"></i>
                    Voir toutes les notifications
                </a>
            </div>
        </div>
    </div>
</div>

<style>
/* Styles pour le notification bell */
.notification-bell-wrapper {
    position: relative;
}

.notification-bell-trigger {
    border: none !important;
    background: transparent !important;
    box-shadow: none !important;
}

.notification-bell-icon {
    position: relative;
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    color: #6b7280;
    border-radius: 10px;
    transition: all 0.3s ease;
    cursor: pointer;
}

.notification-bell-icon:hover {
    background: rgba(79, 70, 229, 0.1);
    color: #4f46e5;
}

.notification-bell-badge {
    position: absolute;
    top: -2px;
    right: -2px;
    background: #ef4444;
    color: white;
    border-radius: 50%;
    font-size: 10px;
    font-weight: 600;
    min-width: 18px;
    height: 18px;
    display: flex;
    align-items: center;
    justify-content: center;
    animation: notificationPulse 2s infinite;
    border: 2px solid white;
}

@keyframes notificationPulse {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.1); }
}

.notification-bell-dropdown {
    width: 380px;
    max-height: 500px;
    padding: 0;
    border: none;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
    border-radius: 12px;
    overflow: hidden;
    margin-top: 8px;
}

.notification-bell-header {
    background: linear-gradient(135deg, #4f46e5 0%, #3730a3 100%);
    color: white;
    padding: 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.notification-bell-header h6 {
    color: white;
    font-weight: 600;
    margin: 0;
}

.notification-bell-actions {
    display: flex;
    gap: 5px;
}

.notification-bell-actions .btn {
    color: rgba(255, 255, 255, 0.8);
    border-radius: 6px;
    transition: all 0.3s ease;
    width: 30px;
    height: 30px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.notification-bell-actions .btn:hover {
    color: white;
    background: rgba(255, 255, 255, 0.1);
}

.notification-bell-filters {
    display: flex;
    background: #f9fafb;
    border-bottom: 1px solid #e5e7eb;
}

.filter-tab {
    flex: 1;
    padding: 10px 12px;
    border: none;
    background: transparent;
    font-size: 12px;
    font-weight: 500;
    color: #6b7280;
    cursor: pointer;
    transition: all 0.3s ease;
}

.filter-tab:hover,
.filter-tab.active {
    background: #4f46e5;
    color: white;
}

.notification-bell-list {
    max-height: 300px;
    overflow-y: auto;
}

.notification-bell-item {
    padding: 15px 20px;
    border-bottom: 1px solid #f3f4f6;
    cursor: pointer;
    transition: all 0.3s ease;
    position: relative;
    display: flex;
    gap: 12px;
    align-items: flex-start;
}

.notification-bell-item:hover {
    background: #f9fafb;
}

.notification-bell-item:last-child {
    border-bottom: none;
}

.notification-bell-item.unread {
    background: linear-gradient(90deg, #fef7ff 0%, #ffffff 100%);
    border-left: 3px solid #4f46e5;
}

.notification-bell-item.unread::before {
    content: '';
    position: absolute;
    top: 18px;
    left: 8px;
    width: 6px;
    height: 6px;
    background: #4f46e5;
    border-radius: 50%;
}

.notification-bell-item-icon {
    width: 32px;
    height: 32px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 14px;
    flex-shrink: 0;
}

.notification-bell-item-content {
    flex: 1;
    min-width: 0;
}

.notification-bell-item-title {
    font-size: 13px;
    font-weight: 600;
    color: #111827;
    margin: 0 0 4px 0;
    line-height: 1.3;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.notification-bell-item-message {
    font-size: 12px;
    color: #6b7280;
    margin: 0 0 6px 0;
    line-height: 1.3;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.notification-bell-item-time {
    font-size: 10px;
    color: #9ca3af;
}

.notification-bell-footer {
    padding: 15px 20px;
    background: #f9fafb;
    border-top: 1px solid #e5e7eb;
}

.notification-bell-loading,
.notification-bell-empty {
    padding: 20px;
    text-align: center;
    color: #6b7280;
}

/* Icons par type */
.icon-admin_registered { background: #dbeafe; color: #3b82f6; }
.icon-admin_expired { background: #fee2e2; color: #ef4444; }
.icon-admin_expiring { background: #fef3c7; color: #f59e0b; }
.icon-admin_inactive { background: #f3f4f6; color: #6b7280; }
.icon-high_order_volume { background: #dbeafe; color: #3b82f6; }
.icon-system { background: #f3f4f6; color: #6b7280; }
.icon-security { background: #fee2e2; color: #ef4444; }
.icon-backup { background: #d1fae5; color: #10b981; }
.icon-maintenance { background: #e0e7ff; color: #6366f1; }
.icon-performance { background: #fef3c7; color: #f59e0b; }
.icon-disk_space { background: #fee2e2; color: #ef4444; }
.icon-database { background: #dbeafe; color: #3b82f6; }

/* Responsive */
@media (max-width: 768px) {
    .notification-bell-dropdown {
        width: 320px;
        left: -280px !important;
        max-height: 400px;
    }
    
    .notification-bell-header {
        padding: 15px;
    }
    
    .notification-bell-item {
        padding: 12px 15px;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    initializeNotificationBell();
});

function initializeNotificationBell() {
    const wrapper = document.querySelector('.notification-bell-wrapper');
    if (!wrapper) return;
    
    const autoRefresh = wrapper.dataset.autoRefresh === 'true';
    const refreshInterval = parseInt(wrapper.dataset.refreshInterval) * 1000;
    
    // Charger les notifications au démarrage
    loadNotificationBellData();
    
    // Actualisation automatique
    if (autoRefresh) {
        setInterval(loadNotificationBellData, refreshInterval);
    }
    
    // Gestionnaires d'événements pour les filtres
    setupFilterHandlers();
    
    // Charger les notifications quand le dropdown s'ouvre
    const dropdown = document.getElementById('notificationBellDropdown');
    if (dropdown) {
        dropdown.addEventListener('shown.bs.dropdown', function() {
            loadNotificationBellData();
        });
    }
}

function loadNotificationBellData() {
    fetch('/super-admin/api/notifications/recent')
        .then(response => response.json())
        .then(data => {
            updateNotificationBellBadge(data.unread_count || 0);
            updateNotificationBellList(data.notifications || []);
        })
        .catch(error => {
            console.error('Erreur lors du chargement des notifications:', error);
            showNotificationBellError();
        });
}

function updateNotificationBellBadge(count) {
    const badge = document.getElementById('notificationBellBadge');
    if (badge) {
        badge.textContent = count;
        badge.style.display = count > 0 ? 'flex' : 'none';
    }
}

function updateNotificationBellList(notifications) {
    const loading = document.getElementById('notificationBellLoading');
    const list = document.getElementById('notificationBellList');
    const empty = document.getElementById('notificationBellEmpty');
    
    if (loading) loading.style.display = 'none';
    
    if (notifications.length === 0) {
        if (list) list.style.display = 'none';
        if (empty) empty.style.display = 'block';
        return;
    }
    
    if (empty) empty.style.display = 'none';
    if (list) {
        list.style.display = 'block';
        list.innerHTML = notifications.slice(0, 5).map(notification => `
            <div class="notification-bell-item ${notification.read_at ? '' : 'unread'}" 
                 onclick="handleNotificationBellClick(${notification.id})">
                <div class="notification-bell-item-icon icon-${notification.type}">
                    <i class="${getNotificationIcon(notification.type)}"></i>
                </div>
                <div class="notification-bell-item-content">
                    <div class="notification-bell-item-title">${notification.title}</div>
                    <div class="notification-bell-item-message">${notification.message}</div>
                    <div class="notification-bell-item-time">${notification.time_ago}</div>
                </div>
            </div>
        `).join('');
    }
}

function showNotificationBellError() {
    const loading = document.getElementById('notificationBellLoading');
    const list = document.getElementById('notificationBellList');
    const empty = document.getElementById('notificationBellEmpty');
    
    if (loading) loading.style.display = 'none';
    if (list) list.style.display = 'none';
    if (empty) {
        empty.style.display = 'block';
        empty.innerHTML = `
            <div class="text-center py-4">
                <i class="fas fa-exclamation-triangle text-warning mb-2" style="font-size: 2rem;"></i>
                <p class="text-muted mb-2">Erreur de chargement</p>
                <button onclick="loadNotificationBellData()" class="btn btn-sm btn-primary">
                    Réessayer
                </button>
            </div>
        `;
    }
}

function setupFilterHandlers() {
    document.querySelectorAll('.filter-tab').forEach(tab => {
        tab.addEventListener('click', function() {
            document.querySelectorAll('.filter-tab').forEach(t => t.classList.remove('active'));
            this.classList.add('active');
            
            const filter = this.dataset.filter;
            filterNotificationBellItems(filter);
        });
    });
}

function filterNotificationBellItems(filter) {
    const items = document.querySelectorAll('.notification-bell-item');
    items.forEach(item => {
        let show = true;
        
        switch(filter) {
            case 'unread':
                show = item.classList.contains('unread');
                break;
            case 'important':
                // Vous pouvez ajouter un attribut data-priority
                show = item.dataset.priority === 'high';
                break;
            default:
                show = true;
        }
        
        item.style.display = show ? 'flex' : 'none';
    });
}

function handleNotificationBellClick(notificationId) {
    // Marquer comme lue et rediriger
    markNotificationAsRead(notificationId);
    
    // Fermer le dropdown
    const dropdown = bootstrap.Dropdown.getInstance(document.getElementById('notificationBellDropdown'));
    if (dropdown) dropdown.hide();
    
    // Rediriger vers la page des notifications
    window.location.href = '/super-admin/notifications';
}

function markNotificationAsRead(notificationId) {
    fetch(`/super-admin/notifications/mark-read/${notificationId}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            loadNotificationBellData();
        }
    })
    .catch(error => console.error('Erreur:', error));
}

function markAllNotificationsAsRead() {
    fetch('/super-admin/notifications/mark-all-read', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => {
        if (response.ok) {
            loadNotificationBellData();
            
            // Fermer le dropdown
            const dropdown = bootstrap.Dropdown.getInstance(document.getElementById('notificationBellDropdown'));
            if (dropdown) dropdown.hide();
        }
    })
    .catch(error => console.error('Erreur:', error));
}

function refreshNotificationBell() {
    const loading = document.getElementById('notificationBellLoading');
    const list = document.getElementById('notificationBellList');
    const empty = document.getElementById('notificationBellEmpty');
    
    if (loading) loading.style.display = 'block';
    if (list) list.style.display = 'none';
    if (empty) empty.style.display = 'none';
    
    loadNotificationBellData();
}

function getNotificationIcon(type) {
    const icons = {
        'admin_registered': 'fas fa-user-plus',
        'admin_expired': 'fas fa-exclamation-triangle',
        'admin_expiring': 'fas fa-clock',
        'admin_inactive': 'fas fa-user-slash',
        'high_order_volume': 'fas fa-chart-line',
        'system': 'fas fa-cog',
        'security': 'fas fa-shield-alt',
        'backup': 'fas fa-download',
        'maintenance': 'fas fa-tools',
        'performance': 'fas fa-tachometer-alt',
        'disk_space': 'fas fa-hdd',
        'database': 'fas fa-database'
    };
    return icons[type] || 'fas fa-bell';
}

// Exposer les fonctions globalement
window.loadNotificationBellData = loadNotificationBellData;
window.markAllNotificationsAsRead = markAllNotificationsAsRead;
window.refreshNotificationBell = refreshNotificationBell;
</script>