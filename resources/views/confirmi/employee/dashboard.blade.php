@extends('confirmi.layouts.app')
@section('title', 'Dashboard Employé')
@section('page-title', 'Mon Dashboard')

@section('content')
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="stat-card">
            <div class="d-flex align-items-center gap-3">
                <div class="stat-icon icon-orange"><i class="fas fa-phone-volume"></i></div>
                <div>
                    <div class="stat-value">{{ $stats['pending'] }}</div>
                    <div class="stat-label">À traiter</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card">
            <div class="d-flex align-items-center gap-3">
                <div class="stat-icon icon-purple"><i class="fas fa-spinner"></i></div>
                <div>
                    <div class="stat-value">{{ $stats['in_progress'] }}</div>
                    <div class="stat-label">En cours</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card">
            <div class="d-flex align-items-center gap-3">
                <div class="stat-icon icon-green"><i class="fas fa-check-circle"></i></div>
                <div>
                    <div class="stat-value">{{ $stats['completed_today'] }}</div>
                    <div class="stat-label">Terminées aujourd'hui</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card">
            <div class="d-flex align-items-center gap-3">
                <div class="stat-icon icon-blue"><i class="fas fa-trophy"></i></div>
                <div>
                    <div class="stat-value">{{ $stats['total_completed'] }}</div>
                    <div class="stat-label">Total confirmées</div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- My Assigned Orders -->
<div class="content-card">
    <div class="card-header-custom">
        <h6><i class="fas fa-phone-volume me-2 text-primary"></i>Commandes à traiter</h6>
        <span class="badge bg-primary">{{ $myAssignments->count() }}</span>
    </div>
    @if($myAssignments->count() > 0)
    <div class="table-responsive">
        <table class="table table-modern">
            <thead>
                <tr>
                    <th>Commande</th>
                    <th>Client</th>
                    <th>Destinataire</th>
                    <th>Téléphone</th>
                    <th>Tentatives</th>
                    <th>Statut</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($myAssignments as $assignment)
                <tr>
                    <td><strong>#{{ $assignment->order->id ?? 'N/A' }}</strong></td>
                    <td><small class="text-muted">{{ $assignment->admin->shop_name ?? $assignment->admin->name ?? 'N/A' }}</small></td>
                    <td>{{ $assignment->order->customer_name ?? 'N/A' }}</td>
                    <td>
                        <a href="tel:{{ $assignment->order->customer_phone ?? '' }}" class="text-decoration-none fw-semibold">
                            {{ $assignment->order->customer_phone ?? 'N/A' }}
                        </a>
                        @if($assignment->order->customer_phone_2)
                            <br><small><a href="tel:{{ $assignment->order->customer_phone_2 }}" class="text-muted">{{ $assignment->order->customer_phone_2 }}</a></small>
                        @endif
                    </td>
                    <td>
                        <span class="badge bg-secondary">{{ $assignment->attempts }}</span>
                    </td>
                    <td>
                        @if($assignment->status === 'assigned')
                            <span class="badge-status badge-assigned">Assignée</span>
                        @else
                            <span class="badge-status badge-in-progress">En cours</span>
                        @endif
                    </td>
                    <td>
                        <a href="{{ route('confirmi.employee.orders.show', $assignment) }}" class="btn btn-sm btn-royal">
                            <i class="fas fa-headset"></i> Traiter
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @else
    <div class="text-center py-5">
        <i class="fas fa-inbox fa-3x text-muted mb-3 d-block" style="opacity:0.3;"></i>
        <p class="text-muted">Aucune commande à traiter pour le moment.</p>
    </div>
    @endif
</div>
@endsection
