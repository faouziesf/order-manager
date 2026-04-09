<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>BL - {{ $order->tracking_number }}</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jsbarcode/3.11.5/JsBarcode.all.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcode-generator/1.4.4/qrcode.min.js"></script>
    @include('admin.kolixy._print-bl-styles')
</head>
<body>

<div class="controls">
    <button onclick="window.print()">🖨️ Imprimer le BL</button>
    <a href="{{ route('admin.kolixy.imprimer-bl') }}">← Retour</a>
</div>

@include('admin.kolixy._print-bl-ticket', ['order' => $order, 'admin' => $admin])

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Barcodes
        document.querySelectorAll('.barcode').forEach(function(el) {
            try {
                JsBarcode(el, el.dataset.code, { format: "CODE128", width: 2, height: 50, displayValue: false, margin: 10 });
            } catch(e) { console.error('Barcode error:', e); }
        });

        // QR Codes
        document.querySelectorAll('.qr-holder').forEach(function(el) {
            try {
                var url = el.dataset.url;
                var qr = qrcode(0, 'M');
                qr.addData(url);
                qr.make();
                var size = 110;
                var moduleCount = qr.getModuleCount();
                var moduleSize = Math.max(1, Math.floor(size / moduleCount));
                var canvas = document.createElement('canvas');
                canvas.width = size;
                canvas.height = size;
                var ctx = canvas.getContext('2d');
                var actualSize = moduleCount * moduleSize;
                var offset = (size - actualSize) / 2;
                ctx.fillStyle = "#ffffff";
                ctx.fillRect(0, 0, size, size);
                ctx.save();
                ctx.translate(offset, offset);
                qr.renderTo2dContext(ctx, moduleSize);
                ctx.restore();
                el.innerHTML = '';
                el.appendChild(canvas);
            } catch(e) { console.error('QR error:', e); }
        });

        setTimeout(function() { window.print(); }, 500);
    });
</script>
</body>
</html>
