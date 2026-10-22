<?php
require_once __DIR__ . "/../includes/bootstrap.php";
require_role("voter");

use App\Repository\Pdo\PdoCategoryRepository;
use App\Repository\Pdo\PdoUserRepository;
use App\Service\VoteServiceFactory;
use App\Exception\VoteException;

$user_id = (int) $_SESSION["user_id"];

$userRepo     = new PdoUserRepository(db());
$categoryRepo = new PdoCategoryRepository(db());
$votes        = VoteServiceFactory::create(db());

$voter       = $userRepo->findById($user_id);
$category_id = (int) ($voter?->categoryId ?? 0);
$voterName   = $voter?->fullName ?? "Voter";

// ---- Cast a single vote ----
if (isset($_POST["submit"])) {
    csrf_verify();
    $candidate_id = (int) ($_POST["vote"] ?? 0);
    try {
        $votes->castVote($user_id, $candidate_id);
        // Denormalised convenience flag for dashboards; the vote table is authoritative.
        execute("UPDATE system_users SET voted = 'Yes' WHERE user_id = ?", [$user_id]);
        $_SESSION["messages"][] = "Your ballot has been successfully verified and recorded. Thank you for voting!";
    } catch (VoteException $ex) {
        $_SESSION["errors"][] = $ex->getMessage();
    } catch (Throwable $ex) {
        error_log("Vote cast failed: " . $ex->getMessage());
        $_SESSION["errors"][] = "Vote submission failed. Please try again.";
    }
    redirect($_SERVER["PHP_SELF"]);
}

require_once __DIR__ . "/nav.php";

$election        = $categoryRepo->find($category_id);
$category_name   = $election?->name ?? "";
$vote_start_time = $election?->startsAt ? $election->startsAt->format('Y-m-d H:i:s') : "";
$vote_end_time   = $election?->endsAt   ? $election->endsAt->format('Y-m-d H:i:s')   : "";

$now   = time();
$start = $election?->startsAt ? $election->startsAt->getTimestamp() : false;
$end   = $election?->endsAt   ? $election->endsAt->getTimestamp()   : false;

$hasVoted = $votes->hasVoted($user_id, $category_id);

// Auditable tally (domain object handles ordering + winner/tie).
$tally = $votes->tally($category_id);
$candidate_name_array = array_map(static fn ($s) => $s->candidateName, $tally->standings);
$candidate_vote_array = array_map(static fn ($s) => $s->votes, $tally->standings);
$winner = $tally->winnerLabel();
$totalVotesCast = $tally->totalVotes();
?>

