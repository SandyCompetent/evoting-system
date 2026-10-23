<?php
require_once __DIR__ . "/../includes/bootstrap.php";
require_role("organiser");

use App\Repository\Pdo\PdoCategoryRepository;
use App\Service\VoteServiceFactory;

$user_id    = (int) $_SESSION["user_id"];
$organiser  = fetchOne("SELECT election_category_id FROM system_users WHERE user_id = ?", [$user_id]);
$categoryID = (int) ($organiser["election_category_id"] ?? 0);

$categoryRepo = new PdoCategoryRepository(db());
$votesService = VoteServiceFactory::create(db());

if (isset($_POST["update_vote_time"])) {
    csrf_verify();
    $raw_start = trim($_POST["start_timestamp"] ?? "");
    $raw_end   = trim($_POST["end_timestamp"] ?? "");
    $start     = $raw_start !== "" ? date("Y-m-d H:i:s", strtotime($raw_start)) : "";
    $end       = $raw_end !== "" ? date("Y-m-d H:i:s", strtotime($raw_end)) : "";

    $categoryRepo->updateVotingWindow($categoryID, $start, $end);
    $_SESSION["messages"][] = "Election voting window updated successfully.";
    redirect($_SERVER["PHP_SELF"]);
}

require_once __DIR__ . "/nav.php";

$cat = fetchOne("SELECT * FROM election_category WHERE id = ?", [$categoryID]);
$categoryName      = $cat["name"] ?? "Election";
$get_vote_start_dt = $cat["vote_start_time"] ?? "";
$get_vote_end_dt   = $cat["vote_end_time"] ?? "";

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
            <a href="create_candidate_account.php" class="btn btn-sm btn-outline-primary">
                <i class="bi bi-person-plus me-1"></i>New Candidate
            </a>
            <a href="create_voter_account.php" class="btn btn-sm btn-primary">
                <i class="bi bi-people me-1"></i>New Voter
            </a>
        </div>
    </div>

    <?php require_once("../includes/view_messages_and_errors.php"); ?>

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
                <div class="stat-icon bg-primary bg-opacity-10 text-primary me-3">
                    <i class="bi bi-check2-circle"></i>
                </div>
                <div>
                    <div class="text-muted small text-uppercase fw-semibold">Ballots Cast</div>
                    <div class="fs-4 fw-bold text-dark"><?= (int)$voterReceived ?></div>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="card stat-card h-100 p-3">
                <div class="stat-icon bg-success bg-opacity-10 text-success me-3">
                    <i class="bi bi-pie-chart-fill"></i>
                </div>
                <div>
                    <div class="text-muted small text-uppercase fw-semibold">Turnout</div>
                    <div class="fs-4 fw-bold text-dark"><?= $turnoutPct ?>%</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="card stat-card h-100 p-3">
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
                    <div id="barChartContainer" style="width: 100%; min-height: 280px;">
                        <div id="barChart" style="width: 100%; height: 280px;"></div>
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
                <div class="card-body p-4">
                    <form action="<?php echo e($_SERVER['PHP_SELF']); ?>" method="post">
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
                            <a href="create_candidate_account.php" class="btn btn-sm btn-primary">
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
                    <div id="piechartContainer" style="width: 100%; min-height: 250px;">
                        <div id="piechart" style="width: 100%; height: 250px;"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
<script type="text/javascript">
    var candidate_name_array = <?php echo json_encode($candidate_name_array); ?>;
    var candidate_vote_array = <?php echo json_encode($candidate_vote_array); ?>;
    var voterReceived = <?php echo (int) $voterReceived; ?>;
    var voterPending  = <?php echo (int) $voterPending; ?>;

    google.charts.load('current', { 'packages': ['bar', 'corechart'] });
    google.charts.setOnLoadCallback(function () {
        drawBar();
        drawPie();
    });

    function drawBar() {
        var el = document.getElementById('barChart');
        if (!el || candidate_name_array.length === 0) return;
        var data = [['Candidate', 'Votes']];
        for (var i = 0; i < candidate_name_array.length; i++) {
            data.push([candidate_name_array[i], candidate_vote_array[i]]);
        }
        var containerWidth = el.parentElement ? el.parentElement.clientWidth : 400;
        var chart = new google.charts.Bar(el);
        chart.draw(google.visualization.arrayToDataTable(data),
            google.charts.Bar.convertOptions({
                legend: { position: 'none' },
                bar: { groupWidth: "50%" },
                width: containerWidth,
                height: 280
            })
        );
    }

    function drawPie() {
        var el = document.getElementById('piechart');
        if (!el) return;
        var data = google.visualization.arrayToDataTable([
            ['Participation', 'Voters'],
            ['Ballots Cast', voterReceived],
            ['Pending Voters', voterPending]
        ]);
        var containerWidth = el.parentElement ? el.parentElement.clientWidth : 300;
        var chart = new google.visualization.PieChart(el);
        chart.draw(data, {
            pieHole: 0.45,
            legend: { position: 'bottom' },
            colors: ['#2563eb', '#cbd5e1'],
            chartArea: { width: '90%', height: '75%' },
            width: containerWidth,
            height: 250
        });
    }

    // Responsive window resize listener fixes NaN container width bugs
    window.addEventListener('resize', function() {
        drawBar();
        drawPie();
    });
</script>

<?php
require_once("../includes/footer.php");
?>
