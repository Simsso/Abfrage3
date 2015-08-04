<?php
	require('mail.php');
	
	$name = $_POST['name'];
	$mail = $_POST['email'];
	$subject = $_POST['subject'];
	$message = $_POST['message'];
	
	$mail = new Mail(Mail::DEFAULT_SENDER_EMAIL, $mail, null, $subject, $message);
	$mail->send();
	
	echo "<p>Thanks for your approach!</p><p>Your message has been sent.</p>";
?>