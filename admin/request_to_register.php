<?php
require_once("nav.php");
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
						<form class="form-horizontal" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post" enctype="multipart/form-data">
							<div class="form-group">
								<table class="table ">
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
										<?php if ($data = fetchData("request_to_register", "WHERE ac_status='Pending'")) :
											foreach ($data as $value) : ?>
												<tr>
													<?php
													$request_id = $value['id'];
													echo '<td>' . $value['full_name'] . '</td>';
													echo '<td>' . $value['email'] . '</td>';
													echo '<td>' . $value['election_category'] . '</td>';
													echo '<td>' . $value['max_voter'] . '</td>';
													echo '<td>' . $value['adult_voter'] . '</td>';
													echo '<td>' . $value['expire_timestamp'] . '</td>';
													echo '<td>' . $value['timestamp'] . '</td>';
													echo '<td><button type="submit" class="btn btn-primary btn-sm" name="edit' . $request_id . '">Accept</button>&emsp;';
													echo '<button type="submit" class="btn btn-danger btn-sm" id="delete" name="delete' . $request_id . '">Reject</button>';

													if (isset($_POST['edit' . $request_id])) {
														$category = trim($value['election_category']);
														$expire_datetime = trim($value['expire_timestamp']);

														$sql = "INSERT INTO election_category (name, expire_timestamp) VALUES ('$category', '$expire_datetime')";
														if (executeInsertQuery($sql)) {
															$fname = $value['full_name'];
															$email = $value['email'];
															$pass = "12345678";
															$encPass = password_hash($pass, PASSWORD_DEFAULT);
															$role = "organiser";

															$data = fetchData("election_category", "WHERE name='$category'");
															foreach ($data as $value) :
																$cat = $value["id"];
															endforeach;


															$sql = "INSERT INTO system_users(user_id,full_name,email,password,role,election_category_id) VALUES('','$fname','$email','$encPass','$role','$cat')";
															if (executeInsertQuery($sql)) {
																
																//XYZ
																$sql = "UPDATE request_to_register SET ac_status = 'Approved' WHERE id = '$cat'";
																if (executeInsertQuery($sql)) {
																	$_SESSION["messages"][] = "Data Accepted. New Category and Organiser is Created";
																
																} else {
																	$_SESSION["errors"][] = "Data Updation Failed";
																}
																
															} else {
																$_SESSION["errors"][] = "Data Failed to Accept";
																echo "<script>window.location='" . $_SERVER["PHP_SELF"] . "';</script>";
																exit(0);
															}
														} else {
															$_SESSION["errors"][] = "Data Insertion Failed" + $_SESSION["errors"];
															echo "<script>window.location='" . $_SERVER["PHP_SELF"] . "';</script>";
															exit(0);
														}
													}

													if (isset($_POST['delete' . $request_id])) {

														if (executeDeleteQuery("request_to_register","WHERE id=$request_id")) {
															$_SESSION["messages"][] = "Data Deleted Successfully";
															echo "<script>window.location='" . $_SERVER["PHP_SELF"] . "';</script>";
															exit(0);
														} else {
															$_SESSION["errors"][] = "Data Deletion Failed" + $_SESSION["errors"];
															echo "<script>window.location='" . $_SERVER["PHP_SELF"] . "';</script>";
															exit(0);
														}
													}
													?>
												</tr>
										<?php endforeach;
										endif; ?>

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