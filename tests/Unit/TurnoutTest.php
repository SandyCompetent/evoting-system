<?php
declare(strict_types=1);

namespace App\Tests\Unit;

use App\Domain\Turnout;
use PHPUnit\Framework\TestCase;

final class TurnoutTest extends TestCase
{
    public function testTurnoutCalculations(): void
    {
        $turnout = new Turnout(100, 45);
        self::assertSame(100, $turnout->eligibleVoters);
        self::assertSame(45, $turnout->votesCast);
        self::assertSame(55, $turnout->pendingVoters());
        self::assertSame(45.0, $turnout->percentage());
    }

    public function testTurnoutWithZeroEligibleVoters(): void
    {
        $turnout = new Turnout(0, 0);
        self::assertSame(0, $turnout->eligibleVoters);
        self::assertSame(0, $turnout->votesCast);
        self::assertSame(0, $turnout->pendingVoters());
        self::assertSame(0.0, $turnout->percentage());
    }

    public function testTurnout100Percent(): void
    {
        $turnout = new Turnout(50, 50);
        self::assertSame(0, $turnout->pendingVoters());
        self::assertSame(100.0, $turnout->percentage());
    }

    public function testPendingVotersNeverNegative(): void
    {
        $turnout = new Turnout(10, 15);
        self::assertSame(0, $turnout->pendingVoters());
        self::assertSame(150.0, $turnout->percentage());
    }

    public function testRoundingToOneDecimal(): void
    {
        $turnout = new Turnout(3, 1);
        self::assertSame(33.3, $turnout->percentage());
    }
}
