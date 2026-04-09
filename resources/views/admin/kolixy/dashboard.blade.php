@extends('layouts.admin')
@section('title', 'Kolixy — Dashboard')

@section('css')
@include('admin.kolixy._styles')
<style>
    .status-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
        gap: 0.75rem;
    }
    .pkg-status-card {
        display: block;
        padding: 1rem;
        border-radius: var(--radius, 12px);
        border: 1px solid var(--border, #e5e7eb);
        background: var(--bg-card, #fff);
        text-decoration: none !important;
        transition: all 0.2s;
        text-align: center;
    }
    .pkg-status-card:hover {
        transform: translateY(-2px);
        box-shadow: var(--shadow-md, 0 4px 6px rgba(0,0,0,0.1));
        border-color: var(--card-color);
    }
    .pkg-status-card .pkg-emoji { font-size: 1.5rem; margin-bottom: 0.5rem; }
    .pkg-status-card .pkg-count {
        font-size: 1.75rem;
        font-weight: 800;
        color: var(--card-color);
        line-height: 1;
        margin-bottom: 0.25rem;
    }
    .pkg-status-card .pkg-label {
        font-size: 0.7rem;
        font-weight: 600;
        color: var(--text-secondary, #6b7280);
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .pkg-status-card .pkg-dot {
        display: inline-block;
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background: var(--card-color);
        margin-right: 0.25rem;
        animation: statusPulse 2s infinite;
    }
    @keyframes statusPulse {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.4; }
    }
    @media (max-width: 768px) {
        .status-grid {
            grid-template-columns: repeat(3, 1fr);
            gap: 0.5rem;
        }
        .pkg-status-card { padding: 0.75rem 0.5rem; }
        .pkg-status-card .pkg-count { font-size: 1.25rem; }
        .pkg-status-card .pkg-emoji { font-size: 1.25rem; }
    }
    @media (max-width: 480px) {
        .status-grid { grid-template-columns: repeat(2, 1fr); }
    }
</style>
@endsection

@section('content')
<div class="kolixy-page p-3">
    <div id="kolixy-toast" class="kolixy-toast"></div>

    {{-- Header --}}
    <div class="kolixy-card mb-3">
        <div class="kolixy-header">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <h4><i class="fas fa-chart-line me-2"></i>Dashboard Livraison</h4>
                    <p>Vue d'ensemble de vos expéditions Kolixy</p>
                </div>
                <div>
                    @if($connected)
                        <span class="kolixy-badge kolixy-badge-green"><span class="kolixy-status-dot kolixy-connected"></span> Connecté</span>
                    @else
                        <span class="kolixy-badge kolixy-badge-red"><span class="kolixy-status-dot kolixy-disconnected"></span> Non connecté</span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @if(!$connected)
    <div class="kolixy-card">
        <div class="kolixy-card-body text-center py-5">
            <i class="fas fa-plug" style="font-size:3rem;color:var(--kolixy-primary);margin-bottom:1rem;"></i>
            <h5>Connectez votre compte Kolixy</h5>
            <p class="text-muted mb-3">Rendez-vous dans la section Configuration pour lier votre compte.</p>
            <a href="{{ route('admin.kolixy.configuration') }}" class="kolixy-btn kolixy-btn-primary">
                <i class="fas fa-cog"></i> Aller à la Configuration
            </a>
        </div>
    </div>
    @else

    {{-- Stats locales --}}
    <div class="row g-3 mb-3">
        <div class="col-6 col-md-4 col-lg">
            <div class="kolixy-stat-card">
                <div class="stat-icon" style="color:var(--kolixy-primary);"><i class="fas fa-paper-plane"></i></div>
                <div class="stat-value">{{ $localStats['total_sent'] }}</div>
                <div class="stat-label">Envoyées</div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-lg">
            <div class="kolixy-stat-card">
                <div class="stat-icon" style="color:var(--kolixy-info);"><i class="fas fa-truck"></i></div>
                <div class="stat-value">{{ $localStats['en_cours'] }}</div>
                <div class="stat-label">En cours</div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-lg">
            <div class="kolixy-stat-card">
                <div class="stat-icon" style="color:var(--kolixy-success);"><i class="fas fa-check-circle"></i></div>
                <div class="stat-value">{{ $localStats['livrees'] }}</div>
                <div class="stat-label">Livrées</div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-lg">
            <div class="kolixy-stat-card">
                <div class="stat-icon" style="color:var(--kolixy-danger);"><i class="fas fa-undo"></i></div>
                <div class="stat-value">{{ $localStats['en_retour'] }}</div>
                <div class="stat-label">Retours</div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-lg">
            <div class="kolixy-stat-card">
                <div class="stat-icon" style="color:var(--kolixy-accent);"><i class="fas fa-hourglass-half"></i></div>
                <div class="stat-value">{{ $localStats['pret_envoyer'] }}</div>
                <div class="stat-label">Prêtes à envoyer</div>
            </div>
        </div>
    </div>

    {{-- Kolixy API Stats by Status --}}
    @if($stats)
    <div class="kolixy-card mb-3">
        <div class="kolixy-card-body">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <h6 class="fw-bold mb-0"><i class="fas fa-cloud me-2" style="color:var(--kolixy-primary);"></i>Colis par statut</h6>
                <span class="kolixy-badge kolixy-badge-blue">Total: {{ $stats['total_packages'] ?? 0 }}</span>
            </div>

            <div class="status-grid">
                @php
                    $statusCards = [
                        ['key' => 'CREATED,AVAILABLE', 'label' => 'À enlever', 'emoji' => '📦', 'color' => '#f59e0b'],
                        ['key' => 'AT_DEPOT', 'label' => 'Au dépôt', 'emoji' => '🏭', 'color' => '#3b82f6'],
                        ['key' => 'OUT_FOR_DELIVERY', 'label' => 'En livraison', 'emoji' => '🚚', 'color' => '#8b5cf6'],
                        ['key' => 'DELIVERED', 'label' => 'Livrés', 'emoji' => '✅', 'color' => '#10b981'],
                        ['key' => 'PAID', 'label' => 'Payés', 'emoji' => '💵', 'color' => '#059669'],
                        ['key' => 'RETURN_CONFIRMED', 'label' => 'Retournés', 'emoji' => '↩️', 'color' => '#ef4444'],
                        ['key' => 'RETURNED,RETURN_IN_PROGRESS', 'label' => 'Retour en cours', 'emoji' => '🔄', 'color' => '#ec4899'],
                        ['key' => 'RETURN_ISSUE', 'label' => 'Problème', 'emoji' => '❌', 'color' => '#be123c'],
                    ];
                    $byStatus = $stats['by_status'] ?? [];
                @endphp

                @foreach($statusCards as $sc)
                    @php
                        $keys = explode(',', $sc['key']);
                        $count = 0;
                        foreach ($keys as $k) {
                            $count += $byStatus[trim($k)] ?? 0;
                        }
                    @endphp
                    <div class="pkg-status-card" style="--card-color: {{ $sc['color'] }}">
                        <div class="pkg-emoji">{{ $sc['emoji'] }}</div>
                        <div class="pkg-count">{{ $count }}</div>
                        <div class="pkg-label"><span class="pkg-dot"></span>{{ $sc['label'] }}</div>
                    </div>
                @endforeach
            </div>

            @if(isset($stats['this_month']))
            <hr class="my-3">
            <h6 class="fw-bold mb-2"><i class="fas fa-calendar-alt me-2"></i>Ce mois</h6>
            <div class="row g-2">
                <div class="col-6 col-md-3">
                    <small class="text-muted d-block">Total</small>
                    <span class="fw-bold">{{ $stats['this_month']['total'] ?? 0 }}</span>
                </div>
                <div class="col-6 col-md-3">
                    <small class="text-muted d-block">Livrés</small>
                    <span class="fw-bold text-success">{{ $stats['this_month']['delivered'] ?? 0 }}</span>
                </div>
                <div class="col-6 col-md-3">
                    <small class="text-muted d-block">En cours</small>
                    <span class="fw-bold text-info">{{ $stats['this_month']['in_progress'] ?? 0 }}</span>
                </div>
                <div class="col-6 col-md-3">
                    <small class="text-muted d-block">Retournés</small>
                    <span class="fw-bold text-danger">{{ $stats['this_month']['returned'] ?? 0 }}</span>
                </div>
            </div>
            @endif
        </div>
    </div>
    @endif

    {{-- Quick actions --}}
    <div class="kolixy-card">
        <div class="kolixy-card-body">
            <h6 class="fw-bold mb-3"><i class="fas fa-bolt me-2" style="color:var(--kolixy-accent);"></i>Actions rapides</h6>
            <div class="d-flex flex-wrap gap-2">
                <a href="{{ route('admin.kolixy.envoyer-commande') }}" class="kolixy-btn kolixy-btn-primary">
                    <i class="fas fa-paper-plane"></i> Envoyer commandes
                </a>
                <a href="{{ route('admin.kolixy.imprimer-bl') }}" class="kolixy-btn kolixy-btn-outline">
                    <i class="fas fa-print"></i> Imprimer BL
                </a>
                <a href="{{ route('admin.kolixy.verification') }}" class="kolixy-btn kolixy-btn-outline">
                    <i class="fas fa-search"></i> Vérification
                </a>
                <a href="{{ route('admin.kolixy.configuration') }}" class="kolixy-btn kolixy-btn-outline">
                    <i class="fas fa-cog"></i> Configuration
                </a>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection
