<?php
require_once("../includes/init.php");

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'organiser') {
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
					<a class="dropdown-toggle" data-toggle="dropdown" href="#">Candidate<span class="caret"></span></a>
					<ul class="dropdown-menu">
						<li><a href="create_candidate_account.php">Create New Candidate</a></li>
						<li><a href="edit_candidate_details.php">Edit Candidate Details</a></li>
					</ul>
				</li>
				<li class="dropdown">
					<a class="dropdown-toggle" data-toggle="dropdown" href="#">Voter<span class="caret"></span></a>
					<ul class="dropdown-menu">
						<li><a href="create_voter_account.php">Create New Voter</a></li>
						<li><a href="edit_voter_details.php">Edit Voter Details</a></li>
					</ul>
				</li>
			</ul>
			<ul class="nav navbar-nav navbar-right">
				<li><a href="change_password.php">Change Password</a></li>
				<li><a href="../includes/logout.php">Logout</a></li>
			</ul>
		</div>
	</div>
</nav>