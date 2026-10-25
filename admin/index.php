<?php
require_once __DIR__ . "/../includes/bootstrap.php";
require_role("admin");

use App\Repository\Pdo\PdoCategoryRepository;

$categoriesRepo = new PdoCategoryRepository(db());

// Category edit -> stash id and open update page (supports legacy POST button and GET)
if (($editId = pressed_action("edit")) !== null || isset($_GET["edit_id"])) {
    $targetId = $editId ?? (int)($_GET["edit_id"] ?? 0);
    $_SESSION["category_refID"] = $targetId;
    redirect("update-election-category.php?id=" . $targetId);
}

// Category delete -> transactional cascade via CategoryRepository
$delId = pressed_action("delete");
if ($delId === null && isset($_POST["delete_id"])) {
    $delId = (int)$_POST["delete_id"];
}
if ($delId !== null && $delId > 0) {
    csrf_verify();
    try {
        $categoriesRepo->deleteWithCascade($delId);
        $_SESSION["messages"][] = "Category and all associated candidates, voters, and ballots were deleted.";
    } catch (\Throwable $ex) {
        error_log("Category cascade delete failed: " . $ex->getMessage());
        $_SESSION["errors"][] = "Could not delete category.";
    }
    redirect($_SERVER["PHP_SELF"]);
}

require_once __DIR__ . "/nav.php";

$categoryCount     = (int) (fetchOne("SELECT COUNT(*) c FROM election_category")["c"] ?? 0);
$organisationCount = (int) (fetchOne("SELECT COUNT(*) c FROM system_users WHERE role = 'organiser'")["c"] ?? 0);
$voterCount        = (int) (fetchOne("SELECT COUNT(*) c FROM system_users WHERE role = 'voter'")["c"] ?? 0);
$pending_request   = (int) (fetchOne("SELECT COUNT(*) c FROM request_to_register WHERE ac_status = 'Pending'")["c"] ?? 0);

$categories = fetchAll("SELECT * FROM election_category ORDER BY name ASC");
$now = time();
?>

