@extends('adminlte::page')

@section('title', 'Tableau de bord')

@section('content_header')
    <h1>Tableau de bord</h1>
@stop

@section('content')
    <div class="row">
        <!-- Carte statistique: Produits -->
        <div class="col-lg-3 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ \App\Models\Product::where('admin_id', Auth::guard('admin')->id())->count() }}</h3>
                    <p>Produits</p>
                </div>
                <div class="icon">
                    <i class="fas fa-box-open"></i>
                </div>
                <a href="{{ route('admin.products.index') }}" class="small-box-footer">
                    Plus d'info <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>
        
        <!-- Carte statistique: Produits actifs -->
        <div class="col-lg-3 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ \App\Models\Product::where('admin_id', Auth::guard('admin')->id())->where('is_active', true)->count() }}</h3>
                    <p>Produits actifs</p>
                </div>
                <div class="icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <a href="{{ route('admin.products.index') }}?status=1" class="small-box-footer">
                    Plus d'info <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>
        
        <!-- Carte statistique: Produits en rupture de stock -->
        <div class="col-lg-3 col-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ \App\Models\Product::where('admin_id', Auth::guard('admin')->id())->where('stock', '<=', 0)->count() }}</h3>
                    <p>Ruptures de stock</p>
                </div>
                <div class="icon">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <a href="{{ route('admin.products.index') }}?stock=out_of_stock" class="small-box-footer">
                    Plus d'info <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>
        
        <!-- Carte statistique: Valeur totale des produits -->
        <div class="col-lg-3 col-6">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3>{{ number_format(\App\Models\Product::where('admin_id', Auth::guard('admin')->id())->sum('price'), 3) }} DT</h3>
                    <p>Valeur des produits</p>
                </div>
                <div class="icon">
                    <i class="fas fa-money-bill"></i>
                </div>
                <a href="{{ route('admin.products.index') }}" class="small-box-footer">
                    Plus d'info <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>
    </div>
    
    <div class="row">
        <!-- Produits récents -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-box-open mr-1"></i>
                        Produits récents
                    </h3>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table m-0">
                            <thead>
                                <tr>
                                    <th>Nom</th>
                                    <th>Prix</th>
                                    <th>Stock</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $recentProducts = \App\Models\Product::where('admin_id', Auth::guard('admin')->id())
                                        ->orderBy('created_at', 'desc')
                                        ->take(5)
                                        ->get();
                                @endphp
                                
                                @forelse($recentProducts as $product)
                                    <tr>
                                        <td>
                                            @if($product->image)
                                                <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}" class="img-circle mr-2" width="35">
                                            @else
                                                <i class="fas fa-box mr-2"></i>
                                            @endif
                                            {{ $product->name }}
                                        </td>
                                        <td>{{ number_format($product->price, 3) }} DT</td>
                                        <td>
                                            <span class="badge {{ $product->stock > 0 ? 'badge-success' : 'badge-danger' }}">
                                                {{ $product->stock }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group">
                                                <a href="{{ route('admin.products.edit', $product) }}" class="btn btn-sm btn-default">
                                                    <i class="fas fa-edit text-primary"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-3">Aucun produit trouvé</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer clearfix">
                    <a href="{{ route('admin.products.create') }}" class="btn btn-sm btn-primary float-left">
                        <i class="fas fa-plus"></i> Nouveau produit
                    </a>
                    <a href="{{ route('admin.products.index') }}" class="btn btn-sm btn-secondary float-right">
                        Voir tous les produits
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Info utilisateur -->
        <div class="col-md-4">
            <div class="card card-primary card-outline">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-info-circle mr-1"></i>
                        Informations du compte
                    </h3>
                </div>
                <div class="card-body box-profile">
                    <div class="text-center">
                        <img class="profile-user-img img-fluid img-circle" 
                             src="{{ asset('vendor/adminlte/dist/img/user2-160x160.jpg') }}" 
                             alt="Photo de profil">
                    </div>
                    
                    <h3 class="profile-username text-center">{{ Auth::guard('admin')->user()->name }}</h3>
                    <p class="text-muted text-center">Administrateur</p>
                    
                    <ul class="list-group list-group-unbordered mb-3">
                        <li class="list-group-item">
                            <b>Email</b> <a class="float-right">{{ Auth::guard('admin')->user()->email }}</a>
                        </li>
                        <li class="list-group-item">
                            <b>Boutique</b> <a class="float-right">{{ Auth::guard('admin')->user()->shop_name }}</a>
                        </li>
                        <li class="list-group-item">
                            <b>Identifiant</b> <a class="float-right">{{ Auth::guard('admin')->user()->identifier }}</a>
                        </li>
                        <li class="list-group-item">
                            <b>Date d'expiration</b> 
                            <a class="float-right">
                                @if(Auth::guard('admin')->user()->expiry_date)
                                    {{ Auth::guard('admin')->user()->expiry_date->format('d/m/Y') }}
                                @else
                                    Illimitée
                                @endif
                            </a>
                        </li>
                    </ul>
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
        
        .small-box .icon {
            font-size: 70px;
            opacity: 0.2;
        }
    </style>
@stop

@section('js')
    <script>
        $(document).ready(function() {
            // Animations
            $('.small-box').addClass('animate__animated animate__fadeIn');
            
            // SweetAlert pour bienvenue (uniquement première visite)
            if (!localStorage.getItem('dashboard_welcome')) {
                localStorage.setItem('dashboard_welcome', 'true');
                
                Swal.fire({
                    title: 'Bienvenue dans Order Manager!',
                    text: 'Votre tableau de bord est prêt à l\'utilisation.',
                    icon: 'success',
                    confirmButtonText: 'Commencer'
                });
            }
        });
    </script>
@stop