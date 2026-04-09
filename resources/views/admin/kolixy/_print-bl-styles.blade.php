<style>
    @page { 
        size: A4; 
        margin: 0;
    }
    body { 
        font-family: 'Arial', sans-serif; 
        margin: 0; 
        padding: 0;
        background-color: #ffffff;
        direction: ltr;
    }
    .a4-page {
        width: 210mm;
        min-height: 297mm;
        margin: 0 auto;
        background: white;
        padding: 8mm 10mm;
        box-sizing: border-box;
    }

    /* Header */
    .bl-header {
        display: grid;
        grid-template-columns: 1fr 2fr 1fr;
        align-items: center;
        gap: 3mm;
        border-bottom: 2px solid #000;
        padding-bottom: 3mm;
        margin-bottom: 3mm;
    }
    .logo-section { text-align: left; }
    .logo { height: 50px; width: auto; object-fit: contain; }
    .barcode-section { text-align: center; }
    .bl-title { font-size: 16px; font-weight: bold; margin-bottom: 2mm; }
    .barcode { max-width: 100%; height: 50px; }
    .barcode-text {
        font-family: 'Courier New', monospace;
        font-size: 12px;
        font-weight: bold;
        margin-top: 2mm;
    }
    .qr-section { text-align: right; }
    .qr-section canvas { width: 80px; height: 80px; }

    /* Dispatch path */
    .dispatch-path {
        text-align: center;
        font-size: 12px;
        font-weight: bold;
        margin-bottom: 3mm;
        padding: 2mm;
        background-color: #f0f0f0;
        border: 1px solid #ccc;
        border-radius: 4px;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 5px;
    }
    .dispatch-line {
        display: inline-block;
        width: 80px;
        height: 2px;
        background-color: #4F46E5;
    }
    .dispatch-label {
        color: #4F46E5;
        font-size: 11px;
        text-transform: uppercase;
    }

    /* Info grid */
    .info-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 3mm;
        margin-bottom: 3mm;
        font-size: 10px;
    }
    .info-section {
        border: 1px solid #000;
        padding: 3mm;
    }
    .info-section-title {
        font-weight: bold;
        margin-bottom: 2mm;
        text-decoration: underline;
    }
    .info-line {
        margin-bottom: 1mm;
        line-height: 1.4;
    }
    .info-label { font-weight: 600; }

    /* Package details */
    .package-details {
        border: 1px solid #000;
        padding: 2mm;
        margin-bottom: 3mm;
        font-size: 10px;
    }
    .details-grid {
        display: grid;
        grid-template-columns: 2fr 1fr 1fr;
        gap: 2mm;
    }
    .detail-item { padding: 2mm; }
    .detail-label { font-weight: bold; text-transform: uppercase; }
    .instructions-section {
        margin-top: 3mm;
        padding: 3mm;
        border: 1px solid #ccc;
        background-color: #fffbf0;
        font-size: 9px;
    }
    .instruction-row {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        margin-bottom: 2mm;
    }
    .instruction-compact {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 2px 8px;
        background-color: white;
        border: 1px solid #e0e0e0;
        border-radius: 3px;
        white-space: nowrap;
    }
    .instruction-label { font-weight: bold; font-size: 9px; }
    .instruction-value { font-size: 9px; }
    .montant-section {
        border-top: 2px solid #000;
        margin-top: 3mm;
        padding-top: 3mm;
        text-align: right;
    }
    .montant-label { font-weight: bold; font-size: 13px; }
    .montant-value { font-weight: bold; font-size: 18px; color: #000; }
    .tva-notice { font-size: 9px; font-style: italic; color: #666; margin-top: 2mm; }

    /* Signature */
    .signature-section {
        margin: 3mm 0;
        text-align: center;
    }
    .signature-line {
        display: inline-block;
        width: 40mm;
        border-bottom: 1px solid #000;
        margin: 0 5mm;
    }

    /* Legal text (Arabic) */
    .legal-text {
        margin-top: 2mm;
        padding: 2mm;
        border: 1px solid #ccc;
        font-size: 7px;
        line-height: 1.4;
        direction: rtl;
        text-align: right;
        font-family: 'Arial', 'Tahoma', sans-serif;
    }
    .legal-title {
        font-weight: bold;
        text-align: center;
        font-size: 8px;
        margin-bottom: 1mm;
        text-decoration: underline;
    }
    .legal-section {
        margin-bottom: 1mm;
    }

    /* Footer */
    .footer {
        margin-top: 2mm;
        text-align: center;
        font-size: 9px;
        border-top: 1px solid #000;
        padding-top: 2mm;
    }
    .footer-logo { font-weight: bold; color: #4F46E5; font-size: 11px; }
    .footer-info { margin-top: 1mm; font-size: 8px; }

    /* Controls */
    .controls {
        text-align: center;
        padding: 10px;
        background: #f0f0f0;
    }
    .controls button {
        background: #4F46E5;
        color: white;
        border: none;
        padding: 10px 20px;
        border-radius: 5px;
        font-size: 14px;
        cursor: pointer;
        margin: 0 5px;
    }
    .controls button:hover { background: #4338CA; }
    .controls a {
        color: #4F46E5;
        text-decoration: none;
        font-size: 14px;
        margin-left: 10px;
        padding: 10px 20px;
        background: white;
        border: 1px solid #4F46E5;
        border-radius: 5px;
    }

    @media print {
        body { background-color: white; }
        .controls { display: none !important; }
        .a4-page {
            margin: 0;
            padding: 8mm 10mm;
            width: 210mm;
            min-height: 297mm;
            border: none;
            box-shadow: none;
            page-break-after: always;
        }
        .a4-page:last-child { page-break-after: avoid; }
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }
</style>
