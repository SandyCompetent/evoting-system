<?php
declare(strict_types=1);

namespace App\Repository\Pdo;

use PDO;

final class PdoDashboardRepository
{
    public function __construct(private readonly PDO $pdo) {}

    /**
     * @return array{categories:int,organisers:int,voters:int,pending:int}
     */
    public function adminCounts(): array
    {
        return [
            'categories' => (int) $this->pdo->query('SELECT COUNT(*) FROM election_category')->fetchColumn(),
            'organisers' => (int) $this->pdo->query("SELECT COUNT(*) FROM system_users WHERE role = 'organiser'")->fetchColumn(),
            'voters'     => (int) $this->pdo->query("SELECT COUNT(*) FROM system_users WHERE role = 'voter'")->fetchColumn(),
            'pending'    => (int) $this->pdo->query(
                "SELECT COUNT(*) FROM request_to_register WHERE LOWER(ac_status) = 'pending'"
            )->fetchColumn(),
        ];
    }

    /**
     * @return array{categories:int,organisers:int,voters:int,ballots:int,pending:int}
     */
    public function demoCounts(): array
    {
        return [
            'categories' => (int) $this->pdo->query('SELECT COUNT(*) FROM election_category')->fetchColumn(),
            'organisers' => (int) $this->pdo->query("SELECT COUNT(*) FROM system_users WHERE role = 'organiser'")->fetchColumn(),
            'voters'     => (int) $this->pdo->query("SELECT COUNT(*) FROM system_users WHERE role = 'voter'")->fetchColumn(),
            'ballots'    => (int) $this->pdo->query('SELECT COUNT(*) FROM vote')->fetchColumn(),
            'pending'    => (int) $this->pdo->query(
                "SELECT COUNT(*) FROM request_to_register WHERE LOWER(ac_status) = 'pending'"
            )->fetchColumn(),
        ];
    }
}