<div class="container py-3">
    <!-- Header Welcome Card -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 pb-2 border-bottom">
        <div>
            <h2 class="h3 fw-bold text-dark mb-1">
                <i class="bi bi-person-circle text-primary me-2"></i>Welcome, <?= e($voterName); ?>
            </h2>
            <div class="text-muted">
                Assigned Election Category: <strong class="text-dark"><?= e($category_name); ?></strong> (ID #<?= (int)$category_id; ?>)
            </div>
        </div>
        <div class="mt-2 mt-md-0">
            <?php if ($hasVoted): ?>
                <span class="badge bg-success py-2 px-3 fs-6">
                    <i class="bi bi-check-circle-fill me-1"></i> Ballot Cast
                </span>
            <?php else: ?>
                <span class="badge bg-primary py-2 px-3 fs-6">
                    <i class="bi bi-clock-history me-1"></i> Awaiting Your Vote
                </span>
            <?php endif; ?>
        </div>
    </div>

    <?php require_once("../includes/view_messages_and_errors.php"); ?>

    <?php
    if ($hasVoted) {
        if ($end !== false && $now > $end) {
            // Election finished - Winner Declaration Hero Banner
            ?>
            <div class="winner-banner mb-4 text-center">
                <div class="winner-trophy-icon mb-2">
                    <i class="bi bi-trophy-fill"></i>
                </div>
                <h3 class="fw-bold text-dark mb-1">Election Concluded - Final Results</h3>
                <div class="display-6 fw-bold text-primary my-2"><?= e($winner) ?></div>
                <p class="text-muted mb-0">
                    A total of <strong><?= (int)$totalVotesCast ?></strong> votes were cast in this category.
                </p>
            </div>

            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="card-title mb-0 fw-semibold text-primary">
                        <i class="bi bi-bar-chart-fill me-2"></i>Final Vote Count Distribution
                    </h5>
                </div>
                <div class="card-body p-3">
                    <div id="barChartContainer" style="width: 100%; min-height: 280px;">
                        <div id="barChart" style="width: 100%; height: 280px;"></div>
                    </div>
                </div>
            </div>
            <?php
        } else {
            // Voted, but polls still open
            ?>
            <div class="card shadow-sm border-0 text-center p-5 mb-4">
                <div class="mb-3 text-success">
                    <i class="bi bi-shield-fill-check display-3"></i>
                </div>
                <h3 class="fw-bold text-dark">Thank You for Voting</h3>
                <p class="text-muted col-md-8 mx-auto mb-4">
                    Your vote has been recorded successfully. Results will be displayed once the voting period ends.
                </p>
                <?php if ($end !== false): ?>
                    <div class="countdown-box col-md-8 col-lg-6 mx-auto">
                        <div class="small text-uppercase tracking-wide text-light">Polls Close In</div>
                        <div class="countdown-tiles" id="poll_close_countdown">
                            <!-- Populated by JavaScript -->
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            <?php
        }
    } else {
        // Has NOT voted yet
        if ($start !== false && $now < $start) {
            // Before voting window
            ?>
            <div class="card shadow-sm border-0 text-center p-5 mb-4">
                <div class="mb-3 text-warning">
                    <i class="bi bi-hourglass-top display-3"></i>
                </div>
                <h3 class="fw-bold text-dark">Voting Has Not Started Yet</h3>
                <p class="text-muted col-md-8 mx-auto mb-4">
                    The voting window for <strong><?= e($category_name); ?></strong> will open shortly. Please return once the countdown reaches zero.
                </p>
                <div class="countdown-box col-md-8 col-lg-6 mx-auto">
                    <div class="small text-uppercase tracking-wide text-light">Voting Opens In</div>
                    <div class="countdown-tiles" id="vote_start_countdown">
                        <!-- Populated by JavaScript -->
                    </div>
                </div>
            </div>
            <?php
        } elseif ($end !== false && $now <= $end) {
            // Voting is OPEN! Show countdown bar + candidate cards ballot
            ?>
            <div class="countdown-box mb-4">
                <div class="d-flex flex-wrap justify-content-between align-items-center">
                    <div>
                        <h5 class="fw-bold mb-0 text-white"><i class="bi bi-broadcast text-danger me-2"></i>Polls Are Live</h5>
                        <small class="text-light text-opacity-75">Cast your ballot before the remaining time expires.</small>
                    </div>
                    <div class="countdown-tiles" id="vote_end_countdown">
                        <!-- Populated by JavaScript -->
                    </div>
                </div>
            </div>

            <?php
            require_once("give_vote.php");
        } else {
            // Expired without voting
            ?>
            <div class="card shadow-sm border-0 text-center p-5 mb-4">
                <div class="mb-3 text-danger">
                    <i class="bi bi-clock-history display-3"></i>
                </div>
                <h3 class="fw-bold text-dark">Voting Window Expired</h3>
                <p class="text-muted col-md-8 mx-auto mb-0">
                    The voting period for this election ended at <strong><?= e($vote_end_time); ?></strong>. No further ballots can be accepted.
                </p>
            </div>
            <?php
        }
    }
    ?>
</div>

<script type="text/javascript">
    // Timezone synchronization: pass exact epoch milliseconds computed by server PHP
    var start_target = <?= $start !== false ? ($start * 1000) : 0 ?>;
    var end_target   = <?= $end !== false ? ($end * 1000) : 0 ?>;
    var server_time  = <?= (time() * 1000) ?>;
    var client_offset = server_time - Date.now();

    function renderTiles(containerId, targetMillis) {
        var el = document.getElementById(containerId);
        if (!el || targetMillis <= 0) return;

        var now = Date.now() + client_offset;
        var diff = Math.max(0, Math.floor((targetMillis - now) / 1000));

        var d = Math.floor(diff / 86400); diff %= 86400;
        var h = Math.floor(diff / 3600);  diff %= 3600;
        var m = Math.floor(diff / 60);    var s = Math.floor(diff % 60);

        el.innerHTML =
            '<div class="countdown-tile"><span class="val">' + d + '</span><span class="lbl">Days</span></div>' +
            '<div class="countdown-tile"><span class="val">' + (h < 10 ? '0' + h : h) + '</span><span class="lbl">Hours</span></div>' +
            '<div class="countdown-tile"><span class="val">' + (m < 10 ? '0' + m : m) + '</span><span class="lbl">Minutes</span></div>' +
            '<div class="countdown-tile"><span class="val">' + (s < 10 ? '0' + s : s) + '</span><span class="lbl">Seconds</span></div>';
    }

    function updateTimers() {
        renderTiles('vote_start_countdown', start_target);
        renderTiles('vote_end_countdown', end_target);
        renderTiles('poll_close_countdown', end_target);
    }

    updateTimers();
    setInterval(updateTimers, 1000);
</script>

<?php if ($hasVoted && $end !== false && $now > $end): ?>
<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
<script type="text/javascript">
    var candidate_name_array = <?php echo json_encode($candidate_name_array); ?>;
    var candidate_vote_array = <?php echo json_encode($candidate_vote_array); ?>;

    google.charts.load('current', { 'packages': ['bar'] });
    google.charts.setOnLoadCallback(drawBar);

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

    window.addEventListener('resize', drawBar);
</script>
<?php endif; ?>

<?php
require_once("../includes/footer.php");
?>
