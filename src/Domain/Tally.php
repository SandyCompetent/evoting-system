<?php
declare(strict_types=1);

namespace App\Domain;

/**
 * An ordered election tally (highest first). Encapsulates winner/tie logic so
 * no page has to re-implement it.
 *
 * @param Standing[] $standings
 */
final class Tally
{
    /** @var Standing[] */
    public readonly array $standings;

    public function __construct(array $standings)
    {
        // Defensive: always keep sorted by votes desc, then name asc.
        usort($standings, static function (Standing $a, Standing $b): int {
            return $b->votes <=> $a->votes ?: strcmp($a->candidateName, $b->candidateName);
        });
        $this->standings = $standings;
    }

    public function isEmpty(): bool
    {
        return $this->standings === [];
    }

    public function totalVotes(): int
    {
        return array_sum(array_map(static fn (Standing $s) => $s->votes, $this->standings));
    }

    /** @return Standing[] the leader(s); more than one means a tie. */
    public function leaders(): array
    {
        if ($this->isEmpty()) {
            return [];
        }
        $top = $this->standings[0]->votes;
        return array_values(array_filter($this->standings, static fn (Standing $s) => $s->votes === $top));
    }

    public function isTie(): bool
    {
        return count($this->leaders()) > 1;
    }

    /**
     * Human-readable result label for dashboards.
     */
    public function winnerLabel(): string
    {
        if ($this->isEmpty()) {
            return 'No candidates';
        }
        if ($this->totalVotes() === 0) {
            return 'No votes cast';
        }
        $leaders = $this->leaders();
        if (count($leaders) > 1) {
            return 'Tie between ' . implode(', ', array_map(static fn (Standing $s) => $s->candidateName, $leaders));
        }
        return $leaders[0]->candidateName;
    }
}
