<?php
// src/Controller/Web/DashboardController.php
// CONTROLEUR WEB : renvoie uniquement le squelette HTML.
// Les données (lots, décompte péremption) sont chargées dynamiquement par
// public/js/dashboard.js via l'API JSON (/api/v1/batches, /api/v1/dashboard/summary).

namespace PharmaFEFO\Controller\Web;

class DashboardController
{
    public function index(): void
    {
        require __DIR__ . '/../../../templates/layout/base.php';
    }
}
