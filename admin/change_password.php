<?php 
	require_once("nav.php");
	
	if(isset($_POST["change"]))
	{
		$user_id = trim($_SESSION["user_id"]);
		$old_password = trim($_POST["old_password"]);
		$new_password = trim($_POST["new_password"]);
		$confirm_password = trim($_POST["confirm_password"]);
		
		
		if($old_password == $new_password)
		{
			$_SESSION["errors"][] = "Old password and new password cannot be same.";
		}
		else if(strlen($_POST["new_password"]) < 8)
		{
			$_SESSION["errors"][] = "Password must be of atleast 8 characters.";
		}
		else if($new_password != $confirm_password)
		{
			$_SESSION["errors"][] = "The password and confirmation password are not matching.";
		}
		else
		{
			$password = $old_password;
			
			$user_data = fetchData("system_users", "WHERE user_id = '$user_id'");

			if($user_data && (password_verify($password, $user_data[0]["password"]) || sha1($password) == $user_data[0]["password"]))
			{
				$new_password_hash = password_hash($new_password, PASSWORD_DEFAULT);
				if(executeUpdateQuery("UPDATE system_users SET password = '$new_password_hash' WHERE user_id = '$user_id'"))
				{
					$_SESSION["messages"][] = "Changed successfully.";
				}
				else
				{
					$_SESSION["errors"][] = "Failed to change, Please try again in a few minutes.";
				}
			}
			else
			{
				$_SESSION["errors"][] = "Incorrect old password.";
			}
		}
		
		echo "<script>window.location='".$_SERVER["PHP_SELF"]."';</script>";
		exit(0);
	}
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
						<form class="form-horizontal" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post" enctype="multipart/form-data">
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