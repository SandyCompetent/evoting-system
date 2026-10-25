<?php
declare(strict_types=1);
require_once __DIR__ . "/../includes/bootstrap.php";
require_role("admin");
require_once __DIR__ . "/nav.php";

$fromEmail = env("MAIL_FROM", "no-reply@evoting.local");
?>

<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <?php require_once("../includes/view-messages-and-errors.php"); ?>

            <div class="card shadow-sm border-0">
                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="card-title mb-0 fw-semibold text-primary">
                        <i class="bi bi-envelope-paper-fill me-2"></i>Send Direct Email Notification
                    </h5>
                </div>
                <div class="card-body p-4">
                    <form action="email-script.php" method="post">
                        <?php echo csrf_field(); ?>

                        <div class="mb-3">
                            <label class="form-label fw-semibold" for="fromEmail">Sender Address (From)</label>
                            <input type="text" id="fromEmail" class="form-control text-muted bg-light" value="<?php echo e($fromEmail); ?>" readonly>
                        </div>

                        <div class="mb-3">
                            <label class="form-label highlight fw-semibold" for="toEmail">Recipient Address (To)</label>
                            <input type="email" name="toEmail" id="toEmail" class="form-control" placeholder="user@domain.com" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label highlight fw-semibold" for="subject">Email Subject</label>
                            <input type="text" id="subject" name="subject" class="form-control" placeholder="Notification Subject" required>
                        </div>

                        <div class="mb-4">
                            <label class="form-label highlight fw-semibold" for="message">Message Body</label>
                            <textarea id="message" name="message" class="form-control" rows="5" placeholder="Compose notification message..." required></textarea>
                        </div>

                        <div class="d-flex justify-content-end">
                            <button type="submit" name="sendMailBtn" class="btn btn-primary px-4 fw-semibold">
                                <i class="bi bi-send-fill me-1"></i> Send Email
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
