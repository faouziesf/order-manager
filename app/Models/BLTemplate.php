<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BLTemplate extends Model
{
    use HasFactory;

    protected $table = 'bl_templates';

    protected $fillable = [
        'admin_id',
        'carrier_slug',
        'template_name',
        'layout_config',
        'is_default',
        'is_active',
    ];

    protected $casts = [
        'layout_config' => 'array',
        'is_default' => 'boolean',
        'is_active' => 'boolean',
    ];

    // ========================================
    // RELATIONS
    // ========================================
    
    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class);
    }

    // ========================================
    // ACCESSORS
    // ========================================
    
    public function getDisplayNameAttribute(): string
    {
        $carrier = $this->carrier_slug ? ucfirst($this->carrier_slug) : 'Général';
        return "{$carrier} - {$this->template_name}";
    }

    public function getStatusBadgeClassAttribute(): string
    {
        if (!$this->is_active) {
            return 'badge-secondary';
        }
        
        return $this->is_default ? 'badge-primary' : 'badge-success';
    }

    public function getStatusLabelAttribute(): string
    {
        if (!$this->is_active) {
            return 'Inactif';
        }
        
        return $this->is_default ? 'Par défaut' : 'Actif';
    }

    public function getCarrierDisplayNameAttribute(): string
    {
        return match($this->carrier_slug) {
            'fparcel' => 'Fparcel',
            'dhl' => 'DHL',
            'aramex' => 'Aramex',
            'tunisia_post' => 'Poste Tunisienne',
            null => 'Général',
            default => ucfirst($this->carrier_slug),
        };
    }

    // ========================================
    // MÉTHODES
    // ========================================
    
    public function setAsDefault(): void
    {
        // Désactiver tous les autres templates par défaut pour cet admin et transporteur
        static::where('admin_id', $this->admin_id)
            ->where('carrier_slug', $this->carrier_slug)
            ->where('id', '!=', $this->id)
            ->update(['is_default' => false]);

        // Activer celui-ci comme défaut
        $this->update(['is_default' => true, 'is_active' => true]);
    }

    public function duplicate(string $newName): self
    {
        $duplicate = $this->replicate();
        $duplicate->template_name = $newName;
        $duplicate->is_default = false;
        $duplicate->save();

        return $duplicate;
    }

    public function mergeWithDefaults(array $config = []): array
    {
        $defaults = self::getDefaultLayoutConfig();
        return array_merge_recursive($defaults, $this->layout_config ?? [], $config);
    }

    public function validateConfig(): array
    {
        $errors = [];
        $config = $this->layout_config ?? [];
        
        // Validation des champs obligatoires
        if (empty($config['page'])) {
            $errors[] = 'Configuration de page manquante';
        }
        
        if (empty($config['fields'])) {
            $errors[] = 'Configuration des champs manquante';
        }
        
        // Validation des champs essentiels
        $requiredFields = ['customer_name', 'customer_address', 'tracking_number'];
        foreach ($requiredFields as $field) {
            if (empty($config['fields'][$field]['enabled'])) {
                $errors[] = "Le champ '{$field}' doit être activé";
            }
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors,
        ];
    }

    public function getFieldsForCarrier(string $carrier): array
    {
        $baseFields = $this->getDefaultLayoutConfig()['fields'];
        
        // Ajouter des champs spécifiques selon le transporteur
        if ($carrier === 'fparcel') {
            $baseFields['cod_amount'] = [
                'enabled' => true,
                'label' => 'Montant COD',
                'x' => 300,
                'y' => 200,
                'font_size' => 12,
                'font_weight' => 'bold',
            ];
        }
        
        return $baseFields;
    }

    // ========================================
    // MÉTHODES STATIQUES
    // ========================================
    
    public static function getDefaultLayoutConfig(): array
    {
        return [
            'page' => [
                'size' => 'A4',
                'orientation' => 'portrait',
                'margin' => [
                    'top' => 20,
                    'right' => 20,
                    'bottom' => 20,
                    'left' => 20,
                ],
            ],
            'logo' => [
                'enabled' => false,
                'position' => 'top-left',
                'width' => 100,
                'height' => 50,
                'path' => null,
            ],
            'fields' => [
                'customer_name' => [
                    'enabled' => true,
                    'label' => 'Nom du client',
                    'x' => 20,
                    'y' => 100,
                    'font_size' => 12,
                    'font_weight' => 'bold',
                ],
                'customer_phone' => [
                    'enabled' => true,
                    'label' => 'Téléphone',
                    'x' => 20,
                    'y' => 120,
                    'font_size' => 10,
                ],
                'customer_address' => [
                    'enabled' => true,
                    'label' => 'Adresse',
                    'x' => 20,
                    'y' => 140,
                    'font_size' => 10,
                    'max_lines' => 3,
                ],
                'sender_name' => [
                    'enabled' => true,
                    'label' => 'Expéditeur',
                    'x' => 300,
                    'y' => 100,
                    'font_size' => 12,
                    'font_weight' => 'bold',
                ],
                'sender_address' => [
                    'enabled' => true,
                    'label' => 'Adresse expéditeur',
                    'x' => 300,
                    'y' => 120,
                    'font_size' => 10,
                    'max_lines' => 2,
                ],
                'tracking_number' => [
                    'enabled' => true,
                    'label' => 'N° de suivi',
                    'x' => 20,
                    'y' => 200,
                    'font_size' => 14,
                    'font_weight' => 'bold',
                ],
                'order_number' => [
                    'enabled' => true,
                    'label' => 'N° commande',
                    'x' => 20,
                    'y' => 180,
                    'font_size' => 12,
                ],
                'barcode' => [
                    'enabled' => true,
                    'label' => 'Code-barres',
                    'x' => 20,
                    'y' => 220,
                    'width' => 200,
                    'height' => 50,
                    'format' => 'Code128',
                ],
                'return_barcode' => [
                    'enabled' => true,
                    'label' => 'Code retour',
                    'x' => 300,
                    'y' => 220,
                    'width' => 150,
                    'height' => 30,
                    'format' => 'Code128',
                ],
                'total_amount' => [
                    'enabled' => true,
                    'label' => 'Montant total',
                    'x' => 300,
                    'y' => 180,
                    'font_size' => 12,
                    'font_weight' => 'bold',
                ],
                'shipping_date' => [
                    'enabled' => true,
                    'label' => 'Date d\'expédition',
                    'x' => 20,
                    'y' => 300,
                    'font_size' => 10,
                ],
                'weight' => [
                    'enabled' => false,
                    'label' => 'Poids',
                    'x' => 300,
                    'y' => 300,
                    'font_size' => 10,
                ],
                'pieces_count' => [
                    'enabled' => false,
                    'label' => 'Nb pièces',
                    'x' => 400,
                    'y' => 300,
                    'font_size' => 10,
                ],
            ],
            'custom_text' => [
                [
                    'text' => 'Merci de votre confiance',
                    'x' => 20,
                    'y' => 350,
                    'font_size' => 10,
                    'alignment' => 'left',
                ]
            ],
            'styles' => [
                'primary_color' => '#000000',
                'secondary_color' => '#666666',
                'border_color' => '#cccccc',
                'background_color' => '#ffffff',
                'font_family' => 'Arial',
            ],
        ];
    }

    public static function createDefault(Admin $admin, ?string $carrier = null): self
    {
        return self::create([
            'admin_id' => $admin->id,
            'carrier_slug' => $carrier,
            'template_name' => 'Template par défaut',
            'layout_config' => self::getDefaultLayoutConfig(),
            'is_default' => true,
            'is_active' => true,
        ]);
    }

    public static function getFieldTypes(): array
    {
        return [
            'text' => 'Texte simple',
            'multiline' => 'Texte multiligne',
            'barcode' => 'Code-barres',
            'qrcode' => 'QR Code',
            'image' => 'Image',
            'date' => 'Date',
            'currency' => 'Montant',
        ];
    }

    public static function getBarcodeFormats(): array
    {
        return [
            'Code128' => 'Code 128',
            'Code39' => 'Code 39',
            'EAN13' => 'EAN-13',
            'QRCode' => 'QR Code',
        ];
    }

    public static function getAvailableFields(): array
    {
        return [
            'customer' => [
                'customer_name' => 'Nom du client',
                'customer_phone' => 'Téléphone client',
                'customer_address' => 'Adresse client',
                'customer_city' => 'Ville client',
                'customer_email' => 'Email client',
            ],
            'sender' => [
                'sender_name' => 'Nom expéditeur',
                'sender_address' => 'Adresse expéditeur',
                'sender_phone' => 'Téléphone expéditeur',
                'sender_email' => 'Email expéditeur',
            ],
            'shipment' => [
                'tracking_number' => 'N° de suivi',
                'order_number' => 'N° commande',
                'return_barcode' => 'Code retour',
                'weight' => 'Poids',
                'pieces_count' => 'Nombre de pièces',
                'content_description' => 'Description contenu',
            ],
            'financial' => [
                'total_amount' => 'Montant total',
                'cod_amount' => 'Montant COD',
                'shipping_cost' => 'Frais de port',
            ],
            'dates' => [
                'shipping_date' => 'Date d\'expédition',
                'pickup_date' => 'Date d\'enlèvement',
                'estimated_delivery' => 'Livraison estimée',
            ],
            'barcodes' => [
                'barcode' => 'Code-barres principal',
                'return_barcode' => 'Code-barres retour',
                'qr_code' => 'QR Code',
            ],
        ];
    }

    // ========================================
    // SCOPES
    // ========================================
    
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    public function scopeForCarrier($query, ?string $carrier)
    {
        return $query->where('carrier_slug', $carrier);
    }

    public function scopeForAdmin($query, $adminId)
    {
        return $query->where('admin_id', $adminId);
    }

    public function scopeGeneral($query)
    {
        return $query->whereNull('carrier_slug');
    }
}