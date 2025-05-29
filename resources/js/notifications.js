/**
 * Order Manager - Système de Notifications Temps Réel
 * ================================================
 * 
 * Ce système gère les notifications en temps réel dans l'interface admin.
 * Il vérifie périodiquement les nouvelles notifications et les affiche.
 * 
 * À inclure dans resources/js/notifications.js
 */

class NotificationManager {
    constructor(options = {}) {
        this.options = {
            checkInterval: 30000, // 30 secondes
            maxNotifications: 10,
            autoHide: true,
            autoHideDelay: 8000,
            apiEndpoint: '/api/notifications',
            position: 'top-right',
            ...options
        };
        
        this.notifications = [];
        this.isInitialized = false;
        this.intervalId = null;
        this.container = null;
        
        this.init();
    }
    
    /**
     * Initialisation du système
     */
    init() {
        if (this.isInitialized) return;
        
        this.createContainer();
        this.bindEvents();
        this.startPeriodicCheck();
        this.isInitialized = true;
        
        console.log('🔔 Système de notifications initialisé');
    }
    
    /**
     * Création du conteneur de notifications
     */
    createContainer() {
        this.container = document.createElement('div');
        this.container.id = 'notification-container';
        this.container.className = `notification-container position-${this.options.position}`;
        
        // Styles inline pour éviter les dépendances CSS
        this.container.style.cssText = `
            position: fixed;
            z-index: 9999;
            max-width: 400px;
            width: 100%;
            pointer-events: none;
            ${this.getPositionStyles()}
        `;
        
        document.body.appendChild(this.container);
        
        // Ajouter les styles CSS nécessaires
        this.injectStyles();
    }
    
    /**
     * Obtenir les styles de position
     */
    getPositionStyles() {
        const positions = {
            'top-right': 'top: 20px; right: 20px;',
            'top-left': 'top: 20px; left: 20px;',
            'bottom-right': 'bottom: 20px; right: 20px;',
            'bottom-left': 'bottom: 20px; left: 20px;',
            'top-center': 'top: 20px; left: 50%; transform: translateX(-50%);',
            'bottom-center': 'bottom: 20px; left: 50%; transform: translateX(-50%);'
        };
        
        return positions[this.options.position] || positions['top-right'];
    }
    
