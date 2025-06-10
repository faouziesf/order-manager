{{-- Fichier: resources/views/components/super-admin/notification-bell.blade.php --}}

@props([
    'size' => 'md',           // sm, md, lg
    'showCount' => true,      // Afficher le compteur
    'showDropdown' => true,   // Afficher le dropdown
    'maxItems' => 5,          // Nombre max d'items dans le dropdown
    'autoRefresh' => true,    // Actualisation automatique
    'refreshInterval' => 30   // Intervalle en secondes
])

<div class="notification-bell-component" data-auto-refresh="{{ $autoRefresh }}" data-refresh-interval="{{ $refreshInterval }}">
    @if($showDropdown)
        <!-- Version avec dropdown -->
        <div class="dropdown notification-dropdown">
            <button class="btn notification-bell-btn notification-bell-{{ $size }}" 
                    type="button" 
                    id="notificationBellDropdown" 
                    data-bs-toggle="dropdown" 
                    aria-expanded="false"
                    title="Notifications">
                <i class="fas fa-bell"></i>
                @if($showCount)
                    <span class="notification-count-badge" id="notificationCountBadge">0</span>
                @endif
            </button>
            
            <div class="dropdown-menu dropdown-menu-end notification-dropdown-menu" 
                 aria-labelledby="notificationBellDropdown">
                
                <!-- Header du dropdown -->
                <div class="notification-dropdown-header">
                    <h6 class="mb-0">Notifications</h6>
                    <div class="dropdown-header-actions">
                        <button class="btn btn-sm btn-link text-white" onclick="markAllNotificationsAsRead()" title="Tout marquer comme lu">
                            <i class="fas fa-check-double"></i>
                        </button>
                        <button class="btn btn-sm btn-link text-white" onclick="refreshNotificationDropdown()" title="Actualiser">
                            <i class="fas fa-sync-alt"></i>
                        </button>
                    </div>
                </div>
                
                <!-- Filtres rapides -->
                <div class="notification-quick-filters">
                    <button class="filter-tab active" data-filter="all">Toutes</button>
                    <button class="filter-tab" data-filter="unread">Non lues</button>
                    <button class="filter-tab" data-filter="important">Importantes</button>
                </div>
                
                <!-- Liste des notifications -->
                <div class="notification-dropdown-list" id="notificationDropdownList">
                    <div class="notification-loading-state">
                        <div class="loading-spinner"></div>
                        <span>Chargement...</span>
                    </div>
                </div>
                
                <!-- Footer du dropdown -->
                <div class="notification-dropdown-footer">
                    <a href="{{ route('super-admin.notifications.index') }}" class="btn btn-primary btn-sm w-100">
                        <i class="fas fa-list me-1"></i>
                        Voir toutes les notifications
                    </a>
                </div>
            </div>
        </div>
    @else
        <!-- Version simple (lien vers la page des notifications) -->
        <a href="{{ route('super-admin.notifications.index') }}" 
           class="btn notification-bell-btn notification-bell-{{ $size }}"
           title="Notifications">
            <i class="fas fa-bell"></i>
            @if($showCount)
                <span class="notification-count-badge" id="notificationCountBadge">0</span>
            @endif
        </a>
    @endif
</div>

<style>
/* Styles pour le composant notification bell */
.notification-bell-component {
    position: relative;
}

.notification-bell-btn {
    position: relative;
    border: none;
    background: transparent;
    color: #6b7280;
    transition: all 0.3s ease;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
}

/* Tailles du bouton */
.notification-bell-sm {
    padding: 6px 8px;
    font-size: 14px;
}

.notification-bell-md {
    padding: 8px 12px;
    font-size: 16px;
}

.notification-bell-lg {
    padding: 10px 15px;
    font-size: 18px;
}

.notification-bell-btn:hover {
    color: #4f46e5;
    background: rgba(79, 70, 229, 0.1);
    transform: translateY(-1px);
}

.notification-bell-btn:focus {
    box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.2);
    outline: none;
}

/* Badge de compteur */
.notification-count-badge {
    position: absolute;
    top: -2px;
    right: -2px;
    background: #ef4444;
    color: white;
    border-radius: 50%;
    font-size: 10px;
    font-weight: 600;
    min-width: 16px;
    height: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    animation: notificationPulse 2s infinite;
    border: 2px solid white;
}

