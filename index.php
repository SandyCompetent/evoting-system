<?php
require_once __DIR__ . "/includes/bootstrap.php";

// Already logged in? Send to the right dashboard.
if (isset($_SESSION["role"]) && in_array($_SESSION["role"], ["admin", "organiser", "voter"], true)) {
    redirect($_SESSION["role"] . "/");
}

// ---- Login ----
if (isset($_POST["login"])) {
    csrf_verify();
    $email    = trim($_POST["email"] ?? "");
    $password = (string) ($_POST["password"] ?? "");

    $user = fetchOne("SELECT * FROM system_users WHERE email = ?", [$email]);

    $valid = false;
    if ($user) {
        if (password_verify($password, $user["password"])) {
            $valid = true;
        } elseif (hash_equals((string) $user["password"], sha1($password))) {
            // Legacy SHA1 hash: verified, now transparently upgraded.
            $valid = true;
            execute(
                "UPDATE system_users SET password = ? WHERE user_id = ?",
                [password_hash($password, PASSWORD_DEFAULT), $user["user_id"]]
            );
        }
    }

    if ($valid) {
        session_regenerate_id(true);          // prevent session fixation
        $_SESSION["user_id"] = $user["user_id"];
        $_SESSION["role"]    = $user["role"];
        redirect($user["role"] . "/");
    }

    $_SESSION["errors"][] = "Invalid email/password";
    redirect($_SERVER["PHP_SELF"]);
}

// ---- Public request to register an election ----
if (isset($_POST["request_register"])) {
    csrf_verify();
    $full_name = trim($_POST["full_name"] ?? "");
    $email     = trim($_POST["email"] ?? "");
    $category  = trim($_POST["category"] ?? "");
    $max_voter = (int) ($_POST["max_voter"] ?? 0);
    $is_adult  = (($_POST["is_adult_voter"] ?? "No") === "Yes") ? "Yes" : "No";
    $expire    = trim($_POST["expire_timestamp"] ?? "");

    if ($full_name === "" || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION["errors"][] = "Please provide a valid name and email.";
        redirect($_SERVER["PHP_SELF"]);
    }

    execute(
        "INSERT INTO request_to_register
            (full_name, email, election_category, max_voter, adult_voter, expire_timestamp)
         VALUES (?, ?, ?, ?, ?, ?)",
        [$full_name, $email, $category, $max_voter, $is_adult, $expire]
    );
    $_SESSION["messages"][] = "Request Registered Successfully";
    redirect($_SERVER["PHP_SELF"]);
}

require_once __DIR__ . "/includes/header.php";
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
					<form action="<?php echo e($_SERVER['PHP_SELF']); ?>" method="post">
						<?php echo csrf_field(); ?>
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
					<div class="title">For organising your elections with our system, please <button type="button" class="btn btn-primary btn-sm" data-toggle="collapse" data-target="#register_form">Fill the Form</button></div>

				</div>
				<div id="register_form" class="panel-body collapse">
					<form action="<?php echo e($_SERVER['PHP_SELF']); ?>" method="post">
						<?php echo csrf_field(); ?>

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
