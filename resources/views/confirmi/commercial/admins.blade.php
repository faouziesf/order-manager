@extends('confirmi.layouts.app')
@section('title', 'Clients Confirmi')
@section('page-title', 'Clients Confirmi actifs')

@section('content')
<div class="content-card">
    <div class="card-header-custom">
        <h6><i class="fas fa-building me-2 text-primary"></i>Clients avec Confirmi actif ({{ $admins->count() }})</h6>
    </div>
    <div class="table-responsive">
        <table class="table table-modern">
            <thead>
                <tr>
                    <th>Admin</th>
                    <th>Boutique</th>
                    <th>Email</th>
                    <th>Tarif confirmé</th>
                    <th>Tarif livré</th>
                    <th>Commandes</th>
                    <th>En cours</th>
                    <th>Activé le</th>
                </tr>
            </thead>
            <tbody>
                @forelse($admins as $admin)
                <tr>
                    <td><strong>{{ $admin->name }}</strong></td>
                    <td>{{ $admin->shop_name ?? '-' }}</td>
                    <td><small>{{ $admin->email }}</small></td>
                    <td><strong>{{ number_format($admin->confirmi_rate_confirmed, 3) }} DT</strong></td>
                    <td><strong>{{ number_format($admin->confirmi_rate_delivered, 3) }} DT</strong></td>
                    <td><span class="badge bg-primary">{{ $admin->total_confirmi_orders ?? 0 }}</span></td>
                    <td><span class="badge bg-warning text-dark">{{ $admin->pending_confirmi_orders ?? 0 }}</span></td>
                    <td><small>{{ $admin->confirmi_activated_at ? \Carbon\Carbon::parse($admin->confirmi_activated_at)->format('d/m/Y') : '-' }}</small></td>
                </tr>
                @empty
                <tr><td colspan="8" class="text-center py-4 text-muted">Aucun client actif.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
