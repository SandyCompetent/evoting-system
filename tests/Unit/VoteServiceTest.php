<?php
declare(strict_types=1);

namespace App\Tests\Unit;

use App\Domain\Election;
use App\Exception\AlreadyVotedException;
use App\Exception\InvalidCandidateException;
use App\Exception\NotEligibleException;
use App\Exception\VotingClosedException;
use App\Service\VoteService;
use App\Support\FixedClock;
use App\Tests\Fake\InMemoryCandidateRepository;
use App\Tests\Fake\InMemoryCategoryRepository;
use App\Tests\Fake\InMemoryVoteRepository;
use PHPUnit\Framework\TestCase;

final class VoteServiceTest extends TestCase
{
    private InMemoryVoteRepository $votes;
    private InMemoryCandidateRepository $candidates;
    private InMemoryCategoryRepository $categories;

    private function service(string $now = '2026-01-01 12:00'): VoteService
    {
        $this->votes = new InMemoryVoteRepository();
        $this->candidates = new InMemoryCandidateRepository();
        $this->categories = new InMemoryCategoryRepository();

        // Election #10 open 09:00-17:00; voter 1 assigned; candidate 100 in it.
        $this->categories->addElection(new Election(
            10, 'Mayor', new \DateTimeImmutable('2026-01-01 09:00'), new \DateTimeImmutable('2026-01-01 17:00')
        ));
        $this->categories->assignVoter(1, 10);
        $this->candidates->add(100, 10);
        $this->candidates->add(200, 99); // candidate in a DIFFERENT category

        return new VoteService(
            $this->votes, $this->candidates, $this->categories,
            new FixedClock(new \DateTimeImmutable($now))
        );
    }

    public function testHappyPathRecordsOneBallot(): void
    {
        $svc = $this->service();
        $svc->castVote(1, 100);
        self::assertTrue($svc->hasVoted(1, 10));
        self::assertSame(1, $svc->turnout(10));
    }

    public function testTurnoutReportReturnsValueObject(): void
    {
        $svc = $this->service();
        $svc->castVote(1, 100);
        $report = $svc->turnoutReport(10);
        self::assertSame(1, $report->eligibleVoters);
        self::assertSame(1, $report->votesCast);
        self::assertSame(0, $report->pendingVoters());
        self::assertSame(100.0, $report->percentage());
    }

    public function testRejectsVoterWithNoElection(): void
    {
        $svc = $this->service();
        $this->expectException(NotEligibleException::class);
        $svc->castVote(999, 100); // voter 999 unassigned
    }

    public function testRejectsBeforeWindow(): void
    {
        $svc = $this->service('2026-01-01 08:00');
        $this->expectException(VotingClosedException::class);
        $svc->castVote(1, 100);
    }

    public function testRejectsAfterWindow(): void
    {
        $svc = $this->service('2026-01-01 18:00');
        $this->expectException(VotingClosedException::class);
        $svc->castVote(1, 100);
    }

    public function testRejectsCandidateFromAnotherCategory(): void
    {
        $svc = $this->service();
        $this->expectException(InvalidCandidateException::class);
        $svc->castVote(1, 200); // candidate 200 belongs to category 99
    }

    public function testRejectsUnknownCandidate(): void
    {
        $svc = $this->service();
        $this->expectException(InvalidCandidateException::class);
        $svc->castVote(1, 12345);
    }

    public function testCannotVoteTwice(): void
    {
        $svc = $this->service();
        $svc->castVote(1, 100);
        $this->expectException(AlreadyVotedException::class);
        $svc->castVote(1, 100);
    }

    public function testConcurrentRaceIsCaughtByRepository(): void
    {
        $svc = $this->service();
        // Pre-check passes (no ballot yet) but the "DB" rejects on insert.
        $this->votes->simulateRace = true;
        $this->expectException(AlreadyVotedException::class);
        $svc->castVote(1, 100);
    }
}
