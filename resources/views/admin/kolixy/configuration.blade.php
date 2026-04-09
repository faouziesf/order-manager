@extends('layouts.admin')
@section('title', 'Kolixy — Configuration')

@section('css')
@include('admin.kolixy._styles')
@endsection

@section('content')
<div class="kolixy-page p-3">
    <div id="kolixy-toast" class="kolixy-toast"></div>

    {{-- Header --}}
    <div class="kolixy-card mb-3">
        <div class="kolixy-header">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <h4><i class="fas fa-cog me-2"></i>Configuration Kolixy</h4>
                    <p>Gérez votre connexion et vos paramètres de livraison</p>
                </div>
                <a href="{{ route('admin.kolixy.dashboard') }}" class="kolixy-btn kolixy-btn-outline" style="background:rgba(255,255,255,0.15);color:white;border-color:rgba(255,255,255,0.3);">
                    <i class="fas fa-arrow-left"></i> Dashboard
                </a>
            </div>
        </div>
    </div>

    <div class="row g-3">
        {{-- Connexion --}}
        <div class="col-lg-5">
            <div class="kolixy-card">
                <div class="kolixy-card-body">
                    @if($config && $config->api_token)
                        {{-- Connected state --}}
                        <div class="text-center mb-3">
                            <div style="width:60px;height:60px;border-radius:50%;background:linear-gradient(135deg,var(--kolixy-primary),var(--kolixy-primary-light));display:inline-flex;align-items:center;justify-content:center;color:white;font-size:1.5rem;">
                                <i class="fas fa-user-check"></i>
                            </div>
                            <h6 class="fw-bold mt-2 mb-0">{{ $config->masafa_user_name ?? 'Compte Kolixy' }}</h6>
                            <small class="text-muted">{{ $config->masafa_user_email ?? '' }}</small>
                            <div class="mt-2">
                                <span class="kolixy-badge kolixy-badge-green"><i class="fas fa-check-circle me-1"></i>Connecté</span>
                            </div>
                        </div>

                        <hr>

                        <div class="d-flex gap-2">
                            <button class="kolixy-btn kolixy-btn-outline flex-fill" onclick="testConnection()">
                                <i class="fas fa-plug"></i> Tester connexion
                            </button>
                            <button class="kolixy-btn kolixy-btn-danger kolixy-btn-sm" onclick="deleteConfig()">
                                <i class="fas fa-unlink"></i>
                            </button>
                        </div>
                    @else
                        {{-- Login form --}}
                        <h6 class="fw-bold mb-3"><i class="fas fa-sign-in-alt me-2" style="color:var(--kolixy-primary);"></i>Connexion à Kolixy</h6>
                        <p class="text-muted" style="font-size:0.85rem;">Entrez vos identifiants Kolixy (compte client) pour lier votre compte.</p>

                        <div class="mb-3">
                            <label class="form-label fw-semibold" style="font-size:0.85rem;">Email</label>
                            <input type="email" id="kolixy-email" class="kolixy-input" placeholder="votre@email.com">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold" style="font-size:0.85rem;">Mot de passe</label>
                            <div style="position:relative;">
                                <input type="password" id="kolixy-password" class="kolixy-input" placeholder="••••••••">
                                <button type="button" onclick="togglePassword()" style="position:absolute;right:10px;top:50%;transform:translateY(-50%);background:none;border:none;color:#9ca3af;cursor:pointer;">
                                    <i class="fas fa-eye" id="pw-icon"></i>
                                </button>
                            </div>
                        </div>
                        <button class="kolixy-btn kolixy-btn-primary w-100" onclick="connectKolixy()" id="btn-connect">
                            <i class="fas fa-link"></i> Se connecter avec Kolixy
                        </button>
                        <p class="text-muted mt-2" style="font-size:0.75rem;"><i class="fas fa-shield-alt me-1"></i>Vos identifiants ne sont pas stockés. Seul un token API sécurisé est conservé.</p>
                    @endif
                </div>
            </div>
        </div>

        {{-- Paramètres --}}
        <div class="col-lg-7">
            @if($config && $config->api_token)
            <div class="kolixy-card">
                <div class="kolixy-card-body">
                    <h6 class="fw-bold mb-3"><i class="fas fa-map-marker-alt me-2" style="color:var(--kolixy-primary);"></i>Adresses de ramassage</h6>

                    <div class="mb-3">
                        <label class="form-label fw-semibold" style="font-size:0.85rem;">Adresse de ramassage par défaut</label>
                        <div class="d-flex gap-2">
                            <select id="pickup-address-select" class="kolixy-input" onchange="onPickupChange(this)">
                                <option value="">— Chargement... —</option>
                            </select>
                            <button class="kolixy-btn kolixy-btn-outline kolixy-btn-sm" onclick="loadPickupAddresses()" title="Rafraîchir">
                                <i class="fas fa-sync-alt"></i>
                            </button>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold" style="font-size:0.85rem;">Nom pour ramassage</label>
                        <input type="text" id="pickup-name" class="kolixy-input" placeholder="Nom affiché pour le ramassage" value="{{ $config->pickup_name ?? '' }}">
                    </div>

                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="auto-send" {{ $config->auto_send ? 'checked' : '' }}>
                            <label class="form-check-label fw-semibold" for="auto-send" style="font-size:0.85rem;">Envoi automatique des commandes confirmées</label>
                        </div>
                    </div>

                    <button class="kolixy-btn kolixy-btn-primary" onclick="saveConfig()">
                        <i class="fas fa-save"></i> Enregistrer
                    </button>
                </div>
            </div>
            @else
            <div class="kolixy-card">
                <div class="kolixy-card-body kolixy-empty">
                    <i class="fas fa-lock"></i>
                    <h6>Connectez votre compte d'abord</h6>
                    <p class="text-muted">Les paramètres seront disponibles après connexion.</p>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
