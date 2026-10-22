<?php
require_once __DIR__ . "/../includes/init.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    redirect('../index.php');
}
?>

<nav class="navbar navbar-expand-lg navbar-dark bg-primary sticky-top shadow-sm mb-4">
    <div class="container-fluid">
        <a class="navbar-brand fw-bold" href="index.php">
            <i class="bi bi-check2-circle me-1"></i> e-Voting <span class="badge bg-light text-primary fs-6 ms-1">Admin</span>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#adminNavbar" aria-controls="adminNavbar" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="adminNavbar">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-collection me-1"></i> Election Category
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="create_election_category.php"><i class="bi bi-plus-circle me-1"></i> Create New Election Category</a></li>
                        <li><a class="dropdown-item" href="edit_election_category.php"><i class="bi bi-pencil-square me-1"></i> Edit Election Category</a></li>
                    </ul>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-people me-1"></i> Accounts
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="create_organiser_account.php"><i class="bi bi-person-plus me-1"></i> Create New Account</a></li>
                        <li><a class="dropdown-item" href="edit_account_details.php"><i class="bi bi-person-gear me-1"></i> Edit Account Details</a></li>
                        <li><a class="dropdown-item" href="checkingmail.php"><i class="bi bi-envelope me-1"></i> Send Mail to User</a></li>
                    </ul>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="request_to_register.php"><i class="bi bi-inbox me-1"></i> Request To Register</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-warning fw-semibold" href="generate_demo_data.php"><i class="bi bi-database-fill-gear me-1"></i> Demo Data</a>
                </li>
            </ul>
            <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                <li class="nav-item"><a class="nav-link" href="change_password.php"><i class="bi bi-key me-1"></i> Change Password</a></li>
                <li class="nav-item"><a class="nav-link" href="../includes/logout.php"><i class="bi bi-box-arrow-right me-1"></i> Logout</a></li>
            </ul>
        </div>
    </div>
</nav>