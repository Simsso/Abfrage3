<?php
	require('database.php');
	require('mail.php');
	
	
	$email = $_POST['email'];
	$password = $_POST['password'];
	
	$result = Database::check_login_data($email, $password);
	
	$id = Database::email2id($email);
	
	// correct combination
	if ($result == 1) {
		$_SESSION['id'] = $id;
		header("Location: /./");
		exit();
	} else if ($result == 2) { // correct combination but email not comfirmed yet
		$user = Database::get_user_by_id($id);
		$mail = Mail::get_email_confirmation_mail($user->firstname, $user->email, $user->email_confirmation_key);
		$mail->send();
		header("Location: /./?login_message=The email address has not been confirmed yet. A new email has been sent.&email=" . $user->email);
		exit();
	} else {
		header("Location: /./?login_message=The given email-password-combination does not exist.&email=" . $email);
		exit();
	}
	
	
?>