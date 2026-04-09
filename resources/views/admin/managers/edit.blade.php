@extends('layouts.admin')

@section('title', 'Modifier le Manager')

@section('css')
@include('admin.partials._shared-styles')
@endsection

@section('content')
<div class="container-fluid om-animate">
    <div class="om-page-header">
        <div>
            <h1 class="om-page-title">Modifier le Manager</h1>
            <p class="om-page-subtitle">Modifier les informations de {{ $manager->name }}</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.managers.show', $manager) }}" class="om-btn om-btn-ghost om-btn-sm">
                <i class="fas fa-eye"></i> Voir
            </a>
            <a href="{{ route('admin.managers.index') }}" class="om-btn om-btn-ghost om-btn-sm">
                <i class="fas fa-arrow-left"></i> Retour
            </a>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="om-card">
                <div class="om-card-header" style="background: linear-gradient(135deg, var(--om-primary), var(--om-primary-dark)); border: none;">
                    <div class="d-flex align-items-center gap-3">
                        <div class="om-avatar om-avatar-lg" style="background: rgba(255,255,255,0.2); border: 2px solid rgba(255,255,255,0.3);">
                            {{ strtoupper(substr($manager->name, 0, 1)) }}
                        </div>
                        <div>
                            <h5 class="mb-0 text-white fw-bold">{{ $manager->name }}</h5>
                            <small class="text-white-50">{{ $manager->email }}</small>
                        </div>
                    </div>
                    <span class="om-badge {{ $manager->is_active ? 'om-badge-success' : 'om-badge-danger' }}" style="background: rgba(255,255,255,0.2); color: white;">
                        {{ $manager->is_active ? 'Actif' : 'Inactif' }}
                    </span>
                </div>

                <div class="om-card-body">
                    <form method="POST" action="{{ route('admin.managers.update', $manager) }}">
                        @csrf
                        @method('PUT')

                        <div class="om-form-section">
                            <div class="om-form-section-title"><i class="fas fa-user"></i> Informations personnelles</div>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="om-form-group">
                                        <label class="om-form-label">Nom complet <span class="text-danger">*</span></label>
                                        <input type="text" class="om-form-input @error('name') is-invalid @enderror"
                                               name="name" value="{{ old('name', $manager->name) }}" required>
                                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="om-form-group">
                                        <label class="om-form-label">Adresse email <span class="text-danger">*</span></label>
                                        <input type="email" class="om-form-input @error('email') is-invalid @enderror"
                                               name="email" value="{{ old('email', $manager->email) }}" required>
                                        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="om-form-group">
                                        <label class="om-form-label">Numéro de téléphone</label>
                                        <input type="tel" class="om-form-input @error('phone') is-invalid @enderror"
                                               name="phone" value="{{ old('phone', $manager->phone) }}" placeholder="+216 XX XXX XXX">
                                        @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="om-form-section">
                            <div class="om-form-section-title"><i class="fas fa-key"></i> Modifier le mot de passe</div>
                            <div class="p-3 mb-3" style="background: var(--om-info-light); border-radius: var(--om-radius-sm); color: var(--om-info); font-size: 0.875rem;">
                                <i class="fas fa-info-circle me-2"></i>Laissez vide si vous ne souhaitez pas modifier le mot de passe
                            </div>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="om-form-group">
                                        <label class="om-form-label">Nouveau mot de passe</label>
                                        <div class="input-group">
                                            <input type="password" class="form-control @error('password') is-invalid @enderror"
                                                   id="password" name="password" style="border-radius: var(--om-radius-sm) 0 0 var(--om-radius-sm);">
                                            <button class="btn btn-outline-secondary" type="button" onclick="togglePwd('password')">
                                                <i class="fas fa-eye" id="passwordIcon"></i>
                                            </button>
                                        </div>
                                        @error('password')<div class="text-danger" style="font-size: 0.8rem;">{{ $message }}</div>@enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="om-form-group">
                                        <label class="om-form-label">Confirmer</label>
                                        <div class="input-group">
                                            <input type="password" class="form-control"
                                                   id="password_confirmation" name="password_confirmation" style="border-radius: var(--om-radius-sm) 0 0 var(--om-radius-sm);">
                                            <button class="btn btn-outline-secondary" type="button" onclick="togglePwd('password_confirmation')">
                                                <i class="fas fa-eye" id="password_confirmationIcon"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="om-form-section">
                            <div class="om-form-section-title"><i class="fas fa-cog"></i> Paramètres du compte</div>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1"
                                               {{ old('is_active', $manager->is_active) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_active">
                                            <strong>Compte actif</strong><br>
                                            <small style="color: var(--om-gray-500);">Le manager peut se connecter</small>
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="p-3" style="background: var(--om-gray-50); border-radius: var(--om-radius-sm); border: 1px solid var(--om-gray-200);">
                                        <div class="d-flex justify-content-between mb-2"><small style="color: var(--om-gray-500);">Employés gérés</small><span class="om-badge om-badge-info">{{ $manager->employees()->count() }}</span></div>
                                        <div class="d-flex justify-content-between mb-2"><small style="color: var(--om-gray-500);">Créé le</small><small>{{ $manager->created_at->format('d/m/Y') }}</small></div>
                                        <div class="d-flex justify-content-between"><small style="color: var(--om-gray-500);">Modifié le</small><small>{{ $manager->updated_at->format('d/m/Y') }}</small></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="om-form-section">
                            <div class="om-form-section-title"><i class="fas fa-shield-alt"></i> Permissions</div>
                            @php
                                $perms = $manager->permissions ?? [];
                                $defaults = \App\Models\Admin::DEFAULT_PERMISSIONS;
                                $permLabels = [
                                    'can_manage_orders'        => ['label' => 'Gérer les commandes',      'icon' => 'fa-shopping-cart'],
                                    'can_process_orders'       => ['label' => 'Traiter les commandes',    'icon' => 'fa-tasks'],
                                    'can_manage_products'      => ['label' => 'Gérer les produits',       'icon' => 'fa-box'],
                                    'can_manage_stock'         => ['label' => 'Gérer le stock',           'icon' => 'fa-warehouse'],
                                    'can_manage_users'         => ['label' => 'Gérer les utilisateurs',  'icon' => 'fa-users'],
                                    'can_view_stats'           => ['label' => 'Voir les statistiques',    'icon' => 'fa-chart-bar'],
                                    'can_manage_delivery'      => ['label' => 'Livraison (Kolixy)',        'icon' => 'fa-truck'],
                                    'can_import'               => ['label' => 'Importer des commandes',  'icon' => 'fa-file-import'],
                                    'can_manage_settings'      => ['label' => 'Paramètres',              'icon' => 'fa-cog'],
                                    'can_manage_integrations'  => ['label' => 'Intégrations',            'icon' => 'fa-plug'],
                                ];
                            @endphp
                            <div class="row g-3">
                                @foreach($permLabels as $key => $info)
                                <div class="col-md-6">
                                    <div class="form-check form-switch p-3" style="background: var(--om-gray-50); border-radius: var(--om-radius-sm); border: 1px solid var(--om-gray-200); padding-left: 3rem !important;">
                                        <input class="form-check-input" type="checkbox"
                                               id="perm_{{ $key }}"
                                               name="permissions[{{ $key }}]"
                                               value="1"
                                               {{ old('permissions.'.$key, $perms[$key] ?? $defaults[$key] ?? false) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="perm_{{ $key }}">
                                            <i class="fas {{ $info['icon'] }} me-1" style="color: var(--om-primary);"></i>
                                            <strong>{{ $info['label'] }}</strong>
                                        </label>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>

                        @if($manager->employees()->count() > 0)
                        <div class="om-form-section">
                            <div class="om-form-section-title"><i class="fas fa-users"></i> Employés assignés ({{ $manager->employees()->count() }})</div>
                            <div class="row g-2">
                                @foreach($manager->employees as $employee)
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center gap-2 p-2" style="background: var(--om-gray-50); border-radius: var(--om-radius-sm); border: 1px solid var(--om-gray-200);">
                                        <div class="om-avatar om-avatar-sm" style="background: var(--om-success);">{{ strtoupper(substr($employee->name, 0, 1)) }}</div>
                                        <div class="flex-grow-1">
                                            <div class="fw-bold" style="font-size: 0.85rem;">{{ $employee->name }}</div>
                                            <small style="color: var(--om-gray-500);">{{ $employee->email }}</small>
                                        </div>
                                        <span class="om-badge {{ $employee->is_active ? 'om-badge-success' : 'om-badge-danger' }}" style="font-size: 0.65rem;">{{ $employee->is_active ? 'Actif' : 'Inactif' }}</span>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        @endif

                        <div class="d-flex justify-content-between align-items-center pt-3" style="border-top: 1px solid var(--om-gray-200);">
                            <button type="button" class="om-btn om-btn-danger om-btn-sm" data-bs-toggle="modal" data-bs-target="#deleteModal">
                                <i class="fas fa-trash-alt"></i> Supprimer
                            </button>
                            <div class="d-flex gap-2">
                                <a href="{{ route('admin.managers.index') }}" class="om-btn om-btn-ghost"><i class="fas fa-times"></i> Annuler</a>
                                <button type="submit" class="om-btn om-btn-primary"><i class="fas fa-save"></i> Sauvegarder</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content" style="border-radius: var(--om-radius); overflow: hidden;">
            <div class="modal-header" style="background: var(--om-danger); border: none;">
                <h5 class="modal-title text-white fw-bold"><i class="fas fa-exclamation-triangle me-2"></i>Confirmer la suppression</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Êtes-vous sûr de vouloir supprimer le manager <strong>{{ $manager->name }}</strong> ?</p>
                <div class="p-3" style="background: var(--om-warning-light); border-radius: var(--om-radius-sm); color: var(--om-warning);">
                    <i class="fas fa-exclamation-triangle me-2"></i><strong>Attention :</strong> Cette action est irréversible.
                    @if($manager->employees()->count() > 0)
                        <br>Ce manager supervise {{ $manager->employees()->count() }} employé(s) qui seront désassignés.
                    @endif
                </div>
            </div>
            <div class="modal-footer" style="border-top: 1px solid var(--om-gray-200);">
                <button type="button" class="om-btn om-btn-ghost" data-bs-dismiss="modal"><i class="fas fa-times"></i> Annuler</button>
                <form method="POST" action="{{ route('admin.managers.destroy', $manager) }}" class="d-inline">
                    @csrf @method('DELETE')
                    <button type="submit" class="om-btn om-btn-danger"><i class="fas fa-trash-alt"></i> Supprimer</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
function togglePwd(id) {
    const f = document.getElementById(id);
    const i = document.getElementById(id + 'Icon');
    if (f.type === 'password') { f.type = 'text'; i.classList.replace('fa-eye', 'fa-eye-slash'); }
    else { f.type = 'password'; i.classList.replace('fa-eye-slash', 'fa-eye'); }
}
</script>
@endsection
