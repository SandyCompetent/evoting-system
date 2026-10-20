<?php
require_once("nav.php");
$voter_refID = $_SESSION['voter_refID'];

$data = fetchData("system_users", "WHERE user_id=$voter_refID");
foreach ($data as $value) :
	$name = $value["full_name"];
	$email = $value["email"];
endforeach;


if (isset($_POST["submit"])) {
	$fname = trim($_POST["first_name"]);
	$email = trim($_POST["email"]);
	$pass = trim($_POST["password"]);
	$encPass = password_hash($pass, PASSWORD_DEFAULT);
	
	$sql = "UPDATE system_users SET full_name = '$fname', email = '$email', password = '$encPass' WHERE user_id = $voter_refID";
	if (executeInsertQuery($sql)) {
		$_SESSION["messages"][] = "Data Updation Successful";
		echo "<script>window.location='" . $_SERVER["PHP_SELF"] . "';</script>";
		exit(0);
	} else {
		$_SESSION["errors"][] = "Data Updation Failed";
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
						<div class="title">Update Voter (<?php echo $name; ?>) Details</div>
					</div>
					<div class="panel-body">
						<form class="form-horizontal" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post" enctype="multipart/form-data">
							<div class="form-group">
								<label class="control-label col-sm-3 highlight" for="reference_id">Reference ID</label>
								<div class="col-sm-8">
									<input type="text" class="form-control" id="reference_id" name="reference_id" placeholder="<?php echo $voter_refID; ?>" required disabled>
								</div>
							</div>
						
							<div class="form-group">
								<label class="control-label col-sm-3 highlight" for="first_name">Full Name</label>
								<div class="col-sm-8">
									<input type="text" class="form-control" id="first_name" name="first_name" placeholder="Enter first name" value="<?php echo $name; ?>" required>
								</div>
							</div>

							<div class="form-group">
								<label class="control-label col-sm-3 highlight" for="email">Email Address</label>
								<div class="col-sm-8">
									<input type="email" class="form-control" id="email" name="email" placeholder="Enter email address" value="<?php echo $email; ?>" required>
								</div>
							</div>
							<div class="form-group">
								<label class="control-label col-sm-3 highlight" for="password">Password</label>
								<div class="col-sm-8">
									<input type="text" class="form-control" id="password" name="password" placeholder="Ex. 12345678" required>
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