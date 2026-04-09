@extends('layouts.admin')

@section('title', 'Importation de commandes')

@section('css')
@include('admin.partials._shared-styles')
<style>
    .upload-zone {
        border: 2px dashed var(--om-border);
        border-radius: 16px;
        padding: 48px 24px;
        text-align: center;
        background: var(--bg-muted, #f8fafc);
        transition: all 0.3s ease;
        cursor: pointer;
        position: relative;
    }
    .upload-zone:hover, .upload-zone.dragover {
        border-color: var(--om-primary);
        background: linear-gradient(135deg, #eef2ff 0%, #e0e7ff 100%);
        transform: translateY(-2px);
    }
    .upload-zone input[type="file"] {
        position: absolute;
        top: 0; left: 0;
        width: 100%; height: 100%;
        opacity: 0;
        cursor: pointer;
    }
    .upload-icon {
        width: 72px; height: 72px;
        border-radius: 20px;
        background: linear-gradient(135deg, var(--om-primary) 0%, #7c3aed 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 16px;
        color: white;
        font-size: 28px;
    }
    .file-info {
        display: none;
        margin-top: 16px;
        padding: 12px 20px;
        background: #ecfdf5;
        border-radius: 10px;
        color: #065f46;
        font-weight: 600;
        gap: 8px;
        align-items: center;
        justify-content: center;
    }
    .file-info.show { display: flex; }
    .format-card {
        background: var(--card-bg, white);
        border-radius: 16px;
        border: 1px solid var(--om-border);
        padding: 24px;
    }
    .format-card h6 {
        font-weight: 700;
        color: var(--om-text);
        margin-bottom: 16px;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .column-list {
        list-style: none;
        padding: 0;
        margin: 0 0 16px 0;
    }
    .column-list li {
        padding: 8px 0;
        border-bottom: 1px solid var(--border-color, #f1f5f9);
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 13px;
    }
    .column-list li:last-child { border-bottom: 0; }
    .column-list .col-tag {
        background: #eef2ff;
        color: var(--om-primary);
        padding: 2px 8px;
        border-radius: 6px;
        font-family: monospace;
        font-size: 12px;
        font-weight: 600;
    }
    .product-example {
        width: 100%;
        border-collapse: collapse;
        margin: 12px 0;
        border-radius: 10px;
        overflow: hidden;
        font-size: 13px;
    }
    .product-example th {
        background: var(--om-primary);
        color: white;
        padding: 8px 12px;
        font-weight: 600;
        text-align: left;
    }
    .product-example td {
        padding: 8px 12px;
        background: var(--bg-muted, #f8fafc);
        border-bottom: 1px solid var(--border-color, #e2e8f0);
    }
    .options-section {
        background: var(--bg-muted, #f8fafc);
        border-radius: 12px;
        border: 1px solid var(--om-border);
        padding: 20px;
        margin-top: 20px;
    }
    .options-section h6 {
        font-weight: 700;
        margin-bottom: 16px;
        color: var(--om-text);
        display: flex;
        align-items: center;
        gap: 8px;
    }
</style>
@endsection

@section('content')
<div class="om-page-header">
    <div>
        <h1 style="font-size:24px; font-weight:800; color:var(--om-text); margin:0;">
            <i class="fas fa-file-import" style="color:var(--om-primary); margin-right:8px;"></i>Importation de commandes
        </h1>
        <p style="color:var(--om-text-light); margin:4px 0 0; font-size:14px;">Importez vos commandes depuis un fichier CSV en quelques clics</p>
    </div>
</div>

<div class="row" style="gap:0;">
    {{-- LEFT: Upload Form --}}
    <div class="col-lg-8 mb-4">
        <div class="om-card">
            <div class="om-card-header">
                <h3 class="om-card-title"><i class="fas fa-cloud-upload-alt" style="color:var(--om-primary);"></i> Importer un fichier CSV</h3>
            </div>
            <div class="om-card-body">
                <form action="{{ route('admin.import.csv') }}" method="POST" enctype="multipart/form-data" id="importForm">
                    @csrf

                    <div class="upload-zone" id="uploadZone">
                        <input type="file" name="csv_file" id="csvFile" accept=".csv, .txt" required />
                        <div class="upload-icon">
                            <i class="fas fa-file-csv"></i>
                        </div>
                        <h5 style="font-weight:700; color:var(--om-text); margin-bottom:4px;">Glissez votre fichier CSV ici</h5>
                        <p style="color:var(--om-text-light); margin:0; font-size:14px;">ou cliquez pour parcourir vos fichiers</p>
                        <div class="file-info" id="fileInfo">
                            <i class="fas fa-check-circle"></i>
                            <span id="fileName"></span>
                        </div>
                    </div>

                    <div class="row mt-4" style="gap:0;">
                        <div class="col-md-4 mb-3">
                            <div class="om-form-group">
                                <label class="om-form-label">Delimiteur</label>
                                <select class="om-form-input" id="delimiter" name="delimiter" required>
                                    <option value="," selected>Virgule (,)</option>
                                    <option value=";">Point-virgule (;)</option>
                                    <option value="|">Barre verticale (|)</option>
                                    <option value="\t">Tabulation</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="om-form-group">
                                <label class="om-form-label">Statut par defaut</label>
                                <select class="om-form-input" id="default_status" name="default_status" required>
                                    <option value="nouvelle" selected>Nouvelle</option>
                                    <option value="confirmee">Confirmee</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="om-form-group">
                                <label class="om-form-label">Priorite par defaut</label>
                                <select class="om-form-input" id="default_priority" name="default_priority" required>
                                    <option value="normale" selected>Normale</option>
                                    <option value="urgente">Urgente</option>
                                    <option value="vip">VIP</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="options-section">
                        <h6><i class="fas fa-sliders-h" style="color:var(--om-primary);"></i> Options avancees</h6>
                        <div class="row" style="gap:0;">
                            <div class="col-md-6 mb-3">
                                <div class="om-form-group">
                                    <label class="om-form-label">Gouvernorat par defaut</label>
                                    <select class="om-form-input" id="default_governorate" name="default_governorate">
                                        <option value="">-- Selectionner --</option>
                                        @foreach($regions as $region)
                                            <option value="{{ $region->id }}">{{ $region->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="om-form-group">
                                    <label class="om-form-label">Ville par defaut</label>
                                    <select class="om-form-input" id="default_city" name="default_city">
                                        <option value="">-- Selectionner un gouvernorat --</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div style="margin-top:24px; display:flex; gap:12px;">
                        <button type="submit" class="om-btn om-btn-primary">
                            <i class="fas fa-file-import"></i> Importer les commandes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- RIGHT: Format Guide --}}
    <div class="col-lg-4 mb-4">
        <div class="format-card">
            <h6><i class="fas fa-book" style="color:var(--om-primary);"></i> Format du fichier CSV</h6>
            <p style="font-size:13px; color:var(--om-text-light); margin-bottom:16px;">
                Votre fichier doit contenir au moins une colonne telephone. Colonnes reconnues :
            </p>
            <ul class="column-list">
                <li><span class="col-tag">telephone</span> Telephone principal</li>
                <li><span class="col-tag">nom</span> Nom du client</li>
                <li><span class="col-tag">telephone2</span> Telephone secondaire</li>
                <li><span class="col-tag">adresse</span> Adresse de livraison</li>
                <li><span class="col-tag">gouvernorat</span> Region / Gouvernorat</li>
                <li><span class="col-tag">ville</span> Ville / Delegation</li>
                <li><span class="col-tag">frais_livraison</span> Frais de livraison</li>
                <li><span class="col-tag">notes</span> Remarques / Notes</li>
            </ul>

            <h6 style="margin-top:20px;"><i class="fas fa-box" style="color:#8b5cf6;"></i> Format des produits</h6>
            <p style="font-size:13px; color:var(--om-text-light);">Ajoutez des produits avec le format suivant :</p>
            <table class="product-example">
                <tr>
                    <th>produit_1</th>
                    <th>quantite_1</th>
                    <th>prix_1</th>
                </tr>
                <tr>
                    <td>iPhone 15</td>
                    <td>1</td>
                    <td>1200</td>
                </tr>
            </table>
            <p style="font-size:12px; color:var(--om-text-light); margin-top:8px;">
                Incrementez le chiffre pour plusieurs produits (produit_2, produit_3...)
            </p>

            <div style="margin-top:20px;">
                <a href="{{ asset('exemples/modele_import_commandes.csv') }}" class="om-btn om-btn-ghost" style="width:100%; justify-content:center;">
                    <i class="fas fa-download"></i> Telecharger le modele CSV
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const fileInput = document.getElementById('csvFile');
    const fileInfo = document.getElementById('fileInfo');
    const fileName = document.getElementById('fileName');
    const uploadZone = document.getElementById('uploadZone');

    fileInput.addEventListener('change', function() {
        if (this.files && this.files.length > 0) {
            fileName.textContent = this.files[0].name;
            fileInfo.classList.add('show');
            uploadZone.style.borderColor = 'var(--om-success)';
            uploadZone.style.background = 'linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%)';
        } else {
            fileInfo.classList.remove('show');
            uploadZone.style.borderColor = '';
            uploadZone.style.background = '';
        }
    });

    ['dragenter', 'dragover'].forEach(evt => {
        uploadZone.addEventListener(evt, function(e) {
            e.preventDefault();
            this.classList.add('dragover');
        });
    });
    ['dragleave', 'drop'].forEach(evt => {
        uploadZone.addEventListener(evt, function(e) {
            e.preventDefault();
            this.classList.remove('dragover');
        });
    });

    const govSelect = document.getElementById('default_governorate');
    const citySelect = document.getElementById('default_city');

    govSelect.addEventListener('change', function() {
        const regionId = this.value;
        citySelect.innerHTML = '<option value="">-- Selectionner une ville --</option>';
        if (regionId) {
            fetch('/admin/get-cities?region_id=' + encodeURIComponent(regionId))
                .then(r => r.json())
                .then(cities => {
                    cities.forEach(city => {
                        const opt = document.createElement('option');
                        opt.value = city.id;
                        opt.textContent = city.name;
                        citySelect.appendChild(opt);
                    });
                })
                .catch(err => console.error('Erreur chargement villes:', err));
        }
    });
});
</script>
@endsection
