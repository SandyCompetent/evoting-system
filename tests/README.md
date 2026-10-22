# Tests

Unit tests for the election-critical domain logic (`src/`). They use in-memory
fake repositories (no database required) and a `FixedClock`, so they are fast
and deterministic.

## Run

```bash
composer install        # once, to fetch PHPUnit into vendor/
composer test           # or: vendor/bin/phpunit
```

## Coverage

- `ElectionTest` - voting-window boundaries (inclusive start/end, null window).
- `TallyTest` - ordering, single winner, tie, no-votes, no-candidates.
- `VoteServiceTest` - the rules that make an election valid:
  happy path, voter not eligible, before/after window, candidate from another
  category, unknown candidate, double-vote, and the concurrent-race path
  (UNIQUE constraint caught at insert).

These exercise the same objects the app uses at runtime (`voter/index.php`
casts votes through `VoteService`), so a green suite means the voting rules
hold independently of the UI.
