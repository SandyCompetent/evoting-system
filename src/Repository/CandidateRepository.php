<?php
declare(strict_types=1);

namespace App\Repository;

interface CandidateRepository
{
    public function belongsToCategory(int $candidateId, int $categoryId): bool;
}
