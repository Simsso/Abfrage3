<?php
require('dbconnect.inc.php'); // include data base connection
require('validation.class.php'); // include validation class to verify correctness of strings in general

class Database {
	// register user
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

	// check if an email-address is available
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
	// 1: right combination
	// 2: right combination but email has not been confirmed yet
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

	// read the user specifict salt used to do the password hash
	static function get_salt_by_email($email) {
		global $con;

		$sql = "SELECT `salt` FROM `user` WHERE `email` = '$email'";
		$query = mysqli_query($con, $sql);
		while ($row = mysqli_fetch_assoc($query)) {
			return $row['salt'];
		}
		return null;
	}

	// conver a user email to the id of the user
	static function email2id($email) {
		global $con;

		$sql = "SELECT `id` FROM `user` WHERE `email` = '$email'";
		$query = mysqli_query($con, $sql);
		while ($row = mysqli_fetch_assoc($query)) {
			return $row['id'];
		}
		return NULL;
	}

	// get user object by id
	static function get_user_by_id($id) {
		return new User($id);
	}

	// confirm email-address
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

	// add a login
	static function add_login($id) {
		global $con;
		$ip = $_SERVER['REMOTE_ADDR'];
		$time = time();

		$sql = "INSERT INTO `login` (`user`, `time`, `ip`) VALUES ('" . $id . "', '" . $time . "', '" . $ip . "')";
		$query = mysqli_query($con, $sql);
	}

	// returns true if the given user logs in for the first time
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

	// returns login object of the last login of a user
	static function get_last_login_of_user($id) {
		global $con;
		$sql = "SELECT * FROM `login` WHERE `user` = '$id' ORDER BY `time` DESC LIMIT 1";
		$query = mysqli_query($con, $sql);
		while ($row = mysqli_fetch_assoc($query)) {
			return new Login($row['id'], $row['user'], $row['time'], $row['ip']);
		}
		return NULL;
	}

	// returns login object of the next to last login of a user
	static function get_next_to_last_login_of_user($id) {
		global $con;
		$sql = "SELECT * FROM `login` WHERE `user` = '$id' ORDER BY `time` DESC LIMIT 1,2";
		$query = mysqli_query($con, $sql);
		while ($row = mysqli_fetch_assoc($query)) {
			return new Login($row['id'], $row['user'], $row['time'], $row['ip']);
		}
		return NULL;
	}

	// get list of added users of user
	static function get_list_of_added_users_of_user($id) {
		global $con;
		$sql = "
		SELECT `user`.`id`, `user`.`firstname`, `user`.`lastname`, `user`.`email`
		FROM `user`, `relationship`
		WHERE `user`.`id` = `relationship`.`user2` AND `relationship`.`user1` = '" . $id . "' AND `relationship`.`type` = 1";
		$query = mysqli_query($con, $sql);
		$result = array();
		while ($row = mysqli_fetch_assoc($query)) {
			$user = new SimpleUser($row['id'], $row['firstname'], $row['lastname'], $row['email']);
			$user->bidirectional = self::users_have_added_them_both($id, $row['id']);
			array_push($result, $user);
		}
		return $result;
	}

	// get list of users who have added user
	static function get_list_of_users_who_have_added_user($id) {
		global $con;
		$sql = "
		SELECT `user`.`id`, `user`.`firstname`, `user`.`lastname`, `user`.`email`
		FROM `user`, `relationship`
		WHERE `user`.`id` = `relationship`.`user1` AND `relationship`.`user2` = '$id' AND `relationship`.`type` = 1";
		$query = mysqli_query($con, $sql);
		$result = array();
		while ($row = mysqli_fetch_assoc($query)) {
			$user = new SimpleUser($row['id'], $row['firstname'], $row['lastname'], $row['email']);
			$user->bidirectional = self::users_have_added_them_both($id, $row['id']);
			array_push($result, $user);
		}
		return $result;
	}

