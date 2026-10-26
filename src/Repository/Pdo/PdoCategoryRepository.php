<?php
declare(strict_types=1);

namespace App\Repository\Pdo;

use App\Domain\Election;
use App\Repository\CategoryRepository;
use PDO;
use PDOException;

final class PdoCategoryRepository implements CategoryRepository
{
    public function __construct(private readonly PDO $pdo) {}

    public function findByVoter(int $voterId): ?Election
    {
        $stmt = $this->pdo->prepare(
            'SELECT ec.id, ec.name, ec.vote_start_time, ec.vote_end_time
               FROM system_users u
               JOIN election_category ec ON ec.id = u.election_category_id
              WHERE u.user_id = ?'
        );
        $stmt->execute([$voterId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? $this->hydrate($row) : null;
    }

    public function find(int $categoryId): ?Election
    {
        $stmt = $this->pdo->prepare(
            'SELECT id, name, vote_start_time, vote_end_time FROM election_category WHERE id = ?'
        );
        $stmt->execute([$categoryId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? $this->hydrate($row) : null;
    }

    /**
     * @return Election[]
     */
    public function all(): array
    {
        $stmt = $this->pdo->query(
            'SELECT id, name, vote_start_time, vote_end_time FROM election_category ORDER BY name ASC'
        );
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_map(fn(array $r) => $this->hydrate($r), $rows);
    }

    public function countVoters(int $categoryId): int
    {
        $stmt = $this->pdo->prepare(
            "SELECT COUNT(*) FROM system_users WHERE role = 'voter' AND election_category_id = ?"
        );
        $stmt->execute([$categoryId]);
        return (int) $stmt->fetchColumn();
    }

    public function updateVotingWindow(int $id, string $start, string $end): void
    {
        $stmt = $this->pdo->prepare(
            'UPDATE election_category SET vote_start_time = ?, vote_end_time = ? WHERE id = ?'
        );
        $stmt->execute([$start, $end, $id]);
    }

    /**
     * @return array<int,array<string,mixed>>
     */
    public function allRows(): array
    {
        $stmt = $this->pdo->query('SELECT * FROM election_category ORDER BY name ASC');

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findRow(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM election_category WHERE id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ?: null;
    }

    public function create(
        string $name,
        int $maxVoter,
        string $adultVoter,
        string $expireTimestamp,
        string $voteStart,
        string $voteEnd,
    ): int {
        $stmt = $this->pdo->prepare(
            'INSERT INTO election_category
                (name, max_voter, adult_voter, expire_timestamp, vote_start_time, vote_end_time)
             VALUES (?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([$name, $maxVoter, $adultVoter, $expireTimestamp, $voteStart, $voteEnd]);

        return (int) $this->pdo->lastInsertId();
    }

    public function update(int $id, string $name, string $expireTimestamp): void
    {
        $stmt = $this->pdo->prepare(
            'UPDATE election_category SET name = ?, expire_timestamp = ? WHERE id = ?'
        );
        $stmt->execute([$name, $expireTimestamp, $id]);
    }

    public function count(): int
    {
        return (int) $this->pdo->query('SELECT COUNT(*) FROM election_category')->fetchColumn();
    }

    public function deleteWithCascade(int $id): void
    {
        try {
            $this->pdo->beginTransaction();
            $stmt1 = $this->pdo->prepare('DELETE FROM vote WHERE category_id = ?');
            $stmt1->execute([$id]);

            $stmt2 = $this->pdo->prepare('DELETE FROM candidate WHERE category_id = ?');
            $stmt2->execute([$id]);

            $stmt3 = $this->pdo->prepare("DELETE FROM system_users WHERE election_category_id = ? AND role <> 'admin'");
            $stmt3->execute([$id]);

            $stmt4 = $this->pdo->prepare('DELETE FROM election_category WHERE id = ?');
            $stmt4->execute([$id]);

            $this->pdo->commit();
        } catch (PDOException $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $e;
        }
    }

    private function hydrate(array $row): Election
    {
        return new Election(
            (int) $row['id'],
            (string) $row['name'],
            $this->toDate($row['vote_start_time'] ?? null),
            $this->toDate($row['vote_end_time'] ?? null),
        );
    }

    private function toDate(?string $value): ?\DateTimeImmutable
    {
        if ($value === null || trim($value) === '' || str_starts_with($value, '0000')) {
            return null;
        }
        try {
            return new \DateTimeImmutable($value);
        } catch (\Exception) {
            return null;
        }
    }
}
