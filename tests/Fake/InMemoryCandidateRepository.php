<?php
declare(strict_types=1);

namespace App\Tests\Fake;

use App\Domain\Candidate;
use App\Exception\VotingActiveException;
use App\Repository\CandidateRepository;

final class InMemoryCandidateRepository implements CandidateRepository
{
    /** @var array<int,Candidate> keyed by candidate id */
    private array $candidates = [];

    /** Flag to simulate active election voting window */
    public bool $simulateActiveElection = false;
    /** Flag to simulate that votes were already cast for candidate */
    public bool $simulateExistingVotes = false;
    /** Flag to simulate that election voting has commenced */
    public bool $simulateStartedElection = false;

    public function add(int $candidateId, int $categoryId, string $name = 'Candidate', string $photo = 'photo.jpg', string $symbol = 'symbol.png'): void
    {
        $this->candidates[$candidateId] = new Candidate($candidateId, $name, $photo, $symbol, $categoryId);
    }

    public function belongsToCategory(int $candidateId, int $categoryId): bool
    {
        return isset($this->candidates[$candidateId]) && $this->candidates[$candidateId]->categoryId === $categoryId;
    }

    /**
     * @return Candidate[]
     */
    public function findByCategory(int $categoryId): array
    {
        return array_values(array_filter($this->candidates, fn(Candidate $c) => $c->categoryId === $categoryId));
    }

    public function findById(int $id, int $categoryId): ?Candidate
    {
        $c = $this->candidates[$id] ?? null;
        return ($c && $c->categoryId === $categoryId) ? $c : null;
    }

    public function create(string $name, string $photo, string $symbol, int $categoryId): int
    {
        $id = empty($this->candidates) ? 1 : (max(array_keys($this->candidates)) + 1);
        $this->candidates[$id] = new Candidate($id, $name, $photo, $symbol, $categoryId);
        return $id;
    }

    public function update(int $id, string $name, ?string $photo, ?string $symbol, int $categoryId): void
    {
        $existing = $this->findById($id, $categoryId);
        if ($existing) {
            $this->candidates[$id] = new Candidate(
                $id,
                $name,
                $photo ?? $existing->photo,
                $symbol ?? $existing->symbol,
                $categoryId
            );
        }
    }

    public function delete(int $id, int $categoryId): void
    {
        if ($this->simulateActiveElection) {
            throw new VotingActiveException('Cannot delete candidate while voting is actively underway.');
        }
        if ($this->simulateStartedElection) {
            throw new VotingActiveException('Cannot delete candidate once election voting has commenced.');
        }
        if ($this->simulateExistingVotes) {
            throw new VotingActiveException('Cannot delete candidate after votes have already been cast in this election.');
        }
        if ($this->belongsToCategory($id, $categoryId)) {
            unset($this->candidates[$id]);
        }
    }
}