	// returns true if users have added them both (bidirectional)
	static function users_have_added_them_both($user1, $user2) {
		global $con;
		$sql = "SELECT COUNT(`id`) AS `count` FROM `relationship` WHERE `user1` = '$user1' AND `user2` = '$user2' AND `relationship`.`type` = 1";
		$query = mysqli_query($con, $sql);
		if (mysqli_fetch_object($query)->count == 1) {
			$sql = "SELECT COUNT(`id`) AS `count` FROM `relationship` WHERE `user2` = '$user1' AND `user1` = '$user2' AND `relationship`.`type` = 1";
			$query = mysqli_query($con, $sql);
			if (mysqli_fetch_object($query)->count == 1) {
				return true;
			}
		}
		return false;
	}

	// get number of registered users
	static function get_number_of_registered_users() {
		global $con;
		$sql = "SELECT COUNT(`id`) AS `count` FROM `user`";
		$query = mysqli_query($con, $sql);
		return mysqli_fetch_object($query)->count;
	}

	// get number of logins during last time given in seconds
	static function get_number_of_logins_during_last_time($time_in_seconds) {
		$time_min = time() - $time_in_seconds;
		global $con;
		$sql = "SELECT COUNT(`id`) AS `count` FROM `login` WHERE `time` > '$time_min'";
		$query = mysqli_query($con, $sql);
		return mysqli_fetch_object($query)->count;
	}

	// add user
	static function add_user($id, $email) {
		global $con;
		$added_user_id = self::email2id($email);

		if (is_null($added_user_id))
		return -1;

		if ($added_user_id == $id)
		return -2;

		$time = time();
		// check if the user is already added
		if (!self::user_already_have_relationship($id, $added_user_id)) {
			$sql = "INSERT INTO `relationship` (`user1`, `user2`, `time`, `type`) VALUES ('$id', '$added_user_id', '$time', '1')";
			$query = mysqli_query($con, $sql);
			return 1;
		} else {
			$sql = "UPDATE `relationship` SET `type` = '1', `time`= '$time' WHERE `user1` = '$id' AND `user2` = '$added_user_id'";
			$query = mysqli_query($con, $sql);
			return 2;
		}
		return 0;
	}

	// remove user
	static function remove_user($user1, $user2) {
		global $con;

		$sql = "UPDATE `relationship` SET `type` = '0' WHERE `user1` = '$user1' AND `user2` = '$user2'";
		$query = mysqli_query($con, $sql);
		return 1;
	}

	// are two users already having a relationship entry in the data base
	static function user_already_have_relationship($user1, $user2) {
		global $con;

		$sql = "SELECT COUNT(`id`) AS `count` FROM `relationship` WHERE `user1` = '$user1' AND `user2` = '$user2'";
		$query = mysqli_query($con, $sql);
		if (mysqli_fetch_object($query)->count == 0) {
			return false;
		}
		return true;
	}


	// word lists
	// add word lsit
	static function add_word_list($id, $name) {
		// returns id and state
		global $con;
		$time = time();
		$sql = "INSERT INTO `list` (`name`, `creator`, `creation_time`) VALUES ('$name', '$id', '$time')";
		$query = mysqli_query($con, $sql);

		$result->state = 1;
		$result->id = mysqli_insert_id($con);
		return $result;
	}

	// get list of word lists of a user
	static function get_word_lists_of_user($id) {
		global $con;
		$sql = "
		SELECT `id`, `name`, `creator`, `comment`, `language1`, `language2`, `creation_time`
		FROM `list`
		WHERE `creator` = '$id' AND `active` = '1'";
		$query = mysqli_query($con, $sql);
		$result = array();
		while ($row = mysqli_fetch_assoc($query)) {
			$list = new BasicWordList($row['id'], $row['name'], $row['creator'], $row['comment'], $row['language1'], $row['language2'], $row['creation_time']);
			array_push($result, $list);
		}
		return $result;
	}

