<?php
require_once __DIR__ . "/../includes/bootstrap.php";
require_role("admin");

$organiser_refID = (int) ($_GET["id"] ?? $_POST["id"] ?? $_SESSION["organiser_refID"] ?? 0);
if ($organiser_refID > 0) {
    $_SESSION["organiser_refID"] = $organiser_refID;
}

$organiser = fetchOne(
    "SELECT * FROM system_users WHERE user_id = ? AND role = 'organiser'",
    [$organiser_refID]
);
if (!$organiser) {
    $_SESSION["errors"][] = "Organiser not found.";
    redirect("edit-account-details.php");
}

if (isset($_POST["submit"])) {
    csrf_verify();
    $fname = trim($_POST["first_name"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $pass  = (string) ($_POST["pass"] ?? "");
    $cat   = (int) ($_POST["election_category"] ?? 0);

    if ($fname === "" || !filter_var($email, FILTER_VALIDATE_EMAIL) || $cat <= 0) {
        $_SESSION["errors"][] = "Please provide a valid name, email and category.";
        redirect($_SERVER["PHP_SELF"] . "?id=" . $organiser_refID);
    }
    try {
        if ($pass !== "") {
            if (strlen($pass) < 8) {
                $_SESSION["errors"][] = "Password must be at least 8 characters.";
                redirect($_SERVER["PHP_SELF"] . "?id=" . $organiser_refID);
            }
            execute(
                "UPDATE system_users SET full_name = ?, email = ?, password = ?, election_category_id = ? WHERE user_id = ?",
                [$fname, $email, password_hash($pass, PASSWORD_DEFAULT), $cat, $organiser_refID]
            );
        } else {
            execute(
                "UPDATE system_users SET full_name = ?, email = ?, election_category_id = ? WHERE user_id = ?",
                [$fname, $email, $cat, $organiser_refID]
            );
        }
        $_SESSION["messages"][] = "Organiser updated successfully.";
    } catch (PDOException $ex) {
        error_log("Organiser update failed: " . $ex->getMessage());
        $_SESSION["errors"][] = "Update failed (the email may already be in use).";
    }
    redirect($_SERVER["PHP_SELF"] . "?id=" . $organiser_refID);
}

require_once __DIR__ . "/nav.php";
$categories = fetchAll("SELECT id, name FROM election_category ORDER BY name ASC");
?>

<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <?php require_once("../includes/view-messages-and-errors.php"); ?>

            <div class="card shadow-sm border-0">
                <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0 fw-semibold text-primary">
                        <i class="bi bi-person-gear me-2"></i>Edit Organiser: <?= e($organiser['full_name']); ?>
                    </h5>
                    <span class="badge bg-secondary">User ID #<?= (int)$organiser_refID ?></span>
                </div>
                <div class="card-body p-4">
                    <form action="<?php echo e($_SERVER['PHP_SELF']); ?>?id=<?= (int)$organiser_refID ?>" method="post">
                        <?php echo csrf_field(); ?>
                        <input type="hidden" name="id" value="<?= (int)$organiser_refID ?>">

                        <div class="mb-3">
                            <label class="form-label highlight fw-semibold" for="first_name">Full Name</label>
                            <input type="text" class="form-control" id="first_name" name="first_name" value="<?php echo e($organiser['full_name']); ?>" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label highlight fw-semibold" for="email">Email Address</label>
                            <input type="email" class="form-control" id="email" name="email" value="<?php echo e($organiser['email']); ?>" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold" for="password">Change Password</label>
                            <input type="password" class="form-control" id="password" name="pass" placeholder="Leave blank to keep existing password" minlength="8">
                            <div class="form-text text-muted">Only enter a value if you wish to reset the organiser's password (min 8 chars).</div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label highlight fw-semibold" for="election_category">Assigned Election Category</label>
                            <select class="form-select" id="election_category" name="election_category" required>
                                <option value="">Select an election category</option>
                                <?php foreach ($categories as $c): ?>
                                    <option value="<?php echo (int) $c['id']; ?>" <?php echo ((int) $c['id'] === (int) $organiser['election_category_id']) ? 'selected' : ''; ?>>
                                        <?php echo e($c['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="d-flex justify-content-between align-items-center">
                            <a href="edit-account-details.php" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-left me-1"></i> Back to Accounts
                            </a>
                            <button type="submit" class="btn btn-primary px-4 fw-semibold" id="submit" name="submit">
                                <i class="bi bi-check2-circle me-1"></i> Update Organiser
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
