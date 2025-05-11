@extends('layouts.admin')

@section('title', 'Traitement des commandes')

@section('css')
<style>
    .no-orders-container {
        text-align: center;
        padding: 50px 20px;
    }
    
    .no-orders-icon {
        font-size: 80px;
        color: #d1d3e2;
        margin-bottom: 20px;
    }
    
    .no-orders-title {
        font-size: 24px;
        color: #5a5c69;
        margin-bottom: 15px;
    }
    
    .no-orders-message {
        font-size: 16px;
        color: #858796;
        max-width: 500px;
        margin: 0 auto 20px;
    }
    
    .queue-buttons {
        margin-top: 30px;
    }
</style>
@endsection

@section('content')
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Traitement des commandes</h1>
</div>

<div class="card shadow mb-4">
    <div class="card-body">
        <div class="no-orders-container">
            <div class="no-orders-icon">
                <i class="fas fa-mug-hot"></i>
            </div>
            <h3 class="no-orders-title">Aucune commande à traiter</h3>
            <p class="no-orders-message">
                Il n'y a actuellement aucune commande à traiter dans cette file d'attente. Prenez une pause café et revenez plus tard, ou essayez une autre file.
            </p>
            
            <div class="queue-buttons">
                <a href="{{ route('admin.process.standard') }}" class="btn {{ request()->routeIs('admin.process.standard') ? 'btn-primary' : 'btn-outline-primary' }} m-1">
                    <i class="fas fa-list mr-1"></i> File Standard
                </a>
                <a href="{{ route('admin.process.dated') }}" class="btn {{ request()->routeIs('admin.process.dated') ? 'btn-primary' : 'btn-outline-primary' }} m-1">
                    <i class="fas fa-calendar-alt mr-1"></i> File Datée
                </a>
                <a href="{{ route('admin.process.old') }}" class="btn {{ request()->routeIs('admin.process.old') ? 'btn-primary' : 'btn-outline-primary' }} m-1">
                    <i class="fas fa-history mr-1"></i> File Ancienne
                </a>
            </div>
            
            <div class="mt-4">
                <a href="{{ route('admin.orders.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left mr-1"></i> Retour à la liste des commandes
                </a>
            </div>
        </div>
    </div>
</div>
@endsection