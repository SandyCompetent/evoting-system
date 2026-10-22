<?php
declare(strict_types=1);

namespace App\Support;

/**
 * A clock pinned to a fixed instant - used by tests (and anywhere a frozen
 * "now" is useful).
 */
final class FixedClock implements Clock
{
    public function __construct(private \DateTimeImmutable $now) {}

    public function now(): \DateTimeImmutable
    {
        return $this->now;
    }
}
