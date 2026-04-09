@extends('layouts.admin')

@section('title', 'Integrations WooCommerce')

@section('css')
<style>:root { --intg-color-1: #7c3aed; --intg-color-2: #a78bfa; }</style>
@include('admin.partials._integration-styles')
@endsection

@section('content')
<div class="om-page-header">
    <div>
        <h1 style="font-size:24px; font-weight:800; color:var(--om-text); margin:0;">
            <i class="fab fa-wordpress" style="color:#7c3aed; margin-right:8px;"></i>Integrations WooCommerce
        </h1>
        <p style="color:var(--om-text-light); margin:4px 0 0; font-size:14px;">Connectez vos boutiques WooCommerce pour importer automatiquement les commandes</p>
    </div>
    <div style="display:flex; gap:8px; flex-wrap:wrap;">
        <button class="om-btn om-btn-primary" data-bs-toggle="modal" data-bs-target="#addIntegrationModal">
            <i class="fas fa-plus"></i> Nouvelle integration
        </button>
        <button class="om-btn om-btn-ghost" onclick="refreshStats()">
            <i class="fas fa-sync-alt"></i> Actualiser
        </button>
        @if($integrations->where('is_active', true)->count() > 0)
        <a href="{{ route('admin.woocommerce.sync') }}" class="om-btn om-btn-success">
            <i class="fas fa-download"></i> Synchroniser
        </a>
        @endif
    </div>
</div>

{{-- Stats --}}
<div class="integration-stats">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; flex-wrap:wrap; gap:12px;">
        <h4 style="font-weight:800; margin:0;"><i class="fas fa-chart-bar" style="margin-right:8px;"></i>Apercu des integrations</h4>
        <div class="intg-badges">
            <span class="intg-badge"><i class="fas fa-clock" style="margin-right:6px;"></i>Sync auto: 3 min</span>
            <span class="intg-badge"><i class="fas fa-store" style="margin-right:6px;"></i>Multi-boutiques</span>
        </div>
    </div>
    <div class="intg-stat-grid">
        <div class="intg-stat-box">
            <div class="intg-stat-num" id="total-integrations">{{ $syncStats['total_integrations'] }}</div>
            <div class="intg-stat-lbl">Integrations totales</div>
        </div>
        <div class="intg-stat-box">
            <div class="intg-stat-num" id="active-integrations">{{ $syncStats['active_integrations'] }}</div>
            <div class="intg-stat-lbl">Integrations actives</div>
        </div>
        <div class="intg-stat-box">
            <div class="intg-stat-num" id="total-orders">{{ $syncStats['total_orders'] }}</div>
            <div class="intg-stat-lbl">Commandes importees</div>
        </div>
    </div>
</div>

{{-- Integration list --}}
@if($integrations->count() > 0)
<h5 style="font-weight:700; color:var(--om-text); margin-bottom:16px;">
    <i class="fas fa-list" style="margin-right:8px; color:var(--om-primary);"></i>Integrations configurees
</h5>
@foreach($integrations as $integration)
<div class="intg-card {{ $integration->is_active ? 'active' : 'inactive' }}">
    <div class="intg-card-info">
        <div style="display:flex; align-items:center;">
            <span class="status-dot {{ $integration->is_active ? ($integration->sync_status === 'syncing' ? 'syncing' : ($integration->sync_status === 'error' ? 'error' : 'active')) : 'inactive' }}"></span>
            <h6>{{ parse_url($integration->store_url, PHP_URL_HOST) }}</h6>
        </div>
        <div class="intg-card-url">{{ $integration->store_url }}</div>
        @if($integration->last_sync_at)
        <div class="intg-card-meta"><i class="fas fa-clock" style="margin-right:4px;"></i>Derniere sync: {{ $integration->last_sync_at->diffForHumans() }}</div>
        @endif
        @if($integration->sync_error)
        <div style="color:var(--om-danger); font-size:12px; margin-top:4px;">
            <i class="fas fa-exclamation-triangle" style="margin-right:4px;"></i>{{ $integration->sync_error }}
        </div>
        @endif
    </div>
    <div class="intg-card-actions">
        <span style="font-size:13px; color:var(--om-text-light);">{{ $integration->is_active ? 'Actif' : 'Inactif' }}</span>
        <label class="toggle-sw">
            <input type="checkbox" {{ $integration->is_active ? 'checked' : '' }} onchange="toggleIntegration({{ $integration->id }}, this)">
            <span class="slider"></span>
        </label>
        <button class="om-btn om-btn-ghost om-btn-sm om-btn-icon" style="color:var(--om-danger);" onclick="deleteIntegration({{ $integration->id }}, '{{ parse_url($integration->store_url, PHP_URL_HOST) }}')">
            <i class="fas fa-trash"></i>
        </button>
    </div>
</div>
@endforeach
@else
<div class="om-card" style="text-align:center; padding:48px 32px;">
    <div class="om-empty">
        <div class="om-empty-icon"><i class="fab fa-wordpress"></i></div>
        <h3>Aucune integration WooCommerce</h3>
        <p>Commencez par connecter votre premiere boutique</p>
        <button class="om-btn om-btn-primary" data-bs-toggle="modal" data-bs-target="#addIntegrationModal">
            <i class="fas fa-plus"></i> Ajouter une integration
        </button>
    </div>
</div>
@endif

{{-- Add Modal --}}
<div class="modal fade" id="addIntegrationModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header intg-modal-header">
                <h5 class="modal-title"><i class="fab fa-wordpress" style="margin-right:8px;"></i>Nouvelle integration WooCommerce</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.woocommerce.store') }}" method="POST" id="woocommerce-form">
                @csrf
                <div class="modal-body">
                    <div class="conn-result" id="connection-result"></div>
                    <div class="om-form-group" style="margin-bottom:16px;">
                        <label class="om-form-label">URL de la boutique</label>
                        <input type="url" class="om-form-input @error('store_url') is-invalid @enderror" id="store_url" name="store_url" value="{{ old('store_url') }}" required placeholder="https://votreboutique.com">
                        @error('store_url')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="row" style="gap:0;">
                        <div class="col-md-6 mb-3">
                            <div class="om-form-group">
                                <label class="om-form-label">Cle API (Consumer Key)</label>
                                <input type="text" class="om-form-input @error('consumer_key') is-invalid @enderror" id="consumer_key" name="consumer_key" value="{{ old('consumer_key') }}" required placeholder="ck_xxxxxxxxx">
                                @error('consumer_key')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="om-form-group">
                                <label class="om-form-label">Secret API (Consumer Secret)</label>
                                <input type="password" class="om-form-input @error('consumer_secret') is-invalid @enderror" id="consumer_secret" name="consumer_secret" value="{{ old('consumer_secret') }}" required placeholder="cs_xxxxxxxxx">
                                @error('consumer_secret')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>
                    <label class="modern-check" style="margin-bottom:20px;">
                        <input type="checkbox" id="is_active" name="is_active" value="1" checked>
                        <div>
                            <strong>Activer immediatement</strong>
                            <small style="display:block; color:var(--om-text-light);">Synchronisation automatique toutes les 3 min</small>
                        </div>
                    </label>
                    <div class="help-box">
                        <h6><i class="fas fa-info-circle" style="margin-right:8px;"></i>Comment obtenir vos cles API</h6>
                        <ol>
                            <li>Connectez-vous a l'administration WordPress</li>
                            <li>Allez dans <strong>WooCommerce > Parametres > Avance > REST API</strong></li>
                            <li>Cliquez sur <strong>"Ajouter une cle"</strong></li>
                            <li>Selectionnez <strong>"Lecture/Ecriture"</strong> pour les droits</li>
                            <li>Cliquez sur <strong>"Generer une cle API"</strong></li>
                            <li>Copiez la cle client et le secret dans les champs ci-dessus</li>
                        </ol>
                    </div>
                    <div class="info-box">
                        <h6><i class="fas fa-magic" style="margin-right:8px;"></i>Fonctionnement</h6>
                        <ul>
                            <li><strong>Multi-boutiques :</strong> Connectez plusieurs boutiques</li>
                            <li><strong>Import intelligent :</strong> Les commandes terminees gardent leur statut</li>
                            <li><strong>Localisation auto :</strong> Regions et villes creees automatiquement</li>
                            <li><strong>Sync bidirectionnelle :</strong> Modifications synchronisees</li>
                        </ul>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="om-btn om-btn-ghost" onclick="testConnection()">
                        <i class="fas fa-plug"></i> Tester la connexion
                    </button>
                    <button type="button" class="om-btn om-btn-ghost" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="om-btn om-btn-primary"><i class="fas fa-save"></i> Ajouter</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

function testConnection() {
    const url = document.getElementById('store_url').value;
    const key = document.getElementById('consumer_key').value;
    const secret = document.getElementById('consumer_secret').value;
    const btn = document.querySelector('button[onclick="testConnection()"]');
    if (!url || !key || !secret) { showConn('error', 'Remplissez tous les champs.'); return; }
    btn.disabled = true; btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Test...';
    fetch('{{ route("admin.woocommerce.test-connection") }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
        body: JSON.stringify({ store_url: url, consumer_key: key, consumer_secret: secret })
    }).then(r => r.json()).then(d => {
        let msg = d.message;
        if (d.store_info) { msg += '<br><strong>Boutique:</strong> ' + d.store_info.name; if(d.store_info.version) msg += ' (WC ' + d.store_info.version + ')'; }
        showConn(d.success ? 'success' : 'error', msg);
    }).catch(e => showConn('error', 'Erreur: ' + e.message))
    .finally(() => { btn.disabled = false; btn.innerHTML = '<i class="fas fa-plug"></i> Tester la connexion'; });
}

