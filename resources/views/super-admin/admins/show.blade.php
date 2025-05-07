@extends('layouts.super-admin')

@section('title', 'Détails de l\'Administrateur')

@section('content')
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Détails de l'Administrateur</h1>
        <div>
            <a href="{{ route('super-admin.admins.edit', $admin) }}" class="btn btn-warning">
                <i class="fas fa-edit fa-sm"></i> Modifier
            </a>
            <a href="{{ route('super-admin.admins.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left fa-sm"></i> Retour à la liste
            </a>
        </div>
    </div>
    
    <div class="row">
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="card-title">Informations personnelles</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <tbody>
                                <tr>
                                    <th style="width: 30%">ID</th>
                                    <td>{{ $admin->id }}</td>
                                </tr>
                                <tr>
                                    <th>Identifiant</th>
                                    <td>{{ $admin->identifier }}</td>
                                </tr>
                                <tr>
                                    <th>Nom</th>
                                    <td>{{ $admin->name }}</td>
                                </tr>
                                <tr>
                                    <th>Email</th>
                                    <td>{{ $admin->email }}</td>
                                </tr>
                                <tr>
                                    <th>Boutique</th>
                                    <td>{{ $admin->shop_name }}</td>
                                </tr>
                                <tr>
                                    <th>Téléphone</th>
                                    <td>{{ $admin->phone ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Date d'expiration</th>
                                    <td>{{ $admin->expiry_date ? $admin->expiry_date->format('d/m/Y') : 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>État</th>
                                    <td>
                                        @if($admin->is_active)
                                            <span class="badge bg-success">Actif</span>
                                        @else
                                            <span class="badge bg-danger">Inactif</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>Date de création</th>
                                    <td>{{ $admin->created_at->format('d/m/Y H:i') }}</td>
                                </tr>
                                <tr>
                                    <th>Dernière mise à jour</th>
                                    <td>{{ $admin->updated_at->format('d/m/Y H:i') }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="card-title">Statistiques</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <h5 class="small font-weight-bold">Managers <span class="float-end">{{ $totalManagers }} / {{ $admin->max_managers }}</span></h5>
                        <div class="progress">
                            <div class="progress-bar bg-info" role="progressbar" style="width: {{ $admin->max_managers > 0 ? ($totalManagers / $admin->max_managers) * 100 : 0 }}%" aria-valuenow="{{ $totalManagers }}" aria-valuemin="0" aria-valuemax="{{ $admin->max_managers }}"></div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <h5 class="small font-weight-bold">Employés <span class="float-end">{{ $totalEmployees }} / {{ $admin->max_employees }}</span></h5>
                        <div class="progress">
                            <div class="progress-bar bg-success" role="progressbar" style="width: {{ $admin->max_employees > 0 ? ($totalEmployees / $admin->max_employees) * 100 : 0 }}%" aria-valuenow="{{ $totalEmployees }}" aria-valuemin="0" aria-valuemax="{{ $admin->max_employees }}"></div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <h5 class="small font-weight-bold">Commandes <span class="float-end">{{ $admin->total_orders }}</span></h5>
                        <div class="progress">
                            <div class="progress-bar bg-warning" role="progressbar" style="width: 100%" aria-valuenow="{{ $admin->total_orders }}" aria-valuemin="0" aria-valuemax="{{ $admin->total_orders }}"></div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <h5 class="small font-weight-bold">Heures d'activité <span class="float-end">{{ $admin->total_active_hours }}</span></h5>
                        <div class="progress">
                            <div class="progress-bar bg-danger" role="progressbar" style="width: 100%" aria-valuenow="{{ $admin->total_active_hours }}" aria-valuemin="0" aria-valuemax="{{ $admin->total_active_hours }}"></div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="card-title">Actions</h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <form action="{{ route('super-admin.admins.toggle-active', $admin) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="btn btn-{{ $admin->is_active ? 'danger' : 'success' }} btn-block">
                                <i class="fas fa-{{ $admin->is_active ? 'times' : 'check' }} fa-sm"></i> {{ $admin->is_active ? 'Désactiver' : 'Activer' }} le compte
                            </button>
                        </form>
                        
                        <form action="{{ route('super-admin.admins.destroy', $admin) }}" method="POST" class="delete-form">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-block">
                                <i class="fas fa-trash fa-sm"></i> Supprimer le compte
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js')
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // Confirmation de suppression
            const deleteForm = document.querySelector('.delete-form');
            
            if (deleteForm) {
                deleteForm.addEventListener('submit', function(event) {
                    event.preventDefault();
                    
                    if(confirm('Êtes-vous sûr de vouloir supprimer cet administrateur ? Cette action est irréversible.')) {
                        this.submit();
                    }
                });
            }
        });
    </script>
@endsection