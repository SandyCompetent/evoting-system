<?php
require_once __DIR__ . "/../includes/bootstrap.php";
require_role("organiser");

use App\Repository\Pdo\PdoCandidateRepository;

$user_id     = (int) $_SESSION["user_id"];
$organiser   = fetchOne("SELECT election_category_id FROM system_users WHERE user_id = ?", [$user_id]);
$category_id = (int) ($organiser["election_category_id"] ?? 0);

$candidateRepo = new PdoCandidateRepository(db());

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

        $candidateRepo->create($candidate_name, $photo, $symbol, $category_id);
        $_SESSION["messages"][] = "Candidate '{$candidate_name}' added successfully.";
    } catch (Throwable $ex) {
        error_log("Candidate create failed: " . $ex->getMessage());
        $_SESSION["errors"][] = "Could not add candidate: " . $ex->getMessage();
    }
    redirect($_SERVER["PHP_SELF"]);
}

require_once __DIR__ . "/nav.php";
?>

<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <?php require_once("../includes/view-messages-and-errors.php"); ?>

            <div class="card shadow-sm border-0">
                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="card-title mb-0 fw-semibold text-primary">
                        <i class="bi bi-person-plus-fill me-2"></i>Add Election Candidate
                    </h5>
                </div>
                <div class="card-body p-4">
                    <form action="<?php echo e($_SERVER['PHP_SELF']); ?>" method="post" enctype="multipart/form-data">
                        <?php echo csrf_field(); ?>

                        <div class="mb-3">
                            <label class="form-label highlight fw-semibold" for="candidate_name">Full Name</label>
                            <input type="text" class="form-control" id="candidate_name" name="candidate_name" placeholder="Enter candidate's full legal name" required>
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label highlight fw-semibold" for="uploadPhoto">Candidate Portrait Photo</label>
                                <input type="file" class="form-control" name="uploadPhoto" id="uploadPhoto" accept="image/*" required>
                                <div class="form-text">JPG, PNG, or GIF up to 2 MB. Square portrait recommended.</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label highlight fw-semibold" for="uploadSymbol">Party / Candidate Symbol</label>
                                <input type="file" class="form-control" name="uploadSymbol" id="uploadSymbol" accept="image/*" required>
                                <div class="form-text">Emblem or logo representing candidate on the voter ballot.</div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="edit-candidate-details.php" class="btn btn-outline-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary px-4 fw-semibold" id="submit" name="submit">
                                <i class="bi bi-plus-circle me-1"></i> Add Candidate
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
