@extends('layouts.super-admin')

@section('title', 'Utilisateurs Confirmi')
@section('page-title', 'Utilisateurs Confirmi')

@section('content')
    <!-- Filters -->
    <div class="sa-card" style="margin-bottom:24px">
        <form method="GET" action="{{ route('super-admin.confirmi-users.index') }}" style="display:flex;align-items:center;gap:12px;flex-wrap:wrap">
            <input type="text" name="search" class="sa-input" placeholder="Rechercher..." value="{{ request('search') }}" style="max-width:260px">
            <select name="role" class="sa-input sa-select" style="max-width:160px">
                <option value="">Tous les rôles</option>
                <option value="commercial" {{ request('role') === 'commercial' ? 'selected' : '' }}>Commercial</option>
                <option value="employee" {{ request('role') === 'employee' ? 'selected' : '' }}>Employé</option>
                <option value="agent" {{ request('role') === 'agent' ? 'selected' : '' }}>Agent</option>
            </select>
            <button type="submit" class="sa-btn sa-btn-primary sa-btn-sm"><i class="fas fa-search"></i> Filtrer</button>
            @if(request()->hasAny(['search','role']))
                <a href="{{ route('super-admin.confirmi-users.index') }}" class="sa-btn sa-btn-outline sa-btn-sm"><i class="fas fa-times"></i></a>
            @endif
            <a href="{{ route('super-admin.confirmi-users.create') }}" class="sa-btn sa-btn-primary" style="margin-left:auto"><i class="fas fa-plus"></i> Nouvel Utilisateur</a>
        </form>
    </div>

    <!-- Users Table -->
    <div class="sa-card">
        <div class="sa-card-header">
            <h3 class="sa-card-title">{{ $users->total() }} utilisateur(s)</h3>
        </div>
        <div class="sa-table-wrap">
            <table class="sa-table">
                <thead>
                    <tr>
                        <th>Utilisateur</th>
                        <th>Rôle</th>
                        <th>Téléphone</th>
                        <th>Statut</th>
                        <th>Dernière connexion</th>
                        <th>Créé le</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                        <tr>
                            <td>
                                <div style="display:flex;align-items:center;gap:10px">
                                    <div class="sa-avatar" style="background:{{ $user->role === 'commercial' ? 'linear-gradient(135deg,var(--sa-primary),var(--sa-primary-light))' : ($user->role === 'agent' ? 'linear-gradient(135deg,var(--sa-warning),#fbbf24)' : 'linear-gradient(135deg,var(--sa-success),#34d399)') }}">
                                        {{ strtoupper(substr($user->name,0,1)) }}
                                    </div>
                                    <div>
                                        <strong>{{ $user->name }}</strong>
                                        <div style="font-size:.7rem;color:var(--sa-text-muted)">{{ $user->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="sa-badge sa-badge-{{ $user->role === 'commercial' ? 'primary' : ($user->role === 'agent' ? 'warning' : 'info') }}">
                                    {{ ucfirst($user->role) }}
                                </span>
                            </td>
                            <td style="font-size:.8125rem;color:var(--sa-text-secondary)">{{ $user->phone ?? '-' }}</td>
                            <td><span class="sa-badge sa-badge-{{ $user->is_active ? 'success' : 'danger' }}">{{ $user->is_active ? 'Actif' : 'Inactif' }}</span></td>
                            <td style="font-size:.75rem;color:var(--sa-text-muted)">{{ $user->last_login_at ? $user->last_login_at->diffForHumans() : 'Jamais' }}</td>
                            <td style="font-size:.75rem;color:var(--sa-text-muted)">{{ $user->created_at->format('d/m/Y') }}</td>
                            <td>
                                <div style="display:flex;gap:4px">
                                    <a href="{{ route('super-admin.confirmi-users.edit', $user) }}" class="sa-btn sa-btn-outline sa-btn-icon sa-btn-sm" title="Modifier"><i class="fas fa-pen"></i></a>
                                    <form action="{{ route('super-admin.confirmi-users.login-as', $user) }}" method="POST" style="display:inline" onsubmit="return confirm('Se connecter comme cet utilisateur Confirmi ?')">
                                        @csrf
                                        <button type="submit" class="sa-btn sa-btn-primary sa-btn-icon sa-btn-sm" title="Se connecter comme utilisateur Confirmi">
                                            <i class="fas fa-right-to-bracket"></i>
                                        </button>
                                    </form>
                                    <form action="{{ route('super-admin.confirmi-users.toggle-active', $user) }}" method="POST" style="display:inline">
                                        @csrf @method('PATCH')
                                        <button type="submit" class="sa-btn sa-btn-icon sa-btn-sm {{ $user->is_active ? 'sa-btn-warning' : 'sa-btn-success' }}" title="{{ $user->is_active ? 'Désactiver' : 'Activer' }}">
                                            <i class="fas fa-{{ $user->is_active ? 'ban' : 'check' }}"></i>
                                        </button>
                                    </form>
                                    <form action="{{ route('super-admin.confirmi-users.destroy', $user) }}" method="POST" style="display:inline" onsubmit="return confirm('Supprimer cet utilisateur ?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="sa-btn sa-btn-danger sa-btn-icon sa-btn-sm" title="Supprimer"><i class="fas fa-trash"></i></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7"><div class="sa-empty"><i class="fas fa-users-cog"></i><p>Aucun utilisateur Confirmi</p></div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($users->hasPages())
            <div class="sa-pagination">{{ $users->links() }}</div>
        @endif
    </div>
@endsection