    /**
     * Injection des styles CSS
     */
    injectStyles() {
        if (document.getElementById('notification-styles')) return;
        
        const styles = document.createElement('style');
        styles.id = 'notification-styles';
        styles.textContent = `
            .notification-item {
                background: white;
                border-radius: 12px;
                box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
                margin-bottom: 16px;
                padding: 20px;
                border-left: 4px solid #6366f1;
                pointer-events: all;
                transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
                transform: translateX(100%);
                opacity: 0;
                position: relative;
                overflow: hidden;
                backdrop-filter: blur(10px);
                border: 1px solid rgba(255, 255, 255, 0.2);
            }
            
            .notification-item.show {
                transform: translateX(0);
                opacity: 1;
            }
            
            .notification-item.hide {
                transform: translateX(100%);
                opacity: 0;
                margin-bottom: 0;
                padding-top: 0;
                padding-bottom: 0;
                max-height: 0;
            }
            
            .notification-item::before {
                content: '';
                position: absolute;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background: linear-gradient(135deg, rgba(255,255,255,0.1) 0%, rgba(255,255,255,0.05) 100%);
                pointer-events: none;
            }
            
            .notification-item.type-success {
                border-left-color: #10b981;
                background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%);
            }
            
            .notification-item.type-warning {
                border-left-color: #f59e0b;
                background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%);
            }
            
            .notification-item.type-danger {
                border-left-color: #ef4444;
                background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%);
            }
            
            .notification-item.type-info {
                border-left-color: #3b82f6;
                background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
            }
            
            .notification-header {
                display: flex;
                align-items: flex-start;
                gap: 12px;
                margin-bottom: 8px;
            }
            
            .notification-icon {
                width: 24px;
                height: 24px;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 12px;
                color: white;
                flex-shrink: 0;
                margin-top: 2px;
            }
            
            .notification-icon.type-success { background: #10b981; }
            .notification-icon.type-warning { background: #f59e0b; }
            .notification-icon.type-danger { background: #ef4444; }
            .notification-icon.type-info { background: #3b82f6; }
            
            .notification-content {
                flex: 1;
            }
            
            .notification-title {
                font-weight: 600;
                font-size: 14px;
                color: #1f2937;
                margin-bottom: 4px;
                line-height: 1.3;
            }
            
            .notification-message {
                font-size: 13px;
                color: #4b5563;
                line-height: 1.4;
                margin-bottom: 8px;
            }
            
            .notification-actions {
                display: flex;
                gap: 8px;
                align-items: center;
            }
            
            .notification-action {
                padding: 6px 12px;
                border-radius: 6px;
                font-size: 12px;
                font-weight: 500;
                text-decoration: none;
                transition: all 0.2s ease;
                border: 1px solid transparent;
            }
            
            .notification-action.primary {
                background: #6366f1;
                color: white;
            }
            
            .notification-action.primary:hover {
                background: #4f46e5;
                color: white;
                text-decoration: none;
                transform: translateY(-1px);
            }
            
            .notification-action.secondary {
                background: #f3f4f6;
                color: #6b7280;
            }
            
            .notification-action.secondary:hover {
                background: #e5e7eb;
                color: #374151;
                text-decoration: none;
            }
            
            .notification-close {
                position: absolute;
                top: 8px;
                right: 8px;
                width: 24px;
                height: 24px;
                border: none;
                background: rgba(0, 0, 0, 0.1);
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                cursor: pointer;
                font-size: 12px;
                color: #6b7280;
                transition: all 0.2s ease;
            }
            
            .notification-close:hover {
                background: rgba(0, 0, 0, 0.2);
                color: #374151;
                transform: scale(1.1);
            }
            
            .notification-progress {
                position: absolute;
                bottom: 0;
                left: 0;
                height: 2px;
                background: rgba(99, 102, 241, 0.3);
                transition: width linear;
            }
            
            .notification-progress::after {
                content: '';
                position: absolute;
                top: 0;
                left: 0;
                height: 100%;
                background: #6366f1;
                width: 100%;
                transform-origin: left;
                animation: progressBar linear;
            }
            
            @keyframes progressBar {
                from { transform: scaleX(1); }
                to { transform: scaleX(0); }
            }
            
            @media (max-width: 640px) {
                .notification-container {
                    left: 10px !important;
                    right: 10px !important;
                    max-width: none !important;
                    width: auto !important;
                    transform: none !important;
                }
                
                .notification-item {
                    padding: 16px;
                }
            }
        `;
        
        document.head.appendChild(styles);
    }
    
    /**
     * Liaison des événements
     */
    bindEvents() {
        // Écouter les changements de visibilité de la page
        document.addEventListener('visibilitychange', () => {
            if (document.hidden) {
                this.pausePeriodicCheck();
            } else {
                this.resumePeriodicCheck();
                this.checkNotifications(); // Vérification immédiate
            }
        });
        
        // Écouter les événements de focus/blur de la fenêtre
        window.addEventListener('focus', () => {
            this.checkNotifications();
        });
        
        // Nettoyage lors du déchargement de la page
        window.addEventListener('beforeunload', () => {
            this.destroy();
        });
    }
    
    /**
     * Démarrer la vérification périodique
     */
    startPeriodicCheck() {
        if (this.intervalId) return;
        
        this.intervalId = setInterval(() => {
            this.checkNotifications();
        }, this.options.checkInterval);
        
        // Première vérification immédiate
        this.checkNotifications();
    }
    
    /**
     * Pauser la vérification périodique
     */
    pausePeriodicCheck() {
        if (this.intervalId) {
            clearInterval(this.intervalId);
            this.intervalId = null;
        }
    }
    
