@extends('confirmi.layouts.app')
@section('title', 'Produits')
@section('page-title', 'Catalogue produits')

@section('css')
<style>
.prod-banner {
    background: linear-gradient(135deg, #10b981, #059669);
    border-radius: var(--radius-lg); padding: 1.25rem 1.5rem;
    color: white; position: relative; overflow: hidden; margin-bottom: 1.25rem;
}
.prod-banner::before {
    content:''; position:absolute; top:-40%; right:-8%;
    width:250px; height:250px; background:rgba(255,255,255,.06);
    border-radius:50%;
}
.prod-banner h2 { font-weight:800; font-size:1.2rem; margin:0; position:relative; z-index:1; }
.prod-banner p { opacity:.8; font-size:.82rem; margin:.25rem 0 0; position:relative; z-index:1; }

.prod-filters {
    display: flex; gap: .65rem; flex-wrap: wrap; align-items: end;
}

.prod-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
    gap: .75rem;
}

.prod-card {
    background: var(--bg-card);
    border: 1px solid var(--border);
    border-radius: var(--radius-lg, 12px);
    overflow: hidden;
    transition: all .15s;
    display: flex; flex-direction: column;
}
.prod-card:hover { border-color: var(--accent); box-shadow: var(--shadow); transform: translateY(-2px); }

.prod-img {
    width: 100%; height: 140px; object-fit: cover;
    background: var(--bg-hover); display: flex;
    align-items: center; justify-content: center;
    color: var(--text-muted); font-size: 2rem;
}
.prod-img img { width:100%; height:100%; object-fit:cover; }

.prod-body { padding: .85rem; flex: 1; display: flex; flex-direction: column; }
.prod-name { font-weight: 700; font-size: .88rem; color: var(--text); margin-bottom: .25rem; }
.prod-ref { font-size: .72rem; color: var(--text-muted); margin-bottom: .35rem; }
.prod-price { font-weight: 800; font-size: 1rem; color: var(--accent); }
.prod-stock { font-size: .72rem; font-weight: 600; }
.prod-stock.in { color: var(--success); }
.prod-stock.out { color: var(--danger); }

.prod-actions { margin-top: auto; padding-top: .65rem; }

.add-modal-backdrop {
    position: fixed; inset: 0; background: rgba(0,0,0,.45);
    z-index: 1050; display: none; align-items: center; justify-content: center;
}
.add-modal-backdrop.show { display: flex; }
.add-modal {
    background: var(--bg-card); border-radius: var(--radius-lg);
    border: 1px solid var(--border); box-shadow: var(--shadow-lg);
    width: 90%; max-width: 420px; padding: 1.25rem;
}
.add-modal h5 { font-weight: 800; font-size: 1rem; color: var(--text); margin-bottom: 1rem; }

#productsLoading { display: none; }
#noProducts { display: none; }
</style>
@endsection

@section('content')
<div class="prod-banner">
    <h2><i class="fas fa-boxes me-2"></i>Catalogue produits</h2>
    <p>Parcourez les produits des clients et ajoutez-les à vos commandes</p>
</div>

