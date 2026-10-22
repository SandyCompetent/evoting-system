<?php
require_once __DIR__ . "/../includes/bootstrap.php";
require_role("admin");

$category_refID = (int) ($_SESSION["category_refID"] ?? 0);
$category = fetchOne("SELECT * FROM election_category WHERE id = ?", [$category_refID]);
if (!$category) {
    $_SESSION["errors"][] = "Category not found.";
    redirect("edit_election_category.php");
}

if (isset($_POST["submit"])) {
    csrf_verify();
    $name   = trim($_POST["category_name"] ?? "");
    $expire = trim($_POST["expire_timestamp"] ?? "");
    if ($name === "") {
        $_SESSION["errors"][] = "Category name is required.";
        redirect($_SERVER["PHP_SELF"]);
    }
    execute(
        "UPDATE election_category SET name = ?, expire_timestamp = ? WHERE id = ?",
        [$name, $expire, $category_refID]
    );
    $_SESSION["messages"][] = "Category updated successfully.";
    redirect($_SERVER["PHP_SELF"]);
}

require_once __DIR__ . "/nav.php";
?>

<div class="page-section">
	<div class="container">
		<div class="row">
			<div class="col-md-12">
				<?php require_once("../includes/view_messages_and_errors.php"); ?>
				<div class="panel panel-default">
					<div class="panel-heading">
						<div class="title">Edit Category (<?php echo e($category['name']); ?>) Details</div>
					</div>
					<div class="panel-body">
						<form class="form-horizontal" action="<?php echo e($_SERVER['PHP_SELF']); ?>" method="post">
							<?php echo csrf_field(); ?>
							<div class="form-group">
								<label class="control-label col-sm-3 highlight" for="reference_id">Reference ID</label>
								<div class="col-sm-8">
									<input type="text" class="form-control" id="reference_id" value="<?php echo (int) $category_refID; ?>" disabled>
								</div>
							</div>
							<div class="form-group">
								<label class="control-label col-sm-3 highlight" for="category_name">Category Name</label>
								<div class="col-sm-7">
									<input type="text" class="form-control" id="category_name" name="category_name" value="<?php echo e($category['name']); ?>" required>
								</div>
								<div class="col-sm-2">
									<button type="submit" class="btn btn-primary btn-sm" id="submit" name="submit">Update</button>
								</div>
							</div>
							<div class="form-group">
								<label class="control-label col-sm-3 highlight" for="expire_timestamp">Expire Time</label>
								<div class="col-sm-7">
									<div class="input-group date form_datetime col-md-7" data-date-format="yyyy-mm-dd HH:mm:ss" data-link-field="expire_timestamp">
										<input class="form-control" size="16" type="text" id="expire_timestamp" name="expire_timestamp" value="<?php echo e($category['expire_timestamp']); ?>" readonly>
										<span class="input-group-addon"><span class="glyphicon glyphicon-remove"></span></span>
										<span class="input-group-addon"><span class="glyphicon glyphicon-th"></span></span>
									</div>
								</div>
							</div>
						</form>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<script type="text/javascript" src="assets/js/bootstrap-datetimepicker.js" charset="UTF-8"></script>
<script type="text/javascript">
	$('.form_datetime').datetimepicker({ weekStart: 1, todayBtn: 1, autoclose: 1, todayHighlight: 1, startView: 2, forceParse: 0, showMeridian: 1 });
</script>

<?php
require_once("../includes/footer.php");
?>
