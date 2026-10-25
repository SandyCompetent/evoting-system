<?php
require_once __DIR__ . "/../includes/bootstrap.php";
require_role("organiser");

use App\Repository\Pdo\PdoUserRepository;
use App\Service\AuthService;

$auth = new AuthService(new PdoUserRepository(db()));

if (isset($_POST["change"])) {
    csrf_verify();
    $user_id = (int) $_SESSION["user_id"];
    $old     = (string) ($_POST["old_password"] ?? "");
    $new     = (string) ($_POST["new_password"] ?? "");
    $confirm = (string) ($_POST["confirm_password"] ?? "");

    try {
        $auth->changePassword($user_id, $old, $new, $confirm);
        $_SESSION["messages"][] = "Password changed successfully.";
    } catch (\InvalidArgumentException $ex) {
        $_SESSION["errors"][] = $ex->getMessage();
    } catch (\Throwable $ex) {
        error_log("Organiser password change error: " . $ex->getMessage());
        $_SESSION["errors"][] = "Could not update password. Please try again.";
    }
    redirect($_SERVER["PHP_SELF"]);
}

require_once __DIR__ . "/nav.php";
?>

<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <?php require_once("../includes/view-messages-and-errors.php"); ?>

            <div class="card shadow-sm border-0">
                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="card-title mb-0 fw-semibold text-primary">
                        <i class="bi bi-shield-lock-fill me-2"></i>Change Organiser Password
                    </h5>
                </div>
                <div class="card-body p-4">
                    <form action="<?php echo e($_SERVER['PHP_SELF']); ?>" method="post">
                        <?php echo csrf_field(); ?>

                        <div class="mb-3">
                            <label class="form-label highlight fw-semibold" for="old_password">Current Password</label>
                            <input type="password" class="form-control" id="old_password" name="old_password" placeholder="Enter current password" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label highlight fw-semibold" for="new_password">New Password</label>
                            <input type="password" class="form-control" id="new_password" name="new_password" placeholder="At least 8 characters" minlength="8" required>
                        </div>

                        <div class="mb-4">
                            <label class="form-label highlight fw-semibold" for="confirm_password">Confirm New Password</label>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" placeholder="Re-enter new password" minlength="8" required>
                        </div>

                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary px-4 fw-semibold" id="change" name="change">
                                <i class="bi bi-key-fill me-1"></i> Update Password
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
