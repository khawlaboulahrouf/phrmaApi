<?php
// src/Service/StockService.php
// Logique métier de calcul des stocks, partagée par les contrôleurs Web et API.

namespace PharmaFEFO\Service;

use DateTime;
use PharmaFEFO\Entity\StockBatch;
use PharmaFEFO\Enum\BatchStatus;
use PharmaFEFO\Repository\StockBatchRepository;

class StockService
{
    private StockBatchRepository $repository;

    public function __construct()
    {
        $this->repository = new StockBatchRepository();
    }

    /**
     * US 1.1 - Réception d'un nouveau lot.
     * Refuse si la DLU est vide ou antérieure à aujourd'hui.
     *
     * @return array{success: bool, error?: string, batch?: StockBatch}
     */
    public function receiveBatch(int $productId, string $lotNumber, int $quantity, string $expiryDate): array
    {
        if (empty($expiryDate)) {
            return ['success' => false, 'error' => "La date de péremption (DLU) est obligatoire."];
        }

        $expiry = DateTime::createFromFormat('Y-m-d', $expiryDate);
        $today = new DateTime('today');

        if ($expiry === false || $expiry < $today) {
            return ['success' => false, 'error' => "La date de péremption ne peut pas être vide ou antérieure à aujourd'hui."];
        }

        if ($quantity <= 0) {
            return ['success' => false, 'error' => "La quantité doit être positive."];
        }

        $batch = new StockBatch(0, $productId, $lotNumber, $quantity, $expiry, BatchStatus::OK);
        $this->repository->save($batch);

        return ['success' => true, 'batch' => $batch];
    }

    /**
     * US 3.1 - Sortie de stock FEFO : décrémente automatiquement le(s) lot(s)
     * dont la DLU est la plus courte pour couvrir la quantité demandée.
     *
     * @return array{success: bool, error?: string, batches?: StockBatch[]}
     */
    public function dispatchFefo(int $productId, int $quantityRequested): array
    {
        $batches = $this->repository->findFefoBatchesForProduct($productId, $quantityRequested);

        if (empty($batches)) {
            return ['success' => false, 'error' => "Aucun lot disponible pour ce produit."];
        }

        $remaining = $quantityRequested;
        $affected = [];

        foreach ($batches as $batch) {
            if ($remaining <= 0) {
                break;
            }

            $taken = min($remaining, $batch->getQuantity());
            $batch->decreaseStock($taken);
            $this->repository->save($batch);

            $affected[] = $batch;
            $remaining -= $taken;
        }

        if ($remaining > 0) {
            return [
                'success'  => false,
                'error'    => "Stock insuffisant : il manque {$remaining} unité(s).",
                'batches'  => $affected,
            ];
        }

        return ['success' => true, 'batches' => $affected];
    }

    /**
     * US 3.1 (Part 2) - "Délivrer 1 boîte" : raccourci pour dispatchFefo() avec quantité = 1.
     * Retourne le lot FEFO unique qui a été décrémenté (le premier affecté).
     *
     * @return array{success: bool, error?: string, batch?: StockBatch}
     */
    public function checkoutOneUnit(int $productId): array
    {
        $result = $this->dispatchFefo($productId, 1);

        if (!$result['success']) {
            return ['success' => false, 'error' => $result['error']];
        }

        return ['success' => true, 'batch' => $result['batches'][0]];
    }

    /**
     * US 4.1 - Déclare un lot comme "Périmé / À détruire".
     * Statut -> EXPIRED, quantité -> 0.
     *
     * @return array{success: bool, error?: string, batch?: StockBatch}
     */
    public function declareExpired(int $batchId): array
    {
        $batch = $this->repository->findById($batchId);

        if ($batch === null) {
            return ['success' => false, 'error' => "Lot introuvable."];
        }

        if (!$batch->isExpired()) {
            return ['success' => false, 'error' => "Ce lot n'a pas encore atteint sa date de péremption."];
        }

        $batch->markAsExpired();
        $this->repository->save($batch);

        return ['success' => true, 'batch' => $batch];
    }

    /**
     * US 2.1 (API) - Retourne les lots filtrés par critère de criticité.
     *
     * @return StockBatch[]
     */
    public function getBatchesByCriteria(string $criteria = 'all'): array
    {
        return $this->repository->findAllForApi($criteria);
    }

    /**
     * US (Pharmacien) - Initie un retour fournisseur pour un lot proche de la
     * péremption (en vue d'un remboursement). Statut -> RETURN_PROCESS, quantité -> 0.
     *
     * @return array{success: bool, error?: string, batch?: StockBatch}
     */
    public function returnToSupplier(int $batchId): array
    {
        $batch = $this->repository->findById($batchId);

        if ($batch === null) {
            return ['success' => false, 'error' => "Lot introuvable."];
        }

        $batch->setStatus(BatchStatus::RETURN_PROCESS);
        $batch->setQuantity(0);
        $this->repository->save($batch);

        return ['success' => true, 'batch' => $batch];
    }

    /**
     * US 2.2 - Nombre de lots qui périment le mois prochain (encadré dynamique).
     */
    public function countExpiringNextMonth(): int
    {
        return count($this->repository->findExpiringNextMonth());
    }
}
