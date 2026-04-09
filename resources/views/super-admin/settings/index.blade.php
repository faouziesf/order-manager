@extends('layouts.super-admin')

@section('title', 'Paramètres')
@section('page-title', 'Paramètres')

@section('content')
    <div style="max-width:720px">
        <!-- General Settings -->
        <div class="sa-card" style="margin-bottom:24px">
            <div class="sa-card-header">
                <h3 class="sa-card-title"><i class="fas fa-cog" style="color:var(--sa-primary);margin-right:8px"></i>Paramètres Généraux</h3>
            </div>

            <form method="POST" action="{{ route('super-admin.settings.update') }}">
                @csrf

                @if($errors->any())
                    <div class="sa-alert sa-alert-danger"><i class="fas fa-exclamation-circle"></i><div>@foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div></div>
                @endif

                <div class="sa-grid sa-grid-2">
                    <div class="sa-form-group">
                        <label class="sa-form-label">Période d'essai (jours)</label>
                        <input type="number" name="trial_period" class="sa-input" value="{{ $settings['trial_period'] ?? 14 }}" min="0" required>
                        <div class="sa-form-hint">Durée de la période d'essai pour les nouveaux admins</div>
                    </div>
                    <div class="sa-form-group">
                        <label class="sa-form-label">Inscription publique</label>
                        <select name="allow_registration" class="sa-input sa-select" required>
                            <option value="1" {{ ($settings['allow_registration'] ?? '1') == '1' ? 'selected' : '' }}>Activée</option>
                            <option value="0" {{ ($settings['allow_registration'] ?? '1') == '0' ? 'selected' : '' }}>Désactivée</option>
                        </select>
                        <div class="sa-form-hint">Autoriser les nouveaux utilisateurs à s'inscrire</div>
                    </div>
                </div>

                <div style="padding-top:16px;border-top:1px solid var(--sa-border);margin-top:8px">
                    <button type="submit" class="sa-btn sa-btn-primary"><i class="fas fa-save"></i> Enregistrer</button>
                </div>
            </form>
        </div>

        <!-- Platform Info -->
        <div class="sa-card" style="margin-bottom:24px">
            <div class="sa-card-header">
                <h3 class="sa-card-title"><i class="fas fa-info-circle" style="color:var(--sa-info);margin-right:8px"></i>Informations Plateforme</h3>
            </div>
            <table style="width:100%">
                @php
                    $info = [
                        ['Plateforme', 'Order Manager'],
                        ['Version Laravel', app()->version()],
                        ['Version PHP', phpversion()],
                        ['Environnement', app()->environment()],
                        ['Fuseau horaire', config('app.timezone')],
                        ['Admins Total', \App\Models\Admin::where('role','admin')->count()],
                        ['Utilisateurs Confirmi', \App\Models\ConfirmiUser::count()],
                    ];
                @endphp
                @foreach($info as [$label, $value])
                    <tr>
                        <td style="padding:8px 0;font-size:.8125rem;color:var(--sa-text-secondary);width:40%">{{ $label }}</td>
                        <td style="padding:8px 0;font-size:.8125rem;font-weight:500">{{ $value }}</td>
                    </tr>
                @endforeach
            </table>
        </div>

        <!-- Quick System Actions -->
        <div class="sa-card">
            <div class="sa-card-header">
                <h3 class="sa-card-title"><i class="fas fa-tools" style="color:var(--sa-warning);margin-right:8px"></i>Actions Système</h3>
            </div>
            <div style="display:flex;gap:12px;flex-wrap:wrap">
                @if(Route::has('super-admin.system.cache.clear'))
                    <form action="{{ route('super-admin.system.cache.clear') }}" method="POST" onsubmit="return confirm('Vider le cache ?')">
                        @csrf
                        <button type="submit" class="sa-btn sa-btn-outline sa-btn-sm"><i class="fas fa-broom"></i> Vider le cache</button>
                    </form>
                @endif
            </div>
        </div>
    </div>
@endsection
