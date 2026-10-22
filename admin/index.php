<?php
require_once __DIR__ . "/../includes/bootstrap.php";
require_role("admin");

// Category edit -> stash id and open update page.
if (($editId = pressed_action("edit")) !== null) {
    csrf_verify();
    $_SESSION["category_refID"] = $editId;
    redirect("update_election_category.php");
}

// Category delete -> transactional cascade (fixes the legacy $category_id typo).
if (($delId = pressed_action("delete")) !== null) {
    csrf_verify();
    $pdo = db();
    try {
        $pdo->beginTransaction();
        execute("DELETE FROM vote WHERE category_id = ?", [$delId]);
        execute("DELETE FROM candidate WHERE category_id = ?", [$delId]);
        execute("DELETE FROM system_users WHERE election_category_id = ? AND role <> 'admin'", [$delId]);
        execute("DELETE FROM election_category WHERE id = ?", [$delId]);
        $pdo->commit();
        $_SESSION["messages"][] = "Category and all related data deleted.";
    } catch (PDOException $ex) {
        if ($pdo->inTransaction()) { $pdo->rollBack(); }
        error_log("Category cascade delete failed: " . $ex->getMessage());
        $_SESSION["errors"][] = "Delete failed.";
    }
    redirect($_SERVER["PHP_SELF"]);
}

require_once __DIR__ . "/nav.php";

$categoryCount     = (int) (fetchOne("SELECT COUNT(*) c FROM election_category")["c"] ?? 0);
$organisationCount = (int) (fetchOne("SELECT COUNT(*) c FROM system_users WHERE role = 'organiser'")["c"] ?? 0);
$voterCount        = (int) (fetchOne("SELECT COUNT(*) c FROM system_users WHERE role = 'voter'")["c"] ?? 0);
$pending_request   = (int) (fetchOne("SELECT COUNT(*) c FROM request_to_register WHERE ac_status = 'Pending'")["c"] ?? 0);

$categories = fetchAll("SELECT * FROM election_category ORDER BY name");
date_default_timezone_set('Asia/Kolkata');
$now = time();
?>

<div class="page-section">
	<div class="container">
		<div class="row">
			<div class="col-sm-5">
				<?php require_once("../includes/view_messages_and_errors.php"); ?>
				<div class="panel panel-default">
					<div class="panel-heading">
						<div class="title">Request for Election System</div>
					</div>
					<div class="panel-body">
						<h2 class="text-center"><?php echo (int) $pending_request; ?></h2><br/>
						<div class="col-md-12 text-center">
							<input type="button" class="btn btn-primary" onclick="window.location='request_to_register.php';" value="Go to Pending Request Page">
						</div>
					</div>
				</div>

				<div class="panel panel-default">
					<div class="panel-heading">
						<div class="title">Total Users Base</div>
					</div>
					<div id="barChart"></div>
				</div>
			</div>

			<div class="col-sm-7">
				<?php require_once("../includes/view_messages_and_errors.php"); ?>
				<div class="panel panel-default">
					<div class="panel-heading">
						<div class="title">Category Details in Short</div>
					</div>
					<div class="panel-body">
						<form class="form-horizontal" action="<?php echo e($_SERVER['PHP_SELF']); ?>" method="post">
							<?php echo csrf_field(); ?>
							<div class="form-group">
								<table class="table">
									<thead>
										<tr>
											<th width="35%">Category Name</th>
											<th width="35%">Expire In</th>
											<th width="20%">Action</th>
										</tr>
									</thead>
									<tbody>
										<?php foreach ($categories as $value):
											$days_left = floor((strtotime($value['expire_timestamp']) - $now) / 86400); ?>
											<tr>
												<td><?php echo e($value['name']); ?></td>
												<td>
													<?php if ($days_left > 0): ?>
														<strong><?php echo (int) $days_left; ?> day(s) left</strong>
													<?php else: ?>
														<strong>Expired!</strong>
													<?php endif; ?>
												</td>
												<td>
													<button type="submit" class="btn btn-primary btn-sm" name="edit<?php echo (int) $value['id']; ?>">Edit</button>&emsp;
													<button type="submit" class="btn btn-danger btn-sm" name="delete<?php echo (int) $value['id']; ?>" onclick="return confirm('Delete this category and all its data?');">Delete</button>
												</td>
											</tr>
										<?php endforeach; ?>
									</tbody>
								</table>
							</div>
						</form>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
<script type="text/javascript">
	google.charts.load('current', { 'packages': ['bar'] });
	google.charts.setOnLoadCallback(drawStuff);
	function drawStuff() {
		var data = new google.visualization.arrayToDataTable([
			['Result', 'Count'],
			["Category", <?php echo (int) $categoryCount; ?>],
			["Organiser", <?php echo (int) $organisationCount; ?>],
			["Voter", <?php echo (int) $voterCount; ?>],
		]);
		var options = { width: $('#chartparent').width() * 0.5, legend: { position: 'none' }, bar: { groupWidth: "50%" } };
		var chart = new google.charts.Bar(document.getElementById('barChart'));
		chart.draw(data, google.charts.Bar.convertOptions(options));
	};
</script>

<?php
require_once("../includes/footer.php");
?>
