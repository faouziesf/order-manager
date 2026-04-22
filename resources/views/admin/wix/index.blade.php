@extends('layouts.admin')
@section('title', 'Wix Integration')

@section('content')
<div class="container py-4">
    <div class="row mb-4">
        <div class="col-md-8">
            <h2><i class="fas fa-link me-2"></i>Wix Integration</h2>
            <p class="text-muted">Synchronisez vos commandes Wix automatiquement</p>
        </div>
    </div>

    @if($syncStats['active_integrations'] > 0)
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-shopping-cart text-primary" style="font-size:2rem;"></i>
                        <div class="ms-3">
                            <small class="text-muted d-block">Commandes Wix</small>
                            <strong>{{ $syncStats['total_orders'] }}</strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-plug text-success" style="font-size:2rem;"></i>
                        <div class="ms-3">
                            <small class="text-muted d-block">Intégrations Actives</small>
                            <strong>{{ $syncStats['active_integrations'] }}</strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-cogs text-info" style="font-size:2rem;"></i>
                        <div class="ms-3">
                            <small class="text-muted d-block">Total</small>
                            <strong>{{ $syncStats['total_integrations'] }}</strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Intégrations existantes -->
    @if($integrations->isNotEmpty())
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-light">
            <h5 class="mb-0">Vos intégrations Wix</h5>
        </div>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Site</th>
                        <th>Statut</th>
                        <th>Dernière synchro</th>
                        <th>Commandes</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($integrations as $int)
                    <tr>
                        <td>
                            <strong>{{ $int->site_display_name ?? 'Wix Site ' . substr($int->account_id, 0, 8) }}</strong>
                            <br>
                            <small class="text-muted">ID: {{ $int->account_id }}</small>
                        </td>
                        <td>
                            @if($int->is_active)
                                <span class="badge bg-success"><i class="fas fa-check me-1"></i>Actif</span>
                            @else
                                <span class="badge bg-secondary"><i class="fas fa-times me-1"></i>Inactif</span>
                            @endif
                        </td>
                        <td>
                            @if($int->last_sync_at)
                                <small>{{ $int->last_sync_at->diffForHumans() }}</small><br>
                                <small class="text-muted">{{ $int->last_sync_status ?? '-' }}</small>
                            @else
                                <small class="text-muted">Jamais synchronisé</small>
                            @endif
                        </td>
                        <td>
                            <strong>{{ \App\Models\Order::where('admin_id', $int->admin_id)->where('external_source', 'wix')->where('external_id', 'LIKE', '%' . $int->account_id . '%')->count() }}</strong>
                        </td>
                        <td>
                            @if($int->is_active)
                            <button class="btn btn-sm btn-outline-primary" onclick="syncNow({{ $int->id }})">
                                <i class="fas fa-sync me-1"></i>Synchroniser
                            </button>
                            @endif
                            <button class="btn btn-sm btn-outline-secondary" onclick="toggleIntegration({{ $int->id }})">
                                <i class="fas fa-{{ $int->is_active ? 'pause' : 'play' }} me-1"></i>{{ $int->is_active ? 'Pause' : 'Reprendre' }}
                            </button>
                            <button class="btn btn-sm btn-outline-danger" onclick="deleteIntegration({{ $int->id }})">
                                <i class="fas fa-trash me-1"></i>Supprimer
                            </button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    <!-- Formulaire d'ajout -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-light">
            <h5 class="mb-0"><i class="fas fa-plus me-2"></i>Ajouter une intégration Wix</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.wix.store') }}" method="POST">
                @csrf
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">ID du compte Wix <span class="text-danger">*</span></label>
                        <input type="text" name="account_id" class="form-control" value="{{ old('account_id', $newIntegration->account_id) }}" required placeholder="e.g., 1234567890">
                        <small class="text-muted">Trouvez votre Account ID dans les paramètres Wix</small>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Clé API Wix <span class="text-danger">*</span></label>
                        <input type="password" name="api_key" class="form-control" value="{{ old('api_key') }}" required placeholder="Votre clé API Wix">
                        <small class="text-muted">Générée depuis Wix Dev Console</small>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Nom du site (optionnel)</label>
                        <input type="text" name="site_display_name" class="form-control" value="{{ old('site_display_name') }}" placeholder="Mon site Wix">
                    </div>
                </div>

                <!-- Paramètres avancés -->
                <div class="card bg-light border-0 mt-3 mb-3">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="fas fa-sliders-h me-2"></i>Paramètres avancés de synchronisation</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Date de première synchronisation</label>
                                <input type="date" name="first_sync_date" class="form-control" value="{{ old('first_sync_date') }}">
                                <small class="text-muted">Laissez vide pour commencer dès maintenant</small>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Jour resynchronisation</label>
                                <select name="resync_day_of_week" class="form-select">
                                    <option value="">Aucun</option>
                                    <option value="0" {{ old('resync_day_of_week') === '0' ? 'selected' : '' }}>Dimanche</option>
                                    <option value="1" {{ old('resync_day_of_week') === '1' ? 'selected' : '' }}>Lundi</option>
                                    <option value="2" {{ old('resync_day_of_week') === '2' ? 'selected' : '' }}>Mardi</option>
                                    <option value="3" {{ old('resync_day_of_week') === '3' ? 'selected' : '' }}>Mercredi</option>
                                    <option value="4" {{ old('resync_day_of_week') === '4' ? 'selected' : '' }}>Jeudi</option>
                                    <option value="5" {{ old('resync_day_of_week') === '5' ? 'selected' : '' }}>Vendredi</option>
                                    <option value="6" {{ old('resync_day_of_week') === '6' ? 'selected' : '' }}>Samedi</option>
                                </select>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Heure resynchronisation</label>
                                <input type="time" name="resync_time" class="form-control" value="{{ old('resync_time', '02:00') }}">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-check mb-3">
                    <input type="checkbox" name="is_active" class="form-check-input" id="is_active" {{ old('is_active', true) ? 'checked' : '' }}>
                    <label class="form-check-label" for="is_active">
                        Activer cette intégration immédiatement
                    </label>
                </div>

                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-2"></i>Ajouter l'intégration
                </button>
            </form>
        </div>
    </div>
</div>

<script>
const CSRF = '{{ csrf_token() }}';

function syncNow(id) {
    if (!confirm('Synchroniser maintenant ?')) return;
    window.location.href = `{{ route('admin.wix.sync', ':id') }}`.replace(':id', id);
}

function toggleIntegration(id) {
    fetch(`{{ url('admin/wix/toggle') }}/${id}`, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': CSRF },
    })
    .then(r => r.json())
    .then(d => {
        if (d.success) location.reload();
    });
}

function deleteIntegration(id) {
    if (!confirm('Supprimer cette intégration ?')) return;
    fetch(`{{ url('admin/wix/delete') }}/${id}`, {
        method: 'DELETE',
        headers: { 'X-CSRF-TOKEN': CSRF },
    })
    .then(r => r.json())
    .then(d => {
        if (d.success) location.reload();
    });
}
</script>
@endsection
