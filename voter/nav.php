<?php
require_once __DIR__ . "/../includes/init.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'voter') {
    redirect('../index.php');
}
?>

<nav class="navbar navbar-expand-lg navbar-dark bg-primary sticky-top shadow-sm mb-4">
    <div class="container-fluid">
        <a class="navbar-brand fw-bold" href="index.php">
            <i class="bi bi-check2-circle me-1"></i> e-Voting <span class="badge bg-light text-primary fs-6 ms-1">Voter</span>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#voterNavbar" aria-controls="voterNavbar" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="voterNavbar">
            <div class="mobile-nav-divider d-lg-none"></div>
            <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                <li class="nav-item"><a class="nav-link" href="change_password.php"><i class="bi bi-key me-1"></i> Change Password</a></li>
                <li class="nav-item"><a class="nav-link" href="../includes/logout.php"><i class="bi bi-box-arrow-right me-1 text-danger"></i> Logout</a></li>
            </ul>
        </div>
    </div>
</nav>