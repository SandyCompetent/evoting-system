<?php
declare(strict_types=1);

namespace App\Tests\Unit;

use App\Support\Password;
use PHPUnit\Framework\TestCase;

final class PasswordTest extends TestCase
{
    public function testHashVerifiesAndUsesArgon2idWhenAvailable(): void
    {
        $hash = Password::hash('Secret123!');

        self::assertTrue(password_verify('Secret123!', $hash));

        if (defined('PASSWORD_ARGON2ID')) {
            self::assertTrue(str_starts_with($hash, '$argon2id$'));
            self::assertFalse(Password::needsRehash($hash));
        }
    }

    public function testNeedsRehashDetectsLegacyBcrypt(): void
    {
        $bcrypt = password_hash('Secret123!', PASSWORD_BCRYPT);

        if (defined('PASSWORD_ARGON2ID')) {
            self::assertTrue(Password::needsRehash($bcrypt));
        } else {
            self::assertFalse(Password::needsRehash($bcrypt));
        }
    }
}
