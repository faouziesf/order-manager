@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')
    <h1>Tableau de bord</h1>
@stop

@section('content')
    <div class="container-fluid">
        
        <!-- Messages Flash -->
        @if(session('success'))
            <div class="alert alert-success alert-dismissible">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                <h5><i class="icon fas fa-check"></i> Succès!</h5>
                {{ session('success') }}
            </div>
        @endif
        
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                <h5><i class="icon fas fa-ban"></i> Erreur!</h5>
                {{ session('error') }}
            </div>
        @endif
        
        <!-- Contenu principal -->
        @yield('main_content')
    </div>
@stop

@section('css')
    <!-- Styles supplémentaires -->
    <style>
        .content-wrapper {
            background-color: #f4f6f9;
        }
        
        .card {
            box-shadow: 0 0 1px rgba(0,0,0,.125), 0 1px 3px rgba(0,0,0,.2);
            margin-bottom: 1rem;
        }
        
        .table td {
            vertical-align: middle;
        }
    </style>
@stop

@section('js')
    <!-- Animation de chargement de page -->
    <script>
        $(document).ready(function() {
            // Ajouter un écouteur pour les clics sur les liens qui naviguent dans l'application
            $(document).on('click', 'a:not([target="_blank"]):not([href^="#"]):not([href^="javascript"])', function(e) {
                // Vérifier que le lien est interne
                var href = $(this).attr('href');
                if (!href || href.indexOf('http') === 0) return true;
                
                // Afficher l'animation de chargement
                showLoading();
            });
            
            // Ajouter un écouteur pour les soumissions de formulaire
            $(document).on('submit', 'form', function() {
                showLoading();
            });
            
            // Fonction pour afficher l'animation de chargement
            function showLoading() {
                // Créer l'élément de préchargement s'il n'existe pas déjà
                if ($('#page-loader').length === 0) {
                    $('body').append(
                        '<div id="page-loader" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(255,255,255,0.8); z-index: 9999; display: flex; justify-content: center; align-items: center;">' +
                        '<div style="text-align: center;">' +
                        '<div style="font-size: 40px; color: #007bff; margin-bottom: 10px;"><i class="fas fa-shopping-cart"></i></div>' +
                        '<h4 style="color: #007bff; margin-bottom: 15px;">Order Manager</h4>' +
                        '<div class="spinner-border text-primary" role="status"><span class="sr-only">Chargement...</span></div>' +
                        '</div>' +
                        '</div>'
                    );
                } else {
                    $('#page-loader').fadeIn(300);
                }
            }
            
            // Masquer l'animation de chargement après le chargement complet de la page
            $(window).on('load', function() {
                $('#page-loader').fadeOut(300, function() {
                    $(this).remove();
                });
            });
        });
    </script>
    
    <!-- Scripts spécifiques à la page -->
    @stack('page_scripts')
@stop