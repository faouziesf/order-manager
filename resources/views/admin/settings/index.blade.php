@extends('layouts.admin')

@section('title', 'Paramètres du système')

@section('css')
<style>
    .nav-tabs .nav-link {
        color: #4e73df;
    }
    
    .nav-tabs .nav-link.active {
        color: #4e73df;
        font-weight: bold;
        border-color: #4e73df #dee2e6 #fff;
    }
    
    .param-card {
        background-color: #f8f9fc;
        border-radius: 0.35rem;
        border: 1px solid #e3e6f0;
        padding: 15px;
        margin-bottom: 15px;
    }
    
    .param-card h6 {
        color: #4e73df;
        margin-bottom: 15px;
        border-bottom: 1px solid #e3e6f0;
        padding-bottom: 10px;
    }
    
    .param-description {
        font-size: 0.8rem;
        color: #858796;
        margin-top: 5px;
    }
</style>
@endsection

@section('content')
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Paramètres du système</h1>
</div>

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <ul class="nav nav-tabs card-header-tabs" id="queueTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <a class="nav-link active" id="standard-tab" data-bs-toggle="tab" href="#standard" role="tab" aria-controls="standard" aria-selected="true">
                    <i class="fas fa-list mr-1"></i> File Standard
                </a>
            </li>
            <li class="nav-item" role="presentation">
                <a class="nav-link" id="dated-tab" data-bs-toggle="tab" href="#dated" role="tab" aria-controls="dated" aria-selected="false">
                    <i class="fas fa-calendar-alt mr-1"></i> File Datée
                </a>
            </li>
            <li class="nav-item" role="presentation">
                <a class="nav-link" id="old-tab" data-bs-toggle="tab" href="#old" role="tab" aria-controls="old" aria-selected="false">
                    <i class="fas fa-history mr-1"></i> File Ancienne
                </a>
            </li>
        </ul>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.settings.store') }}" method="POST">
            @csrf
            
            <div class="tab-content" id="queueTabsContent">
                <!-- File Standard -->
                <div class="tab-pane fade show active" id="standard" role="tabpanel" aria-labelledby="standard-tab">
                    <h5 class="mb-3">Paramètres de la file standard</h5>
                    <p class="text-muted mb-4">
                        Ces paramètres contrôlent le comportement des nouvelles commandes qui n'ont pas encore atteint le maximum de tentatives.
                    </p>
                    
                    <div class="row">
                        <div class="col-md-4">
                            <div class="param-card">
                                <h6>Tentatives journalières</h6>
                                <div class="form-group">
                                    <input type="number" class="form-control" id="standard_max_daily_attempts" name="standard_max_daily_attempts" 
                                           value="{{ $standardSettings['max_daily_attempts'] }}" min="1" max="10" required>
                                    <div class="param-description">
                                        Nombre maximum de tentatives par jour pour une commande
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="param-card">
                                <h6>Délai entre tentatives</h6>
                                <div class="form-group">
                                    <input type="number" class="form-control" id="standard_delay_hours" name="standard_delay_hours" 
                                           value="{{ $standardSettings['delay_hours'] }}" min="0.5" max="12" step="0.5" required>
                                    <div class="param-description">
                                        Délai en heures entre deux tentatives pour une même commande
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="param-card">
                                <h6>Tentatives totales</h6>
                                <div class="form-group">
                                    <input type="number" class="form-control" id="standard_max_total_attempts" name="standard_max_total_attempts" 
                                           value="{{ $standardSettings['max_total_attempts'] }}" min="1" max="30" required>
                                    <div class="param-description">
                                        Nombre maximum de tentatives au total avant passage en file ancienne
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- File Datée -->
                <div class="tab-pane fade" id="dated" role="tabpanel" aria-labelledby="dated-tab">
                    <h5 class="mb-3">Paramètres de la file datée</h5>
                    <p class="text-muted mb-4">
                        Ces paramètres contrôlent le comportement des commandes qui ont été programmées à une date spécifique.
                    </p>
                    
                    <div class="row">
                        <div class="col-md-4">
                            <div class="param-card">
                                <h6>Tentatives journalières</h6>
                                <div class="form-group">
                                    <input type="number" class="form-control" id="dated_max_daily_attempts" name="dated_max_daily_attempts" 
                                           value="{{ $datedSettings['max_daily_attempts'] }}" min="1" max="10" required>
                                    <div class="param-description">
                                        Nombre maximum de tentatives par jour pour une commande datée
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="param-card">
                                <h6>Délai entre tentatives</h6>
                                <div class="form-group">
                                    <input type="number" class="form-control" id="dated_delay_hours" name="dated_delay_hours" 
                                           value="{{ $datedSettings['delay_hours'] }}" min="0.5" max="12" step="0.5" required>
                                    <div class="param-description">
                                        Délai en heures entre deux tentatives pour une même commande datée
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="param-card">
                                <h6>Tentatives totales</h6>
                                <div class="form-group">
                                    <input type="number" class="form-control" id="dated_max_total_attempts" name="dated_max_total_attempts" 
                                           value="{{ $datedSettings['max_total_attempts'] }}" min="1" max="30" required>
                                    <div class="param-description">
                                        Nombre maximum de tentatives au total avant passage en file ancienne
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- File Ancienne -->
                <div class="tab-pane fade" id="old" role="tabpanel" aria-labelledby="old-tab">
                    <h5 class="mb-3">Paramètres de la file ancienne</h5>
                    <p class="text-muted mb-4">
                        Ces paramètres contrôlent le comportement des commandes qui ont dépassé le nombre maximal de tentatives standard.
                    </p>
                    
                    <div class="row">
                        <div class="col-md-4">
                            <div class="param-card">
                                <h6>Tentatives journalières</h6>
                                <div class="form-group">
                                    <input type="number" class="form-control" id="old_max_daily_attempts" name="old_max_daily_attempts" 
                                        value="{{ $oldSettings['max_daily_attempts'] }}" min="1" max="10" required>
                                    <div class="param-description">
                                        Nombre maximum de tentatives par jour pour une commande ancienne
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="param-card">
                                <h6>Délai entre tentatives</h6>
                                <div class="form-group">
                                    <input type="number" class="form-control" id="old_delay_hours" name="old_delay_hours" 
                                        value="{{ $oldSettings['delay_hours'] }}" min="0.5" max="12" step="0.5" required>
                                    <div class="param-description">
                                        Délai en heures entre deux tentatives pour une même commande ancienne
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="param-card">
                                <h6>Tentatives totales</h6>
                                <div class="form-group">
                                    <input type="number" class="form-control" id="old_max_total_attempts" name="old_max_total_attempts" 
                                        value="{{ $oldSettings['max_total_attempts'] }}" min="0" max="30">
                                    <div class="param-description">
                                        Nombre maximum de tentatives au total (0 = illimité)
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="mt-4">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save mr-1"></i> Enregistrer les paramètres
                </button>
            </div>
        </form>
    </div>
</div>
@endsection