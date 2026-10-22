<?php
declare(strict_types=1);

namespace App\Repository\Pdo;

use App\Domain\User;
use App\Repository\UserRepository;
use PDO;

final class PdoUserRepository implements UserRepository
{
    public function __construct(private readonly PDO $pdo) {}

    public function findById(int $id): ?User
    {
        $stmt = $this->pdo->prepare(
            'SELECT user_id, full_name, email, role, election_category_id, date_of_birth, voted FROM system_users WHERE user_id = ?'
        );
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ? $this->hydrate($row) : null;
    }

    public function findByEmail(string $email): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT * FROM system_users WHERE email = ?'
        );
        $stmt->execute([$email]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ?: null;
    }

    /**
     * @return User[]
     */
    public function findOrganisers(): array
    {
        $stmt = $this->pdo->query(
            "SELECT user_id, full_name, email, role, election_category_id, date_of_birth, voted FROM system_users WHERE role = 'organiser' ORDER BY full_name ASC"
        );
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_map(fn(array $r) => $this->hydrate($r), $rows);
    }

    /**
     * @return User[]
     */
    public function findVotersByCategory(int $categoryId): array
    {
        $stmt = $this->pdo->prepare(
            "SELECT user_id, full_name, email, role, election_category_id, date_of_birth, voted, created_timestamp FROM system_users WHERE role = 'voter' AND election_category_id = ? ORDER BY full_name ASC"
        );
        $stmt->execute([$categoryId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_map(fn(array $r) => $this->hydrate($r), $rows);
    }

    public function updatePassword(int $userId, string $passwordHash): void
    {
        $stmt = $this->pdo->prepare(
            'UPDATE system_users SET password = ? WHERE user_id = ?'
        );
        $stmt->execute([$passwordHash, $userId]);
    }

    private function hydrate(array $row): User
    {
        return new User(
            (int) $row['user_id'],
            (string) $row['full_name'],
            (string) $row['email'],
            (string) $row['role'],
            isset($row['election_category_id']) ? (int) $row['election_category_id'] : null,
            isset($row['date_of_birth']) ? (string) $row['date_of_birth'] : null,
            (string) ($row['voted'] ?? 'No'),
            isset($row['created_timestamp']) ? (string) $row['created_timestamp'] : null,
        );
    }
}
