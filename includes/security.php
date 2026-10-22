<?php
/**
 * Output escaping. Use e($value) everywhere untrusted data is printed into HTML.
 */
function e($value): string
{
    return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES, 'UTF-8');
}

/**
 * CSRF protection. Put csrf_field() inside every POST <form>, and call
 * csrf_verify() at the top of every POST handler.
 */
function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

function csrf_verify(): void
{
    $sent = $_POST['csrf_token'] ?? '';
    if (!is_string($sent) || !hash_equals(csrf_token(), $sent)) {
        http_response_code(419);
        exit('Invalid or expired form token. Please go back and try again.');
    }
}

/**
 * Safe server-side redirect. Must be called before any output is sent
 * (converted pages run their handlers via bootstrap.php, before header.php).
 */
function redirect(string $path): void
{
    header('Location: ' . $path);
    exit;
}

/**
 * Role guard. Call at the very top of a page (after bootstrap, before output)
 * so an unauthorised user is redirected with a real Location header.
 */
function require_role(string $role, string $loginPath = '../index.php'): void
{
    if (($_SESSION['role'] ?? '') !== $role) {
        redirect($loginPath);
    }
}

/**
 * Listing pages render per-row buttons named "<prefix><id>" (e.g. delete42).
 * Returns the integer id of the pressed button for the given prefix, or null.
 */
function pressed_action(string $prefix): ?int
{
    foreach (array_keys($_POST) as $key) {
        if (strncmp($key, $prefix, strlen($prefix)) === 0) {
            $id = substr($key, strlen($prefix));
            if (ctype_digit($id)) {
                return (int) $id;
            }
        }
    }
    return null;
}

/**
 * Neutralise CSV / spreadsheet formula injection on a single cell value.
 */
function sanitize_csv_cell(string $value): string
{
    return preg_match('/^[=\-+@\t\r]/', $value) === 1 ? "'" . $value : $value;
}

/**
 * Validate + store an uploaded image, returning a safe random basename.
 * Throws RuntimeException on any problem. Prevents the original-filename /
 * web-served-path and content-type issues in the legacy upload code.
 */
function store_uploaded_image(array $file, string $destDir): string
{
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK || !is_uploaded_file($file['tmp_name'])) {
        throw new RuntimeException('Upload failed.');
    }
    if ($file['size'] > 2 * 1024 * 1024) {
        throw new RuntimeException('Image too large (max 2 MB).');
    }
    $info = @getimagesize($file['tmp_name']);
    $allowed = [IMAGETYPE_JPEG => 'jpg', IMAGETYPE_PNG => 'png', IMAGETYPE_GIF => 'gif'];
    if ($info === false || !isset($allowed[$info[2]])) {
        throw new RuntimeException('Only JPG, PNG or GIF images are allowed.');
    }
    if (!is_dir($destDir) && !mkdir($destDir, 0755, true) && !is_dir($destDir)) {
        throw new RuntimeException('Upload directory unavailable.');
    }
    $name = bin2hex(random_bytes(16)) . '.' . $allowed[$info[2]];
    if (!move_uploaded_file($file['tmp_name'], rtrim($destDir, '/') . '/' . $name)) {
        throw new RuntimeException('Could not save the uploaded image.');
    }
    return $name;
}
