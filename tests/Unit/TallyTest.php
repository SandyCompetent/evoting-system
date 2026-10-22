<?php
declare(strict_types=1);

namespace App\Tests\Unit;

use App\Domain\Standing;
use App\Domain\Tally;
use PHPUnit\Framework\TestCase;

final class TallyTest extends TestCase
{
    public function testOrdersByVotesDescThenName(): void
    {
        $t = new Tally([
            new Standing('Bob', 3),
            new Standing('Alice', 5),
            new Standing('Carol', 3),
        ]);
        self::assertSame('Alice', $t->standings[0]->candidateName);
        // Tie at 3 -> alphabetical: Bob before Carol.
        self::assertSame('Bob', $t->standings[1]->candidateName);
        self::assertSame('Carol', $t->standings[2]->candidateName);
    }

    public function testWinnerLabelSingleLeader(): void
    {
        $t = new Tally([new Standing('Alice', 5), new Standing('Bob', 2)]);
        self::assertFalse($t->isTie());
        self::assertSame('Alice', $t->winnerLabel());
    }

    public function testWinnerLabelTie(): void
    {
        $t = new Tally([new Standing('Alice', 4), new Standing('Bob', 4)]);
        self::assertTrue($t->isTie());
        self::assertSame('Tie between Alice, Bob', $t->winnerLabel());
    }

    public function testNoVotesCast(): void
    {
        $t = new Tally([new Standing('Alice', 0), new Standing('Bob', 0)]);
        self::assertSame('No votes cast', $t->winnerLabel());
        self::assertSame(0, $t->totalVotes());
    }

    public function testNoCandidates(): void
    {
        $t = new Tally([]);
        self::assertTrue($t->isEmpty());
        self::assertSame('No candidates', $t->winnerLabel());
    }
}
