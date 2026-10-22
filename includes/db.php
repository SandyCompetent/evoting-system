<?php
require_once __DIR__ . '/config.php';

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
            exit('A server error occurred. Please try again later.');
        }
    }
    return $pdo;
}

// Backwards-compatibility: legacy pages still reference the global $db.
$db = db();
