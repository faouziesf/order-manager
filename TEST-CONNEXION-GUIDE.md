# 🧪 Guide de Test - Connexion Employé Confirmi

## ✅ Vérifications Effectuées

### 1. Base de Données
```
✅ Utilisateur existe: employe@confirmi.com
✅ ID: 2
✅ Role: employee
✅ is_active: 1 (OUI)
✅ Password hash: Valide
✅ Test Hash::check('password'): VALIDE ✅
```

### 2. Test d'Authentification Guard
```
✅ Auth::guard('confirmi')->attempt() en Tinker: CONNEXION REUSSIE
```

### 3. Configuration
```
✅ Guard 'confirmi' configuré dans config/auth.php
✅ Provider 'confirmi-users' → Model ConfirmiUser
✅ Middleware web avec StartSession + VerifyCsrfToken
✅ Routes confirmi avec middleware web
```

## 🔍 Logs de Débogage Activés

J'ai ajouté des logs détaillés dans `AuthController::login()`:
- Log au début de la tentative Confirmi
- Log si connexion réussie avec user_id et role
- Log si compte inactif
- Log avant essai Super Admin

## 📋 Procédure de Test

### Étape 1: Vider les logs
```bash
cd C:\Users\DELL\Documents\GitHub\masafaexpress\order-manager
echo. > storage\logs\laravel.log
```

### Étape 2: Tester la connexion
1. Ouvrir http://localhost:8000/
2. Se connecter avec:
   - Email: `employe@confirmi.com`
   - Password: `password`
3. Noter le résultat (erreur, redirection, etc.)

### Étape 3: Consulter les logs
```bash
type storage\logs\laravel.log
```

**Chercher les lignes contenant:**
- "Tentative connexion Confirmi"
- "Connexion Confirmi réussie"
- "Échec connexion Confirmi"
- "Compte Confirmi inactif"

## 🎯 Comptes de Test

| Email | Password | Type | Statut Attendu |
|-------|----------|------|----------------|
| super@admin.com | password | Super Admin | ✅ FONCTIONNE |
| admin@test.com | password | Admin | ✅ FONCTIONNE |
| commercial@confirmi.com | password | Commercial Confirmi | À TESTER |
| employe@confirmi.com | password | Employé Confirmi | ⚠️ PROBLÈME |

## 🚀 Corrections Appliquées

### 1. Auto-refresh après déconnexion
- Ajouté dans `home.blade.php`
- Force le rechargement de la page après logout
- Génère une nouvelle session propre

### 2. Logs détaillés
- Trace complète du flux de connexion
- Identification du guard utilisé
- Vérification du statut is_active

### 3. Connexion unifiée
- Tous les comptes passent par `Confirmi\AuthController::login()`
- Essai dans l'ordre: Confirmi → Super Admin → Admin
- Redirection automatique selon le rôle

## 📊 Résultat Attendu

Après connexion avec `employe@confirmi.com`:
1. ✅ Message dans les logs: "Tentative connexion Confirmi"
2. ✅ Message dans les logs: "Connexion Confirmi réussie"
3. ✅ Redirection vers `/confirmi/dashboard`
4. ✅ Dashboard employé affiché

## 🔧 Si Problème Persiste

Vérifier dans les logs Laravel (`storage/logs/laravel.log`):
- Y a-t-il une erreur SQL?
- Y a-t-il une exception?
- Quel guard a réussi l'authentification?
- Y a-t-il un problème de session?