const CSRF = '{{ csrf_token() }}';

function showToast(type, msg) {
    const t = document.getElementById('kolixy-toast');
    t.className = 'kolixy-toast ' + type;
    t.textContent = msg;
    t.style.display = 'block';
    setTimeout(() => t.style.display = 'none', 4000);
}

function togglePassword() {
    const inp = document.getElementById('kolixy-password');
    const ico = document.getElementById('pw-icon');
    if (inp.type === 'password') { inp.type = 'text'; ico.className = 'fas fa-eye-slash'; }
    else { inp.type = 'password'; ico.className = 'fas fa-eye'; }
}

function connectKolixy() {
    const email = document.getElementById('kolixy-email').value.trim();
    const password = document.getElementById('kolixy-password').value;
    const btn = document.getElementById('btn-connect');

    if (!email || !password) { showToast('error', 'Veuillez remplir tous les champs.'); return; }

    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Connexion...';

    fetch('{{ route("admin.kolixy.connect") }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
        body: JSON.stringify({ email, password })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            showToast('success', data.message || 'Connecté !');
            setTimeout(() => location.reload(), 1000);
        } else {
            showToast('error', data.message || 'Erreur de connexion.');
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-link"></i> Se connecter avec Kolixy';
        }
    })
    .catch(() => {
        showToast('error', 'Erreur réseau.');
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-link"></i> Se connecter avec Kolixy';
    });
}

function testConnection() {
    fetch('{{ route("admin.kolixy.test-connection") }}', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' }
    })
    .then(r => r.json())
    .then(data => showToast(data.success ? 'success' : 'error', data.message))
    .catch(() => showToast('error', 'Erreur réseau.'));
}

function deleteConfig() {
    if (!confirm('Voulez-vous déconnecter votre compte Kolixy ?')) return;

    fetch('{{ route("admin.kolixy.config.delete") }}', {
        method: 'DELETE',
        headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' }
    })
    .then(r => r.json())
    .then(data => {
        showToast(data.success ? 'success' : 'error', data.message);
        if (data.success) setTimeout(() => location.reload(), 1000);
    });
}

function loadPickupAddresses() {
    const sel = document.getElementById('pickup-address-select');
    if (!sel) return;
    sel.innerHTML = '<option value="">Chargement...</option>';

    fetch('{{ route("admin.kolixy.pickup-addresses") }}', {
        headers: { 'Accept': 'application/json' }
    })
    .then(r => r.json())
    .then(data => {
        sel.innerHTML = '<option value="">— Sélectionner —</option>';
        const addresses = data.data || [];
        const currentId = '{{ $config->masafa_client_id ?? "" }}';
        addresses.forEach(a => {
            const opt = document.createElement('option');
            opt.value = a.id;
            opt.textContent = (a.label || a.name || a.address || 'Adresse #' + a.id) + (a.gouvernorat ? ' — ' + a.gouvernorat : '');
            if (String(a.id) === String(currentId)) opt.selected = true;
            sel.appendChild(opt);
        });
    })
    .catch(() => {
        sel.innerHTML = '<option value="">Erreur de chargement</option>';
    });
}

function onPickupChange(sel) {
    // Auto-fill pickup name if available
}

function saveConfig() {
    const addrId = document.getElementById('pickup-address-select')?.value || '';
    const pickupName = document.getElementById('pickup-name')?.value || '';
    const autoSend = document.getElementById('auto-send')?.checked ? 1 : 0;

    fetch('{{ route("admin.kolixy.config.save") }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
        body: JSON.stringify({
            kolixy_pickup_address_id: addrId,
            pickup_name: pickupName,
            auto_send: autoSend,
        })
    })
    .then(r => r.json())
    .then(data => showToast(data.success ? 'success' : 'error', data.message))
    .catch(() => showToast('error', 'Erreur réseau.'));
}

// Load addresses on page load if connected
@if($config && $config->api_token)
document.addEventListener('DOMContentLoaded', loadPickupAddresses);
@endif
</script>
@endsection
