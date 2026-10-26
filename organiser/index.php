<?php
declare(strict_types=1);
require_once __DIR__ . "/../includes/bootstrap.php";
require_role("organiser");

use App\Repository\Pdo\PdoCategoryRepository;
use App\Repository\Pdo\PdoUserRepository;
use App\Service\VoteServiceFactory;

$user_id = (int) $_SESSION["user_id"];
$userRepo = new PdoUserRepository(db());
$categoryID = (int) ($userRepo->findById($user_id)?->categoryId ?? 0);

$categoryRepo = new PdoCategoryRepository(db());
$votesService = VoteServiceFactory::create(db());

if (isset($_POST["update_vote_time"])) {
    csrf_verify();
    $start = parse_datetime_local((string) ($_POST["start_timestamp"] ?? ""));
    $end   = parse_datetime_local((string) ($_POST["end_timestamp"] ?? ""));

    if ($start === null || $end === null || strtotime($start) >= strtotime($end)) {
        $_SESSION["errors"][] = "Please provide a valid start and end time; the end must be after the start.";
        redirect(current_path());
    }

    $categoryRepo->updateVotingWindow($categoryID, $start, $end);
    $_SESSION["messages"][] = "Election voting window updated successfully.";
    redirect(current_path());
}

require_once __DIR__ . "/nav.php";

$category           = $categoryRepo->find($categoryID);
$categoryName       = $category?->name ?? "Election";
$get_vote_start_dt  = $category?->startsAt?->format('Y-m-d H:i:s') ?? "";
$get_vote_end_dt    = $category?->endsAt?->format('Y-m-d H:i:s') ?? "";

// Turnout summary via VoteService & Turnout Value Object
$turnoutReport = $votesService->turnoutReport($categoryID);
$totalVoters   = $turnoutReport->eligibleVoters;
$voterReceived = $turnoutReport->votesCast;
$voterPending  = $turnoutReport->pendingVoters();
$turnoutPct    = $turnoutReport->percentage();

$now = time();
$startTime = !empty($get_vote_start_dt) ? strtotime($get_vote_start_dt) : 0;
$endTime   = !empty($get_vote_end_dt) ? strtotime($get_vote_end_dt) : 0;

$statusBadge = '<span class="badge bg-secondary">Unscheduled</span>';
if ($startTime && $endTime) {
    if ($now < $startTime) {
        $statusBadge = '<span class="badge bg-warning text-dark"><i class="bi bi-hourglass-split me-1"></i>Upcoming</span>';
    } elseif ($now <= $endTime) {
        $statusBadge = '<span class="badge bg-success"><i class="bi bi-broadcast me-1"></i>Live & Active</span>';
    } else {
        $statusBadge = '<span class="badge bg-danger"><i class="bi bi-check-all me-1"></i>Closed</span>';
    }
}

// Auditable tally via VoteService
$tally = $votesService->tally($categoryID);
$candidate_name_array = array_map(static fn($s) => $s->candidateName, $tally->standings);
$candidate_vote_array = array_map(static fn($s) => $s->votes, $tally->standings);
$count = count($tally->standings);
$winner = $tally->winnerLabel();
?>

