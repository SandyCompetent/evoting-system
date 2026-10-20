<?php
require_once("includes/init.php");

if (isset($_SESSION["role"]) && ($_SESSION["role"] == "admin" || $_SESSION["role"] == "organiser")) {
	echo "<script>window.location='" . $_SESSION["role"] . "';</script>";
	exit(0);
}

if (isset($_POST["login"])) {
	$email = trim($_POST["email"]);
	$password = $_POST["password"];

	if ($data = fetchData("system_users", "WHERE email = '$email'")) {
		if (password_verify($password, $data[0]["password"]) || sha1($password) == $data[0]["password"]) {
			// If it was still a sha1 password, upgrade it to password_hash
			if (sha1($password) == $data[0]["password"]) {
				$newHash = password_hash($password, PASSWORD_DEFAULT);
				$userId = $data[0]["user_id"];
				executeUpdateQuery("UPDATE system_users SET password = '$newHash' WHERE user_id = '$userId'");
			}

			$_SESSION["user_id"] = $data[0]["user_id"];
			$_SESSION["role"] = $data[0]["role"];
			echo "<script>window.location='" . $_SESSION["role"] . "';</script>";
			exit(0);
		}
	}
	$_SESSION["errors"][] = "Invalid email/password";
	
}

if (isset($_POST["request_register"])) {
	$full_name = trim($_POST["full_name"]);
	$email = trim($_POST["email"]);
	$election_category = trim($_POST["category"]);
	$max_voter = trim($_POST["max_voter"]);
	$is_adult_voter = trim($_POST["is_adult_voter"]);
	$expire_datetime = trim($_POST["expire_timestamp"]);

	$sql = "INSERT INTO request_to_register (full_name, email, election_category, max_voter, adult_voter, expire_timestamp) VALUES ('$full_name', '$email', '$election_category', '$max_voter', '$is_adult_voter', '$expire_datetime')";
	if (executeInsertQuery($sql)) {
		$_SESSION["messages"][] = "Request Registered Successfully";
		echo "<script>window.location='" . $_SERVER["PHP_SELF"] . "';</script>";
		exit(0);
	} else {
		$_SESSION["errors"][] = "Request Registration Failed" + $_SESSION["errors"];
		
	}
}

?>
<div class="container">
	<div class="col-md-12 text-center">
		<h1>e-Voting</h1>
		<hr>
	</div>
	<div class="row">

		<div class="col-sm-6">
			<?php require_once("includes/view_messages_and_errors.php"); ?>
			<div class="panel panel-default">
				<div class="panel-heading">
					<div class="title">Register User Can Login Here !</div>
				</div>
				<div class="panel-body">
					<form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post">
						<div class="form-group">
							<label class="control-label col-sm-4 highlight" for="email">Email Address</label>
							<input type="email" class="form-control" name="email" id="email" placeholder="Enter email address" required>
						</div>
						<div class="form-group">
							<label class="control-label col-sm-4 highlight" for="password">Password</label>
							<input type="password" class="form-control" name="password" id="password" placeholder="Enter password" required>
						</div>
						<button type="submit" class="btn btn-primary btn-block" name="login">Login</button>
					</form>
				</div>
			</div>
		</div>


		<div class="col-sm-6">
			<?php require_once("includes/view_messages_and_errors.php"); ?>
			<div class="panel panel-default">
				<div class="panel-heading">
					<div class="title">For organising your elections with our system, please <button type="submit" class="btn btn-primary btn-sm" name="login" data-toggle="collapse" data-target="#register_form">Fill the Form</button></div>

				</div>
				<div id="register_form" class="panel-body collapse">
					<form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post">

						<div class="form-group">
							<label class="control-label col-sm-4 highlight" for="full_name">Full Name</label>
							<input type="text" class="form-control" name="full_name" id="full_name" placeholder="Enter your full name" required>
						</div>
						<div class="form-group">
							<label class="control-label col-sm-4 highlight" for="email">Email</label>
							<input type="email" class="form-control" name="email" id="email" placeholder="Enter email" required>
						</div>
						<div class="form-group">
							<label class="control-label col-sm-4 highlight" for="category">Election Category</label>
							<input type="text" class="form-control" name="category" id="category" placeholder="Enter election category" required>
						</div>
						
						<div class="form-group">
							<label class="control-label col-sm-4 highlight" for="expire_timestamp">Voting Expire Time</label>

							<div class="input-group date form_datetime col-md-7" data-date="2020-09-18T11:00:00Z" data-date-format="yyyy-mm-dd HH:mm:ss" data-link-field="expire_timestamp" required>
								<input class="form-control" size="16" type="text" id="expire_timestamp" name="expire_timestamp" required readonly>
								<span class="input-group-addon"><span class="glyphicon glyphicon-remove"></span></span>
								<span class="input-group-addon"><span class="glyphicon glyphicon-th"></span></span>
							</div>

						</div>

						<div class="form-group">
							<label class="control-label col-sm-4 highlight" for="max_voter">Maximum Voter</label>
							<select class="form-control" id="max_voter" name="max_voter" required>
								<option>10</option>
								<option>20</option>
								<option>30</option>
								<option>40</option>
								<option>50</option>
								<option>60</option>
								<option>70</option>
								<option>80</option>
								<option>90</option>
								<option>100</option>
							</select>
						</div>

						
						<div class="form-group">
							<label class="control-label col-sm-4 highlight" for="is_adult_voter">Are Voter 18+ ?</label>
							<div class="radio">
								<label><input type="radio" name="is_adult_voter" value="Yes">Yes</label>
								<label><input type="radio" name="is_adult_voter" value="No" checked>No</label>
							</div>
						</div>
						
						<button type="submit" class="btn btn-primary  btn-block" name="request_register">Request to Create Election System</button>

					</form>
				</div>
			</div>


		</div>
	</div>
</div>

<script type="text/javascript" src="assets/js/bootstrap-datetimepicker.js" charset="UTF-8"></script>
<script type="text/javascript" src="assets/js/locales/bootstrap-datetimepicker.fr.js" charset="UTF-8"></script>

<script type="text/javascript">
	$('.form_datetime').datetimepicker({
		//language:  'fr',
		weekStart: 1,
		todayBtn: 1,
		autoclose: 1,
		todayHighlight: 1,
		startView: 2,
		forceParse: 0,
		showMeridian: 1
	});
</script>

<?php
require_once("includes/footer.php");
?>