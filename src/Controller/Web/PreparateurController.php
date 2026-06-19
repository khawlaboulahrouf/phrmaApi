<?php


namespace PharmaFEFO\Controller\Web;

use PDO;
use PharmaFEFO\Config\Database;

class PreparateurController
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

   
    public function receiveForm(): void
    {
        $products = $this->db->query("SELECT id, name, reference FROM products ORDER BY name ASC")->fetchAll();

        require __DIR__ . '/../../../templates/preparateur/receive.php';
    }

 
    public function dispatchForm(): void
    {
        $products = $this->db->query("SELECT id, name, reference FROM products ORDER BY name ASC")->fetchAll();

        require __DIR__ . '/../../../templates/preparateur/dispatch.php';
    }
}
