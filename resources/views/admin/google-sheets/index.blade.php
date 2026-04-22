@extends('layouts.admin')
@section('title', 'Google Sheets Integration')

@section('content')
<div class="container py-4">
    <div class="row mb-4">
        <div class="col-md-8">
            <h2><i class="fas fa-table me-2"></i>Google Sheets Automation</h2>
            <p class="text-muted">Synchronisez vos commandes depuis une feuille Google Sheets</p>
        </div>
    </div>

    @if($integration->id && $integration->is_active)
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-file-import text-success" style="font-size:2rem;"></i>
                        <div class="ms-3">
                            <small class="text-muted d-block">Total Importé</small>
                            <strong>{{ $stats['total_imported'] ?? 0 }}</strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-sync text-info" style="font-size:2rem;"></i>
                        <div class="ms-3">
                            <small class="text-muted d-block">Dernière synchro</small>
                            <strong>{{ $stats['last_sync'] ?? 'Jamais' }}</strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Configuration -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-light">
            <h5 class="mb-0"><i class="fas fa-cog me-2"></i>Configuration</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.google-sheets.store') }}" method="POST">
                @csrf

                <div class="alert alert-info" role="alert">
                    <h6><i class="fas fa-info-circle me-2"></i>Instructions</h6>
                    <ol style="margin: 0.5rem 0 0 1rem; padding: 0;">
                        <li>Créez une feuille Google Sheets avec vos commandes</li>
                        <li>Colonnes recommandées: <code>order_id</code>, <code>customer_name</code>, <code>customer_phone</code>, <code>customer_email</code>, <code>customer_address</code>, <code>customer_city</code>, <code>total_price</code></li>
                        <li>Pour import CSV: Publiez la feuille en tant que CSV (Fichier → Partager → Obtenir lien partageable)</li>
                        <li>Collez l'URL et activez la synchronisation</li>
                    </ol>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Type d'import</label>
                        <select name="import_type" class="form-select" onchange="updateImportType()">
                            <option value="published_csv" {{ old('import_type', $integration->import_type ?? 'published_csv') === 'published_csv' ? 'selected' : '' }}>
                                CSV Publié (sans authentification)
                            </option>
                            <option value="oauth2" {{ old('import_type', $integration->import_type) === 'oauth2' ? 'selected' : '' }} disabled>
                                OAuth2 (à venir)
                            </option>
                        </select>
                        <small class="text-muted">CSV publié est plus simple et sans authentification requise</small>
                    </div>
                </div>

                <div id="csv-section">
                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label class="form-label">URL CSV Publié <span class="text-danger">*</span></label>
                            <input type="url" name="csv_url" class="form-control" value="{{ old('csv_url', $integration->csv_url) }}" 
                                placeholder="https://docs.google.com/spreadsheets/d/.../export?format=csv&gid=0">
                            <small class="text-muted">Format: <code>https://docs.google.com/spreadsheets/d/SHEET_ID/export?format=csv&gid=0</gid></code></small>
                        </div>
                    </div>
                </div>

                <div id="oauth2-section" style="display:none;">
                    <div class="alert alert-warning">Authentification OAuth2 sera disponible prochainement</div>
                </div>

                <hr>

                <!-- Paramètres avancés -->
                <div class="card bg-light border-0 mb-3">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="fas fa-sliders-h me-2"></i>Paramètres avancés</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Date première synchro</label>
                                <input type="date" name="first_sync_date" class="form-control" value="{{ old('first_sync_date', $integration->first_sync_date?->format('Y-m-d')) }}">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Jour resync hebdo</label>
                                <select name="resync_day_of_week" class="form-select">
                                    <option value="">Aucun</option>
                                    <option value="0" {{ old('resync_day_of_week', $integration->resync_day_of_week) === '0' || old('resync_day_of_week', $integration->resync_day_of_week) === 0 ? 'selected' : '' }}>Dimanche</option>
                                    <option value="1" {{ old('resync_day_of_week', $integration->resync_day_of_week) === '1' || old('resync_day_of_week', $integration->resync_day_of_week) === 1 ? 'selected' : '' }}>Lundi</option>
                                    <option value="2" {{ old('resync_day_of_week', $integration->resync_day_of_week) === '2' || old('resync_day_of_week', $integration->resync_day_of_week) === 2 ? 'selected' : '' }}>Mardi</option>
                                    <option value="3" {{ old('resync_day_of_week', $integration->resync_day_of_week) === '3' || old('resync_day_of_week', $integration->resync_day_of_week) === 3 ? 'selected' : '' }}>Mercredi</option>
                                    <option value="4" {{ old('resync_day_of_week', $integration->resync_day_of_week) === '4' || old('resync_day_of_week', $integration->resync_day_of_week) === 4 ? 'selected' : '' }}>Jeudi</option>
                                    <option value="5" {{ old('resync_day_of_week', $integration->resync_day_of_week) === '5' || old('resync_day_of_week', $integration->resync_day_of_week) === 5 ? 'selected' : '' }}>Vendredi</option>
                                    <option value="6" {{ old('resync_day_of_week', $integration->resync_day_of_week) === '6' || old('resync_day_of_week', $integration->resync_day_of_week) === 6 ? 'selected' : '' }}>Samedi</option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Heure resync</label>
                                <input type="time" name="resync_time" class="form-control" value="{{ old('resync_time', $integration->resync_time?->format('H:i') ?? '03:00') }}">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-check form-switch mb-3">
                    <input type="checkbox" name="auto_sync" class="form-check-input" id="auto_sync" {{ old('auto_sync', $integration->auto_sync) ? 'checked' : '' }}>
                    <label class="form-check-label" for="auto_sync">
                        Synchroniser automatiquement selon les paramètres
                    </label>
                </div>

                <div class="form-check mb-3">
                    <input type="checkbox" name="is_active" class="form-check-input" id="is_active" {{ old('is_active', $integration->is_active) ? 'checked' : '' }}>
                    <label class="form-check-label" for="is_active">
                        Activer cette intégration
                    </label>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Sauvegarder
                    </button>
                    @if($integration->id && $integration->is_active)
                    <form action="{{ route('admin.google-sheets.sync-now') }}" method="POST" style="display:inline;">
                        @csrf
                        <button type="submit" class="btn btn-outline-primary">
                            <i class="fas fa-sync me-2"></i>Synchroniser maintenant
                        </button>
                    </form>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Historique -->
    @if($integration->id && $integration->last_sync_at)
    <div class="card border-0 shadow-sm mt-4">
        <div class="card-header bg-light">
            <h6 class="mb-0"><i class="fas fa-history me-2"></i>Historique</h6>
        </div>
        <div class="card-body">
            <table class="table table-sm mb-0">
                <tr>
                    <td><strong>Dernière sync</strong></td>
                    <td>{{ $integration->last_sync_at->format('d/m/Y H:i') }}</td>
                </tr>
                <tr>
                    <td><strong>Statut</strong></td>
                    <td>
                        @if($integration->last_sync_status === 'success')
                            <span class="badge bg-success">Succès</span>
                        @elseif($integration->last_sync_status === 'error')
                            <span class="badge bg-danger">Erreur</span>
                        @else
                            <span class="badge bg-secondary">{{ $integration->last_sync_status }}</span>
                        @endif
                    </td>
                </tr>
                <tr>
                    <td><strong>Importé / Mis à jour</strong></td>
                    <td>{{ $integration->total_imported }} / {{ $integration->total_updated }}</td>
                </tr>
                @if($integration->last_sync_error)
                <tr>
                    <td><strong>Erreur</strong></td>
                    <td><code style="font-size:0.8rem;">{{ $integration->last_sync_error }}</code></td>
                </tr>
                @endif
            </table>
        </div>
    </div>
    @endif
</div>

<script>
function updateImportType() {
    const type = document.querySelector('select[name="import_type"]').value;
    document.getElementById('csv-section').style.display = type === 'published_csv' ? 'block' : 'none';
    document.getElementById('oauth2-section').style.display = type === 'oauth2' ? 'block' : 'none';
}

updateImportType();
</script>
@endsection
