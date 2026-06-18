<?php
// templates/dashboard/index.php
// Vue HTML isolée - squelette initial uniquement.
// La table des lots et l'encadré de notifications sont remplis dynamiquement
// par public/js/dashboard.js via l'API (/api/v1/batches, /api/v1/dashboard/summary).
?>
<section class="dashboard">
    <h2>Alertes de péremption (FEFO)</h2>

    <!-- US 2.2 - Encadré dynamique rempli par dashboard.js au chargement -->
    <div id="summary-box" class="summary-cards">
        <div class="card card-green"><span class="count" id="count-ok">…</span><span class="label">Lots OK (&gt; 6 mois)</span></div>
        <div class="card card-orange"><span class="count" id="count-warning">…</span><span class="label">Lots à surveiller (&lt; 90 jours)</span></div>
        <div class="card card-red"><span class="count" id="count-critical">…</span><span class="label">Lots critiques (&lt; 30 jours)</span></div>
        <div class="card card-gray"><span class="count" id="count-expiring">…</span><span class="label">Périment le mois prochain</span></div>
    </div>

    <div id="api-error"></div>

    <!-- US 2.1 - Filtres cliquables, mise à jour du tableau via JS, sans reload -->
    <div class="filter-bar" id="filter-bar">
        <button type="button" class="filter-btn active" data-criteria="all">Tous les lots en alerte</button>
        <button type="button" class="filter-btn" data-criteria="critical">🔴 Alerte Rouge uniquement</button>
        <button type="button" class="filter-btn" data-criteria="warning">🟠 Alerte Orange uniquement</button>
    </div>

    <h3>Lots à péremption (priorité de sortie FEFO)</h3>
    <table class="batch-table">
        <thead>
            <tr>
                <th>Lot</th>
                <th>Médicament</th>
                <th>Quantité</th>
                <th>DLU</th>
                <th>Jours restants</th>
                <th>Statut</th>
            </tr>
        </thead>
        <tbody id="batches-tbody">
            <tr><td colspan="6">Chargement…</td></tr>
        </tbody>
    </table>
</section>

<script src="js/dashboard.js"></script>