<div class="container py-3">
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2 mb-4 pb-2 border-bottom">
        <div>
            <h2 class="h3 fw-bold text-dark mb-1">
                <i class="bi bi-speedometer2 text-primary me-2"></i>Administrator Dashboard
            </h2>
            <div class="text-muted small">System administration and election management</div>
        </div>
        <div class="mt-2 mt-sm-0 d-flex flex-wrap gap-2">
            <a href="generate-demo-data.php" class="btn btn-sm btn-outline-warning">
                <i class="bi bi-database-fill-gear me-1"></i>Demo Data
            </a>
            <a href="create-election-category.php" class="btn btn-sm btn-primary">
                <i class="bi bi-plus-circle me-1"></i>New Category
            </a>
            <a href="create-organiser-account.php" class="btn btn-sm btn-outline-primary">
                <i class="bi bi-person-plus me-1"></i>New Organiser
            </a>
        </div>
    </div>

    <?php require_once("../includes/view-messages-and-errors.php"); ?>

    <!-- 4-Stat KPI Grid -->
    <div class="row g-2 g-sm-3 mb-4">
        <div class="col-6 col-xl-3">
            <div class="card stat-card h-100 p-3">
                <div class="d-flex align-items-center">
                    <div class="stat-icon bg-primary bg-opacity-10 text-primary me-3">
                        <i class="bi bi-collection-fill"></i>
                    </div>
                    <div>
                        <div class="text-muted small text-uppercase fw-semibold">Categories</div>
                        <div class="fs-4 fw-bold text-dark"><?= (int)$categoryCount ?></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="card stat-card h-100 p-3">
                <div class="d-flex align-items-center">
                    <div class="stat-icon bg-success bg-opacity-10 text-success me-3">
                        <i class="bi bi-person-badge-fill"></i>
                    </div>
                    <div>
                        <div class="text-muted small text-uppercase fw-semibold">Organisers</div>
                        <div class="fs-4 fw-bold text-dark"><?= (int)$organisationCount ?></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="card stat-card h-100 p-3">
                <div class="d-flex align-items-center">
                    <div class="stat-icon bg-info bg-opacity-10 text-info me-3">
                        <i class="bi bi-people-fill"></i>
                    </div>
                    <div>
                        <div class="text-muted small text-uppercase fw-semibold">Voters</div>
                        <div class="fs-4 fw-bold text-dark"><?= (int)$voterCount ?></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="card stat-card h-100 p-3">
                <div class="d-flex align-items-center">
                    <div class="stat-icon bg-warning bg-opacity-10 text-warning me-3">
                        <i class="bi bi-hourglass-split"></i>
                    </div>
                    <div>
                        <div class="text-muted small text-uppercase fw-semibold">Pending Requests</div>
                        <div class="fs-4 fw-bold text-dark"><?= (int)$pending_request ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Left: Pending Queue & Chart -->
        <div class="col-lg-5">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0 fw-semibold text-primary">
                        <i class="bi bi-inbox-fill me-2"></i>Registration Requests
                    </h5>
                    <?php if ($pending_request > 0): ?>
                        <span class="badge bg-warning text-dark"><?= (int)$pending_request ?> Action Required</span>
                    <?php else: ?>
                        <span class="badge bg-success">All Clear</span>
                    <?php endif; ?>
                </div>
                <div class="card-body p-4 text-center">
                    <div class="display-5 fw-bold text-dark mb-2"><?= (int)$pending_request ?></div>
                    <p class="text-muted small mb-3">Public requests awaiting verification and approval</p>
                    <a href="request-to-register.php" class="btn btn-outline-primary btn-sm px-3">
                        <i class="bi bi-arrow-right-circle me-1"></i> Review Requests Queue
                    </a>
                </div>
            </div>

            <div class="card shadow-sm border-0">
                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="card-title mb-0 fw-semibold text-primary">
                        <i class="bi bi-pie-chart-fill me-2"></i>System User Base
                    </h5>
                </div>
                <div class="card-body p-3">
                    <div id="barChartContainer" style="width: 100%; min-height: 250px;">
                        <div id="barChart" style="width: 100%; height: 250px;"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right: Active Election Categories -->
        <div class="col-lg-7">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0 fw-semibold text-primary">
                        <i class="bi bi-collection-play-fill me-2"></i>Election Categories
                    </h5>
                    <span class="badge bg-secondary"><?= count($categories) ?> Total</span>
                </div>
                <div class="card-body p-0">
                    <form action="<?php echo e($_SERVER['PHP_SELF']); ?>" method="post">
                        <?php echo csrf_field(); ?>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th scope="col" class="ps-3">Category Name</th>
                                        <th scope="col">Status / Expiry</th>
                                        <th scope="col" class="text-end pe-3">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($categories)): ?>
                                        <tr>
                                            <td colspan="3" class="text-center py-4 text-muted">No election categories found.</td>
                                        </tr>
                                    <?php endif; ?>
                                    <?php foreach ($categories as $value):
                                        $expireSec = !empty($value['expire_timestamp']) ? strtotime($value['expire_timestamp']) : 0;
                                        $diffDays  = floor(($expireSec - $now) / 86400);
                                    ?>
                                        <tr>
                                            <td class="ps-3 fw-medium">
                                                <i class="bi bi-folder2 text-primary me-2"></i><?= e($value['name']); ?>
                                            </td>
                                            <td>
                                                <?php if ($expireSec <= 0): ?>
                                                    <span class="badge bg-secondary">Unset</span>
                                                <?php elseif ($expireSec < $now): ?>
                                                    <span class="badge bg-danger"><i class="bi bi-x-circle me-1"></i>Expired</span>
                                                <?php elseif ($diffDays == 0): ?>
                                                    <span class="badge bg-warning text-dark"><i class="bi bi-clock-history me-1"></i>Expires Today</span>
                                                <?php else: ?>
                                                    <span class="badge bg-success"><i class="bi bi-clock me-1"></i><?= (int)$diffDays ?>d remaining</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-end pe-3">
                                                <a href="update-election-category.php?id=<?= (int)$value['id'] ?>" class="btn btn-sm btn-outline-primary me-1">
                                                    <i class="bi bi-pencil-square"></i> Edit
                                                </a>
                                                <button type="submit" name="delete_id" value="<?= (int)$value['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete category \'<?= addslashes(e($value['name'])) ?>\' and ALL its associated ballots, candidates, and voters?');">
                                                    <i class="bi bi-trash"></i> Delete
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
    </div>
</div>

<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
<script type="text/javascript">
    google.charts.load('current', { 'packages': ['bar'] });
    google.charts.setOnLoadCallback(drawStuff);

    function drawStuff() {
        var el = document.getElementById('barChart');
        if (!el) return;

        var data = new google.visualization.arrayToDataTable([
            ['Entity', 'Count'],
            ["Categories", <?php echo (int) $categoryCount; ?>],
            ["Organisers", <?php echo (int) $organisationCount; ?>],
            ["Voters",     <?php echo (int) $voterCount; ?>]
        ]);

        var containerWidth = el.parentElement ? el.parentElement.clientWidth : 300;
        var options = {
            width: containerWidth,
            height: 250,
            legend: { position: 'none' },
            bar: { groupWidth: "55%" }
        };

        var chart = new google.charts.Bar(el);
        chart.draw(data, google.charts.Bar.convertOptions(options));
    }

    window.addEventListener('resize', drawStuff);
</script>

<?php
require_once("../includes/footer.php");
?>
