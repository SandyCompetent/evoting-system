<?php
require_once("nav.php");

$user_id = $_SESSION['user_id'];

$data = fetchData("system_users", "WHERE user_id=$user_id");
foreach ($data as $value) :
	$category_id = $value["election_category_id"];
endforeach;

if (isset($_POST["submit"])) {
	$fname = trim($_POST["first_name"]);
	$email = trim($_POST["email"]);
	$dob = trim($_POST["dob"]);
	$encPass = password_hash("12345678", PASSWORD_DEFAULT);
	$role = "voter";
	$voted = "No";

	$sql = "INSERT INTO system_users(user_id, full_name, date_of_birth,email,password,role,election_category_id, voted) VALUES('','$fname','$dob','$email','$encPass','$role', '$category_id', '$voted')";
	if (executeInsertQuery($sql)) {
		$_SESSION["messages"][] = "Data Inserted Successfully";
		echo "<script>window.location='" . $_SERVER["PHP_SELF"] . "';</script>";
		exit(0);
	} else {
		$_SESSION["errors"][] = "Data Insertion Failed";
		echo "<script>window.location='" . $_SERVER["PHP_SELF"] . "';</script>";
		exit(0);
	}
}

if (isset($_POST["upload_csv"])) {

	$filename = $_FILES["csv_file"]["name"];
	$file_temp_name = $_FILES["csv_file"]["tmp_name"];
	$file_folder = "../upload/csv/" . $filename;

	
	$encCsvPass = password_hash("12345678", PASSWORD_DEFAULT);


	$ext = substr($filename, strrpos($filename, "."), (strlen($filename) - strrpos($filename, ".")));
	//we check,file must be have csv extention
	if ($ext == ".csv") {

		if (move_uploaded_file($file_temp_name, $file_folder)) {
			$file = fopen($file_folder, "r");
			while (($emapData = fgetcsv($file, 10000, ",")) !== FALSE) {
				
				$sql = "INSERT into system_users(full_name, email, date_of_birth, password, election_category_id) values('$emapData[0]','$emapData[1]', '$emapData[2]', '$encCsvPass', '$category_id')";
				if (executeInsertQuery($sql)) {
					
				} else {
					$_SESSION["errors"][] = "Data Insertion Failed";
					echo "<script>window.location='" . $_SERVER["PHP_SELF"] . "';</script>";
					exit(0);
				}
			}
			fclose($file);
			$_SESSION["messages"][] = "Data Inserted Successfully";
				echo "<script>window.location='" . $_SERVER["PHP_SELF"] . "';</script>";
				exit(0);
		} else {
			$_SESSION["errors"][] = "Image Move Failed";
		}
	} else {
		echo "Error: Please Upload only CSV File";
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
						<div class="title">Create New Voter (Manual Entry)</div>
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
									<input type="text" class="form-control" id="password" name="password" value="12345678" disabled>
								</div>
							</div>

							<div class="form-group">
								<label class="control-label col-sm-3 highlight" for="dob">Date of Birth</label>
								<div class="col-sm-7">
									<div class="input-group date form_datetime col-md-7" data-date="2020-09-20" data-date-format="yyyy-mm-dd" data-link-field="dob">
										<input class="form-control" size="16" type="text" id="dob" name="dob" readonly>
										<span class="input-group-addon"><span class="glyphicon glyphicon-remove"></span></span>
										<span class="input-group-addon"><span class="glyphicon glyphicon-th"></span></span>
									</div>
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
				<div class="panel panel-default">
					<div class="panel-heading">
						<div class="title">Create New Voter (Upload CSV File)</div>
					</div>
					<div class="panel-body">
						<form class="form-horizontal" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post" enctype="multipart/form-data">

							<div class="form-group">
								<label class="control-label col-sm-3 highlight" for="csv_file">Import CSV file</label>
								<div class="col-sm-8">
									<input type="file" class="form-control" id="csv_file" name="csv_file" required>
								</div>
							</div>

							<div class="form-group">
								<label class="control-label col-sm-3 highlight" for="csv_file">CSV Template</label>
								<div class="col-sm-8">
									<img src="assets/template.png">
								</div>
							</div>

							<div class="form-group">
								<div class="col-sm-offset-3 col-sm-10">
									<button type="submit" class="btn btn-primary btn-sm" id="upload_csv" name="upload_csv">Upload</button>
								</div>
							</div>
							
						</form>
					</div>
				</div>
				<br/><br/><br/>
			</div>
		</div>
	</div>
</div>

<script type="text/javascript" src="assets/js/bootstrap-datetimepicker.js" charset="UTF-8"></script>


<script type="text/javascript">
	$('.form_datetime').datetimepicker({
		format: 'yyyy-mm-dd',
		weekStart: 1,
		todayBtn: 1,
		autoclose: 1,
		todayHighlight: 1,
		startView: 2,
		forceParse: 0,
	});
</script>

<?php
require_once("../includes/footer.php");
?>