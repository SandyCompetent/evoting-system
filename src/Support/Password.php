<?php
declare(strict_types=1);

namespace App\Support;

use RuntimeException;

/**
 * Single source of truth for password hashing.
 *
 * Prefers Argon2id. PHP builds compiled without Argon2 support fall back to
 * PASSWORD_DEFAULT so account creation cannot fatal with an undefined constant.
 */
final class Password
{
    public static function hash(string $plain): string
    {
        $hash = password_hash($plain, self::algorithm());
        if ($hash === false) {
            throw new RuntimeException('Password hashing failed.');
        }

        return $hash;
    }

    public static function needsRehash(string $hash): bool
    {
        return password_needs_rehash($hash, self::algorithm());
    }

    private static function algorithm(): string
    {
        return defined('PASSWORD_ARGON2ID') ? PASSWORD_ARGON2ID : PASSWORD_DEFAULT;
    }
}