	// get specific word list
	static function get_word_list($user_id, $word_list_id) {
		global $con;
		$sql = "
		SELECT `list`.`id`, `list`.`name`, `list`.`creator`, `list`.`comment`, `list`.`language1`, `list`.`language2`, `list`.`creation_time`
		FROM `list`, `share`
		WHERE (`list`.`creator` = '" . $user_id . "' OR `share`.`user` = '".$user_id."' AND `share`.`list` = '".$word_list_id."') AND `list`.`id` = '" . $word_list_id . "'";
		$query = mysqli_query($con, $sql);
		while ($row = mysqli_fetch_assoc($query)) {
			$list = new WordList(
			$row['id'],
			$row['name'],
			SimpleUser::get_by_id($row['creator']),
			$row['comment'],
			$row['language1'],
			$row['language2'],
			$row['creation_time'],
			self::get_words_of_list($row['id']));
			$list->set_labels(WordList::get_labels_of_list($user_id, $word_list_id));
			return $list;
		}
	}

	// rename word list
	static function rename_word_list($user_id, $word_list_id, $list_name) {
		global $con;

		$sql = "UPDATE `list` SET `name` = '".$list_name."' WHERE `id` = '".$word_list_id."' AND `creator` = '".$user_id."'";
		$query = mysqli_query($con, $sql);
		return 1;
	}

	// get words of list
	static function get_words_of_list($list_id) {
		global $con;
		$sql = "SELECT * FROM `word` WHERE `list` = '$list_id' AND `status` = '1' ORDER BY `id` DESC";
		$query = mysqli_query($con, $sql);
		$output = array();
		while ($row = mysqli_fetch_assoc($query)) {
			array_push($output, new Word($row['id'], $row['list'], $row['language1'], $row['language2']));
		}
		return $output;
	}

	// delete word list
	static function delete_word_list($user_id, $word_list_id) {
		global $con;

		$sql = "UPDATE `list` SET `active` = '0' WHERE `id` = '$word_list_id' AND `creator` = '$user_id'";
		$query = mysqli_query($con, $sql);
		return 1;
	}

	// add word
	static function add_word($user_id, $word_list_id, $lang1, $lang2) {
		global $con;
		$sql = "INSERT INTO `word` (`list`, `language1`, `language2`)
		VALUES ('" . $word_list_id . "', '" . $lang1 . "', '" . $lang2 . "')";
		$query = mysqli_query($con, $sql);
		return mysqli_insert_id($con);
	}

	// update word
	static function update_word($user_id, $word_id, $lang1, $lang2) {
		global $con;
		// TODO: add check if word is owned by $user_id
		$sql = "UPDATE `word` SET `language1` = '$lang1', `language2` = '$lang2' WHERE `id` = '$word_id'";
		$query = mysqli_query($con, $sql);
		return 1;
	}

	// remove word
	static function remove_word($user_id, $word_id) {
		global $con;
		// TODO: add check if word is owned by $user_id
		$sql = "UPDATE `word` SET `status` = '0' WHERE `id` = '$word_id'";
		$query = mysqli_query($con, $sql);
		return 1;
	}

	// get list of shared word lists of user
	static function get_list_of_shared_word_lists_of_user($id) {
		global $con;
		$sql = "SELECT `share`.`id` AS 'share_id', `list`.`id` AS 'list_id', `list`.`name`, `list`.`creator`, `list`.`comment`, `list`.`language1`, `list`.`language2`, `list`.`creation_time` FROM `share`, `list` WHERE `share`.`list` = `list`.`id` AND `list`.`creator` = '$id' AND `list`.`active` = '1'";
		$query = mysqli_query($con, $sql);
		$result = array();
		while ($row = mysqli_fetch_assoc($query)) {
			$list = new BasicWordList($row['list_id'], $row['name'], $row['creator'], $row['comment'], $row['language1'], $row['language2'], $row['creation_time']);
			$list->sharing_id = $row['share_id'];
			array_push($result, $list);
		}
		return $result;
	}

