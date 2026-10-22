<?php
require_once __DIR__ . "/../includes/bootstrap.php";
require_role("admin");

if (($editId = pressed_action("edit")) !== null) {
    csrf_verify();
    $_SESSION["organiser_refID"] = $editId;
    redirect("update_account_details.php");
}
if (($delId = pressed_action("delete")) !== null) {
    csrf_verify();
    $n = execute("DELETE FROM system_users WHERE user_id = ? AND role = 'organiser'", [$delId]);
    $_SESSION[$n ? "messages" : "errors"][] = $n ? "Organiser deleted." : "Delete failed.";
    redirect($_SERVER["PHP_SELF"]);
}

require_once __DIR__ . "/nav.php";
$organisers = fetchAll(
    "SELECT u.*, c.name AS category_name
       FROM system_users u
       LEFT JOIN election_category c ON c.id = u.election_category_id
      WHERE u.role = 'organiser'
      ORDER BY u.full_name"
);
?>

<div class="page-section">
	<div class="container">
		<div class="row">
			<div class="col-sm-12">
				<?php require_once("../includes/view_messages_and_errors.php"); ?>
				<div class="panel panel-default">
					<div class="panel-heading">
						<div class="title">Edit Account Details</div>
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
											<th width="15%">Category</th>
											<th width="25%">Account Create Date / Time</th>
											<th width="15%">Action</th>
										</tr>
									</thead>
									<tbody>
										<?php foreach ($organisers as $value): ?>
											<tr>
												<td><?php echo e($value['full_name']); ?></td>
												<td><?php echo e($value['email']); ?></td>
												<td><?php echo e($value['category_name'] ?? ''); ?></td>
												<td><?php echo e($value['created_timestamp'] ?? ''); ?></td>
												<td>
													<button type="submit" class="btn btn-primary btn-sm" name="edit<?php echo (int) $value['user_id']; ?>">Edit</button>&emsp;
													<button type="submit" class="btn btn-danger btn-sm" name="delete<?php echo (int) $value['user_id']; ?>" onclick="return confirm('Delete this organiser?');">Delete</button>
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
