/**
 * Gestionnaire d'état offline pour Order Manager
 * Améliore l'UX en gérant les états en ligne/hors ligne de manière élégante
 */

class OfflineHandler {
    constructor() {
        this.isOnline = navigator.onLine;
        this.notification = null;
        this.init();
    }

    init() {
        // Enregistrer le Service Worker
        this.registerServiceWorker();
        
        // Écouter les changements de connexion
        window.addEventListener('online', () => this.handleOnline());
        window.addEventListener('offline', () => this.handleOffline());
        
        // Vérifier l'état initial
        if (!this.isOnline) {
            this.handleOffline();
        }
    }

    registerServiceWorker() {
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.register('/sw.js')
                .then((registration) => {
                    console.log('Service Worker enregistré:', registration.scope);
                    
                    // Vérifier les mises à jour
                    registration.addEventListener('updatefound', () => {
                        const newWorker = registration.installing;
                        newWorker.addEventListener('statechange', () => {
                            if (newWorker.state === 'activated') {
                                // Nouveau service worker activé
                                if (navigator.serviceWorker.controller) {
                                    this.showUpdateNotification();
                                }
                            }
                        });
                    });
                })
                .catch((error) => {
                    console.warn('Erreur d\'enregistrement du Service Worker:', error);
                });
        }
    }

    handleOnline() {
        this.isOnline = true;
        this.removeOfflineNotification();
        this.showNotification('✅ Connexion rétablie', 'success');
        
        // Recharger la page si on est sur la page offline
        if (window.location.pathname === '/offline') {
            setTimeout(() => {
                window.location.href = '/';
            }, 1000);
        }
    }

    handleOffline() {
        this.isOnline = false;
        this.showOfflineNotification();
        
        // Rediriger vers la page offline seulement si ce n'est pas déjà le cas
        if (window.location.pathname !== '/offline') {
            setTimeout(() => {
                window.location.href = '/offline';
            }, 2000);
        }
    }

    showNotification(message, type = 'info') {
        // Créer une notification toast
        const notification = document.createElement('div');
        notification.className = `offline-toast offline-toast-${type}`;
        notification.innerHTML = `
            <div class="offline-toast-content">
                <span>${message}</span>
                <button onclick="this.parentElement.parentElement.remove()" class="offline-toast-close">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        `;
        
        document.body.appendChild(notification);
        
        // Animation d'entrée
        setTimeout(() => notification.classList.add('offline-toast-show'), 100);
        
        // Supprimer automatiquement après 5 secondes
        setTimeout(() => {
            notification.classList.remove('offline-toast-show');
            setTimeout(() => notification.remove(), 300);
        }, 5000);
    }

    showOfflineNotification() {
        if (this.notification) return;
        
        this.notification = document.createElement('div');
        this.notification.className = 'offline-banner';
        this.notification.innerHTML = `
            <div class="offline-banner-content">
                <i class="fas fa-wifi-slash"></i>
                <span>Vous êtes hors ligne. Certaines fonctionnalités peuvent être limitées.</span>
            </div>
        `;
        
        document.body.prepend(this.notification);
    }

    removeOfflineNotification() {
        if (this.notification) {
            this.notification.remove();
            this.notification = null;
        }
    }

    showUpdateNotification() {
        const updateBanner = document.createElement('div');
        updateBanner.className = 'update-banner';
        updateBanner.innerHTML = `
            <div class="update-banner-content">
                <i class="fas fa-sync-alt"></i>
                <span>Une nouvelle version est disponible</span>
                <button onclick="window.location.reload()" class="update-banner-btn">
                    Mettre à jour
                </button>
            </div>
        `;
        
        document.body.prepend(updateBanner);
    }
}

// Styles pour les notifications
const styles = document.createElement('style');
styles.textContent = `
    .offline-toast {
        position: fixed;
        top: 20px;
        right: 20px;
        background: white;
        border-radius: 12px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        padding: 1rem 1.25rem;
        z-index: 10000;
        opacity: 0;
        transform: translateY(-20px);
        transition: all 0.3s ease;
        max-width: 400px;
    }

    .offline-toast-show {
        opacity: 1;
        transform: translateY(0);
    }

    .offline-toast-success {
        border-left: 4px solid #10b981;
    }

    .offline-toast-error {
        border-left: 4px solid #ef4444;
    }

    .offline-toast-info {
        border-left: 4px solid #3b82f6;
    }

    .offline-toast-content {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        font-size: 0.9375rem;
        font-weight: 500;
        color: #1f2937;
    }

    .offline-toast-close {
        background: none;
        border: none;
        color: #9ca3af;
        cursor: pointer;
        font-size: 1rem;
        padding: 0.25rem;
        line-height: 1;
        transition: color 0.2s;
    }

    .offline-toast-close:hover {
        color: #4b5563;
    }

    .offline-banner {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        color: white;
        padding: 0.75rem 1rem;
        z-index: 9999;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        animation: slideDown 0.3s ease-out;
    }

    @keyframes slideDown {
        from {
            transform: translateY(-100%);
        }
        to {
            transform: translateY(0);
        }
    }

    .offline-banner-content {
        max-width: 1200px;
        margin: 0 auto;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.75rem;
        font-size: 0.9375rem;
        font-weight: 600;
    }

    .offline-banner-content i {
        font-size: 1.125rem;
        animation: pulse 2s ease-in-out infinite;
    }

    .update-banner {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
        color: white;
        padding: 0.875rem 1rem;
        z-index: 9999;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        animation: slideDown 0.3s ease-out;
    }

    .update-banner-content {
        max-width: 1200px;
        margin: 0 auto;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 1rem;
        font-size: 0.9375rem;
        font-weight: 600;
    }

    .update-banner-btn {
        background: white;
        color: #3b82f6;
        border: none;
        padding: 0.5rem 1.25rem;
        border-radius: 8px;
        font-weight: 700;
        font-size: 0.875rem;
        cursor: pointer;
        transition: all 0.2s;
    }

    .update-banner-btn:hover {
        transform: scale(1.05);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }

    @media (max-width: 640px) {
        .offline-toast {
            right: 10px;
            left: 10px;
            max-width: none;
        }

        .offline-banner-content,
        .update-banner-content {
            font-size: 0.875rem;
        }
    }
`;
document.head.appendChild(styles);

// Initialiser le gestionnaire offline
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        new OfflineHandler();
    });
} else {
    new OfflineHandler();
}
