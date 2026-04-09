@extends('layouts.super-admin')

@section('title', $admin->name)
@section('page-title', $admin->name)

@section('content')
    <!-- Admin Header -->
    <div class="sa-card" style="margin-bottom:24px">
        <div style="display:flex;align-items:center;gap:20px;flex-wrap:wrap">
            <div class="sa-avatar sa-avatar-primary" style="width:56px;height:56px;font-size:1.5rem">{{ strtoupper(substr($admin->name,0,1)) }}</div>
            <div style="flex:1;min-width:200px">
                <h2 style="font-size:1.25rem;font-weight:700;margin-bottom:2px">{{ $admin->name }}</h2>
                <div style="display:flex;align-items:center;gap:12px;flex-wrap:wrap;font-size:.8125rem;color:var(--sa-text-secondary)">
                    <span><i class="fas fa-envelope" style="margin-right:4px"></i>{{ $admin->email }}</span>
                    @if($admin->phone)<span><i class="fas fa-phone" style="margin-right:4px"></i>{{ $admin->phone }}</span>@endif
                    <span><i class="fas fa-store" style="margin-right:4px"></i>{{ $admin->shop_name }}</span>
                </div>
            </div>
            <div style="display:flex;gap:8px">
                <span class="sa-badge sa-badge-{{ $admin->is_active ? 'success' : 'danger' }}">{{ $admin->is_active ? 'Actif' : 'Inactif' }}</span>
                <span class="sa-badge sa-badge-{{ $admin->subscription_type === 'premium' ? 'primary' : ($admin->subscription_type === 'enterprise' ? 'success' : 'muted') }}">{{ ucfirst($admin->subscription_type ?? 'trial') }}</span>
                @if($admin->confirmi_status === 'active')
                    <span class="sa-badge sa-badge-info">Confirmi</span>
                @endif
            </div>
            <div style="display:flex;gap:8px">
                <a href="{{ route('super-admin.admins.edit', $admin) }}" class="sa-btn sa-btn-primary sa-btn-sm"><i class="fas fa-pen"></i> Modifier</a>
                <a href="{{ route('super-admin.admins.index') }}" class="sa-btn sa-btn-outline sa-btn-sm"><i class="fas fa-arrow-left"></i> Retour</a>
            </div>
        </div>
    </div>

    <!-- Stats -->
    <div class="sa-grid sa-grid-4" style="margin-bottom:24px">
        <div class="sa-stat">
            <div class="sa-stat-icon sa-stat-icon-primary"><i class="fas fa-shopping-cart"></i></div>
            <div><div class="sa-stat-value">{{ number_format($stats['total_orders']) }}</div><div class="sa-stat-label">Commandes</div></div>
        </div>
        <div class="sa-stat">
            <div class="sa-stat-icon sa-stat-icon-success"><i class="fas fa-coins"></i></div>
            <div><div class="sa-stat-value">{{ number_format($stats['total_revenue'], 2) }} DH</div><div class="sa-stat-label">Revenus</div></div>
        </div>
        <div class="sa-stat">
            <div class="sa-stat-icon sa-stat-icon-info"><i class="fas fa-user-tie"></i></div>
            <div><div class="sa-stat-value">{{ $totalManagers }}</div><div class="sa-stat-label">Managers</div></div>
        </div>
        <div class="sa-stat">
            <div class="sa-stat-icon sa-stat-icon-warning"><i class="fas fa-users"></i></div>
            <div><div class="sa-stat-value">{{ $totalEmployees }}</div><div class="sa-stat-label">Employés</div></div>
        </div>
    </div>

    <div class="sa-grid sa-grid-2" style="margin-bottom:24px">
        <!-- Details Card -->
        <div class="sa-card">
            <div class="sa-card-header"><h3 class="sa-card-title">Informations</h3></div>
            <table style="width:100%">
                @php
                    $rows = [
                        ['Identifiant', $admin->identifier],
                        ['Inscription', $admin->created_at->format('d/m/Y H:i')],
                        ['Dernière connexion', $admin->last_login_at ? $admin->last_login_at->diffForHumans() : 'Jamais'],
                        ['Expiration', $admin->expiry_date ? $admin->expiry_date->format('d/m/Y') : 'Non défini'],
                        ['Max Managers', $admin->max_managers],
                        ['Max Employés', $admin->max_employees],
                        ['Créé par Super Admin', $admin->created_by_super_admin ? 'Oui' : 'Non'],
                    ];
                @endphp
                @foreach($rows as [$label, $value])
                    <tr>
                        <td style="padding:8px 0;font-size:.8125rem;color:var(--sa-text-secondary);width:40%">{{ $label }}</td>
                        <td style="padding:8px 0;font-size:.8125rem;font-weight:500">{{ $value }}</td>
                    </tr>
                @endforeach
            </table>
        </div>

        <!-- Confirmi & Emballage -->
        <div class="sa-card">
            <div class="sa-card-header"><h3 class="sa-card-title">Confirmi & Emballage</h3></div>
            <table style="width:100%">
                <tr>
                    <td style="padding:8px 0;font-size:.8125rem;color:var(--sa-text-secondary);width:40%">Confirmi</td>
                    <td style="padding:8px 0">
                        @if($admin->confirmi_status === 'active')
                            <span class="sa-badge sa-badge-success">Actif</span>
                        @elseif($admin->confirmi_status === 'pending')
                            <span class="sa-badge sa-badge-warning">En attente</span>
                        @else
                            <span class="sa-badge sa-badge-muted">Inactif</span>
                        @endif
                    </td>
                </tr>
                @if($admin->confirmi_status === 'active')
                    <tr>
                        <td style="padding:8px 0;font-size:.8125rem;color:var(--sa-text-secondary)">Tarif confirmé</td>
                        <td style="padding:8px 0;font-size:.8125rem;font-weight:600">{{ $admin->confirmi_rate_confirmed }} DH</td>
                    </tr>
                    <tr>
                        <td style="padding:8px 0;font-size:.8125rem;color:var(--sa-text-secondary)">Tarif livré</td>
                        <td style="padding:8px 0;font-size:.8125rem;font-weight:600">{{ $admin->confirmi_rate_delivered }} DH</td>
                    </tr>
                    <tr>
                        <td style="padding:8px 0;font-size:.8125rem;color:var(--sa-text-secondary)">Activé le</td>
                        <td style="padding:8px 0;font-size:.8125rem">{{ $admin->confirmi_activated_at?->format('d/m/Y') ?? '-' }}</td>
                    </tr>
                @endif
                <tr>
                    <td style="padding:8px 0;font-size:.8125rem;color:var(--sa-text-secondary)">Emballage</td>
                    <td style="padding:8px 0">
                        <span class="sa-badge sa-badge-{{ $admin->emballage_enabled ? 'success' : 'muted' }}">{{ $admin->emballage_enabled ? 'Activé' : 'Désactivé' }}</span>
                    </td>
                </tr>
            </table>
        </div>
    </div>

    <!-- Actions -->
    <div class="sa-card">
        <div class="sa-card-header"><h3 class="sa-card-title">Actions Rapides</h3></div>
        <div style="display:flex;gap:12px;flex-wrap:wrap">
            <form action="{{ route('super-admin.admins.toggle-active', $admin) }}" method="POST">
                @csrf @method('PATCH')
                <button type="submit" class="sa-btn {{ $admin->is_active ? 'sa-btn-warning' : 'sa-btn-success' }} sa-btn-sm">
                    <i class="fas fa-{{ $admin->is_active ? 'ban' : 'check' }}"></i> {{ $admin->is_active ? 'Désactiver' : 'Activer' }}
                </button>
            </form>

            <form action="{{ route('super-admin.admins.extend-subscription', $admin) }}" method="POST" style="display:flex;align-items:center;gap:8px">
                @csrf @method('PATCH')
                <select name="months" class="sa-input sa-select" style="width:auto">
                    @for($i = 1; $i <= 12; $i++)
                        <option value="{{ $i }}">{{ $i }} mois</option>
                    @endfor
                </select>
                <button type="submit" class="sa-btn sa-btn-primary sa-btn-sm"><i class="fas fa-calendar-plus"></i> Prolonger</button>
            </form>

            <form action="{{ route('super-admin.admins.reset-password', $admin) }}" method="POST" onsubmit="return confirm('Réinitialiser le mot de passe ?')">
                @csrf
                <button type="submit" class="sa-btn sa-btn-outline sa-btn-sm"><i class="fas fa-key"></i> Réinitialiser MDP</button>
            </form>

            <form action="{{ route('super-admin.admins.destroy', $admin) }}" method="POST" onsubmit="return confirm('Supprimer définitivement ?')" style="margin-left:auto">
                @csrf @method('DELETE')
                <button type="submit" class="sa-btn sa-btn-danger sa-btn-sm"><i class="fas fa-trash"></i> Supprimer</button>
            </form>
        </div>
    </div>
@endsection
