<?php
require_once __DIR__ . "/../includes/bootstrap.php";
require_role("admin");

// Reject a pending request.
if (($delId = pressed_action("delete")) !== null) {
    csrf_verify();
    $n = execute("DELETE FROM request_to_register WHERE id = ?", [$delId]);
    $_SESSION[$n ? "messages" : "errors"][] = $n ? "Request rejected." : "Reject failed.";
    redirect($_SERVER["PHP_SELF"]);
}

// Accept a pending request: create the category + an organiser, then mark approved.
if (($acceptId = pressed_action("edit")) !== null) {
    csrf_verify();
    $req = fetchOne("SELECT * FROM request_to_register WHERE id = ? AND ac_status = 'Pending'", [$acceptId]);
    if (!$req) {
        $_SESSION["errors"][] = "Request not found or already processed.";
        redirect($_SERVER["PHP_SELF"]);
    }
    $plain = bin2hex(random_bytes(4));
    $pdo = db();
    try {
        $pdo->beginTransaction();
        execute(
            "INSERT INTO election_category (name, expire_timestamp) VALUES (?, ?)",
            [trim($req["election_category"]), trim($req["expire_timestamp"])]
        );
        $categoryId = (int) $pdo->lastInsertId();
        execute(
            "INSERT INTO system_users (full_name, email, password, role, election_category_id)
             VALUES (?, ?, ?, 'organiser', ?)",
            [$req["full_name"], $req["email"], password_hash($plain, PASSWORD_DEFAULT), $categoryId]
        );
        // Mark THIS request approved (legacy bug used the category id here).
        execute("UPDATE request_to_register SET ac_status = 'Approved' WHERE id = ?", [$acceptId]);
        $pdo->commit();
        $_SESSION["messages"][] = "Approved. Category and organiser created. "
            . "Temporary password for " . $req["email"] . ": " . $plain . " (share securely).";
    } catch (PDOException $ex) {
        if ($pdo->inTransaction()) { $pdo->rollBack(); }
        error_log("Request approval failed: " . $ex->getMessage());
        $_SESSION["errors"][] = "Approval failed (the organiser email may already exist).";
    }
    redirect($_SERVER["PHP_SELF"]);
}

require_once __DIR__ . "/nav.php";
$requests = fetchAll("SELECT * FROM request_to_register WHERE ac_status = 'Pending' ORDER BY timestamp DESC");
?>

<div class="page-section">
	<div class="container-fluid">
		<div class="row">
			<div class="col-sm-12">
				<?php require_once("../includes/view_messages_and_errors.php"); ?>
				<div class="panel panel-default">
					<div class="panel-heading">
						<div class="title">Request for Election System</div>
					</div>
					<div class="panel-body">
						<form class="form-horizontal" action="<?php echo e($_SERVER['PHP_SELF']); ?>" method="post">
							<?php echo csrf_field(); ?>
							<div class="form-group">
								<table class="table">
									<thead>
										<tr>
											<th>Full Name</th>
											<th>Email</th>
											<th>Election Category</th>
											<th>Max Voter</th>
											<th>Adult Voter</th>
											<th>Expire Timestamp</th>
											<th>Timestamp</th>
											<th>Action</th>
										</tr>
									</thead>
									<tbody>
										<?php foreach ($requests as $value): ?>
											<tr>
												<td><?php echo e($value['full_name']); ?></td>
												<td><?php echo e($value['email']); ?></td>
												<td><?php echo e($value['election_category']); ?></td>
												<td><?php echo e($value['max_voter']); ?></td>
												<td><?php echo e($value['adult_voter']); ?></td>
												<td><?php echo e($value['expire_timestamp']); ?></td>
												<td><?php echo e($value['timestamp']); ?></td>
												<td>
													<button type="submit" class="btn btn-primary btn-sm" name="edit<?php echo (int) $value['id']; ?>">Accept</button>&emsp;
													<button type="submit" class="btn btn-danger btn-sm" name="delete<?php echo (int) $value['id']; ?>" onclick="return confirm('Reject this request?');">Reject</button>
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
</div>

<?php
require_once("../includes/footer.php");
?>
