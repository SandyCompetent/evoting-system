<?php
declare(strict_types=1);

namespace App\Repository\Pdo;

use App\Domain\Standing;
use App\Domain\Tally;
use App\Exception\AlreadyVotedException;
use App\Repository\VoteRepository;
use PDO;
use PDOException;

final class PdoVoteRepository implements VoteRepository
{
    public function __construct(private readonly PDO $pdo) {}

    public function hasVoted(int $voterId, int $categoryId): bool
    {
        $stmt = $this->pdo->prepare('SELECT 1 FROM vote WHERE voter_id = ? AND category_id = ?');
        $stmt->execute([$voterId, $categoryId]);
        return $stmt->fetchColumn() !== false;
    }

    public function record(int $voterId, int $categoryId, int $candidateId): void
    {
        try {
            $this->pdo->beginTransaction();

            $stmt = $this->pdo->prepare(
                'INSERT INTO vote (voter_id, category_id, candidate_id) VALUES (?, ?, ?)'
            );
            $stmt->execute([$voterId, $categoryId, $candidateId]);

            // The vote table is authoritative; this denormalised flag is used
            // by the organiser voter list, so it must change in the same
            // transaction as the ballot itself.
            $markVoted = $this->pdo->prepare("UPDATE system_users SET voted = 'Yes' WHERE user_id = ?");
            $markVoted->execute([$voterId]);

            $this->pdo->commit();
        } catch (PDOException $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            // 23000 = integrity constraint violation = UNIQUE(voter_id, category_id).
            if ($e->getCode() === '23000') {
                throw new AlreadyVotedException('You have already voted in this election.', 0, $e);
            }
            throw $e;
        }
    }

    public function tallyForCategory(int $categoryId): Tally
    {
        $stmt = $this->pdo->prepare(
            'SELECT c.full_name AS name, COUNT(v.id) AS votes
               FROM candidate c
               LEFT JOIN vote v ON v.candidate_id = c.id AND v.category_id = c.category_id
              WHERE c.category_id = ?
              GROUP BY c.id, c.full_name'
        );
        $stmt->execute([$categoryId]);
        $standings = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $standings[] = new Standing((string) $row['name'], (int) $row['votes']);
        }
        return new Tally($standings);
    }

    public function distinctVoterCount(int $categoryId): int
    {
        $stmt = $this->pdo->prepare('SELECT COUNT(DISTINCT voter_id) FROM vote WHERE category_id = ?');
        $stmt->execute([$categoryId]);
        return (int) $stmt->fetchColumn();
    }
}
