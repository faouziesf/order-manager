@extends('confirmi.layouts.app')
@section('title', 'Historique')
@section('page-title', 'Historique')

@section('css')
<style>
.filter-bar {
    display: flex; gap: 0.5rem; flex-wrap: wrap; align-items: center;
    padding: 0.65rem 1rem;
    border-bottom: 1px solid var(--border);
}
.filter-bar select, .filter-bar input[type="date"] {
    padding: 0.35rem 0.65rem;
    border: 1.5px solid var(--input-border, var(--border));
    border-radius: 8px;
    background: var(--input-bg); color: var(--text);
    font-size: 0.8rem;
}
.filter-bar select:focus, .filter-bar input[type="date"]:focus {
    outline: none; border-color: var(--accent);
    box-shadow: 0 0 0 3px rgba(37,99,235,0.1);
}
</style>
@endsection

@section('content')
{{-- Today summary --}}
<div class="row g-3 mb-3">
    <div class="col-6 col-md-3">
        <div class="stat-card">
            <div class="d-flex align-items-center gap-3">
                <div class="stat-icon icon-green"><i class="fas fa-check-circle"></i></div>
                <div>
                    <div class="stat-value">{{ $todayConfirmed }}</div>
                    <div class="stat-label">Confirmées aujourd'hui</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card">
            <div class="d-flex align-items-center gap-3">
                <div class="stat-icon icon-red"><i class="fas fa-times-circle"></i></div>
                <div>
                    <div class="stat-value">{{ $todayCancelled }}</div>
                    <div class="stat-label">Annulées aujourd'hui</div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="content-card">
    <div class="card-header-custom">
        <h6><i class="fas fa-clock-rotate-left me-2" style="color:var(--accent);"></i>Historique ({{ $assignments->total() }})</h6>
    </div>

    {{-- Filters --}}
    <form class="filter-bar" method="GET" action="{{ route('confirmi.employee.orders.history') }}">
        <input type="date" name="date" value="{{ request('date') }}" style="max-width:160px;">
        <select name="result">
            <option value="">Tous les résultats</option>
            <option value="confirmed" {{ request('result') === 'confirmed' ? 'selected' : '' }}>Confirmées</option>
            <option value="cancelled" {{ request('result') === 'cancelled' ? 'selected' : '' }}>Annulées</option>
        </select>
        <button type="submit" class="btn btn-sm btn-royal"><i class="fas fa-filter me-1"></i>Filtrer</button>
        @if(request('date') || request('result'))
            <a href="{{ route('confirmi.employee.orders.history') }}" class="btn btn-sm btn-outline-royal"><i class="fas fa-times"></i></a>
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
                    <th>Tentatives</th>
                    <th>Statut</th>
                    <th>Terminée le</th>
                </tr>
            </thead>
            <tbody>
                @forelse($assignments as $a)
                <tr>
                    <td><strong>#{{ $a->order->id ?? '-' }}</strong></td>
                    <td><small style="color:var(--text-secondary);">{{ $a->admin->shop_name ?? $a->admin->name ?? '-' }}</small></td>
                    <td>{{ $a->order->customer_name ?? 'N/A' }}</td>
                    <td><span class="badge bg-secondary">{{ $a->attempts }}</span></td>
                    <td>
                        <span class="badge-status badge-{{ $a->status }}">
                            {{ match($a->status) { 'confirmed' => 'Confirmée', 'cancelled' => 'Annulée', 'delivered' => 'Livrée', default => ucfirst($a->status) } }}
                        </span>
                    </td>
                    <td style="color:var(--text-secondary);font-size:0.8rem;">{{ $a->completed_at?->format('d/m/Y H:i') ?? '-' }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center py-4" style="color:var(--text-muted);">
                        <i class="fas fa-inbox d-block mb-2" style="font-size:1.5rem;opacity:0.3;"></i>
                        Aucun historique.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="p-3">{{ $assignments->withQueryString()->links() }}</div>
</div>
@endsection
