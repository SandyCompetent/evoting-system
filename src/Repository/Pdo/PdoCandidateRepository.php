<?php
declare(strict_types=1);

namespace App\Repository\Pdo;

use App\Domain\Candidate;
use App\Exception\VotingActiveException;
use App\Repository\CandidateRepository;
use PDO;
use PDOException;

final class PdoCandidateRepository implements CandidateRepository
{
    public function __construct(private readonly PDO $pdo) {}

    public function belongsToCategory(int $candidateId, int $categoryId): bool
    {
        $stmt = $this->pdo->prepare(
            'SELECT 1 FROM candidate WHERE id = ? AND category_id = ?'
        );
        $stmt->execute([$candidateId, $categoryId]);
        return $stmt->fetchColumn() !== false;
    }

    /**
     * @return Candidate[]
     */
    public function findByCategory(int $categoryId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT id, full_name, photo, symbol, category_id FROM candidate WHERE category_id = ? ORDER BY full_name ASC'
        );
        $stmt->execute([$categoryId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_map(fn(array $r) => $this->hydrate($r), $rows);
    }

    public function findById(int $id, int $categoryId): ?Candidate
    {
        $stmt = $this->pdo->prepare(
            'SELECT id, full_name, photo, symbol, category_id FROM candidate WHERE id = ? AND category_id = ?'
        );
        $stmt->execute([$id, $categoryId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ? $this->hydrate($row) : null;
    }

    public function create(string $name, string $photo, string $symbol, int $categoryId): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO candidate (full_name, photo, symbol, category_id) VALUES (?, ?, ?, ?)'
        );
        $stmt->execute([$name, $photo, $symbol, $categoryId]);
        return (int) $this->pdo->lastInsertId();
    }

    public function update(int $id, string $name, ?string $photo, ?string $symbol, int $categoryId): void
    {
        $params = [$name];
        $sql = 'UPDATE candidate SET full_name = ?';

        if ($photo !== null) {
            $sql .= ', photo = ?';
            $params[] = $photo;
        }
        if ($symbol !== null) {
            $sql .= ', symbol = ?';
            $params[] = $symbol;
        }

        $sql .= ' WHERE id = ? AND category_id = ?';
        $params[] = $id;
        $params[] = $categoryId;

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
    }

    public function delete(int $id, int $categoryId): void
    {
        // 1. Guard against deletion if election is underway, started, or votes have been cast
        $catStmt = $this->pdo->prepare(
            'SELECT vote_start_time, vote_end_time FROM election_category WHERE id = ?'
        );
        $catStmt->execute([$categoryId]);
        $cat = $catStmt->fetch(PDO::FETCH_ASSOC);

        if ($cat) {
            $now = time();
            $start = !empty($cat['vote_start_time']) ? strtotime((string) $cat['vote_start_time']) : 0;
            $end   = !empty($cat['vote_end_time'])   ? strtotime((string) $cat['vote_end_time'])   : 0;

            if ($start > 0 && $end > 0 && $now >= $start && $now <= $end) {
                throw new VotingActiveException('Cannot delete candidate while voting is actively underway.');
            }
            if ($start > 0 && $now >= $start) {
                throw new VotingActiveException('Cannot delete candidate once election voting has commenced.');
            }
        }

        $voteStmt = $this->pdo->prepare('SELECT COUNT(*) FROM vote WHERE candidate_id = ? AND category_id = ?');
        $voteStmt->execute([$id, $categoryId]);
        if ((int) $voteStmt->fetchColumn() > 0) {
            throw new VotingActiveException('Cannot delete candidate after votes have already been cast in this election.');
        }

        // 2. Perform atomic deletion in transaction
        try {
            $this->pdo->beginTransaction();
            $stmtVote = $this->pdo->prepare('DELETE FROM vote WHERE candidate_id = ? AND category_id = ?');
            $stmtVote->execute([$id, $categoryId]);

            $stmtCand = $this->pdo->prepare('DELETE FROM candidate WHERE id = ? AND category_id = ?');
            $stmtCand->execute([$id, $categoryId]);

            $this->pdo->commit();
        } catch (PDOException $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $e;
        }
    }

    private function hydrate(array $row): Candidate
    {
        return new Candidate(
            (int) $row['id'],
            (string) $row['full_name'],
            (string) $row['photo'],
            (string) $row['symbol'],
            (int) $row['category_id'],
        );
    }
}
