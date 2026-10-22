<?php
require_once __DIR__ . "/../includes/bootstrap.php";
require_role("organiser");

$user_id   = (int) $_SESSION["user_id"];
$organiser = fetchOne("SELECT election_category_id FROM system_users WHERE user_id = ?", [$user_id]);
$category_id = (int) ($organiser["election_category_id"] ?? 0);

if (($editId = pressed_action("edit")) !== null) {
    csrf_verify();
    $_SESSION["voter_refID"] = $editId;
    redirect("update_voter_details.php");
}

if (($delId = pressed_action("delete")) !== null) {
    csrf_verify();
    $n = execute(
        "DELETE FROM system_users WHERE user_id = ? AND role = 'voter' AND election_category_id = ?",
        [$delId, $category_id]
    );
    $_SESSION[$n ? "messages" : "errors"][] = $n ? "Voter deleted." : "Delete failed.";
    redirect($_SERVER["PHP_SELF"]);
}

require_once __DIR__ . "/nav.php";

$voters = fetchAll(
    "SELECT * FROM system_users WHERE role = 'voter' AND election_category_id = ? ORDER BY full_name",
    [$category_id]
);
?>

<div class="page-section">
	<div class="container">
		<div class="row">
			<div class="col-md-12">
				<?php require_once("../includes/view_messages_and_errors.php"); ?>
				<div class="panel panel-default">
					<div class="panel-heading">
						<div class="title">Edit Voter Details</div>
					</div>
					<div class="panel-body">
						<form class="form-horizontal" action="<?php echo e($_SERVER['PHP_SELF']); ?>" method="post">
							<?php echo csrf_field(); ?>
							<div class="form-group">
								<table class="table">
									<thead>
										<tr>
											<th width="20%">Full Name</th>
											<th width="25%">Email</th>
											<th width="15%">Voted</th>
											<th width="25%">Account Create Timestamp</th>
											<th width="15%">Action</th>
										</tr>
									</thead>
									<tbody>
										<?php foreach ($voters as $value): ?>
											<tr>
												<td><?php echo e($value['full_name']); ?></td>
												<td><?php echo e($value['email']); ?></td>
												<td><?php echo e($value['voted']); ?></td>
												<td><?php echo e($value['created_timestamp'] ?? ''); ?></td>
												<td>
													<button type="submit" class="btn btn-primary btn-sm" name="edit<?php echo (int) $value['user_id']; ?>">Edit</button>&emsp;
													<button type="submit" class="btn btn-danger btn-sm" name="delete<?php echo (int) $value['user_id']; ?>" onclick="return confirm('Delete this voter?');">Delete</button>
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
