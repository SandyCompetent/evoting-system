<?php
declare(strict_types=1);
/**
 * Single source of truth for URL helpers, CDN asset URLs, and HTTP security
 * headers.
 *
 * The asset constants are deliberately the only place a CDN origin is written
 * down. The Content-Security-Policy below is built from those same constants,
 * so a new asset origin cannot silently drift out of the policy.
 */

const ASSET_CDN          = 'https://cdn.jsdelivr.net';
const ASSET_GSTATIC      = 'https://www.gstatic.com';
const ASSET_FONTS        = 'https://fonts.googleapis.com';
const ASSET_FONTS_STATIC = 'https://fonts.gstatic.com';

const BOOTSTRAP_CSS        = ASSET_CDN . '/npm/bootswatch@5.3.3/dist/zephyr/bootstrap.min.css';
const BOOTSTRAP_JS         = ASSET_CDN . '/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js';
const BOOTSTRAP_ICONS_CSS  = ASSET_CDN . '/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css';
const GOOGLE_CHARTS_LOADER = ASSET_GSTATIC . '/charts/loader.js';

/**
 * Application root as seen by the browser ('' at a domain root,
 * '/evoting' for a sub-directory install). Works from the root pages and from
 * the admin/, organiser/ and voter/ sub-directories.
 */
function app_root(): string
{
    $script = str_replace('\\', '/', (string) ($_SERVER['SCRIPT_NAME'] ?? ''));
    $dir = rtrim(dirname($script), '/');
    if (preg_match('#/(admin|organiser|voter)$#', $dir) === 1) {
        $dir = rtrim(dirname($dir), '/');
    }

    return ($dir === '' || $dir === '.' || $dir === '/') ? '' : $dir;
}

/**
 * Public URL for a project-relative asset, e.g. asset_url('assets/app.js').
 */
function asset_url(string $path): string
{
    return app_root() . '/' . ltrim($path, '/');
}

/**
 * Safe current script path for form actions and redirects. Unlike PHP_SELF,
 * this never includes PATH_INFO supplied by the request.
 */
function current_path(): string
{
    return (string) ($_SERVER['SCRIPT_NAME'] ?? '/');
}

/**
 * True when this request was served over HTTPS.
 *
 * X-Forwarded-Proto is deliberately not trusted here: a client can forge it
 * unless a configured reverse proxy is guaranteed to overwrite it.
 */
function is_https(): bool
{
    if (!empty($_SERVER['HTTPS']) && strtolower((string) $_SERVER['HTTPS']) !== 'off') {
        return true;
    }

    return (int) ($_SERVER['SERVER_PORT'] ?? 0) === 443;
}

/**
 * Content-Security-Policy for every application response.
 *
 * script-src has no 'unsafe-inline': all JavaScript is loaded from external
 * files. style-src has no 'unsafe-inline' either; the only inline styles the
 * application itself emits are class-based after the 2026-10-26 cleanup.
 * Google Charts may ask for style attributes at runtime; if the browser
 * reports that, a scoped style-src-attr exception is added deliberately and
 * documented rather than weakening script-src.
 */
function content_security_policy(): string
{
    $scriptSrc = ["'self'", ASSET_CDN, ASSET_GSTATIC];
    $styleSrc  = ["'self'", ASSET_CDN, ASSET_FONTS, ASSET_GSTATIC];
    $fontSrc   = ["'self'", ASSET_CDN, ASSET_FONTS_STATIC];

    // The Google Charts webfont helper derives its stylesheet URL from the
    // page protocol, so a plain-HTTP local preview asks for the HTTP font
    // origin. Production (HTTPS) never gets this exception.
    if (!is_https()) {
        $styleSrc[] = 'http://fonts.googleapis.com';
    }

    return implode('; ', [
        "default-src 'self'",
        "base-uri 'self'",
        "form-action 'self'",
        "frame-ancestors 'self'",
        "object-src 'none'",
        'script-src ' . implode(' ', $scriptSrc),
        'style-src ' . implode(' ', $styleSrc),
        'font-src ' . implode(' ', $fontSrc),
        "img-src 'self' data:",
        'connect-src ' . implode(' ', ["'self'", ASSET_GSTATIC]),
        // Google Charts positions its SVG output with style attributes at
        // runtime. This exception is deliberately limited to style
        // attributes: script-src stays strict and the application itself has
        // no inline styles left in its own markup.
        "style-src-attr 'unsafe-inline'",
    ]);
}

/**
 * @return array<string,string>
 */
function security_headers(): array
{
    $headers = [
        'X-Content-Type-Options'  => 'nosniff',
        'X-Frame-Options'         => 'SAMEORIGIN',
        'Referrer-Policy'         => 'strict-origin-when-cross-origin',
        'Permissions-Policy'      => 'camera=(), microphone=(), geolocation=()',
        'Content-Security-Policy' => content_security_policy(),
    ];

    // HSTS is only meaningful (and only sent) over HTTPS.
    if (is_https()) {
        $headers['Strict-Transport-Security'] = 'max-age=31536000; includeSubDomains';
    }

    return $headers;
}

function send_security_headers(): void
{
    foreach (security_headers() as $name => $value) {
        header($name . ': ' . $value);
    }
}
