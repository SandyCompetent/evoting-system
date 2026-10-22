<?php
declare(strict_types=1);

namespace App\Domain;

final class User
{
    public function __construct(
        public readonly int $id,
        public readonly string $fullName,
        public readonly string $email,
        public readonly string $role,
        public readonly ?int $categoryId = null,
        public readonly ?string $dob = null,
        public readonly string $voted = 'No',
        public readonly ?string $createdTimestamp = null,
    ) {}
}
