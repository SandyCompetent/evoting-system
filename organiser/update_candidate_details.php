<?php
require_once __DIR__ . "/../includes/bootstrap.php";
require_role("organiser");

$candidate_refID = (int) ($_SESSION["candidate_refID"] ?? 0);
$user_id   = (int) $_SESSION["user_id"];
$organiser = fetchOne("SELECT election_category_id FROM system_users WHERE user_id = ?", [$user_id]);
$category_id = (int) ($organiser["election_category_id"] ?? 0);

$candidate = fetchOne(
    "SELECT * FROM candidate WHERE id = ? AND category_id = ?",
    [$candidate_refID, $category_id]
);
if (!$candidate) {
    $_SESSION["errors"][] = "Candidate not found.";
    redirect("edit_candidate_details.php");
}

if (isset($_POST["submit"])) {
    csrf_verify();
    $candidate_name = trim($_POST["candidate_name"] ?? "");
    if ($candidate_name === "") {
        $_SESSION["errors"][] = "Candidate name is required.";
        redirect($_SERVER["PHP_SELF"]);
    }
    try {
        $photo  = $candidate["photo"];
        $symbol = $candidate["symbol"];
        if (($_FILES["uploadPhoto"]["error"] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK) {
            $photo = store_uploaded_image($_FILES["uploadPhoto"], __DIR__ . "/../upload/photo");
        }
        if (($_FILES["uploadSymbol"]["error"] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK) {
            $symbol = store_uploaded_image($_FILES["uploadSymbol"], __DIR__ . "/../upload/symbol");
        }
        execute(
            "UPDATE candidate SET full_name = ?, photo = ?, symbol = ? WHERE id = ? AND category_id = ?",
            [$candidate_name, $photo, $symbol, $candidate_refID, $category_id]
        );
        $_SESSION["messages"][] = "Candidate updated successfully.";
    } catch (Throwable $ex) {
        error_log("Candidate update failed: " . $ex->getMessage());
        $_SESSION["errors"][] = "Update failed: " . $ex->getMessage();
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
						<div class="title">Update Candidate (<?php echo e($candidate['full_name']); ?>) Details</div>
					</div>
					<div class="panel-body">
						<form class="form-horizontal" action="<?php echo e($_SERVER['PHP_SELF']); ?>" method="post" enctype="multipart/form-data">
							<?php echo csrf_field(); ?>
							<div class="form-group">
								<label class="control-label col-sm-3 highlight" for="reference_id">Reference ID</label>
								<div class="col-sm-8">
									<input type="text" class="form-control" id="reference_id" value="<?php echo (int) $candidate_refID; ?>" disabled>
								</div>
							</div>
							<div class="form-group">
								<label class="control-label col-sm-3 highlight" for="candidate_name">Candidate Name</label>
								<div class="col-sm-8">
									<input type="text" class="form-control" id="candidate_name" name="candidate_name" value="<?php echo e($candidate['full_name']); ?>" required>
								</div>
							</div>
							<div class="form-group">
								<label class="control-label col-sm-3 highlight" for="uploadPhoto">Candidate Photo</label>
								<div class="col-sm-6">
									Replace image (optional): <input type="file" name="uploadPhoto" id="uploadPhoto" accept="image/*"><br />
								</div>
								<img src="../upload/photo/<?php echo e($candidate['photo']); ?>" alt="Photo" style="width:150px;height:150px;object-fit:cover;">
							</div>
							<div class="form-group">
								<label class="control-label col-sm-3 highlight" for="uploadSymbol">Candidate Symbol</label>
								<div class="col-sm-6">
									Replace image (optional): <input type="file" name="uploadSymbol" id="uploadSymbol" accept="image/*"><br />
								</div>
								<img src="../upload/symbol/<?php echo e($candidate['symbol']); ?>" alt="Symbol" style="width:150px;height:150px;object-fit:cover;">
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
