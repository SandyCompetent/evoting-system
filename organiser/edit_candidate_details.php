<?php
require_once __DIR__ . "/../includes/bootstrap.php";
require_role("organiser");

$user_id   = (int) $_SESSION["user_id"];
$organiser = fetchOne("SELECT election_category_id FROM system_users WHERE user_id = ?", [$user_id]);
$category_id = (int) ($organiser["election_category_id"] ?? 0);

// Handle Edit button -> stash ref id and go to update page.
if (($editId = pressed_action("edit")) !== null) {
    csrf_verify();
    $_SESSION["candidate_refID"] = $editId;
    redirect("update_candidate_details.php");
}

// Handle Delete button (scoped to this organiser's category).
if (($delId = pressed_action("delete")) !== null) {
    csrf_verify();
    // Remove any ballots cast for this candidate first, then the candidate.
    execute("DELETE FROM vote WHERE candidate_id = ? AND category_id = ?", [$delId, $category_id]);
    $n = execute("DELETE FROM candidate WHERE id = ? AND category_id = ?", [$delId, $category_id]);
    $_SESSION[$n ? "messages" : "errors"][] = $n ? "Candidate deleted." : "Delete failed.";
    redirect($_SERVER["PHP_SELF"]);
}

require_once __DIR__ . "/nav.php";

$candidates = fetchAll("SELECT * FROM candidate WHERE category_id = ? ORDER BY full_name", [$category_id]);
?>

<div class="page-section">
	<div class="container">
		<div class="row">
			<div class="col-md-12">
				<?php require_once("../includes/view_messages_and_errors.php"); ?>
				<div class="panel panel-default">
					<div class="panel-heading">
						<div class="title">Edit Candidate Details</div>
					</div>
					<div class="panel-body">
						<form class="form-horizontal" action="<?php echo e($_SERVER['PHP_SELF']); ?>" method="post">
							<?php echo csrf_field(); ?>
							<div class="form-group">
								<table class="table">
									<thead>
										<tr>
											<th width="30%">Name</th>
											<th width="15%">Photo</th>
											<th width="15%">Symbol</th>
											<th width="15%">Timestamp</th>
											<th width="20%">Action</th>
										</tr>
									</thead>
									<tbody>
										<?php foreach ($candidates as $value): ?>
											<tr>
												<td><?php echo e($value['full_name']); ?></td>
												<td><img src="../upload/photo/<?php echo e($value['photo']); ?>" alt="Photo" style="width:100px;height:100px;object-fit:cover;"></td>
												<td><img src="../upload/symbol/<?php echo e($value['symbol']); ?>" alt="Symbol" style="width:100px;height:100px;object-fit:cover;"></td>
												<td><?php echo e($value['timestamp']); ?></td>
												<td>
													<button type="submit" class="btn btn-primary btn-sm" name="edit<?php echo (int) $value['id']; ?>">Edit</button>&emsp;
													<button type="submit" class="btn btn-danger btn-sm" name="delete<?php echo (int) $value['id']; ?>" onclick="return confirm('Delete this candidate?');">Delete</button>
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

<?php
require_once("../includes/footer.php");
?>
