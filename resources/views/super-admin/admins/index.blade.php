@extends('layouts.super-admin')

@section('title', 'Administrateurs')
@section('page-title', 'Administrateurs')

@section('content')
    <!-- Stats -->
    <div class="sa-grid sa-grid-4" style="margin-bottom:24px">
        <div class="sa-stat">
            <div class="sa-stat-icon sa-stat-icon-primary"><i class="fas fa-building"></i></div>
            <div><div class="sa-stat-value">{{ $stats['total'] ?? 0 }}</div><div class="sa-stat-label">Total</div></div>
        </div>
        <div class="sa-stat">
            <div class="sa-stat-icon sa-stat-icon-success"><i class="fas fa-check-circle"></i></div>
            <div><div class="sa-stat-value">{{ $stats['active'] ?? 0 }}</div><div class="sa-stat-label">Actifs</div></div>
        </div>
        <div class="sa-stat">
            <div class="sa-stat-icon sa-stat-icon-danger"><i class="fas fa-times-circle"></i></div>
            <div><div class="sa-stat-value">{{ $stats['inactive'] ?? 0 }}</div><div class="sa-stat-label">Inactifs</div></div>
        </div>
        <div class="sa-stat">
            <div class="sa-stat-icon sa-stat-icon-warning"><i class="fas fa-clock"></i></div>
            <div><div class="sa-stat-value">{{ $stats['expired'] ?? 0 }}</div><div class="sa-stat-label">Expirés</div></div>
        </div>
    </div>

    <!-- Filters & Actions -->
    <div class="sa-card" style="margin-bottom:24px">
        <form method="GET" action="{{ route('super-admin.admins.index') }}" style="display:flex;align-items:center;gap:12px;flex-wrap:wrap">
            <input type="text" name="search" class="sa-input" placeholder="Rechercher par nom, email, boutique..." value="{{ request('search') }}" style="max-width:280px">
            <select name="status" class="sa-input sa-select" style="max-width:160px">
                <option value="">Tous les statuts</option>
                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Actifs</option>
                <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactifs</option>
                <option value="expired" {{ request('status') === 'expired' ? 'selected' : '' }}>Expirés</option>
            </select>
            <select name="subscription_type" class="sa-input sa-select" style="max-width:160px">
                <option value="">Tous abonnements</option>
                <option value="trial" {{ request('subscription_type') === 'trial' ? 'selected' : '' }}>Trial</option>
                <option value="basic" {{ request('subscription_type') === 'basic' ? 'selected' : '' }}>Basic</option>
                <option value="premium" {{ request('subscription_type') === 'premium' ? 'selected' : '' }}>Premium</option>
                <option value="enterprise" {{ request('subscription_type') === 'enterprise' ? 'selected' : '' }}>Enterprise</option>
            </select>
            <select name="confirmi" class="sa-input sa-select" style="max-width:160px">
                <option value="">Confirmi</option>
                <option value="active" {{ request('confirmi') === 'active' ? 'selected' : '' }}>Confirmi Actif</option>
                <option value="inactive" {{ request('confirmi') === 'inactive' ? 'selected' : '' }}>Confirmi Inactif</option>
            </select>
            <button type="submit" class="sa-btn sa-btn-primary"><i class="fas fa-search"></i> Filtrer</button>
            @if(request()->hasAny(['search','status','subscription_type','confirmi']))
                <a href="{{ route('super-admin.admins.index') }}" class="sa-btn sa-btn-outline"><i class="fas fa-times"></i></a>
            @endif
            <div style="margin-left:auto;display:flex;gap:8px">
                <a href="{{ route('super-admin.admins.create') }}" class="sa-btn sa-btn-primary"><i class="fas fa-plus"></i> Nouveau</a>
                <a href="{{ route('super-admin.admins.export.csv') }}?{{ http_build_query(request()->query()) }}" class="sa-btn sa-btn-outline sa-btn-sm"><i class="fas fa-download"></i> CSV</a>
            </div>
        </form>
    </div>

    <!-- Bulk actions form -->
    <form id="bulkForm" method="POST" action="{{ route('super-admin.admins.bulk-actions') }}">
        @csrf
        <input type="hidden" name="action" id="bulkAction">

        <!-- Admin Table -->
        <div class="sa-card">
            <div class="sa-card-header">
                <h3 class="sa-card-title">{{ $admins->total() }} administrateur(s)</h3>
                <div style="display:flex;gap:8px" id="bulkButtons" class="sa-hidden">
                    <button type="button" onclick="doBulk('activate')" class="sa-btn sa-btn-success sa-btn-sm"><i class="fas fa-check"></i> Activer</button>
                    <button type="button" onclick="doBulk('deactivate')" class="sa-btn sa-btn-warning sa-btn-sm"><i class="fas fa-ban"></i> Désactiver</button>
                    <button type="button" onclick="doBulk('delete')" class="sa-btn sa-btn-danger sa-btn-sm"><i class="fas fa-trash"></i> Supprimer</button>
                </div>
            </div>
            <div class="sa-table-wrap">
                <table class="sa-table">
                    <thead>
                        <tr>
                            <th><input type="checkbox" id="checkAll" onchange="toggleAll(this)"></th>
                            <th>
                                <a href="{{ route('super-admin.admins.index', array_merge(request()->query(), ['sort_by'=>'name','sort_direction'=> request('sort_by')==='name' && request('sort_direction')==='asc' ? 'desc' : 'asc'])) }}" style="color:inherit;text-decoration:none">
                                    Admin {!! request('sort_by') === 'name' ? (request('sort_direction') === 'asc' ? '↑' : '↓') : '' !!}
                                </a>
                            </th>
                            <th>Boutique</th>
                            <th>Abonnement</th>
                            <th>Expiration</th>
                            <th>Statut</th>
                            <th>Confirmi</th>
                            <th>Sous-comptes</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($admins as $admin)
                            <tr>
                                <td><input type="checkbox" name="admin_ids[]" value="{{ $admin->id }}" class="row-check" onchange="updateBulk()"></td>
                                <td>
                                    <div style="display:flex;align-items:center;gap:10px">
                                        <div class="sa-avatar sa-avatar-primary">{{ strtoupper(substr($admin->name,0,1)) }}</div>
                                        <div>
                                            <a href="{{ route('super-admin.admins.show', $admin) }}" style="font-weight:600;color:var(--sa-text);text-decoration:none">{{ $admin->name }}</a>
                                            <div style="font-size:.7rem;color:var(--sa-text-muted)">{{ $admin->email }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>{{ $admin->shop_name }}</td>
                                <td>
                                    <span class="sa-badge sa-badge-{{ $admin->subscription_type === 'premium' ? 'primary' : ($admin->subscription_type === 'enterprise' ? 'success' : ($admin->subscription_type === 'basic' ? 'info' : 'muted')) }}">
                                        {{ ucfirst($admin->subscription_type ?? 'trial') }}
                                    </span>
                                </td>
                                <td>
                                    @if($admin->expiry_date)
                                        <span style="font-size:.8rem;color:{{ $admin->expiry_date->isPast() ? 'var(--sa-danger)' : ($admin->expiry_date->diffInDays() <= 7 ? 'var(--sa-warning)' : 'var(--sa-text-secondary)') }}">
                                            {{ $admin->expiry_date->format('d/m/Y') }}
                                        </span>
                                    @else
                                        <span style="color:var(--sa-text-muted)">-</span>
                                    @endif
                                </td>
                                <td><span class="sa-badge sa-badge-{{ $admin->is_active ? 'success' : 'danger' }}">{{ $admin->is_active ? 'Actif' : 'Inactif' }}</span></td>
                                <td>
                                    @if($admin->confirmi_status === 'active')
                                        <span class="sa-badge sa-badge-success">Actif</span>
                                    @elseif($admin->confirmi_status === 'pending')
                                        <span class="sa-badge sa-badge-warning">En attente</span>
                                    @else
                                        <span class="sa-badge sa-badge-muted">-</span>
                                    @endif
                                </td>
                                <td style="font-size:.8rem;color:var(--sa-text-secondary)">
                                    <i class="fas fa-user-tie" style="color:var(--sa-primary)"></i> {{ $admin->managers_count ?? 0 }}
                                    &nbsp;
                                    <i class="fas fa-user" style="color:var(--sa-success)"></i> {{ $admin->employees_count ?? 0 }}
                                </td>
                                <td>
                                    <div style="display:flex;gap:4px">
                                        <a href="{{ route('super-admin.admins.show', $admin) }}" class="sa-btn sa-btn-outline sa-btn-icon sa-btn-sm" title="Voir"><i class="fas fa-eye"></i></a>
                                        <a href="{{ route('super-admin.admins.edit', $admin) }}" class="sa-btn sa-btn-outline sa-btn-icon sa-btn-sm" title="Modifier"><i class="fas fa-pen"></i></a>
                                        <form action="{{ route('super-admin.admins.login-as', $admin) }}" method="POST" style="display:inline" onsubmit="return confirm('Se connecter comme cet admin ?')">
                                            @csrf
                                            <button type="submit" class="sa-btn sa-btn-primary sa-btn-icon sa-btn-sm" title="Se connecter comme admin">
                                                <i class="fas fa-right-to-bracket"></i>
                                            </button>
                                        </form>
                                        <form action="{{ route('super-admin.admins.toggle-active', $admin) }}" method="POST" style="display:inline">
                                            @csrf @method('PATCH')
                                            <button type="submit" class="sa-btn sa-btn-icon sa-btn-sm {{ $admin->is_active ? 'sa-btn-warning' : 'sa-btn-success' }}" title="{{ $admin->is_active ? 'Désactiver' : 'Activer' }}">
                                                <i class="fas fa-{{ $admin->is_active ? 'ban' : 'check' }}"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="9"><div class="sa-empty"><i class="fas fa-building"></i><p>Aucun administrateur trouvé</p></div></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($admins->hasPages())
                <div class="sa-pagination">{{ $admins->links() }}</div>
            @endif
        </div>
    </form>
@endsection

@section('css')
<style>.sa-hidden{display:none!important}</style>
@endsection

@section('scripts')
<script>
function toggleAll(el){document.querySelectorAll('.row-check').forEach(c=>c.checked=el.checked);updateBulk()}
function updateBulk(){const c=document.querySelectorAll('.row-check:checked').length;document.getElementById('bulkButtons').classList.toggle('sa-hidden',c===0)}
function doBulk(action){if(!confirm('Êtes-vous sûr ?'))return;document.getElementById('bulkAction').value=action;document.getElementById('bulkForm').submit()}
</script>
@endsection
