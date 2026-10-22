<?php
declare(strict_types=1);

namespace App\Domain;

final class Candidate
{
    public function __construct(
        public readonly int $id,
        public readonly string $fullName,
        public readonly string $photo,
        public readonly string $symbol,
        public readonly int $categoryId,
    ) {}
}
