<?php
require_once __DIR__ . "/../includes/bootstrap.php";
require_role("admin");

$organiser_refID = (int) ($_SESSION["organiser_refID"] ?? 0);
$organiser = fetchOne(
    "SELECT * FROM system_users WHERE user_id = ? AND role = 'organiser'",
    [$organiser_refID]
);
if (!$organiser) {
    $_SESSION["errors"][] = "Organiser not found.";
    redirect("edit_account_details.php");
}

if (isset($_POST["submit"])) {
    csrf_verify();
    $fname = trim($_POST["first_name"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $pass  = (string) ($_POST["pass"] ?? "");
    $cat   = (int) ($_POST["election_category"] ?? 0);

    if ($fname === "" || !filter_var($email, FILTER_VALIDATE_EMAIL) || $cat <= 0) {
        $_SESSION["errors"][] = "Please provide a valid name, email and category.";
        redirect($_SERVER["PHP_SELF"]);
    }
    try {
        if ($pass !== "") {
            if (strlen($pass) < 8) {
                $_SESSION["errors"][] = "Password must be at least 8 characters.";
                redirect($_SERVER["PHP_SELF"]);
            }
            execute(
                "UPDATE system_users SET full_name = ?, email = ?, password = ?, election_category_id = ? WHERE user_id = ?",
                [$fname, $email, password_hash($pass, PASSWORD_DEFAULT), $cat, $organiser_refID]
            );
        } else {
            execute(
                "UPDATE system_users SET full_name = ?, email = ?, election_category_id = ? WHERE user_id = ?",
                [$fname, $email, $cat, $organiser_refID]
            );
        }
        $_SESSION["messages"][] = "Organiser updated successfully.";
    } catch (PDOException $ex) {
        error_log("Organiser update failed: " . $ex->getMessage());
        $_SESSION["errors"][] = "Update failed (the email may already be in use).";
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
						<div class="title">Edit Organiser (<?php echo e($organiser['full_name']); ?>) Details</div>
					</div>
					<div class="panel-body">
						<form class="form-horizontal" action="<?php echo e($_SERVER['PHP_SELF']); ?>" method="post">
							<?php echo csrf_field(); ?>
							<div class="form-group">
								<label class="control-label col-sm-3 highlight" for="reference_id">Reference ID</label>
								<div class="col-sm-8">
									<input type="text" class="form-control" id="reference_id" value="<?php echo (int) $organiser_refID; ?>" disabled>
								</div>
							</div>
							<div class="form-group">
								<label class="control-label col-sm-3 highlight" for="first_name">Full Name</label>
								<div class="col-sm-8">
									<input type="text" class="form-control" id="first_name" name="first_name" value="<?php echo e($organiser['full_name']); ?>" required>
								</div>
							</div>
							<div class="form-group">
								<label class="control-label col-sm-3 highlight" for="email">Email Address</label>
								<div class="col-sm-8">
									<input type="email" class="form-control" id="email" name="email" value="<?php echo e($organiser['email']); ?>" required>
								</div>
							</div>
							<div class="form-group">
								<label class="control-label col-sm-3 highlight" for="password">New Password</label>
								<div class="col-sm-8">
									<input type="password" class="form-control" id="password" name="pass" placeholder="Leave blank to keep current password">
								</div>
							</div>
							<div class="form-group">
								<label class="control-label col-sm-3 highlight" for="election_category">Election Category</label>
								<div class="col-sm-8">
									<select class="form-control" id="election_category" name="election_category" required>
										<option value="">Select election category</option>
										<?php foreach ($categories as $c): ?>
											<option value="<?php echo (int) $c['id']; ?>" <?php echo ((int) $c['id'] === (int) $organiser['election_category_id']) ? 'selected' : ''; ?>>
												<?php echo e($c['name']); ?>
											</option>
										<?php endforeach; ?>
									</select>
								</div>
							</div>
							<div class="form-group">
								<div class="col-sm-offset-3 col-sm-10">
									<button type="submit" class="btn btn-primary btn-sm" id="submit" name="submit">Update</button>
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
