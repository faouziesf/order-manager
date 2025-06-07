@extends('layouts.admin')

@section('title', 'Détail Client - Commandes Doubles')

@push('head')
<meta name="csrf-token" content="{{ csrf_token() }}">
@endpush

@section('css')
<style>
    /* Variables CSS pour cohérence */
    :root {
        --primary: #6366f1;
        --primary-dark: #4f46e5;
        --success: #10b981;
        --warning: #f59e0b;
        --danger: #ef4444;
        --info: #3b82f6;
        --gray-50: #f9fafb;
        --gray-100: #f3f4f6;
        --gray-200: #e5e7eb;
        --gray-300: #d1d5db;
        --gray-400: #9ca3af;
        --gray-500: #6b7280;
        --gray-600: #4b5563;
        --gray-700: #374151;
        --gray-800: #1f2937;
        --gray-900: #111827;
        --white: #ffffff;
        --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
        --shadow: 0 1px 3px 0 rgb(0 0 0 / 0.1), 0 1px 2px -1px rgb(0 0 0 / 0.1);
        --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
        --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
        --shadow-xl: 0 20px 25px -5px rgb(0 0 0 / 0.1), 0 8px 10px -6px rgb(0 0 0 / 0.1);
        --radius: 0.5rem;
        --radius-lg: 0.75rem;
        --radius-xl: 1rem;
        --radius-2xl: 1.5rem;
    }

    body {
        background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
        color: var(--gray-700);
        line-height: 1.6;
        overflow-x: hidden;
    }

    .container-fluid {
        max-width: 1600px;
        margin: 0 auto;
        padding: 1rem 1.5rem 2rem;
    }

    /* Header navigation optimisé */
    .page-breadcrumb {
        background: linear-gradient(135deg, var(--white) 0%, #f8fafc 100%);
        border-radius: var(--radius-xl);
        padding: 1rem 1.5rem;
        margin-bottom: 1rem;
        box-shadow: var(--shadow);
        border: 1px solid var(--gray-200);
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .breadcrumb-path {
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .btn-back {
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
        color: var(--white);
        border: none;
        padding: 0.625rem 1rem;
        border-radius: var(--radius);
        font-weight: 600;
        text-decoration: none;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        transition: all 0.3s ease;
        box-shadow: var(--shadow-sm);
        font-size: 0.875rem;
    }

    .btn-back:hover {
        transform: translateY(-1px);
        box-shadow: var(--shadow-md);
        color: var(--white);
    }

    .breadcrumb-title h1 {
        font-size: 1.25rem;
        font-weight: 700;
        color: var(--gray-900);
        margin: 0;
    }

    .breadcrumb-title p {
        color: var(--gray-600);
        margin: 0;
        font-size: 0.8rem;
    }

    /* Header client optimisé */
    .client-header {
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
        color: var(--white);
        border-radius: var(--radius-xl);
        padding: 1.5rem 2rem;
        margin-bottom: 1rem;
        box-shadow: var(--shadow-lg);
        position: relative;
        overflow: hidden;
    }

    .client-header::before {
        content: '';
        position: absolute;
        top: -30%;
        right: -5%;
        width: 150px;
        height: 150px;
        background: rgba(255, 255, 255, 0.1);
        border-radius: 50%;
    }

    .client-info {
        position: relative;
        z-index: 2;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 2rem;
    }

    .client-details h2 {
        font-size: 1.5rem;
        font-weight: 800;
        margin-bottom: 0.5rem;
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .client-details .client-meta {
        display: flex;
        flex-direction: column;
        gap: 0.25rem;
        opacity: 0.9;
    }

    .client-meta-item {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.85rem;
    }

    .client-meta-item i {
        width: 14px;
        text-align: center;
        opacity: 0.8;
    }

    /* Stats cards compactes */
    .client-stats {
        display: flex;
        gap: 1rem;
    }

    .stat-card {
        background: rgba(255, 255, 255, 0.15);
        border-radius: var(--radius-lg);
        padding: 1rem;
        text-align: center;
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.2);
        transition: all 0.3s ease;
        min-width: 100px;
    }

    .stat-card:hover {
        background: rgba(255, 255, 255, 0.2);
        transform: translateY(-1px);
    }

    .stat-number {
        font-size: 1.25rem;
        font-weight: 800;
        margin-bottom: 0.25rem;
        line-height: 1;
    }

    .stat-label {
        font-size: 0.7rem;
        opacity: 0.9;
        font-weight: 500;
    }

    /* Toolbar de sélection optimisé */
    .selection-toolbar {
        background: linear-gradient(135deg, var(--white) 0%, #f8fafc 100%);
        border-radius: var(--radius-xl);
        padding: 1rem 1.5rem;
        margin-bottom: 1rem;
        box-shadow: var(--shadow-md);
        border: 1px solid var(--gray-200);
        display: none;
        transition: all 0.3s ease;
    }

    .selection-toolbar.show {
        display: block;
        animation: slideDown 0.3s ease;
    }

    .selection-content {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 1rem;
    }

    .selection-info {
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .selection-badge {
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
        color: var(--white);
        padding: 0.375rem 0.75rem;
        border-radius: var(--radius);
        font-weight: 700;
        font-size: 0.8rem;
        box-shadow: var(--shadow-sm);
    }

    .action-buttons {
        display: flex;
        gap: 0.75rem;
        flex-wrap: wrap;
    }

    .btn-action {
        padding: 0.625rem 1.25rem;
        border-radius: var(--radius);
        font-weight: 600;
        border: none;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        cursor: pointer;
        font-size: 0.8rem;
    }

    .btn-action:hover {
        transform: translateY(-1px);
        box-shadow: var(--shadow-md);
    }

    .btn-action:disabled {
        opacity: 0.5;
        cursor: not-allowed;
        transform: none;
    }

    .btn-clear-selection {
        background: linear-gradient(135deg, var(--gray-600) 0%, var(--gray-700) 100%);
        color: var(--white);
    }

    .btn-merge-selected {
        background: linear-gradient(135deg, var(--success) 0%, #059669 100%);
        color: var(--white);
    }

    .btn-cancel-selected {
        background: linear-gradient(135deg, var(--danger) 0%, #dc2626 100%);
        color: var(--white);
    }

    /* Grille des commandes optimisée - COMPACTE */
    .orders-container {
        margin-bottom: 1rem;
    }

    .orders-header {
        background: linear-gradient(135deg, var(--white) 0%, #f8fafc 100%);
        border-radius: var(--radius-xl) var(--radius-xl) 0 0;
        padding: 1rem 1.5rem;
        border: 1px solid var(--gray-200);
        border-bottom: none;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .orders-title {
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .orders-title h5 {
        font-size: 1rem;
        font-weight: 700;
        color: var(--gray-900);
        margin: 0;
    }

    .orders-count {
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
        color: var(--white);
        padding: 0.25rem 0.625rem;
        border-radius: var(--radius);
        font-size: 0.75rem;
        font-weight: 700;
    }

    .orders-controls {
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .select-all-wrapper {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.8rem;
        color: var(--gray-700);
    }

    .select-all-checkbox {
        width: 18px;
        height: 18px;
        accent-color: var(--primary);
        cursor: pointer;
    }

    .orders-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(380px, 1fr));
        gap: 1rem;
        padding: 1.25rem;
        background: linear-gradient(135deg, var(--white) 0%, #f8fafc 100%);
        border: 1px solid var(--gray-200);
        border-radius: 0 0 var(--radius-xl) var(--radius-xl);
        box-shadow: var(--shadow-lg);
        max-height: 70vh;
        overflow-y: auto;
    }

    /* Cards de commandes COMPACTES */
    .order-card {
        background: var(--white);
        border-radius: var(--radius-lg);
        overflow: hidden;
        box-shadow: var(--shadow-md);
        transition: all 0.3s ease;
        border: 2px solid transparent;
        position: relative;
        min-height: auto;
    }

    .order-card:hover {
        transform: translateY(-2px);
        box-shadow: var(--shadow-lg);
    }

    .order-card.duplicate {
        border-color: #d4a147;
        box-shadow: 0 4px 20px rgba(212, 161, 71, 0.2);
    }

    .order-card.selected {
        border-color: var(--primary);
        box-shadow: 0 8px 30px rgba(99, 102, 241, 0.3);
        transform: translateY(-1px);
    }

    /* Sélecteur de commande repositionné */
    .btn-select-order {
        position: absolute;
        top: 0.75rem;
        left: 0.75rem;
        width: 22px;
        height: 22px;
        border-radius: var(--radius);
        border: 2px solid var(--gray-300);
        background: var(--white);
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.3s ease;
        z-index: 10;
        box-shadow: var(--shadow-sm);
    }

    .btn-select-order:hover {
        border-color: var(--primary);
        transform: scale(1.05);
    }

    .btn-select-order.selected {
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
        border-color: var(--primary);
        color: var(--white);
        box-shadow: var(--shadow-md);
    }

    .btn-select-order i {
        font-size: 0.7rem;
        transition: all 0.2s ease;
    }

    /* Header de commande COMPACT */
    .order-header {
        padding: 1rem 1rem 1rem 3rem;
        background: linear-gradient(135deg, #f8fafc 0%, var(--gray-100) 100%);
        border-bottom: 1px solid var(--gray-200);
        position: relative;
    }

    .order-meta {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 0.5rem;
    }

    .order-id {
        font-size: 0.9rem;
        font-weight: 700;
        color: var(--gray-900);
        margin-bottom: 0.25rem;
    }

    .order-date {
        color: var(--gray-600);
        font-size: 0.75rem;
    }

    .order-badges {
        display: flex;
        flex-direction: column;
        align-items: flex-end;
        gap: 0.25rem;
    }

    .order-status-badges {
        display: flex;
        gap: 0.25rem;
        flex-wrap: wrap;
    }

    .badge {
        padding: 0.2rem 0.5rem;
        border-radius: var(--radius);
        font-size: 0.65rem;
        font-weight: 600;
    }

    .badge-doublé {
        background: linear-gradient(135deg, #d4a147 0%, #b8941f 100%);
        color: var(--white);
        border: none;
        font-weight: 700;
        padding: 0.2rem 0.5rem;
        border-radius: var(--radius);
        font-size: 0.65rem;
        box-shadow: var(--shadow-sm);
    }

    .scheduled-info {
        margin-top: 0.25rem;
        color: var(--gray-600);
        font-size: 0.7rem;
        display: flex;
        align-items: center;
        gap: 0.25rem;
    }

    /* Corps de commande COMPACT */
    .order-body {
        padding: 1rem;
    }

    .order-info-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 0.75rem;
        margin-bottom: 1rem;
    }

    .info-item {
        display: flex;
        flex-direction: column;
        gap: 0.2rem;
    }

    .info-label {
        font-size: 0.65rem;
        font-weight: 700;
        color: var(--gray-600);
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .info-value {
        font-size: 0.8rem;
        color: var(--gray-900);
        font-weight: 500;
    }

    .amount-highlight {
        color: var(--success);
        font-weight: 700;
        font-size: 0.9rem;
    }

    .address-info {
        grid-column: 1 / -1;
    }

    .address-info .info-value {
        color: var(--gray-700);
        line-height: 1.3;
        font-size: 0.75rem;
    }

    /* Section produits COMPACTE */
    .products-section {
        margin-bottom: 1rem;
    }

    .products-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 0.5rem;
    }

    .products-title {
        font-size: 0.75rem;
        font-weight: 700;
        color: var(--gray-800);
    }

    .products-list {
        max-height: 80px;
        overflow-y: auto;
        border: 1px solid var(--gray-200);
        border-radius: var(--radius);
        background: var(--gray-50);
    }

    .product-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0.5rem 0.75rem;
        border-bottom: 1px solid var(--gray-200);
        transition: background-color 0.2s ease;
    }

    .product-item:last-child {
        border-bottom: none;
    }

    .product-item:hover {
        background: var(--white);
    }

    .product-name {
        font-size: 0.75rem;
        color: var(--gray-800);
        font-weight: 500;
    }

    .product-quantity {
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
        color: var(--white);
        padding: 0.15rem 0.4rem;
        border-radius: var(--radius);
        font-size: 0.65rem;
        font-weight: 700;
    }

    /* Notes section COMPACTE */
    .notes-section {
        margin-bottom: 1rem;
    }

    .notes-content {
        background: var(--gray-50);
        border: 1px solid var(--gray-200);
        border-radius: var(--radius);
        padding: 0.5rem;
        font-size: 0.75rem;
        color: var(--gray-700);
        line-height: 1.3;
        max-height: 50px;
        overflow-y: auto;
    }

    /* Indicateurs de compatibilité */
    .compatibility-indicator {
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
        padding: 0.2rem 0.5rem;
        border-radius: var(--radius);
        font-size: 0.65rem;
        font-weight: 600;
        margin-top: 0.5rem;
    }

    .compatible {
        background: rgba(16, 185, 129, 0.1);
        color: #059669;
        border: 1px solid rgba(16, 185, 129, 0.3);
    }

    .incompatible {
        background: rgba(239, 68, 68, 0.1);
        color: #dc2626;
        border: 1px solid rgba(239, 68, 68, 0.3);
    }

    /* Footer de commande COMPACT */
    .order-footer {
        padding: 0.75rem 1rem;
        background: linear-gradient(135deg, #f8fafc 0%, var(--gray-100) 100%);
        border-top: 1px solid var(--gray-200);
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .order-attempts {
        display: flex;
        flex-direction: column;
        gap: 0.2rem;
    }

    .attempts-count {
        font-size: 0.7rem;
        color: var(--gray-600);
    }

    .last-attempt {
        font-size: 0.65rem;
        color: var(--gray-500);
    }

    .footer-actions {
        display: flex;
        gap: 0.4rem;
        align-items: center;
    }

    .btn-view-order, .btn-order-history {
        border: none;
        padding: 0.4rem 0.7rem;
        border-radius: var(--radius);
        font-size: 0.7rem;
        font-weight: 600;
        text-decoration: none;
        transition: all 0.2s ease;
        display: flex;
        align-items: center;
        gap: 0.3rem;
        cursor: pointer;
    }

    .btn-view-order {
        background: linear-gradient(135deg, var(--info) 0%, #1d4ed8 100%);
        color: var(--white);
    }

    .btn-order-history {
        background: linear-gradient(135deg, var(--warning) 0%, #d97706 100%);
        color: var(--white);
    }

    .btn-view-order:hover, .btn-order-history:hover {
        transform: translateY(-1px);
        box-shadow: var(--shadow-sm);
        color: var(--white);
    }

    /* Modal historique commande optimisé */
    .history-item {
        background: var(--gray-50);
        border: 1px solid var(--gray-200);
        border-radius: var(--radius);
        padding: 0.75rem;
        margin-bottom: 0.5rem;
        transition: all 0.2s ease;
    }

    .history-item:hover {
        background: var(--white);
        box-shadow: var(--shadow-sm);
    }

    .history-item:last-child {
        margin-bottom: 0;
    }

    .history-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 0.4rem;
    }

    .history-action {
        font-weight: 700;
        color: var(--gray-900);
        font-size: 0.85rem;
    }

    .history-date {
        font-size: 0.75rem;
        color: var(--gray-500);
    }

    .history-details {
        font-size: 0.8rem;
        color: var(--gray-700);
        line-height: 1.3;
    }

    /* Spinner Bootstrap style */
    .spinner-border {
        display: inline-block;
        width: 2rem;
        height: 2rem;
        vertical-align: text-bottom;
        border: 0.25em solid currentColor;
        border-right-color: transparent;
        border-radius: 50%;
        animation: spinner-border 0.75s linear infinite;
    }

    @keyframes spinner-border {
        to {
            transform: rotate(360deg);
        }
    }

    .visually-hidden {
        position: absolute !important;
        width: 1px !important;
        height: 1px !important;
        padding: 0 !important;
        margin: -1px !important;
        overflow: hidden !important;
        clip: rect(0, 0, 0, 0) !important;
        white-space: nowrap !important;
        border: 0 !important;
    }

    /* Animations */
    @keyframes slideDown {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* Loading overlay */
    .loading-overlay {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.6);
        display: none;
        justify-content: center;
        align-items: center;
        z-index: 9999;
        backdrop-filter: blur(4px);
    }

    .loading-spinner {
        background: var(--white);
        padding: 2rem;
        border-radius: var(--radius-xl);
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 1rem;
        box-shadow: var(--shadow-xl);
    }

    .spinner {
        width: 40px;
        height: 40px;
        border: 3px solid var(--gray-200);
        border-top: 3px solid var(--primary);
        border-radius: 50%;
        animation: spin 1s linear infinite;
    }

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    /* Responsive optimisations */
    @media (max-width: 1400px) {
        .container-fluid {
            padding: 1rem;
        }
        
        .orders-grid {
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
        }
        
        .client-stats {
            flex-wrap: wrap;
        }
    }

    @media (max-width: 768px) {
        .client-info {
            flex-direction: column;
            gap: 1rem;
        }

        .client-stats {
            justify-content: center;
        }

        .orders-grid {
            grid-template-columns: 1fr;
            padding: 1rem;
            max-height: 60vh;
        }

        .selection-content {
            flex-direction: column;
            align-items: stretch;
        }

        .action-buttons {
            justify-content: center;
        }

        .timeline {
            padding-left: 1.25rem;
        }

        .timeline-content {
            margin-left: 0.4rem;
        }

        .order-info-grid {
            grid-template-columns: 1fr;
        }

        .page-breadcrumb {
            flex-direction: column;
            gap: 0.75rem;
            text-align: center;
        }
    }

    /* Custom scrollbars plus subtils */
    .orders-grid::-webkit-scrollbar,
    .products-list::-webkit-scrollbar,
    .notes-content::-webkit-scrollbar,
    .order-history-content::-webkit-scrollbar {
        width: 4px;
        height: 4px;
    }

    .orders-grid::-webkit-scrollbar-track,
    .products-list::-webkit-scrollbar-track,
    .notes-content::-webkit-scrollbar-track,
    .order-history-content::-webkit-scrollbar-track {
        background: var(--gray-100);
        border-radius: 2px;
    }

    .orders-grid::-webkit-scrollbar-thumb,
    .products-list::-webkit-scrollbar-thumb,
    .notes-content::-webkit-scrollbar-thumb,
    .order-history-content::-webkit-scrollbar-thumb {
        background: var(--gray-400);
        border-radius: 2px;
    }

    .orders-grid::-webkit-scrollbar-thumb:hover,
    .products-list::-webkit-scrollbar-thumb:hover,
    .notes-content::-webkit-scrollbar-thumb:hover,
    .order-history-content::-webkit-scrollbar-thumb:hover {
        background: var(--gray-500);
    }

    /* Amélioration pour l'accessibilité */
    .cursor-pointer {
        cursor: pointer;
    }

    /* Badge amélioré */
    .bg-info { background: linear-gradient(135deg, var(--info) 0%, #1d4ed8 100%) !important; }
    .bg-success { background: linear-gradient(135deg, var(--success) 0%, #059669 100%) !important; }
    .bg-danger { background: linear-gradient(135deg, var(--danger) 0%, #dc2626 100%) !important; }
    .bg-warning { background: linear-gradient(135deg, var(--warning) 0%, #d97706 100%) !important; }
    .bg-primary { background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%) !important; }
    .bg-secondary { background: linear-gradient(135deg, var(--gray-600) 0%, var(--gray-700) 100%) !important; }
</style>
@endsection

@section('content')
<div class="container-fluid">
    <!-- Breadcrumb navigation -->
    <div class="page-breadcrumb">
        <div class="breadcrumb-path">
            <a href="/admin/duplicates" class="btn-back">
                <i class="fas fa-arrow-left"></i>Retour
            </a>
            <div class="breadcrumb-title">
                <h1>Détail Client - {{ $phone }}</h1>
                <p>Analyse des {{ $orders->count() }} commandes du client</p>
            </div>
        </div>
    </div>

    <!-- En-tête Client optimisé -->
    <div class="client-header">
        <div class="client-info">
            <div class="client-details">
                <h2>
                    <i class="fas fa-phone"></i>{{ $phone }}
                </h2>
                <div class="client-meta">
                    @if($orders->first()->customer_name)
                        <div class="client-meta-item">
                            <i class="fas fa-user"></i>
                            <span>{{ $orders->first()->customer_name }}</span>
                        </div>
                    @endif
                    @if($orders->first()->customer_address)
                        <div class="client-meta-item">
                            <i class="fas fa-map-marker-alt"></i>
                            <span>{{ Str::limit($orders->first()->customer_address, 50) }}</span>
                        </div>
                    @endif
                    <div class="client-meta-item">
                        <i class="fas fa-calendar-plus"></i>
                        <span>Client depuis {{ $orders->min('created_at')->format('M Y') }}</span>
                    </div>
                </div>
            </div>
            <div class="client-stats">
                <div class="stat-card">
                    <div class="stat-number">{{ $orders->count() }}</div>
                    <div class="stat-label">Commandes</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number">{{ $orders->where('is_duplicate', true)->count() }}</div>
                    <div class="stat-label">Doublons</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number">{{ number_format($orders->sum('total_price'), 2) }}</div>
                    <div class="stat-label">TND</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Barre d'outils de sélection -->
    <div class="selection-toolbar" id="selectionToolbar">
        <div class="selection-content">
            <div class="selection-info">
                <div class="selection-badge">
                    <i class="fas fa-check-square"></i>
                    <span id="selectedCount">0</span> sélectionnée(s)
                </div>
            </div>
            <div class="action-buttons">
                <button class="btn-action btn-clear-selection" id="btnClearSelection">
                    <i class="fas fa-times"></i>Effacer
                </button>
                <button class="btn-action btn-merge-selected" id="btnMergeSelected">
                    <i class="fas fa-compress-arrows-alt"></i>Fusionner
                </button>
                <button class="btn-action btn-cancel-selected" id="btnCancelSelected">
                    <i class="fas fa-ban"></i>Annuler
                </button>
            </div>
        </div>
    </div>

    <!-- Grille des Commandes optimisée -->
    <div class="orders-container">
        <div class="orders-header">
            <div class="orders-title">
                <h5>
                    <i class="fas fa-shopping-cart"></i>Commandes
                </h5>
                <span class="orders-count">{{ $orders->count() }}</span>
            </div>
            <div class="orders-controls">
                <div class="select-all-wrapper">
                    <input type="checkbox" id="selectAllOrders" class="select-all-checkbox">
                    <label for="selectAllOrders" class="cursor-pointer">Tout sélectionner</label>
                </div>
                <button class="btn-action btn-clear-selection" onclick="refreshPage()">
                    <i class="fas fa-sync-alt"></i>Actualiser
                </button>
            </div>
        </div>
        
        <div class="orders-grid">
            @foreach($orders as $order)
                <div class="order-card {{ $order->is_duplicate ? 'duplicate' : '' }}" data-order-id="{{ $order->id }}">
                    @if($order->is_duplicate && in_array($order->status, ['nouvelle', 'datée']))
                        <div class="btn-select-order" data-order-id="{{ $order->id }}">
                            <i class="fas fa-check" style="display: none;"></i>
                        </div>
                    @endif
                    
                    <div class="order-header">
                        <div class="order-meta">
                            <div>
                                <div class="order-id">Commande #{{ $order->id }}</div>
                                <div class="order-date">{{ $order->created_at->format('d/m/Y à H:i') }}</div>
                            </div>
                            <div class="order-badges">
                                <div class="order-status-badges">
                                    @switch($order->status)
                                        @case('nouvelle')
                                            <span class="badge bg-info">Nouvelle</span>
                                            @break
                                        @case('confirmée')
                                            <span class="badge bg-success">Confirmée</span>
                                            @break
                                        @case('annulée')
                                            <span class="badge bg-danger">Annulée</span>
                                            @break
                                        @case('datée')
                                            <span class="badge bg-warning">Datée</span>
                                            @break
                                        @case('en_route')
                                            <span class="badge bg-primary">En route</span>
                                            @break
                                        @case('livrée')
                                            <span class="badge bg-success">Livrée</span>
                                            @break
                                        @default
                                            <span class="badge bg-secondary">{{ ucfirst($order->status) }}</span>
                                    @endswitch
                                    
                                    @if($order->is_duplicate)
                                        <span class="badge-doublé">Doublon</span>
                                    @endif
                                    
                                    @if($order->priority !== 'normale')
                                        <span class="badge bg-danger">{{ ucfirst($order->priority) }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        
                        @if($order->scheduled_date)
                            <div class="scheduled-info">
                                <i class="fas fa-calendar"></i>
                                <span>Programmée: {{ $order->scheduled_date->format('d/m/Y') }}</span>
                            </div>
                        @endif
                    </div>
                    
                    <div class="order-body">
                        <div class="order-info-grid">
                            <div class="info-item">
                                <div class="info-label">Client</div>
                                <div class="info-value">{{ $order->customer_name ?: 'Non spécifié' }}</div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">Montant</div>
                                <div class="info-value amount-highlight">{{ number_format($order->total_price, 3) }} TND</div>
                            </div>
                            @if($order->customer_address)
                                <div class="info-item address-info">
                                    <div class="info-label">Adresse</div>
                                    <div class="info-value">{{ Str::limit($order->customer_address, 70) }}</div>
                                </div>
                            @endif
                        </div>
                        
                        <div class="products-section">
                            <div class="products-header">
                                <div class="products-title">
                                    <i class="fas fa-box"></i>
                                    Produits ({{ $order->items->count() }})
                                </div>
                            </div>
                            <div class="products-list">
                                @foreach($order->items as $item)
                                    <div class="product-item">
                                        <span class="product-name">{{ Str::limit($item->product->name ?? 'Produit supprimé', 20) }}</span>
                                        <span class="product-quantity">{{ $item->quantity }}x</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        
                        @if($order->notes)
                            <div class="notes-section">
                                <div class="info-label">Notes</div>
                                <div class="notes-content">{{ $order->notes }}</div>
                            </div>
                        @endif
                        
                        <!-- Indicateur de compatibilité -->
                        @if($order->is_duplicate && in_array($order->status, ['nouvelle', 'datée']))
                            <div class="compatibility-indicator compatible">
                                <i class="fas fa-check"></i>Fusionnable
                            </div>
                        @elseif($order->is_duplicate)
                            <div class="compatibility-indicator incompatible">
                                <i class="fas fa-times"></i>Non fusionnable
                            </div>
                        @endif
                    </div>
                    
                    <div class="order-footer">
                        <div class="order-attempts">
                            <div class="attempts-count">
                                <i class="fas fa-redo"></i>
                                {{ $order->attempts_count }} tentative(s)
                            </div>
                            @if($order->last_attempt_at)
                                <div class="last-attempt">
                                    <i class="fas fa-clock"></i>
                                    {{ $order->last_attempt_at->diffForHumans() }}
                                </div>
                            @endif
                        </div>
                        
                        <div class="footer-actions">
                            <button class="btn-order-history" onclick="showOrderHistory({{ $order->id }})">
                                <i class="fas fa-history"></i>
                                Historique
                            </button>
                            <a href="{{ route('admin.orders.show', $order->id) }}" 
                               class="btn-view-order" target="_blank">
                                <i class="fas fa-external-link-alt"></i>
                                Détails
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    </div>
</div>

<!-- Modal Historique Commande Individuelle -->
<div class="modal fade" id="orderHistoryModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-history"></i>Historique de la Commande
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="orderHistoryContent">
                    <!-- Contenu chargé via AJAX -->
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Fusion Sélective -->
<div class="modal fade" id="selectiveMergeModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-compress-arrows-alt"></i>Fusion Sélective
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Attention:</strong> Cette action va fusionner les commandes sélectionnées.
                </div>
                
                <div class="mb-3">
                    <label class="form-label fw-semibold">Note de fusion:</label>
                    <textarea class="form-control" id="selectiveMergeNote" rows="3" 
                              placeholder="Raison de la fusion..."></textarea>
                </div>
                
                <div id="selectedOrdersPreview">
                    <!-- Liste des commandes sélectionnées -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <button type="button" class="btn btn-success" id="btnConfirmSelectiveMerge">
                    <i class="fas fa-check me-2"></i>Confirmer
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Annulation Groupée -->
<div class="modal fade" id="cancelOrdersModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-ban"></i>Annuler les Commandes
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    Annuler <span id="cancelCount">0</span> commande(s) ?
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Raison:</label>
                    <textarea class="form-control" id="cancelReason" rows="3" 
                              placeholder="Raison de l'annulation..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <button type="button" class="btn btn-danger" id="btnConfirmCancel">
                    <i class="fas fa-ban me-2"></i>Confirmer
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Loading Overlay -->
<div class="loading-overlay" id="loadingOverlay">
    <div class="loading-spinner">
        <div class="spinner"></div>
        <p class="mb-0 fw-semibold">Traitement en cours...</p>
    </div>
</div>

@endsection

@section('scripts')
<script>
// Setup CSRF token for AJAX requests
$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});

$(document).ready(function() {
    let selectedOrders = new Set();
    
    // Sélection individuelle des commandes
    $('.btn-select-order').click(function(e) {
        e.stopPropagation();
        const orderId = $(this).data('order-id');
        const orderCard = $(`.order-card[data-order-id="${orderId}"]`);
        
        if (selectedOrders.has(orderId)) {
            selectedOrders.delete(orderId);
            $(this).removeClass('selected');
            $(this).find('i').hide();
            orderCard.removeClass('selected');
        } else {
            selectedOrders.add(orderId);
            $(this).addClass('selected');
            $(this).find('i').show();
            orderCard.addClass('selected');
        }
        
        updateSelectionUI();
        updateSelectAllCheckbox();
    });
    
    // Sélectionner toutes les commandes
    $('#selectAllOrders').change(function() {
        const isChecked = $(this).is(':checked');
        
        if (isChecked) {
            // Sélectionner toutes les commandes fusionnables
            $('.btn-select-order').each(function() {
                const orderId = $(this).data('order-id');
                const orderCard = $(`.order-card[data-order-id="${orderId}"]`);
                
                selectedOrders.add(orderId);
                $(this).addClass('selected');
                $(this).find('i').show();
                orderCard.addClass('selected');
            });
        } else {
            // Désélectionner toutes
            selectedOrders.clear();
            $('.order-card').removeClass('selected');
            $('.btn-select-order').removeClass('selected');
            $('.btn-select-order i').hide();
        }
        
        updateSelectionUI();
    });
    
    // Mettre à jour le checkbox "Tout sélectionner"
    function updateSelectAllCheckbox() {
        const totalSelectable = $('.btn-select-order').length;
        const selectedCount = selectedOrders.size;
        
        if (selectedCount === 0) {
            $('#selectAllOrders').prop('checked', false).prop('indeterminate', false);
        } else if (selectedCount === totalSelectable) {
            $('#selectAllOrders').prop('checked', true).prop('indeterminate', false);
        } else {
            $('#selectAllOrders').prop('checked', false).prop('indeterminate', true);
        }
    }
    
    // Effacer sélection
    $('#btnClearSelection').click(function() {
        if (selectedOrders.size === 0) return;
        
        selectedOrders.clear();
        $('.order-card').removeClass('selected');
        $('.btn-select-order').removeClass('selected');
        $('.btn-select-order i').hide();
        $('#selectAllOrders').prop('checked', false).prop('indeterminate', false);
        updateSelectionUI();
        
        showInfo(`Sélection effacée`);
    });
    
    // Fusionner sélection avec validation
    $('#btnMergeSelected').click(function() {
        if (selectedOrders.size < 2) {
            showError('Sélectionnez au moins 2 commandes');
            return;
        }
        
        const selectedOrdersArray = Array.from(selectedOrders);
        
        // Vérifier la compatibilité
        let compatible = true;
        selectedOrdersArray.forEach(orderId => {
            const orderCard = $(`.order-card[data-order-id="${orderId}"]`);
            const hasCompatibleIndicator = orderCard.find('.compatible').length > 0;
            if (!hasCompatibleIndicator) {
                compatible = false;
            }
        });
        
        if (!compatible) {
            showError('Seules les commandes fusionnables peuvent être sélectionnées');
            return;
        }
        
        // Générer l'aperçu
        let preview = `<h6>Commandes à fusionner:</h6><ul class="list-group">`;
        selectedOrdersArray.forEach(orderId => {
            const orderCard = $(`.order-card[data-order-id="${orderId}"]`);
            const orderNumber = orderCard.find('.order-id').text();
            const amount = orderCard.find('.amount-highlight').text();
            
            preview += `<li class="list-group-item d-flex justify-content-between">
                <span>${orderNumber}</span>
                <strong>${amount}</strong>
            </li>`;
        });
        preview += '</ul>';
        
        $('#selectedOrdersPreview').html(preview);
        $('#selectiveMergeModal').modal('show');
    });
    
    // Annuler sélection
    $('#btnCancelSelected').click(function() {
        if (selectedOrders.size === 0) {
            showError('Sélectionnez au moins une commande');
            return;
        }
        
        $('#cancelCount').text(selectedOrders.size);
        $('#cancelOrdersModal').modal('show');
    });
    
    // Confirmer fusion sélective
    $('#btnConfirmSelectiveMerge').click(function() {
        const note = $('#selectiveMergeNote').val().trim();
        const orderIds = Array.from(selectedOrders);
        
        if (!note) {
            showError('Veuillez indiquer une raison');
            return;
        }
        
        showLoading();
        
        $.post('/admin/duplicates/selective-merge', {
            order_ids: orderIds,
            note: note,
            _token: '{{ csrf_token() }}'
        })
        .done(function(response) {
            hideLoading();
            $('#selectiveMergeModal').modal('hide');
            
            if (response.success) {
                showSuccess(response.message);
                setTimeout(() => window.location.reload(), 1500);
            } else {
                showError(response.message);
            }
        })
        .fail(function() {
            hideLoading();
            showError('Erreur lors de la fusion');
        });
    });
    
    // Confirmer annulation
    $('#btnConfirmCancel').click(function() {
        const reason = $('#cancelReason').val().trim();
        const orderIds = Array.from(selectedOrders);
        
        if (!reason) {
            showError('Veuillez indiquer une raison');
            return;
        }
        
        showLoading();
        
        let completed = 0;
        const total = orderIds.length;
        
        orderIds.forEach(orderId => {
            $.post('/admin/duplicates/cancel', {
                order_id: orderId,
                reason: reason,
                _token: '{{ csrf_token() }}'
            })
            .always(function() {
                completed++;
                if (completed === total) {
                    hideLoading();
                    $('#cancelOrdersModal').modal('hide');
                    showSuccess(`${total} commande(s) annulée(s)`);
                    setTimeout(() => window.location.reload(), 1500);
                }
            });
        });
    });
    
    // Mise à jour de l'UI de sélection
    function updateSelectionUI() {
        const count = selectedOrders.size;
        $('#selectedCount').text(count);
        
        if (count > 0) {
            $('#selectionToolbar').addClass('show');
        } else {
            $('#selectionToolbar').removeClass('show');
        }
        
        $('#btnMergeSelected').prop('disabled', count < 2);
        $('#btnCancelSelected').prop('disabled', count === 0);
    }
    
    // Fonctions utilitaires
    function showLoading() {
        $('#loadingOverlay').show();
    }
    
    function hideLoading() {
        $('#loadingOverlay').hide();
    }
    
    function showSuccess(message) {
        showAlert('success', 'fas fa-check-circle', message);
    }
    
    function showError(message) {
        showAlert('danger', 'fas fa-exclamation-circle', message);
    }
    
    function showInfo(message) {
        showAlert('info', 'fas fa-info-circle', message);
    }
    
    function showAlert(type, icon, message) {
        const alertId = 'alert-' + Date.now();
        const alert = $(`
            <div id="${alertId}" class="alert alert-${type} alert-dismissible fade show position-fixed" 
                 style="top: 20px; right: 20px; z-index: 9999; min-width: 300px;">
                <i class="${icon} me-2"></i>${message}
                <button type="button" class="btn-close" onclick="$('#${alertId}').fadeOut()"></button>
            </div>
        `);
        
        $('body').append(alert);
        
        setTimeout(() => {
            alert.fadeOut(() => alert.remove());
        }, 4000);
    }
});

// Fonction pour afficher l'historique d'une commande spécifique
function showOrderHistory(orderId) {
    // Afficher le loading
    $('#orderHistoryContent').html(`
        <div class="text-center py-4">
            <div class="spinner-border" role="status">
                <span class="visually-hidden">Chargement...</span>
            </div>
            <p class="mt-2">Chargement de l'historique...</p>
        </div>
    `);
    
    // Mettre à jour le titre du modal
    $('#orderHistoryModal .modal-title').html(`
        <i class="fas fa-history"></i>Historique de la Commande #${orderId}
    `);
    
    // Afficher le modal
    $('#orderHistoryModal').modal('show');
    
    // Charger les données via AJAX
    $.ajax({
        url: `/admin/orders/${orderId}/history-modal`,
        method: 'GET',
        success: function(response) {
            let content = '';
            
            if (response.history && response.history.length > 0) {
                response.history.forEach(function(item) {
                    content += `
                        <div class="history-item">
                            <div class="history-header">
                                <div class="history-action">${item.action_label || item.action}</div>
                                <div class="history-date">${item.formatted_date || item.created_at}</div>
                            </div>
                            <div class="history-details">
                                ${item.notes || 'Aucune note'}
                                ${item.user_name ? '<br><small><strong>Par:</strong> ' + item.user_name + '</small>' : ''}
                            </div>
                        </div>
                    `;
                });
            } else {
                content = `
                    <div class="text-center text-muted py-4">
                        <i class="fas fa-history fa-2x mb-2"></i>
                        <h6>Aucun historique</h6>
                        <p>Cette commande n'a pas encore d'historique d'actions.</p>
                    </div>
                `;
            }
            
            $('#orderHistoryContent').html(content);
        },
        error: function() {
            $('#orderHistoryContent').html(`
                <div class="text-center text-danger py-4">
                    <i class="fas fa-exclamation-triangle fa-2x mb-2"></i>
                    <h6>Erreur de chargement</h6>
                    <p>Impossible de charger l'historique de cette commande.</p>
                </div>
            `);
        }
    });
}

// Fonction globale pour actualiser la page
function refreshPage() {
    window.location.reload();
}

// Raccourcis clavier
$(document).keydown(function(e) {
    // Escape : Effacer sélection
    if (e.key === 'Escape') {
        $('#btnClearSelection').click();
    }
    
    // Ctrl+A : Sélectionner tout
    if (e.ctrlKey && e.key === 'a') {
        e.preventDefault();
        $('#selectAllOrders').prop('checked', true).trigger('change');
    }
    
    // F5 : Actualiser
    if (e.key === 'F5') {
        e.preventDefault();
        refreshPage();
    }
});

// Fonctions utilitaires globales
function showLoading() {
    $('#loadingOverlay').show();
}

function hideLoading() {
    $('#loadingOverlay').hide();
}

function showSuccess(message) {
    showAlert('success', 'fas fa-check-circle', message);
}

function showError(message) {
    showAlert('danger', 'fas fa-exclamation-circle', message);
}

function showInfo(message) {
    showAlert('info', 'fas fa-info-circle', message);
}

function showAlert(type, icon, message) {
    const alertId = 'alert-' + Date.now();
    const alert = $(`
        <div id="${alertId}" class="alert alert-${type} alert-dismissible fade show position-fixed" 
             style="top: 20px; right: 20px; z-index: 9999; min-width: 300px;">
            <i class="${icon} me-2"></i>${message}
            <button type="button" class="btn-close" onclick="$('#${alertId}').fadeOut()"></button>
        </div>
    `);
    
    $('body').append(alert);
    
    setTimeout(() => {
        alert.fadeOut(() => alert.remove());
    }, 4000);
}
</script>
@endsection