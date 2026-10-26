<?php
declare(strict_types=1);

namespace App\Tests\Unit;

use App\Service\AuthService;
use App\Tests\Fake\InMemoryUserRepository;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class AuthServiceTest extends TestCase
{
    private InMemoryUserRepository $users;
    private AuthService $auth;

    protected function setUp(): void
    {
        $this->users = new InMemoryUserRepository();
        $this->auth = new AuthService($this->users);
    }

    public function testAuthenticateSuccessWithBcrypt(): void
    {
        $hash = password_hash('Secret123!', PASSWORD_DEFAULT);
        $this->users->addUser(1, 'Alice Admin', 'alice@test.com', $hash, 'admin');

        $user = $this->auth->authenticate('alice@test.com', 'Secret123!');
        self::assertNotNull($user);
        self::assertSame(1, $user->id);
        self::assertSame('admin', $user->role);
    }

    public function testAuthenticateRehashesBcryptToArgon2id(): void
    {
        $hash = password_hash('Secret123!', PASSWORD_BCRYPT);
        $this->users->addUser(12, 'Dana Voter', 'dana@test.com', $hash, 'voter');

        $user = $this->auth->authenticate('dana@test.com', 'Secret123!');
        self::assertNotNull($user);

        $updated = $this->users->findByEmail('dana@test.com');
        self::assertNotNull($updated);
        self::assertTrue(password_verify('Secret123!', $updated['password']));
        if (defined('PASSWORD_ARGON2ID')) {
            self::assertTrue(str_starts_with($updated['password'], '$argon2id$'));
        }
    }

    public function testAuthenticateUpgradesSha1Transparently(): void
    {
        $sha1 = sha1('OldPassword1');
        $this->users->addUser(2, 'Bob Organiser', 'bob@test.com', $sha1, 'organiser');

        $user = $this->auth->authenticate('bob@test.com', 'OldPassword1');
        self::assertNotNull($user);
        self::assertSame('bob@test.com', $user->email);

        // Verify transparent upgrade in repo
        $updatedUser = $this->users->findByEmail('bob@test.com');
        self::assertNotNull($updatedUser);
        self::assertTrue(password_verify('OldPassword1', $updatedUser['password']));
    }

    public function testAuthenticateFailsOnWrongPassword(): void
    {
        $hash = password_hash('Secret123!', PASSWORD_DEFAULT);
        $this->users->addUser(1, 'Alice', 'alice@test.com', $hash);

        $user = $this->auth->authenticate('alice@test.com', 'WrongPassword');
        self::assertNull($user);
    }

    public function testAuthenticateFailsOnNonexistentEmail(): void
    {
        $user = $this->auth->authenticate('unknown@test.com', 'Password');
        self::assertNull($user);
    }

    public function testChangePasswordSuccess(): void
    {
        $hash = password_hash('CurrentPass1', PASSWORD_DEFAULT);
        $this->users->addUser(5, 'Voter Five', 'voter5@test.com', $hash, 'voter');

        $this->auth->changePassword(5, 'CurrentPass1', 'NewSecretPass99', 'NewSecretPass99');

        $row = $this->users->findByEmail('voter5@test.com');
        self::assertTrue(password_verify('NewSecretPass99', $row['password']));
    }

    public function testChangePasswordRejectsEmptyFields(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->auth->changePassword(1, '', 'NewPass123', 'NewPass123');
    }

    public function testChangePasswordRejectsSameOldAndNew(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->auth->changePassword(1, 'Identical1', 'Identical1', 'Identical1');
    }

    public function testChangePasswordRejectsMismatch(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->auth->changePassword(1, 'OldPass123', 'NewPass123', 'Different123');
    }

    public function testChangePasswordRejectsTooShort(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->auth->changePassword(1, 'OldPass123', 'short', 'short');
    }

    public function testChangePasswordRejectsWrongOldPassword(): void
    {
        $hash = password_hash('RealOldPassword', PASSWORD_DEFAULT);
        $this->users->addUser(10, 'Carol', 'carol@test.com', $hash);

        $this->expectException(InvalidArgumentException::class);
        $this->auth->changePassword(10, 'WrongOldPass', 'BrandNewPass123', 'BrandNewPass123');
    }
}
