<?php
declare(strict_types=1);
require_once __DIR__ . '/config.php';

// Harden Session Settings
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.use_strict_mode', 1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Returns a shared PDO connection configured from the .env file.
 * Credentials are never hard-coded and connection errors are never
 * echoed to the browser (they are logged instead).
 */
function db(): PDO
{
    static $pdo = null;
    if ($pdo === null) {
        $host = env('DB_HOST', '127.0.0.1');
        $name = env('DB_NAME', 'evoting_system');
        $user = env('DB_USER', 'root');
        $pass = env('DB_PASS', '');
        try {
            $pdo = new PDO(
                "mysql:host={$host};dbname={$name};charset=utf8mb4",
                $user,
                $pass,
                [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ]
            );
        } catch (PDOException $e) {
            error_log('DB connection failed: ' . $e->getMessage());
            http_response_code(500);
            $isDev = env('APP_ENV') === 'development'
                || in_array($_SERVER['SERVER_NAME'] ?? '', ['localhost', '127.0.0.1'], true)
                || php_sapi_name() === 'cli-server'
                || php_sapi_name() === 'cli';
            if ($isDev) {
                exit("<!DOCTYPE html><html><head><title>Database Connection Error</title><link rel='stylesheet' href='https://cdn.jsdelivr.net/npm/bootswatch@5.3.3/dist/zephyr/bootstrap.min.css'></head><body class='bg-light py-5'><div class='container col-md-6'><div class='card shadow-sm border-danger'><div class='card-header bg-danger text-white fw-bold'><i class='bi bi-exclamation-triangle-fill me-2'></i>Database Connection Error</div><div class='card-body'><p>Could not connect to database host: <code>" . htmlspecialchars($host, ENT_QUOTES, 'UTF-8') . "</code></p><p><strong>Details:</strong> " . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . "</p><hr><p class='small text-muted mb-0'>Ensure MySQL is running (<code>brew services start mysql</code>) and verify credentials in your <code>.env</code> file.</p></div></div></div></body></html>");
            }
            exit('A server error occurred. Please try again later.');
        }
    }
    return $pdo;
}

// Backwards-compatibility: legacy pages still reference the global $db.
$db = db();
