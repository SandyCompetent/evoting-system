<?php
declare(strict_types=1);

namespace App\Tests\Unit;

use App\Service\DemoDataSeeder;
use PDO;
use PHPUnit\Framework\TestCase;

final class DemoDataSeederTest extends TestCase
{
    private PDO $pdo;
    private DemoDataSeeder $seeder;

    protected function setUp(): void
    {
        $this->pdo = new PDO('sqlite::memory:');
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->pdo->exec('
            CREATE TABLE election_category (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT, max_voter INTEGER, adult_voter TEXT,
                timestamp TEXT, expire_timestamp TEXT,
                vote_start_time TEXT, vote_end_time TEXT
            );
            CREATE TABLE candidate (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                full_name TEXT, photo TEXT, symbol TEXT,
                category_id INTEGER, timestamp TEXT
            );
            CREATE TABLE system_users (
                user_id INTEGER PRIMARY KEY AUTOINCREMENT,
                full_name TEXT, email TEXT UNIQUE, date_of_birth TEXT,
                password TEXT, role TEXT, election_category_id INTEGER,
                voted TEXT, created_timestamp TEXT
            );
            CREATE TABLE vote (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                voter_id INTEGER, category_id INTEGER, candidate_id INTEGER,
                created_at TEXT, UNIQUE(voter_id, category_id)
            );
            CREATE TABLE request_to_register (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                full_name TEXT, email TEXT, election_category TEXT,
                max_voter INTEGER, adult_voter TEXT, expire_timestamp TEXT,
                timestamp TEXT, ac_status TEXT
            );
        ');

        $this->seeder = new DemoDataSeeder($this->pdo);
    }

    public function testSeedFullShowcaseCreatesNonPastDatesAndCoherentElections(): void
    {
        $res = $this->seeder->seedFullShowcase(false);

        self::assertCount(3, $res['categories']);
        self::assertCount(10, $res['candidates']);
        self::assertCount(3, $res['organisers']);
        self::assertSame(34, $res['votes_cast']);
        self::assertSame(2, $res['requests']);

        $now = time();

        // Verify Live Election has valid non-past window (start < now < end)
        $liveCat = $this->pdo->query("SELECT * FROM election_category WHERE name LIKE '%Student Council%'")->fetch(PDO::FETCH_ASSOC);
        self::assertNotEmpty($liveCat);
        $startLive = strtotime($liveCat['vote_start_time']);
        $endLive   = strtotime($liveCat['vote_end_time']);
        self::assertTrue($startLive <= $now, 'Live election started in the recent past');
        self::assertTrue($endLive > $now, 'Live election end date must be in the future (not in past)');

        // Verify Upcoming Election window (now < start < end)
        $upCat = $this->pdo->query("SELECT * FROM election_category WHERE name LIKE '%Faculty Board%'")->fetch(PDO::FETCH_ASSOC);
        self::assertNotEmpty($upCat);
        $startUp = strtotime($upCat['vote_start_time']);
        $endUp   = strtotime($upCat['vote_end_time']);
        self::assertTrue($startUp > $now, 'Upcoming election must start in the future');
        self::assertTrue($endUp > $startUp, 'Upcoming election end must be after start');

        // Verify Concluded Election has real votes and winner
        $conCat = $this->pdo->query("SELECT * FROM election_category WHERE name LIKE '%Alumni%'")->fetch(PDO::FETCH_ASSOC);
        self::assertNotEmpty($conCat);
        $votesInCon = (int)$this->pdo->query("SELECT COUNT(*) FROM vote WHERE category_id = " . (int)$conCat['id'])->fetchColumn();
        self::assertSame(18, $votesInCon);
    }

    public function testSeedFullShowcaseCreatesAdminIfMissing(): void
    {
        $adminCountBefore = (int)$this->pdo->query("SELECT COUNT(*) FROM system_users WHERE role = 'admin'")->fetchColumn();
        self::assertSame(0, $adminCountBefore);

        $this->seeder->seedFullShowcase(false);

        $adminCountAfter = (int)$this->pdo->query("SELECT COUNT(*) FROM system_users WHERE role = 'admin'")->fetchColumn();
        self::assertSame(1, $adminCountAfter);

        $admin = $this->pdo->query("SELECT * FROM system_users WHERE role = 'admin'")->fetch(PDO::FETCH_ASSOC);
        self::assertSame('admin@evoting.com', $admin['email']);
    }

    public function testAddRandomVotersGeneratesValidRecords(): void
    {
        $res = $this->seeder->seedFullShowcase(false);
        $catId = $res['categories'][0]['id'];

        $countBefore = (int)$this->pdo->query("SELECT COUNT(*) FROM system_users WHERE election_category_id = {$catId} AND role = 'voter'")->fetchColumn();

        $voters = $this->seeder->addRandomVoters($catId, 5);
        self::assertCount(5, $voters);

        $countAfter = (int)$this->pdo->query("SELECT COUNT(*) FROM system_users WHERE election_category_id = {$catId} AND role = 'voter'")->fetchColumn();
        self::assertSame($countBefore + 5, $countAfter);

        foreach ($voters as $v) {
            self::assertNotEmpty($v['name']);
            self::assertStringContainsString('@evoting.com', $v['email']);
            self::assertSame('password123', $v['password']);
        }
    }

    public function testSimulateVotesRecordsBallotsAndMarksVoted(): void
    {
        $res = $this->seeder->seedFullShowcase(false);
        $catId = $res['categories'][0]['id'];

        $votesBefore = (int)$this->pdo->query("SELECT COUNT(*) FROM vote WHERE category_id = {$catId}")->fetchColumn();

        $cast = $this->seeder->simulateVotes($catId, 4);
        self::assertSame(4, $cast);

        $votesAfter = (int)$this->pdo->query("SELECT COUNT(*) FROM vote WHERE category_id = {$catId}")->fetchColumn();
        self::assertSame($votesBefore + 4, $votesAfter);
    }

    public function testPurgeClearsDataPreservingAdmin(): void
    {
        $this->seeder->seedFullShowcase(false);

        $this->seeder->purgeDemoData();

        $cats = (int)$this->pdo->query("SELECT COUNT(*) FROM election_category")->fetchColumn();
        $cands = (int)$this->pdo->query("SELECT COUNT(*) FROM candidate")->fetchColumn();
        $votes = (int)$this->pdo->query("SELECT COUNT(*) FROM vote")->fetchColumn();
        $nonAdmin = (int)$this->pdo->query("SELECT COUNT(*) FROM system_users WHERE role <> 'admin'")->fetchColumn();
        $admin = (int)$this->pdo->query("SELECT COUNT(*) FROM system_users WHERE role = 'admin'")->fetchColumn();

        self::assertSame(0, $cats);
        self::assertSame(0, $cands);
        self::assertSame(0, $votes);
        self::assertSame(0, $nonAdmin);
        self::assertSame(1, $admin, 'Admin account should be preserved during purge');
    }
}
