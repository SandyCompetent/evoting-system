<?php
declare(strict_types=1);

namespace App\Service;

use App\Domain\Tally;
use App\Exception\AlreadyVotedException;
use App\Exception\InvalidCandidateException;
use App\Exception\NotEligibleException;
use App\Exception\VotingClosedException;
use App\Repository\CandidateRepository;
use App\Repository\CategoryRepository;
use App\Repository\VoteRepository;
use App\Support\Clock;

/**
 * All the rules that make an election valid, in one place and free of any
 * HTTP/PDO detail so they can be unit-tested against in-memory fakes.
 */
final class VoteService
{
    public function __construct(
        private readonly VoteRepository $votes,
        private readonly CandidateRepository $candidates,
        private readonly CategoryRepository $categories,
        private readonly Clock $clock,
    ) {}

    /**
     * Cast a single ballot.
     *
     * @throws NotEligibleException      voter is not assigned to an election
     * @throws VotingClosedException     outside the voting window
     * @throws InvalidCandidateException candidate missing / wrong category
     * @throws AlreadyVotedException     voter already voted (pre-check or race)
     */
    public function castVote(int $voterId, int $candidateId): void
    {
        $election = $this->categories->findByVoter($voterId);
        if ($election === null) {
            throw new NotEligibleException('You are not assigned to an election.');
        }

        if (!$election->isOpen($this->clock->now())) {
            throw new VotingClosedException('Voting is not currently open for your election.');
        }

        if (!$this->candidates->belongsToCategory($candidateId, $election->id)) {
            throw new InvalidCandidateException('Please select a valid candidate.');
        }

        if ($this->votes->hasVoted($voterId, $election->id)) {
            throw new AlreadyVotedException('You have already voted in this election.');
        }

        // The repository re-throws AlreadyVotedException if the UNIQUE key
        // catches a concurrent double-submit that slipped past the pre-check.
        $this->votes->record($voterId, $election->id, $candidateId);
    }

    public function hasVoted(int $voterId, int $categoryId): bool
    {
        return $this->votes->hasVoted($voterId, $categoryId);
    }

    public function tally(int $categoryId): Tally
    {
        return $this->votes->tallyForCategory($categoryId);
    }

    public function turnout(int $categoryId): int
    {
        return $this->votes->distinctVoterCount($categoryId);
    }
}
