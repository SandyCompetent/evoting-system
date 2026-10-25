<?php
require_once __DIR__ . "/../includes/bootstrap.php";
require_role("admin");

use App\Repository\Pdo\PdoCategoryRepository;

$categoriesRepo = new PdoCategoryRepository(db());

if (($editId = pressed_action("edit")) !== null) {
    csrf_verify();
    $_SESSION["category_refID"] = $editId;
    redirect("update-election-category.php?id=" . $editId);
}

$delId = pressed_action("delete");
if ($delId === null && isset($_POST["delete_id"])) {
    $delId = (int)$_POST["delete_id"];
}
if ($delId !== null && $delId > 0) {
    csrf_verify();
    try {
        $categoriesRepo->deleteWithCascade($delId);
        $_SESSION["messages"][] = "Category and all associated data deleted successfully.";
    } catch (\Throwable $ex) {
        error_log("Category delete failed: " . $ex->getMessage());
        $_SESSION["errors"][] = "Could not delete category.";
    }
    redirect($_SERVER["PHP_SELF"]);
}

require_once __DIR__ . "/nav.php";
$categories = fetchAll("SELECT * FROM election_category ORDER BY name ASC");
?>

<div class="container py-4">
    <?php require_once("../includes/view-messages-and-errors.php"); ?>

    <div class="card shadow-sm border-0">
        <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0 fw-semibold text-primary">
                <i class="bi bi-collection-fill me-2"></i>Election Categories
            </h5>
            <a href="create-election-category.php" class="btn btn-sm btn-primary">
                <i class="bi bi-plus-circle me-1"></i> Create New Category
            </a>
        </div>
        <div class="card-body p-0">
            <form action="<?php echo e($_SERVER['PHP_SELF']); ?>" method="post">
                <?php echo csrf_field(); ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th scope="col" class="ps-3">Category Name</th>
                                <th scope="col">Created At</th>
                                <th scope="col">Expires At</th>
                                <th scope="col" class="text-end pe-3">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($categories)): ?>
                                <tr>
                                    <td colspan="4" class="text-center py-4 text-muted">No election categories found.</td>
                                </tr>
                            <?php endif; ?>
                            <?php foreach ($categories as $value): ?>
                                <tr>
                                    <td class="ps-3 fw-medium">
                                        <i class="bi bi-folder2 text-primary me-2"></i><?= e($value['name']); ?>
                                    </td>
                                    <td class="text-muted small"><?= e($value['timestamp'] ?? 'N/A'); ?></td>
                                    <td class="text-muted small"><?= e($value['expire_timestamp'] ?? 'N/A'); ?></td>
                                    <td class="text-end pe-3">
                                        <a href="update-election-category.php?id=<?= (int)$value['id'] ?>" class="btn btn-sm btn-outline-primary me-1">
                                            <i class="bi bi-pencil-square"></i> Edit
                                        </a>
                                        <button type="submit" name="delete_id" value="<?= (int)$value['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete category \'<?= addslashes(e($value['name'])) ?>\' and all associated ballots?');">
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
