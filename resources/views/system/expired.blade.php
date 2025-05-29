{{-- resources/views/system/expired.blade.php --}}
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Abonnement Expiré - Order Manager</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .expired-container {
            max-width: 600px;
            width: 100%;
            background: white;
            border-radius: 24px;
            padding: 48px;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        
        .expired-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #ef4444, #dc2626);
        }
        
        .expired-icon {
            width: 120px;
            height: 120px;
            background: linear-gradient(135deg, #fee2e2, #fecaca);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 32px;
            position: relative;
        }
        
        .expired-icon i {
            font-size: 3rem;
            color: #dc2626;
            animation: pulse 2s infinite;
        }
        
        .expired-title {
            font-size: 2.5rem;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 16px;
            letter-spacing: -0.025em;
        }
        
        .expired-subtitle {
            font-size: 1.25rem;
            color: #6b7280;
            margin-bottom: 32px;
            line-height: 1.6;
        }
        
        .expired-message {
            background: #fef2f2;
            border: 1px solid #fecaca;
            border-radius: 12px;
            padding: 24px;
            margin: 32px 0;
            text-align: left;
        }
        
        .expired-message h4 {
            color: #991b1b;
            font-weight: 600;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .expired-message p {
            color: #7f1d1d;
            margin: 0;
            line-height: 1.6;
        }
        
        .contact-info {
            background: #f8fafc;
            border-radius: 12px;
            padding: 24px;
            margin: 32px 0;
        }
        
        .contact-info h5 {
            color: #374151;
            font-weight: 600;
            margin-bottom: 16px;
        }
        
        .contact-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 8px 0;
            color: #6b7280;
        }
        
        .contact-item i {
            width: 20px;
            text-align: center;
            color: #6366f1;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #6366f1, #4f46e5);
            border: none;
            padding: 16px 32px;
            border-radius: 12px;
            font-weight: 600;
            font-size: 1.1rem;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(99, 102, 241, 0.3);
        }
        
        .btn-outline-secondary {
            border: 2px solid #e5e7eb;
            color: #6b7280;
            padding: 12px 24px;
            border-radius: 12px;
            font-weight: 500;
            text-decoration: none;
            display: inline-block;
            margin-left: 16px;
            transition: all 0.3s ease;
        }
        
        .btn-outline-secondary:hover {
            background: #f3f4f6;
            color: #374151;
            text-decoration: none;
        }
        
        @keyframes pulse {
            0%, 100% {
                transform: scale(1);
            }
            50% {
                transform: scale(1.05);
            }
        }
        
        @media (max-width: 640px) {
            .expired-container {
                padding: 32px 24px;
            }
            
            .expired-title {
                font-size: 2rem;
            }
            
            .expired-subtitle {
                font-size: 1.1rem;
            }
            
            .btn-outline-secondary {
                margin-left: 0;
                margin-top: 12px;
                display: block;
                text-align: center;
            }
        }
    </style>
</head>
<body>
    <div class="expired-container">
        <div class="expired-icon">
            <i class="fas fa-calendar-times"></i>
        </div>
        
        <h1 class="expired-title">Abonnement Expiré</h1>
        <p class="expired-subtitle">
            Votre accès à Order Manager a expiré
        </p>
        
        <div class="expired-message">
            <h4>
                <i class="fas fa-exclamation-triangle"></i>
                Accès Suspendu
            </h4>
            <p>
                Votre abonnement Order Manager a expiré. Pour continuer à utiliser toutes les fonctionnalités 
                de la plateforme, veuillez renouveler votre abonnement en contactant notre équipe support.
            </p>
        </div>
        
        <div class="contact-info">
            <h5>Contactez-nous pour renouveler</h5>
            <div class="contact-item">
                <i class="fas fa-envelope"></i>
                <span>support@ordermanager.com</span>
            </div>
            <div class="contact-item">
                <i class="fas fa-phone"></i>
                <span>+216 XX XXX XXX</span>
            </div>
            <div class="contact-item">
                <i class="fas fa-clock"></i>
                <span>Lun-Ven: 9h00 - 18h00</span>
            </div>
        </div>
        
        <div class="mt-4">
            <a href="mailto:support@ordermanager.com" class="btn btn-primary">
                <i class="fas fa-envelope me-2"></i>
                Contacter le Support
            </a>
            <a href="{{ route('admin.login') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i>
                Retour à la connexion
            </a>
        </div>
        
        <div class="mt-4">
            <small class="text-muted">
                Order Manager © {{ date('Y') }} - Tous droits réservés
            </small>
        </div>
    </div>
</body>
</html>

{{-- ================================================================ --}}
{{-- resources/views/system/maintenance.blade.php --}}
{{-- ================================================================ --}}

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Maintenance - Order Manager</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .maintenance-container {
            max-width: 700px;
            width: 100%;
            background: white;
            border-radius: 24px;
            padding: 48px;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        
        .maintenance-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #f59e0b, #d97706);
        }
        
        .maintenance-icon {
            width: 140px;
            height: 140px;
            background: linear-gradient(135deg, #fef3c7, #fde047);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 32px;
            position: relative;
        }
        
        .maintenance-icon i {
            font-size: 3.5rem;
            color: #d97706;
            animation: rotate 3s linear infinite;
        }
        
        .maintenance-title {
            font-size: 2.5rem;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 16px;
            letter-spacing: -0.025em;
        }
        
        .maintenance-subtitle {
            font-size: 1.25rem;
            color: #6b7280;
            margin-bottom: 32px;
            line-height: 1.6;
        }
        
        .maintenance-message {
            background: #fffbeb;
            border: 1px solid #fde047;
            border-radius: 12px;
            padding: 24px;
            margin: 32px 0;
            text-align: left;
        }
        
        .maintenance-message h4 {
            color: #92400e;
            font-weight: 600;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .maintenance-message p {
            color: #78350f;
            margin: 0;
            line-height: 1.6;
        }
        
        .features-list {
            background: #f8fafc;
            border-radius: 12px;
            padding: 24px;
            margin: 32px 0;
            text-align: left;
        }
        
        .features-list h5 {
            color: #374151;
            font-weight: 600;
            margin-bottom: 16px;
            text-align: center;
        }
        
        .feature-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 8px 0;
            color: #6b7280;
        }
        
        .feature-item i {
            width: 20px;
            text-align: center;
            color: #10b981;
        }
        
        .progress-container {
            margin: 32px 0;
        }
        
        .progress {
            height: 8px;
            border-radius: 4px;
            background: #f3f4f6;
            overflow: hidden;
        }
        
        .progress-bar {
            background: linear-gradient(90deg, #10b981, #059669);
            border-radius: 4px;
            animation: progressAnimation 2s ease-in-out infinite;
        }
        
        .eta-info {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 12px;
            font-size: 0.9rem;
            color: #6b7280;
        }
        
        .status-indicator {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: #dcfce7;
            color: #166534;
            padding: 8px 16px;
            border-radius: 20px;
            font-weight: 500;
            font-size: 0.9rem;
        }
        
        .status-dot {
            width: 8px;
            height: 8px;
            background: #16a34a;
            border-radius: 50%;
            animation: pulse 2s infinite;
        }
        
        @keyframes rotate {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
        
        @keyframes progressAnimation {
            0% { width: 30%; }
            50% { width: 80%; }
            100% { width: 30%; }
        }
        
        @keyframes pulse {
            0%, 100% {
                opacity: 1;
                transform: scale(1);
            }
            50% {
                opacity: 0.5;
                transform: scale(1.2);
            }
        }
        
        @media (max-width: 640px) {
            .maintenance-container {
                padding: 32px 24px;
            }
            
            .maintenance-title {
                font-size: 2rem;
            }
            
            .maintenance-subtitle {
                font-size: 1.1rem;
            }
            
            .eta-info {
                flex-direction: column;
                gap: 8px;
                text-align: center;
            }
        }
    </style>
</head>
<body>
    <div class="maintenance-container">
        <div class="maintenance-icon">
            <i class="fas fa-cog"></i>
        </div>
        
        <h1 class="maintenance-title">Maintenance en Cours</h1>
        <p class="maintenance-subtitle">
            Nous améliorons Order Manager pour vous offrir une meilleure expérience
        </p>
        
        <div class="status-indicator">
            <div class="status-dot"></div>
            Système en maintenance
        </div>
        
        <div class="maintenance-message">
            <h4>
                <i class="fas fa-tools"></i>
                Mise à jour en cours
            </h4>
            <p>
                Notre équipe technique effectue actuellement une maintenance programmée pour améliorer 
                les performances et ajouter de nouvelles fonctionnalités à la plateforme Order Manager.
            </p>
        </div>
        
        <div class="features-list">
            <h5>Améliorations en cours :</h5>
            <div class="feature-item">
                <i class="fas fa-check"></i>
                <span>Optimisation de la base de données</span>
            </div>
            <div class="feature-item">
                <i class="fas fa-check"></i>
                <span>Amélioration des performances</span>
            </div>
            <div class="feature-item">
                <i class="fas fa-check"></i>
                <span>Nouvelles fonctionnalités de gestion</span>
            </div>
            <div class="feature-item">
                <i class="fas fa-check"></i>
                <span>Corrections de bugs</span>
            </div>
        </div>
        
        <div class="progress-container">
            <div class="progress">
                <div class="progress-bar" style="width: 65%;"></div>
            </div>
            <div class="eta-info">
                <span>Progression: 65%</span>
                <span>Temps estimé: ~30 minutes</span>
            </div>
        </div>
        
        <div class="mt-4">
            <p class="text-muted mb-3">
                La maintenance devrait être terminée sous peu. 
                Nous vous remercions de votre patience.
            </p>
            <button onclick="location.reload()" class="btn btn-primary">
                <i class="fas fa-sync-alt me-2"></i>
                Actualiser la page
            </button>
        </div>
        
        <div class="mt-4">
            <small class="text-muted">
                Pour toute urgence, contactez-nous à : support@ordermanager.com
            </small>
        </div>
    </div>
    
    <script>
        // Auto-refresh toutes les 30 secondes
        setTimeout(function() {
            location.reload();
        }, 30000);
    </script>
</body>
</html>