	// get list of shared word lists with user (given id)
	static function get_list_of_shared_word_lists_with_user($id) {
		global $con;
		$sql = "
		SELECT `share`.`id` AS 'share_id', `share`.`permissions`, `list`.`id` AS 'list_id', `list`.`name`, `list`.`creator`, `list`.`comment`, `list`.`language1`, `list`.`language2`, `list`.`creation_time`
		FROM `share`, `list`, `relationship`
		WHERE `share`.`user` = '$id' AND `share`.`list` = `list`.`id` AND `list`.`active` = '1' AND `share`.`permissions` <> '0'
		AND `relationship`.`user1` = '$id' AND `relationship`.`user2` = `list`.`creator` AND `relationship`.`type` = '1'";
		$query = mysqli_query($con, $sql);
		$result = array();
		while ($row = mysqli_fetch_assoc($query)) {
			$list = new BasicWordList($row['list_id'], $row['name'], $row['creator'], $row['comment'], $row['language1'], $row['language2'], $row['creation_time']);
			$list->permissions = $row['permissions'];
			$list->sharing_id = $row['share_id'];
			array_push($result, $list);
		}
		return $result;
	}

	// set sharing permissions of word list with user (email)
	static function set_sharing_permissions($user_id, $word_list_id, $email, $permissions) {
		$share_with_id = self::email2id($email);
		if ($share_with_id == $user_id) return -1;

		global $con;
		$sql = "SELECT COUNT(`id`) AS `count` FROM `share` WHERE `user` = '" . $share_with_id . "' AND `list` = '" . $word_list_id . "'";
		$query = mysqli_query($con, $sql);
		$count = mysqli_fetch_object($query)->count;
		if ($count == 0) {
			$sql = "INSERT INTO `share` (`user`, `list`, `permissions`) VALUES ('" . $share_with_id . "', '" . $word_list_id . "', '" . $permissions . "')";
			$query = mysqli_query($con, $sql);
			return 1;
		} else {
			$sql = "UPDATE `share` SET `permissions` = '$permissions' WHERE `list` = '" . $word_list_id . "' AND (`user` = '" . $share_with_id . "')";
			$query = mysqli_query($con, $sql);
			return 2;
		}
		return -1;
	}

	// set sharing permissions of word list with user (id)
	static function set_sharing_permissions_by_sharing_id($user_id, $id, $permissions) {
		global $con;

		$sql = "
		UPDATE `share`, `list`
		SET `share`.`permissions` = '$permissions'
		WHERE `share`.`id` = '$id' AND (`list`.`id` = `share`.`list` AND `list`.`creator` = '" . $user_id . "' OR `share`.`user` = '" . $user_id . "')";
		$query = mysqli_query($con, $sql);
		return 1;
	}

	// set sharing permissions of list with user
	static function get_sharing_perimssions_of_list_with_user($list_owner, $word_list_id, $email) {
		$share_with_id = self::email2id($email);

		global $con;
		$sql = "SELECT `share`.`id`, `share`.`permissions` FROM `share`, `list` WHERE `list`.`id` = '$word_list_id' AND `share`.`list` = `list`.`id` AND `list`.`creator` = '$list_owner' AND `list`.`active` = '1' AND `list`.`user` = '$share_with_id'";
		$query = mysqli_query($con, $sql);
		while ($row = mysqli_fetch_assoc($query)) {
			return new SharingInformation($row['id'], new SimpleUser($share_with_id, null, null, $email), $row['permissions']);
		}
	}

	// get sharing information of list
	static function get_sharing_info_of_list($user_id, $word_list_id) {
		global $con;
		$sql = "
		SELECT `share`.`permissions`, `share`.`id` AS 'share_id', `user`.`id` AS 'user_id', `user`.`firstname`, `user`.`lastname`, `user`.`email`
		FROM `share`, `list`, `user`
		WHERE `share`.`list` = `list`.`id` AND `share`.`user` = `user`.`id` AND
		`list`.`id` = '$word_list_id' AND `list`.`creator` = '$user_id' AND `list`.`active` = '1' AND `share`.`permissions` <> '0'";
		$query = mysqli_query($con, $sql);
		$result = array();
		while ($row = mysqli_fetch_assoc($query)) {
			array_push($result, new SharingInformation($row['share_id'], new SimpleUser($row['user_id'], $row['firstname'], $row['lastname'], $row['email']), $row['permissions']));
		}
		return $result;
	}

