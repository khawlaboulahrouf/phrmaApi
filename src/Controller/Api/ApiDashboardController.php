<?php
// src/Controller/Api/ApiDashboardController.php
// CONTROLEUR API : retourne uniquement du JSON, jamais de HTML/require de template.

namespace PharmaFEFO\Controller\Api;

use PharmaFEFO\Service\AuthService;
use PharmaFEFO\Service\StockService;

class ApiDashboardController
{
    private StockService $stockService;

    public function __construct()
    {
        header('Content-Type: application/json');
        $this->stockService = new StockService();
    }

    /**
     * GET /api/v1/batches?criteria=critical
     * US 2.1 - Liste des lots filtrés par critère ('all', 'critical', 'warning', 'ok', 'expired').
     * Accessible aux 3 rôles connectés (lecture seule).
     */
    public function batches(): void
    {
        AuthService::requireApiLogin();

        $criteria = $_GET['criteria'] ?? 'all';
        $batches = $this->stockService->getBatchesByCriteria($criteria);

        echo json_encode([
            'success' => true,
            'criteria' => $criteria,
            'count'   => count($batches),
            'data'    => $batches,
        ]);
    }

    /**
     * GET /api/v1/dashboard/summary
     * US 2.2 - Encadré dynamique : décompte des produits qui périment le mois prochain.
     */
    public function summary(): void
    {
        AuthService::requireApiLogin();

        echo json_encode([
            'success' => true,
            'data' => [
                'expiring_next_month' => $this->stockService->countExpiringNextMonth(),
                'critical_count'      => count($this->stockService->getBatchesByCriteria('critical')),
                'warning_count'       => count($this->stockService->getBatchesByCriteria('warning')),
                'ok_count'            => count($this->stockService->getBatchesByCriteria('ok')),
            ],
        ]);
    }
}
