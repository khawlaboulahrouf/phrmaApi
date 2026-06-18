<?php
// templates/preparateur/dispatch.php
// US 3.1 (Part 2) - "Délivrer 1 boîte" : décrémente instantanément en tâche de fond
// le lot FEFO adéquat (PATCH/POST asynchrone), sans rechargement de page.
// La liste des médicaments + quantité totale en stock est chargée par dispatch.js.
use PharmaFEFO\Service\AuthService;
$currentUser = AuthService::currentUser();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PharmaFEFO - Sortie de stock (FEFO)</title>
    <link rel="stylesheet" href="css/style.css">
    <script src="js/app.js"></script>
</head>
<body>
    <header class="topbar">
        <h1>💊 PharmaFEFO</h1>
        <nav>
            <a href="index.php?route=dashboard">Tableau de bord</a>
            <a href="index.php?route=preparateur/receive">Réception</a>
            <a href="index.php?route=preparateur/dispatch">Sortie de stock</a>
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
        <h2>Sortie de stock - Règle FEFO (US 3.1)</h2>
        <p>Clique sur « Délivrer 1 boîte » : le système décrémente automatiquement le lot dont la DLU
           est la plus courte, en tâche de fond, sans recharger la page.</p>

        <div id="api-error"></div>

        <table class="batch-table">
            <thead>
                <tr>
                    <th>Médicament</th>
                    <th>Lot le plus proche (FEFO)</th>
                    <th>Quantité disponible</th>
                    <th>DLU</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody id="dispatch-tbody">
                <tr><td colspan="5">Chargement…</td></tr>
            </tbody>
        </table>
    </main>

    <footer class="footer">
        <p>PharmaFEFO &copy; <?= date('Y') ?> - Architecture MVC</p>
    </footer>

    <script src="js/dispatch.js"></script>
</body>
</html>
