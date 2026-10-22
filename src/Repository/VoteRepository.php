<?php
declare(strict_types=1);

namespace App\Repository;

use App\Domain\Tally;

interface VoteRepository
{
    public function hasVoted(int $voterId, int $categoryId): bool;

    /**
     * Persist one ballot. Implementations MUST throw
     * App\Exception\AlreadyVotedException if the one-vote-per-election
     * constraint is violated (race / replay).
     */
    public function record(int $voterId, int $categoryId, int $candidateId): void;

    public function tallyForCategory(int $categoryId): Tally;

    public function distinctVoterCount(int $categoryId): int;
}
