<?php
/**
 * Minimal .env loader (no external dependencies).
 * Reads KEY=VALUE pairs from the project-root .env file once and caches them.
 */
function env(string $key, $default = null)
{
    static $vars = null;
    if ($vars === null) {
        $vars = [];
        $path = __DIR__ . '/../.env';
        if (is_readable($path)) {
            foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
                $line = trim($line);
                if ($line === '' || $line[0] === '#') {
                    continue;
                }
                $parts = array_pad(explode('=', $line, 2), 2, '');
                $vars[trim($parts[0])] = trim($parts[1]);
            }
        }
    }
    return array_key_exists($key, $vars) ? $vars[$key] : $default;
}
