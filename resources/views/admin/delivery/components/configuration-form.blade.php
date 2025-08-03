{{--
    Composant de formulaire de configuration pour les transporteurs
    
    Props:
    - $carrier: Array avec les informations du transporteur
    - $carrierSlug: String slug du transporteur (jax_delivery, mes_colis)
    - $config: Model DeliveryConfiguration (optionnel, pour édition)
    - $mode: String 'create' ou 'edit' (défaut: 'create')
--}}

@php
    $mode = $mode ?? 'create';
    $isEdit = $mode === 'edit' && isset($config);
@endphp

<div class="mb-4">
    <label for="integration_name" class="form-label">
        <i class="fas fa-tag me-1"></i>
        Nom de la Liaison <span class="text-danger">*</span>
    </label>
    <input type="text" 
           class="form-control @error('integration_name') is-invalid @enderror" 
           id="integration_name" 
           name="integration_name" 
           value="{{ old('integration_name', $isEdit ? $config->integration_name : '') }}"
           placeholder="Ex: Compte Principal, Entrepôt Sud..."
           required>
    <div class="form-text">
        Donnez un nom unique à cette configuration pour la différencier des autres.
    </div>
    @error('integration_name')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

@if($carrierSlug === 'jax_delivery')
    <div class="row">
        <div class="col-md-6 mb-4">
            <label for="username" class="form-label">
                <i class="fas fa-user me-1"></i>
                Numéro de Compte <span class="text-danger">*</span>
            </label>
            <input type="text" 
                   class="form-control @error('username') is-invalid @enderror" 
                   id="username" 
                   name="username" 
                   value="{{ old('username', $isEdit ? $config->username : '') }}"
                   required>
            @error('username')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        
        <div class="col-md-6 mb-4">
            <label for="password" class="form-label">
                <i class="fas fa-key me-1"></i>
                Token API @if(!$isEdit)<span class="text-danger">*</span>@endif
            </label>
            <div class="input-group">
                <input type="password" 
                       class="form-control @error('password') is-invalid @enderror" 
                       id="password" 
                       name="password"
                       placeholder="{{ $isEdit ? 'Laisser vide pour conserver' : '' }}"
                       {{ !$isEdit ? 'required' : '' }}>
                {{-- Note: a button to show/hide password would be a good UX improvement here --}}
            </div>
             @if($isEdit)
            <div class="form-text">
                Remplir uniquement pour changer le token actuel.
            </div>
            @endif
            @error('password')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
@elseif($carrierSlug === 'mes_colis')
    <div class="mb-4">
        <label for="username" class="form-label">
            <i class="fas fa-key me-1"></i>
            Token API <span class="text-danger">*</span>
        </label>
        <input type="text" 
               class="form-control @error('username') is-invalid @enderror" 
               id="username" 
               name="username" 
               value="{{ old('username', $isEdit ? $config->username : '') }}"
               required>
        @error('username')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
@endif

<input type="hidden" name="environment" value="prod">

<div class="card bg-light border-0">
    <div class="card-header bg-transparent border-0 py-2">
        <h6 class="mb-0 text-muted">
            <i class="fas fa-cogs me-1"></i>
            Paramètres Supplémentaires
        </h6>
    </div>
    <div class="card-body">
        <label class="form-label">
            <i class="fas fa-sliders-h me-1"></i>
            Configuration Spécifique (JSON)
        </label>
        <textarea class="form-control" 
                  name="settings" 
                  rows="3"
                  placeholder='{"timeout": 30, "retry_count": 3}'>{{ old('settings', ($isEdit && $config->settings) ? json_encode($config->settings, JSON_PRETTY_PRINT) : '') }}</textarea>
        <small class="form-text text-muted">
            Configuration JSON optionnelle pour des paramètres avancés (laisser vide si non nécessaire).
        </small>
    </div>
</div>