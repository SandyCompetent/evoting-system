<?php
// Included from voter/index.php within the active-voting branch.
// Expects $category_id and $category_name in scope.
use App\Repository\Pdo\PdoCandidateRepository;

$candidateRepo = new PdoCandidateRepository(db());
$candidates = $candidateRepo->findByCategory($category_id);
?>

<div class="card shadow-sm border-0 my-4">
    <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
        <div>
            <h5 class="card-title mb-0 fw-semibold text-primary">
                <i class="bi bi-person-check me-2"></i>Official Ballot: <?= e($category_name); ?>
            </h5>
            <small class="text-muted">Select one candidate to cast your vote. You may only vote once.</small>
        </div>
        <span class="badge bg-light text-primary border px-3 py-2">
            <i class="bi bi-check-circle-fill me-1"></i> 1 Vote Allowed
        </span>
    </div>
    <div class="card-body p-4">
        <?php require_once("../includes/view_messages_and_errors.php"); ?>

        <?php if (empty($candidates)): ?>
            <div class="alert alert-warning text-center py-4">
                <i class="bi bi-exclamation-triangle-fill fs-3 d-block mb-2"></i>
                <h5>No Candidates Registered</h5>
                <p class="mb-0 text-muted">There are no candidates participating in this election category yet.</p>
            </div>
        <?php else: ?>
            <form action="<?php echo e($_SERVER['PHP_SELF']); ?>" method="post" onsubmit="return confirm('Are you sure you want to submit your vote for this candidate?');">
                <?php echo csrf_field(); ?>

                <div class="row g-4 mb-4">
                    <?php foreach ($candidates as $cand):
                        $candId     = $cand->id;
                        $name       = $cand->fullName;
                        $initial    = strtoupper(mb_substr($name, 0, 1));
                        $photoPath  = "../upload/photo/" . $cand->photo;
                        $hasPhoto   = !empty($cand->photo) && is_file(__DIR__ . "/" . $photoPath);
                        $symbolPath = "../upload/symbol/" . $cand->symbol;
                        $hasSymbol  = !empty($cand->symbol) && is_file(__DIR__ . "/" . $symbolPath);
                    ?>
                        <div class="col-sm-6 col-lg-4">
                            <label class="candidate-card-wrapper d-block text-start h-100" for="cand_<?= $candId ?>">
                                <input type="radio" class="candidate-radio" name="vote" id="cand_<?= $candId ?>" value="<?= $candId ?>" required>
                                <div class="candidate-card text-center d-flex flex-column justify-content-between">
                                    <div>
                                        <!-- Avatar & Symbol Badge -->
                                        <div class="position-relative d-inline-block mx-auto mb-3 mt-2">
                                            <?php if ($hasPhoto): ?>
                                                <img src="<?= e($photoPath) ?>" alt="<?= e($name) ?>" class="candidate-avatar">
                                            <?php else: ?>
                                                <div class="avatar-monogram"><?= e($initial) ?></div>
                                            <?php endif; ?>

                                            <?php if ($hasSymbol): ?>
                                                <div class="candidate-symbol-badge" title="Party / Candidate Symbol">
                                                    <img src="<?= e($symbolPath) ?>" alt="Symbol" class="candidate-symbol-img">
                                                </div>
                                            <?php endif; ?>
                                        </div>

                                        <h5 class="fw-bold text-dark mb-1"><?= e($name) ?></h5>
                                        <p class="small text-muted mb-3">Official Candidate</p>
                                    </div>

                                    <div class="pt-3 border-top d-flex justify-content-between align-items-center">
                                        <span class="small fw-semibold text-secondary">Select Candidate</span>
                                        <div class="candidate-check-indicator">
                                            <i class="bi bi-check-lg small"></i>
                                        </div>
                                    </div>
                                </div>
                            </label>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="d-flex justify-content-between align-items-center pt-3 border-top">
                    <span class="text-muted small">
                        <i class="bi bi-info-circle me-1"></i> Check your choice before clicking Submit.
                    </span>
                    <button type="submit" name="submit" class="btn btn-primary btn-lg px-4 shadow-sm fw-semibold">
                        <i class="bi bi-check2-circle me-2"></i> Submit Your Vote
                    </button>
                </div>
            </form>
        <?php endif; ?>
    </div>
</div>
