<?php
require_once __DIR__ . "/../includes/bootstrap.php";
require_role("admin");

use App\Service\DemoDataSeeder;

$seeder = new DemoDataSeeder(db());

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    csrf_verify();
    $action = $_POST["action"] ?? "";

    try {
        if ($action === "seed_full") {
            $purge = isset($_POST["purge"]) && $_POST["purge"] === "1";
            $res = $seeder->seedFullShowcase($purge);
            $_SESSION["messages"][] = "Full showcase dataset generated successfully! Created "
                . count($res["categories"]) . " categories, "
                . count($res["candidates"]) . " candidates, and "
                . $res["votes_cast"] . " cast ballots.";
        } elseif ($action === "add_voters") {
            $catId = (int)($_POST["category_id"] ?? 0);
            $count = max(1, min(100, (int)($_POST["voter_count"] ?? 10)));
            if ($catId <= 0) {
                $_SESSION["errors"][] = "Please select a valid election category.";
            } else {
                $voters = $seeder->addRandomVoters($catId, $count);
                $_SESSION["messages"][] = "Successfully added {$count} random voters to category.";
            }
        } elseif ($action === "simulate_votes") {
            $catId = (int)($_POST["category_id"] ?? 0);
            $count = max(1, min(50, (int)($_POST["ballot_count"] ?? 5)));
            if ($catId <= 0) {
                $_SESSION["errors"][] = "Please select a valid election category.";
            } else {
                $cast = $seeder->simulateVotes($catId, $count);
                $_SESSION["messages"][] = "Simulated {$cast} random ballot(s) for unvoted voters.";
            }
        } elseif ($action === "purge") {
            $seeder->purgeDemoData();
            $_SESSION["messages"][] = "All election categories, candidates, non-admin users, and ballots have been cleared.";
        }
    } catch (\Throwable $e) {
        error_log("Demo data operation failed: " . $e->getMessage());
        $_SESSION["errors"][] = "Operation failed: " . $e->getMessage();
    }

    redirect($_SERVER["PHP_SELF"]);
}

require_once __DIR__ . "/nav.php";

$pdo = db();
$categoryCount  = (int)($pdo->query("SELECT COUNT(*) FROM election_category")->fetchColumn() ?: 0);
$organiserCount = (int)($pdo->query("SELECT COUNT(*) FROM system_users WHERE role = 'organiser'")->fetchColumn() ?: 0);
$voterCount     = (int)($pdo->query("SELECT COUNT(*) FROM system_users WHERE role = 'voter'")->fetchColumn() ?: 0);
$ballotCount    = (int)($pdo->query("SELECT COUNT(*) FROM vote")->fetchColumn() ?: 0);
$requestCount   = (int)($pdo->query("SELECT COUNT(*) FROM request_to_register WHERE ac_status = 'pending'")->fetchColumn() ?: 0);

$categories = fetchAll("SELECT id, name, vote_start_time, vote_end_time FROM election_category ORDER BY id DESC");
$now = time();
?>

