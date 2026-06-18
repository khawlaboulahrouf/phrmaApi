# PharmaFEFO

Application de gestion de stock de médicaments appliquant la règle **FEFO** (First Expired, First Out), avec architecture MVC évoluée en **API-Ready** (Partie 2) : contrôleurs Web (HTML) et API (JSON) séparés, consommés en JavaScript natif (ES6, `fetch()`).

## Rôles & Droits (RBAC)

| Rôle | Périmètre |
|---|---|
| **Préparateur** | Réception de commandes (lot + DLU), sortie de stock FEFO ("Délivrer 1 boîte") |
| **Pharmacien titulaire** | Validation des inventaires, retours fournisseur, configuration des seuils d'alerte, déclaration des lots périmés |
| **Administrateur** | Gestion des utilisateurs, base des médicaments, rapport financier des pertes (`/admin/reports`, strictement interdit aux deux autres rôles) |

## Architecture

```
pharmafefo/
├── config/
│   ├── database.php       # Connexion PDO (Singleton), lit les identifiants depuis .env
│   └── environment.php    # Charge .env, configure l'affichage des erreurs (dev vs prod)
├── public/
│   ├── css/style.css
│   ├── js/
│   │   ├── app.js         # Helper fetch global + gestion des erreurs réseau
│   │   ├── dashboard.js   # US 2.1 / 2.2 - filtres + encadré dynamique
│   │   ├── inventory.js   # US 4.1 - validation inventaire / retours fournisseur
│   │   └── dispatch.js    # US 3.1 - "Délivrer 1 boîte"
│   └── index.php          # Routeur (aiguille vers Web ou Api)
├── src/
│   ├── Controller/
│   │   ├── Web/            # Retournent du HTML (squelettes, remplis par JS)
│   │   └── Api/             # Retournent uniquement du JSON
│   ├── Service/
│   │   ├── AuthService.php   # Sessions, RBAC (gardes Web + Api)
│   │   └── StockService.php  # Logique métier FEFO
│   ├── Repository/
│   ├── Entity/               # Product, StockBatch implémentent JsonSerializable
│   └── Enum/
├── templates/                 # Squelettes HTML (pas de logique métier)
└── database/schema.sql
```

## Installation locale

1. Copier `.env.example` en `.env` et renseigner les accès BDD.
2. Importer `database/schema.sql` dans MySQL.
3. Lancer `php -S localhost:8000 -t public`.
4. Se connecter via `/index.php?route=login` (comptes de démo dans `schema.sql`).

## API (extrait)

| Méthode | Route | Rôle requis |
|---|---|---|
| POST | `/api/v1/stock/add` | Préparateur |
| GET | `/api/v1/batches?criteria=critical` | Authentifié |
| GET | `/api/v1/dashboard/summary` | Authentifié |
| POST | `/api/v1/batches/checkout` | Préparateur |
| PATCH | `/api/v1/batches/{id}/expire` | Pharmacien |
| PATCH | `/api/v1/batches/{id}/return-supplier` | Pharmacien |

Toutes les routes API renvoient `Content-Type: application/json` et un code HTTP explicite (401, 403, 422...). Le JavaScript (`app.js`) intercepte ces codes et affiche une alerte claire dans `#api-error`.

## Déploiement

- `.env` est exclu du dépôt via `.gitignore` (zéro secret sur GitHub).
- `APP_ENV=production` dans l'environnement d'hébergement masque les erreurs détaillées (page générique "Erreur 500").
- Brancher l'hébergeur (Render/Railway) sur la branche `main` pour un déploiement continu à chaque merge de Pull Request.
