<?php
declare(strict_types=1);

namespace App\Repository;

use App\Domain\User;

interface UserRepository
{
    public function findById(int $id): ?User;

    /**
     * @return array{user_id:int,full_name:string,email:string,password:string,role:string,election_category_id:?int,date_of_birth:?string,voted:string}|null
     */
    public function findByEmail(string $email): ?array;

    /**
     * @return User[]
     */
    public function findOrganisers(): array;

    /**
     * @return User[]
     */
    public function findVotersByCategory(int $categoryId): array;

    public function updatePassword(int $userId, string $passwordHash): void;
}
