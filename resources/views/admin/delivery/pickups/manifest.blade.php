<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manifeste d'Enlèvement #{{ $manifestData['pickup_id'] }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
            margin: 0;
            padding: 20px;
        }
        
        .header {
            border-bottom: 3px solid #007bff;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        
        .header-content {
            display: table;
            width: 100%;
        }
        
        .header-left {
            display: table-cell;
            vertical-align: top;
            width: 60%;
        }
        
        .header-right {
            display: table-cell;
            vertical-align: top;
            width: 40%;
            text-align: right;
        }
        
        .logo {
            max-width: 150px;
            max-height: 75px;
            margin-bottom: 10px;
        }
        
        .company-name {
            font-size: 24px;
            font-weight: bold;
            color: #007bff;
            margin-bottom: 5px;
        }
        
        .document-title {
            font-size: 20px;
            font-weight: bold;
            color: #007bff;
            margin-bottom: 10px;
        }
        
        .document-info {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        
        .info-grid {
            display: table;
            width: 100%;
        }
        
        .info-row {
            display: table-row;
        }
        
        .info-cell {
            display: table-cell;
            padding: 5px 10px 5px 0;
            vertical-align: top;
        }
        
        .info-label {
            font-weight: bold;
            color: #666;
            width: 150px;
        }
        
        .info-value {
            color: #333;
        }
        
        .section {
            margin-bottom: 25px;
        }
        
        .section-title {
            font-size: 16px;
            font-weight: bold;
            color: #007bff;
            border-bottom: 2px solid #e9ecef;
            padding-bottom: 5px;
            margin-bottom: 15px;
        }
        
        .pickup-address {
            background: #e7f3ff;
            padding: 15px;
            border-radius: 5px;
            border-left: 4px solid #007bff;
        }
        
        .address-name {
            font-weight: bold;
            font-size: 14px;
            color: #007bff;
            margin-bottom: 5px;
        }
        
        .address-contact {
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .shipments-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        
        .shipments-table th,
        .shipments-table td {
            border: 1px solid #dee2e6;
            padding: 8px;
            text-align: left;
        }
        
        .shipments-table th {
            background: #007bff;
            color: white;
            font-weight: bold;
            font-size: 11px;
        }
        
        .shipments-table td {
            font-size: 10px;
        }
        
        .shipments-table .text-center {
            text-align: center;
        }
        
        .shipments-table .text-right {
            text-align: right;
        }
        
        .total-row {
            background: #f8f9fa;
            font-weight: bold;
        }
        
        .summary {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-top: 20px;
        }
        
        .summary-grid {
            display: table;
            width: 100%;
        }
        
        .summary-row {
            display: table-row;
        }
        
        .summary-cell {
            display: table-cell;
            padding: 5px 15px 5px 0;
            vertical-align: top;
        }
        
        .summary-label {
            font-weight: bold;
            color: #666;
            width: 200px;
        }
        
        .summary-value {
            font-weight: bold;
            color: #007bff;
            font-size: 14px;
        }
        
        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #dee2e6;
            font-size: 10px;
            color: #666;
        }
        
        .signature-section {
            margin-top: 40px;
            display: table;
            width: 100%;
        }
        
        .signature-box {
            display: table-cell;
            width: 45%;
            border: 1px solid #dee2e6;
            padding: 20px;
            margin: 0 2.5%;
            text-align: center;
            vertical-align: top;
        }
        
        .signature-title {
            font-weight: bold;
            margin-bottom: 50px;
            color: #007bff;
        }
        
        .signature-line {
            border-top: 1px solid #333;
            margin-top: 40px;
            padding-top: 5px;
            font-size: 10px;
        }
        
        .status-badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .status-draft {
            background: #6c757d;
            color: white;
        }
        
        .status-validated {
            background: #007bff;
            color: white;
        }
        
        .status-picked_up {
            background: #28a745;
            color: white;
        }
        
        .status-problem {
            background: #dc3545;
            color: white;
        }
        
        .barcode {
            font-family: 'Courier New', monospace;
            font-weight: bold;
            background: #f8f9fa;
            padding: 2px 4px;
            border-radius: 3px;
        }
        
        .page-break {
            page-break-before: always;
        }
        
        @media print {
            body {
                margin: 0;
                padding: 15px;
            }
            
            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body>
    <!-- En-tête du manifeste -->
    <div class="header">
        <div class="header-content">
            <div class="header-left">
                @if(!empty($manifestData['logo']['path']))
                    <img src="{{ $manifestData['logo']['path'] }}" alt="Logo" class="logo">
                @endif
                <div class="company-name">{{ $manifestData['company_name'] ?? 'Mon Entreprise' }}</div>
                <div>{{ $manifestData['company_address'] ?? '' }}</div>
                @if(!empty($manifestData['company_phone']))
                    <div>Tél: {{ $manifestData['company_phone'] }}</div>
                @endif
            </div>
            <div class="header-right">
                <div class="document-title">MANIFESTE D'ENLÈVEMENT</div>
                <div><strong>#{{ $manifestData['pickup_id'] }}</strong></div>
                <div>{{ $manifestData['generated_at'] }}</div>
            </div>
        </div>
    </div>

    <!-- Informations générales -->
    <div class="document-info">
        <div class="info-grid">
            <div class="info-row">
                <div class="info-cell info-label">N° Enlèvement:</div>
                <div class="info-cell info-value">#{{ $manifestData['pickup_id'] }}</div>
                <div class="info-cell info-label">Transporteur:</div>
                <div class="info-cell info-value">{{ $manifestData['carrier_name'] }}</div>
            </div>
            <div class="info-row">
                <div class="info-cell info-label">Date de création:</div>
                <div class="info-cell info-value">{{ $manifestData['pickup_created_at'] }}</div>
                <div class="info-cell info-label">Date d'enlèvement:</div>
                <div class="info-cell info-value">{{ $manifestData['pickup_date'] ?? 'Non définie' }}</div>
            </div>
            <div class="info-row">
                <div class="info-cell info-label">Statut:</div>
                <div class="info-cell info-value">
                    <span class="status-badge status-{{ $manifestData['pickup_status'] }}">
                        {{ $manifestData['pickup_status_label'] }}
                    </span>
                </div>
                <div class="info-cell info-label">Validé le:</div>
                <div class="info-cell info-value">{{ $manifestData['pickup_validated_at'] ?? 'Non validé' }}</div>
            </div>
        </div>
    </div>

    <!-- Adresse d'enlèvement -->
    @if(!empty($manifestData['pickup_address']))
        <div class="section">
            <div class="section-title">Adresse d'Enlèvement</div>
            <div class="pickup-address">
                <div class="address-name">{{ $manifestData['pickup_address']['name'] }}</div>
                <div class="address-contact">Contact: {{ $manifestData['pickup_address']['contact_name'] }}</div>
                <div>{{ $manifestData['pickup_address']['address'] }}</div>
                @if($manifestData['pickup_address']['city'] || $manifestData['pickup_address']['postal_code'])
                    <div>
                        {{ $manifestData['pickup_address']['city'] }}
                        {{ $manifestData['pickup_address']['postal_code'] }}
                    </div>
                @endif
                <div>
                    <strong>Tél:</strong> {{ $manifestData['pickup_address']['phone'] }}
                    @if($manifestData['pickup_address']['email'])
                        | <strong>Email:</strong> {{ $manifestData['pickup_address']['email'] }}
                    @endif
                </div>
            </div>
        </div>
    @endif

    <!-- Liste des expéditions -->
    <div class="section">
        <div class="section-title">Liste des Expéditions ({{ count($manifestData['shipments']) }} colis)</div>
        
        @if(!empty($manifestData['shipments']))
            <table class="shipments-table">
                <thead>
                    <tr>
                        <th style="width: 8%;">#</th>
                        <th style="width: 12%;">Commande</th>
                        <th style="width: 15%;">Code Suivi</th>
                        <th style="width: 20%;">Client</th>
                        <th style="width: 15%;">Téléphone</th>
                        <th style="width: 20%;">Destination</th>
                        <th style="width: 10%;" class="text-right">Montant (DT)</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($manifestData['shipments'] as $index => $shipment)
                        <tr>
                            <td class="text-center">{{ $index + 1 }}</td>
                            <td class="text-center">
                                <strong>#{{ $shipment['order_id'] }}</strong>
                                @if($shipment['order_number'])
                                    <br><small>{{ $shipment['order_number'] }}</small>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($shipment['pos_barcode'])
                                    <div class="barcode">{{ $shipment['pos_barcode'] }}</div>
                                @else
                                    <em>En attente</em>
                                @endif
                            </td>
                            <td>
                                <strong>{{ $shipment['customer_name'] ?: 'N/A' }}</strong>
                            </td>
                            <td class="text-center">
                                {{ $shipment['customer_phone'] ?: 'N/A' }}
                            </td>
                            <td>
                                <div>{{ Str::limit($shipment['customer_address'] ?: 'N/A', 40) }}</div>
                                @if($shipment['customer_city'])
                                    <small><em>{{ $shipment['customer_city'] }}</em></small>
                                @endif
                            </td>
                            <td class="text-right">
                                <strong>{{ number_format($shipment['value'] ?: 0, 3) }}</strong>
                            </td>
                        </tr>
                    @endforeach
                    
                    <!-- Ligne de total -->
                    <tr class="total-row">
                        <td colspan="6" class="text-right"><strong>TOTAL:</strong></td>
                        <td class="text-right">
                            <strong>{{ number_format($manifestData['total_value'], 3) }} DT</strong>
                        </td>
                    </tr>
                </tbody>
            </table>
        @else
            <div style="text-align: center; padding: 40px; color: #666;">
                <em>Aucune expédition dans cet enlèvement</em>
            </div>
        @endif
    </div>

    <!-- Résumé -->
    <div class="summary">
        <div class="section-title">Résumé de l'Enlèvement</div>
        <div class="summary-grid">
            <div class="summary-row">
                <div class="summary-cell summary-label">Nombre total d'expéditions:</div>
                <div class="summary-cell summary-value">{{ $manifestData['total_shipments'] }}</div>
                <div class="summary-cell summary-label">Expéditions validées:</div>
                <div class="summary-cell summary-value">{{ $manifestData['validated_shipments'] }}</div>
            </div>
            <div class="summary-row">
                <div class="summary-cell summary-label">Valeur totale des colis:</div>
                <div class="summary-cell summary-value">{{ number_format($manifestData['total_value'], 3) }} DT</div>
                <div class="summary-cell summary-label">Valeur moyenne par colis:</div>
                <div class="summary-cell summary-value">{{ number_format($manifestData['average_value'], 3) }} DT</div>
            </div>
            @if($manifestData['total_cod_amount'] > 0)
                <div class="summary-row">
                    <div class="summary-cell summary-label">Montant COD total:</div>
                    <div class="summary-cell summary-value">{{ number_format($manifestData['total_cod_amount'], 3) }} DT</div>
                    <div class="summary-cell summary-label"></div>
                    <div class="summary-cell summary-value"></div>
                </div>
            @endif
        </div>
    </div>

    <!-- Instructions spéciales -->
    @if(!empty($manifestData['special_instructions']))
        <div class="section">
            <div class="section-title">Instructions Spéciales</div>
            <div style="background: #fff3cd; padding: 15px; border-radius: 5px; border-left: 4px solid #ffc107;">
                {{ $manifestData['special_instructions'] }}
            </div>
        </div>
    @endif

    <!-- Section signatures -->
    <div class="signature-section">
        <div class="signature-box">
            <div class="signature-title">EXPÉDITEUR</div>
            <div style="margin: 20px 0;">
                Nom: ________________________________
            </div>
            <div style="margin: 20px 0;">
                Date: {{ date('d/m/Y') }} Heure: _________
            </div>
            <div class="signature-line">
                Signature et cachet
            </div>
        </div>
        
        <div class="signature-box">
            <div class="signature-title">TRANSPORTEUR</div>
            <div style="margin: 20px 0;">
                Nom: ________________________________
            </div>
            <div style="margin: 20px 0;">
                Date: _________ Heure: _________
            </div>
            <div class="signature-line">
                Signature et cachet
            </div>
        </div>
    </div>

    <!-- Pied de page -->
    <div class="footer">
        <div style="display: table; width: 100%;">
            <div style="display: table-cell; width: 50%;">
                <strong>Manifeste généré le:</strong> {{ $manifestData['generated_at'] }}
                <br><strong>Par:</strong> {{ $manifestData['generated_by'] }}
            </div>
            <div style="display: table-cell; width: 50%; text-align: right;">
                <strong>Document confidentiel</strong>
                <br>Enlèvement #{{ $manifestData['pickup_id'] }} - {{ $manifestData['carrier_name'] }}
            </div>
        </div>
        
        <div style="text-align: center; margin-top: 15px; font-size: 9px; color: #999;">
            Ce manifeste certifie la remise des colis listés ci-dessus au transporteur pour livraison.
            <br>Toute réclamation doit être formulée dans les 48h suivant la remise.
        </div>
    </div>
</body>
</html>