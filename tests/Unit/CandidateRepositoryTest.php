<?php
declare(strict_types=1);

namespace App\Tests\Unit;

use App\Exception\VoteException;
use App\Tests\Fake\InMemoryCandidateRepository;
use PHPUnit\Framework\TestCase;

final class CandidateRepositoryTest extends TestCase
{
    private InMemoryCandidateRepository $repo;

    protected function setUp(): void
    {
        $this->repo = new InMemoryCandidateRepository();
    }

    public function testCreateAndFindCandidate(): void
    {
        $id = $this->repo->create('Alice Smith', 'alice.jpg', 'alice_sym.png', 10);
        self::assertTrue($id > 0);

        $cand = $this->repo->findById($id, 10);
        self::assertNotNull($cand);
        self::assertSame('Alice Smith', $cand->fullName);
        self::assertSame('alice.jpg', $cand->photo);
        self::assertSame('alice_sym.png', $cand->symbol);
        self::assertSame(10, $cand->categoryId);
    }

    public function testFindByIdWithWrongCategoryReturnsNull(): void
    {
        $id = $this->repo->create('Bob Jones', 'bob.jpg', 'bob_sym.png', 10);
        self::assertNull($this->repo->findById($id, 99));
    }

    public function testFindByCategoryFiltersCorrectly(): void
    {
        $this->repo->create('Alice', 'a.jpg', 'a.png', 1);
        $this->repo->create('Bob', 'b.jpg', 'b.png', 1);
        $this->repo->create('Charlie', 'c.jpg', 'c.png', 2);

        $cat1 = $this->repo->findByCategory(1);
        $cat2 = $this->repo->findByCategory(2);
        $cat3 = $this->repo->findByCategory(3);

        self::assertCount(2, $cat1);
        self::assertCount(1, $cat2);
        self::assertCount(0, $cat3);
    }

    public function testUpdateCandidate(): void
    {
        $id = $this->repo->create('Dave Old', 'dave_old.jpg', 'dave_sym.png', 5);
        $this->repo->update($id, 'Dave New', 'dave_new.jpg', null, 5);

        $cand = $this->repo->findById($id, 5);
        self::assertNotNull($cand);
        self::assertSame('Dave New', $cand->fullName);
        self::assertSame('dave_new.jpg', $cand->photo);
        self::assertSame('dave_sym.png', $cand->symbol);
    }

    public function testDeleteAllowedWhenElectionNotActive(): void
    {
        $id = $this->repo->create('Eve', 'eve.jpg', 'eve.png', 5);
        self::assertTrue($this->repo->belongsToCategory($id, 5));

        $this->repo->delete($id, 5);
        self::assertFalse($this->repo->belongsToCategory($id, 5));
        self::assertNull($this->repo->findById($id, 5));
    }

    public function testDeleteRejectsWhenElectionIsActive(): void
    {
        $id = $this->repo->create('Frank', 'frank.jpg', 'frank.png', 5);
        $this->repo->simulateActiveElection = true;

        $this->expectException(VoteException::class);
        $this->repo->delete($id, 5);
    }

    public function testDeleteRejectsWhenVotesAlreadyCast(): void
    {
        $id = $this->repo->create('Grace', 'grace.jpg', 'grace.png', 5);
        $this->repo->simulateExistingVotes = true;

        $this->expectException(VoteException::class);
        $this->repo->delete($id, 5);
    }

    public function testDeleteRejectsWhenElectionCommenced(): void
    {
        $id = $this->repo->create('Harry', 'harry.jpg', 'harry.png', 5);
        $this->repo->simulateStartedElection = true;

        $this->expectException(VoteException::class);
        $this->repo->delete($id, 5);
    }
}
