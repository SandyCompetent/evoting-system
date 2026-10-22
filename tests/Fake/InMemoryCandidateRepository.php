<?php
declare(strict_types=1);

namespace App\Tests\Fake;

use App\Repository\CandidateRepository;

final class InMemoryCandidateRepository implements CandidateRepository
{
    /** @var array<int,int> candidateId => categoryId */
    private array $candidates = [];

    public function add(int $candidateId, int $categoryId): void
    {
        $this->candidates[$candidateId] = $categoryId;
    }

    public function belongsToCategory(int $candidateId, int $categoryId): bool
    {
        return ($this->candidates[$candidateId] ?? null) === $categoryId;
    }
}