{{-- Filters --}}
<div class="content-card mb-3">
    <div class="p-3">
        <div class="prod-filters">
            <div style="flex:1;min-width:180px;">
                <label class="form-label" style="font-size:.75rem;font-weight:600;">Recherche</label>
                <input type="text" id="searchInput" class="form-control form-control-sm" placeholder="Nom ou référence du produit...">
            </div>
            <div style="min-width:180px;">
                <label class="form-label" style="font-size:.75rem;font-weight:600;">Client (Admin)</label>
                <select id="adminFilter" class="form-select form-select-sm">
                    <option value="">Tous les clients</option>
                    @foreach($admins as $admin)
                        <option value="{{ $admin->id }}">{{ $admin->shop_name ?? $admin->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="form-label" style="font-size:.75rem;font-weight:600;">&nbsp;</label>
                <button onclick="loadProducts()" class="btn btn-sm btn-royal d-block"><i class="fas fa-search me-1"></i>Rechercher</button>
            </div>
        </div>
    </div>
</div>

@if($admins->isEmpty())
<div class="content-card">
    <div class="p-4 text-center" style="color:var(--text-secondary);">
        <i class="fas fa-info-circle d-block mb-2" style="font-size:2rem;opacity:.4;"></i>
        Aucune commande active. Les produits seront disponibles lorsque des commandes vous seront assignées.
    </div>
</div>
@else

{{-- Product Grid --}}
<div id="productsLoading" class="text-center py-4" style="color:var(--text-secondary);">
    <i class="fas fa-spinner fa-spin me-2"></i>Chargement des produits...
</div>
<div id="noProducts" class="content-card">
    <div class="p-4 text-center" style="color:var(--text-secondary);">
        <i class="fas fa-box-open d-block mb-2" style="font-size:2rem;opacity:.4;"></i>
        Aucun produit trouvé.
    </div>
</div>
<div class="prod-grid" id="productsGrid"></div>

{{-- Add to Order Modal --}}
<div class="add-modal-backdrop" id="addModal">
    <div class="add-modal">
        <h5><i class="fas fa-cart-plus me-2" style="color:var(--success);"></i>Ajouter à une commande</h5>
        <form id="addForm" method="POST">
            @csrf
            <input type="hidden" name="product_id" id="modalProductId">
            <div class="mb-3">
                <label class="form-label" style="font-size:.8rem;font-weight:600;">Produit</label>
                <div id="modalProductName" style="font-weight:700;font-size:.9rem;color:var(--text);"></div>
                <div id="modalProductPrice" style="font-size:.82rem;color:var(--accent);font-weight:600;"></div>
            </div>
            <div class="mb-3">
                <label class="form-label" style="font-size:.8rem;font-weight:600;">Commande cible</label>
                <select name="assignment_id" id="modalAssignment" class="form-select form-select-sm" required>
                    @foreach($activeAssignments as $a)
                        <option value="{{ $a->id }}" data-admin="{{ $a->admin_id }}">
                            #{{ $a->order->id ?? '?' }} — {{ $a->order->customer_name ?? 'N/A' }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label" style="font-size:.8rem;font-weight:600;">Quantité</label>
                <input type="number" name="quantity" class="form-control form-control-sm" value="1" min="1" required>
            </div>
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-sm btn-royal flex-grow-1"><i class="fas fa-plus me-1"></i>Ajouter</button>
                <button type="button" class="btn btn-sm btn-outline-royal" onclick="closeModal()">Annuler</button>
            </div>
        </form>
    </div>
</div>
@endif
@endsection

@section('scripts')
@php
    $assignmentsData = $activeAssignments->map(fn($a) => ['id' => $a->id, 'admin_id' => $a->admin_id, 'order_id' => $a->order->id ?? null, 'customer' => $a->order->customer_name ?? 'N/A']);
@endphp
<script>
const apiUrl = @json(route('confirmi.employee.products.api'));
const assignments = @json($assignmentsData);
const addItemBaseUrl = @json(url('confirmi/employee/orders'));

let debounceTimer;
document.getElementById('searchInput')?.addEventListener('input', function() {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(loadProducts, 400);
});
document.getElementById('adminFilter')?.addEventListener('change', loadProducts);

function loadProducts() {
    const grid = document.getElementById('productsGrid');
    const loading = document.getElementById('productsLoading');
    const noResults = document.getElementById('noProducts');
    if (!grid) return;

    const q = document.getElementById('searchInput')?.value || '';
    const adminId = document.getElementById('adminFilter')?.value || '';

    loading.style.display = 'block';
    noResults.style.display = 'none';
    grid.innerHTML = '';

    const params = new URLSearchParams();
    if (q) params.append('q', q);
    if (adminId) params.append('admin_id', adminId);

    fetch(apiUrl + '?' + params.toString())
        .then(r => r.json())
        .then(products => {
            loading.style.display = 'none';
            if (products.length === 0) {
                noResults.style.display = 'block';
                return;
            }
            let html = '';
            products.forEach(p => {
                const imgHtml = p.image
                    ? `<div class="prod-img"><img src="/storage/${p.image}" alt="${p.name}"></div>`
                    : `<div class="prod-img"><i class="fas fa-box"></i></div>`;
                const stockCls = (p.stock > 0) ? 'in' : 'out';
                const stockTxt = (p.stock > 0) ? `${p.stock} en stock` : 'Rupture';
                html += `<div class="prod-card">
                    ${imgHtml}
                    <div class="prod-body">
                        <div class="prod-name">${p.name}</div>
                        <div class="prod-ref">Réf: ${p.reference || '-'}</div>
                        <div class="d-flex align-items-center justify-content-between">
                            <span class="prod-price">${parseFloat(p.price).toFixed(3)} DT</span>
                            <span class="prod-stock ${stockCls}"><i class="fas fa-circle" style="font-size:.45rem;margin-right:3px;"></i>${stockTxt}</span>
                        </div>
                        <div class="prod-actions">
                            <button class="btn btn-sm btn-royal w-100" onclick="openModal(${p.id}, '${p.name.replace(/'/g, "\\'")}', '${parseFloat(p.price).toFixed(3)}', ${p.admin_id})">
                                <i class="fas fa-cart-plus me-1"></i>Ajouter
                            </button>
                        </div>
                    </div>
                </div>`;
            });
            grid.innerHTML = html;
        })
        .catch(() => {
            loading.style.display = 'none';
            grid.innerHTML = '<div class="text-center py-3" style="color:var(--danger);">Erreur de chargement</div>';
        });
}

function openModal(productId, productName, productPrice, adminId) {
    document.getElementById('modalProductId').value = productId;
    document.getElementById('modalProductName').textContent = productName;
    document.getElementById('modalProductPrice').textContent = productPrice + ' DT';

    // Filter assignments to only show orders from the same admin
    const select = document.getElementById('modalAssignment');
    const options = select.querySelectorAll('option');
    let firstVisible = null;
    options.forEach(opt => {
        const aAdmin = parseInt(opt.dataset.admin);
        if (aAdmin === adminId) {
            opt.style.display = '';
            if (!firstVisible) firstVisible = opt;
        } else {
            opt.style.display = 'none';
        }
    });
    if (firstVisible) select.value = firstVisible.value;

    // Set form action
    const assignmentId = select.value;
    updateFormAction(assignmentId);
    select.onchange = function() { updateFormAction(this.value); };

    document.getElementById('addModal').classList.add('show');
}

function updateFormAction(assignmentId) {
    document.getElementById('addForm').action = addItemBaseUrl + '/' + assignmentId + '/add-item';
}

function closeModal() {
    document.getElementById('addModal').classList.remove('show');
}

document.getElementById('addModal')?.addEventListener('click', function(e) {
    if (e.target === this) closeModal();
});

// Initial load
@if($admins->isNotEmpty())
loadProducts();
@endif
</script>
@endsection