    /**
     * Reprendre la vérification périodique
     */
    resumePeriodicCheck() {
        if (!this.intervalId) {
            this.startPeriodicCheck();
        }
    }
    
    /**
     * Vérifier les nouvelles notifications via API
     */
    async checkNotifications() {
        try {
            const response = await fetch(this.options.apiEndpoint, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                },
                credentials: 'same-origin'
            });
            
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
            
            const data = await response.json();
            
            if (data.notifications && Array.isArray(data.notifications)) {
                this.processNotifications(data.notifications);
            }
            
        } catch (error) {
            console.warn('🔔 Erreur lors de la récupération des notifications:', error.message);
            
            // En cas d'erreur 401, l'utilisateur n'est plus connecté
            if (error.message.includes('401')) {
                this.pausePeriodicCheck();
            }
        }
    }
    
    /**
     * Traiter les nouvelles notifications
     */
    processNotifications(notifications) {
        notifications.forEach(notification => {
            // Vérifier si la notification existe déjà
            const existingIndex = this.notifications.findIndex(n => n.id === notification.id);
            
            if (existingIndex === -1) {
                // Nouvelle notification
                this.showNotification(notification);
            }
        });
        
        // Nettoyer les anciennes notifications qui ne sont plus présentes
        this.notifications = this.notifications.filter(existingNotif => {
            return notifications.some(newNotif => newNotif.id === existingNotif.id);
        });
    }
    
    /**
     * Afficher une notification
     */
    showNotification(notification) {
        // Limiter le nombre de notifications
        if (this.notifications.length >= this.options.maxNotifications) {
            const oldest = this.notifications.shift();
            this.hideNotification(oldest.id);
        }
        
        // Ajouter la notification à la liste
        this.notifications.push(notification);
        
        // Créer l'élément DOM
        const element = this.createNotificationElement(notification);
        this.container.appendChild(element);
        
        // Animation d'apparition
        setTimeout(() => {
            element.classList.add('show');
        }, 10);
        
        // Auto-hide si configuré
        if (this.options.autoHide && !notification.persistent) {
            setTimeout(() => {
                this.hideNotification(notification.id);
            }, this.options.autoHideDelay);
        }
        
        // Émettre un événement personnalisé
        this.emit('notification:show', notification);
    }
    
    /**
     * Créer l'élément DOM d'une notification
     */
    createNotificationElement(notification) {
        const element = document.createElement('div');
        element.className = `notification-item type-${notification.type}`;
        element.dataset.notificationId = notification.id;
        
        const icon = this.getTypeIcon(notification.type);
        const hasAction = notification.action_url && notification.action_text;
        
        element.innerHTML = `
            <div class="notification-header">
                <div class="notification-icon type-${notification.type}">
                    <i class="${notification.icon || icon}"></i>
                </div>
                <div class="notification-content">
                    <div class="notification-title">${this.escapeHtml(notification.title)}</div>
                    <div class="notification-message">${this.escapeHtml(notification.message)}</div>
                    ${hasAction ? `
                        <div class="notification-actions">
                            <a href="${notification.action_url}" class="notification-action primary">
                                ${this.escapeHtml(notification.action_text)}
                            </a>
                            <button class="notification-action secondary" onclick="notificationManager.hideNotification('${notification.id}')">
                                Plus tard
                            </button>
                        </div>
                    ` : ''}
                </div>
            </div>
            <button class="notification-close" onclick="notificationManager.hideNotification('${notification.id}')" title="Fermer">
                <i class="fas fa-times"></i>
            </button>
            ${this.options.autoHide && !notification.persistent ? `
                <div class="notification-progress" style="animation-duration: ${this.options.autoHideDelay}ms;"></div>
            ` : ''}
        `;
        
        return element;
    }
    
    /**
     * Masquer une notification
     */
    hideNotification(notificationId) {
        const element = this.container.querySelector(`[data-notification-id="${notificationId}"]`);
        if (!element) return;
        
        element.classList.add('hide');
        
        setTimeout(() => {
            if (element.parentNode) {
                element.parentNode.removeChild(element);
            }
        }, 300);
        
        // Retirer de la liste
        this.notifications = this.notifications.filter(n => n.id !== notificationId);
        
        // Émettre un événement personnalisé
        this.emit('notification:hide', notificationId);
    }
    
    /**
     * Masquer toutes les notifications
     */
    hideAll() {
        const elements = this.container.querySelectorAll('.notification-item');
        elements.forEach(element => {
            const id = element.dataset.notificationId;
            this.hideNotification(id);
        });
    }
    
    /**
     * Obtenir l'icône par défaut selon le type
     */
    getTypeIcon(type) {
        const icons = {
            success: 'fas fa-check',
            warning: 'fas fa-exclamation-triangle',
            danger: 'fas fa-exclamation-circle',
            info: 'fas fa-info-circle'
        };
        
        return icons[type] || icons.info;
    }
    
    /**
     * Échapper le HTML pour éviter les injections XSS
     */
    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
    
    /**
     * Émettre un événement personnalisé
     */
    emit(eventName, data) {
        const event = new CustomEvent(eventName, { detail: data });
        document.dispatchEvent(event);
    }
    
    /**
     * Détruire le gestionnaire de notifications
     */
    destroy() {
        this.pausePeriodicCheck();
        
        if (this.container && this.container.parentNode) {
            this.container.parentNode.removeChild(this.container);
        }
        
        this.notifications = [];
        this.isInitialized = false;
        
        console.log('🔔 Système de notifications détruit');
    }
    
    /**
     * Afficher manuellement une notification
     */
    show(notification) {
        const defaultNotification = {
            id: 'manual_' + Date.now(),
            type: 'info',
            title: 'Notification',
            message: '',
            persistent: false,
            ...notification
        };
        
        this.showNotification(defaultNotification);
    }
    
    /**
     * Méthodes de convenance pour différents types
     */
    success(title, message, options = {}) {
        this.show({ type: 'success', title, message, ...options });
    }
    
    warning(title, message, options = {}) {
        this.show({ type: 'warning', title, message, ...options });
    }
    
    danger(title, message, options = {}) {
        this.show({ type: 'danger', title, message, ...options });
    }
    
    info(title, message, options = {}) {
        this.show({ type: 'info', title, message, ...options });
    }
}

