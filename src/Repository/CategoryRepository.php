<?php
declare(strict_types=1);

namespace App\Repository;

use App\Domain\Election;

interface CategoryRepository
{
    public function findByVoter(int $voterId): ?Election;

    public function find(int $categoryId): ?Election;

    /**
     * @return Election[]
     */
    public function all(): array;

    public function countVoters(int $categoryId): int;

    public function updateVotingWindow(int $id, string $start, string $end): void;

    public function deleteWithCascade(int $id): void;
}
