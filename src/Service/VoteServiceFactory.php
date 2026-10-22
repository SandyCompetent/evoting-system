<?php
declare(strict_types=1);

namespace App\Service;

use App\Repository\Pdo\PdoCandidateRepository;
use App\Repository\Pdo\PdoCategoryRepository;
use App\Repository\Pdo\PdoVoteRepository;
use App\Support\SystemClock;
use PDO;

final class VoteServiceFactory
{
    public static function create(PDO $pdo, string $timezone = 'Asia/Kolkata'): VoteService
    {
        return new VoteService(
            new PdoVoteRepository($pdo),
            new PdoCandidateRepository($pdo),
            new PdoCategoryRepository($pdo),
            new SystemClock(new \DateTimeZone($timezone)),
        );
    }
}
