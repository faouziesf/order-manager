@extends('layouts.admin')

@section('title', 'Importation de commandes')

@section('css')
<style>
    .import-box {
        border: 2px dashed #ccc;
        padding: 20px;
        text-align: center;
        background-color: #f9f9f9;
        border-radius: 5px;
        margin-bottom: 20px;
    }
    
    .import-icon {
        font-size: 40px;
        color: #aaa;
        margin-bottom: 15px;
    }
    
    .file-input-zone {
        position: relative;
        padding: 30px;
        cursor: pointer;
        transition: all 0.3s;
    }
    
    .file-input-zone:hover {
        background-color: #f0f0f0;
    }
    
    .file-input-zone input[type="file"] {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        opacity: 0;
        cursor: pointer;
    }
    
    .example-table {
        width: 100%;
        margin: 20px 0;
        border-collapse: collapse;
    }
    
    .example-table th, .example-table td {
        border: 1px solid #ddd;
        padding: 8px;
        text-align: left;
    }
    
    .example-table th {
        background-color: #f8f9fc;
        font-weight: 500;
    }
    
    .advanced-options {
        margin-top: 20px;
        padding: 15px;
        background-color: #f8f9fc;
        border-radius: 5px;
        border: 1px solid #e3e6f0;
    }
    
    .file-selected {
        display: none;
        margin-top: 15px;
        font-weight: 500;
    }
</style>
@endsection

@section('content')
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Importation de commandes</h1>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">Importer des commandes depuis un fichier CSV</h6>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.import.csv') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    
                    <div class="import-box">
                        <div class="file-input-zone" id="fileInputZone">
                            <input type="file" name="csv_file" id="csvFile" accept=".csv, .txt" required />
                            <div class="import-icon">
                                <i class="fas fa-file-csv"></i>
                            </div>
                            <h5>Glissez votre fichier CSV ici</h5>
                            <p class="text-muted">ou cliquez pour sélectionner un fichier</p>
                            <div class="file-selected" id="fileSelected">
                                Fichier sélectionné: <span id="fileName"></span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group row">
                        <label for="delimiter" class="col-sm-3 col-form-label">Délimiteur</label>
                        <div class="col-sm-9">
                            <select class="form-control" id="delimiter" name="delimiter" required>
                                <option value="," selected>Virgule (,)</option>
                                <option value=";">Point-virgule (;)</option>
                                <option value="|">Barre verticale (|)</option>
                                <option value="\t">Tabulation</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-group row">
                        <label for="default_status" class="col-sm-3 col-form-label">Statut par défaut</label>
                        <div class="col-sm-9">
                            <select class="form-control" id="default_status" name="default_status" required>
                                <option value="nouvelle" selected>Nouvelle</option>
                                <option value="confirmée">Confirmée</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-group row">
                        <label for="default_priority" class="col-sm-3 col-form-label">Priorité par défaut</label>
                        <div class="col-sm-9">
                            <select class="form-control" id="default_priority" name="default_priority" required>
                                <option value="normale" selected>Normale</option>
                                <option value="urgente">Urgente</option>
                                <option value="vip">VIP</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="advanced-options">
                        <h6 class="mb-3">Options avancées</h6>
                        
                        <div class="form-group row">
                            <label for="default_governorate" class="col-sm-3 col-form-label">Gouvernorat par défaut</label>
                            <div class="col-sm-9">
                                <select class="form-control" id="default_governorate" name="default_governorate">
                                    <option value="">-- Sélectionner --</option>
                                    @foreach($regions as $region)
                                        <option value="{{ $region->id }}">{{ $region->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        
                        <div class="form-group row">
                            <label for="default_city" class="col-sm-3 col-form-label">Ville par défaut</label>
                            <div class="col-sm-9">
                                <select class="form-control" id="default_city" name="default_city">
                                    <option value="">-- Sélectionner d'abord un gouvernorat --</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-file-import mr-1"></i> Importer les commandes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Format du fichier CSV</h6>
            </div>
            <div class="card-body">
                <p>Votre fichier CSV doit contenir au moins une colonne de téléphone. Les noms de colonnes reconnus automatiquement sont:</p>
                
                <ul>
                    <li><strong>Téléphone:</strong> telephone, phone, tel</li>
                    <li><strong>Nom:</strong> nom, name, client</li>
                    <li><strong>Téléphone 2:</strong> telephone2, phone2, tel2</li>
                    <li><strong>Adresse:</strong> adresse, address</li>
                    <li><strong>Gouvernorat:</strong> gouvernorat, region</li>
                    <li><strong>Ville:</strong> ville, city</li>
                    <li><strong>Frais de livraison:</strong> frais_livraison, shipping, livraison</li>
                    <li><strong>Notes:</strong> notes, remarques, commentaire</li>
                </ul>
                
                <p><strong>Produits:</strong> Pour ajouter des produits, utilisez le format suivant:</p>
                
                <table class="example-table">
                    <tr>
                        <th>produit_1</th>
                        <th>quantite_1</th>
                        <th>prix_1</th>
                    </tr>
                    <tr>
                        <td>iPhone</td>
                        <td>1</td>
                        <td>1200</td>
                    </tr>
                </table>
                
                <p class="mt-3">Vous pouvez ajouter plusieurs produits en incrémentant le chiffre (produit_2, produit_3, etc.)</p>
                
                <div class="mt-4">
                    <a href="{{ asset('exemples/modele_import_commandes.csv') }}" class="btn btn-sm btn-outline-primary">
                        <i class="fas fa-download mr-1"></i> Télécharger modèle CSV
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Gestion de la sélection de fichier
        const fileInput = document.getElementById('csvFile');
        const fileSelected = document.getElementById('fileSelected');
        const fileName = document.getElementById('fileName');
        
        fileInput.addEventListener('change', function(e) {
            if (this.files && this.files.length > 0) {
                fileName.textContent = this.files[0].name;
                fileSelected.style.display = 'block';
            } else {
                fileSelected.style.display = 'none';
            }
        });
        
        // Chargement dynamique des villes en fonction du gouvernorat
        const governorateSelect = document.getElementById('default_governorate');
        const citySelect = document.getElementById('default_city');
        
        governorateSelect.addEventListener('change', function() {
            const regionId = this.value;
            
            // Réinitialiser le select des villes
            citySelect.innerHTML = '<option value="">-- Sélectionner une ville --</option>';
            
            if (regionId) {
                // Charger les villes via AJAX
                fetch(`/admin/get-cities?region_id=${regionId}`)
                    .then(response => response.json())
                    .then(cities => {
                        cities.forEach(city => {
                            const option = document.createElement('option');
                            option.value = city.id;
                            option.textContent = city.name;
                            citySelect.appendChild(option);
                        });
                    })
                    .catch(error => console.error('Erreur lors du chargement des villes:', error));
            }
        });
    });
</script>
@endsection