<?php
declare(strict_types=1);
/**
 * Non-HTML application bootstrap: config, DB/session, helpers, security, and
 * the App\ class autoloader (src/).
 *
 * Require this FIRST on any page that needs to run logic (and redirect) before
 * emitting HTML.
 */

require_once __DIR__ . '/http.php';
require_once __DIR__ . '/validation.php';

// Every response gets the same centrally-defined security policy before any
// page code can emit output.
send_security_headers();

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/security.php';

// Set application-wide default timezone
date_default_timezone_set('Asia/Kolkata');

// Prefer Composer's autoloader (also pulls in dev tooling); otherwise fall back
// to a minimal PSR-4 loader so the application runs with no Composer install.
$composer = __DIR__ . '/../vendor/autoload.php';
if (is_file($composer)) {
    require_once $composer;
} else {
    spl_autoload_register(static function (string $class): void {
        $prefix = 'App\\';
        $baseDir = __DIR__ . '/../src/';
        if (strncmp($class, $prefix, strlen($prefix)) !== 0) {
            return;
        }
        $relative = str_replace('\\', '/', substr($class, strlen($prefix))) . '.php';
        $file = $baseDir . $relative;
        if (is_file($file)) {
            require_once $file;
        }
    });
}
