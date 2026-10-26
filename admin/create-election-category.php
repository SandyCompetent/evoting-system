<?php
declare(strict_types=1);
use App\Repository\Pdo\PdoCategoryRepository;
require_once __DIR__ . "/../includes/bootstrap.php";
require_role("admin");

if (isset($_POST["submit"])) {
    csrf_verify();
    $category = trim($_POST["category_name"] ?? "");
    $expire   = parse_datetime_local((string) ($_POST["expire_timestamp"] ?? ""));

    if ($category === "" || !text_within_length($category, 255)
        || $expire === null || !is_future_datetime($expire)
    ) {
        $_SESSION["errors"][] = "A category name and a future expiry time are required.";
        redirect(current_path());
    }

    try {
        $categoriesRepo = new PdoCategoryRepository(db());
        $now = date("Y-m-d H:i:s");
        // Elections open immediately and run until the expiry the admin chose;
        // the organiser can still narrow the window from their dashboard.
        $categoriesRepo->create($category, 0, 'No', $expire, $now, $expire);
        $_SESSION["messages"][] = "Category created successfully.";
    } catch (PDOException $ex) {
        error_log("Category create failed: " . $ex->getMessage());
        $_SESSION["errors"][] = "Could not create category.";
    }
    redirect(current_path());
}

require_once __DIR__ . "/nav.php";
?>

<div class="container py-3">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <?php require_once("../includes/view-messages-and-errors.php"); ?>

            <div class="card shadow-sm border-0">
                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="card-title mb-0 fw-semibold text-primary">
                        <i class="bi bi-folder-plus me-2"></i>Create Election Category
                    </h5>
                </div>
                <div class="card-body p-4">
                    <form action="<?= e(current_path()); ?>" method="post">
                        <?php echo csrf_field(); ?>

                        <div class="mb-3">
                            <label class="form-label highlight fw-semibold" for="category_name">Category Name</label>
                            <input type="text" class="form-control" id="category_name" name="category_name" placeholder="e.g. Student Council 2026" required>
                        </div>

                        <div class="mb-4">
                            <label class="form-label highlight fw-semibold" for="expire_timestamp">Voting Expiration Time</label>
                            <input type="datetime-local" class="form-control" id="expire_timestamp" name="expire_timestamp" required>
                            <div class="form-text text-muted">
                                Specify the final deadline after which no further ballots can be submitted.
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="edit-election-category.php" class="btn btn-outline-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary px-4" id="submit" name="submit">
                                <i class="bi bi-plus-circle me-1"></i> Create Category
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
