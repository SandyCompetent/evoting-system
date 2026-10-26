<?php
declare(strict_types=1);

namespace App\Service;

use App\Domain\User;
use App\Repository\UserRepository;
use App\Support\Password;
use InvalidArgumentException;

final class AuthService
{
    public function __construct(private readonly UserRepository $users) {}

    public function authenticate(string $email, string $password): ?User
    {
        $row = $this->users->findByEmail($email);
        if (!$row) {
            return null;
        }

        $hash = (string) ($row['password'] ?? '');
        $valid = password_verify($password, $hash)
            || hash_equals($hash, sha1($password));

        if (!$valid) {
            return null;
        }

        // Upgrade legacy SHA-1 and bcrypt hashes on the next successful login.
        if (Password::needsRehash($hash)) {
            $this->users->updatePassword((int) $row['user_id'], Password::hash($password));
        }

        return new User(
            (int) $row['user_id'],
            (string) $row['full_name'],
            (string) $row['email'],
            (string) $row['role'],
            isset($row['election_category_id']) ? (int) $row['election_category_id'] : null,
            isset($row['date_of_birth']) ? (string) $row['date_of_birth'] : null,
            (string) ($row['voted'] ?? 'No'),
        );
    }

    /**
     * @throws InvalidArgumentException
     */
    public function changePassword(int $userId, string $oldPassword, string $newPassword, string $confirmPassword): void
    {
        if ($oldPassword === '' || $newPassword === '' || $confirmPassword === '') {
            throw new InvalidArgumentException('Please enter all mandatory fields.');
        }

        if ($oldPassword === $newPassword) {
            throw new InvalidArgumentException('Old password and new password cannot be same.');
        }

        if ($newPassword !== $confirmPassword) {
            throw new InvalidArgumentException('The password and confirmation password are not matching.');
        }

        if (strlen($newPassword) < 8) {
            throw new InvalidArgumentException('Password must be at least 8 characters.');
        }

        $user = $this->users->findById($userId);
        if (!$user) {
            throw new InvalidArgumentException('User account not found.');
        }

        $row = $this->users->findByEmail($user->email);
        $currentHash = (string) ($row['password'] ?? '');

        $isMatch = password_verify($oldPassword, $currentHash) || hash_equals($currentHash, sha1($oldPassword));
        if (!$isMatch) {
            throw new InvalidArgumentException('Incorrect old password.');
        }

        $this->users->updatePassword($userId, Password::hash($newPassword));
    }
}
