<?php
declare(strict_types=1);

namespace App\Repository;

use App\Domain\Election;

interface CategoryRepository
{
    public function findByVoter(int $voterId): ?Election;

    public function find(int $categoryId): ?Election;
}
