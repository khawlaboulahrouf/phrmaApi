<?php
// templates/pharmacien/inventory.php
// CONTROLEUR WEB : squelette HTML uniquement.
// Le tableau des lots et les actions (déclarer périmé / retour fournisseur) sont
// gérés par public/js/inventory.js via l'API JSON, sans rechargement de page.
use PharmaFEFO\Service\AuthService;
$currentUser = AuthService::currentUser();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PharmaFEFO - Validation inventaire</title>
    <link rel="stylesheet" href="css/style.css">
    <script src="js/app.js"></script>
</head>
<body>
    <header class="topbar">
        <h1>💊 PharmaFEFO</h1>
        <nav>
            <a href="index.php?route=dashboard">Tableau de bord</a>
            <a href="index.php?route=pharmacien/inventory">Inventaire</a>
            <a href="index.php?route=pharmacien/thresholds">Seuils d'alerte</a>
        </nav>
        <div class="user-box">
            <span class="user-name"><?= htmlspecialchars($currentUser['name']) ?></span>
            <span class="user-role badge-role-<?= htmlspecialchars($currentUser['role']) ?>">
                <?= htmlspecialchars(ucfirst($currentUser['role'])) ?>
            </span>
            <a class="logout" href="index.php?route=logout">Déconnexion</a>
        </div>
    </header>

    <main class="container">
        <h2>Validation de l'inventaire</h2>
        <p>Lots triés FEFO (date de péremption la plus proche en premier). Déclarez les lots périmés ou initiez un retour fournisseur pour les lots proches de la péremption.</p>

        <div id="api-error"></div>

        <table class="batch-table">
            <thead>
                <tr>
                    <th>Lot</th>
                    <th>Médicament</th>
                    <th>Quantité</th>
                    <th>DLU</th>
                    <th>Jours restants</th>
                    <th>Statut</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody id="inventory-tbody">
                <tr><td colspan="7">Chargement…</td></tr>
            </tbody>
        </table>
    </main>

    <footer class="footer">
        <p>PharmaFEFO &copy; <?= date('Y') ?> - Architecture MVC</p>
    </footer>

    <script src="js/inventory.js"></script>
</body>
</html>
