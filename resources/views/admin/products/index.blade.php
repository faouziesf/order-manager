@extends('adminlte::page')

@section('title', 'Gestion des Produits')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>Gestion des Produits</h1>
        <a href="{{ route('admin.products.create') }}" class="btn btn-primary">
            <i class="fas fa-plus-circle"></i> Nouveau Produit
        </a>
    </div>
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <!-- Filtres -->
            <div class="card card-outline card-primary">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-filter mr-1"></i>
                        Filtres
                    </h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                            <i class="fas fa-minus"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.products.index') }}" method="GET" class="row">
                        <div class="col-md-4 form-group">
                            <label for="search">Rechercher</label>
                            <input type="text" class="form-control" id="search" name="search" placeholder="Nom du produit..." value="{{ request('search') }}">
                        </div>
                        <div class="col-md-3 form-group">
                            <label for="status">Statut</label>
                            <select class="form-control" id="status" name="status">
                                <option value="">Tous les statuts</option>
                                <option value="1" {{ request('status') == '1' ? 'selected' : '' }}>Actif</option>
                                <option value="0" {{ request('status') == '0' ? 'selected' : '' }}>Inactif</option>
                            </select>
                        </div>
                        <div class="col-md-3 form-group">
                            <label for="stock">Stock</label>
                            <select class="form-control" id="stock" name="stock">
                                <option value="">Tous les stocks</option>
                                <option value="in_stock" {{ request('stock') == 'in_stock' ? 'selected' : '' }}>En stock</option>
                                <option value="out_of_stock" {{ request('stock') == 'out_of_stock' ? 'selected' : '' }}>Rupture de stock</option>
                            </select>
                        </div>
                        <div class="col-md-2 form-group d-flex align-items-end">
                            <button type="submit" class="btn btn-primary btn-block">
                                <i class="fas fa-search"></i> Filtrer
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Liste des produits -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Liste des produits</h3>
                    <div class="card-tools">
                        <span class="badge badge-primary">Total: {{ $products->total() }}</span>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover text-nowrap">
                            <thead>
                                <tr>
                                    <th style="width: 80px" class="text-center">Image</th>
                                    <th>Nom</th>
                                    <th style="width: 120px">Prix</th>
                                    <th style="width: 100px" class="text-center">Stock</th>
                                    <th style="width: 100px" class="text-center">Statut</th>
                                    <th style="width: 120px" class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($products as $product)
                                <tr>
                                    <td class="text-center">
                                        @if($product->image)
                                            <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}" class="img-size-50 img-circle">
                                        @else
                                            <div class="bg-light d-flex align-items-center justify-content-center" style="height: 50px; width: 50px; margin: 0 auto; border-radius: 50%;">
                                                <i class="fas fa-image text-secondary"></i>
                                            </div>
                                        @endif
                                    </td>
                                    <td>
                                        <strong>{{ $product->name }}</strong>
                                        @if($product->description)
                                            <p class="text-muted mb-0 small">{{ Str::limit($product->description, 50) }}</p>
                                        @endif
                                    </td>
                                    <td>{{ number_format($product->price, 3) }} DT</td>
                                    <td class="text-center">
                                        <span class="badge {{ $product->stock > 0 ? 'badge-success' : 'badge-danger' }}">
                                            {{ $product->stock }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <div class="custom-control custom-switch custom-switch-on-success d-flex justify-content-center">
                                            <input type="checkbox" class="custom-control-input" id="customSwitch{{ $product->id }}" {{ $product->is_active ? 'checked' : '' }} disabled>
                                            <label class="custom-control-label" for="customSwitch{{ $product->id }}"></label>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group">
                                            <a href="{{ route('admin.products.edit', $product) }}" class="btn btn-sm btn-default">
                                                <i class="fas fa-edit text-primary"></i>
                                            </a>
                                            <form action="{{ route('admin.products.destroy', $product) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-default" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce produit?')">
                                                    <i class="fas fa-trash text-danger"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5">
                                        <div class="text-center">
                                            <i class="fas fa-box-open fa-3x text-secondary mb-3"></i>
                                            <p class="text-secondary mb-3">Aucun produit trouvé</p>
                                            <a href="{{ route('admin.products.create') }}" class="btn btn-primary">
                                                <i class="fas fa-plus-circle mr-1"></i> Ajouter un produit
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer">
                    {{ $products->appends(request()->query())->links() }}
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
    <style>
        .img-circle {
            border-radius: 50%;
            object-fit: cover;
        }
    </style>
@stop

@section('js')
    <script>
        $(function () {
            // Animer les nouvelles lignes
            @if(session('success') && Str::contains(session('success'), 'créé'))
                $('tbody tr:first-child').addClass('bg-success-light');
                setTimeout(function() {
                    $('tbody tr:first-child').removeClass('bg-success-light');
                }, 3000);
            @endif
        });
    </script>
@stop