<div class="container py-3">
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2 mb-4 pb-2 border-bottom">
        <div>
            <h2 class="h3 fw-bold text-dark mb-1">
                <i class="bi bi-speedometer2 text-primary me-2"></i>Organiser Dashboard
            </h2>
            <div class="text-muted small">
                Category: <strong class="text-dark"><?= e($categoryName) ?></strong> (ID #<?= (int)$categoryID ?>) &bull; Status: <?= $statusBadge ?>
            </div>
        </div>
        <div class="mt-2 mt-sm-0 d-flex flex-wrap gap-2">
            <a href="create-candidate-account.php" class="btn btn-sm btn-outline-primary">
                <i class="bi bi-person-plus me-1"></i>New Candidate
            </a>
            <a href="create-voter-account.php" class="btn btn-sm btn-primary">
                <i class="bi bi-people me-1"></i>New Voter
            </a>
        </div>
    </div>

    <?php require_once("../includes/view-messages-and-errors.php"); ?>

    <!-- 4-Stat KPI Grid -->
    <div class="row g-2 g-sm-3 mb-4">
        <div class="col-6 col-xl-3">
            <div class="card stat-card h-100 p-3">
                <div class="d-flex align-items-center">
                    <div class="stat-icon bg-info bg-opacity-10 text-info me-3">
                        <i class="bi bi-people-fill"></i>
                    </div>
                    <div>
                        <div class="text-muted small text-uppercase fw-semibold">Eligible Voters</div>
                        <div class="fs-4 fw-bold text-dark"><?= (int)$totalVoters ?></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="card stat-card h-100 p-3">
                <div class="d-flex align-items-center">
                    <div class="stat-icon bg-primary bg-opacity-10 text-primary me-3">
                        <i class="bi bi-check2-circle"></i>
                    </div>
                    <div>
                        <div class="text-muted small text-uppercase fw-semibold">Ballots Cast</div>
                        <div class="fs-4 fw-bold text-dark"><?= (int)$voterReceived ?></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="card stat-card h-100 p-3">
                <div class="d-flex align-items-center">
                    <div class="stat-icon bg-success bg-opacity-10 text-success me-3">
                        <i class="bi bi-pie-chart-fill"></i>
                    </div>
                    <div>
                        <div class="text-muted small text-uppercase fw-semibold">Turnout</div>
                        <div class="fs-4 fw-bold text-dark"><?= $turnoutPct ?>%</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="card stat-card h-100 p-3">
                <div class="d-flex align-items-center">
                    <div class="stat-icon bg-warning bg-opacity-10 text-warning me-3">
                        <i class="bi bi-person-badge-fill"></i>
                    </div>
                    <div>
                        <div class="text-muted small text-uppercase fw-semibold">Candidates</div>
                        <div class="fs-4 fw-bold text-dark"><?= (int)$count ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Left: Charts & Voting Window Settings -->
        <div class="col-lg-7">
            <!-- Candidate Vote Tally Chart -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="card-title mb-0 fw-semibold text-primary">
                        <i class="bi bi-bar-chart-fill me-2"></i>Candidate Vote Distribution
                    </h5>
                </div>
                <div class="card-body p-3">
                    <div id="barChartContainer" class="chart-box chart-box-lg">
                        <div id="organiser-bar-chart" class="chart-canvas-lg"></div>
                    </div>
                </div>
            </div>

            <!-- Voting Window Configuration Card -->
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="card-title mb-0 fw-semibold text-primary">
                        <i class="bi bi-clock-history me-2"></i>Configure Election Voting Window
                    </h5>
                </div>
                <div class="card-body p-4">                        <form action="<?= e(current_path()); ?>" method="post">
                        <?php echo csrf_field(); ?>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label highlight fw-semibold" for="start_timestamp">Voting Start Time</label>
                                <input type="datetime-local" class="form-control" id="start_timestamp" name="start_timestamp" value="<?= !empty($get_vote_start_dt) ? date('Y-m-d\TH:i', strtotime($get_vote_start_dt)) : '' ?>" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label highlight fw-semibold" for="end_timestamp">Voting End Time</label>
                                <input type="datetime-local" class="form-control" id="end_timestamp" name="end_timestamp" value="<?= !empty($get_vote_end_dt) ? date('Y-m-d\TH:i', strtotime($get_vote_end_dt)) : '' ?>" required>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end mt-4">
                            <button type="submit" class="btn btn-primary px-4" name="update_vote_time">
                                <i class="bi bi-save-fill me-1"></i> Save Voting Schedule
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Right: Leaderboard & Turnout Pie -->
        <div class="col-lg-5">
            <!-- Leading Candidate Card -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="card-title mb-0 fw-semibold text-primary">
                        <i class="bi bi-trophy-fill text-warning me-2"></i>Leading Candidate
                    </h5>
                </div>
                <div class="card-body p-4 text-center">
                    <?php if ($winner !== null && $count > 0): ?>
                        <div class="display-6 fw-bold text-dark mb-2"><?= e($winner); ?></div>
                        <p class="text-muted mb-0">
                            <strong><?= (int)$voterReceived; ?></strong> of <strong><?= (int)$totalVoters; ?></strong> registered voters have cast ballots
                        </p>
                    <?php else: ?>
                        <div class="py-3">
                            <i class="bi bi-person-x fs-1 text-muted d-block mb-2"></i>
                            <p class="text-muted">No candidates registered in this election category.</p>
                            <a href="create-candidate-account.php" class="btn btn-sm btn-primary">
                                <i class="bi bi-person-plus me-1"></i> Create First Candidate
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Turnout Pie Chart -->
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="card-title mb-0 fw-semibold text-primary">
                        <i class="bi bi-pie-chart-fill me-2"></i>Participation Ratio
                    </h5>
                </div>
                <div class="card-body p-3">
                    <div id="piechartContainer" class="chart-box chart-box-bar">
                        <div id="organiser-pie-chart" class="chart-canvas"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="organiser-chart-data" class="d-none" data-chart="<?= e(json_encode([
    'names' => array_values($candidate_name_array),
    'votes' => array_values($candidate_vote_array),
    'received' => (int) $voterReceived,
    'pending' => (int) $voterPending,
])); ?>"></div>

<?php
$pageScripts = [GOOGLE_CHARTS_LOADER, asset_url('assets/js/organiser-dashboard.js')];
require_once("../includes/footer.php");
?>
