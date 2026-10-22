<?php
declare(strict_types=1);

namespace App\Repository;

use App\Domain\Candidate;

interface CandidateRepository
{
    public function belongsToCategory(int $candidateId, int $categoryId): bool;

    /**
     * @return Candidate[]
     */
    public function findByCategory(int $categoryId): array;

    public function findById(int $id, int $categoryId): ?Candidate;

    public function create(string $name, string $photo, string $symbol, int $categoryId): int;

    public function update(int $id, string $name, ?string $photo, ?string $symbol, int $categoryId): void;

    public function delete(int $id, int $categoryId): void;
}
