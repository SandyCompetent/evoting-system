<?php
require_once __DIR__ . "/../includes/bootstrap.php";
require_role("admin");

$generated = null;
if (isset($_POST["submit"])) {
    csrf_verify();
    $fname = trim($_POST["first_name"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $cat   = (int) ($_POST["election_category"] ?? 0);

    if ($fname === "" || !filter_var($email, FILTER_VALIDATE_EMAIL) || $cat <= 0) {
        $_SESSION["errors"][] = "Please provide a valid name, email and category.";
        redirect($_SERVER["PHP_SELF"]);
    }
    $plain = bin2hex(random_bytes(4));
    try {
        execute(
            "INSERT INTO system_users (full_name, email, password, role, election_category_id)
             VALUES (?, ?, ?, 'organiser', ?)",
            [$fname, $email, password_hash($plain, PASSWORD_DEFAULT), $cat]
        );
        $_SESSION["messages"][] = "Organiser created. Temporary password for {$email}: {$plain} (share securely).";
    } catch (PDOException $ex) {
        error_log("Organiser create failed: " . $ex->getMessage());
        $_SESSION["errors"][] = "Could not create organiser (the email may already be registered).";
    }
    redirect($_SERVER["PHP_SELF"]);
}

require_once __DIR__ . "/nav.php";
$categories = fetchAll("SELECT id, name FROM election_category ORDER BY name");
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
						<form class="form-horizontal" action="<?php echo e($_SERVER['PHP_SELF']); ?>" method="post">
							<?php echo csrf_field(); ?>
							<div class="form-group">
								<label class="control-label col-sm-3 highlight" for="first_name">Full Name</label>
								<div class="col-sm-8">
									<input type="text" class="form-control" id="first_name" name="first_name" placeholder="Enter full name" required>
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
									<input type="text" class="form-control" id="password" value="(auto-generated and shown after creation)" readonly>
								</div>
							</div>
							<div class="form-group">
								<label class="control-label col-sm-3 highlight" for="election_category">Election Category</label>
								<div class="col-sm-8">
									<select class="form-control" id="election_category" name="election_category" required>
										<option value="">Select election category</option>
										<?php foreach ($categories as $c): ?>
											<option value="<?php echo (int) $c['id']; ?>"><?php echo e($c['name']); ?></option>
										<?php endforeach; ?>
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