function showConn(type, msg) {
    const el = document.getElementById('connection-result');
    el.className = 'conn-result ' + type;
    el.innerHTML = '<i class="fas fa-' + (type==='success'?'check-circle':'exclamation-triangle') + '" style="margin-right:8px;"></i>' + msg;
    el.style.display = 'block';
    if (type==='success') setTimeout(() => el.style.display='none', 10000);
}

function toggleIntegration(id, cb) {
    const active = cb.checked;
    fetch('{{ route("admin.woocommerce.index") }}/toggle/' + id, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
        body: JSON.stringify({ is_active: active })
    }).then(r => r.json()).then(d => {
        if (d.success) {
            const card = cb.closest('.intg-card');
            card.classList.toggle('active', active); card.classList.toggle('inactive', !active);
            refreshStats(); showNotif('success', d.message);
        } else { cb.checked = !active; showNotif('error', 'Erreur'); }
    }).catch(() => { cb.checked = !active; showNotif('error', 'Erreur connexion'); });
}

function deleteIntegration(id, name) {
    if (confirm('Supprimer l\'integration avec ' + name + ' ?'))
        window.location.href = '{{ route("admin.woocommerce.index") }}/delete/' + id;
}

function refreshStats() {
    fetch('{{ route("admin.woocommerce.stats") }}').then(r => r.json()).then(d => {
        document.getElementById('total-integrations').textContent = d.total_integrations;
        document.getElementById('active-integrations').textContent = d.active_integrations;
        document.getElementById('total-orders').textContent = d.total_orders;
    }).catch(e => console.error('Stats error:', e));
}

