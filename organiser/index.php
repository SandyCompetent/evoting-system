<?php
require_once __DIR__ . "/../includes/bootstrap.php";
require_role("organiser");

$user_id   = (int) $_SESSION["user_id"];
$organiser = fetchOne("SELECT election_category_id FROM system_users WHERE user_id = ?", [$user_id]);
$categoryID = (int) ($organiser["election_category_id"] ?? 0);

if (isset($_POST["update_vote_time"])) {
    csrf_verify();
    $start = trim($_POST["start_timestamp"] ?? "");
    $end   = trim($_POST["end_timestamp"] ?? "");
    execute(
        "UPDATE election_category SET vote_start_time = ?, vote_end_time = ? WHERE id = ?",
        [$start, $end, $categoryID]
    );
    $_SESSION["messages"][] = "Voting time updated.";
    redirect($_SERVER["PHP_SELF"]);
}

require_once __DIR__ . "/nav.php";

$cat = fetchOne("SELECT * FROM election_category WHERE id = ?", [$categoryID]);
$get_vote_start_dt = $cat["vote_start_time"] ?? "";
$get_vote_end_dt   = $cat["vote_end_time"] ?? "";

// Turnout derived from the vote table (authoritative), not the denormalised flag.
$totalVoters   = (int) (fetchOne("SELECT COUNT(*) c FROM system_users WHERE role='voter' AND election_category_id = ?", [$categoryID])["c"] ?? 0);
$voterReceived = (int) (fetchOne("SELECT COUNT(DISTINCT voter_id) c FROM vote WHERE category_id = ?", [$categoryID])["c"] ?? 0);
$voterPending  = max(0, $totalVoters - $voterReceived);

// Auditable tally (highest first).
$rows = fetchAll(
    "SELECT c.full_name, COUNT(v.id) AS votes
       FROM candidate c
       LEFT JOIN vote v ON v.candidate_id = c.id AND v.category_id = c.category_id
      WHERE c.category_id = ?
      GROUP BY c.id, c.full_name
      ORDER BY votes DESC, c.full_name ASC",
    [$categoryID]
);
$candidate_name_array = array_map(static fn($r) => $r['full_name'], $rows);
$candidate_vote_array = array_map(static fn($r) => (int) $r['votes'], $rows);
$count = count($rows);

$winner = null;
if ($count > 0) {
    $top = $candidate_vote_array[0];
    $leaders = array_keys($candidate_vote_array, $top, true);
    if ($top === 0)                 $winner = "No votes cast yet";
    elseif (count($leaders) > 1)    $winner = "Tie: " . implode(", ", array_map(fn($i) => $candidate_name_array[$i], $leaders));
    else                            $winner = $candidate_name_array[0];
}
?>

