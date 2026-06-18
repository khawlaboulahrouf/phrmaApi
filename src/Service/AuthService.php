<?php
// src/Service/AuthService.php
// Logique de vérification des sessions et des rôles (RBAC),
// partagée par les contrôleurs Web (HTML) et API (JSON).

namespace PharmaFEFO\Service;

use PDO;
use PharmaFEFO\Config\Database;
use PharmaFEFO\Entity\User;

class AuthService
{
    private PDO $db;

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->db = Database::getConnection();
    }

    /**
     * Vérifie les identifiants et démarre la session si valides.
     *
     * @return array{success: bool, error?: string, user?: array}
     */
    public function attemptLogin(string $email, string $password): array
    {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE email = :email');
        $stmt->execute(['email' => $email]);
        $row = $stmt->fetch();

        if (!$row || !password_verify($password, $row['password'])) {
            return ['success' => false, 'error' => 'Email ou mot de passe incorrect.'];
        }

        $user = [
            'id'    => (int) $row['id'],
            'name'  => $row['name'],
            'email' => $row['email'],
            'role'  => $row['role'],
        ];

        $_SESSION['user'] = $user;

        return ['success' => true, 'user' => $user];
    }

    public function logout(): void
    {
        $_SESSION = [];
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }
    }

    /**
     * Retourne l'utilisateur courant (array) ou null si non connecté.
     */
    public static function currentUser(): ?array
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        return $_SESSION['user'] ?? null;
    }

    // ================================================================
    // Gardes pour les contrôleurs WEB (réponse HTML)
    // ================================================================

    /**
     * Garde de route Web : exige une connexion. Redirige vers /login sinon.
     */
    public static function requireLogin(): array
    {
        $user = self::currentUser();
        if ($user === null) {
            header('Location: index.php?route=login');
            exit;
        }
        return $user;
    }

    /**
     * Garde de route Web : exige un (ou plusieurs) rôle(s) précis.
     * Affiche une page 403 si l'utilisateur connecté n'a pas le bon rôle.
     */
    public static function requireRole(string ...$roles): array
    {
        $user = self::requireLogin();

        if (!in_array($user['role'], $roles, true)) {
            http_response_code(403);
            echo "<h1>403 - Accès refusé</h1><p>Cette page est réservée à : " . implode(', ', $roles) . ".</p>";
            echo '<p><a href="index.php?route=dashboard">Retour au tableau de bord</a></p>';
            exit;
        }

        return $user;
    }

    // ================================================================
    // Gardes pour les contrôleurs API (réponse JSON)
    // ================================================================

    /**
     * Garde de route API : exige une connexion. Réponse JSON 401 sinon.
     */
    public static function requireApiLogin(): array
    {
        $user = self::currentUser();

        if ($user === null) {
            self::jsonError(401, 'Authentification requise.');
        }

        return $user;
    }

    /**
     * Garde de route API : exige un (ou plusieurs) rôle(s) précis.
     * Réponse JSON 403 si le rôle ne correspond pas (route strictement interdite).
     */
    public static function requireApiRole(string ...$roles): array
    {
        $user = self::requireApiLogin();

        if (!in_array($user['role'], $roles, true)) {
            self::jsonError(403, 'Accès interdit : cette ressource est réservée à : ' . implode(', ', $roles) . '.');
        }

        return $user;
    }

    /**
     * Envoie une réponse d'erreur JSON standardisée et termine le script.
     */
    private static function jsonError(int $httpCode, string $message): void
    {
        http_response_code($httpCode);
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => $message]);
        exit;
    }
}
