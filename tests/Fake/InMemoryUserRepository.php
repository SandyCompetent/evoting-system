<?php
declare(strict_types=1);

namespace App\Tests\Fake;

use App\Domain\User;
use App\Repository\UserRepository;

final class InMemoryUserRepository implements UserRepository
{
    /** @var array<int,array{user_id:int,full_name:string,email:string,password:string,role:string,election_category_id:?int,date_of_birth:?string,voted:string}> */
    public array $users = [];

    public function addUser(int $id, string $name, string $email, string $passwordHash, string $role = 'voter', ?int $catId = null): void
    {
        $this->users[$id] = [
            'user_id' => $id,
            'full_name' => $name,
            'email' => $email,
            'password' => $passwordHash,
            'role' => $role,
            'election_category_id' => $catId,
            'date_of_birth' => '2000-01-01',
            'voted' => 'No',
        ];
    }

    public function findById(int $id): ?User
    {
        $r = $this->users[$id] ?? null;
        if (!$r) return null;
        return new User($r['user_id'], $r['full_name'], $r['email'], $r['role'], $r['election_category_id'], $r['date_of_birth'], $r['voted']);
    }

    public function findByEmail(string $email): ?array
    {
        foreach ($this->users as $u) {
            if ($u['email'] === $email) {
                return $u;
            }
        }
        return null;
    }

    public function findOrganisers(): array
    {
        $res = [];
        foreach ($this->users as $u) {
            if ($u['role'] === 'organiser') {
                $res[] = new User($u['user_id'], $u['full_name'], $u['email'], $u['role'], $u['election_category_id']);
            }
        }
        return $res;
    }

    public function findVotersByCategory(int $categoryId): array
    {
        $res = [];
        foreach ($this->users as $u) {
            if ($u['role'] === 'voter' && $u['election_category_id'] === $categoryId) {
                $res[] = new User(
                    $u['user_id'],
                    $u['full_name'],
                    $u['email'],
                    $u['role'],
                    $u['election_category_id'],
                    $u['date_of_birth'] ?? null,
                    $u['voted'] ?? 'No',
                    $u['created_timestamp'] ?? '2026-01-01 00:00:00',
                );
            }
        }
        return $res;
    }

    public function updatePassword(int $userId, string $passwordHash): void
    {
        if (isset($this->users[$userId])) {
            $this->users[$userId]['password'] = $passwordHash;
        }
    }
}
