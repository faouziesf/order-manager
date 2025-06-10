<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Compte Expiré - Order Manager</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --error-color: #dc2626;
            --error-light: #fef2f2;
            --error-dark: #991b1b;
            --warning-color: #d97706;
            --warning-light: #fffbeb;
            --gray-50: #f9fafb;
            --gray-100: #f3f4f6;
            --gray-400: #9ca3af;
            --gray-600: #4b5563;
            --gray-700: #374151;
            --gray-800: #1f2937;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 50%, #fecaca 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }

        .expired-container {
            background: white;
            border-radius: 24px;
            box-shadow: 0 25px 50px rgba(220, 38, 38, 0.15);
            width: 100%;
            max-width: 600px;
            overflow: hidden;
            border: 1px solid rgba(220, 38, 38, 0.1);
        }

        .expired-header {
            background: linear-gradient(135deg, var(--error-color) 0%, var(--error-dark) 100%);
            color: white;
            padding: 3rem 2rem 2rem;
            text-align: center;
            position: relative;
        }

        .expired-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="25" cy="25" r="1" fill="white" opacity="0.1"/><circle cx="75" cy="75" r="1" fill="white" opacity="0.1"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
            opacity: 0.3;
        }

        .expired-icon {
            font-size: 4rem;
            margin-bottom: 1rem;
            display: inline-block;
            position: relative;
            z-index: 1;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.7; }
        }

        .expired-title {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            position: relative;
            z-index: 1;
        }

        .expired-subtitle {
            opacity: 0.9;
            font-size: 1.1rem;
            font-weight: 400;
            position: relative;
            z-index: 1;
        }

        .expired-body {
            padding: 3rem 2rem;
            text-align: center;
        }

        .status-message {
            background: var(--error-light);
            border: 2px solid rgba(220, 38, 38, 0.2);
            border-radius: 16px;
            padding: 2rem;
            margin-bottom: 2rem;
            position: relative;
        }

        .status-message .icon {
            font-size: 2.5rem;
            color: var(--error-color);
            margin-bottom: 1rem;
        }

        .status-message h5 {
            color: var(--error-dark);
            font-weight: 600;
            font-size: 1.25rem;
            margin-bottom: 1rem;
        }

        .status-message p {
            color: var(--gray-700);
            font-size: 1rem;
            line-height: 1.6;
            margin: 0;
        }

        .contact-info {
            background: var(--gray-50);
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            border-left: 4px solid var(--warning-color);
        }

        .contact-info h6 {
            color: var(--gray-800);
            font-weight: 600;
            margin-bottom: 0.75rem;
            font-size: 1rem;
        }

        .contact-info p {
            color: var(--gray-600);
            margin: 0;
            font-size: 0.9rem;
            line-height: 1.5;
        }

        .user-details {
            background: rgba(220, 38, 38, 0.05);
            border-radius: 12px;
            padding: 1.25rem;
            margin-bottom: 2rem;
            text-align: left;
        }

        .user-details .detail-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.5rem 0;
            border-bottom: 1px solid rgba(220, 38, 38, 0.1);
        }

        .user-details .detail-row:last-child {
            border-bottom: none;
        }

        .user-details .label {
            font-weight: 500;
            color: var(--gray-600);
            font-size: 0.9rem;
        }

        .user-details .value {
            font-weight: 600;
            color: var(--gray-800);
            font-size: 0.9rem;
        }

        .action-buttons {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
        }

        .btn {
            border-radius: 12px;
            padding: 0.75rem 2rem;
            font-weight: 600;
            font-size: 0.95rem;
            transition: all 0.3s ease;
            border: 2px solid transparent;
        }

        .btn-danger {
            background: linear-gradient(135deg, var(--error-color) 0%, var(--error-dark) 100%);
            color: white;
        }

        .btn-danger:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(220, 38, 38, 0.3);
            color: white;
        }

        .btn-outline-secondary {
            border-color: var(--gray-400);
            color: var(--gray-600);
            background: white;
        }

        .btn-outline-secondary:hover {
            background: var(--gray-100);
            border-color: var(--gray-600);
            color: var(--gray-800);
            transform: translateY(-1px);
        }

        .footer-info {
            text-align: center;
            margin-top: 2rem;
            padding-top: 2rem;
            border-top: 1px solid var(--gray-200);
            color: var(--gray-500);
            font-size: 0.85rem;
        }

        /* Animations */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .expired-container {
            animation: fadeInUp 0.6s ease-out;
        }

        /* Responsive */
        @media (max-width: 576px) {
            .expired-container {
                margin: 1rem;
                border-radius: 16px;
            }
            
            .expired-header {
                padding: 2rem 1.5rem 1.5rem;
            }
            
            .expired-body {
                padding: 2rem 1.5rem;
            }
            
            .expired-icon {
                font-size: 3rem;
            }
            
            .expired-title {
                font-size: 1.5rem;
            }
            
            .action-buttons {
                flex-direction: column;
            }
            
            .btn {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="expired-container">
        <div class="expired-header">
            <div class="expired-icon">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <h1 class="expired-title">Accès Refusé</h1>
            <p class="expired-subtitle">Votre compte n'est plus accessible</p>
        </div>
        
        <div class="expired-body">
            <div class="status-message">
                <div class="icon">
                    @if(session('expired_reason') === 'expired')
                        <i class="fas fa-clock"></i>
                    @elseif(session('expired_reason') === 'inactive')
                        <i class="fas fa-user-slash"></i>
                    @elseif(in_array(session('expired_reason'), ['admin_expired', 'admin_inactive']))
                        <i class="fas fa-building"></i>
                    @else
                        <i class="fas fa-ban"></i>
                    @endif
                </div>
                
                <h5>
                    @switch(session('expired_reason'))
                        @case('expired')
                            Période d'essai expirée
                            @break
                        @case('inactive')
                            Compte désactivé
                            @break
                        @case('admin_expired')
                            Abonnement administrateur expiré
                            @break
                        @case('admin_inactive')
                            Compte administrateur désactivé
                            @break
                        @default
                            Accès non autorisé
                    @endswitch
                </h5>
                
                <p>
                    @switch(session('expired_reason'))
                        @case('expired')
                            Votre période d'essai gratuite de 30 jours est arrivée à son terme. Pour continuer à utiliser Order Manager, veuillez contacter notre équipe pour renouveler votre abonnement.
                            @break
                        @case('inactive')
                            Votre compte a été temporairement désactivé. Cette action peut être due à une violation des conditions d'utilisation ou à une demande spécifique. Contactez l'administrateur pour plus d'informations.
                            @break
                        @case('admin_expired')
                            L'abonnement du compte administrateur principal a expiré, ce qui affecte l'accès de tous les utilisateurs associés. L'administrateur doit renouveler son abonnement.
                            @break
                        @case('admin_inactive')
                            Le compte administrateur principal a été désactivé, empêchant l'accès à tous les utilisateurs de cette organisation.
                            @break
                        @default
                            Une erreur empêche l'accès à votre compte. Veuillez contacter l'assistance technique pour résoudre ce problème.
                    @endswitch
                </p>
            </div>

            @if(session('user_name') || session('user_email'))
                <div class="user-details">
                    @if(session('user_name'))
                        <div class="detail-row">
                            <span class="label">
                                <i class="fas fa-user me-2"></i>Nom
                            </span>
                            <span class="value">{{ session('user_name') }}</span>
                        </div>
                    @endif
                    
                    @if(session('user_email'))
                        <div class="detail-row">
                            <span class="label">
                                <i class="fas fa-envelope me-2"></i>Email
                            </span>
                            <span class="value">{{ session('user_email') }}</span>
                        </div>
                    @endif
                    
                    @if(session('user_type'))
                        <div class="detail-row">
                            <span class="label">
                                <i class="fas fa-tag me-2"></i>Type de compte
                            </span>
                            <span class="value">
                                @switch(session('user_type'))
                                    @case('admin')
                                        Administrateur
                                        @break
                                    @case('manager')
                                        Manager
                                        @break
                                    @case('employee')
                                        Employé
                                        @break
                                    @default
                                        {{ ucfirst(session('user_type')) }}
                                @endswitch
                            </span>
                        </div>
                    @endif
                </div>
            @endif

            @if(in_array(session('expired_reason'), ['expired', 'inactive']))
                <div class="contact-info">
                    <h6>
                        <i class="fas fa-headset me-2"></i>
                        Besoin d'aide ?
                    </h6>
                    <p>
                        Contactez notre équipe support à <strong>support@ordermanager.com</strong> 
                        ou appelez le <strong>+216 XX XXX XXX</strong> pour réactiver votre compte 
                        ou discuter des options d'abonnement disponibles.
                    </p>
                </div>
            @endif

            <div class="action-buttons">
                <form action="{{ route('logout') }}" method="POST" style="display: inline;">
                    @csrf
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-sign-out-alt me-2"></i>
                        Retour à la connexion
                    </button>
                </form>
                
                <a href="mailto:support@ordermanager.com?subject=Demande de réactivation - {{ session('user_email') }}&body=Bonjour,%0D%0A%0D%0AJe souhaiterais réactiver mon compte Order Manager.%0D%0A%0D%0AEmail: {{ session('user_email') }}%0D%0ANom: {{ session('user_name') }}%0D%0ARaison: {{ session('expired_reason') }}%0D%0A%0D%0AMerci." 
                   class="btn btn-outline-secondary">
                    <i class="fas fa-envelope me-2"></i>
                    Contacter le support
                </a>
            </div>

            <div class="footer-info">
                <p>
                    <i class="fas fa-info-circle me-1"></i>
                    Cette page s'affiche pour protéger la sécurité de votre compte et de vos données.
                    <br>
                    <small>Order Manager - Gestion professionnelle des commandes</small>
                </p>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Auto-focus sur le bouton principal après 2 secondes
            setTimeout(() => {
                const primaryBtn = document.querySelector('.btn-danger');
                if (primaryBtn) {
                    primaryBtn.focus();
                }
            }, 2000);
            
            // Effet de typing pour les messages longs
            const message = document.querySelector('.status-message p');
            if (message && message.textContent.length > 100) {
                const text = message.textContent;
                message.textContent = '';
                message.style.opacity = '1';
                
                let i = 0;
                function typeWriter() {
                    if (i < text.length) {
                        message.textContent += text.charAt(i);
                        i++;
                        setTimeout(typeWriter, 20);
                    }
                }
                
                setTimeout(typeWriter, 1000);
            }
        });
    </script>
</body>
</html>