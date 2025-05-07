<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Compte Expiré - Order Manager</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        body {
            height: 100vh;
            display: flex;
            align-items: center;
            background-color: #f8f9fc;
            font-family: 'Nunito', sans-serif;
        }
        
        .expired-container {
            max-width: 600px;
            width: 100%;
            margin: 0 auto;
        }
        
        .card {
            border: none;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
        }
        
        .card-header {
            background-color: #e74a3b;
            color: white;
            text-align: center;
            padding: 1.5rem;
            border-bottom: none;
        }
        
        .expired-icon {
            font-size: 2.5rem;
            margin-bottom: 0.5rem;
        }
        
        .card-body {
            padding: 2rem;
            text-align: center;
        }
        
        .btn-danger {
            background-color: #e74a3b;
            border-color: #e74a3b;
        }
        
        .btn-danger:hover {
            background-color: #be3c30;
            border-color: #be3c30;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="expired-container">
            <div class="card">
                <div class="card-header">
                    <div class="expired-icon">
                        <i class="fas fa-exclamation-circle"></i>
                    </div>
                    <h4 class="mb-0">Compte Expiré ou Inactif</h4>
                </div>
                <div class="card-body">
                    <h5 class="mb-4">Votre compte a été désactivé ou votre période d'essai a expiré.</h5>
                    <p class="mb-4">Veuillez contacter l'administrateur du système pour réactiver votre compte ou prolonger votre période d'essai.</p>
                    
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-sign-out-alt me-2"></i> Déconnexion
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>