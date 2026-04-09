@extends('confirmi.layouts.app')
@section('title', 'Mes commandes')
@section('page-title', 'Mes commandes')

@section('css')
<style>
.tabs-bar {
    display: flex; gap: 0.35rem; flex-wrap: wrap;
    padding: 0.75rem 1rem;
    border-bottom: 1px solid var(--border);
}
.tab-link {
    padding: 0.4rem 0.85rem; border-radius: 8px;
    font-size: 0.78rem; font-weight: 600;
    color: var(--text-secondary); text-decoration: none;
    transition: all 0.15s; display: inline-flex;
    align-items: center; gap: 0.35rem;
    border: 1px solid transparent;
}
.tab-link:hover { background: var(--bg-hover); color: var(--text); }
.tab-link.active { background: var(--accent); color: white; border-color: var(--accent); }
.tab-count {
    font-size: 0.65rem; padding: 0.1rem 0.4rem;
    border-radius: 8px; font-weight: 700;
}
.tab-link.active .tab-count { background: rgba(255,255,255,0.25); color: white; }
.tab-link:not(.active) .tab-count { background: var(--bg-hover); color: var(--text-secondary); }
.search-bar {
    padding: 0.65rem 1rem; border-bottom: 1px solid var(--border);
    display: flex; gap: 0.5rem; align-items: center;
}
.search-bar input {
    flex: 1; padding: 0.4rem 0.75rem;
    border: 1.5px solid var(--input-border, var(--border));
    border-radius: 8px; background: var(--input-bg);
    color: var(--text); font-size: 0.82rem;
}
.search-bar input::placeholder { color: var(--text-muted); }
.search-bar input:focus { outline: none; border-color: var(--accent); box-shadow: 0 0 0 3px rgba(37,99,235,0.1); }
</style>
@endsection

@section('content')
<div class="content-card">
    {{-- Tabs --}}
    <div class="tabs-bar">
        <a href="{{ route('confirmi.employee.orders.index', ['tab' => 'active']) }}" class="tab-link {{ $tab === 'active' ? 'active' : '' }}">
            <i class="fas fa-phone-volume"></i> À traiter <span class="tab-count">{{ $counts['active'] }}</span>
        </a>
        <a href="{{ route('confirmi.employee.orders.index', ['tab' => 'confirmed']) }}" class="tab-link {{ $tab === 'confirmed' ? 'active' : '' }}">
            <i class="fas fa-check-circle"></i> Confirmées <span class="tab-count">{{ $counts['confirmed'] }}</span>
        </a>
        <a href="{{ route('confirmi.employee.orders.index', ['tab' => 'cancelled']) }}" class="tab-link {{ $tab === 'cancelled' ? 'active' : '' }}">
            <i class="fas fa-times-circle"></i> Annulées <span class="tab-count">{{ $counts['cancelled'] }}</span>
        </a>
        <a href="{{ route('confirmi.employee.orders.index', ['tab' => 'all']) }}" class="tab-link {{ $tab === 'all' ? 'active' : '' }}">
            <i class="fas fa-layer-group"></i> Tout
        </a>
    </div>

    {{-- Search --}}
    <form class="search-bar" method="GET" action="{{ route('confirmi.employee.orders.index') }}">
        <input type="hidden" name="tab" value="{{ $tab }}">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Rechercher par nom, téléphone ou N° commande...">
        <button type="submit" class="btn btn-sm btn-royal"><i class="fas fa-search"></i></button>
        @if(request('search'))
            <a href="{{ route('confirmi.employee.orders.index', ['tab' => $tab]) }}" class="btn btn-sm btn-outline-royal"><i class="fas fa-times"></i></a>
        @endif
    </form>

    {{-- Table --}}
    <div class="table-responsive">
        <table class="table table-modern">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Client</th>
                    <th>Destinataire</th>
                    <th>Téléphone</th>
                    <th>Tentatives</th>
                    <th>Statut</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($assignments as $a)
                <tr>
                    <td><strong>#{{ $a->order->id ?? '-' }}</strong></td>
                    <td><small style="color:var(--text-secondary);">{{ $a->admin->shop_name ?? $a->admin->name ?? '-' }}</small></td>
                    <td>{{ $a->order->customer_name ?? 'N/A' }}</td>
                    <td>
                        <a href="tel:{{ $a->order->customer_phone ?? '' }}" class="fw-semibold text-decoration-none" style="color:var(--accent);">
                            {{ $a->order->customer_phone ?? 'N/A' }}
                        </a>
                    </td>
                    <td><span class="badge bg-secondary">{{ $a->attempts }}</span></td>
                    <td>
                        <span class="badge-status badge-{{ $a->status }}">
                            {{ match($a->status) {
                                'assigned' => 'Assignée', 'in_progress' => 'En cours',
                                'confirmed' => 'Confirmée', 'cancelled' => 'Annulée', default => ucfirst($a->status)
                            } }}
                        </span>
                    </td>
                    <td>
                        <a href="{{ route('confirmi.employee.orders.show', $a) }}" class="btn btn-sm {{ in_array($a->status, ['assigned', 'in_progress']) ? 'btn-royal' : 'btn-outline-royal' }}">
                            <i class="fas fa-eye me-1"></i>Voir
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center py-4" style="color:var(--text-muted);">
                        <i class="fas fa-inbox d-block mb-2" style="font-size:1.5rem;opacity:0.3;"></i>
                        Aucune commande dans cet onglet.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="p-3">{{ $assignments->withQueryString()->links() }}</div>
</div>
@endsection
