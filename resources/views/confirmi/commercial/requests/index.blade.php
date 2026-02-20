@extends('confirmi.layouts.app')
@section('title', 'Demandes d\'activation')
@section('page-title', 'Demandes d\'activation Confirmi')

@section('content')
<div class="content-card">
    <div class="card-header-custom">
        <h6><i class="fas fa-inbox me-2 text-primary"></i>Demandes ({{ $requests->total() }})</h6>
    </div>
    <div class="table-responsive">
        <table class="table table-modern">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Admin</th>
                    <th>Boutique</th>
                    <th>Message</th>
                    <th>Statut</th>
                    <th>Date</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($requests as $req)
                <tr>
                    <td><strong>{{ $req->id }}</strong></td>
                    <td>{{ $req->admin->name ?? 'N/A' }}</td>
                    <td>{{ $req->admin->shop_name ?? '-' }}</td>
                    <td><small>{{ Str::limit($req->admin_message, 50) }}</small></td>
                    <td>
                        @if($req->status === 'pending')
                            <span class="badge-status badge-pending">En attente</span>
                        @elseif($req->status === 'approved')
                            <span class="badge-status badge-confirmed">Approuvée</span>
                        @else
                            <span class="badge-status badge-cancelled">Rejetée</span>
                        @endif
                    </td>
                    <td><small>{{ $req->created_at->format('d/m/Y H:i') }}</small></td>
                    <td>
                        <a href="{{ route('confirmi.commercial.requests.show', $req) }}" class="btn btn-sm btn-{{ $req->status === 'pending' ? 'royal' : 'outline-royal' }}">
                            <i class="fas fa-{{ $req->status === 'pending' ? 'gavel' : 'eye' }}"></i>
                        </a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="text-center py-4 text-muted">Aucune demande.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="p-3">{{ $requests->links() }}</div>
</div>
@endsection
