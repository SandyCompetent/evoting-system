<?php
require_once("../includes/init.php");

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
	echo "<script>window.location = '../index.php';</script>";
	exit(0);
}
?>

<nav class="navbar navbar-inverse navbar-fixed-top">
	<div class="container-fluid">
		<div class="navbar-header">
			<a class="navbar-brand" href="index.php">e-Voting</a>
			<button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#myNavbar">
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
			</button>
		</div>
		<div class="collapse navbar-collapse" id="myNavbar">
			<ul class="nav navbar-nav">
				<li class="dropdown">
					<a class="dropdown-toggle" data-toggle="dropdown" href="#">Election Category<span class="caret"></span></a>
					<ul class="dropdown-menu">
						<li><a href="create_election_category.php">Create New Election Category</a></li>
						<li><a href="edit_election_category.php">Edit Election Category</a></li>
					</ul>
				</li>
				<li class="dropdown">
					<a class="dropdown-toggle" data-toggle="dropdown" href="#">Accounts<span class="caret"></span></a>
					<ul class="dropdown-menu">
						<li><a href="create_organiser_account.php">Create New Account</a></li>
						<li><a href="edit_account_details.php">Edit Account Details</a></li>
						<li><a href="checkingmail.php">Send Mail to User</a></li>
					</ul>
				</li>
				<li><a href="request_to_register.php">Request To Register</a></li>
			</ul>
			<ul class="nav navbar-nav navbar-right">
				<li><a href="change_password.php">Change Password</a></li>
				<li><a href="../includes/logout.php">Logout</a></li>
			</ul>
		</div>
	</div>
</nav>