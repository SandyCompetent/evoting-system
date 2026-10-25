<?php
require_once __DIR__ . "/../includes/bootstrap.php";
require_role("organiser");

$voter_refID = (int) ($_GET["id"] ?? $_POST["id"] ?? $_SESSION["voter_refID"] ?? 0);
if ($voter_refID > 0) {
    $_SESSION["voter_refID"] = $voter_refID;
}

$user_id     = (int) $_SESSION["user_id"];
$organiser   = fetchOne("SELECT election_category_id FROM system_users WHERE user_id = ?", [$user_id]);
$category_id = (int) ($organiser["election_category_id"] ?? 0);

$voter = fetchOne(
    "SELECT * FROM system_users WHERE user_id = ? AND role = 'voter' AND election_category_id = ?",
    [$voter_refID, $category_id]
);
if (!$voter) {
    $_SESSION["errors"][] = "Voter not found.";
    redirect("edit-voter-details.php");
}

if (isset($_POST["submit"])) {
    csrf_verify();
    $fname = trim($_POST["first_name"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $pass  = (string) ($_POST["password"] ?? "");

    if ($fname === "" || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION["errors"][] = "Please provide a valid name and email.";
        redirect($_SERVER["PHP_SELF"] . "?id=" . $voter_refID);
    }

    try {
        if ($pass !== "") {
            if (strlen($pass) < 8) {
                $_SESSION["errors"][] = "Password must be at least 8 characters.";
                redirect($_SERVER["PHP_SELF"] . "?id=" . $voter_refID);
            }
            execute(
                "UPDATE system_users SET full_name = ?, email = ?, password = ? WHERE user_id = ? AND election_category_id = ?",
                [$fname, $email, password_hash($pass, PASSWORD_DEFAULT), $voter_refID, $category_id]
            );
        } else {
            execute(
                "UPDATE system_users SET full_name = ?, email = ? WHERE user_id = ? AND election_category_id = ?",
                [$fname, $email, $voter_refID, $category_id]
            );
        }
        $_SESSION["messages"][] = "Voter details updated successfully.";
    } catch (PDOException $ex) {
        error_log("Voter update failed: " . $ex->getMessage());
        $_SESSION["errors"][] = "Update failed (the email may already be in use).";
    }
    redirect($_SERVER["PHP_SELF"] . "?id=" . $voter_refID);
}

require_once __DIR__ . "/nav.php";
?>

<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <?php require_once("../includes/view-messages-and-errors.php"); ?>

            <div class="card shadow-sm border-0">
                <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0 fw-semibold text-primary">
                        <i class="bi bi-person-gear me-2"></i>Update Voter: <?= e($voter['full_name']); ?>
                    </h5>
                    <span class="badge bg-secondary">User ID #<?= (int)$voter_refID ?></span>
                </div>
                <div class="card-body p-4">
                    <form action="<?php echo e($_SERVER['PHP_SELF']); ?>?id=<?= (int)$voter_refID ?>" method="post">
                        <?php echo csrf_field(); ?>
                        <input type="hidden" name="id" value="<?= (int)$voter_refID ?>">

                        <div class="mb-3">
                            <label class="form-label highlight fw-semibold" for="first_name">Full Name</label>
                            <input type="text" class="form-control" id="first_name" name="first_name" value="<?php echo e($voter['full_name']); ?>" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label highlight fw-semibold" for="email">Email Address</label>
                            <input type="email" class="form-control" id="email" name="email" value="<?php echo e($voter['email']); ?>" required>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-semibold" for="password">Change Password</label>
                            <input type="password" class="form-control" id="password" name="password" placeholder="Leave blank to keep existing password" minlength="8">
                            <div class="form-text text-muted">Only enter a value if you need to reset the voter's password (min 8 characters).</div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center">
                            <a href="edit-voter-details.php" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-left me-1"></i> Back to Voters
                            </a>
                            <button type="submit" class="btn btn-primary px-4 fw-semibold" id="submit" name="submit">
                                <i class="bi bi-check2-circle me-1"></i> Update Voter
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
