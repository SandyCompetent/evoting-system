<?php
require_once __DIR__ . "/../includes/bootstrap.php";
require_role("admin");

$category_refID = (int) ($_GET["id"] ?? $_POST["id"] ?? $_SESSION["category_refID"] ?? 0);
if ($category_refID > 0) {
    $_SESSION["category_refID"] = $category_refID;
}

$category = fetchOne("SELECT * FROM election_category WHERE id = ?", [$category_refID]);
if (!$category) {
    $_SESSION["errors"][] = "Category not found.";
    redirect("edit-election-category.php");
}

if (isset($_POST["submit"])) {
    csrf_verify();
    $name       = trim($_POST["category_name"] ?? "");
    $raw_expire = trim($_POST["expire_timestamp"] ?? "");
    $expire     = $raw_expire !== "" ? date("Y-m-d H:i:s", strtotime($raw_expire)) : "";

    if ($name === "") {
        $_SESSION["errors"][] = "Category name is required.";
        redirect($_SERVER["PHP_SELF"] . "?id=" . $category_refID);
    }
    execute(
        "UPDATE election_category SET name = ?, expire_timestamp = ? WHERE id = ?",
        [$name, $expire, $category_refID]
    );
    $_SESSION["messages"][] = "Category updated successfully.";
    redirect($_SERVER["PHP_SELF"] . "?id=" . $category_refID);
}

require_once __DIR__ . "/nav.php";
?>

<div class="container py-3">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <?php require_once("../includes/view-messages-and-errors.php"); ?>

            <div class="card shadow-sm border-0">
                <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0 fw-semibold text-primary">
                        <i class="bi bi-pencil-square me-2"></i>Edit Category: <?= e($category['name']) ?>
                    </h5>
                    <span class="badge bg-secondary">ID #<?= (int) $category_refID ?></span>
                </div>
                <div class="card-body p-4">
                    <form action="<?php echo e($_SERVER['PHP_SELF']); ?>?id=<?= (int)$category_refID ?>" method="post">
                        <?php echo csrf_field(); ?>
                        <input type="hidden" name="id" value="<?= (int)$category_refID ?>">

                        <div class="mb-3">
                            <label class="form-label highlight fw-semibold" for="category_name">Category Name</label>
                            <input type="text" class="form-control" id="category_name" name="category_name" value="<?php echo e($category['name']); ?>" required>
                        </div>

                        <div class="mb-4">
                            <label class="form-label highlight fw-semibold" for="expire_timestamp">Voting Expiration Time</label>
                            <input type="datetime-local" class="form-control" id="expire_timestamp" name="expire_timestamp" value="<?= !empty($category['expire_timestamp']) ? date('Y-m-d\TH:i', strtotime($category['expire_timestamp'])) : '' ?>" required>
                            <div class="form-text text-muted">
                                Registered voters can only submit ballots prior to this expiration timestamp.
                            </div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center">
                            <a href="edit-election-category.php" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-left me-1"></i> Back to Categories
                            </a>
                            <button type="submit" class="btn btn-primary px-4" id="submit" name="submit">
                                <i class="bi bi-check2-circle me-1"></i> Update Category
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
