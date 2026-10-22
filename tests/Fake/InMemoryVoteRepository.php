<?php
declare(strict_types=1);

namespace App\Tests\Fake;

use App\Domain\Standing;
use App\Domain\Tally;
use App\Exception\AlreadyVotedException;
use App\Repository\VoteRepository;

final class InMemoryVoteRepository implements VoteRepository
{
    /** @var array<int,array{voter:int,category:int,candidate:int}> */
    private array $ballots = [];

    /** Force record() to simulate the DB UNIQUE race (23000) once. */
    public bool $simulateRace = false;

    public function hasVoted(int $voterId, int $categoryId): bool
    {
        foreach ($this->ballots as $b) {
            if ($b['voter'] === $voterId && $b['category'] === $categoryId) {
                return true;
            }
        }
        return false;
    }

    public function record(int $voterId, int $categoryId, int $candidateId): void
    {
        if ($this->simulateRace || $this->hasVoted($voterId, $categoryId)) {
            throw new AlreadyVotedException('You have already voted in this election.');
        }
        $this->ballots[] = ['voter' => $voterId, 'category' => $categoryId, 'candidate' => $candidateId];
    }

    public function tallyForCategory(int $categoryId): Tally
    {
        $counts = [];
        foreach ($this->ballots as $b) {
            if ($b['category'] === $categoryId) {
                $counts[$b['candidate']] = ($counts[$b['candidate']] ?? 0) + 1;
            }
        }
        $standings = [];
        foreach ($counts as $candidateId => $n) {
            $standings[] = new Standing('Candidate ' . $candidateId, $n);
        }
        return new Tally($standings);
    }

    public function distinctVoterCount(int $categoryId): int
    {
        $voters = [];
        foreach ($this->ballots as $b) {
            if ($b['category'] === $categoryId) {
                $voters[$b['voter']] = true;
            }
        }
        return count($voters);
    }
}