// ============================================================================
// INITIALISATION AUTOMATIQUE
// ============================================================================

// Initialiser le gestionnaire quand le DOM est prêt
document.addEventListener('DOMContentLoaded', () => {
    // Vérifier si on est dans l'interface admin
    if (document.querySelector('meta[name="csrf-token"]') && 
        (window.location.pathname.startsWith('/admin') || 
         window.location.pathname.startsWith('/manager') || 
         window.location.pathname.startsWith('/employee'))) {
        
        // Créer l'instance globale
        window.notificationManager = new NotificationManager({
            checkInterval: 30000, // 30 secondes
            maxNotifications: 5,
            autoHide: true,
            autoHideDelay: 8000,
            position: 'top-right'
        });
        
        console.log('🔔 NotificationManager initialisé globalement');
    }
});

// Export pour utilisation en tant que module
if (typeof module !== 'undefined' && module.exports) {
    module.exports = NotificationManager;
}

// ============================================================================
// UTILISATION DANS LES VUES BLADE
// ============================================================================

/*
<!-- À ajouter dans le layout admin, juste avant la fermeture du body -->
<script>
// Écouter les événements de notification personnalisés
document.addEventListener('notification:show', (event) => {
    console.log('Nouvelle notification:', event.detail);
});

document.addEventListener('notification:hide', (event) => {
    console.log('Notification fermée:', event.detail);
});

// Exemple d'utilisation depuis les contrôleurs via session flash
@if(session('notification'))
    document.addEventListener('DOMContentLoaded', function() {
        if (window.notificationManager) {
            const notif = @json(session('notification'));
            window.notificationManager.show(notif);
        }
    });
@endif
</script>
*/