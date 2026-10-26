<?php
declare(strict_types=1);
use App\Repository\Pdo\PdoCategoryRepository;
use App\Repository\Pdo\PdoRegistrationRequestRepository;
use App\Repository\Pdo\PdoUserRepository;
use App\Service\RegistrationRequestService;
require_once __DIR__ . "/../includes/bootstrap.php";
require_role("admin");

$requestsRepo = new PdoRegistrationRequestRepository(db());
$requestService = new RegistrationRequestService(
    db(),
    new PdoCategoryRepository(db()),
    new PdoUserRepository(db()),
    $requestsRepo,
);

// Reject a pending request (status is preserved as Rejected).
$delId = pressed_action("delete");
if ($delId === null && isset($_POST["reject_id"])) {
    $delId = (int)$_POST["reject_id"];
}
if ($delId !== null && $delId > 0) {
    csrf_verify();
    $rejected = $requestService->reject($delId);
    $_SESSION[$rejected ? "messages" : "errors"][] = $rejected ? "Election request rejected." : "Reject failed.";
    redirect(current_path());
}

// Accept a pending request: create the category + an organiser, then mark approved.
$acceptId = pressed_action("edit");
if ($acceptId === null && isset($_POST["accept_id"])) {
    $acceptId = (int)$_POST["accept_id"];
}
if ($acceptId !== null && $acceptId > 0) {
    csrf_verify();
    try {
        $result = $requestService->approve($acceptId);
        $_SESSION["messages"][] = "Request approved! Category and organiser account created. "
            . "Temporary password for " . $result["email"] . ": " . $result["password"] . " (share securely).";
    } catch (\RuntimeException $ex) {
        $_SESSION["errors"][] = $ex->getMessage();
    } catch (\Throwable $ex) {
        error_log("Request approval failed: " . $ex->getMessage());
        $_SESSION["errors"][] = "Approval failed (the organiser email may already exist).";
    }
    redirect(current_path());
}

require_once __DIR__ . "/nav.php";
$requests = $requestsRepo->pending();
?>

<div class="container-fluid px-4 py-4">
    <?php require_once("../includes/view-messages-and-errors.php"); ?>

    <div class="card shadow-sm border-0">
        <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0 fw-semibold text-primary">
                <i class="bi bi-inbox-fill me-2"></i>Pending Election System Registration Requests
            </h5>
            <span class="badge bg-secondary"><?= count($requests) ?> Pending</span>
        </div>
        <div class="card-body p-0">
            <form action="<?= e(current_path()); ?>" method="post">
                <?php echo csrf_field(); ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th scope="col" class="ps-3">Organiser Name</th>
                                <th scope="col">Email Address</th>
                                <th scope="col">Proposed Election Category</th>
                                <th scope="col">Max Voters</th>
                                <th scope="col">18+ Only</th>
                                <th scope="col">Requested Expiry</th>
                                <th scope="col">Submission Time</th>
                                <th scope="col" class="text-end pe-3">Decision</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($requests)): ?>
                                <tr>
                                    <td colspan="8" class="text-center py-5 text-muted">
                                        <i class="bi bi-check2-circle text-success fs-2 d-block mb-2"></i>
                                        No pending election registration requests. All requests have been processed.
                                    </td>
                                </tr>
                            <?php endif; ?>
                            <?php foreach ($requests as $value): ?>
                                <tr>
                                    <td class="ps-3 fw-medium">
                                        <i class="bi bi-person text-secondary me-2"></i><?= e($value['full_name']); ?>
                                    </td>
                                    <td><?= e($value['email']); ?></td>
                                    <td>
                                        <strong class="text-dark"><?= e($value['election_category']); ?></strong>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border"><?= (int)$value['max_voter']; ?></span>
                                    </td>
                                    <td>
                                        <span class="badge <?= ($value['adult_voter'] === 'Yes') ? 'bg-info text-dark' : 'bg-light text-secondary border' ?>">
                                            <?= e($value['adult_voter']); ?>
                                        </span>
                                    </td>
                                    <td class="small text-muted"><?= e($value['expire_timestamp']); ?></td>
                                    <td class="small text-muted"><?= e($value['timestamp']); ?></td>
                                    <td class="text-end pe-3">
                                        <button type="submit" name="accept_id" value="<?= (int)$value['id']; ?>" class="btn btn-sm btn-success me-1">
                                            <i class="bi bi-check-lg"></i> Accept
                                        </button>
                                        <button type="submit" name="reject_id" value="<?= (int)$value['id']; ?>" class="btn btn-sm btn-outline-danger" data-confirm="Reject request for '<?= e($value['election_category']) ?>'?">
                                            <i class="bi bi-x-lg"></i> Reject
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
require_once("../includes/footer.php");
?>