<div class="container-fluid px-4 py-3">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 pb-2 border-bottom">
        <div>
            <h2 class="h3 fw-bold text-dark mb-1">
                <i class="bi bi-database-fill-gear text-warning me-2"></i>Showcase & Demo Data Generator
            </h2>
            <div class="text-muted">Generate realistic test data across active, upcoming, and concluded election scenarios</div>
        </div>
        <div>
            <a href="index.php" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i>Back to Dashboard
            </a>
        </div>
    </div>

    <?php require_once("../includes/view_messages_and_errors.php"); ?>

    <!-- Current Database Summary -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md">
            <div class="card shadow-sm border-0 p-3 h-100">
                <div class="text-muted small text-uppercase fw-semibold">Categories</div>
                <div class="fs-4 fw-bold text-primary mt-1"><?= $categoryCount ?></div>
            </div>
        </div>
        <div class="col-6 col-md">
            <div class="card shadow-sm border-0 p-3 h-100">
                <div class="text-muted small text-uppercase fw-semibold">Organisers</div>
                <div class="fs-4 fw-bold text-success mt-1"><?= $organiserCount ?></div>
            </div>
        </div>
        <div class="col-6 col-md">
            <div class="card shadow-sm border-0 p-3 h-100">
                <div class="text-muted small text-uppercase fw-semibold">Voters</div>
                <div class="fs-4 fw-bold text-info mt-1"><?= $voterCount ?></div>
            </div>
        </div>
        <div class="col-6 col-md">
            <div class="card shadow-sm border-0 p-3 h-100">
                <div class="text-muted small text-uppercase fw-semibold">Ballots Cast</div>
                <div class="fs-4 fw-bold text-dark mt-1"><?= $ballotCount ?></div>
            </div>
        </div>
        <div class="col-12 col-md">
            <div class="card shadow-sm border-0 p-3 h-100">
                <div class="text-muted small text-uppercase fw-semibold">Pending Requests</div>
                <div class="fs-4 fw-bold text-warning mt-1"><?= $requestCount ?></div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Left: Quick Actions -->
        <div class="col-lg-6">
            <!-- 1-Click Full Showcase Card -->
            <div class="card shadow-sm border-0 mb-4 border-top border-4 border-warning">
                <div class="card-header bg-white py-3">
                    <h5 class="card-title mb-0 fw-bold text-dark">
                        <i class="bi bi-lightning-charge-fill text-warning me-2"></i>1-Click Full Showcase Setup
                    </h5>
                </div>
                <div class="card-body p-4">
                    <p class="text-muted">
                        Generates a complete, ready-to-present election environment across active, upcoming, and concluded scenarios:
                    </p>
                    <ul class="small text-muted mb-4 ps-3">
                        <li><strong>Live Active Election:</strong> Student Council (25 voters, 16 votes cast, 4 candidates). Open right now for live balloting.</li>
                        <li><strong>Upcoming Election:</strong> Faculty Board (15 voters, 3 candidates). Countdown timer counts down to opening.</li>
                        <li><strong>Concluded Election:</strong> Alumni Advisory Board (20 voters, 18 votes cast). Displays winner banner &amp; Google bar chart.</li>
                        <li><strong>Pending Requests:</strong> 2 election registration requests for admin approval.</li>
                    </ul>

                    <form action="<?= e($_SERVER['PHP_SELF']) ?>" method="post">
                        <?= csrf_field() ?>
                        <input type="hidden" name="action" value="seed_full">

                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" name="purge" value="1" id="purgeCheck" checked>
                            <label class="form-check-label text-dark small" for="purgeCheck">
                                <strong>Clean reset:</strong> Clear previous demo categories, voters, and ballots before generating
                            </label>
                        </div>

                        <button type="submit" class="btn btn-warning text-dark fw-bold px-4 py-2 shadow-sm">
                            <i class="bi bi-magic me-1"></i> Generate Full Showcase Data
                        </button>
                    </form>
                </div>
            </div>

            <!-- Granular Data Tools -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="card-title mb-0 fw-semibold text-primary">
                        <i class="bi bi-tools me-2"></i>Granular Data Generation
                    </h5>
                </div>
                <div class="card-body p-4">
                    <!-- Add Voters Form -->
                    <h6 class="fw-bold text-dark mb-2">Add Random Voters to Category</h6>
                    <form action="<?= e($_SERVER['PHP_SELF']) ?>" method="post" class="row g-2 align-items-end mb-4">
                        <?= csrf_field() ?>
                        <input type="hidden" name="action" value="add_voters">

                        <div class="col-sm-6">
                            <label class="form-label small text-muted">Select Category</label>
                            <select name="category_id" class="form-select form-select-sm" required>
                                <option value="">-- Choose Category --</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?= (int)$cat['id'] ?>"><?= e($cat['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-sm-3">
                            <label class="form-label small text-muted">Count</label>
                            <select name="voter_count" class="form-select form-select-sm">
                                <option value="5">5 voters</option>
                                <option value="10" selected>10 voters</option>
                                <option value="25">25 voters</option>
                                <option value="50">50 voters</option>
                            </select>
                        </div>
                        <div class="col-sm-3">
                            <button type="submit" class="btn btn-sm btn-outline-primary w-100">
                                <i class="bi bi-person-plus me-1"></i>Add
                            </button>
                        </div>
                    </form>

                    <hr>

                    <!-- Simulate Votes Form -->
                    <h6 class="fw-bold text-dark mb-2 mt-3">Simulate Random Ballots</h6>
                    <p class="small text-muted mb-2">Casts random votes for unvoted voters to populate tally charts.</p>
                    <form action="<?= e($_SERVER['PHP_SELF']) ?>" method="post" class="row g-2 align-items-end">
                        <?= csrf_field() ?>
                        <input type="hidden" name="action" value="simulate_votes">

                        <div class="col-sm-6">
                            <label class="form-label small text-muted">Select Category</label>
                            <select name="category_id" class="form-select form-select-sm" required>
                                <option value="">-- Choose Category --</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?= (int)$cat['id'] ?>"><?= e($cat['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-sm-3">
                            <label class="form-label small text-muted">Ballots to Cast</label>
                            <select name="ballot_count" class="form-select form-select-sm">
                                <option value="3">3 votes</option>
                                <option value="5" selected>5 votes</option>
                                <option value="10">10 votes</option>
                            </select>
                        </div>
                        <div class="col-sm-3">
                            <button type="submit" class="btn btn-sm btn-outline-success w-100">
                                <i class="bi bi-check2-circle me-1"></i>Simulate
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Purge Card -->
            <div class="card shadow-sm border-0 border-top border-4 border-danger">
                <div class="card-header bg-white py-3">
                    <h5 class="card-title mb-0 fw-semibold text-danger">
                        <i class="bi bi-trash3-fill me-2"></i>Reset &amp; Purge Test Data
                    </h5>
                </div>
                <div class="card-body p-4">
                    <p class="small text-muted">
                        Clears all categories, candidates, voters, ballots, and registration requests. The administrator account is preserved.
                    </p>
                    <form action="<?= e($_SERVER['PHP_SELF']) ?>" method="post" onsubmit="return confirm('Are you sure you want to delete all test data? This will clear all categories, candidates, voters, and ballots.');">
                        <?= csrf_field() ?>
                        <input type="hidden" name="action" value="purge">
                        <button type="submit" class="btn btn-sm btn-outline-danger">
                            <i class="bi bi-exclamation-triangle me-1"></i>Purge All Test Data
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Right: Demonstration Credentials Cheat Sheet -->
        <div class="col-lg-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0 fw-semibold text-primary">
                        <i class="bi bi-key-fill text-primary me-2"></i>Showcase Login Credentials
                    </h5>
                    <span class="badge bg-light text-dark border">Password: <code>password123</code></span>
                </div>
                <div class="card-body p-4">
                    <p class="text-muted small">
                        Use these credentials to sign in and test each user flow. All demonstration accounts use the password <strong><code>password123</code></strong>.
                    </p>

                    <!-- Table of Accounts -->
                    <div class="table-responsive">
                        <table class="table table-hover align-middle small mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Role / Flow</th>
                                    <th>Email / Login</th>
                                    <th>Scenario to Test</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><span class="badge bg-primary">Admin</span></td>
                                    <td><code>admin@evoting.com</code></td>
                                    <td>Password: <code>12345678</code> &bull; System management, category creation, approve requests.</td>
                                </tr>
                                <tr>
                                    <td><span class="badge bg-success">Organiser</span></td>
                                    <td><code>council.organiser@evoting.com</code></td>
                                    <td>Live election dashboard, add candidates &amp; voters, view turnout.</td>
                                </tr>
                                <tr>
                                    <td><span class="badge bg-info">Voter (Ready)</span></td>
                                    <td><code>voter17@evoting.com</code></td>
                                    <td><strong>Live Ballot:</strong> Has not voted yet. Log in to experience candidate card selection and submit a vote.</td>
                                </tr>
                                <tr>
                                    <td><span class="badge bg-secondary">Voter (Done)</span></td>
                                    <td><code>voter1@evoting.com</code></td>
                                    <td><strong>Already Voted:</strong> Shows confirmation screen and polls countdown.</td>
                                </tr>
                                <tr>
                                    <td><span class="badge bg-warning text-dark">Voter (Upcoming)</span></td>
                                    <td><code>fvoter1@evoting.com</code></td>
                                    <td><strong>Opening Soon:</strong> Shows countdown timer counting down to voting start.</td>
                                </tr>
                                <tr>
                                    <td><span class="badge bg-dark">Voter (Concluded)</span></td>
                                    <td><code>alumni1@evoting.com</code></td>
                                    <td><strong>Election Ended:</strong> Shows gold trophy winner banner and Google bar chart.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Category Status Table -->
                    <h6 class="fw-bold text-dark mt-4 mb-2">Current Categories &amp; Timestamps</h6>
                    <?php if (empty($categories)): ?>
                        <div class="alert alert-light border small text-muted text-center py-3">
                            No categories exist. Click "Generate Full Showcase Data" to create sample elections.
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered align-middle small mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Name</th>
                                        <th>Voting Start</th>
                                        <th>Voting End</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($categories as $c):
                                        $s = !empty($c['vote_start_time']) ? strtotime($c['vote_start_time']) : 0;
                                        $e = !empty($c['vote_end_time']) ? strtotime($c['vote_end_time']) : 0;
                                        $badge = '<span class="badge bg-secondary">Unscheduled</span>';
                                        if ($s && $e) {
                                            if ($now < $s) {
                                                $badge = '<span class="badge bg-warning text-dark">Upcoming</span>';
                                            } elseif ($now <= $e) {
                                                $badge = '<span class="badge bg-success">Live Now</span>';
                                            } else {
                                                $badge = '<span class="badge bg-dark">Concluded</span>';
                                            }
                                        }
                                    ?>
                                        <tr>
                                            <td class="fw-semibold"><?= e($c['name']) ?></td>
                                            <td><?= e($c['vote_start_time']) ?></td>
                                            <td><?= e($c['vote_end_time']) ?></td>
                                            <td><?= $badge ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
require_once("../includes/footer.php");
