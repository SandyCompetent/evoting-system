<?php
require_once __DIR__ . "/../includes/bootstrap.php";
require_role("organiser");

$user_id   = (int) $_SESSION["user_id"];
$organiser = fetchOne("SELECT election_category_id FROM system_users WHERE user_id = ?", [$user_id]);
$category_id = (int) ($organiser["election_category_id"] ?? 0);

if (isset($_POST["submit"])) {
    csrf_verify();
    $candidate_name = trim($_POST["candidate_name"] ?? "");
    if ($candidate_name === "") {
        $_SESSION["errors"][] = "Candidate name is required.";
        redirect($_SERVER["PHP_SELF"]);
    }
    try {
        $photo  = store_uploaded_image($_FILES["uploadPhoto"]  ?? [], __DIR__ . "/../upload/photo");
        $symbol = store_uploaded_image($_FILES["uploadSymbol"] ?? [], __DIR__ . "/../upload/symbol");
        // Votes now live in the `vote` table (one row per ballot), so a candidate
        // no longer needs a pre-seeded counter row. The tally is derived on read.
        execute(
            "INSERT INTO candidate (full_name, photo, symbol, category_id) VALUES (?, ?, ?, ?)",
            [$candidate_name, $photo, $symbol, $category_id]
        );
        $_SESSION["messages"][] = "Candidate added successfully.";
    } catch (Throwable $ex) {
        error_log("Candidate create failed: " . $ex->getMessage());
        $_SESSION["errors"][] = "Could not add candidate: " . $ex->getMessage();
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
						<div class="title">Add Candidate</div>
					</div>
					<div class="panel-body">
						<form class="form-horizontal" action="<?php echo e($_SERVER['PHP_SELF']); ?>" method="post" enctype="multipart/form-data">
							<?php echo csrf_field(); ?>
							<div class="form-group">
								<label class="control-label col-sm-3 highlight" for="candidate_name">Candidate Name</label>
								<div class="col-sm-8">
									<input type="text" class="form-control" id="candidate_name" name="candidate_name" placeholder="Enter candidate name" required>
								</div>
							</div>
							<div class="form-group">
								<label class="control-label col-sm-3 highlight" for="uploadPhoto">Candidate Photo</label>
								<div class="col-sm-8">
									Select photo to upload (JPG/PNG/GIF, max 2 MB):
									<input type="file" name="uploadPhoto" id="uploadPhoto" accept="image/*" required><br />
								</div>
							</div>
							<div class="form-group">
								<label class="control-label col-sm-3 highlight" for="uploadSymbol">Candidate Symbol</label>
								<div class="col-sm-8">
									Select symbol to upload (JPG/PNG/GIF, max 2 MB):
									<input type="file" name="uploadSymbol" id="uploadSymbol" accept="image/*" required><br />
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
