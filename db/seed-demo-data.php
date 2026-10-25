<?php
declare(strict_types=1);

/**
 * CLI Demo Data Seeder for e-Voting System.
 *
 * Usage:
 *   php db/seed-demo-data.php          (seeds showcase data, appending or refreshing)
 *   php db/seed-demo-data.php --fresh  (purges old demo data first)
 */

require_once __DIR__ . "/../includes/bootstrap.php";

use App\Service\DemoDataSeeder;

$isFresh = in_array('--fresh', $argv ?? [], true) || in_array('-f', $argv ?? [], true);

echo "\n\033[1;34m=== E-Voting System Demo Data Seeder ===\033[0m\n";
echo "Connecting to database...\n";

try {
    $pdo = db();
    $seeder = new DemoDataSeeder($pdo);

    if ($isFresh) {
        echo "\033[1;33mPurging previous demo data...\033[0m\n";
    }

    echo "Generating showcase categories, candidates, voters, and ballots...\n";
    $summary = $seeder->seedFullShowcase($isFresh);

    echo "\n\033[1;32m✔ Showcase Dataset Successfully Seeded!\033[0m\n\n";

    echo "\033[1mCategories Created (" . count($summary['categories']) . "):\033[0m\n";
    foreach ($summary['categories'] as $cat) {
        echo "  - [ID: {$cat['id']}] {$cat['name']} \033[1;36m({$cat['status']})\033[0m\n";
    }

    echo "\n\033[1mSummary Counts:\033[0m\n";
    echo "  - Candidates:   " . count($summary['candidates']) . "\n";
    echo "  - Organisers:   " . count($summary['organisers']) . "\n";
    echo "  - Ballots Cast: {$summary['votes_cast']}\n";
    echo "  - Requests:     {$summary['requests']}\n";

    echo "\n\033[1;33mSample Demonstration Logins (Password: password123):\033[0m\n";
    echo "  - \033[1mAdmin:\033[0m               admin@evoting.com\n";
    echo "  - \033[1mOrganiser (Live):\033[0m     council.organiser@evoting.com\n";
    echo "  - \033[1mVoter (Ready to Vote):\033[0m voter17@evoting.com  (Student Council Election)\n";
    echo "  - \033[1mVoter (Already Voted):\033[0m voter1@evoting.com   (Student Council Election)\n";
    echo "  - \033[1mVoter (Opening Soon):\033[0m  fvoter1@evoting.com  (Faculty Board Election)\n";
    echo "  - \033[1mVoter (Concluded):\033[0m     alumni1@evoting.com  (Alumni Advisory Board)\n\n";

} catch (\Throwable $e) {
    echo "\n\033[1;31m✖ Seeder failed: " . $e->getMessage() . "\033[0m\n";
    exit(1);
}
