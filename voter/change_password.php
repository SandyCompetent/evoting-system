<?php
require_once __DIR__ . "/../includes/bootstrap.php";
require_role("voter");

if (isset($_POST["change"])) {
    csrf_verify();
    $user_id = (int) $_SESSION["user_id"];
    $old = (string) ($_POST["old_password"] ?? "");
    $new = (string) ($_POST["new_password"] ?? "");
    $confirm = (string) ($_POST["confirm_password"] ?? "");

    if ($old === "" || $new === "" || $confirm === "") {
        $_SESSION["errors"][] = "Please enter all mandatory fields.";
    } elseif ($old === $new) {
        $_SESSION["errors"][] = "Old password and new password cannot be same.";
    } elseif ($new !== $confirm) {
        $_SESSION["errors"][] = "The password and confirmation password are not matching.";
    } elseif (strlen($new) < 8) {
        $_SESSION["errors"][] = "Password must be at least 8 characters.";
    } else {
        $user = fetchOne("SELECT password FROM system_users WHERE user_id = ?", [$user_id]);
        $ok = $user && (
            password_verify($old, $user["password"]) ||
            hash_equals((string) $user["password"], sha1($old))
        );
        if ($ok) {
            execute(
                "UPDATE system_users SET password = ? WHERE user_id = ?",
                [password_hash($new, PASSWORD_DEFAULT), $user_id]
            );
            $_SESSION["messages"][] = "Changed successfully.";
        } else {
            $_SESSION["errors"][] = "Incorrect old password.";
        }
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
						<div class="title">Change Password</div>
					</div>
					<div class="panel-body">
						<form class="form-horizontal" action="<?php echo e($_SERVER['PHP_SELF']); ?>" method="post">
							<?php echo csrf_field(); ?>
							<div class="form-group">
								<label class="control-label col-sm-3 highlight" for="old_password">Old Password</label>
								<div class="col-sm-8">
									<input type="password" class="form-control" id="old_password" name="old_password" placeholder="Enter old password" required>
								</div>
							</div>
							<div class="form-group">
								<label class="control-label col-sm-3 highlight" for="new_password">New Password</label>
								<div class="col-sm-8">
									<input type="password" class="form-control" id="new_password" name="new_password" placeholder="Enter new password" required>
								</div>
							</div>
							<div class="form-group">
								<label class="control-label col-sm-3 highlight" for="confirm_password">Confirm Password</label>
								<div class="col-sm-8">
									<input type="password" class="form-control" id="confirm_password" name="confirm_password" placeholder="Enter confirm password" required>
								</div>
							</div>
							<div class="form-group">
								<div class="col-sm-offset-3 col-sm-10">
									<button type="submit" class="btn btn-primary btn-sm" id="change" name="change">Change</button>
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