function showNotif(type, msg) {
    const n = document.createElement('div');
    n.className = 'alert alert-' + (type==='success'?'success':'danger') + ' alert-dismissible fade show position-fixed';
    n.style.cssText = 'top:20px;right:20px;z-index:9999;min-width:300px;border-radius:12px;';
    n.innerHTML = msg + '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
    document.body.appendChild(n);
    setTimeout(() => n.remove(), 3000);
}

setInterval(refreshStats, 30000);

document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('store_url').addEventListener('blur', function() {
        if (this.value && !this.value.startsWith('http')) this.value = 'https://' + this.value;
    });
    document.getElementById('woocommerce-form').addEventListener('submit', function() {
        const btn = this.querySelector('button[type="submit"]');
        btn.disabled = true; btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Enregistrement...';
    });
    @if($errors->any()) new bootstrap.Modal(document.getElementById('addIntegrationModal')).show(); @endif
    document.getElementById('addIntegrationModal').addEventListener('hidden.bs.modal', function() {
        document.getElementById('woocommerce-form').reset();
        document.getElementById('connection-result').style.display = 'none';
        const btn = this.querySelector('button[type="submit"]');
        if(btn) { btn.disabled = false; btn.innerHTML = '<i class="fas fa-save"></i> Ajouter'; }
    });
});
</script>
@endsection
