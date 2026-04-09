{{-- Shared styles for integration views (WooCommerce, Shopify, PrestaShop) --}}
@include('admin.partials._shared-styles')
<style>
    .integration-stats {
        background: linear-gradient(135deg, var(--intg-color-1, #4f46e5) 0%, var(--intg-color-2, #7c3aed) 100%);
        border-radius: 20px;
        padding: 28px;
        color: white;
        margin-bottom: 24px;
        position: relative;
        overflow: hidden;
    }
    .integration-stats::before {
        content: '';
        position: absolute;
        top: -40%; right: -20%;
        width: 300px; height: 300px;
        background: radial-gradient(circle, rgba(255,255,255,0.08) 0%, transparent 70%);
        border-radius: 50%;
    }
    .intg-stat-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 16px;
    }
    .intg-stat-box {
        background: rgba(255,255,255,0.12);
        backdrop-filter: blur(10px);
        border-radius: 14px;
        padding: 20px;
        text-align: center;
        transition: all 0.3s ease;
    }
    .intg-stat-box:hover {
        background: rgba(255,255,255,0.2);
        transform: translateY(-2px);
    }
    .intg-stat-num {
        font-size: 32px;
        font-weight: 800;
        line-height: 1;
        margin-bottom: 4px;
    }
    .intg-stat-lbl {
        font-size: 13px;
        opacity: 0.85;
        font-weight: 500;
    }
    .intg-badges {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
        margin-top: 16px;
    }
    .intg-badge {
        background: rgba(255,255,255,0.15);
        padding: 6px 14px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
    }

    /* Integration cards */
    .intg-card {
        background: var(--card-bg, white);
        border-radius: 16px;
        border: 2px solid var(--om-border);
        padding: 20px 24px;
        margin-bottom: 12px;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        flex-wrap: wrap;
    }
    .intg-card.active { border-color: #10b981; }
    .intg-card.inactive { opacity: 0.65; }
    .intg-card:hover { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(0,0,0,0.08); }
    .intg-card-info h6 { font-weight: 700; margin: 0 0 2px; color: var(--om-text); }
    .intg-card-url { color: var(--om-text-light); font-size: 13px; }
    .intg-card-meta { font-size: 12px; color: var(--om-text-light); margin-top: 4px; }
    .intg-card-actions { display: flex; align-items: center; gap: 12px; }

    /* Status dot */
    .status-dot {
        width: 12px; height: 12px;
        border-radius: 50%;
        display: inline-block;
        margin-right: 8px;
        flex-shrink: 0;
    }
    .status-dot.active { background: #10b981; box-shadow: 0 0 8px rgba(16,185,129,0.4); animation: pulse-dot 2s infinite; }
    .status-dot.inactive { background: #ef4444; }
    .status-dot.syncing { background: #f59e0b; animation: pulse-dot 1.5s infinite; }
    .status-dot.error { background: #ef4444; animation: pulse-dot 1s infinite; }
    @keyframes pulse-dot {
        0%, 100% { opacity: 1; transform: scale(1); }
        50% { opacity: 0.6; transform: scale(1.2); }
    }

    /* Toggle switch */
    .toggle-sw {
        position: relative;
        width: 46px; height: 24px;
        display: inline-block;
    }
    .toggle-sw input { opacity: 0; width: 0; height: 0; }
    .toggle-sw .slider {
        position: absolute;
        cursor: pointer;
        top: 0; left: 0; right: 0; bottom: 0;
        background: #cbd5e1;
        border-radius: 24px;
        transition: 0.3s;
    }
    .toggle-sw .slider::before {
        content: '';
        position: absolute;
        width: 18px; height: 18px;
        left: 3px; bottom: 3px;
        background: white;
        border-radius: 50%;
        transition: 0.3s;
    }
    .toggle-sw input:checked + .slider { background: #10b981; }
    .toggle-sw input:checked + .slider::before { transform: translateX(22px); }

    /* Connection test result */
    .conn-result {
        margin: 16px 0;
        padding: 14px 18px;
        border-radius: 12px;
        border-left: 4px solid;
        display: none;
        animation: slideIn 0.3s ease;
    }
    .conn-result.success { background: #ecfdf5; border-left-color: #10b981; color: #065f46; }
    .conn-result.error { background: #fef2f2; border-left-color: #ef4444; color: #991b1b; }
    @keyframes slideIn { from { opacity: 0; transform: translateY(-8px); } to { opacity: 1; transform: translateY(0); } }

    /* Help box */
    .help-box {
        background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
        border: 1px solid #93c5fd;
        border-radius: 14px;
        padding: 20px;
        margin: 20px 0;
        color: #1e40af;
    }
    .help-box h6 { color: #1d4ed8; font-weight: 700; margin-bottom: 12px; }
    .help-box ol { margin: 0; padding-left: 20px; }
    .help-box li { margin-bottom: 6px; line-height: 1.5; font-size: 13px; }

    /* Info box */
    .info-box {
        background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
        border: 1px solid #7dd3fc;
        border-radius: 14px;
        padding: 20px;
        color: #0c4a6e;
    }
    .info-box h6 { color: #0369a1; font-weight: 700; margin-bottom: 12px; }
    .info-box ul { margin: 0; padding-left: 20px; }
    .info-box li { margin-bottom: 6px; font-size: 13px; }

    /* Modal overrides */
    .modal-content { border-radius: 20px; border: none; box-shadow: 0 25px 50px rgba(0,0,0,0.15); background: var(--card-bg, white); }
    .modal-header.intg-modal-header {
        background: linear-gradient(135deg, var(--intg-color-1, #4f46e5) 0%, var(--intg-color-2, #7c3aed) 100%);
        color: white;
        border-radius: 20px 20px 0 0;
        border-bottom: none;
        padding: 20px 24px;
    }
    .modal-header.intg-modal-header .btn-close { filter: invert(1); opacity: 0.8; }
    .modal-body { padding: 24px; }
    .modal-footer { padding: 16px 24px; border-top: 1px solid var(--om-border); }

    .modern-check {
        display: flex; align-items: center; gap: 12px;
        padding: 14px 18px;
        background: var(--bg-muted, #f8fafc);
        border-radius: 12px;
        border: 2px solid transparent;
        cursor: pointer;
        transition: all 0.2s;
    }
    .modern-check:hover { background: #eef2ff; border-color: rgba(79,70,229,0.2); }
    .modern-check input[type="checkbox"] { width: 18px; height: 18px; accent-color: var(--om-primary); }

    @media (max-width: 768px) {
        .intg-stat-grid { grid-template-columns: 1fr; }
        .intg-card { flex-direction: column; align-items: flex-start; }
    }

    /* ============= DARK MODE OVERRIDES ============= */
    html[data-theme="dark"] .modern-check:hover { background: var(--bg-card-hover); border-color: var(--border); }
</style>
