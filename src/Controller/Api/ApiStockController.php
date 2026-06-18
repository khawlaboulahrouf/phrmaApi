<?php
// src/Controller/Api/ApiStockController.php
// CONTROLEUR API : retourne uniquement du JSON, jamais de HTML/require de template.

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

    /**
     * POST /api/v1/stock/add  (anciennement /stock/add)
     * US 1.1 - Réception asynchrone d'un nouveau lot. Rôle PREPARATEUR uniquement.
     */
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

    /**
     * POST /api/v1/batches/checkout
     * US 3.1 - "Délivrer 1 boîte" : décrémente instantanément le lot FEFO adéquat.
     * Rôle PREPARATEUR uniquement.
     */
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

    /**
     * PATCH /api/v1/batches/{id}/expire
     * US 4.1 - Déclare un lot comme périmé. Rôle PHARMACIEN uniquement.
     */
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

    /**
     * Lit le corps de la requête : accepte JSON ou FormData (US 1.1, critère d'acceptation).
     */
    private function readInput(): array
    {
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';

        if (str_contains($contentType, 'application/json')) {
            $raw = file_get_contents('php://input');
            return json_decode($raw, true) ?? [];
        }

        // FormData (multipart/form-data ou x-www-form-urlencoded)
        return $_POST;
    }
}
