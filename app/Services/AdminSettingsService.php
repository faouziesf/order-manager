<?php

namespace App\Services;

use App\Models\AdminSetting;
use App\Models\User;
use Illuminate\Support\Collection;

class AdminSettingsService
{
    /**
     * Obtenir un paramètre spécifique pour un admin
     */
    public function getSetting(int $adminId, string $settingKey, $defaultValue = null)
    {
        $setting = AdminSetting::forAdmin($adminId)
            ->bySetting($settingKey)
            ->first();

        return $setting ? $setting->setting_value : $defaultValue;
    }

    /**
     * Définir un paramètre pour un admin
     */
    public function setSetting(int $adminId, string $settingKey, $value, string $type = 'string', bool $isEncrypted = false, string $description = null)
    {
        // Convertir la valeur selon le type
        $processedValue = $this->processValueByType($value, $type);

        return AdminSetting::updateOrCreate(
            [
                'admin_id' => $adminId,
                'setting_key' => $settingKey
            ],
            [
                'setting_value' => $processedValue,
                'setting_type' => $type,
                'is_encrypted' => $isEncrypted,
                'description' => $description
            ]
        );
    }

    /**
     * Obtenir tous les paramètres d'un admin
     */
    public function getAllSettings(int $adminId): Collection
    {
        return AdminSetting::forAdmin($adminId)
            ->get()
            ->pluck('setting_value', 'setting_key');
    }

    /**
     * Obtenir tous les paramètres d'un admin avec les métadonnées
     */
    public function getAllSettingsWithMeta(int $adminId): Collection
    {
        return AdminSetting::forAdmin($adminId)->get();
    }

    /**
     * Supprimer un paramètre
     */
    public function deleteSetting(int $adminId, string $settingKey): bool
    {
        return AdminSetting::forAdmin($adminId)
            ->bySetting($settingKey)
            ->delete() > 0;
    }

    /**
     * Supprimer tous les paramètres d'un admin
     */
    public function deleteAllSettings(int $adminId): bool
    {
        return AdminSetting::forAdmin($adminId)->delete() > 0;
    }

    /**
     * Vérifier si un paramètre existe
     */
    public function hasSetting(int $adminId, string $settingKey): bool
    {
        return AdminSetting::forAdmin($adminId)
            ->bySetting($settingKey)
            ->exists();
    }

    /**
     * Définir plusieurs paramètres en une fois
     */
    public function setBulkSettings(int $adminId, array $settings): void
    {
        foreach ($settings as $settingKey => $config) {
            if (is_array($config)) {
                $this->setSetting(
                    $adminId,
                    $settingKey,
                    $config['value'],
                    $config['type'] ?? 'string',
                    $config['is_encrypted'] ?? false,
                    $config['description'] ?? null
                );
            } else {
                $this->setSetting($adminId, $settingKey, $config);
            }
        }
    }

    /**
     * Obtenir les paramètres par défaut pour un nouvel admin
     */
    public function getDefaultSettings(): array
    {
        return [
            'notification_email' => [
                'value' => true,
                'type' => 'boolean',
                'description' => 'Recevoir les notifications par email'
            ],
            'notification_sms' => [
                'value' => false,
                'type' => 'boolean',
                'description' => 'Recevoir les notifications par SMS'
            ],
            'language' => [
                'value' => 'fr',
                'type' => 'string',
                'description' => 'Langue préférée'
            ],
            'timezone' => [
                'value' => 'Europe/Paris',
                'type' => 'string',
                'description' => 'Fuseau horaire'
            ],
            'orders_per_page' => [
                'value' => 25,
                'type' => 'integer',
                'description' => 'Nombre de commandes par page'
            ],
            'auto_refresh_interval' => [
                'value' => 30,
                'type' => 'integer',
                'description' => 'Intervalle de rafraîchissement automatique (secondes)'
            ]
        ];
    }

    /**
     * Initialiser les paramètres par défaut pour un admin
     */
    public function initializeDefaultSettings(int $adminId): void
    {
        $defaultSettings = $this->getDefaultSettings();
        $this->setBulkSettings($adminId, $defaultSettings);
    }

    /**
     * Traiter la valeur selon le type
     */
    private function processValueByType($value, string $type)
    {
        switch ($type) {
            case 'json':
            case 'array':
                return is_string($value) ? $value : json_encode($value);
            case 'boolean':
                return $value ? '1' : '0';
            default:
                return (string) $value;
        }
    }
}