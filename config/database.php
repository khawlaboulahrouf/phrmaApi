<?php


namespace PharmaFEFO\Config;

use PDO;
use PDOException;

class Database
{
    private static ?PDO $instance = null;

    private function __construct()
    {
        // Empêche l'instanciation directe
    }

    public static function getConnection(): PDO
    {
        if (self::$instance === null) {
            Environment::load();

            $host = Environment::get('DB_HOST', '127.0.0.1');
            $name = Environment::get('DB_NAME', 'pharmafefo');
            $user = Environment::get('DB_USER', 'root');
            $pass = Environment::get('DB_PASS', '');
            $charset = Environment::get('DB_CHARSET', 'utf8mb4');

            $dsn = "mysql:host={$host};dbname={$name};charset={$charset}";

            try {
                self::$instance = new PDO($dsn, $user, $pass, [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ]);
            } catch (PDOException $e) {
                throw new PDOException("Erreur de connexion BDD : " . $e->getMessage(), (int) $e->getCode());
            }
        }

        return self::$instance;
    }
}
