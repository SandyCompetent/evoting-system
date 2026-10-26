<?php
declare(strict_types=1);
use App\Repository\Pdo\PdoUserRepository;
require_once __DIR__ . "/../includes/bootstrap.php";
require_role("admin");

$userRepo = new PdoUserRepository(db());

if (($editId = pressed_action("edit")) !== null) {
    csrf_verify();
    $_SESSION["organiser_refID"] = $editId;
    redirect("update-account-details.php?id=" . $editId);
}

$delId = pressed_action("delete");
if ($delId === null && isset($_POST["delete_id"])) {
    $delId = (int)$_POST["delete_id"];
}
if ($delId !== null && $delId > 0) {
    csrf_verify();
    $deleted = $userRepo->deleteOrganiser($delId);
    $_SESSION[$deleted ? "messages" : "errors"][] = $deleted ? "Organiser deleted successfully." : "Delete failed.";
    redirect(current_path());
}

require_once __DIR__ . "/nav.php";
$organisers = $userRepo->findOrganisersWithCategory();
?>

<div class="container py-4">
    <?php require_once("../includes/view-messages-and-errors.php"); ?>

    <div class="card shadow-sm border-0">
        <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0 fw-semibold text-primary">
                <i class="bi bi-people-fill me-2"></i>Organiser Accounts
            </h5>
            <a href="create-organiser-account.php" class="btn btn-sm btn-primary">
                <i class="bi bi-person-plus me-1"></i> Create New Organiser
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
                                <th scope="col">Category</th>
                                <th scope="col">Created Date</th>
                                <th scope="col" class="text-end pe-3">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($organisers)): ?>
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">No organiser accounts found.</td>
                                </tr>
                            <?php endif; ?>
                            <?php foreach ($organisers as $value): ?>
                                <tr>
                                    <td class="ps-3 fw-medium">
                                        <i class="bi bi-person-badge text-primary me-2"></i><?= e($value['full_name']); ?>
                                    </td>
                                    <td><?= e($value['email']); ?></td>
                                    <td>
                                        <span class="badge bg-light text-dark border">
                                            <?= e($value['category_name'] ?? 'Unassigned'); ?>
                                        </span>
                                    </td>
                                    <td class="text-muted small"><?= e($value['created_timestamp'] ?? 'N/A'); ?></td>
                                    <td class="text-end pe-3">
                                        <a href="update-account-details.php?id=<?= (int)$value['user_id'] ?>" class="btn btn-sm btn-outline-primary me-1">
                                            <i class="bi bi-pencil-square"></i> Edit
                                        </a>
                                        <button type="submit" name="delete_id" value="<?= (int)$value['user_id'] ?>" class="btn btn-sm btn-outline-danger" data-confirm="Delete organiser '<?= e($value['full_name']) ?>'?">
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
