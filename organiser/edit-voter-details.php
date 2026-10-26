<?php
declare(strict_types=1);
use App\Repository\Pdo\PdoUserRepository;
require_once __DIR__ . "/../includes/bootstrap.php";
require_role("organiser");

$user_id = (int) $_SESSION["user_id"];
$userRepo = new PdoUserRepository(db());
$category_id = (int) ($userRepo->categoryIdForUser($user_id) ?? 0);

if (($editId = pressed_action("edit")) !== null) {
    csrf_verify();
    $_SESSION["voter_refID"] = $editId;
    redirect("update-voter-details.php?id=" . $editId);
}

$delId = pressed_action("delete");
if ($delId === null && isset($_POST["delete_id"])) {
    $delId = (int)$_POST["delete_id"];
}
if ($delId !== null && $delId > 0) {
    csrf_verify();
    $deleted = $userRepo->deleteVoter($delId, $category_id);
    $_SESSION[$deleted ? "messages" : "errors"][] = $deleted ? "Voter removed successfully." : "Delete failed.";
    redirect(current_path());
}

require_once __DIR__ . "/nav.php";
$voters = $userRepo->findVotersByCategory($category_id);
?>

<div class="container py-4">
    <?php require_once("../includes/view-messages-and-errors.php"); ?>

    <div class="card shadow-sm border-0">
        <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0 fw-semibold text-primary">
                <i class="bi bi-people-fill me-2"></i>Registered Voters
            </h5>
            <a href="create-voter-account.php" class="btn btn-sm btn-primary">
                <i class="bi bi-person-plus me-1"></i> Add New Voter
            </a>
        </div>
        <div class="card-body p-0">
            <form action="<?= e(current_path()); ?>" method="post">
                <?php echo csrf_field(); ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th scope="col" class="ps-3">Full Name</th>
                                <th scope="col">Email Address</th>
                                <th scope="col">Voted Status</th>
                                <th scope="col">Registration Date</th>
                                <th scope="col" class="text-end pe-3">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($voters)): ?>
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">No voters registered in this category.</td>
                                </tr>
                            <?php endif; ?>
                            <?php foreach ($voters as $voter):
                                $hasVoted = $voter->voted === 'Yes';
                            ?>
                                <tr>
                                    <td class="ps-3 fw-medium">
                                        <i class="bi bi-person text-secondary me-2"></i><?= e($voter->fullName); ?>
                                    </td>
                                    <td><?= e($voter->email); ?></td>
                                    <td>
                                        <?php if ($hasVoted): ?>
                                            <span class="badge bg-success"><i class="bi bi-check2 me-1"></i>Voted</span>
                                        <?php else: ?>
                                            <span class="badge bg-light text-muted border">Pending</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-muted small"><?= e($voter->createdTimestamp ?? 'N/A'); ?></td>
                                    <td class="text-end pe-3">
                                        <a href="update-voter-details.php?id=<?= $voter->id ?>" class="btn btn-sm btn-outline-primary me-1">
                                            <i class="bi bi-pencil-square"></i> Edit
                                        </a>
                                        <button type="submit" name="delete_id" value="<?= $voter->id ?>" class="btn btn-sm btn-outline-danger" data-confirm="Delete voter '<?= e($voter->fullName) ?>'?">
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
