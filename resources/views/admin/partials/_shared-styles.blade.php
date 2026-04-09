<style>
/* ============= SHARED DESIGN TOKENS (theme-aware) ============= */
:root {
    --om-primary: var(--primary, #6366f1);
    --om-primary-light: var(--primary-50, #eef2ff);
    --om-primary-dark: var(--primary-dark, #4f46e5);
    --om-success: var(--success, #10b981);
    --om-success-light: var(--success-light, #ecfdf5);
    --om-danger: var(--danger, #ef4444);
    --om-danger-light: var(--danger-light, #fef2f2);
    --om-warning: var(--warning, #f59e0b);
    --om-warning-light: var(--warning-light, #fffbeb);
    --om-info: var(--info, #3b82f6);
    --om-info-light: var(--info-light, #eff6ff);
    --om-text: var(--text, #1e293b);
    --om-text-light: var(--text-secondary, #64748b);
    --om-bg: var(--bg, #f1f5f9);
    --om-bg-card: var(--bg-card, #ffffff);
    --om-bg-hover: var(--bg-card-hover, #f8fafc);
    --om-bg-muted: var(--bg-muted, #f1f5f9);
    --om-border: var(--border, #e2e8f0);
    --om-border-light: var(--border-light, #f1f5f9);
    --om-radius: var(--radius, 14px);
    --om-radius-sm: var(--radius-sm, 10px);
    --om-radius-lg: var(--radius-lg, 18px);
    --om-shadow: var(--shadow, 0 1px 3px rgba(0,0,0,0.06), 0 1px 2px rgba(0,0,0,0.04));
    --om-shadow-md: var(--shadow-md, 0 4px 6px -1px rgba(0,0,0,0.07), 0 2px 4px -1px rgba(0,0,0,0.04));
    --om-shadow-lg: var(--shadow-lg, 0 10px 15px -3px rgba(0,0,0,0.08), 0 4px 6px -2px rgba(0,0,0,0.04));
    --om-transition: all 0.2s ease;
}

/* ============= CARDS ============= */
.om-card {
    background: var(--om-bg-card);
    border-radius: var(--om-radius);
    border: 1px solid var(--om-border);
    box-shadow: var(--om-shadow);
    transition: box-shadow 0.25s ease, transform 0.25s ease;
    overflow: hidden;
}
.om-card:hover {
    box-shadow: var(--om-shadow-md);
}
.om-card-header {
    padding: 1.25rem 1.5rem;
    border-bottom: 1px solid var(--om-border);
    background: var(--om-bg-muted);
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.75rem;
}
.om-card-header h5,
.om-card-header h6 {
    margin: 0;
    font-weight: 700;
    font-size: 0.95rem;
    color: var(--om-text);
}
.om-card-body { padding: 1.5rem; }
.om-card-footer {
    padding: 1rem 1.5rem;
    border-top: 1px solid var(--om-border);
    background: var(--om-bg-muted);
}

/* ============= STAT CARDS ============= */
.om-stat {
    background: var(--om-bg-card);
    border-radius: var(--om-radius);
    padding: 1.35rem;
    border: 1px solid var(--om-border);
    box-shadow: var(--om-shadow);
    transition: transform 0.25s ease, box-shadow 0.25s ease;
    position: relative;
    overflow: hidden;
}
.om-stat::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 3px;
    background: var(--stat-color, var(--om-primary));
    border-radius: 3px 3px 0 0;
}
.om-stat:hover {
    transform: translateY(-3px);
    box-shadow: var(--om-shadow-lg);
}
.om-stat-icon {
    width: 46px;
    height: 46px;
    border-radius: var(--om-radius-sm);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.15rem;
    color: var(--stat-color, var(--om-primary));
    background: var(--stat-bg, var(--om-primary-light));
    flex-shrink: 0;
}
.om-stat-value {
    font-size: 1.65rem;
    font-weight: 800;
    color: var(--om-text);
    line-height: 1;
    letter-spacing: -0.02em;
}
.om-stat-label {
    font-size: 0.72rem;
    color: var(--om-text-light);
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-top: 0.2rem;
}

/* ============= TABLE STYLES ============= */
.om-table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
}
.om-table thead th {
    background: var(--om-bg-muted);
    color: var(--om-text-light);
    font-weight: 600;
    font-size: 0.75rem;
    text-transform: uppercase;
    letter-spacing: 0.6px;
    padding: 0.8rem 1rem;
    border-bottom: 2px solid var(--om-border);
    white-space: nowrap;
}
.om-table tbody td {
    padding: 0.85rem 1rem;
    border-bottom: 1px solid var(--om-border-light);
    vertical-align: middle;
    color: var(--om-text);
    font-size: 0.875rem;
}
.om-table tbody tr {
    transition: background 0.15s ease;
}
.om-table tbody tr:hover {
    background: var(--om-bg-hover);
}
.om-table tbody tr:last-child td {
    border-bottom: none;
}

/* ============= AVATARS ============= */
.om-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 0.875rem;
    color: white;
    flex-shrink: 0;
}
.om-avatar-sm { width: 32px; height: 32px; font-size: 0.75rem; }
.om-avatar-lg { width: 64px; height: 64px; font-size: 1.5rem; }
.om-avatar-xl { width: 80px; height: 80px; font-size: 2rem; }

/* ============= BADGES ============= */
.om-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    padding: 0.3rem 0.7rem;
    border-radius: 20px;
    font-size: 0.72rem;
    font-weight: 600;
    line-height: 1.2;
    white-space: nowrap;
}
.om-badge-success { background: var(--om-success-light); color: var(--om-success); }
.om-badge-danger { background: var(--om-danger-light); color: var(--om-danger); }
.om-badge-warning { background: var(--om-warning-light); color: var(--om-warning); }
.om-badge-info { background: var(--om-info-light); color: var(--om-info); }
.om-badge-primary { background: var(--om-primary-light); color: var(--om-primary); }
.om-badge i { font-size: 0.55rem; }

/* ============= BUTTONS ============= */
.om-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.6rem 1.2rem;
    border-radius: var(--om-radius-sm);
    font-weight: 600;
    font-size: 0.85rem;
    border: none;
    cursor: pointer;
    transition: transform 0.15s ease, box-shadow 0.2s ease, background 0.2s ease;
    text-decoration: none;
    line-height: 1.5;
}
.om-btn:hover { transform: translateY(-1px); box-shadow: var(--om-shadow-md); }
.om-btn:active { transform: translateY(0); }
.om-btn-primary { background: var(--om-primary); color: white; }
.om-btn-primary:hover { background: var(--om-primary-dark); color: white; }
.om-btn-success { background: var(--om-success); color: white; }
.om-btn-success:hover { background: #059669; color: white; }
.om-btn-danger { background: var(--om-danger); color: white; }
.om-btn-danger:hover { background: #dc2626; color: white; }
.om-btn-ghost {
    background: transparent;
    color: var(--om-text);
    border: 1px solid var(--om-border);
}
.om-btn-ghost:hover { background: var(--om-bg-muted); }
.om-btn-sm { padding: 0.35rem 0.7rem; font-size: 0.78rem; }
.om-btn-icon {
    width: 36px;
    height: 36px;
    padding: 0;
    justify-content: center;
    border-radius: var(--om-radius-sm);
}

/* ============= FORMS ============= */
.om-form-group { margin-bottom: 1.25rem; }
.om-form-label {
    display: block;
    margin-bottom: 0.45rem;
    font-weight: 600;
    font-size: 0.85rem;
    color: var(--om-text);
}
.om-form-input {
    width: 100%;
    padding: 0.6rem 0.875rem;
    border: 1px solid var(--om-border);
    border-radius: var(--om-radius-sm);
    font-size: 0.875rem;
    transition: border-color 0.2s ease, box-shadow 0.2s ease;
    background: var(--om-bg-card);
    color: var(--om-text);
    font-family: inherit;
}
.om-form-input:focus {
    outline: none;
    border-color: var(--om-primary);
    box-shadow: 0 0 0 3px var(--om-primary-light);
}
.om-form-input::placeholder { color: var(--om-text-light); opacity: 0.7; }
.om-form-section {
    margin-bottom: 2rem;
    padding-bottom: 1.5rem;
    border-bottom: 1px solid var(--om-border);
}
.om-form-section:last-child { border-bottom: none; }
.om-form-section-title {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 1rem;
    font-weight: 700;
    color: var(--om-text);
    margin-bottom: 1.25rem;
}
.om-form-section-title i { color: var(--om-primary); font-size: 0.9rem; }

/* ============= SEARCH & FILTER BAR ============= */
.om-search-bar {
    position: relative;
}
.om-search-bar input {
    width: 100%;
    padding: 0.6rem 0.875rem 0.6rem 2.5rem;
    border: 1px solid var(--om-border);
    border-radius: var(--om-radius-sm);
    font-size: 0.85rem;
    background: var(--om-bg-card);
    color: var(--om-text);
    transition: border-color 0.2s, box-shadow 0.2s;
    font-family: inherit;
}
.om-search-bar input:focus {
    outline: none;
    border-color: var(--om-primary);
    box-shadow: 0 0 0 3px var(--om-primary-light);
}
.om-search-bar i {
    position: absolute;
    left: 0.85rem;
    top: 50%;
    transform: translateY(-50%);
    color: var(--om-text-light);
    font-size: 0.85rem;
}
.om-filter-pills {
    display: flex;
    gap: 0.4rem;
    flex-wrap: wrap;
}
.om-filter-pill {
    padding: 0.35rem 0.85rem;
    border-radius: 20px;
    font-size: 0.78rem;
    font-weight: 600;
    border: 1px solid var(--om-border);
    background: var(--om-bg-card);
    color: var(--om-text-light);
    cursor: pointer;
    transition: all 0.2s;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
}
.om-filter-pill:hover { border-color: var(--om-primary); color: var(--om-primary); }
.om-filter-pill.active {
    background: var(--om-primary);
    color: white;
    border-color: var(--om-primary);
}
.om-filter-pill .om-count {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 18px;
    height: 18px;
    border-radius: 9px;
    font-size: 0.65rem;
    font-weight: 700;
    background: rgba(0,0,0,0.1);
    padding: 0 4px;
}
.om-filter-pill.active .om-count { background: rgba(255,255,255,0.25); }

/* ============= EMPTY STATE ============= */
.om-empty {
    text-align: center;
    padding: 3rem 2rem;
}
.om-empty-icon {
    width: 80px;
    height: 80px;
    border-radius: var(--om-radius-lg);
    background: var(--om-bg-muted);
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 1.25rem;
    font-size: 2rem;
    color: var(--om-text-light);
    opacity: 0.6;
}
.om-empty h5 { color: var(--om-text); margin-bottom: 0.5rem; font-weight: 700; }
.om-empty p { color: var(--om-text-light); margin-bottom: 1.5rem; max-width: 400px; margin-left: auto; margin-right: auto; }

/* ============= PAGE HEADER ============= */
.om-page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.75rem;
    flex-wrap: wrap;
    gap: 1rem;
}
.om-page-title {
    font-size: 1.5rem;
    font-weight: 800;
    color: var(--om-text);
    margin: 0;
    letter-spacing: -0.02em;
}
.om-page-subtitle {
    color: var(--om-text-light);
    font-size: 0.875rem;
    margin: 0.25rem 0 0;
}

/* ============= TABS ============= */
.om-tabs {
    display: flex;
    gap: 0.25rem;
    border-bottom: 2px solid var(--om-border);
    margin-bottom: 1.5rem;
}
.om-tab {
    padding: 0.65rem 1.15rem;
    font-size: 0.85rem;
    font-weight: 600;
    color: var(--om-text-light);
    text-decoration: none;
    border-bottom: 2px solid transparent;
    margin-bottom: -2px;
    transition: color 0.2s, border-color 0.2s;
    cursor: pointer;
    background: none;
    border-top: none;
    border-left: none;
    border-right: none;
}
.om-tab:hover { color: var(--om-primary); }
.om-tab.active {
    color: var(--om-primary);
    border-bottom-color: var(--om-primary);
}

/* ============= ANIMATIONS ============= */
@keyframes fadeInUp {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}
.om-animate { animation: fadeInUp 0.3s ease forwards; }
.om-animate-delay-1 { animation-delay: 0.05s; }
.om-animate-delay-2 { animation-delay: 0.1s; }
.om-animate-delay-3 { animation-delay: 0.15s; }
.om-animate-delay-4 { animation-delay: 0.2s; }

/* ============= RESPONSIVE ============= */
@media (max-width: 768px) {
    .om-page-header { flex-direction: column; align-items: flex-start; }
    .om-stat { padding: 1rem; }
    .om-stat-value { font-size: 1.4rem; }
    .om-card-body { padding: 1rem; }
    .om-table thead th, .om-table tbody td { padding: 0.625rem 0.5rem; font-size: 0.82rem; }
    .om-btn { padding: 0.5rem 1rem; font-size: 0.8rem; }
    .om-page-title { font-size: 1.25rem; }
    .om-tabs { overflow-x: auto; }
    .om-tab { white-space: nowrap; }
}
@media (max-width: 480px) {
    .om-stat-icon { width: 38px; height: 38px; font-size: 1rem; }
    .om-avatar { width: 36px; height: 36px; font-size: 0.8rem; }
}
</style>
