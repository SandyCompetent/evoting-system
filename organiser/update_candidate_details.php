<?php
require_once __DIR__ . "/../includes/bootstrap.php";
require_role("organiser");

use App\Repository\Pdo\PdoCandidateRepository;

$candidate_refID = (int) ($_GET["id"] ?? $_POST["id"] ?? $_SESSION["candidate_refID"] ?? 0);
if ($candidate_refID > 0) {
    $_SESSION["candidate_refID"] = $candidate_refID;
}

$user_id     = (int) $_SESSION["user_id"];
$organiser   = fetchOne("SELECT election_category_id FROM system_users WHERE user_id = ?", [$user_id]);
$category_id = (int) ($organiser["election_category_id"] ?? 0);

$candidateRepo = new PdoCandidateRepository(db());
$candidate = $candidateRepo->findById($candidate_refID, $category_id);

if (!$candidate) {
    $_SESSION["errors"][] = "Candidate not found.";
    redirect("edit_candidate_details.php");
}

if (isset($_POST["submit"])) {
    csrf_verify();
    $candidate_name = trim($_POST["candidate_name"] ?? "");
    if ($candidate_name === "") {
        $_SESSION["errors"][] = "Candidate name is required.";
        redirect($_SERVER["PHP_SELF"] . "?id=" . $candidate_refID);
    }
    try {
        $photo  = null;
        $symbol = null;
        if (($_FILES["uploadPhoto"]["error"] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK) {
            $photo = store_uploaded_image($_FILES["uploadPhoto"], __DIR__ . "/../upload/photo");
        }
        if (($_FILES["uploadSymbol"]["error"] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK) {
            $symbol = store_uploaded_image($_FILES["uploadSymbol"], __DIR__ . "/../upload/symbol");
        }
        $candidateRepo->update($candidate_refID, $candidate_name, $photo, $symbol, $category_id);
        $_SESSION["messages"][] = "Candidate details updated successfully.";
    } catch (Throwable $ex) {
        error_log("Candidate update failed: " . $ex->getMessage());
        $_SESSION["errors"][] = "Update failed: " . $ex->getMessage();
    }
    redirect($_SERVER["PHP_SELF"] . "?id=" . $candidate_refID);
}

require_once __DIR__ . "/nav.php";
?>

<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <?php require_once("../includes/view_messages_and_errors.php"); ?>

            <div class="card shadow-sm border-0">
                <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0 fw-semibold text-primary">
                        <i class="bi bi-person-gear me-2"></i>Update Candidate: <?= e($candidate->fullName); ?>
                    </h5>
                    <span class="badge bg-secondary">Candidate ID #<?= (int)$candidate_refID ?></span>
                </div>
                <div class="card-body p-4">
                    <form action="<?php echo e($_SERVER['PHP_SELF']); ?>?id=<?= (int)$candidate_refID ?>" method="post" enctype="multipart/form-data">
                        <?php echo csrf_field(); ?>
                        <input type="hidden" name="id" value="<?= (int)$candidate_refID ?>">

                        <div class="mb-3">
                            <label class="form-label highlight fw-semibold" for="candidate_name">Full Name</label>
                            <input type="text" class="form-control" id="candidate_name" name="candidate_name" value="<?php echo e($candidate->fullName); ?>" required>
                        </div>

                        <div class="row g-3 mb-4">
                            <!-- Photo -->
                            <div class="col-md-6">
                                <label class="form-label fw-semibold" for="uploadPhoto">Candidate Portrait Photo</label>
                                <input type="file" class="form-control mb-2" name="uploadPhoto" id="uploadPhoto" accept="image/*">
                                <div class="form-text mb-2">Leave blank to keep existing portrait.</div>
                                <?php if (!empty($candidate->photo)): ?>
                                    <div class="p-2 border rounded bg-light text-center">
                                        <img src="../upload/photo/<?php echo e($candidate->photo); ?>" alt="Current Photo" class="rounded border" style="max-width: 120px; max-height: 120px; object-fit: cover;">
                                        <div class="small text-muted mt-1">Current Photo</div>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <!-- Symbol -->
                            <div class="col-md-6">
                                <label class="form-label fw-semibold" for="uploadSymbol">Party / Ballot Symbol</label>
                                <input type="file" class="form-control mb-2" name="uploadSymbol" id="uploadSymbol" accept="image/*">
                                <div class="form-text mb-2">Leave blank to keep existing symbol.</div>
                                <?php if (!empty($candidate->symbol)): ?>
                                    <div class="p-2 border rounded bg-light text-center">
                                        <img src="../upload/symbol/<?php echo e($candidate->symbol); ?>" alt="Current Symbol" class="rounded border p-1 bg-white" style="max-width: 120px; max-height: 120px; object-fit: contain;">
                                        <div class="small text-muted mt-1">Current Symbol</div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center">
                            <a href="edit_candidate_details.php" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-left me-1"></i> Back to Candidates
                            </a>
                            <button type="submit" class="btn btn-primary px-4 fw-semibold" id="submit" name="submit">
                                <i class="bi bi-check2-circle me-1"></i> Update Candidate
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
require_once("../includes/footer.php");
?>
