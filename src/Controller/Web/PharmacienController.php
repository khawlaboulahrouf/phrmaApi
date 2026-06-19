<?php


namespace PharmaFEFO\Controller\Web;

use PDO;
use PharmaFEFO\Config\Database;
use PharmaFEFO\Repository\StockBatchRepository;

class PharmacienController
{
    private PDO $db;
    private StockBatchRepository $repository;

    public function __construct()
    {
        $this->db = Database::getConnection();
        $this->repository = new StockBatchRepository();
    }

   
    public function thresholds(): void
    {
        $message = null;
        $error = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $warning = (int) ($_POST['warning_days'] ?? 0);
            $critical = (int) ($_POST['critical_days'] ?? 0);

            if ($warning <= $critical || $critical <= 0) {
                $error = "Le seuil 'Orange' doit être supérieur au seuil 'Rouge', et le seuil 'Rouge' doit être positif.";
            } else {
                $stmt = $this->db->prepare("UPDATE alert_thresholds SET warning_days = :w, critical_days = :c WHERE id = 1");
                $stmt->execute(['w' => $warning, 'c' => $critical]);
                $message = "Seuils d'alerte mis à jour avec succès.";
            }
        }

        $current = $this->db->query("SELECT * FROM alert_thresholds WHERE id = 1")->fetch();

        require __DIR__ . '/../../../templates/pharmacien/thresholds.php';
    }

    public function inventory(): void
    {
        require __DIR__ . '/../../../templates/pharmacien/inventory.php';
    }
}
