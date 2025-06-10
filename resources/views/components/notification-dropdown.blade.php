<!-- Composant Dropdown de Notifications - À intégrer dans la navbar -->
<!-- Fichier: resources/views/components/notification-dropdown.blade.php -->

<div class="notification-dropdown">
    <div class="dropdown">
        <button class="btn btn-link notification-trigger" 
                type="button" 
                id="notificationDropdown" 
                data-bs-toggle="dropdown" 
                aria-expanded="false">
            <i class="fas fa-bell"></i>
            <span class="notification-badge" id="notificationBadge">0</span>
        </button>
        
        <div class="dropdown-menu dropdown-menu-end notification-menu" 
             aria-labelledby="notificationDropdown">
            
            <!-- Header -->
            <div class="notification-header">
                <h6 class="mb-0">Notifications</h6>
                <button class="btn btn-sm btn-link mark-all-read" onclick="markAllNotificationsRead()">
                    Tout marquer comme lu
                </button>
            </div>
            
            <!-- Quick filters -->
            <div class="notification-filters">
                <button class="filter-tab active" data-filter="all">Toutes</button>
                <button class="filter-tab" data-filter="unread">Non lues</button>
                <button class="filter-tab" data-filter="important">Importantes</button>
            </div>
            
            <!-- Notifications list -->
            <div class="notification-list" id="dropdownNotificationList">
                <div class="notification-loading">
                    <div class="loading-spinner-small"></div>
                    <span>Chargement...</span>
                </div>
            </div>
            
            <!-- Footer -->
            <div class="notification-footer">
                <a href="{{ route('super-admin.notifications.index') }}" class="btn btn-primary btn-sm w-100">
                    Voir toutes les notifications
                </a>
            </div>
        </div>
    </div>
</div>

<style>
/* Styles pour le dropdown de notifications */
.notification-dropdown {
    position: relative;
}

.notification-trigger {
    position: relative;
    padding: 8px 12px;
    border: none;
    background: transparent;
    color: #6b7280;
    font-size: 18px;
    transition: all 0.3s ease;
}

.notification-trigger:hover {
    color: #4f46e5;
    background: rgba(79, 70, 229, 0.1);
    border-radius: 8px;
}

.notification-badge {
    position: absolute;
    top: 2px;
    right: 2px;
    background: #ef4444;
    color: white;
    border-radius: 50%;
    width: 18px;
    height: 18px;
    font-size: 11px;
    font-weight: 600;
    display: flex;
    align-items: center;
    justify-content: center;
    animation: pulse 2s infinite;
    min-width: 18px;
}

.notification-badge:empty,
.notification-badge[data-count="0"] {
    display: none;
}

@keyframes pulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.1); }
    100% { transform: scale(1); }
}

.notification-menu {
    width: 380px;
    max-height: 500px;
    padding: 0;
    border: none;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
    border-radius: 12px;
    overflow: hidden;
}

