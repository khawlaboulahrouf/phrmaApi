<?php


declare(strict_types=1);

require_once __DIR__ . '/../config/environment.php';
require_once __DIR__ . '/../config/database.php';

use PharmaFEFO\Config\Environment;

Environment::load();

spl_autoload_register(function (string $class) {
    $prefix = 'PharmaFEFO\\';
    $baseDir = __DIR__ . '/../src/';

    if (!str_starts_with($class, $prefix)) {
        return;
    }

    $relativeClass = substr($class, strlen($prefix));
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

    if (file_exists($file)) {
        require $file;
    }
});

use PharmaFEFO\Controller\Api\ApiDashboardController;
use PharmaFEFO\Controller\Api\ApiPharmacienController;
use PharmaFEFO\Controller\Api\ApiStockController;
use PharmaFEFO\Controller\Web\AdminController;
use PharmaFEFO\Controller\Web\AuthController;
use PharmaFEFO\Controller\Web\DashboardController;
use PharmaFEFO\Controller\Web\PharmacienController;
use PharmaFEFO\Controller\Web\PreparateurController;
use PharmaFEFO\Entity\User;
use PharmaFEFO\Service\AuthService;

$route = $_GET['route'] ?? 'dashboard';

switch ($route) {

    case 'login':
        (new AuthController())->showLogin();
        break;

    case 'login/submit':
        (new AuthController())->login();
        break;

    case 'logout':
        (new AuthController())->logout();
        break;


    case 'dashboard':
        AuthService::requireLogin();
        (new DashboardController())->index();
        break;

    case 'preparateur/receive':
        AuthService::requireRole(User::ROLE_PREPARATEUR);
        (new PreparateurController())->receiveForm();
        break;

    case 'preparateur/dispatch':
        AuthService::requireRole(User::ROLE_PREPARATEUR);
        (new PreparateurController())->dispatchForm();
        break;

    case 'pharmacien/inventory':
        AuthService::requireRole(User::ROLE_PHARMACIEN);
        (new PharmacienController())->inventory();
        break;

    case 'pharmacien/thresholds':
        AuthService::requireRole(User::ROLE_PHARMACIEN);
        (new PharmacienController())->thresholds();
        break;

    case 'admin/users':
        AuthService::requireRole(User::ROLE_ADMIN);
        (new AdminController())->users();
        break;

    case 'admin/products':
        AuthService::requireRole(User::ROLE_ADMIN);
        (new AdminController())->products();
        break;

    case 'admin/reports':
        AuthService::requireRole(User::ROLE_ADMIN);
        (new AdminController())->reports();
        break;

  
    case 'api/v1/stock/add':
        (new ApiStockController())->add();
        break;

    case 'api/v1/batches':
        (new ApiDashboardController())->batches();
        break;

    case 'api/v1/dashboard/summary':
        (new ApiDashboardController())->summary();
        break;

    case 'api/v1/batches/checkout':
        (new ApiStockController())->checkout();
        break;

    default:
        if (preg_match('#^api/v1/batches/(\d+)/expire$#', $route, $m)) {
            $_GET['id'] = (int) $m[1];
            (new ApiStockController())->declareExpired();
            break;
        }

        if (preg_match('#^api/v1/batches/(\d+)/return-supplier$#', $route, $m)) {
            $_GET['id'] = (int) $m[1];
            (new ApiPharmacienController())->returnToSupplier();
            break;
        }

        http_response_code(404);
        if (str_starts_with($route, 'api/')) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'Route API introuvable.']);
        } else {
            echo "Page non trouvée.";
        }
        break;
}
