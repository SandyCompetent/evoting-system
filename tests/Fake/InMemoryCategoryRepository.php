<?php
declare(strict_types=1);

namespace App\Tests\Fake;

use App\Domain\Election;
use App\Repository\CategoryRepository;

final class InMemoryCategoryRepository implements CategoryRepository
{
    /** @var array<int,Election> keyed by category id */
    private array $elections = [];
    /** @var array<int,int> voterId => categoryId */
    private array $voterCategory = [];

    public function addElection(Election $e): void
    {
        $this->elections[$e->id] = $e;
    }

    public function assignVoter(int $voterId, int $categoryId): void
    {
        $this->voterCategory[$voterId] = $categoryId;
    }

    public function findByVoter(int $voterId): ?Election
    {
        $cat = $this->voterCategory[$voterId] ?? null;
        return $cat !== null ? ($this->elections[$cat] ?? null) : null;
    }

    public function find(int $categoryId): ?Election
    {
        return $this->elections[$categoryId] ?? null;
    }

    /**
     * @return Election[]
     */
    public function all(): array
    {
        return array_values($this->elections);
    }

    public function countVoters(int $categoryId): int
    {
        $count = 0;
        foreach ($this->voterCategory as $catId) {
            if ($catId === $categoryId) {
                $count++;
            }
        }
        return $count;
    }

    public function updateVotingWindow(int $id, string $start, string $end): void
    {
        if (isset($this->elections[$id])) {
            $old = $this->elections[$id];
            $this->elections[$id] = new Election(
                $old->id,
                $old->name,
                new \DateTimeImmutable($start),
                new \DateTimeImmutable($end),
            );
        }
    }

    public function deleteWithCascade(int $id): void
    {
        unset($this->elections[$id]);
        foreach ($this->voterCategory as $voterId => $catId) {
            if ($catId === $id) {
                unset($this->voterCategory[$voterId]);
            }
        }
    }
}
