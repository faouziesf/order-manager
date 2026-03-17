<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hors ligne - Order Manager</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
            position: relative;
            overflow: hidden;
        }

        body::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 1px, transparent 1px);
            background-size: 50px 50px;
            animation: moveGrid 20s linear infinite;
            pointer-events: none;
        }

        @keyframes moveGrid {
            0% { transform: translate(0, 0); }
            100% { transform: translate(50px, 50px); }
        }

        .offline-container {
            position: relative;
            z-index: 1;
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(20px);
            border-radius: 32px;
            box-shadow: 0 30px 60px rgba(0, 0, 0, 0.2);
            width: 100%;
            max-width: 500px;
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, 0.3);
            animation: fadeInScale 0.6s ease-out;
        }

        @keyframes fadeInScale {
            from {
                opacity: 0;
                transform: scale(0.9) translateY(20px);
            }
            to {
                opacity: 1;
                transform: scale(1) translateY(0);
            }
        }

        .offline-header {
            padding: 3rem 2rem 2rem;
            text-align: center;
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        }

        .offline-icon-container {
            width: 120px;
            height: 120px;
            margin: 0 auto 1.5rem;
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(10px);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            animation: pulse 2s ease-in-out infinite;
        }

        @keyframes pulse {
            0%, 100% {
                transform: scale(1);
                box-shadow: 0 0 0 0 rgba(255, 255, 255, 0.7);
            }
            50% {
                transform: scale(1.05);
                box-shadow: 0 0 0 15px rgba(255, 255, 255, 0);
            }
        }

        .offline-icon {
            font-size: 3.5rem;
            color: white;
            animation: float 3s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }

        .offline-title {
            font-size: 2rem;
            font-weight: 800;
            color: white;
            margin-bottom: 0.5rem;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .offline-subtitle {
            font-size: 1.1rem;
            color: rgba(255, 255, 255, 0.95);
            font-weight: 500;
            margin-bottom: 0;
        }

        .offline-body {
            padding: 2.5rem 2rem;
            text-align: center;
        }

        .offline-message {
            font-size: 1rem;
            color: #4b5563;
            line-height: 1.75;
            margin-bottom: 2rem;
        }

        .offline-steps {
            text-align: left;
            background: #f9fafb;
            border-radius: 16px;
            padding: 1.5rem;
            margin-bottom: 2rem;
        }

        .offline-steps h3 {
            font-size: 0.875rem;
            font-weight: 700;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 1rem;
        }

        .offline-step {
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
            padding: 0.75rem 0;
            border-bottom: 1px solid #e5e7eb;
        }

        .offline-step:last-child {
            border-bottom: none;
            padding-bottom: 0;
        }

        .offline-step-icon {
            width: 32px;
            height: 32px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.875rem;
            font-weight: 700;
            flex-shrink: 0;
        }

        .offline-step-text {
            flex: 1;
            padding-top: 0.25rem;
        }

        .offline-step-title {
            font-weight: 600;
            color: #1f2937;
            font-size: 0.9375rem;
            margin-bottom: 0.25rem;
        }

        .offline-step-desc {
            font-size: 0.8125rem;
            color: #6b7280;
            line-height: 1.5;
        }

        .btn-retry {
            width: 100%;
            padding: 1rem 2rem;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 14px;
            font-size: 1rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
        }

        .btn-retry:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.5);
        }

        .btn-retry:active {
            transform: translateY(0);
        }

        .btn-retry i {
            transition: transform 0.3s ease;
        }

        .btn-retry:hover i {
            transform: rotate(180deg);
        }

        .offline-footer {
            padding: 1.5rem 2rem 2rem;
            text-align: center;
            border-top: 1px solid #e5e7eb;
        }

        .connection-status {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            background: #fef2f2;
            color: #dc2626;
            border-radius: 50px;
            font-size: 0.875rem;
            font-weight: 600;
            margin-bottom: 1rem;
        }

        .connection-status.online {
            background: #f0fdf4;
            color: #16a34a;
        }

        .status-dot {
            width: 8px;
            height: 8px;
            background-color: currentColor;
            border-radius: 50%;
            animation: blink 1.5s ease-in-out infinite;
        }

        .status-dot.online {
            animation: none;
        }

        @keyframes blink {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.3; }
        }

        .offline-tip {
            font-size: 0.8125rem;
            color: #9ca3af;
            font-style: italic;
        }

        .loader {
            display: none;
            width: 20px;
            height: 20px;
            border: 3px solid rgba(255, 255, 255, 0.3);
            border-top-color: white;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        .btn-retry.checking .loader {
            display: block;
        }

        .btn-retry.checking .retry-text {
            display: none;
        }

        @media (max-width: 576px) {
            .offline-container {
                border-radius: 24px;
                max-width: 100%;
            }

            .offline-header {
                padding: 2rem 1.5rem 1.5rem;
            }

            .offline-icon-container {
                width: 100px;
                height: 100px;
                margin-bottom: 1rem;
            }

            .offline-icon {
                font-size: 2.5rem;
            }

            .offline-title {
                font-size: 1.5rem;
            }

            .offline-subtitle {
                font-size: 1rem;
            }

            .offline-body {
                padding: 2rem 1.5rem;
            }

            .btn-retry {
                padding: 0.875rem 1.5rem;
                font-size: 0.9375rem;
            }
        }

        /* Animation pour la reconnexion */
        .reconnecting {
            animation: reconnect 0.6s ease-out;
        }

        @keyframes reconnect {
            0% {
                transform: scale(1);
                opacity: 1;
            }
            50% {
                transform: scale(0.95);
                opacity: 0.8;
            }
            100% {
                transform: scale(1);
                opacity: 1;
            }
        }
    </style>
</head>
<body>
    <div class="offline-container" id="offlineContainer">
        <div class="offline-header">
            <div class="offline-icon-container">
                <i class="fas fa-wifi-slash offline-icon"></i>
            </div>
            <h1 class="offline-title">Vous êtes hors ligne</h1>
            <p class="offline-subtitle">Aucune connexion Internet détectée</p>
        </div>

        <div class="offline-body">
            <p class="offline-message">
                Impossible d'accéder à Order Manager. Vérifiez votre connexion Internet pour continuer.
            </p>

            <div class="offline-steps">
                <h3><i class="fas fa-lightbulb me-2"></i>Solutions rapides</h3>
                
                <div class="offline-step">
                    <div class="offline-step-icon">1</div>
                    <div class="offline-step-text">
                        <div class="offline-step-title">Vérifiez votre connexion</div>
                        <div class="offline-step-desc">Assurez-vous que le Wi-Fi ou les données mobiles sont activés</div>
                    </div>
                </div>

                <div class="offline-step">
                    <div class="offline-step-icon">2</div>
                    <div class="offline-step-text">
                        <div class="offline-step-title">Mode Avion</div>
                        <div class="offline-step-desc">Désactivez le mode avion si celui-ci est activé</div>
                    </div>
                </div>

                <div class="offline-step">
                    <div class="offline-step-icon">3</div>
                    <div class="offline-step-text">
                        <div class="offline-step-title">Redémarrage</div>
                        <div class="offline-step-desc">Redémarrez votre routeur ou modem si nécessaire</div>
                    </div>
                </div>
            </div>

            <button onclick="retryConnection()" class="btn-retry" id="retryBtn">
                <span class="loader"></span>
                <span class="retry-text">
                    <i class="fas fa-redo-alt"></i>
                    Réessayer la connexion
                </span>
            </button>
        </div>

        <div class="offline-footer">
            <div class="connection-status" id="connectionStatus">
                <span class="status-dot"></span>
                <span>Hors ligne</span>
            </div>
            <p class="offline-tip">
                La page se rechargera automatiquement une fois la connexion rétablie
            </p>
        </div>
    </div>

    <script>
        function retryConnection() {
            const btn = document.getElementById('retryBtn');
            const container = document.getElementById('offlineContainer');
            const statusEl = document.getElementById('connectionStatus');
            
            btn.classList.add('checking');
            
            // Tenter de faire une requête vers le serveur
            fetch(window.location.origin + '/api/ping', {
                method: 'GET',
                cache: 'no-cache',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => {
                if (response.ok || response.status === 404) {
                    // Connexion rétablie
                    statusEl.innerHTML = '<span class="status-dot online"></span><span>Connexion rétablie</span>';
                    statusEl.classList.add('online');
                    
                    container.classList.add('reconnecting');
                    
                    setTimeout(() => {
                        window.location.href = '/';
                    }, 800);
                } else {
                    throw new Error('No connection');
                }
            })
            .catch(() => {
                btn.classList.remove('checking');
                
                // Petit effet de secousse pour indiquer l'échec
                container.style.animation = 'none';
                setTimeout(() => {
                    container.style.animation = 'shake 0.5s ease-out';
                }, 10);
            });
        }

        // Vérification automatique toutes les 5 secondes
        let autoCheckInterval = setInterval(() => {
            if (navigator.onLine) {
                fetch(window.location.origin + '/api/ping', {
                    method: 'GET',
                    cache: 'no-cache'
                })
                .then(response => {
                    if (response.ok || response.status === 404) {
                        clearInterval(autoCheckInterval);
                        const statusEl = document.getElementById('connectionStatus');
                        statusEl.innerHTML = '<span class="status-dot online"></span><span>Connexion rétablie</span>';
                        statusEl.classList.add('online');
                        
                        setTimeout(() => {
                            window.location.href = '/';
                        }, 1000);
                    }
                })
                .catch(() => {});
            }
        }, 5000);

        // Écouter les événements online/offline du navigateur
        window.addEventListener('online', () => {
            retryConnection();
        });

        // Animation de secousse
        const style = document.createElement('style');
        style.textContent = `
            @keyframes shake {
                0%, 100% { transform: translateX(0); }
                25% { transform: translateX(-10px); }
                75% { transform: translateX(10px); }
            }
        `;
        document.head.appendChild(style);
    </script>
</body>
</html>
