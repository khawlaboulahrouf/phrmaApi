<?php


namespace PharmaFEFO\Config;

class Environment
{
    private static bool $loaded = false;

    /**
     * Charge le fichier .env situé à la racine du projet (s'il existe)
     * et définit les variables via putenv()/$_ENV.
     */
    public static function load(): void
    {
        if (self::$loaded) {
            return;
        }

        $envFile = dirname(__DIR__) . '/.env';

        if (file_exists($envFile)) {
            $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                $line = trim($line);
                if ($line === '' || str_starts_with($line, '#')) {
                    continue;
                }
                [$name, $value] = array_pad(explode('=', $line, 2), 2, '');
                $name = trim($name);
                $value = trim($value, " \t\n\r\0\x0B\"'");
                if ($name !== '' && getenv($name) === false) {
                    putenv("$name=$value");
                    $_ENV[$name] = $value;
                }
            }
        }

        self::$loaded = true;
        self::configureErrorHandling();
    }

    public static function get(string $key, ?string $default = null): ?string
    {
        $value = getenv($key);
        return $value !== false ? $value : $default;
    }

    public static function isProduction(): bool
    {
        return self::get('APP_ENV', 'development') === 'production';
    }

    
    private static function configureErrorHandling(): void
    {
        if (self::isProduction()) {
            error_reporting(0);
            ini_set('display_errors', '0');

            set_exception_handler(function (\Throwable $e): void {
                http_response_code(500);

                if (self::wantsJson()) {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => false, 'error' => 'Erreur interne du serveur.']);
                } else {
                    echo '<h1>Erreur 500</h1><p>Une erreur interne est survenue. Merci de réessayer plus tard.</p>';
                }
            });
        } else {
            error_reporting(E_ALL);
            ini_set('display_errors', '1');
        }
    }

    private static function wantsJson(): bool
    {
        $route = $_GET['route'] ?? '';
        return str_starts_with($route, 'api/');
    }
}
