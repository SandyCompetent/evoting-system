<?php
require_once __DIR__ . "/../includes/bootstrap.php";
require_role("organiser");

$voter_refID = (int) ($_SESSION["voter_refID"] ?? 0);
$user_id   = (int) $_SESSION["user_id"];
$organiser = fetchOne("SELECT election_category_id FROM system_users WHERE user_id = ?", [$user_id]);
$category_id = (int) ($organiser["election_category_id"] ?? 0);

$voter = fetchOne(
    "SELECT * FROM system_users WHERE user_id = ? AND role = 'voter' AND election_category_id = ?",
    [$voter_refID, $category_id]
);
if (!$voter) {
    $_SESSION["errors"][] = "Voter not found.";
    redirect("edit_voter_details.php");
}

if (isset($_POST["submit"])) {
    csrf_verify();
    $fname = trim($_POST["first_name"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $pass  = (string) ($_POST["password"] ?? "");

    if ($fname === "" || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION["errors"][] = "Please provide a valid name and email.";
        redirect($_SERVER["PHP_SELF"]);
    }

    try {
        if ($pass !== "") {
            if (strlen($pass) < 8) {
                $_SESSION["errors"][] = "Password must be at least 8 characters.";
                redirect($_SERVER["PHP_SELF"]);
            }
            execute(
                "UPDATE system_users SET full_name = ?, email = ?, password = ? WHERE user_id = ? AND election_category_id = ?",
                [$fname, $email, password_hash($pass, PASSWORD_DEFAULT), $voter_refID, $category_id]
            );
        } else {
            execute(
                "UPDATE system_users SET full_name = ?, email = ? WHERE user_id = ? AND election_category_id = ?",
                [$fname, $email, $voter_refID, $category_id]
            );
        }
        $_SESSION["messages"][] = "Voter updated successfully.";
    } catch (PDOException $ex) {
        error_log("Voter update failed: " . $ex->getMessage());
        $_SESSION["errors"][] = "Update failed (the email may already be in use).";
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
						<div class="title">Update Voter (<?php echo e($voter['full_name']); ?>) Details</div>
					</div>
					<div class="panel-body">
						<form class="form-horizontal" action="<?php echo e($_SERVER['PHP_SELF']); ?>" method="post">
							<?php echo csrf_field(); ?>
							<div class="form-group">
								<label class="control-label col-sm-3 highlight" for="reference_id">Reference ID</label>
								<div class="col-sm-8">
									<input type="text" class="form-control" id="reference_id" value="<?php echo (int) $voter_refID; ?>" disabled>
								</div>
							</div>
							<div class="form-group">
								<label class="control-label col-sm-3 highlight" for="first_name">Full Name</label>
								<div class="col-sm-8">
									<input type="text" class="form-control" id="first_name" name="first_name" value="<?php echo e($voter['full_name']); ?>" required>
								</div>
							</div>
							<div class="form-group">
								<label class="control-label col-sm-3 highlight" for="email">Email Address</label>
								<div class="col-sm-8">
									<input type="email" class="form-control" id="email" name="email" value="<?php echo e($voter['email']); ?>" required>
								</div>
							</div>
							<div class="form-group">
								<label class="control-label col-sm-3 highlight" for="password">New Password</label>
								<div class="col-sm-8">
									<input type="password" class="form-control" id="password" name="password" placeholder="Leave blank to keep current password">
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
