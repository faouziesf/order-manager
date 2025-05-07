@extends('layouts.super-admin')

@section('title', 'Tableau de bord')

@section('content')
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Tableau de bord</h1>
    </div>
    
    <!-- Statistiques -->
    <div class="row">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stats-card stats-card-primary h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="stats-card-label">Total Administrateurs</div>
                            <div class="stats-card-number">{{ $totalAdmins }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-2x stats-card-icon"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stats-card stats-card-success h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="stats-card-label">Administrateurs Actifs</div>
                            <div class="stats-card-number">{{ $activeAdmins }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-user-check fa-2x stats-card-icon"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stats-card stats-card-warning h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="stats-card-label">Administrateurs Inactifs</div>
                            <div class="stats-card-number">{{ $inactiveAdmins }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-user-times fa-2x stats-card-icon"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stats-card stats-card-info h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="stats-card-label">Total Commandes</div>
                            <div class="stats-card-number">{{ $totalOrders }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-shopping-cart fa-2x stats-card-icon"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <!-- Administrateurs récents -->
        <div class="col-lg-6">
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="card-title">Administrateurs récents</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Nom</th>
                                    <th>Boutique</th>
                                    <th>État</th>
                                    <th>Date d'expiration</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentAdmins as $admin)
                                    <tr>
                                        <td>{{ $admin->name }}</td>
                                        <td>{{ $admin->shop_name }}</td>
                                        <td>
                                            @if($admin->is_active)
                                                <span class="badge bg-success">Actif</span>
                                            @else
                                                <span class="badge bg-danger">Inactif</span>
                                            @endif
                                        </td>
                                        <td>{{ $admin->expiry_date ? $admin->expiry_date->format('d/m/Y') : 'N/A' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center">Aucun administrateur trouvé</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="text-center mt-3">
                        <a href="{{ route('super-admin.admins.index') }}" class="btn btn-primary btn-sm">
                            Voir tous les administrateurs
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Statistiques d'activité -->
        <div class="col-lg-6">
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="card-title">Statistiques d'activité</h6>
                </div>
                <div class="card-body">
                    <div class="mb-4">
                        <h5 class="small font-weight-bold">Heures d'activité totales <span class="float-end">{{ $totalActiveHours }}</span></h5>
                        <div class="progress">
                            <div class="progress-bar bg-info" role="progressbar" style="width: 100%" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <h5 class="small font-weight-bold">Commandes totales <span class="float-end">{{ $totalOrders }}</span></h5>
                        <div class="progress">
                            <div class="progress-bar bg-success" role="progressbar" style="width: 100%" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                    </div>
                    
                    <div class="text-center mt-3">
                        <a href="{{ route('super-admin.admins.index') }}" class="btn btn-primary btn-sm">
                            Voir les détails
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection