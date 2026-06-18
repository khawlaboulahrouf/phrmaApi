<?php
// src/Controller/Web/AuthController.php
// CONTROLEUR WEB (retourne du HTML) - délègue toute la logique à AuthService.

namespace PharmaFEFO\Controller\Web;

use PharmaFEFO\Service\AuthService;

class AuthController
{
    private AuthService $authService;

    public function __construct()
    {
        $this->authService = new AuthService();
    }

    /**
     * Affiche le formulaire de connexion.
     */
    public function showLogin(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $error = $_SESSION['login_error'] ?? null;
        unset($_SESSION['login_error']);

        require __DIR__ . '/../../../templates/auth/login.php';
    }

    /**
     * Traite le formulaire de connexion (POST classique, rechargement de page).
     */
    public function login(): void
    {
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        $result = $this->authService->attemptLogin($email, $password);

        if (!$result['success']) {
            $_SESSION['login_error'] = $result['error'];
            header('Location: index.php?route=login');
            exit;
        }

        header('Location: index.php?route=dashboard');
        exit;
    }

    public function logout(): void
    {
        $this->authService->logout();
        header('Location: index.php?route=login');
        exit;
    }
}
