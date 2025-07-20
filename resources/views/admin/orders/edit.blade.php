@extends('layouts.admin')

@section('title', 'Modifier Commande #' . str_pad($order->id, 6, '0', STR_PAD_LEFT))
@section('page-title', 'Modifier Commande #' . str_pad($order->id, 6, '0', STR_PAD_LEFT))

@section('css')
<style>
    :root {
        --royal-blue: #1e3a8a;
        --royal-blue-light: #3b82f6;
        --royal-blue-dark: #1e40af;
        --success: #10b981;
        --warning: #f59e0b;
        --danger: #ef4444;
        --glass-bg: rgba(255, 255, 255, 0.98);
        --shadow: 0 2px 15px rgba(30, 58, 138, 0.08);
        --border-radius: 8px;
        --transition: all 0.2s ease;
    }

    body {
        background: linear-gradient(135deg, #f1f5f9 0%, #e2e8f0 100%);
        font-family: 'Inter', sans-serif;
    }

    .page-container {
        max-width: 1400px;
        margin: 0 auto;
        padding: 1rem;
    }

    .page-header {
        background: linear-gradient(135deg, var(--royal-blue) 0%, var(--royal-blue-dark) 100%);
        color: white;
        padding: 1.25rem 1.5rem;
        border-radius: var(--border-radius);
        margin-bottom: 1rem;
        box-shadow: var(--shadow);
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 1rem;
    }

    .page-header-left h1 {
        font-size: 1.5rem;
        font-weight: 700;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .order-status-badge {
        padding: 0.25rem 0.75rem;
        border-radius: 12px;
        font-size: 0.75rem;
        font-weight: 600;
        background: rgba(255, 255, 255, 0.2);
        margin-left: 0.5rem;
    }

    .breadcrumb {
        background: transparent;
        margin: 0.5rem 0 0 0;
        padding: 0;
        font-size: 0.875rem;
    }

    .breadcrumb-item a {
        color: rgba(255, 255, 255, 0.8);
        text-decoration: none;
    }

    .breadcrumb-item.active {
        color: white;
    }

    .header-actions {
        display: flex;
        gap: 0.75rem;
        align-items: center;
        flex-wrap: wrap;
    }

    .header-btn {
        background: rgba(255, 255, 255, 0.2);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.3);
        color: white;
        padding: 0.625rem 1.25rem;
        border-radius: 6px;
        font-weight: 600;
        text-decoration: none;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        transition: var(--transition);
        cursor: pointer;
        font-size: 0.875rem;
    }

    .header-btn:hover {
        background: rgba(255, 255, 255, 0.3);
        color: white;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
    }

    .header-btn.btn-call {
        background: linear-gradient(135deg, rgba(16, 185, 129, 0.8) 0%, rgba(5, 150, 105, 0.8) 100%);
    }

    .header-btn.btn-history {
        background: linear-gradient(135deg, rgba(139, 92, 246, 0.8) 0%, rgba(124, 58, 237, 0.8) 100%);
    }

    .header-btn.btn-duplicates {
        background: linear-gradient(135deg, rgba(245, 158, 11, 0.8) 0%, rgba(217, 119, 6, 0.8) 100%);
    }

    /* Alert de doublons en haut */
    .duplicate-warning {
        background: linear-gradient(135deg, rgba(245, 158, 11, 0.15) 0%, rgba(245, 158, 11, 0.05) 100%);
        border: 2px solid rgba(245, 158, 11, 0.4);
        border-radius: var(--border-radius);
        padding: 1.25rem 1.5rem;
        margin-bottom: 1rem;
        animation: slideIn 0.3s ease;
    }

    .duplicate-warning-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 0.75rem;
        flex-wrap: wrap;
        gap: 1rem;
    }

    .duplicate-warning-title {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-weight: 700;
        color: #92400e;
        font-size: 1.1rem;
    }

    .duplicate-warning-content {
        color: #78350f;
        font-size: 0.9rem;
        line-height: 1.5;
        margin-bottom: 1rem;
    }

    .duplicate-warning-actions {
        display: flex;
        gap: 0.75rem;
        flex-wrap: wrap;
    }

    .btn-warning-action {
        background: linear-gradient(135deg, var(--warning) 0%, #d97706 100%);
        color: white;
        border: none;
        padding: 0.5rem 1rem;
        border-radius: 6px;
        font-weight: 600;
        cursor: pointer;
        transition: var(--transition);
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.875rem;
    }

    .btn-warning-action:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(245, 158, 11, 0.3);
    }

    /* Layout en grid optimisé */
    .edit-container {
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 1.5rem;
        align-items: start;
    }

    @media (max-width: 1200px) {
        .edit-container {
            grid-template-columns: 1fr;
            gap: 1rem;
        }
        
        .page-header {
            flex-direction: column;
            align-items: flex-start;
        }
        
        .header-actions {
            width: 100%;
            justify-content: center;
        }
    }

    .main-form {
        background: var(--glass-bg);
        border-radius: var(--border-radius);
        box-shadow: var(--shadow);
        padding: 1.5rem;
    }

    .sidebar-controls {
        background: var(--glass-bg);
        border-radius: var(--border-radius);
        box-shadow: var(--shadow);
        padding: 1.25rem;
        position: sticky;
        top: 1rem;
        height: fit-content;
    }

    .form-section {
        margin-bottom: 1.5rem;
    }

    .form-section:last-child {
        margin-bottom: 0;
    }

    .section-title {
        font-size: 1rem;
        font-weight: 600;
        color: var(--royal-blue);
        margin-bottom: 1rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding-bottom: 0.5rem;
        border-bottom: 2px solid #f1f5f9;
    }

    .form-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1rem;
    }

    .form-grid.single {
        grid-template-columns: 1fr;
    }

    @media (max-width: 768px) {
        .form-grid {
            grid-template-columns: 1fr;
        }
    }

    .form-group {
        margin-bottom: 1rem;
    }

    .form-label {
        font-weight: 600;
        color: #374151;
        margin-bottom: 0.4rem;
        font-size: 0.875rem;
        display: flex;
        align-items: center;
        gap: 0.4rem;
    }

    .form-control {
        border: 1px solid #d1d5db;
        border-radius: 6px;
        padding: 0.6rem;
        transition: var(--transition);
        font-size: 0.875rem;
        width: 100%;
    }

    .form-control:focus {
        border-color: var(--royal-blue);
        box-shadow: 0 0 0 3px rgba(30, 58, 138, 0.1);
        outline: none;
    }

    .phone-validation-indicator {
        position: absolute;
        right: 0.75rem;
        top: 50%;
        transform: translateY(-50%);
        font-size: 0.875rem;
        display: none;
        z-index: 10;
    }

    .phone-validation-indicator.checking {
        display: block;
        color: #6b7280;
        animation: pulse 1.5s infinite;
    }

    .phone-validation-indicator.has-duplicates {
        display: block;
        color: var(--warning);
    }

    .phone-validation-indicator.clean {
        display: block;
        color: var(--success);
    }

    /* Alert de doublons dans le formulaire */
    .duplicate-alert {
        background: linear-gradient(135deg, rgba(245, 158, 11, 0.1) 0%, rgba(245, 158, 11, 0.05) 100%);
        border: 1px solid rgba(245, 158, 11, 0.3);
        border-radius: 6px;
        padding: 0.75rem;
        margin: 0.5rem 0;
        display: none;
        font-size: 0.8rem;
    }

    .duplicate-alert.show {
        display: block;
        animation: slideIn 0.3s ease;
    }

    .duplicate-alert-content {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        color: #92400e;
        font-weight: 500;
        margin-bottom: 0.5rem;
    }

    .duplicate-alert-actions {
        display: flex;
        gap: 0.5rem;
        flex-wrap: wrap;
    }

    .btn-royal {
        background: linear-gradient(135deg, var(--royal-blue) 0%, var(--royal-blue-light) 100%);
        color: white;
        border: none;
        padding: 0.375rem 0.75rem;
        border-radius: 4px;
        font-weight: 500;
        cursor: pointer;
        transition: var(--transition);
        display: inline-flex;
        align-items: center;
        gap: 0.375rem;
        font-size: 0.75rem;
    }

    .btn-royal:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(30, 58, 138, 0.3);
    }

    .btn-success-small {
        background: linear-gradient(135deg, var(--success) 0%, #059669 100%);
        color: white;
        border: none;
        padding: 0.375rem 0.75rem;
        border-radius: 4px;
        font-weight: 500;
        cursor: pointer;
        transition: var(--transition);
        display: inline-flex;
        align-items: center;
        gap: 0.375rem;
        font-size: 0.75rem;
    }

    .btn-outline-small {
        background: white;
        color: var(--royal-blue);
        border: 1px solid var(--royal-blue);
        padding: 0.375rem 0.75rem;
        border-radius: 4px;
        font-weight: 500;
        cursor: pointer;
        transition: var(--transition);
        display: inline-flex;
        align-items: center;
        gap: 0.375rem;
        font-size: 0.75rem;
    }

    /* Champs conditionnels */
    .conditional-field {
        display: none;
        animation: slideIn 0.3s ease;
    }

    .conditional-field.show {
        display: block;
    }

    .conditional-field .form-control {
        border-color: var(--royal-blue);
        background: rgba(30, 58, 138, 0.05);
    }

    /* Gestion des produits */
    .products-section {
        background: #f8fafc;
        border-radius: 6px;
        padding: 1rem;
        margin-bottom: 1rem;
    }

    .product-search {
        position: relative;
        margin-bottom: 1rem;
    }

    .product-suggestions {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        background: white;
        border: 1px solid #e5e7eb;
        border-radius: 6px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        z-index: 1000;
        max-height: 200px;
        overflow-y: auto;
        display: none;
    }

    .suggestion-item {
        padding: 0.75rem;
        cursor: pointer;
        border-bottom: 1px solid #f3f4f6;
        transition: var(--transition);
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .suggestion-item:hover {
        background: rgba(30, 58, 138, 0.05);
    }

    .product-ref {
        font-family: 'JetBrains Mono', monospace;
        font-size: 0.7rem;
        color: var(--royal-blue);
        background: rgba(30, 58, 138, 0.1);
        padding: 0.2rem 0.4rem;
        border-radius: 3px;
        margin-left: 0.5rem;
    }

    .product-list {
        max-height: 250px;
        overflow-y: auto;
    }

    .product-item {
        background: white;
        border: 1px solid #e5e7eb;
        border-radius: 6px;
        padding: 0.75rem;
        margin-bottom: 0.5rem;
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .product-info {
        flex: 1;
    }

    .product-name {
        font-weight: 600;
        font-size: 0.875rem;
        color: #374151;
    }

    .product-price {
        font-size: 0.75rem;
        color: #6b7280;
        font-family: 'JetBrains Mono', monospace;
    }

    .quantity-control {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .quantity-btn {
        width: 28px;
        height: 28px;
        border: 1px solid #d1d5db;
        background: white;
        border-radius: 4px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: var(--transition);
        font-size: 0.75rem;
    }

    .quantity-btn:hover {
        border-color: var(--royal-blue);
        background: rgba(30, 58, 138, 0.05);
    }

    .quantity-input {
        width: 60px;
        text-align: center;
        border: 1px solid #d1d5db;
        border-radius: 4px;
        padding: 0.25rem;
        font-size: 0.875rem;
    }

    .remove-btn {
        background: #fef2f2;
        color: #dc2626;
        border: 1px solid #fecaca;
        padding: 0.25rem 0.5rem;
        border-radius: 4px;
        cursor: pointer;
        transition: var(--transition);
        font-size: 0.75rem;
    }

    .remove-btn:hover {
        background: #fee2e2;
    }

    /* Contrôles sidebar */
    .control-section {
        margin-bottom: 1.5rem;
    }

    .control-section:last-child {
        margin-bottom: 0;
    }

    .control-title {
        font-weight: 600;
        color: var(--royal-blue);
        margin-bottom: 0.75rem;
        font-size: 0.9rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .status-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 0.5rem;
        margin-bottom: 1rem;
    }

    .status-badge {
        padding: 0.5rem;
        border-radius: 6px;
        border: 2px solid transparent;
        cursor: pointer;
        transition: var(--transition);
        text-align: center;
        font-size: 0.75rem;
        font-weight: 600;
        position: relative;
    }

    .status-badge.active {
        transform: scale(1.02);
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
    }

    .status-nouvelle { background: #f3f4f6; color: #6b7280; }
    .status-nouvelle.active { background: var(--royal-blue); color: white; }

    .status-confirmée { background: #ecfdf5; color: #059669; }
    .status-confirmée.active { background: var(--success); color: white; }

    .status-annulée { background: #fef2f2; color: #dc2626; }
    .status-annulée.active { background: var(--danger); color: white; }

    .status-datée { background: #fef3c7; color: #d97706; }
    .status-datée.active { background: var(--warning); color: white; }

    .priority-grid {
        display: grid;
        grid-template-columns: 1fr 1fr 1fr;
        gap: 0.5rem;
        margin-bottom: 1rem;
    }

    .priority-badge {
        padding: 0.4rem;
        border-radius: 6px;
        border: 2px solid transparent;
        cursor: pointer;
        transition: var(--transition);
        text-align: center;
        font-size: 0.7rem;
        font-weight: 600;
    }

    .priority-badge.active {
        transform: scale(1.02);
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
    }

    .priority-normale { background: #f3f4f6; color: #6b7280; }
    .priority-normale.active { background: #6b7280; color: white; }

    .priority-urgente { background: #fef3c7; color: #d97706; }
    .priority-urgente.active { background: var(--warning); color: white; }

    .priority-vip { background: #fef2f2; color: #dc2626; }
    .priority-vip.active { background: var(--danger); color: white; }

    .summary-box {
        background: #f8fafc;
        border-radius: 6px;
        padding: 1rem;
        margin-bottom: 1rem;
    }

    .summary-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 0.5rem;
        font-size: 0.875rem;
    }

    .summary-row:last-child {
        margin-bottom: 0;
        font-weight: 700;
        padding-top: 0.5rem;
        border-top: 1px solid #e5e7eb;
        color: var(--royal-blue);
    }

    .action-buttons {
        display: flex;
        gap: 0.75rem;
    }

    .btn-save {
        flex: 1;
        background: linear-gradient(135deg, var(--success) 0%, #059669 100%);
        color: white;
        border: none;
        padding: 0.75rem;
        border-radius: 6px;
        font-weight: 600;
        cursor: pointer;
        transition: var(--transition);
        font-size: 0.875rem;
    }

    .btn-save:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
    }

    .btn-save:disabled {
        opacity: 0.6;
        cursor: not-allowed;
        transform: none;
        box-shadow: none;
    }

    .btn-cancel {
        background: #f3f4f6;
        color: #6b7280;
        border: none;
        padding: 0.75rem 1rem;
        border-radius: 6px;
        font-weight: 600;
        cursor: pointer;
        transition: var(--transition);
        text-decoration: none;
        font-size: 0.875rem;
    }

    .btn-cancel:hover {
        background: #e5e7eb;
        color: #374151;
        text-decoration: none;
    }

    .stock-warning {
        background: #fef3c7;
        border: 1px solid #f59e0b;
        border-radius: 6px;
        padding: 0.75rem;
        margin: 0.5rem 0;
        font-size: 0.8rem;
        color: #92400e;
        display: none;
    }

    .stock-warning.show {
        display: block;
    }

    /* Styles pour les modals */
    .modal-royal .modal-content {
        border: none;
        border-radius: var(--border-radius);
        box-shadow: var(--shadow);
    }

    .modal-royal .modal-header {
        background: linear-gradient(135deg, var(--royal-blue) 0%, var(--royal-blue-dark) 100%);
        color: white;
        border: none;
        border-radius: var(--border-radius) var(--border-radius) 0 0;
    }

    .modal-royal .modal-title {
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .modal-royal .btn-close {
        background: rgba(255, 255, 255, 0.2);
        border-radius: 50%;
        opacity: 1;
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .history-timeline {
        max-height: 400px;
        overflow-y: auto;
    }

    .history-item {
        background: #f8fafc;
        border: 1px solid #e5e7eb;
        border-radius: 6px;
        padding: 1rem;
        margin-bottom: 0.75rem;
        transition: var(--transition);
    }

    .history-item:hover {
        background: white;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }

    .history-header {
        display: flex;
        justify-content: space-between;
        align-items: start;
        margin-bottom: 0.5rem;
    }

    .history-action {
        font-weight: 600;
        color: var(--royal-blue);
        font-size: 0.9rem;
    }

    .history-date {
        color: #6b7280;
        font-size: 0.75rem;
    }

    .history-notes {
        color: #374151;
        font-size: 0.875rem;
        line-height: 1.5;
    }

    .duplicates-list {
        max-height: 350px;
        overflow-y: auto;
    }

    .duplicate-item {
        background: #f8fafc;
        border: 1px solid #e5e7eb;
        border-radius: 6px;
        padding: 1rem;
        margin-bottom: 0.75rem;
        transition: var(--transition);
    }

    .duplicate-item:hover {
        background: white;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }

    .duplicate-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 0.5rem;
    }

    .duplicate-id {
        font-weight: 600;
        color: var(--royal-blue);
    }

    .status-badge-mini {
        padding: 0.25rem 0.5rem;
        border-radius: 12px;
        font-size: 0.7rem;
        font-weight: 600;
        text-transform: uppercase;
    }

    .status-nouvelle-mini { background: #e5e7eb; color: #374151; }
    .status-confirmée-mini { background: #d1fae5; color: #065f46; }
    .status-annulée-mini { background: #fee2e2; color: #991b1b; }
    .status-datée-mini { background: #fef3c7; color: #92400e; }

    @keyframes slideIn {
        from { opacity: 0; transform: translateY(-10px); }
        to { opacity: 1; transform: translateY(0); }
    }

    @keyframes pulse {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.5; }
    }
</style>
@endsection

@section('content')
<div class="page-container">
    <div class="page-header">
        <div class="page-header-left">
            <h1>
                <i class="fas fa-edit"></i>
                Commande #{{ str_pad($order->id, 6, '0', STR_PAD_LEFT) }}
                <span class="order-status-badge">{{ ucfirst($order->status) }}</span>
            </h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Accueil</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.orders.index') }}">Commandes</a></li>
                    <li class="breadcrumb-item active">Modifier #{{ $order->id }}</li>
                </ol>
            </nav>
        </div>

        <div class="header-actions">
            <button type="button" class="header-btn btn-call" onclick="showCallAttemptModal()">
                <i class="fas fa-phone"></i>
                Tentative d'Appel
            </button>
            
            <button type="button" class="header-btn btn-history" onclick="showOrderHistoryModal()">
                <i class="fas fa-history"></i>
                Historique
            </button>
            
            @if($order->is_duplicate)
                <button type="button" class="header-btn btn-duplicates" onclick="showDuplicatesModal()">
                    <i class="fas fa-copy"></i>
                    Voir Doublons
                </button>
            @endif
        </div>
    </div>

    <!-- Alert de doublons en haut si applicable -->
    @if($order->is_duplicate)
        <div class="duplicate-warning">
            <div class="duplicate-warning-header">
                <div class="duplicate-warning-title">
                    <i class="fas fa-exclamation-triangle"></i>
                    Cette commande fait partie d'un groupe de doublons
                </div>
            </div>
            <div class="duplicate-warning-content">
                <strong>Attention :</strong> Cette commande partage le même numéro de téléphone avec d'autres commandes dans votre système.
                Il est recommandé de vérifier s'il s'agit du même client ou de commandes distinctes avant toute modification.
            </div>
            <div class="duplicate-warning-actions">
                <button type="button" class="btn-warning-action" onclick="showDuplicatesModal()">
                    <i class="fas fa-eye"></i> Voir tous les doublons
                </button>
                <button type="button" class="btn-warning-action" onclick="window.open('/admin/duplicates/detail/{{ urlencode($order->customer_phone) }}', '_blank')">
                    <i class="fas fa-external-link-alt"></i> Gérer les doublons
                </button>
            </div>
        </div>
    @endif

    <form id="orderForm" action="{{ route('admin.orders.update', $order) }}" method="POST">
        @csrf
        @method('PUT')
        
        <div class="edit-container">
            <!-- Formulaire principal -->
            <div class="main-form">
                <!-- Informations client -->
                <div class="form-section">
                    <h3 class="section-title">
                        <i class="fas fa-user"></i>
                        Informations Client
                    </h3>
                    
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="customer_name" class="form-label">
                                <i class="fas fa-user"></i> Nom complet
                            </label>
                            <input type="text" class="form-control @error('customer_name') is-invalid @enderror" 
                                   id="customer_name" name="customer_name" 
                                   value="{{ old('customer_name', $order->customer_name) }}"
                                   placeholder="Nom et prénom">
                            @error('customer_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="form-group">
                            <label for="customer_phone" class="form-label">
                                <i class="fas fa-phone"></i> Téléphone principal <span class="text-danger">*</span>
                            </label>
                            <div style="position: relative;">
                                <input type="tel" class="form-control @error('customer_phone') is-invalid @enderror" 
                                       id="customer_phone" name="customer_phone" 
                                       value="{{ old('customer_phone', $order->customer_phone) }}"
                                       placeholder="+216 XX XXX XXX" required>
                                <div class="phone-validation-indicator" id="phone-indicator">
                                    <i class="fas fa-spinner fa-spin"></i>
                                </div>
                            </div>
                            @error('customer_phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            
                            <!-- Alert doublons -->
                            <div class="duplicate-alert" id="duplicate-alert">
                                <div class="duplicate-alert-content">
                                    <i class="fas fa-exclamation-triangle"></i>
                                    <span id="duplicate-message"></span>
                                </div>
                                <div class="duplicate-alert-actions">
                                    <button type="button" class="btn-royal" id="view-history-btn">
                                        <i class="fas fa-history"></i> Voir l'historique
                                    </button>
                                    <button type="button" class="btn-success-small" id="fill-data-btn">
                                        <i class="fas fa-fill"></i> Pré-remplir
                                    </button>
                                    <button type="button" class="btn-outline-small" onclick="dismissDuplicateAlert()">
                                        <i class="fas fa-times"></i> Ignorer
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-grid">
                        <div class="form-group">
                            <label for="customer_phone_2" class="form-label">
                                <i class="fas fa-phone-alt"></i> Téléphone secondaire
                            </label>
                            <input type="tel" class="form-control @error('customer_phone_2') is-invalid @enderror" 
                                   id="customer_phone_2" name="customer_phone_2" 
                                   value="{{ old('customer_phone_2', $order->customer_phone_2) }}"
                                   placeholder="Numéro alternatif">
                            @error('customer_phone_2') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="form-group">
                            <label for="customer_governorate" class="form-label">
                                <i class="fas fa-map-marked-alt"></i> Gouvernorat
                            </label>
                            <select class="form-control @error('customer_governorate') is-invalid @enderror" 
                                    id="customer_governorate" name="customer_governorate">
                                <option value="">Choisir un gouvernorat</option>
                                @if (isset($regions))
                                    @foreach ($regions as $region)
                                        <option value="{{ $region->id }}" 
                                                {{ old('customer_governorate', $order->customer_governorate) == $region->id ? 'selected' : '' }}>
                                            {{ $region->name }}
                                        </option>
                                    @endforeach
                                @endif
                            </select>
                            @error('customer_governorate') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="form-grid single">
                        <div class="form-group">
                            <label for="customer_address" class="form-label">
                                <i class="fas fa-map-marker-alt"></i> Adresse complète
                            </label>
                            <textarea class="form-control @error('customer_address') is-invalid @enderror" 
                                      id="customer_address" name="customer_address" rows="2"
                                      placeholder="Adresse détaillée">{{ old('customer_address', $order->customer_address) }}</textarea>
                            @error('customer_address') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <!-- Champs conditionnels selon le statut -->
                    <div class="form-grid single">
                        <div class="form-group conditional-field" id="scheduled-date-field" 
                             {{ $order->status === 'datée' ? 'style=display:block' : '' }}>
                            <label for="scheduled_date" class="form-label">
                                <i class="fas fa-calendar-alt"></i> Date de livraison prévue <span class="text-danger">*</span>
                            </label>
                            <input type="date" class="form-control @error('scheduled_date') is-invalid @enderror"
                                   id="scheduled_date" name="scheduled_date"
                                   value="{{ old('scheduled_date', $order->scheduled_date ? $order->scheduled_date->format('Y-m-d') : '') }}">
                            @error('scheduled_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="form-grid single">
                        <div class="form-group conditional-field" id="total-price-field" 
                             {{ $order->status === 'confirmée' ? 'style=display:block' : '' }}>
                            <label for="total_price" class="form-label">
                                <i class="fas fa-dollar-sign"></i> Prix total de la commande
                            </label>
                            <input type="number" class="form-control @error('total_price') is-invalid @enderror"
                                   id="total_price" name="total_price" step="0.001" min="0"
                                   value="{{ old('total_price', $order->total_price) }}"
                                   placeholder="Laisser vide pour calcul automatique">
                            @error('total_price') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="form-grid single">
                        <div class="form-group">
                            <label for="notes" class="form-label">
                                <i class="fas fa-sticky-note"></i> Commentaires
                            </label>
                            <textarea class="form-control @error('notes') is-invalid @enderror" 
                                      id="notes" name="notes" rows="4"
                                      placeholder="Notes supplémentaires">{{ old('notes', $order->notes) }}</textarea>
                            @error('notes') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </div>

                <!-- Gestion des produits -->
                <div class="form-section">
                    <h3 class="section-title">
                        <i class="fas fa-box"></i>
                        Produits (<span id="product-count">{{ $order->items->count() }}</span>)
                    </h3>
                    
                    <div class="products-section">
                        <div class="product-search">
                            <input type="text" class="form-control" id="product-search" 
                                   placeholder="Rechercher par nom ou référence...">
                            <div class="product-suggestions" id="product-suggestions"></div>
                        </div>

                        <div class="stock-warning" id="stock-warning">
                            <i class="fas fa-exclamation-triangle"></i>
                            <strong>Attention :</strong> Stock insuffisant pour certains produits.
                        </div>

                        <div class="product-list" id="product-list">
                            <!-- Les produits seront chargés ici -->
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar de contrôle -->
            <div class="sidebar-controls">
                <!-- Tentatives d'appel -->
                <div class="control-section">
                    <h4 class="control-title">
                        <i class="fas fa-phone"></i>
                        Tentatives d'appel
                    </h4>
                    <div class="summary-box">
                        <div class="summary-row">
                            <span>Total tentatives:</span>
                            <span id="total-attempts">{{ $order->attempts_count ?? 0 }}</span>
                        </div>
                        <div class="summary-row">
                            <span>Aujourd'hui:</span>
                            <span id="daily-attempts">{{ $order->daily_attempts_count ?? 0 }}</span>
                        </div>
                        @if($order->last_attempt_at)
                            <div class="summary-row">
                                <span>Dernière:</span>
                                <span id="last-attempt">{{ $order->last_attempt_at->format('d/m H:i') }}</span>
                            </div>
                        @else
                            <div class="summary-row">
                                <span>Dernière:</span>
                                <span id="last-attempt">Aucune</span>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Statut -->
                <div class="control-section">
                    <h4 class="control-title">
                        <i class="fas fa-flag"></i>
                        Statut de la commande
                    </h4>
                    <div class="status-grid">
                        <div class="status-badge status-nouvelle {{ $order->status === 'nouvelle' ? 'active' : '' }}" 
                             data-status="nouvelle">
                            Nouvelle
                        </div>
                        <div class="status-badge status-confirmée {{ $order->status === 'confirmée' ? 'active' : '' }}" 
                             data-status="confirmée">
                            Confirmée
                        </div>
                        <div class="status-badge status-annulée {{ $order->status === 'annulée' ? 'active' : '' }}" 
                             data-status="annulée">
                            Annulée
                        </div>
                        <div class="status-badge status-datée {{ $order->status === 'datée' ? 'active' : '' }}" 
                             data-status="datée">
                            Datée
                        </div>
                    </div>
                    <input type="hidden" name="status" id="status" value="{{ $order->status }}">
                </div>

                <!-- Priorité -->
                <div class="control-section">
                    <h4 class="control-title">
                        <i class="fas fa-star"></i>
                        Priorité
                    </h4>
                    <div class="priority-grid">
                        <div class="priority-badge priority-normale {{ $order->priority === 'normale' ? 'active' : '' }}" 
                             data-priority="normale">
                            Normale
                        </div>
                        <div class="priority-badge priority-urgente {{ $order->priority === 'urgente' ? 'active' : '' }}" 
                             data-priority="urgente">
                            Urgente
                        </div>
                        <div class="priority-badge priority-vip {{ $order->priority === 'vip' ? 'active' : '' }}" 
                             data-priority="vip">
                            VIP
                        </div>
                    </div>
                    <input type="hidden" name="priority" id="priority" value="{{ $order->priority }}">
                </div>

                <!-- Assignation -->
                <div class="control-section">
                    <h4 class="control-title">
                        <i class="fas fa-user-tie"></i>
                        Assignation
                    </h4>
                    <select class="form-control" id="employee_id" name="employee_id">
                        <option value="">Non assigné</option>
                        @if (isset($employees))
                            @foreach ($employees as $employee)
                                <option value="{{ $employee->id }}" 
                                        {{ $order->employee_id == $employee->id ? 'selected' : '' }}>
                                    {{ $employee->name }}
                                </option>
                            @endforeach
                        @endif
                    </select>
                </div>

                <!-- Résumé -->
                <div class="control-section">
                    <h4 class="control-title">
                        <i class="fas fa-calculator"></i>
                        Résumé
                    </h4>
                    <div class="summary-box">
                        <div class="summary-row">
                            <span>Sous-total:</span>
                            <span id="subtotal">{{ number_format($order->items->sum('total_price'), 3) }} TND</span>
                        </div>
                        <div class="summary-row">
                            <span>Total:</span>
                            <span id="total">{{ number_format($order->total_price, 3) }} TND</span>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="action-buttons">
                    <a href="{{ route('admin.orders.index') }}" class="btn-cancel">
                        <i class="fas fa-arrow-left"></i> Retour
                    </a>
                    <button type="submit" class="btn-save" id="save-btn">
                        <i class="fas fa-save"></i> Enregistrer
                    </button>
                </div>
            </div>
        </div>

        <!-- Données cachées pour les produits -->
        <div id="product-data" style="display: none;"></div>
    </form>
</div>

<!-- Modal Tentative d'Appel -->
<div class="modal fade modal-royal" id="callAttemptModal" tabindex="-1" aria-labelledby="callAttemptModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="callAttemptModalLabel">
                    <i class="fas fa-phone"></i>
                    Nouvelle Tentative d'Appel
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="callAttemptForm">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="attempt_notes" class="form-label">
                            <i class="fas fa-sticky-note"></i>
                            Notes sur la tentative d'appel <span class="text-danger">*</span>
                        </label>
                        <textarea class="form-control" id="attempt_notes" name="notes"
                                  placeholder="Décrivez le résultat de votre appel (répondu, occupé, boîte vocale, etc.)" 
                                  required rows="4"></textarea>
                    </div>
                    <div class="alert alert-info mt-3">
                        <i class="fas fa-info-circle"></i>
                        <strong>Information :</strong> Cette action incrémentera automatiquement le compteur de
                        tentatives et sera enregistrée dans l'historique de la commande.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times"></i> Annuler
                    </button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save"></i> Enregistrer la Tentative
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Historique -->
<div class="modal fade modal-royal" id="historyModal" tabindex="-1" aria-labelledby="historyModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="historyModalLabel">
                    <i class="fas fa-history"></i>
                    Historique de la Commande #{{ str_pad($order->id, 6, '0', STR_PAD_LEFT) }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="history-timeline" id="history-timeline">
                    <div class="text-center py-4">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Chargement...</span>
                        </div>
                        <p class="mt-3 text-muted">Chargement de l'historique...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Doublons -->
<div class="modal fade modal-royal" id="duplicatesModal" tabindex="-1" aria-labelledby="duplicatesModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="duplicatesModalLabel">
                    <i class="fas fa-copy"></i>
                    Commandes en Doublon pour {{ $order->customer_phone }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <strong>Client :</strong> {{ $order->customer_name ?? 'Non spécifié' }} - {{ $order->customer_phone }}
                </div>
                <div class="duplicates-list" id="duplicates-list">
                    <div class="text-center py-4">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Chargement...</span>
                        </div>
                        <p class="mt-3 text-muted">Chargement des doublons...</p>
                    </div>
                </div>
                <div class="text-center mt-3">
                    <button class="btn btn-warning" onclick="window.open('/admin/duplicates/detail/{{ urlencode($order->customer_phone) }}', '_blank')">
                        <i class="fas fa-external-link-alt me-2"></i>Gérer tous les doublons
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Historique Client -->
<div class="modal fade modal-royal" id="clientHistoryModal" tabindex="-1" aria-labelledby="clientHistoryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="clientHistoryModalLabel">
                    <i class="fas fa-history me-2"></i>Historique du Client
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="client-history-content">
                    <div class="text-center py-4">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Chargement...</span>
                        </div>
                        <p class="mt-3 text-muted">Chargement de l'historique...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    let originalStatus = '{{ $order->status }}';
    let products = [];
    let phoneCheckTimeout;
    let latestClientData = null;

    // Initialiser les produits depuis la base
    @if ($order->items->count() > 0)
        products = [
            @foreach ($order->items as $item)
                {
                    id: {{ $item->product_id }},
                    name: "{{ addslashes($item->product->name ?? 'Produit supprimé') }}",
                    reference: "{{ $item->product->reference ?? 'N/A' }}",
                    price: {{ (float) $item->unit_price }},
                    quantity: {{ $item->quantity }},
                    stock: {{ $item->product->stock ?? 0 }}
                }{{ !$loop->last ? ',' : '' }}
            @endforeach
        ];
    @endif

    updateProductList();
    updateSummary();

    // =========================
    // GESTION DES BOUTONS D'ACTION
    // =========================
    
    window.showCallAttemptModal = function() {
        $('#callAttemptModal').modal('show');
    };

    window.showOrderHistoryModal = function() {
        $('#historyModal').modal('show');
        loadOrderHistory();
    };

    window.showDuplicatesModal = function() {
        $('#duplicatesModal').modal('show');
        loadDuplicates();
    };

    // =========================
    // GESTION DES TENTATIVES D'APPEL
    // =========================
    $('#callAttemptForm').on('submit', function(e) {
        e.preventDefault();
        
        const notes = $('#attempt_notes').val().trim();
        if (!notes) {
            alert('Veuillez saisir des notes sur la tentative d\'appel.');
            return;
        }

        const submitBtn = $(this).find('button[type="submit"]');
        const originalText = submitBtn.html();
        submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Enregistrement...');

        $.post('/admin/orders/{{ $order->id }}/record-attempt', {
            notes: notes,
            _token: $('meta[name="csrf-token"]').attr('content')
        })
        .done(function(response) {
            $('#callAttemptModal').modal('hide');
            $('#attempt_notes').val('');
            
            showNotification('success', 'Tentative d\'appel enregistrée avec succès !');
            
            // Mettre à jour les compteurs dans la sidebar
            updateAttemptCounters(response);
        })
        .fail(function(xhr) {
            const errorMessage = xhr.responseJSON?.message || 'Erreur lors de l\'enregistrement';
            showNotification('error', errorMessage);
        })
        .always(function() {
            submitBtn.prop('disabled', false).html(originalText);
        });
    });

    function updateAttemptCounters(response) {
        $('#total-attempts').text(response.attempts_count || 0);
        $('#daily-attempts').text(response.daily_attempts_count || 0);
        
        if (response.last_attempt_at) {
            const date = new Date(response.last_attempt_at);
            const formatted = date.toLocaleDateString('fr-FR', {
                day: '2-digit',
                month: '2-digit',
                hour: '2-digit',
                minute: '2-digit'
            });
            $('#last-attempt').text(formatted);
        }
    }

    // =========================
    // GESTION DES MODALS
    // =========================
    function loadOrderHistory() {
        $('#history-timeline').html(`
            <div class="text-center py-4">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Chargement...</span>
                </div>
                <p class="mt-3 text-muted">Chargement de l'historique...</p>
            </div>
        `);

        $.get('/admin/orders/{{ $order->id }}/history-modal')
            .done(function(response) {
                $('#history-timeline').html(response);
            })
            .fail(function() {
                $('#history-timeline').html(`
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle"></i>
                        Erreur lors du chargement de l'historique
                    </div>
                `);
            });
    }

    function loadDuplicates() {
        $('#duplicates-list').html(`
            <div class="text-center py-4">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Chargement...</span>
                </div>
                <p class="mt-3 text-muted">Chargement des doublons...</p>
            </div>
        `);

        $.get('/admin/orders/client-history', { phone: '{{ $order->customer_phone }}' })
            .done(function(response) {
                let content = '';
                
                if (response.orders && response.orders.length > 0) {
                    response.orders.forEach(function(order) {
                        const statusClass = 'status-' + order.status.replace('ée', 'e').replace('ée', 'e') + '-mini';
                        const isCurrent = order.id == {{ $order->id }};
                        
                        content += `
                            <div class="duplicate-item ${isCurrent ? 'border-primary' : ''}">
                                <div class="duplicate-header">
                                    <div class="duplicate-id">
                                        Commande #${order.id} ${isCurrent ? '(Actuelle)' : ''}
                                    </div>
                                    <span class="status-badge-mini ${statusClass}">${order.status}</span>
                                </div>
                                <div class="small text-muted">
                                    <strong>Date :</strong> ${new Date(order.created_at).toLocaleDateString('fr-FR')} |
                                    <strong>Montant :</strong> ${parseFloat(order.total_price).toFixed(3)} TND |
                                    <strong>Produits :</strong> ${order.items ? order.items.length : 0}
                                    ${order.customer_name ? '<br><strong>Client :</strong> ' + order.customer_name : ''}
                                </div>
                            </div>
                        `;
                    });
                } else {
                    content = `
                        <div class="text-center py-4">
                            <i class="fas fa-search fa-2x text-muted mb-3"></i>
                            <h6>Aucun doublon trouvé</h6>
                            <p class="text-muted">Cette commande n'a pas de doublons détectés.</p>
                        </div>
                    `;
                }
                
                $('#duplicates-list').html(content);
            })
            .fail(function() {
                $('#duplicates-list').html(`
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle"></i>
                        Erreur lors du chargement des doublons
                    </div>
                `);
            });
    }

    function showNotification(type, message) {
        const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
        const icon = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle';
        
        const notification = $(`
            <div class="alert ${alertClass} alert-dismissible fade show position-fixed" 
                 style="top: 20px; right: 20px; z-index: 9999; min-width: 300px;">
                <i class="fas ${icon} me-2"></i>
                <strong>${message}</strong>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `);

        $('body').append(notification);
        
        setTimeout(() => {
            notification.alert('close');
        }, 5000);
    }

    // =========================
    // VÉRIFICATION DES DOUBLONS EN TEMPS RÉEL
    // =========================
    $('#customer_phone').on('input', function() {
        const phone = $(this).val().trim();
        clearTimeout(phoneCheckTimeout);
        
        if (phone.length >= 8) {
            phoneCheckTimeout = setTimeout(() => {
                checkPhoneForDuplicates(phone);
            }, 500);
        } else {
            resetPhoneValidation();
        }
    });

    function checkPhoneForDuplicates(phone) {
        $('#phone-indicator').removeClass('has-duplicates clean').addClass('checking').show();
        
        $.get('/admin/orders/check-phone-duplicates', { phone: phone })
            .done(function(response) {
                $('#phone-indicator').removeClass('checking');
                
                if (response.has_duplicates && response.total_orders > 1) {
                    $('#phone-indicator').addClass('has-duplicates').html('<i class="fas fa-exclamation-triangle"></i>');
                    $('#customer_phone').addClass('has-duplicates');
                    showDuplicateAlert(response);
                } else {
                    $('#phone-indicator').addClass('clean').html('<i class="fas fa-check"></i>');
                    $('#customer_phone').removeClass('has-duplicates');
                    hideDuplicateAlert();
                }
            })
            .fail(function() {
                resetPhoneValidation();
            });
    }

    function resetPhoneValidation() {
        $('#phone-indicator').removeClass('checking has-duplicates clean').hide();
        $('#customer_phone').removeClass('has-duplicates');
        hideDuplicateAlert();
    }

    function showDuplicateAlert(response) {
        $('#duplicate-message').text(`Ce numéro possède ${response.total_orders} commande(s). Vérifiez les doublons.`);
        $('#duplicate-alert').addClass('show');
    }

    function hideDuplicateAlert() {
        $('#duplicate-alert').removeClass('show');
    }

    window.dismissDuplicateAlert = function() {
        hideDuplicateAlert();
    };

    // Bouton voir historique client
    $('#view-history-btn').on('click', function() {
        const phone = $('#customer_phone').val().trim();
        if (phone) {
            loadClientHistory(phone);
            $('#clientHistoryModal').modal('show');
        }
    });

    // Bouton pré-remplir données
    $('#fill-data-btn').on('click', function() {
        if (latestClientData) {
            fillClientData(latestClientData);
        }
    });

    function loadClientHistory(phone) {
        $('#client-history-content').html(`
            <div class="text-center py-4">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Chargement...</span>
                </div>
                <p class="mt-3 text-muted">Chargement de l'historique...</p>
            </div>
        `);

        $.get('/admin/orders/client-history', { phone: phone })
            .done(function(response) {
                latestClientData = response.latest_order;
                
                let content = '';
                if (response.orders && response.orders.length > 0) {
                    content = `<div class="mb-3"><strong>Nombre total de commandes :</strong> ${response.orders.length}</div>`;
                    
                    response.orders.forEach(function(order) {
                        const statusClass = 'status-' + order.status.replace('ée', 'e') + '-mini';
                        
                        content += `
                            <div class="duplicate-item">
                                <div class="duplicate-header">
                                    <div class="duplicate-id">Commande #${order.id}</div>
                                    <span class="status-badge-mini ${statusClass}">${order.status}</span>
                                </div>
                                <div class="small text-muted">
                                    <strong>Date :</strong> ${new Date(order.created_at).toLocaleDateString('fr-FR')} |
                                    <strong>Montant :</strong> ${parseFloat(order.total_price).toFixed(3)} TND |
                                    <strong>Produits :</strong> ${order.items ? order.items.length : 0}
                                    ${order.customer_name ? '<br><strong>Client :</strong> ' + order.customer_name : ''}
                                </div>
                            </div>
                        `;
                    });
                } else {
                    content = `
                        <div class="text-center py-4">
                            <i class="fas fa-inbox fa-2x text-muted mb-3"></i>
                            <h6>Aucun historique trouvé</h6>
                            <p class="text-muted">Ce numéro n'a pas d'historique de commandes.</p>
                        </div>
                    `;
                }
                
                $('#client-history-content').html(content);
            })
            .fail(function() {
                $('#client-history-content').html(`
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Erreur lors du chargement de l'historique
                    </div>
                `);
            });
    }

    function fillClientData(orderData) {
        if (!orderData) return;
        
        $('#customer_name').val(orderData.customer_name || '');
        $('#customer_phone_2').val(orderData.customer_phone_2 || '');
        $('#customer_address').val(orderData.customer_address || '');
        
        if (orderData.customer_governorate) {
            $('#customer_governorate').val(orderData.customer_governorate);
        }
        
        showNotification('success', 'Données pré-remplies avec succès !');
    }

    // =========================
    // GESTION DES PRODUITS
    // =========================
    $('#product-search').on('input', function() {
        const query = $(this).val().trim();
        if (query.length >= 2) {
            searchProducts(query);
        } else {
            $('#product-suggestions').hide();
        }
    });

    function searchProducts(query) {
        $.get('/admin/orders/search-products', { search: query })
            .done(function(data) {
                showProductSuggestions(data);
            });
    }

    function showProductSuggestions(productList) {
        const suggestions = $('#product-suggestions').empty();
        
        if (productList.length === 0) {
            suggestions.html('<div class="suggestion-item">Aucun produit trouvé</div>');
        } else {
            productList.forEach(product => {
                const item = $(`
                    <div class="suggestion-item" data-product-id="${product.id}">
                        <div>
                            <strong>${product.name}</strong>
                            <span class="product-ref">Réf: ${product.reference || 'N/A'}</span>
                            <br><small>Stock: ${product.stock}</small>
                        </div>
                        <div class="text-success">${parseFloat(product.price).toFixed(3)} TND</div>
                    </div>
                `);
                
                item.on('click', function() {
                    addProduct(product);
                    $('#product-search').val('');
                    suggestions.hide();
                });
                
                suggestions.append(item);
            });
        }
        
        suggestions.show();
    }

    function addProduct(product) {
        const existingProduct = products.find(p => p.id === product.id);
        
        if (existingProduct) {
            existingProduct.quantity += 1;
        } else {
            products.push({
                id: product.id,
                name: product.name,
                reference: product.reference,
                price: parseFloat(product.price),
                quantity: 1,
                stock: product.stock
            });
        }
        
        updateProductList();
        updateSummary();
        checkStock();
    }

    function removeProduct(productId) {
        products = products.filter(p => p.id !== productId);
        updateProductList();
        updateSummary();
        checkStock();
    }

    function updateQuantity(productId, newQuantity) {
        const product = products.find(p => p.id === productId);
        if (product) {
            product.quantity = Math.max(1, Math.min(newQuantity, product.stock));
            updateProductList();
            updateSummary();
            checkStock();
        }
    }

    function updateProductList() {
        const container = $('#product-list');
        container.empty();
        
        if (products.length === 0) {
            container.html('<div class="text-center text-muted py-3">Aucun produit ajouté</div>');
            return;
        }

        products.forEach(product => {
            const item = $(`
                <div class="product-item">
                    <div class="product-info">
                        <div class="product-name">${product.name}</div>
                        <div class="product-price">Réf: ${product.reference} • ${product.price.toFixed(3)} TND</div>
                    </div>
                    <div class="quantity-control">
                        <button type="button" class="quantity-btn minus" data-product-id="${product.id}">
                            <i class="fas fa-minus"></i>
                        </button>
                        <input type="number" class="quantity-input" value="${product.quantity}" 
                               min="1" max="${product.stock}" data-product-id="${product.id}">
                        <button type="button" class="quantity-btn plus" data-product-id="${product.id}">
                            <i class="fas fa-plus"></i>
                        </button>
                    </div>
                    <button type="button" class="remove-btn" data-product-id="${product.id}">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            `);
            
            container.append(item);
        });

        $('#product-count').text(products.length);
        updateFormData();
    }

    function updateSummary() {
        const subtotal = products.reduce((sum, product) => sum + (product.price * product.quantity), 0);
        $('#subtotal').text(subtotal.toFixed(3) + ' TND');
        $('#total').text(subtotal.toFixed(3) + ' TND');
    }

    function updateFormData() {
        const container = $('#product-data');
        container.empty();
        
        products.forEach((product, index) => {
            container.append(`
                <input type="hidden" name="products[${index}][id]" value="${product.id}">
                <input type="hidden" name="products[${index}][quantity]" value="${product.quantity}">
            `);
        });
    }

    function checkStock() {
        const currentStatus = $('#status').val();
        let hasStockIssues = false;
        let stockMessages = [];

        if (currentStatus === 'confirmée') {
            products.forEach(product => {
                if (product.stock < product.quantity) {
                    hasStockIssues = true;
                    stockMessages.push(`<strong>${product.name}</strong>: stock insuffisant (${product.stock} disponible, ${product.quantity} demandée)`);
                }
            });
        }

        if (hasStockIssues) {
            $('#stock-warning').html(`
                <i class="fas fa-exclamation-triangle"></i>
                <strong>Attention :</strong> Stock insuffisant pour certains produits :<br>
                ${stockMessages.join('<br>')}
            `).addClass('show');
        } else {
            $('#stock-warning').removeClass('show');
        }
    }

    // =========================
    // GESTION DES CHAMPS CONDITIONNELS
    // =========================
    function handleConditionalFields(status) {
        // Gestion du champ date programmée
        if (status === 'datée') {
            $('#scheduled-date-field').addClass('show');
            $('#scheduled_date').prop('required', true);
        } else {
            $('#scheduled-date-field').removeClass('show');
            $('#scheduled_date').prop('required', false);
        }

        // Gestion du champ prix total
        if (status === 'confirmée') {
            $('#total-price-field').addClass('show');
        } else {
            $('#total-price-field').removeClass('show');
        }
    }

    // =========================
    // EVENT HANDLERS
    // =========================
    $(document).on('click', '.quantity-btn.minus', function() {
        const productId = parseInt($(this).data('product-id'));
        const product = products.find(p => p.id === productId);
        if (product && product.quantity > 1) {
            updateQuantity(productId, product.quantity - 1);
        }
    });

    $(document).on('click', '.quantity-btn.plus', function() {
        const productId = parseInt($(this).data('product-id'));
        const product = products.find(p => p.id === productId);
        if (product) {
            updateQuantity(productId, product.quantity + 1);
        }
    });

    $(document).on('change', '.quantity-input', function() {
        const productId = parseInt($(this).data('product-id'));
        const newQuantity = parseInt($(this).val()) || 1;
        updateQuantity(productId, newQuantity);
    });

    $(document).on('click', '.remove-btn', function() {
        const productId = parseInt($(this).data('product-id'));
        removeProduct(productId);
    });

    // Gestion des statuts
    $('.status-badge').on('click', function() {
        $('.status-badge').removeClass('active');
        $(this).addClass('active');
        const newStatus = $(this).data('status');
        $('#status').val(newStatus);
        
        // Gérer les champs conditionnels
        handleConditionalFields(newStatus);
        
        if (newStatus === 'confirmée' && originalStatus !== 'confirmée') {
            checkStock();
        } else if (newStatus !== 'confirmée') {
            $('#stock-warning').removeClass('show');
        }
    });

    // Gestion des priorités
    $('.priority-badge').on('click', function() {
        $('.priority-badge').removeClass('active');
        $(this).addClass('active');
        $('#priority').val($(this).data('priority'));
    });

    // Masquer suggestions en cliquant ailleurs
    $(document).on('click', function(e) {
        if (!$(e.target).closest('.product-search').length) {
            $('#product-suggestions').hide();
        }
    });

    // Validation du formulaire avec messages d'erreur français
    $('#orderForm').on('submit', function(e) {
        const errors = [];
        
        if (!$('#customer_phone').val().trim()) {
            errors.push('Le numéro de téléphone principal est obligatoire');
        }
        
        if (products.length === 0) {
            errors.push('Veuillez ajouter au moins un produit');
        }
        
        const newStatus = $('#status').val();
        
        // Validation du champ date si statut datée
        if (newStatus === 'datée' && !$('#scheduled_date').val()) {
            errors.push('La date de livraison est obligatoire pour une commande datée');
        }
        
        // Validation du stock pour confirmation avec messages français
        if (newStatus === 'confirmée') {
            let stockErrors = [];
            products.forEach(product => {
                if (product.stock < product.quantity) {
                    stockErrors.push(`${product.name}: stock insuffisant (${product.stock} disponible, ${product.quantity} demandée)`);
                }
            });
            
            if (stockErrors.length > 0) {
                errors.push('Impossible de confirmer - Stock insuffisant pour certains produits:\n' + stockErrors.join('\n'));
            }
        }

        if (errors.length > 0) {
            e.preventDefault();
            alert('Erreurs détectées:\n\n' + errors.join('\n\n'));
            return false;
        }

        if (newStatus === 'confirmée' && originalStatus !== 'confirmée') {
            if (!confirm('Confirmer cette commande ?\n\nLe stock sera automatiquement déduit des produits.')) {
                e.preventDefault();
                return false;
            }
        }
        
        // Désactiver le bouton pour éviter double soumission
        $('#save-btn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Enregistrement...');
    });

    // Vérifier le téléphone au chargement
    const currentPhone = $('#customer_phone').val();
    if (currentPhone && currentPhone.length >= 8) {
        checkPhoneForDuplicates(currentPhone);
    }

    // Initialiser les champs conditionnels
    handleConditionalFields('{{ $order->status }}');
    checkStock();
});
</script>
@endsection