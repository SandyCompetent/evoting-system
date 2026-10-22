<?php
require_once __DIR__ . "/../includes/bootstrap.php";
require_role("admin");

if (($editId = pressed_action("edit")) !== null) {
    csrf_verify();
    $_SESSION["category_refID"] = $editId;
    redirect("update_election_category.php");
}
if (($delId = pressed_action("delete")) !== null) {
    csrf_verify();
    $n = execute("DELETE FROM election_category WHERE id = ?", [$delId]);
    $_SESSION[$n ? "messages" : "errors"][] = $n ? "Category deleted." : "Delete failed.";
    redirect($_SERVER["PHP_SELF"]);
}

require_once __DIR__ . "/nav.php";
$categories = fetchAll("SELECT * FROM election_category ORDER BY name");
?>

<div class="page-section">
	<div class="container">
		<div class="row">
			<div class="col-md-12">
				<?php require_once("../includes/view_messages_and_errors.php"); ?>
				<div class="panel panel-default">
					<div class="panel-heading">
						<div class="title">Edit Election Category</div>
					</div>
					<div class="panel-body">
						<form class="form-horizontal" action="<?php echo e($_SERVER['PHP_SELF']); ?>" method="post">
							<?php echo csrf_field(); ?>
							<div class="form-group">
								<table class="table">
									<thead>
										<tr>
											<th width="30%">Category Name</th>
											<th width="20%">Create Timestamp</th>
											<th width="20%">Expire Timestamp</th>
											<th width="20%">Action</th>
										</tr>
									</thead>
									<tbody>
										<?php foreach ($categories as $value): ?>
											<tr>
												<td><?php echo e($value['name']); ?></td>
												<td><?php echo e($value['timestamp'] ?? ''); ?></td>
												<td><?php echo e($value['expire_timestamp'] ?? ''); ?></td>
												<td>
													<button type="submit" class="btn btn-primary btn-sm" name="edit<?php echo (int) $value['id']; ?>">Edit</button>&emsp;
													<button type="submit" class="btn btn-danger btn-sm" name="delete<?php echo (int) $value['id']; ?>" onclick="return confirm('Delete this category?');">Delete</button>
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
