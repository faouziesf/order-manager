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

    // Relations
    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class);
    }

    // Accessors
    public function getDisplayNameAttribute(): string
    {
        $carrier = $this->carrier_slug ? ucfirst($this->carrier_slug) : 'Général';
        return "{$carrier} - {$this->template_name}";
    }

    // Methods
    public function setAsDefault(): void
    {
        // Désactiver tous les autres templates par défaut pour cet admin et transporteur
        static::where('admin_id', $this->admin_id)
            ->where('carrier_slug', $this->carrier_slug)
            ->where('id', '!=', $this->id)
            ->update(['is_default' => false]);

        // Activer celui-ci comme défaut
        $this->update(['is_default' => true]);
    }

    public function duplicate(string $newName): self
    {
        $duplicate = $this->replicate();
        $duplicate->template_name = $newName;
        $duplicate->is_default = false;
        $duplicate->save();

        return $duplicate;
    }

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
            ],
        ];
    }

    public function mergeWithDefaults(array $config = []): array
    {
        $defaults = self::getDefaultLayoutConfig();
        return array_merge_recursive($defaults, $config);
    }

    // Scopes
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
}