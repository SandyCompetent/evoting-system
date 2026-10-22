<?php
require_once __DIR__ . "/../includes/bootstrap.php";
require_role("admin");
require_once __DIR__ . "/nav.php";

$fromEmail = env("MAIL_FROM", "no-reply@evoting.local");
?>

<div class="page-section">
    <div class="container">
        <div class="row">
            <div class="col-md-6">
                <?php require_once("../includes/view_messages_and_errors.php"); ?>
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <div class="title">Send Mail to User</div>
                    </div>
                    <div class="panel-body">
                        <form action="email_script.php" method="post" class="form-signin">
                            <?php echo csrf_field(); ?>
                            <div class="form-label-group">
                                <label for="fromEmail">From</label>
                                <input type="text" id="fromEmail" class="form-control" value="<?php echo e($fromEmail); ?>" readonly>
                            </div> <br />
                            <div class="form-label-group">
                                <label for="toEmail">To <span style="color:#FF0000">*</span></label>
                                <input type="email" name="toEmail" id="toEmail" class="form-control" placeholder="Email address" required>
                            </div> <br />
                            <label for="subject">Subject <span style="color:#FF0000">*</span></label>
                            <div class="form-label-group">
                                <input type="text" id="subject" name="subject" class="form-control" placeholder="Subject" required>
                            </div><br />
                            <label for="message">Message <span style="color:#FF0000">*</span></label>
                            <div class="form-label-group">
                                <textarea id="message" name="message" class="form-control" placeholder="Message" required></textarea>
                            </div> <br />
                            <button type="submit" name="sendMailBtn" class="btn btn-lg btn-primary btn-block text-uppercase">Send Email</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
require_once("../includes/footer.php");
?>
