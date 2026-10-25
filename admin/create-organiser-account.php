<?php
require_once __DIR__ . "/../includes/bootstrap.php";
require_role("admin");

if (isset($_POST["submit"])) {
    csrf_verify();
    $fname = trim($_POST["first_name"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $cat   = (int) ($_POST["election_category"] ?? 0);

    if ($fname === "" || !filter_var($email, FILTER_VALIDATE_EMAIL) || $cat <= 0) {
        $_SESSION["errors"][] = "Please provide a valid name, email and category.";
        redirect($_SERVER["PHP_SELF"]);
    }
    $plain = bin2hex(random_bytes(4));
    try {
        execute(
            "INSERT INTO system_users (full_name, email, password, role, election_category_id)
             VALUES (?, ?, ?, 'organiser', ?)",
            [$fname, $email, password_hash($plain, PASSWORD_DEFAULT), $cat]
        );
        $_SESSION["messages"][] = "Organiser account created. Temporary password for {$email}: {$plain} (share securely).";
    } catch (PDOException $ex) {
        error_log("Organiser create failed: " . $ex->getMessage());
        $_SESSION["errors"][] = "Could not create organiser (the email may already be registered).";
    }
    redirect($_SERVER["PHP_SELF"]);
}

require_once __DIR__ . "/nav.php";
$categories = fetchAll("SELECT id, name FROM election_category ORDER BY name ASC");
?>

<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <?php require_once("../includes/view-messages-and-errors.php"); ?>

            <div class="card shadow-sm border-0">
                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="card-title mb-0 fw-semibold text-primary">
                        <i class="bi bi-person-plus-fill me-2"></i>Create New Organiser Account
                    </h5>
                </div>
                <div class="card-body p-4">
                    <form action="<?php echo e($_SERVER['PHP_SELF']); ?>" method="post">
                        <?php echo csrf_field(); ?>

                        <div class="mb-3">
                            <label class="form-label highlight fw-semibold" for="first_name">Full Name</label>
                            <input type="text" class="form-control" id="first_name" name="first_name" placeholder="Organiser full name" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label highlight fw-semibold" for="email">Email Address</label>
                            <input type="email" class="form-control" id="email" name="email" placeholder="organiser@institution.edu" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold" for="password">Initial Password</label>
                            <input type="text" class="form-control text-muted bg-light" id="password" value="(auto-generated and displayed after creation)" readonly>
                        </div>

                        <div class="mb-4">
                            <label class="form-label highlight fw-semibold" for="election_category">Assign to Election Category</label>
                            <select class="form-select" id="election_category" name="election_category" required>
                                <option value="">Choose an election category</option>
                                <?php foreach ($categories as $c): ?>
                                    <option value="<?php echo (int) $c['id']; ?>"><?php echo e($c['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="edit-account-details.php" class="btn btn-outline-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary px-4 fw-semibold" id="submit" name="submit">
                                <i class="bi bi-person-check me-1"></i> Create Organiser
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
