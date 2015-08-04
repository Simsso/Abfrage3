<?php
	require('database.php');
	require('mail.php');
	
	$firstname = $_POST['firstname'];
	$lastname = $_POST['lastname'];
	$email = $_POST['email'];
	$password = $_POST['password'];
	$confirmpassword = $_POST['password'];
	
	$message = NULL;
	$signup_success = FALSE;
	try {
		if (Database::register_user($firstname, $lastname, $email, $password, $confirmpassword)) {
			$signup_success = TRUE;
		}	
	} catch (Exception $e) {
		$signup_success = FALSE;
		$message = $e->getMessage();
	}
	
	if ($signup_success) {
			header("Location: /./?email=" . $email . "&signup_success=" . $signup_success);
			exit();
	} else {
			header("Location: /./?firstname=" . $firstname . "&lastname=" . $lastname . "&email=" . $email . "&signup_success=" . $signup_success . "&signup_message=" . $message);
			exit();
	}
?>