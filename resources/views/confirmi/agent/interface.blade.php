@extends('confirmi.layouts.app')

@section('title', 'Interface Emballage')
@section('page-title', 'Emballage & Expédition')

@section('css')
<style>
    .emb-tabs { display:flex; gap:4px; background:var(--bg-card); border-radius:12px; padding:4px; border:1px solid var(--border); margin-bottom:20px; overflow-x:auto; }
    .emb-tab { flex:1; padding:10px 16px; border-radius:8px; text-align:center; cursor:pointer; font-weight:600; font-size:13px; color:var(--text-secondary); transition:all 0.2s; white-space:nowrap; border:none; background:transparent; }
    .emb-tab.active { background:var(--royal); color:white; box-shadow:0 2px 8px rgba(37,99,235,0.3); }
    .emb-tab .tab-count { display:inline-block; min-width:20px; height:20px; line-height:20px; border-radius:10px; font-size:11px; margin-left:6px; padding:0 6px; }
    .emb-tab.active .tab-count { background:rgba(255,255,255,0.3); color:white; }
    .emb-tab:not(.active) .tab-count { background:var(--border); color:var(--text-secondary); }

    .task-list { min-height:200px; }
    .task-item { background:var(--bg-card); border:1px solid var(--border); border-radius:12px; padding:16px; margin-bottom:12px; transition:all 0.2s; }
    .task-item:hover { border-color:var(--royal); box-shadow:var(--shadow-md); }
    .task-header { display:flex; justify-content:space-between; align-items:start; margin-bottom:10px; }
    .task-customer { font-weight:700; font-size:15px; color:var(--text); }
    .task-admin-badge { font-size:11px; padding:2px 8px; border-radius:6px; background:var(--royal-50); color:var(--royal); font-weight:600; }
    .task-meta { display:flex; gap:16px; color:var(--text-secondary); font-size:13px; margin-bottom:10px; flex-wrap:wrap; }
    .task-meta i { width:16px; text-align:center; }
    .task-items { background:var(--bg); border-radius:8px; padding:10px 12px; margin-bottom:12px; }
    .task-items-row { display:flex; justify-content:space-between; padding:4px 0; font-size:13px; border-bottom:1px solid var(--border); }
    .task-items-row:last-child { border-bottom:none; }
    .task-actions { display:flex; gap:8px; flex-wrap:wrap; }
    .task-btn { padding:8px 16px; border-radius:8px; border:none; font-weight:600; font-size:13px; cursor:pointer; display:inline-flex; align-items:center; gap:6px; transition:all 0.2s; }
    .task-btn:disabled { opacity:0.5; cursor:not-allowed; }
    .task-btn-receive { background:var(--warning); color:white; }
    .task-btn-receive:hover:not(:disabled) { filter:brightness(0.9); }
    .task-btn-pack { background:var(--accent-light); color:white; }
    .task-btn-pack:hover:not(:disabled) { filter:brightness(0.9); }
    .task-btn-bl { background:var(--success); color:white; }
    .task-btn-bl:hover:not(:disabled) { filter:brightness(0.9); }
    .task-btn-print { background:var(--bg); color:var(--text); border:1px solid var(--border); }
    .task-btn-print:hover { background:var(--bg-hover); }
    .bulk-bar { background:var(--bg-card); border:1px solid var(--border); border-radius:12px; padding:12px 16px; margin-bottom:16px; display:none; align-items:center; gap:12px; }
    .bulk-bar.show { display:flex; }
    .empty-state { text-align:center; padding:60px 20px; color:var(--text-secondary); }
    .empty-state i { font-size:48px; opacity:0.3; margin-bottom:12px; }
    .loading-spinner { display:flex; justify-content:center; padding:40px; }
    .loading-spinner::after { content:''; width:32px; height:32px; border:3px solid var(--border); border-top-color:var(--royal); border-radius:50%; animation:spin 0.8s linear infinite; }
    @keyframes spin { to { transform:rotate(360deg); } }
</style>
@endsection

@section('content')
<meta name="csrf-token" content="{{ csrf_token() }}">

{{-- TABS --}}
<div class="emb-tabs" id="embTabs">
    <button class="emb-tab active" data-tab="pending" onclick="switchTab('pending')">
        <i class="fas fa-clock"></i> En attente <span class="tab-count" id="count-pending">0</span>
    </button>
    <button class="emb-tab" data-tab="received" onclick="switchTab('received')">
        <i class="fas fa-box"></i> Reçus <span class="tab-count" id="count-received">0</span>
    </button>
    <button class="emb-tab" data-tab="packed" onclick="switchTab('packed')">
        <i class="fas fa-tape"></i> Emballés <span class="tab-count" id="count-packed">0</span>
    </button>
    <button class="emb-tab" data-tab="shipped" onclick="switchTab('shipped')">
        <i class="fas fa-truck"></i> Expédiés <span class="tab-count" id="count-shipped">0</span>
    </button>
