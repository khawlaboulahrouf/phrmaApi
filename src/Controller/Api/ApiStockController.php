<?php


namespace PharmaFEFO\Controller\Api;

use PharmaFEFO\Entity\User;
use PharmaFEFO\Service\AuthService;
use PharmaFEFO\Service\StockService;

class ApiStockController
{
    private StockService $stockService;

    public function __construct()
    {
        header('Content-Type: application/json');
        $this->stockService = new StockService();
    }

  
    public function add(): void
    {
        AuthService::requireApiRole(User::ROLE_PREPARATEUR);

        $input = $this->readInput();

        $result = $this->stockService->receiveBatch(
            (int) ($input['product_id'] ?? 0),
            (string) ($input['lot_number'] ?? ''),
            (int) ($input['quantity'] ?? 0),
            (string) ($input['expiry_date'] ?? '')
        );

        if (!$result['success']) {
            http_response_code(422);
            echo json_encode(['success' => false, 'error' => $result['error']]);
            return;
        }

        http_response_code(201);
        echo json_encode(['success' => true, 'data' => $result['batch']]);
    }

   
    public function checkout(): void
    {
        AuthService::requireApiRole(User::ROLE_PREPARATEUR);

        $input = $this->readInput();
        $productId = (int) ($input['product_id'] ?? 0);

        $result = $this->stockService->checkoutOneUnit($productId);

        if (!$result['success']) {
            http_response_code(422);
            echo json_encode(['success' => false, 'error' => $result['error']]);
            return;
        }

        echo json_encode(['success' => true, 'data' => $result['batch']]);
    }

  
    public function declareExpired(): void
    {
        AuthService::requireApiRole(User::ROLE_PHARMACIEN);

        $input = $this->readInput();
        $batchId = (int) ($input['batch_id'] ?? ($_GET['id'] ?? 0));

        $result = $this->stockService->declareExpired($batchId);

        if (!$result['success']) {
            http_response_code(422);
            echo json_encode(['success' => false, 'error' => $result['error']]);
            return;
        }

        echo json_encode(['success' => true, 'data' => $result['batch']]);
    }

  
    private function readInput(): array
    {
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';

        if (str_contains($contentType, 'application/json')) {
            $raw = file_get_contents('php://input');
            return json_decode($raw, true) ?? [];
        }

     
        return $_POST;
    }
}