.notification-count-badge:empty,
.notification-count-badge[data-count="0"] {
    display: none;
}

/* Animation pour le badge */
@keyframes notificationPulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.1); }
    100% { transform: scale(1); }
}

/* Dropdown menu */
.notification-dropdown-menu {
    width: 380px;
    max-height: 500px;
    padding: 0;
    border: none;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
    border-radius: 12px;
    overflow: hidden;
    margin-top: 8px;
}

/* Header du dropdown */
.notification-dropdown-header {
    background: linear-gradient(135deg, #4f46e5 0%, #3730a3 100%);
    color: white;
    padding: 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.notification-dropdown-header h6 {
    color: white;
    font-weight: 600;
    margin: 0;
}

.dropdown-header-actions {
    display: flex;
    gap: 5px;
}

.dropdown-header-actions .btn {
    padding: 4px 6px;
    color: rgba(255, 255, 255, 0.8);
    border-radius: 4px;
    transition: all 0.3s ease;
}

.dropdown-header-actions .btn:hover {
    color: white;
    background: rgba(255, 255, 255, 0.1);
}

/* Filtres rapides */
.notification-quick-filters {
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

/* Liste des notifications */
.notification-dropdown-list {
    max-height: 300px;
    overflow-y: auto;
}

/* État de chargement */
.notification-loading-state {
    padding: 40px 20px;
    text-align: center;
    color: #6b7280;
}

.loading-spinner {
    width: 20px;
    height: 20px;
    border: 2px solid #f3f4f6;
    border-top: 2px solid #4f46e5;
    border-radius: 50%;
    animation: spin 1s linear infinite;
    margin: 0 auto 10px;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

/* Item de notification dans le dropdown */
.notification-dropdown-item {
    padding: 15px 20px;
    border-bottom: 1px solid #f3f4f6;
    cursor: pointer;
    transition: all 0.3s ease;
    position: relative;
    display: flex;
    gap: 12px;
    align-items: flex-start;
}

.notification-dropdown-item:hover {
    background: #f9fafb;
}

.notification-dropdown-item:last-child {
    border-bottom: none;
}

.notification-dropdown-item.unread {
    background: linear-gradient(90deg, #fef7ff 0%, #ffffff 100%);
    border-left: 3px solid #4f46e5;
}

.notification-dropdown-item.unread::before {
    content: '';
    position: absolute;
    top: 18px;
    left: 8px;
    width: 6px;
    height: 6px;
    background: #4f46e5;
    border-radius: 50%;
}

/* Icône de notification */
.notification-dropdown-icon {
    width: 32px;
    height: 32px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 14px;
    flex-shrink: 0;
}

/* Contenu de notification */
.notification-dropdown-content {
    flex: 1;
    min-width: 0;
}

.notification-dropdown-title {
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

.notification-dropdown-message {
    font-size: 12px;
    color: #6b7280;
    margin: 0 0 6px 0;
    line-height: 1.3;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.notification-dropdown-time {
    font-size: 10px;
    color: #9ca3af;
}

/* Footer du dropdown */
.notification-dropdown-footer {
    padding: 15px 20px;
    background: #f9fafb;
    border-top: 1px solid #e5e7eb;
}

/* État vide */
.notification-empty-state {
    padding: 40px 20px;
    text-align: center;
    color: #6b7280;
}

.notification-empty-state i {
    font-size: 32px;
    margin-bottom: 15px;
    color: #d1d5db;
}

/* Responsive */
@media (max-width: 768px) {
    .notification-dropdown-menu {
        width: 320px;
        left: -280px !important;
        max-height: 400px;
    }
    
    .notification-dropdown-header {
        padding: 15px;
    }
    
    .notification-dropdown-item {
        padding: 12px 15px;
    }
}

/* Classes pour les icônes par type */
.icon-admin { background: #dbeafe; color: #3b82f6; }
.icon-system { background: #f3f4f6; color: #6b7280; }
.icon-security { background: #fee2e2; color: #ef4444; }
.icon-backup { background: #d1fae5; color: #10b981; }
.icon-warning { background: #fef3c7; color: #f59e0b; }
.icon-maintenance { background: #e0e7ff; color: #6366f1; }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    initializeNotificationBell();
});

function initializeNotificationBell() {
    const component = document.querySelector('.notification-bell-component');
    if (!component) return;
    
    const autoRefresh = component.dataset.autoRefresh === 'true';
    const refreshInterval = parseInt(component.dataset.refreshInterval) * 1000;
    
    // Charger les notifications au démarrage
    loadNotificationData();
    
    // Actualisation automatique
    if (autoRefresh) {
        setInterval(loadNotificationData, refreshInterval);
    }
    
    // Gestionnaires d'événements pour les filtres
    setupFilterHandlers();
}

function loadNotificationData() {
    fetch('/super-admin/api/notifications/recent')
        .then(response => response.json())
        .then(data => {
            updateNotificationCount(data.unread_count || 0);
            if (document.getElementById('notificationDropdownList')) {
                updateDropdownNotifications(data.notifications || []);
            }
        })
        .catch(error => {
            console.error('Erreur lors du chargement des notifications:', error);
        });
}

function updateNotificationCount(count) {
    const badge = document.getElementById('notificationCountBadge');
    if (badge) {
        badge.textContent = count;
        badge.setAttribute('data-count', count);
        badge.style.display = count > 0 ? 'flex' : 'none';
    }
}

function updateDropdownNotifications(notifications) {
    const container = document.getElementById('notificationDropdownList');
    if (!container) return;
    
    if (notifications.length === 0) {
        container.innerHTML = `
            <div class="notification-empty-state">
                <i class="fas fa-bell-slash"></i>
                <div>Aucune notification</div>
            </div>
        `;
        return;
    }
    
    const html = notifications.slice(0, 5).map(notification => `
        <div class="notification-dropdown-item ${notification.read_at ? '' : 'unread'}" 
             onclick="handleNotificationClick(${notification.id})">
            <div class="notification-dropdown-icon icon-${notification.type}">
                <i class="${getNotificationIcon(notification.type)}"></i>
            </div>
            <div class="notification-dropdown-content">
                <div class="notification-dropdown-title">${notification.title}</div>
                <div class="notification-dropdown-message">${notification.message}</div>
                <div class="notification-dropdown-time">${notification.time_ago}</div>
            </div>
        </div>
    `).join('');
    
    container.innerHTML = html;
}

function setupFilterHandlers() {
    document.querySelectorAll('.filter-tab').forEach(tab => {
        tab.addEventListener('click', function() {
            document.querySelectorAll('.filter-tab').forEach(t => t.classList.remove('active'));
            this.classList.add('active');
            
            // Ici vous pouvez ajouter la logique de filtrage
            const filter = this.dataset.filter;
            filterDropdownNotifications(filter);
        });
    });
}

function filterDropdownNotifications(filter) {
    // Logique de filtrage des notifications dans le dropdown
    const items = document.querySelectorAll('.notification-dropdown-item');
    items.forEach(item => {
        let show = true;
        
        switch(filter) {
            case 'unread':
                show = item.classList.contains('unread');
                break;
            case 'important':
                // Vous pouvez ajouter un attribut data-priority aux items
                show = item.dataset.priority === 'high';
                break;
            default:
                show = true;
        }
        
        item.style.display = show ? 'flex' : 'none';
    });
}

function handleNotificationClick(notificationId) {
    // Marquer comme lue et rediriger
    markNotificationAsRead(notificationId);
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
            loadNotificationData(); // Recharger les données
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
            loadNotificationData();
            // Fermer le dropdown
            const dropdown = bootstrap.Dropdown.getInstance(document.getElementById('notificationBellDropdown'));
            if (dropdown) dropdown.hide();
        }
    })
    .catch(error => console.error('Erreur:', error));
}

function refreshNotificationDropdown() {
    loadNotificationData();
}

function getNotificationIcon(type) {
    const icons = {
        'admin_registered': 'fas fa-user-plus',
        'admin_expired': 'fas fa-exclamation-triangle',
        'admin_expiring': 'fas fa-clock',
        'high_order_volume': 'fas fa-chart-line',
        'system': 'fas fa-cog',
        'security': 'fas fa-shield-alt',
        'backup': 'fas fa-download',
        'maintenance': 'fas fa-tools'
    };
    return icons[type] || 'fas fa-bell';
}
</script>