</div>

{{-- BULK BAR (for pending tab) --}}
<div class="bulk-bar" id="bulkBar">
    <input type="checkbox" id="selectAll" onchange="toggleSelectAll()" style="width:18px; height:18px; accent-color:var(--royal);">
    <span style="font-size:13px; color:var(--text-secondary);"><strong id="selectedCount">0</strong> sélectionné(s)</span>
    <button class="task-btn task-btn-receive" onclick="bulkReceive()" id="bulkReceiveBtn">
        <i class="fas fa-check-double"></i> Tout marquer reçu
    </button>
</div>

{{-- TASK LIST --}}
<div class="task-list" id="taskList">
    <div class="loading-spinner"></div>
</div>

@endsection

@section('scripts')
<script>
(function() {
    const API = {
        tasks: (tab) => `/confirmi/agent/emballage/tasks/${tab}`,
        counts: '/confirmi/agent/emballage/counts',
        receive: (id) => `/confirmi/agent/emballage/${id}/receive`,
        pack: (id) => `/confirmi/agent/emballage/${id}/pack`,
        createBL: (id) => `/confirmi/agent/emballage/${id}/create-bl`,
        printBL: (id) => `/confirmi/agent/emballage/${id}/print-bl`,
        bulkReceive: '/confirmi/agent/emballage/bulk-receive'
    };

    let currentTab = 'pending';
    let selectedTasks = new Set();
    const csrf = document.querySelector('meta[name="csrf-token"]').content;

    window.switchTab = function(tab) {
        currentTab = tab;
        selectedTasks.clear();
        document.querySelectorAll('.emb-tab').forEach(t => t.classList.toggle('active', t.dataset.tab === tab));
        document.getElementById('bulkBar').classList.toggle('show', tab === 'pending');
        updateSelectedCount();
        loadTasks(tab);
    };

    function loadTasks(tab) {
        const list = document.getElementById('taskList');
        list.innerHTML = '<div class="loading-spinner"></div>';

        fetch(API.tasks(tab), { headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf }})
            .then(r => r.json())
            .then(data => {
                if (!data.tasks || data.tasks.length === 0) {
                    list.innerHTML = `<div class="empty-state"><i class="fas fa-inbox"></i><p>${getEmptyMessage(tab)}</p></div>`;
                    return;
                }
                list.innerHTML = data.tasks.map(task => renderTask(task, tab)).join('');
            })
            .catch(() => {
                list.innerHTML = '<div class="empty-state"><i class="fas fa-exclamation-triangle"></i><p>Erreur de chargement</p></div>';
            });
    }

    function getEmptyMessage(tab) {
        const msgs = { pending: 'Aucun colis en attente de réception', received: 'Aucun colis reçu à emballer', packed: 'Aucun colis emballé en attente d\'expédition', shipped: 'Aucun colis expédié' };
        return msgs[tab] || 'Aucune tâche';
    }

    function renderTask(task, tab) {
        let items = '';
        if (task.items && task.items.length) {
            items = '<div class="task-items">' + task.items.map(i =>
                `<div class="task-items-row"><span>${i.product_name}${i.variant ? ' - ' + i.variant : ''}</span><span>${i.quantity}x ${parseFloat(i.price).toFixed(3)} DT</span></div>`
            ).join('') + '</div>';
        }

        let actions = '';
        if (tab === 'pending') {
            actions = `<button class="task-btn task-btn-receive" onclick="markReceived(${task.id})"><i class="fas fa-download"></i> Marquer reçu</button>`;
        } else if (tab === 'received') {
            actions = `<button class="task-btn task-btn-pack" onclick="markPacked(${task.id})"><i class="fas fa-tape"></i> Emballer</button>`;
        } else if (tab === 'packed') {
            actions = `<button class="task-btn task-btn-bl" onclick="createBL(${task.id})" id="bl-btn-${task.id}"><i class="fas fa-file-invoice"></i> Créer BL</button>`;
            if (task.tracking_number) {
                actions += ` <a href="${API.printBL(task.id)}" target="_blank" class="task-btn task-btn-print"><i class="fas fa-print"></i> Imprimer</a>`;
            }
        } else if (tab === 'shipped') {
            if (task.tracking_number) {
                actions = `<span style="font-size:12px; color:var(--text-secondary); margin-right:8px;"><i class="fas fa-barcode"></i> ${task.tracking_number}</span>`;
                actions += `<a href="${API.printBL(task.id)}" target="_blank" class="task-btn task-btn-print"><i class="fas fa-print"></i> Imprimer BL</a>`;
            }
        }

        const checkbox = tab === 'pending'
            ? `<input type="checkbox" class="task-check" data-id="${task.id}" onchange="updateSelection()" style="width:18px; height:18px; accent-color:var(--royal); margin-right:8px;">`
            : '';

        return `<div class="task-item" id="task-${task.id}">
            <div class="task-header">
                <div style="display:flex; align-items:center;">
                    ${checkbox}
                    <span class="task-customer">${task.customer_name}</span>
                    <span class="task-admin-badge" style="margin-left:8px;">${task.admin_name}</span>
                </div>
                <span style="font-weight:700; color:var(--royal);">${parseFloat(task.total_price).toFixed(3)} DT</span>
            </div>
            <div class="task-meta">
                <span><i class="fas fa-hashtag"></i> #${task.order_id}</span>
                <span><i class="fas fa-phone"></i> ${task.customer_phone || '-'}</span>
                <span><i class="fas fa-map-marker-alt"></i> ${task.region || ''} ${task.city ? '- ' + task.city : ''}</span>
                <span><i class="fas fa-calendar"></i> ${task.created_at}</span>
            </div>
            ${items}
            <div class="task-actions">${actions}</div>
        </div>`;
    }

    function loadCounts() {
        fetch(API.counts, { headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf }})
            .then(r => r.json())
            .then(data => {
                ['pending', 'received', 'packed', 'shipped'].forEach(tab => {
                    const el = document.getElementById('count-' + tab);
                    if (el) el.textContent = data[tab] || 0;
                });
            });
    }

    function apiAction(url, method, body, taskId, successMsg) {
        const btn = taskId ? document.querySelector(`#task-${taskId} .task-btn`) : null;
        if (btn) { btn.disabled = true; btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> ...'; }

        fetch(url, {
            method: method,
            headers: { 'Accept': 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
            body: body ? JSON.stringify(body) : undefined
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                if (taskId) {
                    const el = document.getElementById('task-' + taskId);
                    if (el) { el.style.opacity = '0.3'; setTimeout(() => el.remove(), 300); }
                }
                loadCounts();
                if (successMsg) {
                    alert(successMsg + (data.tracking_number ? '\nN° suivi: ' + data.tracking_number : ''));
                }
            } else {
                alert(data.error || 'Erreur');
                if (btn) { btn.disabled = false; btn.innerHTML = btn.getAttribute('data-original') || 'Réessayer'; }
            }
        })
        .catch(() => {
            alert('Erreur réseau');
            if (btn) btn.disabled = false;
        });
    }

    window.markReceived = function(id) { apiAction(API.receive(id), 'POST', null, id, 'Marqué comme reçu'); };
    window.markPacked = function(id) { apiAction(API.pack(id), 'POST', null, id, 'Emballé avec succès'); };

    window.createBL = function(id) {
        const btn = document.getElementById('bl-btn-' + id);
        if (btn) { btn.disabled = true; btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Création...'; }

        fetch(API.createBL(id), {
            method: 'POST',
            headers: { 'Accept': 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf }
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                loadCounts();
                // Replace the task actions with print button
                const taskEl = document.getElementById('task-' + id);
                if (taskEl) {
                    const actionsDiv = taskEl.querySelector('.task-actions');
                    if (actionsDiv) {
                        actionsDiv.innerHTML = `<span style="font-size:12px; color:#10b981; margin-right:8px;"><i class="fas fa-check-circle"></i> BL créé - N° ${data.tracking_number || ''}</span>` +
                            `<a href="${API.printBL(id)}" target="_blank" class="task-btn task-btn-print"><i class="fas fa-print"></i> Imprimer BL</a>`;
                    }
                    taskEl.style.borderColor = '#10b981';
                }
            } else {
                alert(data.error || 'Erreur création BL');
                if (btn) { btn.disabled = false; btn.innerHTML = '<i class="fas fa-file-invoice"></i> Créer BL'; }
            }
        })
        .catch(() => {
            alert('Erreur réseau');
            if (btn) { btn.disabled = false; btn.innerHTML = '<i class="fas fa-file-invoice"></i> Créer BL'; }
        });
    };

    window.toggleSelectAll = function() {
        const checked = document.getElementById('selectAll').checked;
        document.querySelectorAll('.task-check').forEach(cb => { cb.checked = checked; });
        updateSelection();
    };

    window.updateSelection = function() {
        selectedTasks.clear();
        document.querySelectorAll('.task-check:checked').forEach(cb => selectedTasks.add(parseInt(cb.dataset.id)));
        updateSelectedCount();
    };

    function updateSelectedCount() {
        const el = document.getElementById('selectedCount');
        if (el) el.textContent = selectedTasks.size;
        const btn = document.getElementById('bulkReceiveBtn');
        if (btn) btn.disabled = selectedTasks.size === 0;
    }

    window.bulkReceive = function() {
        if (selectedTasks.size === 0) return;
        apiAction(API.bulkReceive, 'POST', { task_ids: Array.from(selectedTasks) }, null, 'Tâches marquées comme reçues');
        setTimeout(() => { loadTasks(currentTab); loadCounts(); selectedTasks.clear(); updateSelectedCount(); }, 500);
    };

    // Init
    loadTasks('pending');
    loadCounts();
    setInterval(loadCounts, 30000);
})();
</script>
@endsection
