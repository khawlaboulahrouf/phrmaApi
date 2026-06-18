<?php


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