.notification-header {
    padding: 20px;
    background: linear-gradient(135deg, #4f46e5 0%, #3730a3 100%);
    color: white;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.notification-header h6 {
    color: white;
    font-weight: 600;
}

.mark-all-read {
    color: rgba(255, 255, 255, 0.8);
    font-size: 12px;
    padding: 4px 8px;
    border-radius: 6px;
    transition: all 0.3s ease;
}

.mark-all-read:hover {
    color: white;
    background: rgba(255, 255, 255, 0.1);
}

.notification-filters {
    display: flex;
    background: #f9fafb;
    border-bottom: 1px solid #e5e7eb;
}

.filter-tab {
    flex: 1;
    padding: 12px;
    border: none;
    background: transparent;
    font-size: 13px;
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

.notification-list {
    max-height: 300px;
    overflow-y: auto;
}

.notification-loading {
    padding: 40px 20px;
    text-align: center;
    color: #6b7280;
}

.loading-spinner-small {
    width: 20px;
    height: 20px;
    border: 2px solid #f3f4f6;
    border-top: 2px solid #4f46e5;
    border-radius: 50%;
    animation: spin 1s linear infinite;
    margin: 0 auto 10px;
}

.dropdown-notification-item {
    padding: 15px 20px;
    border-bottom: 1px solid #f3f4f6;
    cursor: pointer;
    transition: all 0.3s ease;
    position: relative;
}

.dropdown-notification-item:hover {
    background: #f9fafb;
}

.dropdown-notification-item.unread {
    background: #fef7ff;
    border-left: 3px solid #4f46e5;
}

.dropdown-notification-item.unread::before {
    content: '';
    position: absolute;
    top: 20px;
    left: 8px;
    width: 6px;
    height: 6px;
    background: #4f46e5;
    border-radius: 50%;
}

.dropdown-notification-content {
    display: flex;
    align-items: flex-start;
    gap: 12px;
}

.dropdown-notification-icon {
    width: 35px;
    height: 35px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 16px;
    flex-shrink: 0;
}

.dropdown-notification-text {
    flex: 1;
    min-width: 0;
}

.dropdown-notification-title {
    font-size: 14px;
    font-weight: 600;
    color: #111827;
    margin: 0 0 4px 0;
    line-height: 1.3;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.dropdown-notification-message {
    font-size: 13px;
    color: #6b7280;
    margin: 0 0 6px 0;
    line-height: 1.3;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.dropdown-notification-time {
    font-size: 11px;
    color: #9ca3af;
}

.notification-footer {
    padding: 15px 20px;
    background: #f9fafb;
    border-top: 1px solid #e5e7eb;
}

.empty-notifications {
    padding: 40px 20px;
    text-align: center;
    color: #6b7280;
}

.empty-notifications i {
    font-size: 32px;
    margin-bottom: 15px;
    color: #d1d5db;
}

/* Responsive */
@media (max-width: 768px) {
    .notification-menu {
        width: 320px;
        left: -280px !important;
    }
}

/* Icons styles */
.icon-admin { background: #dbeafe; color: #3b82f6; }
.icon-system { background: #f3f4f6; color: #6b7280; }
.icon-security { background: #fee2e2; color: #ef4444; }
.icon-backup { background: #d1fae5; color: #10b981; }
.icon-warning { background: #fef3c7; color: #f59e0b; }
</style>

<script>
// Script pour le dropdown de notifications
document.addEventListener('DOMContentLoaded', function() {
    let currentFilter = 'all';
    let notifications = [];
    
    // Charger les notifications au chargement de la page
    loadDropdownNotifications();
    
    // Actualiser les notifications toutes les 30 secondes
    setInterval(loadDropdownNotifications, 30000);
    
    // Gestionnaire de filtres
    document.querySelectorAll('.filter-tab').forEach(tab => {
        tab.addEventListener('click', function() {
            // Mettre à jour les classes actives
            document.querySelectorAll('.filter-tab').forEach(t => t.classList.remove('active'));
            this.classList.add('active');
            
            // Changer le filtre
            currentFilter = this.dataset.filter;
            displayFilteredNotifications();
        });
    });
    
    // Fonction pour charger les notifications
    function loadDropdownNotifications() {
        fetch('/super-admin/api/notifications/recent')
        .then(response => response.json())
        .then(data => {
            notifications = data.notifications || [];
            updateNotificationBadge(data.unread_count || 0);
            displayFilteredNotifications();
        })
        .catch(error => {
            console.error('Erreur lors du chargement des notifications:', error);
            displayErrorState();
        });
    }
    
    // Fonction pour afficher les notifications filtrées
    function displayFilteredNotifications() {
        const container = document.getElementById('dropdownNotificationList');
        if (!container) return;
        
        let filteredNotifications = notifications;
        
        // Appliquer le filtre
        switch(currentFilter) {
            case 'unread':
                filteredNotifications = notifications.filter(n => !n.read_at);
                break;
            case 'important':
                filteredNotifications = notifications.filter(n => n.priority === 'high');
                break;
            default:
                // 'all' - pas de filtre
                break;
        }
        
        // Limiter à 5 notifications
        filteredNotifications = filteredNotifications.slice(0, 5);
        
        if (filteredNotifications.length === 0) {
            container.innerHTML = `
                <div class="empty-notifications">
                    <i class="fas fa-bell-slash"></i>
                    <div>Aucune notification</div>
                </div>
            `;
            return;
        }
        
        const html = filteredNotifications.map(notification => `
            <div class="dropdown-notification-item ${notification.read_at ? '' : 'unread'}" 
                 onclick="handleNotificationClick(${notification.id})">
                <div class="dropdown-notification-content">
                    <div class="dropdown-notification-icon icon-${notification.type}">
                        <i class="${getNotificationIcon(notification.type)}"></i>
                    </div>
                    <div class="dropdown-notification-text">
                        <div class="dropdown-notification-title">${notification.title}</div>
                        <div class="dropdown-notification-message">${notification.message}</div>
                        <div class="dropdown-notification-time">${notification.time_ago}</div>
                    </div>
                </div>
            </div>
        `).join('');
        
        container.innerHTML = html;
    }
    
    // Fonction pour mettre à jour le badge
    function updateNotificationBadge(count) {
        const badge = document.getElementById('notificationBadge');
        if (badge) {
            badge.textContent = count;
            badge.setAttribute('data-count', count);
            
            if (count > 0) {
                badge.style.display = 'flex';
            } else {
                badge.style.display = 'none';
            }
        }
    }
    
    // Fonction pour afficher l'état d'erreur
    function displayErrorState() {
        const container = document.getElementById('dropdownNotificationList');
        if (container) {
            container.innerHTML = `
                <div class="empty-notifications">
                    <i class="fas fa-exclamation-triangle"></i>
                    <div>Erreur de chargement</div>
                    <button onclick="loadDropdownNotifications()" class="btn btn-sm btn-primary mt-2">
                        Réessayer
                    </button>
                </div>
            `;
        }
    }
    
    // Fonction pour obtenir l'icône selon le type
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
    
    // Gestionnaire de clic sur une notification
    window.handleNotificationClick = function(notificationId) {
        // Marquer comme lue si non lue
        const notification = notifications.find(n => n.id === notificationId);
        if (notification && !notification.read_at) {
            markNotificationAsRead(notificationId);
        }
        
        // Rediriger vers la page des notifications ou une action spécifique
        window.location.href = `/super-admin/notifications`;
    };
    
    // Fonction pour marquer une notification comme lue
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
                // Mettre à jour localement
                const notification = notifications.find(n => n.id === notificationId);
                if (notification) {
                    notification.read_at = new Date().toISOString();
                }
                
                // Recharger l'affichage
                const unreadCount = notifications.filter(n => !n.read_at).length;
                updateNotificationBadge(unreadCount);
                displayFilteredNotifications();
            }
        })
        .catch(error => console.error('Erreur:', error));
    }
    
    // Fonction pour marquer toutes les notifications comme lues
    window.markAllNotificationsRead = function() {
        fetch('/super-admin/notifications/mark-all-read', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => {
            if (response.ok) {
                // Mettre à jour localement
                notifications.forEach(n => {
                    if (!n.read_at) {
                        n.read_at = new Date().toISOString();
                    }
                });
                
                updateNotificationBadge(0);
                displayFilteredNotifications();
                
                // Fermer le dropdown
                const dropdown = document.getElementById('notificationDropdown');
                if (dropdown) {
                    bootstrap.Dropdown.getInstance(dropdown)?.hide();
                }
            }
        })
        .catch(error => console.error('Erreur:', error));
    };
    
    // Exposer la fonction de rechargement globalement
    window.loadDropdownNotifications = loadDropdownNotifications;
});
</script>