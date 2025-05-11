@extends('layouts.admin')

@section('title', 'Examiner les nouveaux produits')

@section('content')
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Nouveaux produits à examiner</h1>
    <div>
        @if(count($products) > 0)
        <form action="{{ route('admin.products.mark-all-reviewed') }}" method="POST" class="d-inline">
            @csrf
            <button type="submit" class="btn btn-sm btn-success" onclick="return confirm('Êtes-vous sûr de vouloir marquer tous les produits comme examinés?')">
                <i class="fas fa-check mr-1"></i> Tout marquer comme examiné
            </button>
        </form>
        @endif
        <a href="{{ route('admin.products.index') }}" class="btn btn-sm btn-secondary">
            <i class="fas fa-arrow-left mr-1"></i> Retour à la liste
        </a>
    </div>
</div>

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">
            {{ count($products) }} produit(s) nécessitant un examen
        </h6>
    </div>
    <div class="card-body">
        @if(count($products) > 0)
            <div class="alert alert-info">
                <i class="fas fa-info-circle mr-1"></i> Ces produits ont été créés automatiquement lors d'importations. Veuillez vérifier leurs informations et ajuster si nécessaire.
            </div>
            
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Nom</th>
                            <th>Prix</th>
                            <th>Stock</th>
                            <th>Statut</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($products as $product)
                            <tr>
                                <td>{{ $product->name }}</td>
                                <td>{{ number_format($product->price, 3) }} TND</td>
                                <td>{{ $product->stock }}</td>
                                <td>
                                    @if($product->is_active)
                                        <span class="badge bg-success">Actif</span>
                                    @else
                                        <span class="badge bg-danger">Inactif</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('admin.products.edit', $product->id) }}" class="btn btn-sm btn-primary">
                                        <i class="fas fa-edit"></i> Modifier
                                    </a>
                                    <form action="{{ route('admin.products.mark-reviewed', $product->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-success">
                                            <i class="fas fa-check"></i> Marquer comme examiné
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <div class="mt-3">
                {{ $products->links() }}
            </div>
        @else
            <div class="alert alert-success">
                <i class="fas fa-check-circle mr-1"></i> Il n'y a pas de nouveaux produits nécessitant un examen.
            </div>
        @endif
    </div>
</div>
@endsection