	// set word list languages
	static function set_word_list_languages($user_id, $list_id, $language1, $language2) {
		global $con;

		$sql = "
		UPDATE `list`, `share`
		SET `list`.`language1` = '".$language1."', `list`.`language2` = '".$language2."'
		WHERE `list`.`id` = '".$list_id."' AND
		(`list`.`id` = `share`.`list` AND `list`.`creator` = '" . $user_id . "' OR `share`.`user` = '" . $user_id . "')";
		$query = mysqli_query($con, $sql);
		return 1;
	}


	// word list labels
	// add label
	static function add_label($user_id, $label_name, $parent_label_id) {
		global $con;
		// TODO
		// check already existing
		$sql = "
		SELECT COUNT(`id`) AS `count`
		FROM `label`
		WHERE `name` = '".$label_name."' AND `user` = '".$user_id."' AND `parent` = '".$parent_label_id."'";
		$query = mysqli_query($con, $sql);
		$count = mysqli_fetch_object($query)->count;
		if ($count == 0) {
			// insert
			$sql = "INSERT INTO `label` (`user`, `name`, `parent`)
			VALUES ('" . $user_id . "', '" . $label_name . "', '".$parent_label_id."')";
			$query = mysqli_query($con, $sql);
			return mysqli_insert_id($con);
		} else {
			// update
			$sql = "UPDATE `label` SET `active` = '1' WHERE `name` = '".$label_name."' AND `user` = '".$user_id."' AND `parent` = '".$parent_label_id."'";
			$query = mysqli_query($con, $sql);
			return 1;
		}
	}

	// set label status
	static function set_label_status($user_id, $id, $status) {
		global $con;
		$sql = "UPDATE `label` SET `active` = '".$status."' WHERE `id` = '".$id."' AND `user` = '".$user_id."'";
		$query = mysqli_query($con, $sql);

		if ($status == 0) {
			// unset all label attachments
			$sql = "
			UPDATE `label_attachment`, `label`
			SET `label_attachment`.`active` = '0'
			WHERE `label`.`id` = `label_attachment`.`label` AND
			`label`.`id` = '".$id."' AND `label`.`user` = '".$user_id."'";
			$query = mysqli_query($con, $sql);
		}
		return "user_id: " . $user_id . "; id: " . $id . "; status: " . $status . ";";
	}

	// set label list attachment
	static function set_label_list_attachment($user_id, $label_id, $list_id, $attachment) {
		global $con;
		// check already attached
		$sql = "
		SELECT COUNT(`label_attachment`.`id`) AS `count`
		FROM `label_attachment`, `label`
		WHERE `label_attachment`.`label` = `label`.`id` AND
		`label`.`user` = '".$user_id."' AND `label`.`id` = '".$label_id."' AND `label_attachment`.`list` = '".$list_id."'";
		$query = mysqli_query($con, $sql);
		$count = mysqli_fetch_object($query)->count;
		if ($count == 0) {
			// insert
			$sql = "INSERT INTO `label_attachment` (`list`, `label`, `active`)
			VALUES ('" . $list_id . "', '" . $label_id . "', '".$attachment."')";
			$query = mysqli_query($con, $sql);
			return mysqli_insert_id($con);
		} else {
			// update
			$sql = "UPDATE `label_attachment`, `label` SET `label_attachment`.`active` = '".$attachment."' WHERE `label_attachment`.`label` = `label`.`id` AND
			`label`.`user` = '".$user_id."' AND `label`.`id` = '".$label_id."' AND `label_attachment`.`list` = '".$list_id."'";
			$query = mysqli_query($con, $sql);
			return 1;
		}
	}

	// get labels of user
	static function get_labels_of_user($user_id) {
		global $con;

		$sql = "SELECT * FROM `label` WHERE `user` = '".$user_id."' AND `active` = '1'";
		$query = mysqli_query($con, $sql);
		$output = array();
		while ($row = mysqli_fetch_assoc($query)) {
			array_push($output, new Label($row['id'], $row['name'], $row['user'], $row['parent'], $row['active']));
		}
		return $output;
	}

