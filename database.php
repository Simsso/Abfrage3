<?php
	require('dbconnect.php');
	require('validation.php');
	
	class Database {
		static function register_user($firstname, $lastname, $email, $password, $confirmpassword) {
			if ($firstname == NULL) {
				throw new Exception("No first name given");
			} else if ($lastname == NULL) {
				throw new Exception("No last name given");
			} else if (!Validation::is_email($email)) {
				throw new Exception("Invalid email address");
			} else if (!Validation::is_password($password)) {
				throw new Exception("Invalid password");
			} else if ($password != $confirmpassword) {
				throw new Exception("Different passwords");
			} else if (!self::email_available($email)) {
				throw new Exception("Email already in use");
			} else {
				$salt = rand(0, 999999);
				$password = sha1($salt . $password);
				unset($confirmpassword);
				
				$email_confirmation_key = sha1($salt . $email . $password);
				$reg_time = time();
				
				$sql = "INSERT INTO `user` (`firstname`, `lastname`, `email`, `password`, `salt`, `reg_time`, `email_confirmation_key`) 
					VALUES ('" . $firstname . "', '" . $lastname . "', '" . $email . "', '" . $password . "', '" . $salt . "', '" . $reg_time . "', '" . $email_confirmation_key . "')";
				$query = mysqli_query($con, $insert);
				return TRUE;
			}
		}
		
		static function email_available($email) {
			$sql = "SELECT COUNT(`id`) AS `count` FROM `user` WHERE `email` = '" . $email . "'";
			$query = mysqli_query($con, $sql);
			$count = mysqli_fetch_object($query)->count;
			if ($count == 0) {
				return TRUE;
			} else {
				return FALSE;
			}
		}
	}
?>