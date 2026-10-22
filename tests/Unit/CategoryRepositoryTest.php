<?php
declare(strict_types=1);

namespace App\Tests\Unit;

use App\Domain\Election;
use App\Tests\Fake\InMemoryCategoryRepository;
use PHPUnit\Framework\TestCase;

final class CategoryRepositoryTest extends TestCase
{
    private InMemoryCategoryRepository $repo;

    protected function setUp(): void
    {
        $this->repo = new InMemoryCategoryRepository();
    }

    public function testAllReturnsAllElections(): void
    {
        self::assertCount(0, $this->repo->all());

        $this->repo->addElection(new Election(1, 'Category A', null, null));
        $this->repo->addElection(new Election(2, 'Category B', null, null));

        $all = $this->repo->all();
        self::assertCount(2, $all);
        self::assertSame('Category A', $all[0]->name);
        self::assertSame('Category B', $all[1]->name);
    }

    public function testFindById(): void
    {
        $starts = new \DateTimeImmutable('2026-01-01 09:00:00');
        $ends = new \DateTimeImmutable('2026-01-01 17:00:00');
        $this->repo->addElection(new Election(10, 'Student Council', $starts, $ends));

        $found = $this->repo->find(10);
        self::assertNotNull($found);
        self::assertSame(10, $found->id);
        self::assertSame('Student Council', $found->name);
        self::assertSame($starts, $found->startsAt);
        self::assertSame($ends, $found->endsAt);

        self::assertNull($this->repo->find(999));
    }

    public function testFindByVoter(): void
    {
        $this->repo->addElection(new Election(5, 'Tech Club', null, null));
        $this->repo->assignVoter(101, 5);

        $election = $this->repo->findByVoter(101);
        self::assertNotNull($election);
        self::assertSame(5, $election->id);
        self::assertSame('Tech Club', $election->name);

        self::assertNull($this->repo->findByVoter(999));
    }

    public function testCountVoters(): void
    {
        $this->repo->assignVoter(1, 10);
        $this->repo->assignVoter(2, 10);
        $this->repo->assignVoter(3, 10);
        $this->repo->assignVoter(4, 20);

        self::assertSame(3, $this->repo->countVoters(10));
        self::assertSame(1, $this->repo->countVoters(20));
        self::assertSame(0, $this->repo->countVoters(30));
    }

    public function testUpdateVotingWindow(): void
    {
        $this->repo->addElection(new Election(1, 'Mayor', null, null));

        $this->repo->updateVotingWindow(1, '2026-11-01 08:00:00', '2026-11-01 20:00:00');

        $updated = $this->repo->find(1);
        self::assertNotNull($updated);
        self::assertNotNull($updated->startsAt);
        self::assertNotNull($updated->endsAt);
        self::assertSame('2026-11-01 08:00:00', $updated->startsAt->format('Y-m-d H:i:s'));
        self::assertSame('2026-11-01 20:00:00', $updated->endsAt->format('Y-m-d H:i:s'));
    }

    public function testDeleteWithCascadeRemovesElectionAndAssignedVoters(): void
    {
        $this->repo->addElection(new Election(1, 'Election 1', null, null));
        $this->repo->addElection(new Election(2, 'Election 2', null, null));
        $this->repo->assignVoter(10, 1);
        $this->repo->assignVoter(11, 1);
        $this->repo->assignVoter(12, 2);

        self::assertSame(2, $this->repo->countVoters(1));
        self::assertNotNull($this->repo->find(1));

        $this->repo->deleteWithCascade(1);

        self::assertNull($this->repo->find(1));
        self::assertSame(0, $this->repo->countVoters(1));
        self::assertNull($this->repo->findByVoter(10));
        self::assertNull($this->repo->findByVoter(11));

        // Election 2 remains intact
        self::assertNotNull($this->repo->find(2));
        self::assertSame(1, $this->repo->countVoters(2));
        self::assertNotNull($this->repo->findByVoter(12));
    }
}
