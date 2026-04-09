@extends('layouts.super-admin')

@section('title', 'Demandes Confirmi')
@section('page-title', 'Demandes d\'activation Confirmi')

@section('content')
    <!-- Stats -->
    <div class="sa-grid sa-grid-3" style="margin-bottom:24px">
        @php
            $allCount = \App\Models\ConfirmiRequest::count();
            $approvedCount = \App\Models\ConfirmiRequest::where('status','approved')->count();
            $rejectedCount = \App\Models\ConfirmiRequest::where('status','rejected')->count();
        @endphp
        <div class="sa-stat">
            <div class="sa-stat-icon sa-stat-icon-warning"><i class="fas fa-clock"></i></div>
            <div><div class="sa-stat-value">{{ $pendingCount }}</div><div class="sa-stat-label">En attente</div></div>
        </div>
        <div class="sa-stat">
            <div class="sa-stat-icon sa-stat-icon-success"><i class="fas fa-check"></i></div>
            <div><div class="sa-stat-value">{{ $approvedCount }}</div><div class="sa-stat-label">Approuvées</div></div>
        </div>
        <div class="sa-stat">
            <div class="sa-stat-icon sa-stat-icon-danger"><i class="fas fa-times"></i></div>
            <div><div class="sa-stat-value">{{ $rejectedCount }}</div><div class="sa-stat-label">Rejetées</div></div>
        </div>
    </div>

    <!-- Filter Tabs -->
    <div style="display:flex;gap:8px;margin-bottom:16px">
        <a href="{{ route('super-admin.confirmi-requests.index') }}" class="sa-btn {{ !request('status') ? 'sa-btn-primary' : 'sa-btn-outline' }} sa-btn-sm">Tout ({{ $allCount }})</a>
        <a href="{{ route('super-admin.confirmi-requests.index', ['status'=>'pending']) }}" class="sa-btn {{ request('status')==='pending' ? 'sa-btn-primary' : 'sa-btn-outline' }} sa-btn-sm">En attente ({{ $pendingCount }})</a>
        <a href="{{ route('super-admin.confirmi-requests.index', ['status'=>'approved']) }}" class="sa-btn {{ request('status')==='approved' ? 'sa-btn-primary' : 'sa-btn-outline' }} sa-btn-sm">Approuvées</a>
        <a href="{{ route('super-admin.confirmi-requests.index', ['status'=>'rejected']) }}" class="sa-btn {{ request('status')==='rejected' ? 'sa-btn-primary' : 'sa-btn-outline' }} sa-btn-sm">Rejetées</a>
    </div>

    <!-- Requests -->
    @forelse($requests as $req)
        <div class="sa-card" style="margin-bottom:12px">
            <div style="display:flex;align-items:flex-start;gap:16px;flex-wrap:wrap">
                <!-- Admin info -->
                <div style="display:flex;align-items:center;gap:12px;min-width:220px;flex:1">
                    <div class="sa-avatar sa-avatar-primary">{{ strtoupper(substr($req->admin->name ?? '?', 0, 1)) }}</div>
                    <div>
                        <strong style="font-size:.875rem">{{ $req->admin->name ?? 'Admin supprimé' }}</strong>
                        <div style="font-size:.7rem;color:var(--sa-text-muted)">{{ $req->admin->shop_name ?? '' }} · {{ $req->admin->email ?? '' }}</div>
                    </div>
                </div>

                <!-- Proposed Rates -->
                <div style="display:flex;gap:20px;align-items:center;min-width:200px">
                    <div>
                        <div style="font-size:.65rem;text-transform:uppercase;color:var(--sa-text-muted);font-weight:700">Tarif confirmé</div>
                        <div style="font-size:1rem;font-weight:700;color:var(--sa-primary)">{{ $req->proposed_rate_confirmed ?? '-' }} DH</div>
                    </div>
                    <div>
                        <div style="font-size:.65rem;text-transform:uppercase;color:var(--sa-text-muted);font-weight:700">Tarif livré</div>
                        <div style="font-size:1rem;font-weight:700;color:var(--sa-success)">{{ $req->proposed_rate_delivered ?? '-' }} DH</div>
                    </div>
                </div>

                <!-- Status -->
                <div style="min-width:100px;text-align:center">
                    @if($req->status === 'pending')
                        <span class="sa-badge sa-badge-warning">En attente</span>
                    @elseif($req->status === 'approved')
                        <span class="sa-badge sa-badge-success">Approuvée</span>
                    @else
                        <span class="sa-badge sa-badge-danger">Rejetée</span>
                    @endif
                    <div style="font-size:.65rem;color:var(--sa-text-muted);margin-top:4px">{{ $req->created_at->format('d/m/Y H:i') }}</div>
                </div>

                <!-- Actions (only for pending) -->
                @if($req->status === 'pending')
                    <div style="min-width:280px">
                        <form method="POST" action="{{ route('super-admin.confirmi-requests.approve', $req) }}" style="display:flex;gap:8px;align-items:flex-end;flex-wrap:wrap">
                            @csrf
                            <div>
                                <label style="font-size:.65rem;font-weight:600;color:var(--sa-text-secondary)">Confirmé (DH)</label>
                                <input type="number" name="rate_confirmed" class="sa-input" value="{{ $req->proposed_rate_confirmed }}" step="0.001" min="0" required style="width:90px;padding:6px 8px;font-size:.8rem">
                            </div>
                            <div>
                                <label style="font-size:.65rem;font-weight:600;color:var(--sa-text-secondary)">Livré (DH)</label>
                                <input type="number" name="rate_delivered" class="sa-input" value="{{ $req->proposed_rate_delivered }}" step="0.001" min="0" required style="width:90px;padding:6px 8px;font-size:.8rem">
                            </div>
                            <button type="submit" class="sa-btn sa-btn-success sa-btn-sm"><i class="fas fa-check"></i></button>
                        </form>
                        <form method="POST" action="{{ route('super-admin.confirmi-requests.reject', $req) }}" style="margin-top:6px" onsubmit="return confirm('Rejeter cette demande ?')">
                            @csrf
                            <button type="submit" class="sa-btn sa-btn-danger sa-btn-sm" style="width:100%"><i class="fas fa-times"></i> Rejeter</button>
                        </form>
                    </div>
                @endif
            </div>

            @if($req->admin_message)
                <div style="margin-top:12px;padding-top:12px;border-top:1px solid var(--sa-border);font-size:.8rem;color:var(--sa-text-secondary)">
                    <i class="fas fa-comment" style="margin-right:4px"></i>{{ $req->admin_message }}
                </div>
            @endif
            @if($req->response_message)
                <div style="margin-top:8px;font-size:.8rem;color:var(--sa-text-secondary)">
                    <i class="fas fa-reply" style="margin-right:4px;color:var(--sa-primary)"></i>{{ $req->response_message }}
                </div>
            @endif
        </div>
    @empty
        <div class="sa-card"><div class="sa-empty"><i class="fas fa-inbox"></i><p>Aucune demande trouvée</p></div></div>
    @endforelse

    @if($requests->hasPages())
        <div class="sa-pagination" style="margin-top:16px">{{ $requests->links() }}</div>
    @endif
@endsection
