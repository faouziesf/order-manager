@extends('layouts.super-admin')

@section('title', 'Gestion des Administrateurs')

@section('content')
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Gestion des Administrateurs</h1>
        <a href="{{ route('super-admin.admins.create') }}" class="btn btn-primary">
            <i class="fas fa-plus-circle fa-sm"></i> Nouvel Administrateur
        </a>
    </div>
    
    <div class="card">
        <div class="card-header">
            <h6 class="card-title">Liste des Administrateurs</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Identifiant</th>
                            <th>Nom</th>
                            <th>Email</th>
                            <th>Boutique</th>
                            <th>Téléphone</th>
                            <th>Date d'expiration</th>
                            <th>État</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($admins as $admin)
                            <tr>
                                <td>{{ $admin->id }}</td>
                                <td>{{ $admin->identifier }}</td>
                                <td>{{ $admin->name }}</td>
                                <td>{{ $admin->email }}</td>
                                <td>{{ $admin->shop_name }}</td>
                                <td>{{ $admin->phone ?? 'N/A' }}</td>
                                <td>{{ $admin->expiry_date ? $admin->expiry_date->format('d/m/Y') : 'N/A' }}</td>
                                <td>
                                    @if($admin->is_active)
                                        <span class="badge bg-success">Actif</span>
                                    @else
                                        <span class="badge bg-danger">Inactif</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group">
                                        <a href="{{ route('super-admin.admins.show', $admin) }}" class="btn btn-info btn-sm">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('super-admin.admins.edit', $admin) }}" class="btn btn-warning btn-sm">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('super-admin.admins.toggle-active', $admin) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn btn-{{ $admin->is_active ? 'danger' : 'success' }} btn-sm">
                                                <i class="fas fa-{{ $admin->is_active ? 'times' : 'check' }}"></i>
                                            </button>
                                        </form>
                                        <form action="{{ route('super-admin.admins.destroy', $admin) }}" method="POST" class="d-inline delete-form">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center">Aucun administrateur trouvé</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <div class="mt-3">
                {{ $admins->links() }}
            </div>
        </div>
    </div>
@endsection

@section('js')
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // Confirmation de suppression
            const deleteForms = document.querySelectorAll('.delete-form');
            
            deleteForms.forEach(function(form) {
                form.addEventListener('submit', function(event) {
                    event.preventDefault();
                    
                    if(confirm('Êtes-vous sûr de vouloir supprimer cet administrateur ? Cette action est irréversible.')) {
                        this.submit();
                    }
                });
            });
        });
    </script>
@endsection