<?php
declare(strict_types=1);

namespace App\Domain;

final class Election
{
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly ?\DateTimeImmutable $startsAt,
        public readonly ?\DateTimeImmutable $endsAt,
    ) {}

    public function hasStarted(\DateTimeInterface $now): bool
    {
        return $this->startsAt !== null && $now >= $this->startsAt;
    }

    public function hasEnded(\DateTimeInterface $now): bool
    {
        return $this->endsAt !== null && $now > $this->endsAt;
    }

    public function isOpen(\DateTimeInterface $now): bool
    {
        return $this->hasStarted($now) && !$this->hasEnded($now);
    }
}
