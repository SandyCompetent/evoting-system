<?php
declare(strict_types=1);

namespace App\Domain;

final class Standing
{
    public function __construct(
        public readonly string $candidateName,
        public readonly int $votes,
    ) {}
}
