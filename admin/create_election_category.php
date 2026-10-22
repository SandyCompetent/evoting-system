<?php
require_once __DIR__ . "/../includes/bootstrap.php";
require_role("admin");

if (isset($_POST["submit"])) {
    csrf_verify();
    $category = trim($_POST["category_name"] ?? "");
    $expire   = trim($_POST["expire_timestamp"] ?? "");
    if ($category === "") {
        $_SESSION["errors"][] = "Category name is required.";
        redirect($_SERVER["PHP_SELF"]);
    }
    try {
        execute("INSERT INTO election_category (name, expire_timestamp) VALUES (?, ?)", [$category, $expire]);
        $_SESSION["messages"][] = "Category created successfully.";
    } catch (PDOException $ex) {
        error_log("Category create failed: " . $ex->getMessage());
        $_SESSION["errors"][] = "Could not create category.";
    }
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
						<div class="title">Create Election Category</div>
					</div>
					<div class="panel-body">
						<form class="form-horizontal" action="<?php echo e($_SERVER['PHP_SELF']); ?>" method="post">
							<?php echo csrf_field(); ?>
							<div class="form-group">
								<label class="control-label col-sm-3 highlight" for="category_name">Category Name</label>
								<div class="col-sm-7">
									<input type="text" class="form-control" id="category_name" name="category_name" placeholder="Enter category name" required>
								</div>
								<div class="col-sm-2">
									<button type="submit" class="btn btn-primary btn-sm" id="submit" name="submit">Create</button>
								</div>
							</div>
							<div class="form-group">
								<label class="control-label col-sm-3 highlight" for="expire_timestamp">Expire Time</label>
								<div class="col-sm-7">
									<div class="input-group date form_datetime col-md-7" data-date-format="yyyy-mm-dd HH:mm:ss" data-link-field="expire_timestamp">
										<input class="form-control" size="16" type="text" id="expire_timestamp" name="expire_timestamp" readonly>
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
