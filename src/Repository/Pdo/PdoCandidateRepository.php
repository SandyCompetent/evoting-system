<?php
declare(strict_types=1);

namespace App\Repository\Pdo;

use App\Repository\CandidateRepository;
use PDO;

final class PdoCandidateRepository implements CandidateRepository
{
    public function __construct(private readonly PDO $pdo) {}

    public function belongsToCategory(int $candidateId, int $categoryId): bool
    {
        $stmt = $this->pdo->prepare(
            'SELECT 1 FROM candidate WHERE id = ? AND category_id = ?'
        );
        $stmt->execute([$candidateId, $categoryId]);
        return $stmt->fetchColumn() !== false;
    }
}
