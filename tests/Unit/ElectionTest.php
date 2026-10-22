<?php
declare(strict_types=1);

namespace App\Tests\Unit;

use App\Domain\Election;
use PHPUnit\Framework\TestCase;

final class ElectionTest extends TestCase
{
    private function at(string $iso): \DateTimeImmutable
    {
        return new \DateTimeImmutable($iso);
    }

    public function testOpenWithinWindow(): void
    {
        $e = new Election(1, 'Mayor', $this->at('2026-01-01 09:00'), $this->at('2026-01-01 17:00'));
        self::assertTrue($e->isOpen($this->at('2026-01-01 12:00')));
    }

    public function testClosedBeforeStart(): void
    {
        $e = new Election(1, 'Mayor', $this->at('2026-01-01 09:00'), $this->at('2026-01-01 17:00'));
        self::assertFalse($e->isOpen($this->at('2026-01-01 08:59')));
    }

    public function testClosedAfterEnd(): void
    {
        $e = new Election(1, 'Mayor', $this->at('2026-01-01 09:00'), $this->at('2026-01-01 17:00'));
        self::assertFalse($e->isOpen($this->at('2026-01-01 17:01')));
    }

    public function testBoundariesAreInclusiveStartInclusiveEnd(): void
    {
        $e = new Election(1, 'Mayor', $this->at('2026-01-01 09:00'), $this->at('2026-01-01 17:00'));
        self::assertTrue($e->isOpen($this->at('2026-01-01 09:00')), 'start is inclusive');
        self::assertTrue($e->isOpen($this->at('2026-01-01 17:00')), 'end is inclusive');
    }

    public function testNullWindowIsNeverOpen(): void
    {
        $e = new Election(1, 'Mayor', null, null);
        self::assertFalse($e->isOpen($this->at('2026-01-01 12:00')));
    }
}
