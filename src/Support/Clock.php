<?php
declare(strict_types=1);

namespace App\Support;

/**
 * Supplies "now". Injecting this (instead of calling time()/new DateTime())
 * lets tests drive the voting-window logic deterministically.
 */
interface Clock
{
    public function now(): \DateTimeImmutable;
}
