<?php
declare(strict_types=1);

namespace App\Repository\Pdo;

use App\Domain\Election;
use App\Repository\CategoryRepository;
use PDO;

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