	// rename label
	static function rename_label($user_id, $label_id, $label_name) {
		global $con;
		$sql = "UPDATE `label` SET `name` = '".$label_name."' WHERE `id` = '".$label_id."' AND `user` = '".$user_id."'";
		$query = mysqli_query($con, $sql);
		return 1;
	}
}


class Label {
	public $id;
	public $name;
	public $user;
	public $parent_label;
	public $active;

	public function __construct($id, $name, $user, $parent_label, $active) {
		$this->id = $id;
		$this->name = $name;
		$this->user = $user;
		$this->parent_label = $parent_label;
		$this->active = $active;
	}

	// second constructor (by id)
	public function get_by_id($id) {
		global $con;

		$sql = "SELECT * FROM `label` WHERE `id` = '" . $id . "'";
		$query = mysqli_query($con, $sql);
		while ($row = mysqli_fetch_assoc($query)) {
			return new Label($id, $row['name'], $row['user'], $row['parent_label'], $row['active']);
		}
		return NULL;
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

	// second constructor (by id)
	static function get_by_id($id) {
		global $con;

		$sql = "SELECT `firstname`, `lastname`, `email` FROM `user` WHERE `id` = '" . $id . "'";
		$query = mysqli_query($con, $sql);
		while ($row = mysqli_fetch_assoc($query)) {
			return new SimpleUser($id, $row['firstname'], $row['lastname'], $row['email']);
		}
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

	// constructor (by id)
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

class BasicWordList {
	public $id;
	public $name;
	public $creator;
	public $comment;
	public $language1;
	public $language2;
	public $creation_time;

	public function __construct($id, $name, $creator, $comment, $language1, $language2, $creation_time) {
		$this->id = $id;
		$this->name = $name;
		$this->creator = $creator;
		$this->comment = $comment;
		$this->language1 = $language1;
		$this->language2 = $language2;
		$this->creation_time = $creation_time;
	}
}


class WordList extends BasicWordList {
	// additionally stores words of the list and the list's labels
	public $words;
	public $labels;

	public function __construct($id, $name, $creator, $comment, $language1, $language2, $creation_time, $words) {
		parent::__construct($id, $name, $creator, $comment, $language1, $language2, $creation_time);
		$this->words = $words;
	}

	static function get_labels_of_list($user, $id) {
		global $con;

		$sql = "
		SELECT `label`.`id`, `label`.`user`, `label`.`name`, `label`.`parent`
		FROM `label`, `label_attachment`, `list`
		WHERE `label_attachment`.`label` = `label`.`id` AND `label`.`user` = '".$user."' AND
		`label`.`active` = '1' AND `label_attachment`.`active` = '1' AND
		`label_attachment`.`list` = '".$id."'
		GROUP BY `label`.`id`";
		$query = mysqli_query($con, $sql);
		$output = array();
		while ($row = mysqli_fetch_assoc($query)) {
			array_push($output, new Label($row['id'], $row['name'], $row['user'], $row['parent'], 1));
		}
		return $output;
	}

	function set_labels($labels) {
		$this->labels = $labels;
	}
}

class Word {
	public $id;
	public $list;
	public $language1;
	public $language2;

	public function __construct($id, $list, $language1, $language2) {
		$this->id = $id;
		$this->list = $list;
		$this->language1 = $language1;
		$this->language2 = $language2;
	}

	static function get_by_id($id) {
		global $con;
		$sql = "SELECT * FROM `word` WHERE `id` = '$id' ORDER BY `id` DESC";
		$query = mysqli_query($con, $sql);
		while ($row = mysqli_fetch_assoc($query)) {
			return new Word($id, $row['list'], $row['language1'], $row['language2']);
		}
	}
}

class SharingInformation {
	public $id;
	public $user;
	public $permissions;

	public function __construct($id, SimpleUser $user, $permissions) {
		$this->id = $id;
		$this->user = $user;
		$this->permissions = $permissions;
	}
}
?>
