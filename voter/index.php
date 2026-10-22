<?php
require_once __DIR__ . "/../includes/bootstrap.php";
require_role("voter");

use App\Service\VoteServiceFactory;
use App\Exception\VoteException;

$user_id = (int) $_SESSION["user_id"];
$voter   = fetchOne("SELECT election_category_id FROM system_users WHERE user_id = ?", [$user_id]);
$category_id = (int) ($voter["election_category_id"] ?? 0);

$votes = VoteServiceFactory::create(db());

// ---- Cast a single vote ----
if (isset($_POST["submit"])) {
    csrf_verify();
    $candidate_id = (int) ($_POST["vote"] ?? 0);
    try {
        $votes->castVote($user_id, $candidate_id);
        // Denormalised convenience flag for dashboards; the vote table is authoritative.
        execute("UPDATE system_users SET voted = 'Yes' WHERE user_id = ?", [$user_id]);
        $_SESSION["messages"][] = "Your vote has been recorded. Thank you.";
    } catch (VoteException $ex) {
        // Domain errors carry a user-safe message.
        $_SESSION["errors"][] = $ex->getMessage();
    } catch (Throwable $ex) {
        error_log("Vote cast failed: " . $ex->getMessage());
        $_SESSION["errors"][] = "Vote submission failed. Please try again.";
    }
    redirect($_SERVER["PHP_SELF"]);
}

require_once __DIR__ . "/nav.php";

$cat = fetchOne("SELECT name, vote_start_time, vote_end_time FROM election_category WHERE id = ?", [$category_id]);
$category_name   = $cat["name"] ?? "";
$vote_start_time = $cat["vote_start_time"] ?? "";
$vote_end_time   = $cat["vote_end_time"] ?? "";

date_default_timezone_set('Asia/Kolkata');
$now   = time();
$start = strtotime((string) $vote_start_time);
$end   = strtotime((string) $vote_end_time);

$hasVoted = $votes->hasVoted($user_id, $category_id);

// Auditable tally (domain object handles ordering + winner/tie).
$tally = $votes->tally($category_id);
$candidate_name_array = array_map(static fn ($s) => $s->candidateName, $tally->standings);
$candidate_vote_array = array_map(static fn ($s) => $s->votes, $tally->standings);
$winner = $tally->winnerLabel();
?>

<div class="page-section">
	<div class="container">
		<?php
		if ($hasVoted) {
			if ($end !== false && $now > $end) {
				echo '<div class="col-sm-12"><div class="alert alert-info"><strong>WINNING PARTY!</strong> ' . e($winner) . '</div></div>';
				echo '<div class="col-sm-12"><div class="panel panel-default"><div class="panel-heading"><div class="title">Vote Graph</div></div><div id="barChart"></div></div></div>';
			} else {
				echo '<div class="row"><div class="alert alert-success"><strong>Submitted!</strong> Your vote is recorded; you cannot vote again. The result will be declared after the voting time expires.</div></div>';
			}
		} else {
			if ($start !== false && $now < $start) {
				echo '<div class="row"><div class="col-sm-12"><div class="counter"><h3>Voting will start in :</h3><div class="countdown" id="vote_start_countdown"></div></div></div></div>';
			} elseif ($end !== false && $now <= $end) {
				echo '<div class="row"><div class="col-sm-12"><div class="counter"><h3>Voting will end in :</h3><div class="countdown" id="vote_end_countdown"></div></div></div></div>';
				require_once("give_vote.php");
			} else {
				echo '<div class="row"><div class="alert alert-danger"><strong>Time Expired!</strong> The voting time period has expired and you did not vote.</div></div>';
			}
		}
		?>
	</div>
</div>

<script type="text/javascript">
	var start_target = new Date(<?php echo json_encode($vote_start_time); ?>).getTime();
	var end_target   = new Date(<?php echo json_encode($vote_end_time); ?>).getTime();
	var vote_start_countdown = document.getElementById('vote_start_countdown');
	var vote_end_countdown   = document.getElementById('vote_end_countdown');
	function fmt(sec) {
		var d = parseInt(sec / 86400); sec %= 86400;
		var h = parseInt(sec / 3600);  sec %= 3600;
		var m = parseInt(sec / 60);    var s = parseInt(sec % 60);
		return '<span class="days">' + d + ' <b>Days</b></span> <span class="hours">' + h +
			' <b>Hours</b></span> <span class="minutes">' + m + ' <b>Minutes</b></span> <span class="seconds">' + s + ' <b>Seconds</b></span>';
	}
	setInterval(function () {
		var now = new Date().getTime();
		if (vote_start_countdown) vote_start_countdown.innerHTML = fmt(Math.abs(now - start_target) / 1000);
		if (vote_end_countdown)   vote_end_countdown.innerHTML   = fmt(Math.abs(now - end_target) / 1000);
	}, 1000);
</script>

<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
<script type="text/javascript">
	var candidate_name_array = <?php echo json_encode($candidate_name_array); ?>;
	var candidate_vote_array = <?php echo json_encode($candidate_vote_array); ?>;
	google.charts.load('current', { 'packages': ['bar'] });
	google.charts.setOnLoadCallback(function () {
		var el = document.getElementById('barChart');
		if (!el) return;
		var data = [['Candidate', 'Votes']];
		for (var i = 0; i < candidate_name_array.length; i++) data.push([candidate_name_array[i], candidate_vote_array[i]]);
		var chart = new google.charts.Bar(el);
		chart.draw(google.visualization.arrayToDataTable(data),
			google.charts.Bar.convertOptions({ legend: { position: 'none' }, bar: { groupWidth: "50%" } }));
	});
</script>

<style>
	.counter { background:#2C2C2C; box-shadow: inset 0 0 5px #000; min-height:150px; text-align:center; }
	.counter h3 { color:#E5E5E5; font-size:14px; font-weight:lighter; letter-spacing:1px; padding-top:20px; margin-bottom:30px; }
	.countdown { color:#FFF; }
	.countdown span { color:#E5E5E5; font-size:26px; margin-left:20px; margin-right:20px; text-align:center; }
	.radio { font-weight:bold; }
</style>

<?php
require_once("../includes/footer.php");
?>
