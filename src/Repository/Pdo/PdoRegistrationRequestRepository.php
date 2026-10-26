<?php
declare(strict_types=1);

namespace App\Repository\Pdo;

use PDO;

final class PdoRegistrationRequestRepository
{
    public function __construct(private readonly PDO $pdo) {}

    public function create(
        string $fullName,
        string $email,
        string $category,
        int $maxVoter,
        string $adultVoter,
        string $expireTimestamp,
    ): int {
        $stmt = $this->pdo->prepare(
            "INSERT INTO request_to_register
                (full_name, email, election_category, max_voter, adult_voter, expire_timestamp, ac_status)
             VALUES (?, ?, ?, ?, ?, ?, 'pending')"
        );
        $stmt->execute([$fullName, $email, $category, $maxVoter, $adultVoter, $expireTimestamp]);

        return (int) $this->pdo->lastInsertId();
    }

    /**
     * @return array<int,array<string,mixed>>
     */
    public function pending(): array
    {
        $stmt = $this->pdo->query(
            "SELECT * FROM request_to_register
              WHERE LOWER(ac_status) = 'pending'
              ORDER BY timestamp DESC"
        );

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findPending(int $id): ?array
    {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM request_to_register
              WHERE id = ? AND LOWER(ac_status) = 'pending'"
        );
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ?: null;
    }

    public function markApproved(int $id): void
    {
        $stmt = $this->pdo->prepare(
            "UPDATE request_to_register SET ac_status = 'Approved' WHERE id = ?"
        );
        $stmt->execute([$id]);
    }

    public function reject(int $id): bool
    {
        $stmt = $this->pdo->prepare(
            "UPDATE request_to_register SET ac_status = 'Rejected'
              WHERE id = ? AND LOWER(ac_status) = 'pending'"
        );
        $stmt->execute([$id]);

        return $stmt->rowCount() > 0;
    }

    public function countPending(): int
    {
        return (int) $this->pdo->query(
            "SELECT COUNT(*) FROM request_to_register WHERE LOWER(ac_status) = 'pending'"
        )->fetchColumn();
    }
}
