# Plan de Restructuration Globale — Order Manager CRM

## Architecture des Rôles (État Final)

| Garde Auth | Modèle | Rôles | Table DB |
|---|---|---|---|
| `admin` | `Admin` | admin, manager, employee | `admins` (unifiée) |
| `confirmi` | `ConfirmiUser` | commercial, employee, agent | `confirmi_users` |
| `super-admin` | `SuperAdmin` | — | `super_admins` |

## Éléments déjà en place (vérifiés) ✅

- **`HasOrderProcessing` trait** — utilisé par `Admin\ProcessController` ET `Confirmi\ConfirmiProcessController` (DRY)
- **Permissions granulaires** — `Admin.permissions` (JSON), `Admin::can()`, `DEFAULT_PERMISSIONS`
- **`getEffectiveAdminId()`** — managers/employees voient les données de l'admin parent
- **Middleware auth** — `confirmi`, `confirmi.commercial`, `admin`, `manager`, `employee`
- **Delivery routing** — `routeToDelivery()`: emballage_enabled → Kolixy Société, sinon → Kolixy Personnel
- **Emballage system** — `EmballageTask`, `ConfirmiEmballageController`, agent routes

## Implémentation Réalisée ✅

### Phase 1: Middleware `CheckPermission` ✅
- **Créé** `app/Http/Middleware/CheckPermission.php`
  - Accepte une ou plusieurs permissions (ex: `permission:can_manage_products`)
  - Admin bypasse toutes les vérifications
  - Manager/Employee doivent avoir au moins une permission listée
  - Supporte JSON et redirection selon le type de requête
- **Enregistré** alias `permission` dans `app/Http/Kernel.php`
- **Appliqué** sur toutes les sections de `routes/admin.php`:
  - `can_manage_products` → Produits
  - `can_manage_orders` → Commandes + Doublons
  - `can_process_orders` → Traitement (interface, examen, suspendues, restock)
  - `can_manage_users` → Employés, Managers, Historique connexion
  - `can_import` → Import CSV, WooCommerce, Shopify, PrestaShop
  - `can_manage_settings` → Paramètres
  - `can_manage_delivery` → Kolixy (livraison)

### Phase 2: Sidebar dynamique ✅
- **Refactorisé** `resources/views/layouts/admin.blade.php`
  - Supprimé le bloc `@if($isEmployee)` monolithique
  - Chaque section utilise `@if($user->can('permission_name'))`
  - Dashboard toujours visible
  - Section "Services" apparaît si `can_manage_delivery` OU `can_import`
  - Section "Administration" apparaît si `can_manage_users` OU `can_manage_settings`
  - Confirmi reste réservé au rôle admin

### Phase 3: Nettoyage Masafa ✅
- Remplacé "Masafa Express" → "Kolixy" dans les fichiers blade de backup
- Les références `masafa_*` dans les vues Kolixy sont des noms de colonnes DB (non renommés pour éviter une migration breaking)

### Phase 4: Workflow Livraison ✅ (vérifié, aucun changement nécessaire)
- `routeToDelivery()` dans `HasOrderProcessing` implémente correctement:
  - `emballage_enabled` → pipeline EmballageTask → BL via Kolixy Société
  - Sinon → envoi direct via Kolixy Personnel (`MasafaConfiguration`)

## Permissions par défaut (rappel)

```php
const DEFAULT_PERMISSIONS = [
    'can_manage_orders'       => true,
    'can_process_orders'      => true,
    'can_manage_products'     => true,
    'can_manage_stock'        => true,
    'can_manage_users'        => false,
    'can_manage_settings'     => false,
    'can_manage_delivery'     => false,
    'can_import'              => false,
    'can_view_stats'          => true,
    'can_manage_integrations' => false,
];
```
