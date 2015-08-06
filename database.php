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
			
			$sql = "SELECT `salt` FROM `user` WHERE `email` = '$email'";
			$query = mysqli_query($con, $sql);
			while ($row = mysqli_fetch_assoc($query)) { 
			  return $row['salt'];
			}
			return null;
		}
		
		static function email2id($email) {
			global $con;
			
			$sql = "SELECT `id` FROM `user` WHERE `email` = '$email'";
			$query = mysqli_query($con, $sql);
			while ($row = mysqli_fetch_assoc($query)) { 
			  return $row['id'];
			}
            return NULL;
		}
		
		static function get_user_by_id($id) {
			return new User($id);
		}
		
		static function confirm_email($email, $key) {
			global $con; 
			$sql = "SELECT COUNT(`id`) AS `count` FROM `user` WHERE `email` = '$email' AND `email_confirmation_key` = '$key'";
			$query = mysqli_query($con, $sql);
			$count = mysqli_fetch_object($query)->count;
			if ($count == 1) {
				$sql = "UPDATE `user` SET `email_confirmed` = '1' WHERE `id` = '" . self::email2id($email) . "'";
				$query = mysqli_query($con, $sql);
				return TRUE;
			}
			return FALSE;
		}
		
		static function add_login($id) {
			global $con;
			$ip = $_SERVER['REMOTE_ADDR']; 
			$time = time();
			
			$sql = "INSERT INTO `login` (`user`, `time`, `ip`) VALUES ('$id', '$time', '$ip')";
			$query = mysqli_query($con, $sql);
		}
		
		static function first_login_of_user($id) {
			global $con; 
			$sql = "SELECT COUNT(`id`) AS `count` FROM `user` WHERE `id` = '$id'";
			$query = mysqli_query($con, $sql);
			$count = mysqli_fetch_object($query)->count;
			if ($count == 1) {
				return TRUE;
			}
			return FALSE;
		}
		
		static function get_last_login_of_user($id) {
			global $con;
			$sql = "SELECT * FROM `login` WHERE `user` = '$id' ORDER BY `time` DESC LIMIT 1";
			$query = mysqli_query($con, $sql);
			while ($row = mysqli_fetch_assoc($query)) { 
			  return new Login($row['id'], $row['user'], $row['time'], $row['ip']);
			}
			return NULL;
		}
		
		static function get_next_to_last_login_of_user($id) {
			global $con;
			$sql = "SELECT * FROM `login` WHERE `user` = '$id' ORDER BY `time` DESC LIMIT 1,2";
			$query = mysqli_query($con, $sql);
			while ($row = mysqli_fetch_assoc($query)) { 
			  return new Login($row['id'], $row['user'], $row['time'], $row['ip']);
			}
			return NULL;
		}
		
		static function get_list_of_added_users_of_user($id) {
			global $con;
			$sql = "
            SELECT `user`.`id`, `user`.`firstname`, `user`.`lastname`, `user`.`email` 
            FROM `user`, `relationship` 
            WHERE `user`.`id` = `relationship`.`user2` AND `relationship`.`user1` = '$id' AND `relationship`.`type` = 1";
			$query = mysqli_query($con, $sql);
			$result = array();
			while ($row = mysqli_fetch_assoc($query)) { 
			  array_push($result, new SimpleUser($row['id'], $row['firstname'], $row['lastname'], $row['email']));
			}
			return $result;
		}
		
		static function get_number_of_registered_users() {
			global $con; 
			$sql = "SELECT COUNT(`id`) AS `count` FROM `user`";
			$query = mysqli_query($con, $sql);
			return mysqli_fetch_object($query)->count;
		}
		
		static function get_number_of_logins_during_last_time($time_in_seconds) {
			$time_min = time() - $time_in_seconds;
			global $con; 
			$sql = "SELECT COUNT(`id`) AS `count` FROM `login` WHERE `time` > '$time_min'";
			$query = mysqli_query($con, $sql);
			return mysqli_fetch_object($query)->count;
		}
        
        static function add_user($id, $email) {
            global $con;
            $added_user_id = self::email2id($email);
            
            if (is_null($added_user_id))
                return -1;
            
            if ($added_user_id == $id) 
                return 2;
            
            $time = time();
            // check if the user is already added
            if (!self::user_already_have_relationship($id, $added_user_id)) {
                $sql = "INSERT INTO `relationship` (`user1`, `user2`, `time`, `type`) VALUES ('$id', '$added_user_id', '$time', '1')";
                $query = mysqli_query($con, $sql);
                return 1;
            }
            else {
				$sql = "UPDATE `relationship` SET `type` = '1', `time`= '$time' WHERE `user1` = '$id' AND `user2` = '$added_user_id'";
				$query = mysqli_query($con, $sql);
				return 1;
            }
            return 0;
        }
        
        static function remove_user($user1, $user2) {
                global $con;
            
				$sql = "UPDATE `relationship` SET `type` = '0' WHERE `user1` = '$user1' AND `user2` = '$user2'";
				$query = mysqli_query($con, $sql);
				return 1;
        }
        
        static function user_already_have_relationship($user1, $user2) {
            global $con;
            
			$sql = "SELECT COUNT(`id`) AS `count` FROM `relationship` WHERE `user1` = '$user1' AND `user2` = '$user2'";
			$query = mysqli_query($con, $sql);
			if (mysqli_fetch_object($query)->count == 0) {
                return false;
            }
            return true;
        }
	}

	class SimpleUser {
		public $id;
		public $firstname;
		public $lastname;
		public $email;
		
		public function __construct($id, $firstname, $lastname, $email) {
			$this->id = $id;
			$this->firstname = $firstname;
			$this->lastname = $lastname;
            $this->email = $email;
		}
		
		public function get_last_login() {
			return Database::get_last_login_of_user($this->id);
		}
		
		public function get_next_to_last_login() {
			return Database::get_next_to_last_login_of_user($this->id);
		}
	}
		
		
	class User extends SimpleUser {
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
	
	class Login {
		public $id;
		public $user_id;
		public $date;
		public $ip;
		
		public function __construct($id, $user_id, $date, $ip) {
			$this->id = $id;
			$this->user_id = $user_id;
			$this->date = $date;
			$this->ip = $ip;
		}
		
		public function get_date_string() {
			return date("r", $this->date);
		}
	}
?>