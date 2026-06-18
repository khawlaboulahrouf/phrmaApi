<?php
// src/Controller/Api/ApiPharmacienController.php
// CONTROLEUR API : retourne uniquement du JSON. Actions réservées au Pharmacien titulaire.

namespace PharmaFEFO\Controller\Api;

use PharmaFEFO\Entity\User;
use PharmaFEFO\Service\AuthService;
use PharmaFEFO\Service\StockService;

class ApiPharmacienController
{
    private StockService $stockService;

    public function __construct()
    {
        header('Content-Type: application/json');
        $this->stockService = new StockService();
    }

    /**
     * PATCH /api/v1/batches/{id}/return-supplier
     * Initie un retour fournisseur (remboursement) pour un lot proche de la péremption.
     * Rôle PHARMACIEN uniquement.
     */
    public function returnToSupplier(): void
    {
        AuthService::requireApiRole(User::ROLE_PHARMACIEN);

        $input = $this->readInput();
        $batchId = (int) ($input['batch_id'] ?? ($_GET['id'] ?? 0));

        $result = $this->stockService->returnToSupplier($batchId);

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
