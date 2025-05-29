{{-- resources/views/admin/debug/assignment.blade.php --}}
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Diagnostic d'Assignation - Order Manager</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background: #f8f9fa; }
        .debug-container { max-width: 1200px; margin: 2rem auto; padding: 0 1rem; }
        .debug-section { background: white; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); margin-bottom: 2rem; overflow: hidden; }
        .debug-header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 1.5rem; }
        .debug-content { padding: 1.5rem; }
        .status-badge { padding: 0.25rem 0.75rem; border-radius: 20px; font-size: 0.875rem; font-weight: 500; }
        .status-success { background: #d1fae5; color: #065f46; }
        .status-error { background: #fee2e2; color: #991b1b; }
        .status-warning { background: #fef3c7; color: #92400e; }
        pre { background: #f3f4f6; padding: 1rem; border-radius: 8px; font-size: 0.875rem; overflow-x: auto; }
        .table { margin-bottom: 0; }
        .table th { background: #f8f9fa; border-top: none; }
    </style>
</head>
<body>
    <div class="debug-container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h2 mb-0">
                <i class="fas fa-bug me-2"></i>
                Diagnostic d'Assignation
            </h1>
            <a href="{{ route('admin.orders.index') }}" class="btn btn-outline-primary">
                <i class="fas fa-arrow-left me-2"></i>Retour aux commandes
            </a>
        </div>

        <!-- Section Admin -->
        <div class="debug-section">
            <div class="debug-header">
                <h3 class="mb-0">
                    <i class="fas fa-user-shield me-2"></i>
                    Informations Admin
                </h3>
            </div>
            <div class="debug-content">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-sm">
                            <tr>
                                <th width="30%">ID Admin:</th>
                                <td>{{ $debug['admin']['id'] }}</td>
                            </tr>
                            <tr>
                                <th>Nom:</th>
                                <td>{{ $debug['admin']['name'] }}</td>
                            </tr>
                            <tr>
                                <th>Statut:</th>
                                <td>
                                    <span class="status-badge {{ $debug['admin']['is_active'] ? 'status-success' : 'status-error' }}">
                                        {{ $debug['admin']['is_active'] ? 'Actif' : 'Inactif' }}
                                    </span>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Section Employés -->
        <div class="debug-section">
            <div class="debug-header">
                <h3 class="mb-0">
                    <i class="fas fa-users me-2"></i>
                    Employés Actifs ({{ $debug['employees']['count'] }})
                </h3>
            </div>
            <div class="debug-content">
                @if($debug['employees']['count'] > 0)
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nom</th>
                                    <th>Statut</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($debug['employees']['list'] as $employee)
                                <tr>
                                    <td>{{ $employee['id'] }}</td>
                                    <td>{{ $employee['name'] }}</td>
                                    <td>
                                        <span class="status-badge {{ $employee['is_active'] ? 'status-success' : 'status-error' }}">
                                            {{ $employee['is_active'] ? 'Actif' : 'Inactif' }}
                                        </span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Aucun employé actif trouvé.</strong>
                        Vous devez créer des employés pour pouvoir assigner des commandes.
                    </div>
                @endif
            </div>
        </div>

        <!-- Section Commandes Non Assignées -->
        <div class="debug-section">
            <div class="debug-header">
                <h3 class="mb-0">
                    <i class="fas fa-shopping-cart me-2"></i>
                    Commandes Non Assignées ({{ $debug['unassigned_orders']['count'] }})
                </h3>
            </div>
            <div class="debug-content">
                @if($debug['unassigned_orders']['count'] > 0)
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Client</th>
                                    <th>Statut</th>
                                    <th>Date de création</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($debug['unassigned_orders']['sample'] as $order)
                                <tr>
                                    <td>#{{ str_pad($order['id'], 6, '0', STR_PAD_LEFT) }}</td>
                                    <td>{{ $order['customer_phone'] }}</td>
                                    <td>
                                        <span class="badge bg-secondary">{{ ucfirst($order['status']) }}</span>
                                    </td>
                                    <td>{{ $order['created_at'] }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @if($debug['unassigned_orders']['count'] > 3)
                        <small class="text-muted">
                            <i class="fas fa-info-circle me-1"></i>
                            Affichage des 3 premières commandes sur {{ $debug['unassigned_orders']['count'] }} au total.
                        </small>
                    @endif
                @else
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Aucune commande non assignée.</strong>
                        Toutes les commandes sont déjà assignées ou il n'y a pas de commandes.
                    </div>
                @endif
            </div>
        </div>

        <!-- Test d'Assignation -->
        @if(isset($debug['assignment_test']))
        <div class="debug-section">
            <div class="debug-header">
                <h3 class="mb-0">
                    <i class="fas fa-flask me-2"></i>
                    Test d'Assignation
                </h3>
            </div>
            <div class="debug-content">
                @if($debug['assignment_test']['can_assign'])
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle me-2"></i>
                        <strong>Test réussi !</strong>
                        {{ $debug['assignment_test']['message'] }}
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Détails du test:</h6>
                            <ul class="list-unstyled">
                                <li><strong>Employé:</strong> {{ $debug['assignment_test']['employee_name'] }} (ID: {{ $debug['assignment_test']['employee_id'] }})</li>
                                <li><strong>Commande:</strong> #{{ $debug['assignment_test']['order_id'] }}</li>
                            </ul>
                        </div>
                    </div>
                @else
                    <div class="alert alert-danger">
                        <i class="fas fa-times-circle me-2"></i>
                        <strong>Erreur lors du test:</strong>
                        {{ $debug['assignment_test']['error'] }}
                    </div>
                @endif
            </div>
        </div>
        @endif

        <!-- Structure de Base de Données -->
        <div class="debug-section">
            <div class="debug-header">
                <h3 class="mb-0">
                    <i class="fas fa-database me-2"></i>
                    Structure de Base de Données
                </h3>
            </div>
            <div class="debug-content">
                @if(isset($debug['database_structure']['error']))
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Erreur:</strong> {{ $debug['database_structure']['error'] }}
                    </div>
                @else
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Tables:</h6>
                            <ul class="list-unstyled">
                                <li>
                                    <span class="status-badge {{ $debug['database_structure']['orders_table_exists'] ? 'status-success' : 'status-error' }}">
                                        {{ $debug['database_structure']['orders_table_exists'] ? '✓' : '✗' }}
                                    </span>
                                    Table 'orders'
                                </li>
                                <li>
                                    <span class="status-badge {{ $debug['database_structure']['employees_table_exists'] ? 'status-success' : 'status-error' }}">
                                        {{ $debug['database_structure']['employees_table_exists'] ? '✓' : '✗' }}
                                    </span>
                                    Table 'employees'
                                </li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h6>Colonnes 'orders':</h6>
                            <ul class="list-unstyled">
                                <li>
                                    <span class="status-badge {{ $debug['database_structure']['order_has_employee_id'] ? 'status-success' : 'status-error' }}">
                                        {{ $debug['database_structure']['order_has_employee_id'] ? '✓' : '✗' }}
                                    </span>
                                    Colonne 'employee_id'
                                </li>
                                <li>
                                    <span class="status-badge {{ $debug['database_structure']['order_has_is_assigned'] ? 'status-success' : 'status-error' }}">
                                        {{ $debug['database_structure']['order_has_is_assigned'] ? '✓' : '✗' }}
                                    </span>
                                    Colonne 'is_assigned'
                                </li>
                            </ul>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Routes -->
        <div class="debug-section">
            <div class="debug-header">
                <h3 class="mb-0">
                    <i class="fas fa-route me-2"></i>
                    Routes Requises
                </h3>
            </div>
            <div class="debug-content">
                <div class="row">
                    <div class="col-md-6">
                        <ul class="list-unstyled">
                            <li>
                                <span class="status-badge {{ $debug['routes']['bulk_assign_exists'] ? 'status-success' : 'status-error' }}">
                                    {{ $debug['routes']['bulk_assign_exists'] ? '✓' : '✗' }}
                                </span>
                                Route 'admin.orders.bulk-assign'
                            </li>
                            <li>
                                <span class="status-badge {{ $debug['routes']['unassign_exists'] ? 'status-success' : 'status-error' }}">
                                    {{ $debug['routes']['unassign_exists'] ? '✓' : '✗' }}
                                </span>
                                Route 'admin.orders.unassign'
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Permissions -->
        @if(isset($debug['permissions']))
        <div class="debug-section">
            <div class="debug-header">
                <h3 class="mb-0">
                    <i class="fas fa-shield-alt me-2"></i>
                    Permissions
                </h3>
            </div>
            <div class="debug-content">
                @if(isset($debug['permissions']['error']))
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Erreur:</strong> {{ $debug['permissions']['error'] }}
                    </div>
                @else
                    <div class="row">
                        <div class="col-md-6">
                            <ul class="list-unstyled">
                                <li>
                                    <span class="status-badge {{ $debug['permissions']['can_view'] ? 'status-success' : 'status-error' }}">
                                        {{ $debug['permissions']['can_view'] ? '✓' : '✗' }}
                                    </span>
                                    Peut voir les commandes
                                </li>
                                <li>
                                    <span class="status-badge {{ $debug['permissions']['can_update'] ? 'status-success' : 'status-error' }}">
                                        {{ $debug['permissions']['can_update'] ? '✓' : '✗' }}
                                    </span>
                                    Peut modifier les commandes
                                </li>
                            </ul>
                        </div>
                    </div>
                @endif
            </div>
        </div>
        @endif

        <!-- Données complètes (pour développement) -->
        <div class="debug-section">
            <div class="debug-header">
                <h3 class="mb-0">
                    <i class="fas fa-code me-2"></i>
                    Données Brutes (Debug)
                </h3>
            </div>
            <div class="debug-content">
                <pre>{{ json_encode($debug, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
            </div>
        </div>

        <!-- Recommandations -->
        <div class="debug-section">
            <div class="debug-header">
                <h3 class="mb-0">
                    <i class="fas fa-lightbulb me-2"></i>
                    Recommandations
                </h3>
            </div>
            <div class="debug-content">
                <div class="alert alert-info">
                    <h6><i class="fas fa-info-circle me-2"></i>Étapes de résolution:</h6>
                    <ol>
                        <li>Vérifiez que toutes les routes sont bien définies dans <code>routes/web.php</code></li>
                        <li>Vérifiez que les méthodes du contrôleur sont bien présentes</li>
                        <li>Vérifiez que les colonnes de base de données existent</li>
                        <li>Testez l'assignation avec ce diagnostic</li>
                        <li>Vérifiez les logs dans <code>storage/logs/laravel.log</code></li>
                    </ol>
                </div>
                
                <div class="alert alert-warning">
                    <h6><i class="fas fa-exclamation-triangle me-2"></i>Important:</h6>
                    <p class="mb-0">
                        Supprimez cette route de debug <code>/admin/debug-assignment</code> 
                        une fois le problème résolu pour des raisons de sécurité.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>