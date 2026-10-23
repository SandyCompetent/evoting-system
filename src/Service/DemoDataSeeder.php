<?php
declare(strict_types=1);

namespace App\Service;

use PDO;
use PDOException;
use RuntimeException;

final class DemoDataSeeder
{
    private PDO $pdo;
    private string $defaultPasswordHash;

    private const FIRST_NAMES = [
        'Emma', 'Liam', 'Olivia', 'Noah', 'Sophia', 'Jackson', 'Ava', 'Lucas',
        'Isabella', 'Oliver', 'Mia', 'Ethan', 'Harper', 'Mason', 'Evelyn', 'Logan',
        'Abigail', 'James', 'Emily', 'Alexander', 'Ella', 'Henry', 'Avery', 'Sebastian',
        'Scarlett', 'Jack', 'Chloe', 'Daniel', 'Victoria', 'Matthew', 'Grace', 'Aiden'
    ];

    private const LAST_NAMES = [
        'Smith', 'Johnson', 'Williams', 'Brown', 'Jones', 'Garcia', 'Miller', 'Davis',
        'Rodriguez', 'Martinez', 'Hernandez', 'Lopez', 'Gonzalez', 'Wilson', 'Anderson',
        'Thomas', 'Taylor', 'Moore', 'Martin', 'Lee', 'Perez', 'Thompson', 'White',
        'Harris', 'Sanchez', 'Clark', 'Ramirez', 'Lewis', 'Robinson', 'Walker', 'Young', 'Allen'
    ];

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
        $this->defaultPasswordHash = password_hash('password123', PASSWORD_BCRYPT);
    }

    /**
     * Seeds a full showcase scenario:
     * 1. Active Election (Student Council) with live candidates, 25 voters, 16 votes cast.
     * 2. Upcoming Election (Faculty Board) with candidates, 15 voters, 0 votes cast.
     * 3. Concluded Election (Alumni Advisory Board) with candidates, 20 voters, 18 votes cast and a clear winner.
     * 4. Sample pending election host registration requests.
     */
    public function seedFullShowcase(bool $purgeExisting = false): array
    {
        if ($purgeExisting) {
            $this->purgeDemoData();
        }

        $now = time();
        $summary = [
            'categories' => [],
            'organisers' => [],
            'candidates' => [],
            'voters' => [],
            'votes_cast' => 0,
            'requests' => 0,
        ];

        $this->pdo->beginTransaction();
        try {
            // Ensure an admin user exists if none present
            $hasAdmin = (int)($this->pdo->query("SELECT COUNT(*) FROM system_users WHERE role = 'admin'")->fetchColumn() ?: 0);
            if ($hasAdmin === 0) {
                $stmtAdmin = $this->pdo->prepare(
                    "INSERT INTO system_users (full_name, email, password, role, election_category_id)
                     VALUES ('Administrator', 'admin@evoting.com', ?, 'admin', 0)"
                );
                $stmtAdmin->execute([password_hash('12345678', PASSWORD_BCRYPT)]);
            }
            // ---------------------------------------------------------
            // 1. ACTIVE ELECTION (Voting is currently LIVE)
            // ---------------------------------------------------------
            $startActive = date('Y-m-d H:i:s', $now - 2 * 86400); // 2 days ago
            $endActive   = date('Y-m-d H:i:s', $now + 5 * 86400); // 5 days from now
            $expActive   = date('Y-m-d H:i:s', $now + 6 * 86400);

            $stmt = $this->pdo->prepare(
                "INSERT INTO election_category (name, max_voter, adult_voter, vote_start_time, vote_end_time, expire_timestamp)
                 VALUES (?, ?, ?, ?, ?, ?)"
            );
            $stmt->execute(['Student Council General Election', 50, 'Yes', $startActive, $endActive, $expActive]);
            $activeCatId = (int)$this->pdo->lastInsertId();
            $summary['categories'][] = ['id' => $activeCatId, 'name' => 'Student Council General Election', 'status' => 'Live Now'];

            // Organiser for Active Election
            $stmtUser = $this->pdo->prepare(
                "INSERT INTO system_users (full_name, email, password, role, election_category_id, date_of_birth, voted)
                 VALUES (?, ?, ?, 'organiser', ?, NULL, 'No')"
            );
            $stmtUser->execute(['Sarah Jenkins', 'council.organiser@evoting.com', $this->defaultPasswordHash, $activeCatId]);
            $summary['organisers'][] = ['name' => 'Sarah Jenkins', 'email' => 'council.organiser@evoting.com', 'category' => 'Student Council'];

            // Candidates for Active Election
            $candidatesActive = [
                ['name' => 'Alex Rivera',    'photo' => 'alex_rivera.jpg',  'symbol' => 'star.png'],
                ['name' => 'Jane Doe',       'photo' => 'jane_doe.jpg',     'symbol' => 'eagle.png'],
                ['name' => 'Michael Chang',  'photo' => 'michael_chang.jpg','symbol' => 'sun.png'],
                ['name' => 'Chloe Bennett',  'photo' => 'chloe_bennett.jpg','symbol' => 'tree.png'],
            ];

            $activeCandIds = [];
            $stmtCand = $this->pdo->prepare(
                "INSERT INTO candidate (full_name, photo, symbol, category_id) VALUES (?, ?, ?, ?)"
            );
            foreach ($candidatesActive as $c) {
                $stmtCand->execute([$c['name'], $c['photo'], $c['symbol'], $activeCatId]);
                $candId = (int)$this->pdo->lastInsertId();
                $activeCandIds[] = $candId;
                $summary['candidates'][] = ['id' => $candId, 'name' => $c['name'], 'category' => 'Student Council'];
            }

            // 25 Voters for Active Election
            $activeVoterIds = [];
            $stmtVoter = $this->pdo->prepare(
                "INSERT INTO system_users (full_name, email, password, role, election_category_id, date_of_birth, voted)
                 VALUES (?, ?, ?, 'voter', ?, ?, 'No')"
            );

            for ($i = 1; $i <= 25; $i++) {
                $voterName = self::FIRST_NAMES[$i % count(self::FIRST_NAMES)] . ' ' . self::LAST_NAMES[($i * 3) % count(self::LAST_NAMES)];
                $email = "voter{$i}@evoting.com";
                $dob = (2000 - ($i % 5)) . '-' . sprintf('%02d', ($i % 12) + 1) . '-' . sprintf('%02d', ($i % 28) + 1);
                $stmtVoter->execute([$voterName, $email, $this->defaultPasswordHash, $activeCatId, $dob]);
                $voterId = (int)$this->pdo->lastInsertId();
                $activeVoterIds[] = $voterId;
            }

            // Cast 16 votes (leaving 9 unvoted for live manual voter testing)
            // Distribution: Alex Rivera (7), Jane Doe (5), Michael Chang (3), Chloe Bennett (1)
            $voteDistribution = [
                $activeCandIds[0] => 7,
                $activeCandIds[1] => 5,
                $activeCandIds[2] => 3,
                $activeCandIds[3] => 1,
            ];

            $stmtVote = $this->pdo->prepare(
                "INSERT INTO vote (voter_id, category_id, candidate_id) VALUES (?, ?, ?)"
            );
            $stmtMarkVoted = $this->pdo->prepare("UPDATE system_users SET voted = 'Yes' WHERE user_id = ?");

            $voterIndex = 0;
            foreach ($voteDistribution as $candId => $numVotes) {
                for ($v = 0; $v < $numVotes; $v++) {
                    $vId = $activeVoterIds[$voterIndex];
                    $stmtVote->execute([$vId, $activeCatId, $candId]);
                    $stmtMarkVoted->execute([$vId]);
                    $summary['votes_cast']++;
                    $voterIndex++;
                }
            }

            // ---------------------------------------------------------
            // 2. UPCOMING ELECTION (Voting starts in the future)
            // ---------------------------------------------------------
            $startUp = date('Y-m-d H:i:s', $now + 3 * 86400); // 3 days from now
            $endUp   = date('Y-m-d H:i:s', $now + 10 * 86400); // 10 days from now
            $expUp   = date('Y-m-d H:i:s', $now + 11 * 86400);

            $stmt->execute(['Faculty Board Representative', 30, 'Yes', $startUp, $endUp, $expUp]);
            $upCatId = (int)$this->pdo->lastInsertId();
            $summary['categories'][] = ['id' => $upCatId, 'name' => 'Faculty Board Representative', 'status' => 'Upcoming'];

            // Organiser for Upcoming Election
            $stmtUser->execute(['Marcus Vance', 'faculty.organiser@evoting.com', $this->defaultPasswordHash, $upCatId]);
            $summary['organisers'][] = ['name' => 'Marcus Vance', 'email' => 'faculty.organiser@evoting.com', 'category' => 'Faculty Board'];

            // Candidates for Upcoming Election
            $candidatesUp = [
                ['name' => 'Prof. David Miller', 'photo' => 'david_miller.jpg', 'symbol' => 'book.png'],
                ['name' => 'Dr. Elena Rostova',  'photo' => 'elena_rostova.jpg', 'symbol' => 'globe.png'],
                ['name' => 'Sophia Patel',       'photo' => 'sophia_patel.jpg',  'symbol' => 'feather.png'],
            ];
            foreach ($candidatesUp as $c) {
                $stmtCand->execute([$c['name'], $c['photo'], $c['symbol'], $upCatId]);
                $summary['candidates'][] = ['id' => (int)$this->pdo->lastInsertId(), 'name' => $c['name'], 'category' => 'Faculty Board'];
            }

            // 15 Voters for Upcoming Election (0 votes cast)
            for ($i = 1; $i <= 15; $i++) {
                $voterName = self::FIRST_NAMES[($i + 5) % count(self::FIRST_NAMES)] . ' ' . self::LAST_NAMES[($i * 4) % count(self::LAST_NAMES)];
                $email = "fvoter{$i}@evoting.com";
                $dob = '1998-05-14';
                $stmtVoter->execute([$voterName, $email, $this->defaultPasswordHash, $upCatId, $dob]);
            }

            // ---------------------------------------------------------
            // 3. CONCLUDED ELECTION (Voting ended yesterday, results ready)
            // ---------------------------------------------------------
            $startCon = date('Y-m-d H:i:s', $now - 7 * 86400); // 7 days ago
            $endCon   = date('Y-m-d H:i:s', $now - 1 * 86400); // 1 day ago
            $expCon   = date('Y-m-d H:i:s', $now - 1 * 86400);

            $stmt->execute(['Alumni Advisory Board', 25, 'No', $startCon, $endCon, $expCon]);
            $conCatId = (int)$this->pdo->lastInsertId();
            $summary['categories'][] = ['id' => $conCatId, 'name' => 'Alumni Advisory Board', 'status' => 'Concluded'];

            // Organiser for Concluded Election
            $stmtUser->execute(['Rachel Green', 'alumni.organiser@evoting.com', $this->defaultPasswordHash, $conCatId]);
            $summary['organisers'][] = ['name' => 'Rachel Green', 'email' => 'alumni.organiser@evoting.com', 'category' => 'Alumni Advisory'];

            // Candidates for Concluded Election
            $candidatesCon = [
                ['name' => 'James Wilson', 'photo' => 'james_wilson.jpg', 'symbol' => 'torch.png'],
                ['name' => 'Emily Watson', 'photo' => 'emily_watson.jpg', 'symbol' => 'compass.png'],
                ['name' => 'Daniel Craig', 'photo' => 'daniel_craig.jpg', 'symbol' => 'shield.png'],
            ];
            $conCandIds = [];
            foreach ($candidatesCon as $c) {
                $stmtCand->execute([$c['name'], $c['photo'], $c['symbol'], $conCatId]);
                $candId = (int)$this->pdo->lastInsertId();
                $conCandIds[] = $candId;
                $summary['candidates'][] = ['id' => $candId, 'name' => $c['name'], 'category' => 'Alumni Advisory'];
            }

            // 20 Voters for Concluded Election (18 cast votes: 11 James Wilson, 5 Emily Watson, 2 Daniel Craig)
            $conVoterIds = [];
            for ($i = 1; $i <= 20; $i++) {
                $voterName = self::FIRST_NAMES[($i + 10) % count(self::FIRST_NAMES)] . ' ' . self::LAST_NAMES[($i * 2) % count(self::LAST_NAMES)];
                $email = "alumni{$i}@evoting.com";
                $dob = '1995-11-20';
                $stmtVoter->execute([$voterName, $email, $this->defaultPasswordHash, $conCatId, $dob]);
                $conVoterIds[] = (int)$this->pdo->lastInsertId();
            }

            $conVoteDistribution = [
                $conCandIds[0] => 11, // Winner
                $conCandIds[1] => 5,
                $conCandIds[2] => 2,
            ];

            $voterIdxCon = 0;
            foreach ($conVoteDistribution as $candId => $numVotes) {
                for ($v = 0; $v < $numVotes; $v++) {
                    $vId = $conVoterIds[$voterIdxCon];
                    $stmtVote->execute([$vId, $conCatId, $candId]);
                    $stmtMarkVoted->execute([$vId]);
                    $summary['votes_cast']++;
                    $voterIdxCon++;
                }
            }

            // ---------------------------------------------------------
            // 4. PENDING ELECTION HOST REQUESTS
            // ---------------------------------------------------------
            $stmtReq = $this->pdo->prepare(
                "INSERT INTO request_to_register (full_name, email, election_category, max_voter, adult_voter, expire_timestamp, ac_status)
                 VALUES (?, ?, ?, ?, ?, ?, 'pending')"
            );

            $stmtReq->execute([
                'Dr. Robert Chen',
                'robert.chen@engineering.edu',
                'Engineering Society Executive',
                40,
                'Yes',
                date('Y-m-d H:i:s', $now + 14 * 86400)
            ]);
            $summary['requests']++;

            $stmtReq->execute([
                'Samantha Reed',
                'samantha@athletics.org',
                'Varsity Sports Club Captaincy',
                30,
                'No',
                date('Y-m-d H:i:s', $now + 21 * 86400)
            ]);
            $summary['requests']++;

            $this->pdo->commit();
        } catch (\Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw new RuntimeException("Demo data generation failed: " . $e->getMessage(), 0, $e);
        }

        return $summary;
    }

    /**
     * Adds N random voters to a specified category.
     */
    public function addRandomVoters(int $categoryId, int $count = 10): array
    {
        $category = $this->pdo->query("SELECT id, name FROM election_category WHERE id = " . (int)$categoryId)->fetch(PDO::FETCH_ASSOC);
        if (!$category) {
            throw new RuntimeException("Category ID {$categoryId} not found.");
        }

        $created = [];
        $stmtVoter = $this->pdo->prepare(
            "INSERT INTO system_users (full_name, email, password, role, election_category_id, date_of_birth, voted)
             VALUES (?, ?, ?, 'voter', ?, ?, 'No')"
        );

        $now = time();
        for ($i = 0; $i < $count; $i++) {
            $fn = self::FIRST_NAMES[array_rand(self::FIRST_NAMES)];
            $ln = self::LAST_NAMES[array_rand(self::LAST_NAMES)];
            $name = "{$fn} {$ln}";
            $randomNum = random_int(100, 9999);
            $email = strtolower($fn) . '.' . strtolower($ln) . $randomNum . '@evoting.com';
            $dobYear = random_int(1995, 2005);
            $dobMonth = sprintf('%02d', random_int(1, 12));
            $dobDay = sprintf('%02d', random_int(1, 28));
            $dob = "{$dobYear}-{$dobMonth}-{$dobDay}";

            $stmtVoter->execute([$name, $email, $this->defaultPasswordHash, $categoryId, $dob]);
            $created[] = [
                'id' => (int)$this->pdo->lastInsertId(),
                'name' => $name,
                'email' => $email,
                'password' => 'password123'
            ];
        }

        return $created;
    }

    /**
     * Simulates votes in a category for voters who haven't voted yet.
     */
    public function simulateVotes(int $categoryId, int $ballotsToCast = 5): int
    {
        // Get candidate IDs in this category
        $stmtCands = $this->pdo->prepare("SELECT id FROM candidate WHERE category_id = ?");
        $stmtCands->execute([$categoryId]);
        $candIds = $stmtCands->fetchAll(PDO::FETCH_COLUMN);

        if (empty($candIds)) {
            throw new RuntimeException("Cannot simulate votes: Category {$categoryId} has no candidates.");
        }

        // Get unvoted voters in this category
        $stmtVoters = $this->pdo->prepare("SELECT user_id FROM system_users WHERE election_category_id = ? AND role = 'voter' AND voted = 'No' LIMIT " . (int)$ballotsToCast);
        $stmtVoters->execute([$categoryId]);
        $voterIds = $stmtVoters->fetchAll(PDO::FETCH_COLUMN);

        if (empty($voterIds)) {
            return 0;
        }

        $stmtVote = $this->pdo->prepare("INSERT INTO vote (voter_id, category_id, candidate_id) VALUES (?, ?, ?)");
        $stmtMark = $this->pdo->prepare("UPDATE system_users SET voted = 'Yes' WHERE user_id = ?");

        $castCount = 0;
        $this->pdo->beginTransaction();
        try {
            foreach ($voterIds as $vId) {
                $chosenCand = $candIds[array_rand($candIds)];
                $stmtVote->execute([(int)$vId, $categoryId, (int)$chosenCand]);
                $stmtMark->execute([(int)$vId]);
                $castCount++;
            }
            $this->pdo->commit();
        } catch (\Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw new RuntimeException("Vote simulation failed: " . $e->getMessage(), 0, $e);
        }

        return $castCount;
    }

    /**
     * Purges demo data (categories, candidates, non-admin users, votes, requests).
     * Preserves admin accounts.
     */
    public function purgeDemoData(): void
    {
        $this->pdo->beginTransaction();
        try {
            $this->pdo->exec("DELETE FROM vote");
            $this->pdo->exec("DELETE FROM candidate");
            $this->pdo->exec("DELETE FROM system_users WHERE role <> 'admin'");
            $this->pdo->exec("DELETE FROM election_category");
            $this->pdo->exec("DELETE FROM request_to_register");
            $this->pdo->commit();
        } catch (\Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw new RuntimeException("Purge failed: " . $e->getMessage(), 0, $e);
        }
    }
}
