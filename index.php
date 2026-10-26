<?php
require_once __DIR__ . "/includes/bootstrap.php";

use App\Repository\Pdo\PdoCategoryRepository;
use App\Repository\Pdo\PdoRegistrationRequestRepository;
use App\Repository\Pdo\PdoUserRepository;
use App\Service\AuthService;
use App\Service\RegistrationRequestService;

// Already logged in? Send to the right dashboard.
if (isset($_SESSION["role"]) && in_array($_SESSION["role"], ["admin", "organiser", "voter"], true)) {
    redirect($_SESSION["role"] . "/");
}

// ---- Login ----
if (isset($_POST["login"])) {
    csrf_verify();
    $email    = trim($_POST["email"] ?? "");
    $password = (string) ($_POST["password"] ?? "");

    $auth = new AuthService(new PdoUserRepository(db()));
    $user = $auth->authenticate($email, $password);

    if ($user !== null && in_array($user->role, ['admin', 'organiser', 'voter'], true)) {
        session_regenerate_id(true);          // prevent session fixation
        $_SESSION["user_id"] = $user->id;
        $_SESSION["role"]    = $user->role;
        redirect($user->role . "/");
    }

    $_SESSION["errors"][] = "Invalid email or password.";
    redirect(current_path());
}

// ---- Public request to register an election ----
if (isset($_POST["request_register"])) {
    csrf_verify();
    $full_name  = trim($_POST["full_name"] ?? "");
    $email      = trim($_POST["email"] ?? "");
    $category   = trim($_POST["category"] ?? "");
    $max_voter  = filter_var($_POST["max_voter"] ?? "", FILTER_VALIDATE_INT, [
        'options' => ['min_range' => 1, 'max_range' => 10000],
    ]);
    $is_adult   = (($_POST["is_adult_voter"] ?? "No") === "Yes") ? "Yes" : "No";
    $expire     = parse_datetime_local((string) ($_POST["expire_timestamp"] ?? ""));

    if ($full_name === "" || !text_within_length($full_name, 100)
        || !filter_var($email, FILTER_VALIDATE_EMAIL) || !text_within_length($email, 100)
        || $category === "" || !text_within_length($category, 100)
        || $max_voter === false
        || $expire === null || !is_future_datetime($expire)
    ) {
        $_SESSION["errors"][] = "Please provide a valid name, email, category, voter limit, and future expiry time.";
        redirect(current_path());
    }

    $service = new RegistrationRequestService(
        db(),
        new PdoCategoryRepository(db()),
        new PdoUserRepository(db()),
        new PdoRegistrationRequestRepository(db()),
    );
    $service->submit($full_name, $email, $category, (int) $max_voter, $is_adult, $expire);
    $_SESSION["messages"][] = "Election registration request submitted successfully. An administrator will review your application.";
    redirect(current_path());
}

require_once __DIR__ . "/includes/header.php";
?>

<div class="container py-4">
    <div class="text-center mb-4">
        <h1 class="display-5 fw-bold text-primary d-inline-flex align-items-center justify-content-center">
            <img src="<?= e(asset_url('assets/site-icon.svg')); ?>" alt="Logo" width="48" height="48" class="me-3">e-Voting Portal
        </h1>
        <p class="lead text-muted">Simple, secure online voting</p>
    </div>

    <div class="row g-4 justify-content-center">
        <!-- Sign In Column -->
        <div class="col-lg-5 col-md-6">
            <?php require_once("includes/view-messages-and-errors.php"); ?>
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="card-title mb-0 fw-semibold">
                        <i class="bi bi-box-arrow-in-right text-primary me-2"></i>Sign In
                    </h5>
                </div>
                <div class="card-body p-4">
                    <form action="<?= e(current_path()); ?>" method="post">
                        <?php echo csrf_field(); ?>

                        <div class="form-floating mb-3">
                            <input type="email" class="form-control" name="email" id="email" placeholder="name@example.com" autocomplete="email" inputmode="email" required>
                            <label for="email"><i class="bi bi-envelope me-1"></i> Email Address</label>
                        </div>

                        <div class="form-floating mb-3">
                            <input type="password" class="form-control" name="password" id="password" placeholder="Password" autocomplete="current-password" required>
                            <label for="password"><i class="bi bi-shield-lock me-1"></i> Password</label>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 py-2.5 fs-6 fw-semibold shadow-sm" name="login">
                            <i class="bi bi-box-arrow-in-right me-1"></i> Sign In
                        </button>
                    </form>

                    <div class="mt-4 pt-3 border-top text-center">
                        <small class="text-muted d-inline-flex align-items-center gap-1">
                            <i class="bi bi-shield-check text-success"></i>
                            <span>Secure &amp; confidential online voting</span>
                        </small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Registration Request Column -->
        <div class="col-lg-7 col-md-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white border-bottom py-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <h5 class="card-title mb-0 fw-semibold">
                        <i class="bi bi-calendar-plus text-primary me-2"></i>Host an Election
                    </h5>
                    <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="collapse" data-bs-target="#register_form" aria-expanded="false" aria-controls="register_form">
                        <i class="bi bi-pencil-square me-1"></i>Request Form
                    </button>
                </div>
                <div class="card-body p-4">
                    <p class="text-muted">
                        Need to conduct a democratic vote for your institution, department, or organization? Submit an election request to receive administrator credentials.
                    </p>

                    <div id="register_form" class="collapse">                            <form action="<?= e(current_path()); ?>" method="post" class="mt-3">
                            <?php echo csrf_field(); ?>

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label highlight fw-semibold" for="full_name">Organiser Full Name</label>
                                    <input type="text" class="form-control" name="full_name" id="full_name" placeholder="Full Name" autocomplete="name" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label highlight fw-semibold" for="req_email">Email Address</label>
                                    <input type="email" class="form-control" name="email" id="req_email" placeholder="name@domain.com" autocomplete="email" inputmode="email" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label highlight fw-semibold" for="category">Election Category Name</label>
                                    <input type="text" class="form-control" name="category" id="category" placeholder="e.g. Student Council 2026" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label highlight fw-semibold" for="expire_timestamp">Voting Expiry Time</label>
                                    <input type="datetime-local" class="form-control" name="expire_timestamp" id="expire_timestamp" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label highlight fw-semibold" for="max_voter">Maximum Eligible Voters</label>
                                    <select class="form-select" id="max_voter" name="max_voter" required>
                                        <option value="10">10 Voters</option>
                                        <option value="20">20 Voters</option>
                                        <option value="30">30 Voters</option>
                                        <option value="50" selected>50 Voters</option>
                                        <option value="100">100 Voters</option>
                                        <option value="250">250 Voters</option>
                                        <option value="500">500 Voters</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label highlight fw-semibold">Are Voters 18+?</label>
                                    <div class="d-flex gap-3 pt-2">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="is_adult_voter" id="adult_yes" value="Yes">
                                            <label class="form-check-label" for="adult_yes">Yes (Adults)</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="is_adult_voter" id="adult_no" value="No" checked>
                                            <label class="form-check-label" for="adult_no">No (Under 18)</label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary w-100 py-2 mt-4 fw-semibold" name="request_register">
                                <i class="bi bi-send-fill me-1"></i> Submit Election Request
                            </button>
                        </form>
                    </div>

                    <div class="alert alert-light border mt-3 mb-0 small text-muted">
                        <i class="bi bi-info-circle text-primary me-1"></i>
                        Once approved by an administrator, an organiser account and election category credentials will be dispatched to your registered email.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
require_once("includes/footer.php");
?>
