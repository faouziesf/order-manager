<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Manifeste d'Enlèvement #{{ $pickup->id }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style> @media print { .no-print { display: none; } } </style>
</head>
<body>
    <div class="container my-4">
        <h1 class="mb-4">Manifeste d'Enlèvement #{{ $pickup->id }}</h1>
        <div class="card mb-4"><div class="card-body">
            <p><strong>Transporteur:</strong> {{ $pickup->carrier_name }}</p>
            <p><strong>Date d'enlèvement:</strong> {{ $pickup->pickup_date->format('d/m/Y') }}</p>
            <p><strong>Total Colis:</strong> {{ $pickup->total_shipments }}</p>
            <p><strong>Total COD:</strong> {{ number_format($pickup->total_cod_amount, 3) }} TND</p>
        </div></div>
        <table class="table table-bordered">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>N° Suivi</th>
                    <th>Destinataire</th>
                    <th>Ville</th>
                    <th>Montant COD</th>
                    <th>Signature</th>
                </tr>
            </thead>
            <tbody>
                @foreach($pickup->shipments as $shipment)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td><strong>{{ $shipment->pos_barcode ?? 'N/A' }}</strong></td>
                    <td>{{ $shipment->recipient_info['name'] ?? 'N/A' }}</td>
                    <td>{{ $shipment->recipient_info['city'] ?? 'N/A' }}</td>
                    <td>{{ number_format($shipment->cod_amount, 3) }} TND</td>
                    <td style="height: 50px;"></td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <div class="text-center mt-4 no-print">
            <button onclick="window.print()" class="btn btn-primary">Imprimer</button>
        </div>
    </div>
</body>
</html>