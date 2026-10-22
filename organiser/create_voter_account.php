<?php
require_once __DIR__ . "/../includes/bootstrap.php";

// Guard before any output so we can redirect safely.
if (($_SESSION["role"] ?? "") !== "organiser") {
    redirect("../index.php");
}

$user_id  = (int) $_SESSION["user_id"];
$organiser = fetchOne("SELECT election_category_id FROM system_users WHERE user_id = ?", [$user_id]);
$category_id = (int) ($organiser["election_category_id"] ?? 0);

// Collects temporary passwords generated this request, shown once to the organiser.
$created_credentials = [];

// ---- Manual single-voter creation ----
if (isset($_POST["submit"])) {
    csrf_verify();
    $fname = trim($_POST["first_name"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $dob   = trim($_POST["dob"] ?? "");

    if ($fname === "" || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION["errors"][] = "Please provide a valid name and email.";
        redirect($_SERVER["PHP_SELF"]);
    }

    $plain   = bin2hex(random_bytes(4));                 // unique temporary password
    $encPass = password_hash($plain, PASSWORD_DEFAULT);

    try {
        execute(
            "INSERT INTO system_users
                (full_name, date_of_birth, email, password, role, election_category_id, voted)
             VALUES (?, ?, ?, ?, 'voter', ?, 'No')",
            [$fname, $dob, $email, $encPass, $category_id]
        );
        $_SESSION["messages"][] = "Voter '" . $fname . "' created. Temporary password: " . $plain
            . " (share it securely; the voter should change it on first login).";
    } catch (PDOException $e) {
        error_log("Voter insert failed: " . $e->getMessage());
        $_SESSION["errors"][] = "Could not create voter (the email may already be registered).";
    }
    redirect($_SERVER["PHP_SELF"]);
}

// ---- Bulk CSV import ----
if (isset($_POST["upload_csv"])) {
    csrf_verify();

    $tmp  = $_FILES["csv_file"]["tmp_name"] ?? "";
    $name = $_FILES["csv_file"]["name"] ?? "";
    $ok   = ($_FILES["csv_file"]["error"] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK;

    if (!$ok || !is_uploaded_file($tmp)) {
        $_SESSION["errors"][] = "File upload failed. Please try again.";
        redirect($_SERVER["PHP_SELF"]);
    }
    if (strtolower(pathinfo($name, PATHINFO_EXTENSION)) !== "csv") {
        $_SESSION["errors"][] = "Please upload a .csv file only.";
        redirect($_SERVER["PHP_SELF"]);
    }

    // Read directly from the temp upload; never move an uploaded file into a
    // web-served directory.
    $handle = fopen($tmp, "r");
    if ($handle === false) {
        $_SESSION["errors"][] = "Could not read the uploaded file.";
        redirect($_SERVER["PHP_SELF"]);
    }

    $pdo = db();
    $pdo->beginTransaction();
    $insert = $pdo->prepare(
        "INSERT INTO system_users
            (full_name, email, date_of_birth, password, role, election_category_id, voted)
         VALUES (?, ?, ?, ?, 'voter', ?, 'No')"
    );

    $rowNum = 0;
    $imported = 0;
    $skipped = 0;
    try {
        while (($cols = fgetcsv($handle, 10000, ",")) !== false) {
            $rowNum++;
            // Skip an optional header row.
            if ($rowNum === 1 && stripos((string) ($cols[0] ?? ""), "name") !== false) {
                continue;
            }
            $full  = sanitize_csv_cell(trim((string) ($cols[0] ?? "")));
            $email = trim((string) ($cols[1] ?? ""));
            $dob   = trim((string) ($cols[2] ?? ""));

            if ($full === "" || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $skipped++;
                continue;
            }

            $plain   = bin2hex(random_bytes(4));
            $insert->execute([
                $full, $email, $dob, password_hash($plain, PASSWORD_DEFAULT), $category_id,
            ]);
            $created_credentials[] = $email . " -> " . $plain;
            $imported++;
        }
        $pdo->commit();
    } catch (PDOException $e) {
        $pdo->rollBack();
        error_log("CSV import failed: " . $e->getMessage());
        $_SESSION["errors"][] = "Import aborted on row {$rowNum}; no voters were added.";
        fclose($handle);
        redirect($_SERVER["PHP_SELF"]);
    }
    fclose($handle);

    $_SESSION["messages"][] = "Imported {$imported} voter(s); skipped {$skipped} invalid row(s).";
    // Stash generated credentials to show once on the next render.
    $_SESSION["csv_credentials"] = $created_credentials;
    redirect($_SERVER["PHP_SELF"]);
}

require_once __DIR__ . "/nav.php";

// Pull any one-time credentials produced by a just-finished CSV import.
$csv_credentials = $_SESSION["csv_credentials"] ?? [];
unset($_SESSION["csv_credentials"]);
?>

<div class="page-section">
	<div class="container">
		<div class="row">
			<div class="col-md-12">
				<?php require_once("../includes/view_messages_and_errors.php"); ?>

				<?php if (!empty($csv_credentials)): ?>
					<div class="alert alert-warning">
						<strong>Temporary passwords (shown once):</strong>
						<ul>
							<?php foreach ($csv_credentials as $line): ?>
								<li><?php echo e($line); ?></li>
							<?php endforeach; ?>
						</ul>
					</div>
				<?php endif; ?>

				<div class="panel panel-default">
					<div class="panel-heading">
						<div class="title">Create New Voter (Manual Entry)</div>
					</div>
					<div class="panel-body">
						<form class="form-horizontal" action="<?php echo e($_SERVER['PHP_SELF']); ?>" method="post" enctype="multipart/form-data">
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
						<form class="form-horizontal" action="<?php echo e($_SERVER['PHP_SELF']); ?>" method="post" enctype="multipart/form-data">
							<?php echo csrf_field(); ?>

							<div class="form-group">
								<label class="control-label col-sm-3 highlight" for="csv_file">Import CSV file</label>
								<div class="col-sm-8">
									<input type="file" class="form-control" id="csv_file" name="csv_file" accept=".csv" required>
								</div>
							</div>

							<div class="form-group">
								<label class="control-label col-sm-3 highlight">CSV Template</label>
								<div class="col-sm-8">
									<img src="assets/template.png" alt="CSV template: full_name, email, date_of_birth">
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