<div class="page-section">
	<div class="container">
		<div class="row">
			<div class="col-sm-7">
				<?php require_once("../includes/view_messages_and_errors.php"); ?>
				<div class="panel panel-default">
					<div class="panel-heading">
						<div class="title">Vote Graph</div>
					</div>
					<div id="barChart"></div>
				</div>

				<div class="panel panel-default">
					<div class="panel-heading">
						<div class="title">Edit Voting Start and End Time</div>
					</div>
					<div class="panel-body">
						<form class="form-horizontal" action="<?php echo e($_SERVER['PHP_SELF']); ?>" method="post">
							<?php echo csrf_field(); ?>
							<div class="form-group">
								<label class="control-label col-sm-4 highlight" for="start_timestamp">Start Time</label>
								<div class="col-sm-8">
									<div class="input-group date form_datetime col-md-10" data-date-format="yyyy-mm-dd HH:mm:ss" data-link-field="start_timestamp">
										<input class="form-control" size="16" type="text" id="start_timestamp" name="start_timestamp" value="<?php echo e($get_vote_start_dt); ?>" readonly>
										<span class="input-group-addon"><span class="glyphicon glyphicon-remove"></span></span>
										<span class="input-group-addon"><span class="glyphicon glyphicon-th"></span></span>
									</div>
								</div>
							</div>
							<div class="form-group">
								<label class="control-label col-sm-4 highlight" for="end_timestamp">End Time</label>
								<div class="col-sm-8">
									<div class="input-group date form_datetime col-md-10" data-date-format="yyyy-mm-dd HH:mm:ss" data-link-field="end_timestamp">
										<input class="form-control" size="16" type="text" id="end_timestamp" name="end_timestamp" value="<?php echo e($get_vote_end_dt); ?>" readonly>
										<span class="input-group-addon"><span class="glyphicon glyphicon-remove"></span></span>
										<span class="input-group-addon"><span class="glyphicon glyphicon-th"></span></span>
									</div>
								</div>
							</div>
							<div class="form-group">
								<div class="col-sm-offset-4 col-sm-10">
									<button type="submit" class="btn btn-primary btn-sm" name="update_vote_time">Update Voting Time</button>
								</div>
							</div>
						</form>
					</div>
				</div>
			</div>
			<div class="col-sm-5">
				<?php require_once("../includes/view_messages_and_errors.php"); ?>
				<div class="panel panel-default">
					<div class="panel-heading">
						<div class="title">Leading Candidate</div>
					</div>
					<div class="panel-body">
						<h3 class="text-center">
							<?php if ($winner !== null): ?>
								<?php echo e($winner); ?>
							<?php else: ?>
								No Candidate.
								<div class="col-md-12 text-center"><br/>
									<input type="button" class="btn btn-primary" onclick="window.location='create_candidate_account.php';" value="Create Candidate">
								</div>
							<?php endif; ?>
						</h3>
						<p class="text-center text-muted"><?php echo (int) $voterReceived; ?> of <?php echo (int) $totalVoters; ?> voters have voted</p>
					</div>
				</div>

				<div class="panel panel-default">
					<div class="panel-heading">
						<div class="title">Votes (Received vs Pending)</div>
					</div>
					<div id="piechart"></div>
				</div>
			</div>
		</div>
	</div>
</div>

<script type="text/javascript" src="assets/js/bootstrap-datetimepicker.js" charset="UTF-8"></script>
<script type="text/javascript">
	$('.form_datetime').datetimepicker({ weekStart: 1, todayBtn: 1, autoclose: 1, todayHighlight: 1, startView: 2, forceParse: 0, showMeridian: 1 });
</script>

<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
<script type="text/javascript">
	var candidate_name_array = <?php echo json_encode($candidate_name_array); ?>;
	var candidate_vote_array = <?php echo json_encode($candidate_vote_array); ?>;
	google.charts.load('current', { 'packages': ['bar', 'corechart'] });
	google.charts.setOnLoadCallback(function () { drawBar(); drawPie(); });
	function drawBar() {
		var el = document.getElementById('barChart'); if (!el) return;
		var data = [['Candidate', 'Votes']];
		for (var i = 0; i < candidate_name_array.length; i++) data.push([candidate_name_array[i], candidate_vote_array[i]]);
		var chart = new google.charts.Bar(el);
		chart.draw(google.visualization.arrayToDataTable(data),
			google.charts.Bar.convertOptions({ legend: { position: 'none' }, bar: { groupWidth: "50%" } }));
	}
	function drawPie() {
		var el = document.getElementById('piechart'); if (!el) return;
		var data = google.visualization.arrayToDataTable([
			['Task', 'Votes'],
			['Vote Received', <?php echo (int) $voterReceived; ?>],
			['Vote Pending',  <?php echo (int) $voterPending; ?>],
		]);
		var chart = new google.visualization.PieChart(el);
		chart.draw(data, { pieHole: 0.5, legend: 'none', width: $('#chartparent').width(), height: $(window).height() * 0.5 });
	}
</script>

<?php
require_once("../includes/footer.php");
?>
