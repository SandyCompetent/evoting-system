<?php
declare(strict_types=1);

namespace App\Support;

final class SystemClock implements Clock
{
    public function __construct(private readonly \DateTimeZone $tz) {}

    public function now(): \DateTimeImmutable
    {
        return new \DateTimeImmutable('now', $this->tz);
    }
}
