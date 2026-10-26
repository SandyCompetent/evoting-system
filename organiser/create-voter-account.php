<?php
declare(strict_types=1);
use App\Repository\Pdo\PdoUserRepository;
use App\Support\Password;
require_once __DIR__ . "/../includes/bootstrap.php";

// Guard before any output so we can redirect safely.
if (($_SESSION["role"] ?? "") !== "organiser") {
    redirect("../index.php");
}

$user_id = (int) $_SESSION["user_id"];
$userRepo = new PdoUserRepository(db());
$category_id = (int) ($userRepo->categoryIdForUser($user_id) ?? 0);

// Collects temporary passwords generated this request, shown once to the organiser.
$created_credentials = [];

// ---- Manual single-voter creation ----
if (isset($_POST["submit"])) {
    csrf_verify();
    $fname = trim($_POST["first_name"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $dob   = trim($_POST["dob"] ?? "");

    if ($fname === "" || !text_within_length($fname, 255)
        || !filter_var($email, FILTER_VALIDATE_EMAIL) || !text_within_length($email, 100)
    ) {
        $_SESSION["errors"][] = "Please provide a valid name and email.";
        redirect(current_path());
    }

    $plain = bin2hex(random_bytes(4));                 // unique temporary password

    try {
        $userRepo->createVoter($fname, $email, $dob, Password::hash($plain), $category_id);
        $_SESSION["messages"][] = "Voter '" . $fname . "' created. Temporary password: " . $plain
            . " (share it securely; the voter should change it on first login).";
    } catch (PDOException $e) {
        error_log("Voter insert failed: " . $e->getMessage());
        $_SESSION["errors"][] = "Could not create voter (the email may already be registered).";
    }
    redirect(current_path());
}

// ---- Bulk CSV import ----
if (isset($_POST["upload_csv"])) {
    csrf_verify();

    $tmp  = $_FILES["csv_file"]["tmp_name"] ?? "";
    $name = $_FILES["csv_file"]["name"] ?? "";
    $ok   = ($_FILES["csv_file"]["error"] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK;

    if (!$ok || !is_uploaded_file($tmp)) {
        $_SESSION["errors"][] = "File upload failed. Please try again.";
        redirect(current_path());
    }
    if (strtolower(pathinfo($name, PATHINFO_EXTENSION)) !== "csv") {
        $_SESSION["errors"][] = "Please upload a .csv file only.";
        redirect(current_path());
    }

    $handle = fopen($tmp, "r");
    if ($handle === false) {
        $_SESSION["errors"][] = "Could not read the uploaded file.";
        redirect(current_path());
    }

    $rows = [];
    $rowNum = 0;
    $skipped = 0;

    while (($cols = fgetcsv($handle, 10000, ",")) !== false) {
        $rowNum++;
        if ($rowNum === 1 && stripos((string) ($cols[0] ?? ""), "name") !== false) {
            continue;
        }
        $full  = sanitize_csv_cell(trim((string) ($cols[0] ?? "")));
        $email = trim((string) ($cols[1] ?? ""));
        $dob   = trim((string) ($cols[2] ?? ""));

        if ($full === "" || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $skipped++;
            continue;
        }

        $plain = bin2hex(random_bytes(4));
        $rows[] = [
            'full_name' => $full,
            'email' => $email,
            'date_of_birth' => $dob,
            'password_hash' => Password::hash($plain),
        ];
        $created_credentials[] = $email . " -> " . $plain;
    }
    fclose($handle);

    if ($rows === []) {
        $_SESSION["errors"][] = "No valid voter rows were found in the CSV file.";
        redirect(current_path());
    }

    try {
        $userRepo->importVoters($rows, $category_id);
    } catch (\RuntimeException $e) {
        error_log("CSV import failed: " . $e->getMessage());
        $_SESSION["errors"][] = $e->getMessage();
        redirect(current_path());
    }

    $_SESSION["messages"][] = "Imported " . count($rows) . " voter(s); skipped {$skipped} invalid row(s).";
    $_SESSION["csv_credentials"] = $created_credentials;
    redirect(current_path());
}

require_once __DIR__ . "/nav.php";

$csv_credentials = $_SESSION["csv_credentials"] ?? [];
unset($_SESSION["csv_credentials"]);
?>

<div class="container py-3">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <?php require_once("../includes/view-messages-and-errors.php"); ?>

            <?php if (!empty($csv_credentials)): ?>
                <div class="alert alert-warning border shadow-sm">
                    <h6 class="fw-bold mb-2"><i class="bi bi-key-fill me-1"></i> Generated Temporary Passwords (Displayed Once):</h6>
                    <p class="small text-muted mb-2">Save these credentials now; they will not be shown again.</p>
                    <ul class="mb-0 font-monospace small">
                        <?php foreach ($csv_credentials as $line): ?>
                            <li><?= e($line); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <!-- Manual Single Voter Creation Card -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="card-title mb-0 fw-semibold text-primary">
                        <i class="bi bi-person-plus-fill me-2"></i>Create New Voter (Single Entry)
                    </h5>
                </div>
                <div class="card-body p-4">
                    <form action="<?= e(current_path()); ?>" method="post">
                        <?php echo csrf_field(); ?>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label highlight fw-semibold" for="first_name">Full Name</label>
                                <input type="text" class="form-control" id="first_name" name="first_name" placeholder="Voter's full name" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label highlight fw-semibold" for="email">Email Address</label>
                                <input type="email" class="form-control" id="email" name="email" placeholder="voter@domain.com" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label highlight fw-semibold" for="dob">Date of Birth</label>
                                <input type="date" class="form-control" id="dob" name="dob" max="<?= date('Y-m-d') ?>" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold" for="password_note">Initial Password</label>
                                <input type="text" class="form-control text-muted bg-light" id="password_note" value="Auto-generated temporary password" readonly>
                                <div class="form-text">A temporary password will be generated for the voter.</div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end mt-4">
                            <button type="submit" class="btn btn-primary px-4" id="submit" name="submit">
                                <i class="bi bi-person-check-fill me-1"></i> Create Voter Account
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- CSV Bulk Import Card -->
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="card-title mb-0 fw-semibold text-primary">
                        <i class="bi bi-file-earmark-spreadsheet-fill me-2"></i>Bulk Voter Import (CSV)
                    </h5>
                </div>
                <div class="card-body p-4">
                    <form action="<?= e(current_path()); ?>" method="post" enctype="multipart/form-data">
                        <?php echo csrf_field(); ?>

                        <div class="mb-3">
                            <label class="form-label highlight fw-semibold" for="csv_file">Select CSV File</label>
                            <input type="file" class="form-control" id="csv_file" name="csv_file" accept=".csv" required>
                            <div class="form-text">Columns format: <code>Full Name, Email Address, Date of Birth (YYYY-MM-DD)</code>.</div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold text-muted">Required Template Format:</label>
                            <div>
                                <img src="<?= e(asset_url('assets/template.png')); ?>" class="img-fluid border rounded shadow-sm csv-template-preview" alt="CSV template: full_name, email, date_of_birth">
                            </div>
                        </div>

                        <div class="d-flex justify-content-end mt-3">
                            <button type="submit" class="btn btn-primary px-4" id="upload_csv" name="upload_csv">
                                <i class="bi bi-upload me-1"></i> Upload & Import CSV
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
