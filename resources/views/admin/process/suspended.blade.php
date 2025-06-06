@extends('layouts.admin')

@section('title', 'Commandes Suspendues')
@section('page-title', 'Gestion des Commandes Suspendues')

@section('css')
<style>
    :root {
        --suspended-primary: #8b5cf6;
        --suspended-secondary: #7c3aed;
        --suspended-success: #10b981;
        --suspended-warning: #ef4444;
        --suspended-danger: #f59e0b;
        --suspended-info: #06b6d4;
        --glass-bg: rgba(255, 255, 255, 0.95);
        --shadow-modern: 0 4px 6px rgba(0, 0, 0, 0.05);
        --shadow-elevated: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
        --border-radius-modern: 12px;
        --transition-smooth: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    body {
        background: linear-gradient(135deg, #f3e8ff 0%, #e9d5ff 100%);
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
    }

    /* Container principal optimisé pour desktop */
    .suspended-container {
        background: white;
        border-radius: var(--border-radius-modern);
        box-shadow: var(--shadow-modern);
        margin: 0.5rem;
        min-height: calc(100vh - 120px);
        overflow: hidden;
    }

    /* Header simplifié */
    .suspended-header {
        background: linear-gradient(135deg, var(--suspended-primary) 0%, var(--suspended-secondary) 100%);
        padding: 1.25rem 2rem;
        color: white;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .header-title {
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .header-icon {
        width: 40px;
        height: 40px;
        background: rgba(255, 255, 255, 0.15);
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
    }

    .header-text h1 {
        font-size: 1.5rem;
        font-weight: 700;
        margin: 0;
    }

    .header-subtitle {
        font-size: 0.875rem;
        opacity: 0.9;
        margin: 0;
    }

    .header-stats {
        background: rgba(255, 255, 255, 0.15);
        padding: 0.75rem 1.25rem;
        border-radius: 8px;
        text-align: center;
        backdrop-filter: blur(10px);
    }

    .stat-number {
        font-size: 1.5rem;
        font-weight: 700;
        line-height: 1;
    }

    .stat-label {
        font-size: 0.75rem;
        opacity: 0.9;
        margin-top: 0.25rem;
    }

    /* Barre d'actions modernisée */
    .actions-bar {
        background: #f8fafc;
        padding: 1rem 2rem;
        border-bottom: 1px solid #e5e7eb;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1.5rem;
    }

    .actions-left {
        display: flex;
        align-items: center;
        gap: 1rem;
        flex: 1;
    }

    .actions-right {
        display: flex;
        gap: 0.75rem;
        align-items: center;
    }

    /* Recherche en temps réel */
    .search-container {
        position: relative;
        flex: 1;
        max-width: 400px;
    }

    .search-input {
        width: 100%;
        padding: 0.75rem 1rem 0.75rem 2.5rem;
        border: 2px solid #e5e7eb;
        border-radius: 12px;
        font-size: 0.875rem;
        font-weight: 500;
        transition: var(--transition-smooth);
        background: white;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    }

    .search-input:focus {
        border-color: var(--suspended-primary);
        box-shadow: 0 0 0 3px rgba(139, 92, 246, 0.1);
        outline: none;
    }

    .search-icon {
        position: absolute;
        left: 0.875rem;
        top: 50%;
        transform: translateY(-50%);
        color: #6b7280;
        font-size: 0.875rem;
        z-index: 10;
    }

    .search-clear {
        position: absolute;
        right: 0.875rem;
        top: 50%;
        transform: translateY(-50%);
        color: #9ca3af;
        cursor: pointer;
        font-size: 0.875rem;
        opacity: 0;
        transition: var(--transition-smooth);
        z-index: 10;
    }

    .search-input:not(:placeholder-shown) + .search-icon + .search-clear {
        opacity: 1;
    }

    .search-clear:hover {
        color: #ef4444;
    }

    /* Dropdown filtres avancés */
    .filters-dropdown {
        position: relative;
    }

    .filters-trigger {
        background: white;
        border: 2px solid #e5e7eb;
        color: #374151;
        padding: 0.75rem 1rem;
        border-radius: 12px;
        font-weight: 600;
        font-size: 0.875rem;
        cursor: pointer;
        transition: var(--transition-smooth);
        display: flex;
        align-items: center;
        gap: 0.5rem;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    }

    .filters-trigger:hover {
        border-color: var(--suspended-primary);
        transform: translateY(-1px);
    }

    .filters-trigger.active {
        background: var(--suspended-primary);
        border-color: var(--suspended-primary);
        color: white;
    }

    .filters-badge {
        background: #ef4444;
        color: white;
        font-size: 0.7rem;
        padding: 0.125rem 0.375rem;
        border-radius: 8px;
        font-weight: 700;
        min-width: 16px;
        text-align: center;
        line-height: 1;
    }

    .filters-dropdown-menu {
        position: absolute;
        top: calc(100% + 0.5rem);
        right: 0;
        background: white;
        border-radius: 12px;
        box-shadow: var(--shadow-elevated);
        border: 1px solid #e5e7eb;
        min-width: 320px;
        z-index: 1000;
        opacity: 0;
        visibility: hidden;
        transform: translateY(-10px);
        transition: var(--transition-smooth);
    }

    .filters-dropdown-menu.show {
        opacity: 1;
        visibility: visible;
        transform: translateY(0);
    }

    .filters-header {
        background: #f8fafc;
        padding: 1rem 1.25rem;
        border-bottom: 1px solid #e5e7eb;
        border-radius: 12px 12px 0 0;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .filters-title {
        font-weight: 600;
        color: #374151;
        font-size: 0.875rem;
    }

    .filters-clear {
        color: #6b7280;
        font-size: 0.8rem;
        cursor: pointer;
        transition: var(--transition-smooth);
    }

    .filters-clear:hover {
        color: #ef4444;
    }

    .filters-body {
        padding: 1.25rem;
    }

    .filters-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1rem;
        margin-bottom: 1rem;
    }

    .filter-group {
        display: flex;
        flex-direction: column;
        gap: 0.375rem;
    }

    .filter-group.full-width {
        grid-column: 1 / -1;
    }

    .filter-label {
        font-size: 0.75rem;
        font-weight: 600;
        color: #374151;
        text-transform: uppercase;
        letter-spacing: 0.025em;
    }

    .filter-input {
        border: 1px solid #d1d5db;
        border-radius: 8px;
        padding: 0.5rem 0.75rem;
        font-size: 0.875rem;
        transition: var(--transition-smooth);
        background: white;
    }

    .filter-input:focus {
        border-color: var(--suspended-primary);
        box-shadow: 0 0 0 3px rgba(139, 92, 246, 0.1);
        outline: none;
    }

    .filters-actions {
        display: flex;
        gap: 0.75rem;
        padding-top: 1rem;
        border-top: 1px solid #e5e7eb;
    }

    .filters-apply {
        background: var(--suspended-primary);
        color: white;
        border: none;
        padding: 0.5rem 1rem;
        border-radius: 8px;
        font-weight: 600;
        font-size: 0.875rem;
        cursor: pointer;
        transition: var(--transition-smooth);
        flex: 1;
    }

    .filters-apply:hover {
        background: var(--suspended-secondary);
        transform: translateY(-1px);
    }

    .filters-reset {
        background: #f3f4f6;
        color: #374151;
        border: 1px solid #d1d5db;
        padding: 0.5rem 1rem;
        border-radius: 8px;
        font-weight: 600;
        font-size: 0.875rem;
        cursor: pointer;
        transition: var(--transition-smooth);
    }

    .filters-reset:hover {
        background: #e5e7eb;
    }

    /* Toggle vue liste/grille */
    .view-toggle {
        display: flex;
        background: #e5e7eb;
        border-radius: 8px;
        padding: 0.25rem;
    }

    .view-btn {
        padding: 0.5rem 0.75rem;
        border: none;
        background: transparent;
        border-radius: 6px;
        cursor: pointer;
        transition: var(--transition-smooth);
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-weight: 600;
        color: #6b7280;
        font-size: 0.875rem;
    }

    .view-btn.active {
        background: white;
        color: var(--suspended-primary);
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    }

    /* Mode sélection groupée avec palette violette */
    .bulk-mode-banner {
        background: linear-gradient(135deg, var(--suspended-primary) 0%, var(--suspended-secondary) 100%);
        color: white;
        padding: 0.875rem 2rem;
        display: none;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        animation: slideDown 0.3s ease-out;
    }

    .bulk-mode-banner.active {
        display: flex;
    }

    .bulk-info {
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .bulk-count {
        background: rgba(255, 255, 255, 0.2);
        padding: 0.25rem 0.75rem;
        border-radius: 15px;
        font-weight: 600;
        font-size: 0.875rem;
    }

    .bulk-actions {
        display: flex;
        gap: 0.75rem;
    }

    .bulk-btn {
        background: rgba(255, 255, 255, 0.15);
        border: 1px solid rgba(255, 255, 255, 0.3);
        color: white;
        padding: 0.5rem 1rem;
        border-radius: 6px;
        font-size: 0.875rem;
        font-weight: 600;
        cursor: pointer;
        transition: var(--transition-smooth);
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .bulk-btn:hover {
        background: rgba(255, 255, 255, 0.25);
        transform: translateY(-1px);
    }

    /* Container de contenu avec vues */
    .content-container {
        position: relative;
        min-height: 600px;
    }

    /* Vue tableau (liste) */
    .table-container {
        background: white;
        overflow: hidden;
        position: relative;
        display: none;
    }

    .table-container.active {
        display: block;
    }

    .table {
        width: 100%;
        margin: 0;
        border-collapse: collapse;
    }

    .table thead th {
        background: #f8fafc;
        border: none;
        padding: 0.875rem 1rem;
        font-weight: 600;
        color: #374151;
        text-align: left;
        border-bottom: 1px solid #e5e7eb;
        white-space: nowrap;
        position: sticky;
        top: 0;
        z-index: 10;
        font-size: 0.875rem;
    }

    .table tbody td {
        padding: 0.875rem 1rem;
        vertical-align: middle;
        border-bottom: 1px solid #f1f5f9;
        transition: var(--transition-smooth);
        font-size: 0.875rem;
    }

    .table tbody tr {
        transition: var(--transition-smooth);
        cursor: pointer;
    }

    .table tbody tr:hover {
        background: #f8fafc;
        transform: translateX(2px);
        box-shadow: 4px 0 8px rgba(0, 0, 0, 0.05);
    }

    .table tbody tr.selected {
        background: #f3e8ff !important;
        border-left: 4px solid var(--suspended-primary);
    }

    /* Vue grille */
    .grid-container {
        padding: 1.5rem;
        display: none;
    }

    .grid-container.active {
        display: block;
    }

    .orders-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
        gap: 1rem;
    }

    .order-card {
        background: white;
        border-radius: 16px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        border: 1px solid #f1f5f9;
        overflow: hidden;
        transition: var(--transition-smooth);
        position: relative;
        border-left: 4px solid var(--suspended-primary);
    }

    .order-card:hover {
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.12);
        transform: translateY(-3px);
        border-color: var(--suspended-primary);
    }

    .order-card.selected {
        border-left-color: var(--suspended-secondary);
        box-shadow: 0 0 0 2px rgba(139, 92, 246, 0.2);
        background: linear-gradient(135deg, #f5f3ff 0%, #f3e8ff 100%);
    }

    .order-card-checkbox {
        width: 18px;
        height: 18px;
        border-radius: 6px;
        border: 2px solid #d1d5db;
        background: white;
        cursor: pointer;
        transition: var(--transition-smooth);
        margin: 0;
    }

    .order-card-checkbox:checked {
        background: var(--suspended-primary);
        border-color: var(--suspended-primary);
    }

    .order-card-header {
        background: linear-gradient(135deg, #f3e8ff 0%, #ede9fe 100%);
        padding: 0.875rem;
        border-bottom: 1px solid #f3f4f6;
        position: relative;
    }

    .order-card-body {
        padding: 0.875rem;
    }

    /* Styles pour les cartes modernes et compactes */
    .card-header-top {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 0.5rem;
    }

    .card-checkbox-container {
        display: flex;
        align-items: center;
    }

    .card-header-badges {
        display: flex;
        gap: 0.375rem;
        align-items: center;
        justify-content: space-between;
    }

    .status-badge-compact {
        padding: 0.25rem 0.5rem;
        border-radius: 12px;
        font-size: 0.7rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.025em;
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
    }

    .priority-badge-compact {
        padding: 0.2rem 0.4rem;
        border-radius: 8px;
        font-size: 0.65rem;
        font-weight: 600;
        text-transform: uppercase;
    }

    .customer-compact {
        margin-bottom: 0.75rem;
    }

    .customer-row {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        margin-bottom: 0.375rem;
        font-size: 0.8rem;
    }

    .customer-row:last-child {
        margin-bottom: 0;
    }

    .customer-icon {
        width: 14px;
        color: #6b7280;
        font-size: 0.75rem;
        flex-shrink: 0;
    }

    .customer-name {
        font-weight: 600;
        color: #374151;
        flex: 1;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .customer-phone {
        color: #6b7280;
        font-family: 'JetBrains Mono', monospace;
        font-size: 0.75rem;
    }

    .customer-date {
        color: #9ca3af;
        font-size: 0.75rem;
    }

    .suspension-compact {
        background: #fef2f2;
        border: 1px solid #fecaca;
        border-radius: 8px;
        padding: 0.5rem;
        margin-bottom: 0.75rem;
    }

    .suspension-compact-header {
        display: flex;
        align-items: center;
        gap: 0.375rem;
        color: #dc2626;
        font-weight: 600;
        font-size: 0.75rem;
        margin-bottom: 0.25rem;
    }

    .suspension-reason {
        font-size: 0.7rem;
        color: #7f1d1d;
        line-height: 1.3;
    }

    .availability-compact {
        margin-bottom: 0.75rem;
    }

    .availability-status {
        display: inline-flex;
        align-items: center;
        gap: 0.375rem;
        padding: 0.25rem 0.5rem;
        border-radius: 8px;
        font-size: 0.7rem;
        font-weight: 600;
    }

    .availability-status.available {
        background: #f0fdf4;
        color: #059669;
        border: 1px solid #bbf7d0;
    }

    .availability-status.unavailable {
        background: #fef2f2;
        color: #dc2626;
        border: 1px solid #fecaca;
    }

    .card-footer {
        display: flex;
        justify-content: center;
        align-items: center;
        padding-top: 0.75rem;
        border-top: 1px solid #f1f5f9;
    }

    .price-compact {
        font-family: 'JetBrains Mono', monospace;
        font-weight: 700;
        color: var(--suspended-primary);
        font-size: 0.875rem;
    }

    .card-actions {
        display: flex;
        gap: 0.375rem;
    }

    .card-action-btn {
        width: 28px;
        height: 28px;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        transition: var(--transition-smooth);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.75rem;
        position: relative;
        overflow: hidden;
    }

    .card-action-btn::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(255, 255, 255, 0.2);
        transform: translateX(-100%);
        transition: transform 0.3s ease;
    }

    .card-action-btn:hover::before {
        transform: translateX(0);
    }

    .card-action-btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
    }

    .card-action-btn.reactivate {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        color: white;
    }

    .card-action-btn.edit {
        background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%);
        color: white;
    }

    .card-action-btn.cancel {
        background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        color: white;
    }

    .card-action-btn.modify {
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        color: white;
    }

    /* Composants communs */
    .order-id {
        font-family: 'JetBrains Mono', monospace;
        font-weight: 700;
        color: var(--suspended-primary);
        font-size: 0.95rem;
    }

    .customer-info {
        display: flex;
        flex-direction: column;
        gap: 0.25rem;
    }

    .status-badge {
        padding: 0.375rem 0.75rem;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        display: inline-flex;
        align-items: center;
        gap: 0.375rem;
    }

    .status-nouvelle { background: rgba(107, 114, 128, 0.1); color: #374151; }
    .status-confirmée { background: rgba(16, 185, 129, 0.1); color: #059669; }
    .status-datée { background: rgba(245, 158, 11, 0.1); color: #d97706; }
    .status-annulée { background: rgba(239, 68, 68, 0.1); color: #dc2626; }

    .priority-badge {
        padding: 0.25rem 0.5rem;
        border-radius: 12px;
        font-size: 0.7rem;
        font-weight: 600;
        text-transform: uppercase;
    }

    .priority-normale { background: #f3f4f6; color: #6b7280; }
    .priority-urgente { background: #fef3c7; color: #d97706; }
    .priority-vip { background: #fee2e2; color: #dc2626; }

    .price-display {
        font-family: 'JetBrains Mono', monospace;
        font-weight: 600;
        color: #059669;
        font-size: 0.9rem;
    }

    /* Section de suspension */
    .suspension-info {
        background: #fef2f2;
        border: 1px solid #fecaca;
        border-radius: 6px;
        padding: 0.5rem;
        margin-bottom: 0.5rem;
        max-width: 280px;
    }

    .suspension-header {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        color: #dc2626;
        font-weight: 600;
        font-size: 0.8rem;
        margin-bottom: 0.25rem;
    }

    .stock-status {
        background: #f0fdf4;
        border: 1px solid #bbf7d0;
        border-radius: 6px;
        padding: 0.5rem;
        max-width: 280px;
    }

    .stock-header {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        color: #059669;
        font-weight: 600;
        font-size: 0.8rem;
    }

    .stock-unavailable {
        background: #fef2f2;
        border: 1px solid #fecaca;
    }

    .stock-unavailable .stock-header {
        color: #dc2626;
    }

    /* Actions */
    .action-buttons {
        display: flex;
        gap: 0.5rem;
        flex-wrap: wrap;
    }

    .action-btn {
        padding: 0.375rem 0.75rem;
        border: none;
        border-radius: 6px;
        font-weight: 600;
        font-size: 0.75rem;
        cursor: pointer;
        transition: var(--transition-smooth);
        display: flex;
        align-items: center;
        gap: 0.375rem;
        text-decoration: none;
        white-space: nowrap;
    }

    .action-btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }

    .btn-reactivate { 
        background: linear-gradient(135deg, #10b981 0%, #059669 100%); 
        color: white; 
    }
    .btn-edit { 
        background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%); 
        color: white; 
    }
    .btn-cancel { 
        background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); 
        color: white; 
    }
    .btn-modify { 
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); 
        color: white; 
    }

    /* Boutons principaux */
    .btn-primary {
        background: linear-gradient(135deg, var(--suspended-primary) 0%, var(--suspended-secondary) 100%);
        border: none;
        color: white;
        padding: 0.625rem 1.25rem;
        border-radius: 8px;
        font-weight: 600;
        font-size: 0.875rem;
        cursor: pointer;
        transition: var(--transition-smooth);
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .btn-primary:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(139, 92, 246, 0.3);
    }

    .btn-secondary {
        background: #f3f4f6;
        border: 1px solid #d1d5db;
        color: #374151;
        padding: 0.625rem 1.25rem;
        border-radius: 8px;
        font-weight: 600;
        font-size: 0.875rem;
        cursor: pointer;
        transition: var(--transition-smooth);
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .btn-secondary:hover {
        background: #e5e7eb;
        transform: translateY(-1px);
    }

    /* Case à cocher */
    .checkbox-cell {
        width: 40px;
        text-align: center;
    }

    .order-checkbox {
        width: 18px;
        height: 18px;
        border-radius: 4px;
        border: 2px solid #d1d5db;
        cursor: pointer;
        transition: var(--transition-smooth);
    }

    .order-checkbox:checked {
        background: var(--suspended-primary);
        border-color: var(--suspended-primary);
    }

    /* États vides et de chargement */
    .empty-state, .loading-state {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        text-align: center;
        color: #6b7280;
        padding: 2rem;
    }

    .empty-state i, .loading-state i {
        font-size: 3rem;
        margin-bottom: 1rem;
        opacity: 0.5;
        color: var(--suspended-primary);
    }

    .empty-state h3, .loading-state h3 {
        font-size: 1.25rem;
        margin-bottom: 0.5rem;
        color: #374151;
    }

    .loading-spinner {
        animation: spin 1s linear infinite;
    }

    @keyframes spin {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }

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

    /* Responsive optimisé */
    @media (max-width: 1400px) {
        .orders-grid {
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        }
    }

    @media (max-width: 1200px) {
        .table thead th:nth-child(5),
        .table tbody td:nth-child(5) {
            display: none;
        }
        
        .orders-grid {
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        }
    }

    @media (max-width: 768px) {
        .suspended-container {
            margin: 0.25rem;
        }

        .suspended-header {
            padding: 1rem;
            flex-direction: column;
            gap: 1rem;
            text-align: center;
        }

        .actions-bar {
            flex-direction: column;
            align-items: stretch;
            padding: 1rem;
            gap: 1rem;
        }

        .actions-left {
            flex-direction: column;
            gap: 1rem;
        }

        .search-container {
            max-width: none;
        }

        .filters-dropdown-menu {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: calc(100vw - 2rem);
            max-width: 400px;
        }

        .filters-grid {
            grid-template-columns: 1fr;
        }

        .bulk-mode-banner {
            flex-direction: column;
            text-align: center;
            padding: 1rem;
        }

        .table-container {
            overflow-x: auto;
        }

        .table {
            min-width: 800px;
        }

        .orders-grid {
            grid-template-columns: 1fr;
            gap: 0.75rem;
        }

        .card-header-top {
            flex-direction: column;
            gap: 0.5rem;
            align-items: flex-start;
        }

        .card-checkbox-container {
            align-self: flex-start;
        }

        .card-header-badges {
            align-self: stretch;
            justify-content: space-between;
        }

        .card-footer {
            flex-direction: column;
            gap: 0.75rem;
            align-items: stretch;
        }

        .card-actions {
            justify-content: center;
        }
    }

    /* Animations */
    .fade-in {
        animation: fadeIn 0.3s ease-out;
    }

    .slide-up {
        animation: slideUp 0.3s ease-out;
    }

    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }

    @keyframes slideUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .table tbody tr, .order-card {
        animation: slideUp 0.2s ease-out;
    }
</style>
@endsection

@section('content')
<div class="suspended-container">
    <!-- Header simplifié -->
    <div class="suspended-header">
        <div class="header-title">
            <div class="header-icon">
                <i class="fas fa-pause-circle"></i>
            </div>
            <div class="header-text">
                <h1>Commandes Suspendues</h1>
                <p class="header-subtitle">Gestion et réactivation des commandes suspendues</p>
            </div>
        </div>
        
        <div class="header-stats">
            <div class="stat-number" id="orders-count">0</div>
            <div class="stat-label">Commandes</div>
        </div>
    </div>

    <!-- Barre d'actions modernisée -->
    <div class="actions-bar">
        <div class="actions-left">
            <!-- Recherche en temps réel -->
            <div class="search-container">
                <i class="fas fa-search search-icon"></i>
                <input type="text" class="search-input" id="realtime-search" 
                       placeholder="Rechercher par ID, nom, raison...">
                <i class="fas fa-times search-clear" onclick="clearSearch()"></i>
            </div>

            <!-- Dropdown filtres avancés -->
            <div class="filters-dropdown">
                <button class="filters-trigger" id="filters-trigger" onclick="toggleFiltersDropdown()">
                    <i class="fas fa-filter"></i>
                    <span>Filtres avancés</span>
                    <span class="filters-badge" id="filters-count" style="display: none;">0</span>
                    <i class="fas fa-chevron-down" style="margin-left: auto;"></i>
                </button>
                
                <div class="filters-dropdown-menu" id="filters-dropdown-menu">
                    <div class="filters-header">
                        <span class="filters-title">Filtres avancés</span>
                        <span class="filters-clear" onclick="clearAllFilters()">Tout effacer</span>
                    </div>
                    <div class="filters-body">
                        <div class="filters-grid">
                            <div class="filter-group">
                                <label class="filter-label">Statut</label>
                                <select class="filter-input" id="filter-status">
                                    <option value="">Tous</option>
                                    <option value="nouvelle">Nouvelle</option>
                                    <option value="confirmée">Confirmée</option>
                                    <option value="datée">Datée</option>
                                </select>
                            </div>

                            <div class="filter-group">
                                <label class="filter-label">Priorité</label>
                                <select class="filter-input" id="filter-priority">
                                    <option value="">Toutes</option>
                                    <option value="normale">Normale</option>
                                    <option value="urgente">Urgente</option>
                                    <option value="vip">VIP</option>
                                </select>
                            </div>

                            <div class="filter-group">
                                <label class="filter-label">Disponibilité</label>
                                <select class="filter-input" id="filter-stock">
                                    <option value="">Toutes</option>
                                    <option value="yes">Avec problèmes</option>
                                    <option value="no">Sans problèmes</option>
                                </select>
                            </div>

                            <div class="filter-group">
                                <label class="filter-label">Date début</label>
                                <input type="date" class="filter-input" id="filter-date-from">
                            </div>

                            <div class="filter-group">
                                <label class="filter-label">Date fin</label>
                                <input type="date" class="filter-input" id="filter-date-to">
                            </div>
                        </div>
                        
                        <div class="filters-actions">
                            <button class="filters-apply" onclick="applyAdvancedFilters()">
                                <i class="fas fa-check"></i>
                                Appliquer
                            </button>
                            <button class="filters-reset" onclick="resetAdvancedFilters()">
                                <i class="fas fa-undo"></i>
                                Reset
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="actions-right">
            <!-- Toggle vue -->
            <div class="view-toggle">
                <button class="view-btn active" id="list-view-btn" onclick="switchView('list')">
                    <i class="fas fa-list"></i>
                    Liste
                </button>
                <button class="view-btn" id="grid-view-btn" onclick="switchView('grid')">
                    <i class="fas fa-th"></i>
                    Grille
                </button>
            </div>

            <button class="btn-primary" onclick="refreshOrders()">
                <i class="fas fa-sync-alt"></i>
                Actualiser
            </button>
        </div>
    </div>

    <!-- Banner mode sélection groupée -->
    <div class="bulk-mode-banner" id="bulk-mode-banner">
        <div class="bulk-info">
            <i class="fas fa-check-square"></i>
            <span class="bulk-count" id="bulk-count">0 sélectionnée(s)</span>
            <span>Mode sélection activé</span>
        </div>
        <div class="bulk-actions">
            <button class="bulk-btn" onclick="bulkReactivate()">
                <i class="fas fa-play-circle"></i>
                Réactiver
            </button>
            <button class="bulk-btn" onclick="bulkCancel()">
                <i class="fas fa-times-circle"></i>
                Annuler
            </button>
            <button class="bulk-btn" onclick="clearSelection()">
                <i class="fas fa-times"></i>
                Effacer sélection
            </button>
        </div>
    </div>

    <!-- Container de contenu avec switch vue -->
    <div class="content-container">
        <!-- États de chargement et vide -->
        <div class="loading-state fade-in" id="loading-state">
            <i class="fas fa-spinner loading-spinner"></i>
            <h3>Chargement en cours...</h3>
            <p>Recherche des commandes suspendues</p>
        </div>
        
        <div class="empty-state fade-in" id="empty-state" style="display: none;">
            <i class="fas fa-check-circle"></i>
            <h3>Aucune commande suspendue !</h3>
            <p>Toutes les commandes sont actuellement actives.</p>
        </div>

        <!-- Vue Liste (par défaut) -->
        <div class="table-container active" id="table-view">
            <table class="table" id="orders-table">
                <thead>
                    <tr>
                        <th class="checkbox-cell">
                            <input type="checkbox" class="order-checkbox" id="select-all">
                        </th>
                        <th>ID</th>
                        <th>Client</th>
                        <th>Statut & Priorité</th>
                        <th>Raison de Suspension</th>
                        <th>Disponibilité</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="orders-tbody">
                    <!-- Les commandes seront chargées ici -->
                </tbody>
            </table>
        </div>

        <!-- Vue Grille -->
        <div class="grid-container" id="grid-view">
            <div class="orders-grid" id="orders-grid">
                <!-- Les cartes seront chargées ici -->
            </div>
        </div>
    </div>
</div>

<!-- Modales -->
@include('admin.process.suspended-modals')
@include('admin.process.bulk-modals')

@endsection

@section('scripts')
<script>
$(document).ready(function() {
    let orders = [];
    let allOrders = [];
    let selectedOrders = [];
    let filters = {};
    let currentView = 'list';
    let searchTerm = '';
    
    // =========================
    // INITIALISATION
    // =========================
    
    function initialize() {
        if (typeof $ === 'undefined') {
            console.error('jQuery non chargé!');
            return;
        }
        
        const csrfToken = $('meta[name="csrf-token"]').attr('content');
        if (!csrfToken) {
            console.error('Token CSRF non trouvé!');
            return;
        }
        
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
                'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
            }
        });
        
        setupEventListeners();
        loadSuspendedOrders();
    }
    
    function setupEventListeners() {
        // Recherche en temps réel
        $('#realtime-search').on('input', debounce(handleRealtimeSearch, 300));
        
        // Filtres avancés
        $('#filter-status, #filter-priority, #filter-stock, #filter-date-from, #filter-date-to').on('change', updateFiltersCount);
        
        // Sélection
        $('#select-all').on('change', toggleSelectAll);
        $(document).on('change', '.order-checkbox:not(#select-all), .order-card-checkbox', updateSelection);
        
        // Événements pour les lignes du tableau
        $(document).on('click', 'tbody tr', function(e) {
            if (!$(e.target).is('input, button, a')) {
                const checkbox = $(this).find('.order-checkbox');
                checkbox.prop('checked', !checkbox.prop('checked')).trigger('change');
            }
        });
        
        // Événements pour les cartes
        $(document).on('click', '.order-card', function(e) {
            if (!$(e.target).is('input, button, a')) {
                const checkbox = $(this).find('.order-card-checkbox');
                checkbox.prop('checked', !checkbox.prop('checked')).trigger('change');
            }
        });

        // Fermer le dropdown si on clique à l'extérieur
        $(document).on('click', function(e) {
            if (!$(e.target).closest('.filters-dropdown').length) {
                hideFiltersDropdown();
            }
        });
    }
    
    // =========================
    // RECHERCHE EN TEMPS RÉEL
    // =========================
    
    function handleRealtimeSearch() {
        searchTerm = $('#realtime-search').val().trim().toLowerCase();
        applyFilters();
    }
    
    window.clearSearch = function() {
        $('#realtime-search').val('');
        searchTerm = '';
        applyFilters();
    }
    
    // =========================
    // GESTION DU DROPDOWN FILTRES
    // =========================
    
    window.toggleFiltersDropdown = function() {
        const menu = $('#filters-dropdown-menu');
        if (menu.hasClass('show')) {
            hideFiltersDropdown();
        } else {
            showFiltersDropdown();
        }
    }
    
    function showFiltersDropdown() {
        $('#filters-dropdown-menu').addClass('show');
        $('#filters-trigger').addClass('active');
    }
    
    function hideFiltersDropdown() {
        $('#filters-dropdown-menu').removeClass('show');
        $('#filters-trigger').removeClass('active');
    }
    
    function updateFiltersCount() {
        let count = 0;
        if ($('#filter-status').val()) count++;
        if ($('#filter-priority').val()) count++;
        if ($('#filter-stock').val()) count++;
        if ($('#filter-date-from').val()) count++;
        if ($('#filter-date-to').val()) count++;
        
        const badge = $('#filters-count');
        const trigger = $('#filters-trigger');
        
        if (count > 0) {
            badge.text(count).show();
            trigger.addClass('active');
        } else {
            badge.hide();
            if (!$('#filters-dropdown-menu').hasClass('show')) {
                trigger.removeClass('active');
            }
        }
    }
    
    window.applyAdvancedFilters = function() {
        applyFilters();
        hideFiltersDropdown();
    }
    
    window.resetAdvancedFilters = function() {
        $('#filter-status, #filter-priority, #filter-stock, #filter-date-from, #filter-date-to').val('');
        updateFiltersCount();
        applyFilters();
    }
    
    window.clearAllFilters = function() {
        $('#realtime-search').val('');
        $('#filter-status, #filter-priority, #filter-stock, #filter-date-from, #filter-date-to').val('');
        searchTerm = '';
        updateFiltersCount();
        applyFilters();
        hideFiltersDropdown();
    }
    
    // =========================
    // CHARGEMENT DES COMMANDES
    // =========================
    
    function loadSuspendedOrders() {
        showLoading();
        
        const params = new URLSearchParams(filters);
        
        $.get('/admin/process/suspended/orders?' + params.toString())
            .done(function(data) {
                if (data.hasOrders && data.orders && Array.isArray(data.orders)) {
                    allOrders = data.orders;
                    applyFilters();
                    updateOrdersCount(data.total || allOrders.length);
                } else {
                    showEmpty();
                    updateOrdersCount(0);
                }
            })
            .fail(function(xhr) {
                console.error('Erreur lors du chargement:', xhr);
                let errorMessage = 'Erreur lors du chargement des commandes';
                if (xhr.responseJSON && xhr.responseJSON.error) {
                    errorMessage = xhr.responseJSON.error;
                }
                showNotification(errorMessage, 'error');
                showEmpty();
                updateOrdersCount(0);
            });
    }
    
    // =========================
    // GESTION DES VUES
    // =========================
    
    window.switchView = function(view) {
        currentView = view;
        
        $('.view-btn').removeClass('active');
        $(`#${view}-view-btn`).addClass('active');
        
        if (view === 'list') {
            $('#table-view').addClass('active');
            $('#grid-view').removeClass('active');
        } else {
            $('#table-view').removeClass('active');
            $('#grid-view').addClass('active');
        }
        
        displayOrders(orders);
    }
    
    // =========================
    // AFFICHAGE DES COMMANDES
    // =========================
    
    function displayOrders(ordersToDisplay) {
        if (!Array.isArray(ordersToDisplay) || ordersToDisplay.length === 0) {
            showEmpty();
            return;
        }
        
        if (currentView === 'list') {
            displayTableView(ordersToDisplay);
        } else {
            displayGridView(ordersToDisplay);
        }
        
        showContent();
    }
    
    function displayTableView(ordersToDisplay) {
        const tbody = $('#orders-tbody');
        tbody.empty();
        
        ordersToDisplay.forEach(order => {
            const row = createOrderRow(order);
            tbody.append(row);
        });
    }
    
    function displayGridView(ordersToDisplay) {
        const grid = $('#orders-grid');
        grid.empty();
        
        ordersToDisplay.forEach(order => {
            const card = createOrderCard(order);
            grid.append(card);
        });
    }
    
    function createOrderRow(order) {
        const isSelected = selectedOrders.includes(order.id);
        const canReactivate = order.can_reactivate || false;
        
        // Statut de disponibilité
        let availabilityHtml = '';
        if (canReactivate) {
            availabilityHtml = `
                <div class="stock-status">
                    <div class="stock-header">
                        <i class="fas fa-check-circle"></i>
                        Peut être réactivée
                    </div>
                </div>
            `;
        } else {
            availabilityHtml = `
                <div class="stock-status stock-unavailable">
                    <div class="stock-header">
                        <i class="fas fa-exclamation-triangle"></i>
                        Problème de stock
                    </div>
                </div>
            `;
        }
        
        // Raison de suspension
        const suspensionHtml = `
            <div class="suspension-info">
                <div class="suspension-header">
                    <i class="fas fa-pause-circle"></i>
                    Suspension
                </div>
                <div class="suspension-reason">
                    ${order.suspension_reason || 'Aucune raison spécifiée'}
                </div>
            </div>
        `;
        
        // Actions disponibles
        const actionsHtml = `
            <div class="action-buttons">
                ${canReactivate ? `
                    <button class="action-btn btn-reactivate" onclick="showReactivateModal(${order.id})" title="Réactiver">
                        <i class="fas fa-play-circle"></i>
                    </button>
                ` : ''}
                <button class="action-btn btn-edit" onclick="editOrder(${order.id})" title="Modifier">
                    <i class="fas fa-edit"></i>
                </button>
                <button class="action-btn btn-modify" onclick="showModifySuspensionModal(${order.id})" title="Modifier raison">
                    <i class="fas fa-pen"></i>
                </button>
                <button class="action-btn btn-cancel" onclick="showCancelModal(${order.id})" title="Annuler">
                    <i class="fas fa-times-circle"></i>
                </button>
            </div>
        `;
        
        const row = $(`
            <tr class="${isSelected ? 'selected' : ''}" data-order-id="${order.id}">
                <td class="checkbox-cell">
                    <input type="checkbox" class="order-checkbox" data-order-id="${order.id}" ${isSelected ? 'checked' : ''}>
                </td>
                <td>
                    <span class="order-id">#${String(order.id).padStart(6, '0')}</span>
                </td>
                <td>
                    <div class="customer-info">
                        <div class="customer-name">${order.customer_name || 'Non spécifié'}</div>
                        <div class="customer-phone">
                            <i class="fas fa-phone"></i> ${order.customer_phone || 'N/A'}
                        </div>
                        ${order.customer_address ? `
                            <div class="customer-address" title="${order.customer_address}">
                                <i class="fas fa-map-marker-alt"></i> ${order.customer_address}
                            </div>
                        ` : ''}
                    </div>
                </td>
                <td>
                    <div class="status-badge status-${order.status || 'nouvelle'}">
                        <i class="fas fa-circle"></i>
                        ${capitalizeFirst(order.status || 'nouvelle')}
                    </div>
                    <br>
                    <div class="priority-badge priority-${order.priority || 'normale'}" style="margin-top: 0.5rem;">
                        ${capitalizeFirst(order.priority || 'normale')}
                    </div>
                </td>
                <td>${suspensionHtml}</td>
                <td>${availabilityHtml}</td>
                <td>
                    <div style="font-size: 0.875rem; color: #6b7280;">
                        ${formatDate(order.created_at)}
                    </div>
                </td>
                <td>${actionsHtml}</td>
            </tr>
        `);
        
        return row;
    }
    
    function createOrderCard(order) {
        const isSelected = selectedOrders.includes(order.id);
        const canReactivate = order.can_reactivate || false;
        
        const actionsHtml = `
            <div class="card-actions">
                ${canReactivate ? `
                    <button class="card-action-btn reactivate" onclick="showReactivateModal(${order.id})" title="Réactiver">
                        <i class="fas fa-play-circle"></i>
                    </button>
                ` : ''}
                <button class="card-action-btn edit" onclick="editOrder(${order.id})" title="Modifier">
                    <i class="fas fa-edit"></i>
                </button>
                <button class="card-action-btn modify" onclick="showModifySuspensionModal(${order.id})" title="Modifier raison">
                    <i class="fas fa-pen"></i>
                </button>
                <button class="card-action-btn cancel" onclick="showCancelModal(${order.id})" title="Annuler">
                    <i class="fas fa-times-circle"></i>
                </button>
            </div>
        `;
        
        const card = $(`
            <div class="order-card ${isSelected ? 'selected' : ''}" data-order-id="${order.id}">
                <div class="order-card-header">
                    <div class="card-header-top">
                        <div class="card-checkbox-container">
                            <input type="checkbox" class="order-card-checkbox" data-order-id="${order.id}" ${isSelected ? 'checked' : ''}>
                        </div>
                        <span class="order-id">#${String(order.id).padStart(6, '0')}</span>
                        <div class="price-compact">${parseFloat(order.total_price || 0).toFixed(3)} TND</div>
                    </div>
                    <div class="card-header-badges">
                        <span class="status-badge-compact status-${order.status || 'nouvelle'}">${capitalizeFirst(order.status || 'nouvelle')}</span>
                        <span class="priority-badge-compact priority-${order.priority || 'normale'}">${capitalizeFirst(order.priority || 'normale')}</span>
                    </div>
                </div>
                
                <div class="order-card-body">
                    <div class="customer-compact">
                        <div class="customer-row">
                            <i class="fas fa-user customer-icon"></i>
                            <span class="customer-name">${order.customer_name || 'Non spécifié'}</span>
                        </div>
                        <div class="customer-row">
                            <i class="fas fa-phone customer-icon"></i>
                            <span class="customer-phone">${order.customer_phone || 'N/A'}</span>
                        </div>
                        <div class="customer-row">
                            <i class="fas fa-calendar customer-icon"></i>
                            <span class="customer-date">${formatDate(order.created_at)}</span>
                        </div>
                    </div>
                    
                    <div class="suspension-compact">
                        <div class="suspension-compact-header">
                            <i class="fas fa-pause-circle"></i>
                            <span>Raison de suspension</span>
                        </div>
                        <div class="suspension-reason">
                            ${order.suspension_reason || 'Aucune raison spécifiée'}
                        </div>
                    </div>
                    
                    <div class="availability-compact">
                        <div class="availability-status ${canReactivate ? 'available' : 'unavailable'}">
                            <i class="fas fa-${canReactivate ? 'check-circle' : 'exclamation-triangle'}"></i>
                            <span>${canReactivate ? 'Peut être réactivée' : 'Problème de stock'}</span>
                        </div>
                    </div>
                    
                    <div class="card-footer">
                        ${actionsHtml}
                    </div>
                </div>
            </div>
        `);
        
        return card;
    }
    
    // =========================
    // GESTION DES FILTRES
    // =========================
    
    window.applyFilters = function() {
        filters = {
            search: searchTerm,
            status: $('#filter-status').val(),
            priority: $('#filter-priority').val(),
            has_stock_issues: $('#filter-stock').val(),
            date_from: $('#filter-date-from').val(),
            date_to: $('#filter-date-to').val()
        };
        
        let filteredOrders = allOrders.filter(order => {
            // Recherche en temps réel
            if (searchTerm) {
                const searchFields = [
                    order.id.toString(),
                    order.customer_name || '',
                    order.customer_phone || '',
                    order.suspension_reason || ''
                ].join(' ').toLowerCase();
                
                if (!searchFields.includes(searchTerm)) return false;
            }
            
            // Filtres avancés
            if (filters.status && order.status !== filters.status) return false;
            if (filters.priority && order.priority !== filters.priority) return false;
            
            if (filters.has_stock_issues) {
                if (filters.has_stock_issues === 'yes' && order.can_reactivate) return false;
                if (filters.has_stock_issues === 'no' && !order.can_reactivate) return false;
            }
            
            if (filters.date_from) {
                const orderDate = new Date(order.created_at);
                const fromDate = new Date(filters.date_from);
                if (orderDate < fromDate) return false;
            }
            
            if (filters.date_to) {
                const orderDate = new Date(order.created_at);
                const toDate = new Date(filters.date_to);
                toDate.setHours(23, 59, 59, 999);
                if (orderDate > toDate) return false;
            }
            
            return true;
        });
        
        orders = filteredOrders;
        displayOrders(orders);
        updateOrdersCount(orders.length);
    }
    
    window.refreshOrders = function() {
        clearSelection();
        loadSuspendedOrders();
    }
    
    // =========================
    // SÉLECTION ET ACTIONS GROUPÉES
    // =========================
    
    function toggleSelectAll() {
        const isChecked = $('#select-all').prop('checked');
        $('.order-checkbox:not(#select-all), .order-card-checkbox').prop('checked', isChecked);
        updateSelection();
    }
    
    function updateSelection() {
        selectedOrders = [];
        $('.order-checkbox:not(#select-all):checked, .order-card-checkbox:checked').each(function() {
            selectedOrders.push(parseInt($(this).data('order-id')));
        });
        
        // Mettre à jour les classes des lignes et cartes
        $('tbody tr, .order-card').removeClass('selected');
        selectedOrders.forEach(orderId => {
            $(`tr[data-order-id="${orderId}"], .order-card[data-order-id="${orderId}"]`).addClass('selected');
        });
        
        // Mettre à jour l'état du "Tout sélectionner"
        const totalCheckboxes = $('.order-checkbox:not(#select-all), .order-card-checkbox').length;
        const checkedCheckboxes = $('.order-checkbox:not(#select-all):checked, .order-card-checkbox:checked').length;
        $('#select-all').prop('indeterminate', checkedCheckboxes > 0 && checkedCheckboxes < totalCheckboxes);
        $('#select-all').prop('checked', checkedCheckboxes === totalCheckboxes && totalCheckboxes > 0);
        
        // Afficher/masquer le banner des actions groupées
        updateBulkActions();
    }
    
    function updateBulkActions() {
        const count = selectedOrders.length;
        if (count > 0) {
            $('#bulk-count').text(`${count} sélectionnée${count > 1 ? 's' : ''}`);
            $('#bulk-mode-banner').addClass('active');
        } else {
            $('#bulk-mode-banner').removeClass('active');
        }
    }
    
    window.clearSelection = function() {
        selectedOrders = [];
        $('.order-checkbox, .order-card-checkbox').prop('checked', false);
        $('tbody tr, .order-card').removeClass('selected');
        updateBulkActions();
    }
    
    // Actions groupées avec refresh de page
    window.bulkReactivate = function() {
        if (selectedOrders.length === 0) {
            showNotification('Aucune commande sélectionnée', 'warning');
            return;
        }
        
        const eligibleOrders = selectedOrders.filter(orderId => {
            const order = orders.find(o => o.id === orderId);
            return order && order.can_reactivate;
        });
        
        if (eligibleOrders.length === 0) {
            showNotification('Aucune des commandes sélectionnées ne peut être réactivée', 'warning');
            return;
        }
        
        $('#bulk-reactivate-count').text(eligibleOrders.length);
        $('#bulk-reactivate-orders').val(eligibleOrders.join(','));
        $('#bulkReactivateModal').modal('show');
    }
    
    window.bulkCancel = function() {
        if (selectedOrders.length === 0) {
            showNotification('Aucune commande sélectionnée', 'warning');
            return;
        }
        
        $('#bulk-cancel-count').text(selectedOrders.length);
        $('#bulk-cancel-orders').val(selectedOrders.join(','));
        $('#bulkCancelModal').modal('show');
    }
    
    // Override des fonctions de soumission pour forcer le refresh
    const originalSubmitBulkReactivate = window.submitBulkReactivate;
    window.submitBulkReactivate = function() {
        const ordersValue = $('#bulk-reactivate-orders').val();
        const notesValue = $('#bulk-reactivate-notes').val().trim();
        
        if (!notesValue) {
            showNotification('Veuillez saisir une raison pour la réactivation groupée', 'error');
            return;
        }
        
        processBulkSuspendedActionWithRefresh('/admin/process/suspended/bulk-reactivate', {
            order_ids: ordersValue.split(','),
            notes: notesValue
        }, '#bulkReactivateModal');
    };
    
    const originalSubmitBulkCancel = window.submitBulkCancel;
    window.submitBulkCancel = function() {
        const ordersValue = $('#bulk-cancel-orders').val();
        const notesValue = $('#bulk-cancel-notes').val().trim();
        
        if (!notesValue) {
            showNotification('Veuillez saisir une raison pour l\'annulation groupée', 'error');
            return;
        }
        
        processBulkSuspendedActionWithRefresh('/admin/process/suspended/bulk-cancel', {
            order_ids: ordersValue.split(','),
            notes: notesValue
        }, '#bulkCancelModal');
    };
    
    function processBulkSuspendedActionWithRefresh(url, data, modalSelector) {
        $(modalSelector).modal('hide');
        
        if ($('#bulkProgressModal').length) {
            showProgressModal(data.order_ids.length);
        }
        
        $.ajax({
            url: url,
            method: 'POST',
            data: data,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        })
        .done(function(response) {
            if ($('#bulkProgressModal').length) {
                updateProgressModal(response);
                
                setTimeout(() => {
                    $('#bulkProgressModal').modal('hide');
                    showNotification(response.message, 'success');
                    
                    // Forcer le refresh de la page après action groupée
                    setTimeout(() => {
                        window.location.reload();
                    }, 1500);
                }, 2000);
            } else {
                showNotification(response.message, 'success');
                
                setTimeout(() => {
                    window.location.reload();
                }, 1500);
            }
        })
        .fail(function(xhr) {
            if ($('#bulkProgressModal').length) {
                $('#bulkProgressModal').modal('hide');
            }
            
            let errorMessage = 'Erreur lors du traitement groupé';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMessage = xhr.responseJSON.message;
            }
            showNotification(errorMessage, 'error');
        });
    }
    
    // =========================
    // ACTIONS INDIVIDUELLES
    // =========================
    
    window.showReactivateModal = function(orderId) {
        const order = orders.find(o => o.id === orderId);
        if (!order) return;
        
        $('#reactivateOrderId').val(orderId);
        $('#reactivate-order-number').text(String(orderId).padStart(6, '0'));
        $('#reactivate-notes').val('');
        
        $('#reactivateModal').modal('show');
    }
    
    window.showCancelModal = function(orderId) {
        $('#cancelOrderId').val(orderId);
        $('#cancel-order-number').text(String(orderId).padStart(6, '0'));
        $('#cancel-notes').val('');
        
        $('#cancelModal').modal('show');
    }
    
    window.showModifySuspensionModal = function(orderId) {
        const order = orders.find(o => o.id === orderId);
        if (!order) return;
        
        $('#modifyOrderId').val(orderId);
        $('#modify-order-number').text(String(orderId).padStart(6, '0'));
        $('#modify-current-reason').text(order.suspension_reason || 'Aucune raison spécifiée');
        $('#modify-new-reason').val(order.suspension_reason || '');
        $('#modify-notes').val('');
        
        $('#modifySuspensionModal').modal('show');
    }
    
    window.editOrder = function(orderId) {
        window.location.href = `/admin/orders/${orderId}/edit`;
    }
    
    // =========================
    // SOUMISSION DES ACTIONS INDIVIDUELLES
    // =========================
    
    window.submitReactivate = function() {
        const orderId = $('#reactivateOrderId').val();
        const notes = $('#reactivate-notes').val().trim();
        
        if (!notes) {
            showNotification('Veuillez saisir une raison pour la réactivation', 'error');
            return;
        }
        
        processSuspendedAction(orderId, 'reactivate', notes, '#reactivateModal');
    }
    
    window.submitCancel = function() {
        const orderId = $('#cancelOrderId').val();
        const notes = $('#cancel-notes').val().trim();
        
        if (!notes) {
            showNotification('Veuillez saisir une raison pour l\'annulation', 'error');
            return;
        }
        
        processSuspendedAction(orderId, 'cancel', notes, '#cancelModal');
    }
    
    window.submitModifySuspension = function() {
        const orderId = $('#modifyOrderId').val();
        const newReason = $('#modify-new-reason').val().trim();
        const notes = $('#modify-notes').val().trim();
        
        if (!newReason || !notes) {
            showNotification('Veuillez remplir tous les champs', 'error');
            return;
        }
        
        processSuspendedAction(orderId, 'edit_suspension', notes, '#modifySuspensionModal', {
            new_suspension_reason: newReason
        });
    }
    
    function processSuspendedAction(orderId, action, notes, modalSelector, extraData = {}) {
        const submitBtn = $(modalSelector + ' .btn-primary, ' + modalSelector + ' .btn-success, ' + modalSelector + ' .btn-danger');
        const originalText = submitBtn.html();
        submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Traitement...');
        
        const data = {
            action: action,
            notes: notes,
            _token: $('meta[name="csrf-token"]').attr('content'),
            ...extraData
        };
        
        $.post(`/admin/process/suspended/action/${orderId}`, data)
        .done(function(response) {
            $(modalSelector).modal('hide');
            showNotification(response.message, 'success');
            
            setTimeout(() => {
                refreshOrders();
            }, 1000);
        })
        .fail(function(xhr) {
            let errorMessage = 'Erreur lors du traitement';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMessage = xhr.responseJSON.message;
            }
            showNotification(errorMessage, 'error');
        })
        .always(function() {
            submitBtn.prop('disabled', false).html(originalText);
        });
    }
    
    // =========================
    // GESTION DES ÉTATS
    // =========================
    
    function showLoading() {
        $('#loading-state').show();
        $('#empty-state').hide();
        $('#table-view, #grid-view').hide();
    }
    
    function showEmpty() {
        $('#loading-state').hide();
        $('#empty-state').show();
        $('#table-view, #grid-view').hide();
    }
    
    function showContent() {
        $('#loading-state').hide();
        $('#empty-state').hide();
        if (currentView === 'list') {
            $('#table-view').show();
            $('#grid-view').hide();
        } else {
            $('#table-view').hide();
            $('#grid-view').show();
        }
    }
    
    function updateOrdersCount(count) {
        $('#orders-count').text(count);
    }
    
    // =========================
    // UTILITAIRES
    // =========================
    
    function formatDate(dateString) {
        if (!dateString) return 'N/A';
        
        const date = new Date(dateString);
        const now = new Date();
        const diff = now - date;
        const days = Math.floor(diff / (1000 * 60 * 60 * 24));
        
        if (days === 0) return 'Aujourd\'hui';
        if (days === 1) return 'Hier';
        if (days < 7) return `Il y a ${days} jour${days > 1 ? 's' : ''}`;
        return date.toLocaleDateString('fr-FR');
    }
    
    function capitalizeFirst(str) {
        if (!str) return '';
        return str.charAt(0).toUpperCase() + str.slice(1);
    }
    
    function debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }
    
    function showNotification(message, type = 'info') {
        const alertClass = {
            'success': 'alert-success',
            'error': 'alert-danger', 
            'warning': 'alert-warning',
            'info': 'alert-info'
        }[type] || 'alert-info';
        
        const alert = $(`
            <div class="alert ${alertClass} alert-dismissible fade show position-fixed" 
                 style="top: 100px; right: 20px; z-index: 9999; min-width: 300px;">
                <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : type === 'warning' ? 'exclamation-triangle' : 'info-circle'} me-2"></i>
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `);
        
        $('body').append(alert);
        setTimeout(() => alert.fadeOut(() => alert.remove()), 5000);
    }
    
    function showProgressModal(totalCount) {
        if ($('#progress-total').length) {
            $('#progress-total').text(totalCount);
            $('#progress-success').text(0);
            $('#progress-errors').text(0);
            $('#progress-bar').css('width', '0%');
            $('#progress-message').text('Traitement des commandes en cours...');
            $('#progress-detail').text('Veuillez patienter pendant le traitement des actions groupées.');
            
            $('#bulkProgressModal').modal('show');
            
            let progress = 0;
            const interval = setInterval(() => {
                progress += Math.random() * 20;
                if (progress > 90) progress = 90;
                
                $('#progress-bar').css('width', progress + '%');
                
                if (progress >= 90) {
                    clearInterval(interval);
                }
            }, 200);
        }
    }
    
    function updateProgressModal(response) {
        if ($('#progress-bar').length) {
            $('#progress-bar').css('width', '100%');
            $('#progress-message').text('Traitement terminé');
            
            if (response.details) {
                $('#progress-success').text(response.details.success_count || 0);
                $('#progress-errors').text(response.details.error_count || 0);
                
                if (response.details.error_count > 0) {
                    $('#progress-detail').text(`${response.details.success_count} réussie(s), ${response.details.error_count} erreur(s)`);
                } else {
                    $('#progress-detail').text('Toutes les commandes ont été traitées avec succès');
                }
            } else {
                $('#progress-detail').text('Traitement terminé avec succès');
            }
        }
    }
    
    // =========================
    // INITIALISATION
    // =========================
    
    initialize();
    
    // Actualisation automatique toutes les 2 minutes
    setInterval(() => {
        if (selectedOrders.length === 0) {
            refreshOrders();
        }
    }, 120000);
});
</script>
@endsection