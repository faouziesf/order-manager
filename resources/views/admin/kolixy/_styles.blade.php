{{-- Shared Kolixy styles --}}
<style>
    :root {
        --kolixy-primary: #7c3aed;
        --kolixy-primary-light: #a78bfa;
        --kolixy-primary-dark: #5b21b6;
        --kolixy-accent: #f59e0b;
        --kolixy-success: #10b981;
        --kolixy-warning: #f59e0b;
        --kolixy-danger: #ef4444;
        --kolixy-info: #06b6d4;
        --kolixy-light: #f8fafc;
        --kolixy-dark: #1f2937;
        --kolixy-border: #e5e7eb;
        --kolixy-shadow: 0 1px 3px rgba(0,0,0,0.1);
        --kolixy-shadow-md: 0 4px 6px rgba(0,0,0,0.1);
        --kolixy-radius: 10px;
    }
    .kolixy-page { background: var(--bg, linear-gradient(135deg, #f5f3ff 0%, #ede9fe 100%)); min-height: calc(100vh - 60px); }
    .kolixy-header {
        background: linear-gradient(135deg, var(--kolixy-primary) 0%, var(--kolixy-primary-light) 100%);
        padding: 1.25rem 1.5rem; color: white; border-radius: var(--kolixy-radius) var(--kolixy-radius) 0 0;
        position: relative; overflow: hidden;
    }
    .kolixy-header::before {
        content: ''; position: absolute; top: -50%; right: -20%; width: 300px; height: 300px;
        background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
        border-radius: 50%;
    }
    .kolixy-header h4 { margin: 0; font-weight: 700; font-size: 1.15rem; position: relative; z-index: 1; }
    .kolixy-header p { margin: 0.25rem 0 0; opacity: 0.85; font-size: 0.85rem; position: relative; z-index: 1; }
    .kolixy-card {
        background: var(--bg-card, white); border-radius: var(--kolixy-radius); box-shadow: var(--kolixy-shadow);
        border: 1px solid var(--border, var(--kolixy-border)); overflow: hidden; margin-bottom: 1rem;
    }
    .kolixy-card-body { padding: 1.25rem; }
    .kolixy-badge {
        display: inline-flex; align-items: center; gap: 0.25rem;
        padding: 0.25rem 0.6rem; border-radius: 6px; font-size: 0.75rem; font-weight: 600;
    }
    .kolixy-badge-purple { background: #ede9fe; color: #7c3aed; }
    .kolixy-badge-green { background: #d1fae5; color: #059669; }
    .kolixy-badge-red { background: #fee2e2; color: #dc2626; }
    .kolixy-badge-yellow { background: #fef3c7; color: #d97706; }
    .kolixy-badge-blue { background: #dbeafe; color: #2563eb; }
    .kolixy-badge-gray { background: #f3f4f6; color: #6b7280; }
    .kolixy-btn {
        display: inline-flex; align-items: center; gap: 0.4rem;
        padding: 0.5rem 1rem; border-radius: 8px; font-size: 0.85rem; font-weight: 600;
        border: none; cursor: pointer; transition: all 0.2s;
    }
    .kolixy-btn-primary { background: var(--kolixy-primary); color: white; }
    .kolixy-btn-primary:hover { background: var(--kolixy-primary-dark); color: white; }
    .kolixy-btn-success { background: var(--kolixy-success); color: white; }
    .kolixy-btn-danger { background: var(--kolixy-danger); color: white; }
    .kolixy-btn-outline {
        background: white; color: var(--kolixy-primary); border: 1.5px solid var(--kolixy-primary);
    }
    .kolixy-btn-outline:hover { background: var(--kolixy-primary); color: white; }
    .kolixy-btn-sm { padding: 0.3rem 0.7rem; font-size: 0.78rem; }

    .kolixy-stat-card {
        background: var(--bg-card, white); border-radius: var(--kolixy-radius); padding: 1rem;
        box-shadow: var(--kolixy-shadow); border: 1px solid var(--border, var(--kolixy-border));
        text-align: center; transition: all 0.2s;
    }
    .kolixy-stat-card:hover { transform: translateY(-2px); box-shadow: var(--kolixy-shadow-md); }
    .kolixy-stat-card .stat-value { font-size: 1.75rem; font-weight: 800; color: var(--text, var(--kolixy-dark)); }
    .kolixy-stat-card .stat-label { font-size: 0.78rem; color: #6b7280; margin-top: 0.25rem; }
    .kolixy-stat-card .stat-icon { font-size: 1.5rem; margin-bottom: 0.5rem; }

    .kolixy-table { width: 100%; border-collapse: collapse; font-size: 0.85rem; }
    .kolixy-table th {
        background: #f9fafb; padding: 0.7rem 0.75rem; font-weight: 600; color: #374151;
        border-bottom: 2px solid var(--kolixy-border); text-align: left; font-size: 0.8rem;
    }
    .kolixy-table td { padding: 0.65rem 0.75rem; border-bottom: 1px solid #f3f4f6; color: #4b5563; vertical-align: middle; }
    .kolixy-table tr:hover td { background: #faf5ff; }

    .kolixy-input {
        width: 100%; padding: 0.6rem 0.75rem; border: 1.5px solid var(--kolixy-border);
        border-radius: 8px; font-size: 0.875rem; transition: border-color 0.2s; outline: none;
    }
    .kolixy-input:focus { border-color: var(--kolixy-primary); box-shadow: 0 0 0 3px rgba(124,58,237,0.1); }

    .kolixy-nav { display: flex; gap: 0; border-bottom: 2px solid var(--kolixy-border); margin-bottom: 1rem; }
    .kolixy-nav a {
        padding: 0.6rem 1rem; font-size: 0.85rem; font-weight: 600; color: #6b7280;
        text-decoration: none; border-bottom: 2px solid transparent; margin-bottom: -2px; transition: all 0.2s;
    }
    .kolixy-nav a.active, .kolixy-nav a:hover {
        color: var(--kolixy-primary); border-bottom-color: var(--kolixy-primary);
    }

    .kolixy-empty { text-align: center; padding: 3rem 1rem; color: #9ca3af; }
    .kolixy-empty i { font-size: 2.5rem; margin-bottom: 0.75rem; display: block; }

    .kolixy-toast {
        position: fixed; top: 1rem; right: 1rem; z-index: 9999;
        padding: 0.75rem 1.25rem; border-radius: 8px; color: white; font-size: 0.85rem;
        font-weight: 500; box-shadow: 0 4px 12px rgba(0,0,0,0.15); display: none;
        animation: slideIn 0.3s ease;
    }
    .kolixy-toast.success { background: var(--kolixy-success); }
    .kolixy-toast.error { background: var(--kolixy-danger); }
    @keyframes slideIn { from { transform: translateX(100%); opacity: 0; } to { transform: translateX(0); opacity: 1; } }

    .kolixy-status-dot { width: 8px; height: 8px; border-radius: 50%; display: inline-block; margin-right: 0.35rem; }
    .kolixy-connected { background: var(--kolixy-success); }
    .kolixy-disconnected { background: var(--kolixy-danger); }

    @media (max-width: 768px) {
        .kolixy-stat-card .stat-value { font-size: 1.25rem; }
        .kolixy-table { font-size: 0.78rem; }
        .kolixy-table th, .kolixy-table td { padding: 0.5rem; }
    }

    /* ============= DARK MODE OVERRIDES ============= */
    html[data-theme="dark"] .kolixy-stat-card .stat-label { color: var(--text-muted); }
    html[data-theme="dark"] .kolixy-table th { background: var(--bg-muted); color: var(--text-secondary); border-color: var(--border); }
    html[data-theme="dark"] .kolixy-table td { border-color: var(--border); color: var(--text-secondary); }
    html[data-theme="dark"] .kolixy-table tr:hover td { background: var(--bg-card-hover); }
    html[data-theme="dark"] .kolixy-nav a { color: var(--text-muted); }
    html[data-theme="dark"] .kolixy-empty { color: var(--text-muted); }
</style>
