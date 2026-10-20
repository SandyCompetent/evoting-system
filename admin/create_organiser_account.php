<?php
require_once("nav.php");

if (isset($_POST["submit"])) {
	$fname = trim($_POST["first_name"]);
	$email = trim($_POST["email"]);
	$pass = trim($_POST["pass"]);
	$encPass = password_hash($pass, PASSWORD_DEFAULT);
	$role = "organiser";
	$cat = trim($_POST["election_category"]);


	$sql = "INSERT INTO system_users(user_id,full_name,email,password,role,election_category_id) VALUES('','$fname','$email','$encPass','$role','$cat')";
	if (executeInsertQuery($sql)) {
		$_SESSION["messages"][] = "added";
		echo "<script>window.location='" . $_SERVER["PHP_SELF"] . "';</script>";
		exit(0);
	} else {
		$_SESSION["errors"][] = "falied";
		echo "<script>window.location='" . $_SERVER["PHP_SELF"] . "';</script>";
		exit(0);
	}
}
?>


<div class="page-section">
	<div class="container">
		<div class="row">
			<div class="col-md-12">
				<?php require_once("../includes/view_messages_and_errors.php"); ?>
				<div class="panel panel-default">
					<div class="panel-heading">
						<div class="title">Create Organiser Account</div>
					</div>
					<div class="panel-body">
						<form class="form-horizontal" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post" enctype="multipart/form-data">
							<div class="form-group">
								<label class="control-label col-sm-3 highlight" for="first_name">Full Name</label>
								<div class="col-sm-8">
									<input type="text" class="form-control" id="first_name" name="first_name" placeholder="Enter first name" required>
								</div>
							</div>


							<div class="form-group">
								<label class="control-label col-sm-3 highlight" for="email">Email Address</label>
								<div class="col-sm-8">
									<input type="email" class="form-control" id="email" name="email" placeholder="Enter email address" required>
								</div>
							</div>
							<div class="form-group">
								<label class="control-label col-sm-3 highlight" for="password">Password</label>
								<div class="col-sm-8">
									<input type="text" class="form-control" id="password" name="pass" value="12345678" required>
								</div>
							</div>


							<div class="form-group">
								<label class="control-label col-sm-3 highlight" for="election_category">Election Category</label>
								<div class="col-sm-8">
									<select class="form-control" id="election_category" name="election_category" required>
										<option value="">Select election category</option>
										<?php if ($data = fetchData("election_category", "")) : ?>
											<?php foreach ($data as $value) : ?>
												<option value="<?php echo $value["id"]; ?>"><?php echo $value["name"]; ?></option>
											<?php endforeach; ?>
										<?php endif; ?>
									</select>
								</div>
							</div>
							<div class="form-group">
								<div class="col-sm-offset-3 col-sm-10">
									<button type="submit" class="btn btn-primary btn-sm" id="submit" name="submit">Create</button>
								</div>
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