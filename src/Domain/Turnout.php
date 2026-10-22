<?php
declare(strict_types=1);

namespace App\Domain;

final class Turnout
{
    public function __construct(
        public readonly int $eligibleVoters,
        public readonly int $votesCast,
    ) {}

    public function pendingVoters(): int
    {
        return max(0, $this->eligibleVoters - $this->votesCast);
    }

    public function percentage(): float
    {
        return $this->eligibleVoters > 0
            ? round(($this->votesCast / $this->eligibleVoters) * 100, 1)
            : 0.0;
    }
}
