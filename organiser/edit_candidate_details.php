<?php
require_once __DIR__ . "/../includes/bootstrap.php";
require_role("organiser");

use App\Exception\VoteException;
use App\Repository\Pdo\PdoCandidateRepository;

$user_id   = (int) $_SESSION["user_id"];
$organiser = fetchOne("SELECT election_category_id FROM system_users WHERE user_id = ?", [$user_id]);
$category_id = (int) ($organiser["election_category_id"] ?? 0);

$candidateRepo = new PdoCandidateRepository(db());

// Handle Edit button (legacy POST or redirect)
if (($editId = pressed_action("edit")) !== null) {
    csrf_verify();
    $_SESSION["candidate_refID"] = $editId;
    redirect("update_candidate_details.php?id=" . $editId);
}

// Handle Delete button (scoped to this organiser's category with transaction safety)
$delId = pressed_action("delete");
if ($delId === null && isset($_POST["delete_id"])) {
    $delId = (int)$_POST["delete_id"];
}
if ($delId !== null && $delId > 0) {
    csrf_verify();
    try {
        $candidateRepo->delete($delId, $category_id);
        $_SESSION["messages"][] = "Candidate removed successfully.";
    } catch (VoteException $ve) {
        $_SESSION["errors"][] = $ve->getMessage();
    } catch (\Throwable $ex) {
        error_log("Candidate delete failed: " . $ex->getMessage());
        $_SESSION["errors"][] = "Could not delete candidate.";
    }
    redirect($_SERVER["PHP_SELF"]);
}

require_once __DIR__ . "/nav.php";
$candidates = $candidateRepo->findByCategory($category_id);
?>

<div class="container py-4">
    <?php require_once("../includes/view_messages_and_errors.php"); ?>

    <div class="card shadow-sm border-0">
        <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0 fw-semibold text-primary">
                <i class="bi bi-person-lines-fill me-2"></i>Election Candidates
            </h5>
            <a href="create_candidate_account.php" class="btn btn-sm btn-primary">
                <i class="bi bi-person-plus me-1"></i> Add Candidate
            </a>
        </div>
        <div class="card-body p-0">
            <form action="<?php echo e($_SERVER['PHP_SELF']); ?>" method="post">
                <?php echo csrf_field(); ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th scope="col" class="ps-3">Candidate</th>
                                <th scope="col">Photo</th>
                                <th scope="col">Symbol</th>
                                <th scope="col" class="text-end pe-3">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($candidates)): ?>
                                <tr>
                                    <td colspan="4" class="text-center py-4 text-muted">No candidates registered in this category.</td>
                                </tr>
                            <?php endif; ?>
                            <?php foreach ($candidates as $cand):
                                $photoPath  = "../upload/photo/" . $cand->photo;
                                $hasPhoto   = !empty($cand->photo) && is_file(__DIR__ . "/" . $photoPath);
                                $symbolPath = "../upload/symbol/" . $cand->symbol;
                                $hasSymbol  = !empty($cand->symbol) && is_file(__DIR__ . "/" . $symbolPath);
                            ?>
                                <tr>
                                    <td class="ps-3 fw-medium">
                                        <div class="d-flex align-items-center">
                                            <?php if ($hasPhoto): ?>
                                                <img src="<?= e($photoPath); ?>" alt="<?= e($cand->fullName); ?>" class="rounded-circle me-3 border" style="width: 48px; height: 48px; object-fit: cover;">
                                            <?php else: ?>
                                                <div class="avatar-monogram me-3" style="width: 48px; height: 48px; font-size: 1.25rem;">
                                                    <?= e(strtoupper(mb_substr($cand->fullName, 0, 1))); ?>
                                                </div>
                                            <?php endif; ?>
                                            <div>
                                                <div class="fw-bold text-dark"><?= e($cand->fullName); ?></div>
                                                <small class="text-muted">ID #<?= $cand->id; ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <?php if ($hasPhoto): ?>
                                            <span class="badge bg-light text-success border"><i class="bi bi-image me-1"></i>Uploaded</span>
                                        <?php else: ?>
                                            <span class="badge bg-light text-muted border">None</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($hasSymbol): ?>
                                            <img src="<?= e($symbolPath); ?>" alt="Symbol" class="rounded border p-1 bg-white" style="width: 40px; height: 40px; object-fit: contain;">
                                        <?php else: ?>
                                            <span class="badge bg-light text-muted border">None</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end pe-3">
                                        <a href="update_candidate_details.php?id=<?= $cand->id ?>" class="btn btn-sm btn-outline-primary me-1">
                                            <i class="bi bi-pencil-square"></i> Edit
                                        </a>
                                        <button type="submit" name="delete_id" value="<?= $cand->id ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete candidate \'<?= addslashes(e($cand->fullName)) ?>\'? This action cannot be undone.');">
                                            <i class="bi bi-trash"></i> Delete
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
require_once("../includes/footer.php");
?>
