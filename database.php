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
				
				global $con;
				$sql = "INSERT INTO `user` (`firstname`, `lastname`, `email`, `password`, `salt`, `reg_time`, `email_confirmation_key`) 
					VALUES ('" . $firstname . "', '" . $lastname . "', '" . $email . "', '" . $password . "', '" . $salt . "', '" . $reg_time . "', '" . $email_confirmation_key . "')";
				$query = mysqli_query($con, $sql);
				
				// send email
				Mail::get_email_confirmation_mail($firstname, $email, $email_confirmation_key)->send();
				return TRUE;
			}
		}
		
		static function email_available($email) {
			global $con; 
			$sql = "SELECT COUNT(`id`) AS `count` FROM `user` WHERE `email` = '" . $email . "'";
			$query = mysqli_query($con, $sql);
			$count = mysqli_fetch_object($query)->count;
			if ($count == 0) {
				return TRUE;
			} else {
				return FALSE;
			}
		}
		
		// checks a email password combination
		// returns:
		// 0: wrong combination
		// 1: right combination and email has been confirmed
		// 2: right combination and email has not been confirmed yet
		static function check_login_data($email, $password) {
			global $con;
			
			$password_hash = sha1(self::get_salt_by_email($email) . $password);
			$sql = "SELECT COUNT(`id`) AS `count` FROM `user` WHERE `email` = '" . $email . "' AND `password` = '" . $password_hash . "'";
			$query = mysqli_query($con, $sql);
			$count = mysqli_fetch_object($query)->count;
			if ($count == 0) {
				return 0;
			} else {
				$sql = "SELECT COUNT(`id`) AS `count` FROM `user` WHERE `email` = '" . $email . "' AND `password` = '" . $password_hash . "' AND `email_confirmed` = '0'";
				$query = mysqli_query($con, $sql);
				$count = mysqli_fetch_object($query)->count;
				if ($count == 1) {
					return 2;
				} else {
					return 1;
				}
			}
		}
		
		static function get_salt_by_email($email) {
			global $con;
			
			$sql = "SELECT `salt` FROM `user` WHERE `email` = '" . $email . "'";
			$query = mysqli_query($con, $sql);
			while ($row = mysqli_fetch_assoc($query)) { 
			  return $row['salt'];
			}
			return null;
		}
		
		static function email2id($email) {
			global $con;
			
			$sql = "SELECT `id` FROM `user` WHERE `email` = '" . $email . "'";
			$query = mysqli_query($con, $sql);
			while ($row = mysqli_fetch_assoc($query)) { 
			  return $row['id'];
			}
		}
		
		static function get_user_by_id($id) {
			return new User($id);
		}
		
		static function confirm_email($email, $key) {
			global $con; 
			$sql = "SELECT COUNT(`id`) AS `count` FROM `user` WHERE `email` = '" . $email . "' AND `email_confirmation_key` = '" . $key . "'";
			$query = mysqli_query($con, $sql);
			$count = mysqli_fetch_object($query)->count;
			if ($count == 1) {
				$sql = "UPDATE `user` Set `email_confirmed` = '1' WHERE `id` = '" . self::email2id($email) . "'";
				$query = mysqli_query($con, $sql);
				return TRUE;
			}
			return FALSE;
		}
	}
		
		
	class User {
		public $id;
		public $firstname;
		public $lastname;
		public $email;
		public $password;
		public $salt;
		public $reg_time;
		public $email_confirmed;
		public $email_confirmation_key;
		
		public function __construct($id) {
			global $con;
			
			$sql = "SELECT * FROM `user` WHERE `id` = '" . $id . "'";
			$query = mysqli_query($con, $sql);
			while ($row = mysqli_fetch_assoc($query)) { 
			  $this->id = $id;
			  $this->firstname = $row['firstname'];
			  $this->lastname = $row['lastname'];
			  $this->email = $row['email'];
			  $this->password = $row['password'];
			  $this->salt = $row['salt'];
			  $this->reg_time = $row['reg_time'];
			  $this->email_confirmed = $row['email_confirmed'];
			  $this->email_confirmation_key = $row['email_confirmation_key'];
			}
		}
	}
?>