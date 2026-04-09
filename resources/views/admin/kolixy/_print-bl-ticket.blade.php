@php
    $items = $order->items;
    $regionName = $order->region->name ?? '';
    $cityName = $order->city->name ?? '';
    $shopName = $admin->store_name ?? $admin->shop_name ?? $admin->name ?? 'N/A';
    $adminPhone = $admin->phone ?? 'N/A';
    $contentDescription = $items->map(fn($i) => ($i->product->name ?? $i->product_name ?? 'Produit') . ' x' . $i->quantity)->implode(', ') ?: 'Colis';
    $tvaAmount = $order->total_price * 0.07 / 1.07;
@endphp

<div class="a4-page">
    {{-- Header: Logo / Barcode / QR --}}
    <div class="bl-header">
        <div class="logo-section">
            <img src="{{ asset('img/kolixy.png') }}" alt="Kolixy" class="logo">
        </div>
        <div class="barcode-section">
            <div class="bl-title">Bon de livraison</div>
            <canvas class="barcode" data-code="{{ $order->tracking_number }}"></canvas>
            <div class="barcode-text">{{ $order->tracking_number }}</div>
        </div>
        <div class="qr-section">
            <div class="qr-holder" data-url="https://kolixy.com/track/{{ $order->tracking_number }}"></div>
        </div>
    </div>

    {{-- Dispatch path --}}
    <div class="dispatch-path">
        <span style="color: #059669; font-weight: bold;">{{ $shopName }}</span>
        <span style="font-size: 14px; margin: 0 3px;">>></span>
        <span class="dispatch-line"></span>
        <span class="dispatch-label">Dispatch</span>
        <span class="dispatch-line"></span>
        <span style="font-size: 14px; margin: 0 3px;">>></span>
        <span style="color: #dc2626; font-weight: bold;">{{ $regionName }}</span>
    </div>

    {{-- Sender / Recipient info --}}
    <div class="info-grid">
        {{-- Expéditeur --}}
        <div class="info-section">
            <div class="info-section-title">Nom de l'expéditeur:</div>
            <div class="info-line"><span class="info-label">{{ $shopName }}</span></div>
            <div class="info-line"><span class="info-label">Téléphone:</span> {{ $adminPhone }}</div>
        </div>

        {{-- Destinataire --}}
        <div class="info-section">
            <div class="info-section-title">NOM DE DESTINATAIRE:</div>
            <div class="info-line"><span class="info-label">{{ $order->customer_name }}</span></div>
            <div class="info-line"><span class="info-label">Téléphone:</span> {{ $order->customer_phone }}@if($order->customer_phone_2) / {{ $order->customer_phone_2 }}@endif</div>
            <div class="info-line"><span class="info-label">ADRESSE:</span> {{ $order->customer_address ?? 'N/A' }}@if($cityName), {{ $cityName }}@endif @if($regionName), {{ $regionName }}@endif</div>
        </div>
    </div>

    {{-- Package details --}}
    <div class="package-details">
        <div class="details-grid">
            <div class="detail-item">
                <div class="detail-label">Désignation-contenu du colis</div>
                <div>{{ $contentDescription }}</div>
            </div>
            <div class="detail-item">
                <div class="detail-label">Nb pièces</div>
                <div>{{ $items->sum('quantity') }}</div>
            </div>
            <div class="detail-item">
                <div class="detail-label">Montant TTC</div>
                <div>{{ number_format($order->total_price, 3) }} DT</div>
            </div>
        </div>

        {{-- Items table --}}
        @if($items->count() > 0)
        <div class="instructions-section" style="background-color: #f9f9f9;">
            <div class="detail-label" style="margin-bottom: 2mm; font-size: 10px;">Détails des articles:</div>
            <table style="width:100%; border-collapse:collapse; font-size:9px;">
                <tr style="border-bottom:1px solid #ddd;">
                    <th style="text-align:left; padding:2mm;">Produit</th>
                    <th style="text-align:center; padding:2mm;">Qté</th>
                    <th style="text-align:right; padding:2mm;">Prix</th>
                </tr>
                @foreach($items as $item)
                <tr style="border-bottom:1px solid #eee;">
                    <td style="padding:2mm;">
                        {{ $item->product->name ?? $item->product_name ?? 'Produit' }}
                        @if($item->variant_description)
                            <span style="color:#666;"> — {{ $item->variant_description }}</span>
                        @endif
                    </td>
                    <td style="text-align:center; padding:2mm; font-weight:bold;">{{ $item->quantity }}</td>
                    <td style="text-align:right; padding:2mm;">{{ number_format($item->price ?? $item->unit_price ?? 0, 3) }} DT</td>
                </tr>
                @endforeach
            </table>
        </div>
        @endif

        {{-- Instructions --}}
        <div class="instructions-section">
            <div class="detail-label" style="margin-bottom: 2mm; font-size: 10px;">Instructions diverses:</div>
            <div class="instruction-row">
                <span class="instruction-compact">
                    <span class="instruction-label">Paiement:</span>
                    <span class="instruction-value">Espèces 💵</span>
                </span>
                <span class="instruction-compact">
                    <span class="instruction-label">Ouverture:</span>
                    <span class="instruction-value">OUI 📂</span>
                </span>
            </div>

            @if($order->notes || $order->delivery_notes)
            <div style="margin-top: 2mm; padding: 2mm; background-color: white; border: 1px solid #e0e0e0; border-radius: 3px;">
                <span class="instruction-label">Commentaire:</span>
                <span class="instruction-value" style="font-style: italic; margin-left: 5px;">{{ $order->delivery_notes ?? $order->notes }}</span>
            </div>
            @endif
        </div>

        {{-- Montant --}}
        <div class="montant-section">
            <div class="montant-label">MONTANT À ENCAISSER:</div>
            <div class="montant-value">{{ number_format($order->total_price, 3) }} DT</div>
            <div class="tva-notice">dont TVA 7% : {{ number_format($tvaAmount, 3) }} DT</div>
        </div>
    </div>

    {{-- Signature --}}
    <div class="signature-section">
        <span>Signature expéditeur: <span class="signature-line"></span></span>
    </div>

    {{-- Texte légal en arabe --}}
    <div class="legal-text">
        <div class="legal-title">عقد / توكيل نقل البضائع غير الخاضعة لإجراءات خاصة لحساب الغير عبر الطرقات البرية</div>
        
        <div class="legal-section">
            <strong>بين الطرفين المسميين أسفله:</strong>
        </div>
        
        <div class="legal-section">
            <strong>أولا</strong> {{ $shopName }} بصفته البائع والعارض لسلع
        </div>
        
        <div class="legal-section">
            <strong>ثانيا</strong> شركة KOLIXY الكائن مقرها الاجتماعي بـ تونس وصاحبة الوكالات الفرعية وذات النشاط الأصلي المصرح به لدى السلط الإدارية المعنية "نقل البضائع غير الخاضعة لإجراءات خاصة لحساب الغير عبر الطرقات البرية" بصفتها ناقلة لحساب الغير
        </div>
        
        <div class="legal-section">
            حيث ان الطرف الأول يعرض للبيع على موقعه الرقمي الافتراضي أو بقاعة العرض الخاصة به بضاعة أو منتوجات للحرفاء، وحيث قام الحريف الشاري بإصدار طلبية لدى البائع، واتفقا على شروطها من حيث الثمن والبضاعة وكلفة النقل، وحيث تتولى شركة KOLIXY بصفتها الناقل المتعاقد مع البائع مهمة إيصال الطلبية الى الشاري في شكل طرد محكم الغلق يحتوي على كل البيانات الخاصة بوصف البضاعة وثمنها وبيانات الحريف وعنوان التسليم في الأجل المتفق عليها، كما تتولى الشركة الناقلة تسليم الطرد للحريف وتتسلم نيابة عن البائع المبلغ المسجل على الطرد بما في ذلك كلفة النقل.
        </div>
        
        <div class="legal-section">
            <strong>وبناء على ذلك اتفق الطرفان على:</strong>
        </div>
        
        <div class="legal-section">
            • <strong>الفصل الأول:</strong> تعتبر المقدمة جزء لا يتجزأ من العقد / توكيل
        </div>
        <div class="legal-section">
            • <strong>الفصل الثاني:</strong> تتولى الشركة الناقلة المحافظة على الطرود وإيصالها للحرفاء على الحالة التي تسلمتهم عليها وذلك بالعنوان المحدد من طرف البائع بكامل تراب الجمهورية
        </div>
        <div class="legal-section">
            • <strong>الفصل الثالث:</strong> تقوم الشركة الناقلة بتسليم الطرد إلى الحريف بالعنوان المحدد من طرف البائع
        </div>
        <div class="legal-section">
            • <strong>الفصل الرابع:</strong> الشركة الناقلة لا تتحمل أية مسؤولية تتعلق بنوع ومواصفات ومحتوى البضاعة داخل الطرود
        </div>
        <div class="legal-section">
            • <strong>الفصل الخامس:</strong> يفوض الطرف الأول بصفته البائع الطرف الثاني بصفته ناقل، استلام ثمن الطرد وكلفة النقل
        </div>
        <div class="legal-section">
            • <strong>الفصل السادس:</strong> ان امضاء وصل الاستلام من طرف الحريف يعتبر حجة في تسلمه للطرد
        </div>
        <div class="legal-section">
            • <strong>الفصل السابع:</strong> في صورة توفر حالة من حالات القوة القاهرة يلجا الطرفان للتعويض المادي
        </div>
        <div class="legal-section">
            • <strong>الفصل الثامن:</strong> يلتزم الطرفان بفض كل خلاف يطرأ بينهما بشكل ودي، يتم الالتجاء لمحاكم تونس لفض النزاع
        </div>
        
        <div class="legal-section" style="margin-top: 2mm;">
            <strong>مراجع قانونية تونسية:</strong><br>
            • القانون عدد 33 لسنة 2004 مؤرخ في 19 أفريل 2004، الفصل 37<br>
            • أحكام الفصل 18 من مجلة الأداء على القيمة المضافة المتعلق بالوثائق المصاحبة لعملية نقل البضائع في إطار عملية البيع عن بعد من خلال موقع واب n°921842355000
        </div>
    </div>

    {{-- Footer --}}
    <div class="footer">
        <div class="footer-logo">
            <img src="{{ asset('img/kolixy.png') }}" alt="Kolixy" style="height: 25px; vertical-align: middle;">
        </div>
        <div class="footer-info">
            Commande #{{ $order->id }} — Suivi: {{ $order->tracking_number }} — {{ now()->format('d/m/Y H:i') }}
        </div>
    </div>
</div>
