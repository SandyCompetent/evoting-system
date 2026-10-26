<?php
declare(strict_types=1);

namespace App\Repository\Pdo;

use App\Domain\User;
use App\Repository\UserRepository;
use PDO;
use PDOException;
use RuntimeException;

final class PdoUserRepository implements UserRepository
{
    public function __construct(private readonly PDO $pdo) {}

    public function findById(int $id): ?User
    {
        $stmt = $this->pdo->prepare(
            'SELECT user_id, full_name, email, role, election_category_id, date_of_birth, voted, created_timestamp FROM system_users WHERE user_id = ?'
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

    public function categoryIdForUser(int $userId): ?int
    {
        $stmt = $this->pdo->prepare('SELECT election_category_id FROM system_users WHERE user_id = ?');
        $stmt->execute([$userId]);
        $value = $stmt->fetchColumn();

        return ($value === false || $value === null) ? null : (int) $value;
    }

    public function findOrganiserRow(int $id): ?array
    {
        $stmt = $this->pdo->prepare(
            "SELECT user_id, full_name, email, role, election_category_id, date_of_birth, voted, created_timestamp
               FROM system_users
              WHERE user_id = ? AND role = 'organiser'"
        );
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ?: null;
    }

    /**
     * @return array<int,array<string,mixed>>
     */
    public function findOrganisersWithCategory(): array
    {
        $stmt = $this->pdo->query(
            "SELECT u.user_id, u.full_name, u.email, u.election_category_id, u.created_timestamp, c.name AS category_name
               FROM system_users u
               LEFT JOIN election_category c ON c.id = u.election_category_id
              WHERE u.role = 'organiser'
              ORDER BY u.full_name ASC"
        );

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findVoterInCategory(int $voterId, int $categoryId): ?array
    {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM system_users WHERE user_id = ? AND role = 'voter' AND election_category_id = ?"
        );
        $stmt->execute([$voterId, $categoryId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ?: null;
    }

    public function createOrganiser(string $fullName, string $email, string $passwordHash, int $categoryId): int
    {
        $stmt = $this->pdo->prepare(
            "INSERT INTO system_users (full_name, email, password, role, election_category_id)
             VALUES (?, ?, ?, 'organiser', ?)"
        );
        $stmt->execute([$fullName, $email, $passwordHash, $categoryId]);

        return (int) $this->pdo->lastInsertId();
    }

    public function createVoter(string $fullName, string $email, ?string $dateOfBirth, string $passwordHash, int $categoryId): int
    {
        $stmt = $this->pdo->prepare(
            "INSERT INTO system_users
                (full_name, date_of_birth, email, password, role, election_category_id, voted)
             VALUES (?, ?, ?, ?, 'voter', ?, 'No')"
        );
        $stmt->execute([$fullName, $dateOfBirth, $email, $passwordHash, $categoryId]);

        return (int) $this->pdo->lastInsertId();
    }

    /**
     * Insert a batch of voter rows in one transaction.
     *
     * @param array<int,array{full_name:string,email:string,date_of_birth:?string,password_hash:string}> $rows
     *
     * @throws RuntimeException when any row fails; no rows are committed.
     */
    public function importVoters(array $rows, int $categoryId): int
    {
        $this->pdo->beginTransaction();
        $index = 0;

        try {
            $insert = $this->pdo->prepare(
                "INSERT INTO system_users
                    (full_name, email, date_of_birth, password, role, election_category_id, voted)
                 VALUES (?, ?, ?, ?, 'voter', ?, 'No')"
            );

            foreach ($rows as $index => $row) {
                $insert->execute([
                    $row['full_name'],
                    $row['email'],
                    $row['date_of_birth'],
                    $row['password_hash'],
                    $categoryId,
                ]);
            }

            $this->pdo->commit();

            return count($rows);
        } catch (PDOException $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            throw new RuntimeException('Import aborted on row ' . ($index + 1) . '; no voters were added.', 0, $e);
        }
    }

    public function updateOrganiser(int $id, string $fullName, string $email, int $categoryId, ?string $passwordHash): void
    {
        if ($passwordHash !== null) {
            $stmt = $this->pdo->prepare(
                'UPDATE system_users SET full_name = ?, email = ?, password = ?, election_category_id = ? WHERE user_id = ?'
            );
            $stmt->execute([$fullName, $email, $passwordHash, $categoryId, $id]);
            return;
        }

        $stmt = $this->pdo->prepare(
            'UPDATE system_users SET full_name = ?, email = ?, election_category_id = ? WHERE user_id = ?'
        );
        $stmt->execute([$fullName, $email, $categoryId, $id]);
    }

    public function updateVoter(int $id, int $categoryId, string $fullName, string $email, ?string $passwordHash): void
    {
        if ($passwordHash !== null) {
            $stmt = $this->pdo->prepare(
                'UPDATE system_users SET full_name = ?, email = ?, password = ? WHERE user_id = ? AND election_category_id = ?'
            );
            $stmt->execute([$fullName, $email, $passwordHash, $id, $categoryId]);
            return;
        }

        $stmt = $this->pdo->prepare(
            'UPDATE system_users SET full_name = ?, email = ? WHERE user_id = ? AND election_category_id = ?'
        );
        $stmt->execute([$fullName, $email, $id, $categoryId]);
    }

    public function deleteOrganiser(int $id): bool
    {
        $stmt = $this->pdo->prepare("DELETE FROM system_users WHERE user_id = ? AND role = 'organiser'");
        $stmt->execute([$id]);

        return $stmt->rowCount() > 0;
    }

    public function deleteVoter(int $id, int $categoryId): bool
    {
        $stmt = $this->pdo->prepare(
            "DELETE FROM system_users WHERE user_id = ? AND role = 'voter' AND election_category_id = ?"
        );
        $stmt->execute([$id, $categoryId]);

        return $stmt->rowCount() > 0;
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
