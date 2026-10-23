<?php
require_once __DIR__ . "/../includes/init.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'organiser') {
    redirect('../index.php');
}
?>

<nav class="navbar navbar-expand-lg navbar-dark bg-primary sticky-top shadow-sm mb-4">
    <div class="container-fluid">
        <a class="navbar-brand fw-bold" href="index.php">
            <i class="bi bi-check2-circle me-1"></i> e-Voting <span class="badge bg-light text-primary fs-6 ms-1">Organiser</span>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#organiserNavbar" aria-controls="organiserNavbar" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="organiserNavbar">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-person-lines-fill me-1"></i> Candidate
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="create_candidate_account.php"><i class="bi bi-person-plus me-1"></i> Create New Candidate</a></li>
                        <li><a class="dropdown-item" href="edit_candidate_details.php"><i class="bi bi-pencil-square me-1"></i> Edit Candidate Details</a></li>
                    </ul>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-people me-1"></i> Voter
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="create_voter_account.php"><i class="bi bi-person-plus me-1"></i> Create New Voter</a></li>
                        <li><a class="dropdown-item" href="edit_voter_details.php"><i class="bi bi-pencil-square me-1"></i> Edit Voter Details</a></li>
                    </ul>
                </li>
            </ul>
            <div class="mobile-nav-divider d-lg-none"></div>
            <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                <li class="nav-item"><a class="nav-link" href="change_password.php"><i class="bi bi-key me-1"></i> Change Password</a></li>
                <li class="nav-item"><a class="nav-link" href="../includes/logout.php"><i class="bi bi-box-arrow-right me-1 text-danger"></i> Logout</a></li>
            </ul>
        </div>
    </div>
</nav>
