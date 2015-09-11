<?php
require('dbconnect.inc.php'); // include data base connection
require('validation.class.php'); // include validation class to verify correctness of strings in general

class Database {
  const STAY_LOGGED_IN_DURATION = 32678400;
  
  // log server request
  static function log_server_request($user, $page) {
    // inserts a new server request into the server_request table
    global $con;
    $sql = "INSERT INTO `server_request` (`user`, `page`, `time`, `ip`) VALUES (".$user.", '".$page."', ".time().", '".$_SERVER['REMOTE_ADDR']."');";
    $query = mysqli_query($con, $sql);
  }
  
  // register user
  static function register_user($firstname, $lastname, $email, $password, $confirmpassword) {
    // registers a new user
    // if data is invalid the function throws exceptions
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
      $salt = rand(0, 999999999);
      $password = sha1($salt . $password);
      unset($confirmpassword);
      $email_confirmation_key = sha1($salt . $email . $password);
      $reg_time = time();

      global $con;
      $sql = "
        INSERT INTO `user` (`firstname`, `lastname`, `email`, `password`, `salt`, `reg_time`, `email_confirmation_key`)
		  VALUES ('".$firstname."', '".$lastname."', '".$email."', '".$password."', ".$salt.", ".$reg_time.", '".$email_confirmation_key."');";
      $query = mysqli_query($con, $sql);

      $user_id = mysqli_insert_id($con);
      // create settings table entry
      $sql = "INSERT INTO `user_settings` (`user`) VALUES (".$user_id.");";
      $query = mysqli_query($con, $sql);


      // send email
      Mail::get_email_confirmation_mail($firstname, $email, $email_confirmation_key)->send();
      return TRUE;
    }
  }

  // check if an email-address is available
  static function email_available($email) {
    global $con;
    $sql = "SELECT COUNT(`id`) AS 'count' FROM `user` WHERE `email` LIKE '".$email."';";
    $query = mysqli_query($con, $sql);
    $count = mysqli_fetch_object($query)->count;
    if ($count == 0) {
      return TRUE;
    } else {
      return FALSE;
    }
  }

  // checks an email password combination
  // returns:
  // 0: wrong combination
  // 1: right combination
  // 2: right combination but email has not been confirmed yet
  static function check_login_data($email, $password) {
    global $con;

    // generate password hash by password and salt stored in the user table
    $password_hash = sha1(self::get_salt_by_email($email) . $password);
    unset($password); // delete password variable after useage
    
    // check how many table entries fit the email and password
    // password hash comparison with "LIKE BINARY" to make sure both are exactly equal
    $sql = "SELECT COUNT(`id`) AS 'count' FROM `user` WHERE `email` LIKE '".$email."' AND `password` LIKE BINARY '".$password_hash."' AND `active` = 1;";
    $query = mysqli_query($con, $sql);
    $count = mysqli_fetch_object($query)->count;
    if ($count == 0) { // if no entry exists which fits email and password return 0
      return 0;
    } else { // an entry which fits email and password exists
      // check if the user has already confirmed their email address
      $sql = "SELECT COUNT(`id`) AS 'count' FROM `user` WHERE `email` LIKE '".$email."' AND `password` LIKE BINARY '".$password_hash."' AND `active` = 1 AND `email_confirmed` = 0;";
      $query = mysqli_query($con, $sql);
      $count = mysqli_fetch_object($query)->count;
      if ($count == 1) { // email has not been confirmed
        return 2;
      } else { // email has been confirmed
        return 1;
      }
    }
  }

  // stay logged in
  static function stay_logged_in($login_id, $id) {
    // $login_id is the id of a table row in the table login
    // the login table stores every single login
    
    // generate salt
    $salt = rand(0, 999999999);
    $hash = sha1($salt . $id); // key to identify the user later by their cookie

    // store the hash on the users machine
    setcookie("stay_logged_in_hash", $hash, time() + self::STAY_LOGGED_IN_DURATION, '/'); // set cookie for one month
    // store the user's id on their machine
    setcookie("stay_logged_in_id", $id . "", time() + self::STAY_LOGGED_IN_DURATION, '/'); // set cookie for one month

    // update database with hash and salt
    global $con;
    $sql = "UPDATE `login` SET `stay_logged_in_hash` = '".$hash."', `stay_logged_in_salt` = '".$salt."' WHERE `id` = ".$login_id.";";
    $query = mysqli_query($con, $sql);
  }

  // check stay logged in
  static function check_stay_logged_in($id, $hash) {
    // checks if a user id is staying logged in with the given hash
    $stored_hash = "";
    $stored_salt = 0;

    global $con;
    // accept only login which are not to old (see STAY_LOGGED_IN_DURATION constant)
    $sql = "SELECT `stay_logged_in_hash`, `stay_logged_in_salt` FROM `login` WHERE `user` = ".$id." AND `time` > " . (time() - self::STAY_LOGGED_IN_DURATION) . ";";
    $query = mysqli_query($con, $sql);
    while ($row = mysqli_fetch_assoc($query)) {
      $stored_hash = $row['stay_logged_in_hash'];
      $stored_salt = $row['stay_logged_in_salt'];
      if ($stored_hash == sha1($stored_salt . $id) && $stored_hash == $hash) { // compare users cookie values with the database values
        return true;
      }
    }

    return false;
  }
  
  static function refresh_session_if_staying_logged_in() {
    if (!isset($_SESSION['id']) && isset($_COOKIE['stay_logged_in_hash']) && isset($_COOKIE['stay_logged_in_id'])) {
      // no session running but user has cookies indicating that he stayed logged in

      // confirm if the user stayed logged in
      if (Database::check_stay_logged_in($_COOKIE['stay_logged_in_id'], $_COOKIE['stay_logged_in_hash'])) {
        // the user stayed logged in
        // set the session cookie with the users id
        $_SESSION['id'] = $_COOKIE['stay_logged_in_id'];
      }
    }
  }

  // get salt by email
  static function get_salt_by_email($email) {
    // read the user specifict salt used to do the password hash

    global $con;

    $sql = "SELECT `salt` FROM `user` WHERE `email` LIKE '".$email."';";
    $query = mysqli_query($con, $sql);
    while ($row = mysqli_fetch_assoc($query)) {
      return $row['salt'];
    }
    return null;
  }

  // email to id
  static function email2id($email) {
    // converts a user email to the id of the user
    // both id and email are indivitual to every user
    
    global $con;

    $sql = "SELECT `id` FROM `user` WHERE `email` LIKE '".$email."';";
    $query = mysqli_query($con, $sql);
    while ($row = mysqli_fetch_assoc($query)) {
      return intval($row['id']);
    }
    return NULL;
  }

  // converts a user id to the email of the user
  static function id2email($id) {
    global $con;

    $sql = "SELECT `email` FROM `user` WHERE `id` = ".$id.";";
    $query = mysqli_query($con, $sql);
    while ($row = mysqli_fetch_assoc($query)) {
      return $row['email'];
    }
    return NULL;
  }

  // get user object by id
  static function get_user_by_id($id) {
    return new User($id);
  }

  // confirm email-address
  static function confirm_email($email, $key) {
    // update data base to email_confirmed = 1 if the key is correct
    
    global $con;
    $sql = "SELECT COUNT(`id`) AS 'count' FROM `user` WHERE `email` = '".$email."' AND `email_confirmation_key` = '".$key."';";
    $query = mysqli_query($con, $sql);
    $count = mysqli_fetch_object($query)->count;
    if ($count == 1) { // the given key exists
      $sql = "UPDATE `user` SET `email_confirmed` = 1 WHERE `id` = ".self::email2id($email).";";
      $query = mysqli_query($con, $sql);
      return TRUE;
    }
    return FALSE;
  }

  // add a login
  static function add_login($id, $stay_logged_in) {
    global $con;
    $ip = $_SERVER['REMOTE_ADDR'];
    $time = time();
    $sql = "INSERT INTO `login` (`user`, `time`, `ip`) VALUES (".$id.", ".$time.", '".$ip."');";
    $query = mysqli_query($con, $sql);
    
    if ($stay_logged_in) {
      self::stay_logged_in(mysqli_insert_id($con), $id);
    }
  }

  // returns true if the given user logs in for the first time
  static function first_login_of_user($id) {
    global $con;
    $sql = "SELECT COUNT(`id`) AS 'count' FROM `user` WHERE `id` = ".$id.";";
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
    $sql = "SELECT `id`, `user`, `time`, `ip` FROM `login` WHERE `user` = ".$id." ORDER BY `time` DESC LIMIT 1;";
    $query = mysqli_query($con, $sql);
    while ($row = mysqli_fetch_assoc($query)) {
      return new Login($row['id'], $row['user'], $row['time'], $row['ip']);
    }
    return NULL;
  }

  // returns login object of the next to last login of a user
  static function get_next_to_last_login_of_user($id) {
    global $con;
    $sql = "SELECT `id`, `user`, `time`, `ip` FROM `login` WHERE `user` = ".$id." ORDER BY `time` DESC LIMIT 1,2;";
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
		WHERE `user`.`id` = `relationship`.`user2` AND `relationship`.`user1` = ".$id." AND `relationship`.`type` = 1
        ORDER BY `user`.`firstname`, `user`.`lastname`;";
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
		WHERE `user`.`id` = `relationship`.`user1` AND `relationship`.`user2` = ".$id." AND `relationship`.`type` = 1
        ORDER BY `user`.`firstname`, `user`.`lastname`;";
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
    $sql = "SELECT COUNT(`id`) AS 'count' 
      FROM `relationship` 
      WHERE `user1` = ".$user1." AND `user2` = ".$user2." AND `relationship`.`type` = 1 OR 
        `user2` = ".$user1." AND `user1` = ".$user2." AND `relationship`.`type` = 1;";
    $query = mysqli_query($con, $sql);
    return (mysqli_fetch_object($query)->count == 2);
  }
  
  // user has added user
  static function user_has_added_user($user1, $user2) {
    global $con;
    $sql = "SELECT COUNT(`id`) AS 'count' 
      FROM `relationship` 
      WHERE `user1` = ".$user1." AND `user2` = ".$user2." AND `relationship`.`type` = 1;";
    $query = mysqli_query($con, $sql);
    return (mysqli_fetch_object($query)->count == 1);
  }

  // get number of registered users
  static function get_number_of_registered_users() {
    global $con;
    $sql = "SELECT COUNT(`id`) AS 'count' FROM `user`;";
    $query = mysqli_query($con, $sql);
    return mysqli_fetch_object($query)->count;
  }

  // get number of logins during last time given in seconds
  static function get_number_of_logins_during_last_time($time_in_seconds) {
    $time_min = time() - $time_in_seconds;
    global $con;
    $sql = "SELECT COUNT(`id`) AS 'count' FROM `login` WHERE `time` > ".$time_min.";";
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
      $sql = "INSERT INTO `relationship` (`user1`, `user2`, `time`, `type`) VALUES (".$id.", ".$added_user_id.", ".$time.", 1);";
      $query = mysqli_query($con, $sql);
      return 1;
    } else {
      $sql = "UPDATE `relationship` SET `type` = 1, `time`= ".$time." WHERE `user1` = ".$id." AND `user2` = ".$added_user_id.";";
      $query = mysqli_query($con, $sql);
      return 2;
    }
    return 0;
  }

  // remove user
  static function remove_user($user1, $user2) {
    global $con;

    $sql = "UPDATE `relationship` SET `type` = 0 WHERE `user1` = ".$user1." AND `user2` = ".$user2.";";
    $query = mysqli_query($con, $sql);
    return 1;
  }

  // are two users already having a relationship entry in the data base
  static function user_already_have_relationship($user1, $user2) {
    global $con;

    $sql = "SELECT COUNT(`id`) AS 'count' FROM `relationship` WHERE `user1` = ".$user1." AND `user2` = ".$user2.";";
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
    $sql = "INSERT INTO `list` (`name`, `creator`, `creation_time`) VALUES ('".$name."', ".$id.", ".$time.");";
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
		WHERE `creator` = ".$id." AND `active` = 1
        ORDER BY `name` ASC;";
    $query = mysqli_query($con, $sql);
    $result = array();
    while ($row = mysqli_fetch_assoc($query)) {
      $list = new BasicWordList($row['id'], $row['name'], $row['creator'], $row['comment'], $row['language1'], $row['language2'], $row['creation_time']);
      array_push($result, $list);
    }
    return $result;
  }

  // get query lists of user
  // includes self created lists and lists shared with the user
  static function get_query_lists_of_user($id) {
    $lists = array_merge(self::get_word_lists_of_user($id), self::get_list_of_shared_word_lists_with_user($id));
    for ($i = 0; $i < count($lists); $i++) {
      $lists[$i]->load_words(true, $id);
    }
    return $lists;
  }

  // get specific word list
  static function get_word_list($user_id, $word_list_id, $log) {
    global $con;
    
    if ($log === true) {
      $sql = "INSERT INTO `list_usage` (`user`, `list`, `time`)
          VALUES (".$user_id.", ".$word_list_id.", ".time().");";
      $query = mysqli_query($con, $sql);
    }
    
    $sql = "
		SELECT `list`.`id`, `list`.`name`, `list`.`creator`, `list`.`comment`, `list`.`language1`, `list`.`language2`, `list`.`creation_time`
		FROM `list`, `share`
		WHERE (`list`.`creator` = '".$user_id."' OR `share`.`user` = '".$user_id."' AND `share`.`list` = '".$word_list_id."') AND 
          `list`.`id` = '".$word_list_id."' AND 
          `list`.`active` = 1 AND `share`.`permissions` <> 0";
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
    return NULL;
  }
  
  // rename word list
  static function rename_word_list($user_id, $word_list_id, $list_name) {
    global $con;

    $sql = "UPDATE `list` SET `name` = '".$list_name."' WHERE `id` = ".$word_list_id." AND `creator` = ".$user_id.";";
    $query = mysqli_query($con, $sql);
    return 1;
  }

  // get words of list
  static function get_words_of_list($list_id) {
    global $con;
    $sql = "SELECT `id`, `list`, `language1`, `language2` FROM `word` WHERE `list` = ".$list_id." AND `status` = 1 ORDER BY `id` DESC";
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

    $sql = "UPDATE `list` SET `active` = 0 WHERE `id` = ".$word_list_id." AND `creator` = ".$user_id.";";
    $query = mysqli_query($con, $sql);
    return 1;
  }

  // add word
  static function add_word($user_id, $word_list_id, $lang1, $lang2) {
    global $con;
    // TODO check owner
    $sql = "INSERT INTO `word` (`list`, `language1`, `language2`, `time`, `user`)
		VALUES (".$word_list_id.", '".$lang1."', '".$lang2."', ".time().", ".$user_id.");";
    $query = mysqli_query($con, $sql);
    return mysqli_insert_id($con);
  }

  // update word
  static function update_word($user_id, $word_id, $lang1, $lang2) {
    global $con;
    // TODO: add check if word is owned by $user_id
    $sql = "UPDATE `word` SET `language1` = '".$lang1."', `language2` = '".$lang2."' WHERE `id` = ".$word_id.";";
    $query = mysqli_query($con, $sql);
    return 1;
  }

  // remove word
  static function remove_word($user_id, $word_id) {
    global $con;
    // TODO: add check if word is owned by $user_id
    $sql = "UPDATE `word` SET `status` = 0, `time` = ".time().", `user` = ".$user_id." WHERE `id` = ".$word_id.";";
    $query = mysqli_query($con, $sql);
    return 1;
  }

  // get list of shared word lists of user
  static function get_list_of_shared_word_lists_of_user($id) {
    global $con;
    $sql = "
      SELECT `share`.`id` AS 'share_id', `list`.`id` AS 'list_id', `list`.`name`, `list`.`creator`, `list`.`comment`, `list`.`language1`, `list`.`language2`, `list`.`creation_time` 
      FROM `share`, `list` 
      WHERE `share`.`list` = `list`.`id` AND `list`.`creator` = ".$id." AND `list`.`active` = 1
      ORDER BY `list`.`name` ASC;";
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
		WHERE `share`.`user` = ".$id." AND `share`.`list` = `list`.`id` AND `list`.`active` = 1 AND `share`.`permissions` <> 0
		  AND `relationship`.`user1` = ".$id." AND `relationship`.`user2` = `list`.`creator` AND `relationship`.`type` = 1
        ORDER BY `list`.`name` ASC;";
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
    $sql = "SELECT COUNT(`id`) AS 'count' FROM `share` WHERE `user` = '".$share_with_id."' AND `list` = '".$word_list_id."';";
    $query = mysqli_query($con, $sql);
    $count = mysqli_fetch_object($query)->count;
    if ($count == 0) {
      $sql = "INSERT INTO `share` (`user`, `list`, `permissions`, `time`) VALUES (".$share_with_id.", ".$word_list_id.", ".$permissions.", ".time().");";
      $query = mysqli_query($con, $sql);
      return 1;
    } else {
      $sql = "UPDATE `share` SET `permissions` = $permissions, `time` = ".time()." WHERE `list` = ".$word_list_id." AND (`user` = ".$share_with_id.");";
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
		SET `share`.`permissions` = ".$permissions.", `share`.`time` = ".time()."
		WHERE `share`.`id` = ".$id." AND (`list`.`id` = `share`.`list` AND `list`.`creator` = ".$user_id." OR `share`.`user` = ".$user_id.");";
    $query = mysqli_query($con, $sql);
    return 1;
  }

  // set sharing permissions of list with user
  static function get_sharing_perimssions_of_list_with_user($list_owner, $word_list_id, $email) {
    $share_with_id = self::email2id($email);

    global $con;
    $sql = "
      SELECT `share`.`id`, `share`.`list`, `share`.`permissions` 
      FROM `share`, `list` 
      WHERE `list`.`id` = ".$word_list_id." AND `share`.`list` = `list`.`id` AND `list`.`creator` = ".$list_owner." AND `list`.`active` = 1 AND `list`.`user` = ".$share_with_id.";";
    $query = mysqli_query($con, $sql);
    while ($row = mysqli_fetch_assoc($query)) {
      return new SharingInformation($row['id'], new SimpleUser($share_with_id, null, null, $email), $row['list'], $row['permissions']);
    }
  }

  // get sharing information of list
  static function get_sharing_info_of_list($user_id, $word_list_id) {
    global $con;
    $sql = "
		SELECT `share`.`permissions`, `share`.`id` AS 'share_id', `share`.`list`, `user`.`id` AS 'user_id', `user`.`firstname`, `user`.`lastname`, `user`.`email`
		FROM `share`, `list`, `user`
		WHERE `share`.`list` = `list`.`id` AND `share`.`user` = `user`.`id` AND `list`.`id` = ".$word_list_id." AND `list`.`creator` = ".$user_id." AND `list`.`active` = 1 AND `share`.`permissions` <> 0
        ORDER BY `user`.`firstname` ASC, `user`.`lastname` ASC;";
    $query = mysqli_query($con, $sql);
    $result = array();
    while ($row = mysqli_fetch_assoc($query)) {
      array_push($result, new SharingInformation($row['share_id'], new SimpleUser($row['user_id'], $row['firstname'], $row['lastname'], $row['email']), $row['list'], $row['permissions']));
    }
    return $result;
  }

  // set word list languages
  static function set_word_list_languages($user_id, $list_id, $language1, $language2) {
    global $con;

    $sql = "
		UPDATE `list`, `share`
		SET `list`.`language1` = '".$language1."', `list`.`language2` = '".$language2."'
		WHERE `list`.`id` = ".$list_id." AND `list`.`creator` = ".$user_id." OR (`list`.`id` = `share`.`list` AND `share`.`user` = ".$user_id." AND `share`.`permissions` = 1);";
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
		SELECT COUNT(`id`) AS 'count'
		FROM `label`
		WHERE `name` LIKE '".$label_name."' AND `user` = ".$user_id." AND `parent` = ".$parent_label_id.";";
    $query = mysqli_query($con, $sql);
    $count = mysqli_fetch_object($query)->count;
    if ($count == 0) {
      // insert
      $sql = "INSERT INTO `label` (`user`, `name`, `parent`)
			VALUES (".$user_id.", '".$label_name."', ".$parent_label_id.");";
      $query = mysqli_query($con, $sql);
      return mysqli_insert_id($con);
    } else {
      // update
      $sql = "UPDATE `label` SET `active` = 1 WHERE `name` = '".$label_name."' AND `user` = ".$user_id." AND `parent` = ".$parent_label_id.";";
      $query = mysqli_query($con, $sql);
      return 1;
    }
  }

  // set label status
  static function set_label_status($user_id, $id, $status) {
    global $con;
    $sql = "UPDATE `label` SET `active` = ".$status." WHERE `id` = ".$id." AND `user` = ".$user_id.";";
    $query = mysqli_query($con, $sql);

    if ($status == 0) {
      // unset all label attachments
      $sql = "
			UPDATE `label_attachment`, `label`
			SET `label_attachment`.`active` = 0
			WHERE `label`.`id` = `label_attachment`.`label` AND
			`label`.`id` = ".$id." AND `label`.`user` = ".$user_id.";";
      $query = mysqli_query($con, $sql);
    }
    return "user_id: ".$user_id."; id: ".$id."; status: ".$status.";";
  }

  // set label list attachment
  static function set_label_list_attachment($user_id, $label_id, $list_id, $attachment) {
    global $con;
    // check already attached
    $sql = "
		SELECT COUNT(`label_attachment`.`id`) AS 'count'
		FROM `label_attachment`, `label`
		WHERE `label_attachment`.`label` = `label`.`id` AND
		`label`.`user` = ".$user_id." AND `label`.`id` = ".$label_id." AND `label_attachment`.`list` = ".$list_id.";";
    $query = mysqli_query($con, $sql);
    $count = mysqli_fetch_object($query)->count;
    if ($count == 0) {
      // insert
      $sql = "INSERT INTO `label_attachment` (`list`, `label`, `active`)
			VALUES (".$list_id.", ".$label_id.", ".$attachment.");";
      $query = mysqli_query($con, $sql);
      return mysqli_insert_id($con);
    } else {
      // update
      $sql = "
        UPDATE `label_attachment`, `label` 
        SET `label_attachment`.`active` = ".$attachment." 
        WHERE `label_attachment`.`label` = `label`.`id` AND `label`.`user` = ".$user_id." AND `label`.`id` = ".$label_id." AND `label_attachment`.`list` = ".$list_id.";";
      $query = mysqli_query($con, $sql);
      return 1;
    }
  }

  // get labels of user
  static function get_labels_of_user($user_id) {
    global $con;

    $sql = "SELECT * FROM `label` WHERE `user` = ".$user_id." AND `active` = 1 ORDER BY `label`.`name` ASC;";
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
    $sql = "UPDATE `label` SET `name` = '".$label_name."' WHERE `id` = ".$label_id." AND `user` = ".$user_id.";";
    $query = mysqli_query($con, $sql);
    return 1;
  }

  static function get_label_list_attachments_of_user($id) {
    global $con;

    $sql = "
        SELECT `label_attachment`.`id`, `label_attachment`.`list`, `label_attachment`.`label`, `label_attachment`.`active` 
        FROM `label_attachment`, `label`, `list`, `share`
        WHERE (`label`.`id` = `label_attachment`.`label` AND `label`.`user` = ".$id." AND `label_attachment`.`list` = `list`.`id` AND 
          `label_attachment`.`active` = 1 AND `label`.`active` = 1 AND `list`.`active` = 1) AND 
          (`list`.`creator` = ".$id." OR (`share`.`user` = ".$id." AND `share`.`permissions` <> 0 AND `share`.`list` = `label_attachment`.`list`))
        GROUP BY `label_attachment`.`id`
        ORDER BY `label`.`name` ASC;";
    $query = mysqli_query($con, $sql);
    $output = array();
    while ($row = mysqli_fetch_assoc($query)) {
      $attachment = new LabelAttachment($row['id'], $row['list'], $row['label'], $row['active']);
      array_push($output, $attachment);
    }
    return $output;
  }


  static function add_query_results($user, $data) {
    global $con;
    // add the whole array
    for ($i = 0; $i < count($data); $i++) {
      $sql = "INSERT INTO `answer` (`user`, `word`, `correct`, `time`)
        VALUES (".$user.", '".$data[$i]['word']."', ".$data[$i]['correct'].", ".$data[$i]['time'].");";
      $query = mysqli_query($con, $sql);
    }
    return count($data);
  }

  static function get_query_results($user, $wordIds) {
    $answers = array();
    for ($i = 0; $i < count($wordIds); $i++) {
      array_merge($answers, Answer::get_by_word_id($wordIds[$i]));
    }
    return $answers;
  }
  
  
  
  // settings
  
  static function set_name($id, $firstname, $lastname) {
    if (strlen($firstname) == 0 || strlen($lastname) == 0) {
      return 0;
    }
    // update database
    global $con;
    $sql = "UPDATE `user` SET `firstname` = '".$firstname."', `lastname` = '".$lastname."' WHERE `id` = ".$id.";";
    $query = mysqli_query($con, $sql);
    return 1;
  }
  
  static function set_password($id, $old_pw, $new_pw, $new_pw_confirm) {
    if ($new_pw === $new_pw_confirm) {
      $check_old_pw = self::check_login_data(self::id2email($id), $old_pw);
      if ($check_old_pw === 1) {
        if (Validation::is_password($new_pw)) {
          $salt = rand(0, 999999999);
          $new_pw = sha1($salt . $new_pw);
          unset($new_pw_confirm);
          
          // update database
          global $con;
          $sql = "UPDATE `user` SET `password` = '".$new_pw."', `salt` = ".$salt." WHERE `id` = ".$id.";";
          $query = mysqli_query($con, $sql);
          return 1; // no error
        } else {
          return 5; // invalid new password
        }
      } else if ($check_old_pw === 0) {
        return 3; // wrong old password given
      } else if ($check_old_pw === 2) {
        return 4; // email not confirmed
      }
    }
    else {
      return 2; // passwords not equal
    }
  }
  
  static function delete_account($id, $password) {
    if (self::check_login_data(self::id2email($id), $password) !== 0) {
      unset($password);
      // update database
      global $con;
      $sql = "UPDATE `user` SET `active` = 0 WHERE `id` = ".$id.";";
      $query = mysqli_query($con, $sql);
      return 1; // no error
    }
    return 0;
  }
  
  
  // recently used
  static function get_last_used_n_lists_of_user($id, $limit) {
    global $con;
    $lists = array();
    $sql = "
      SELECT `list_usage`.`list` 
      FROM `list_usage`, `list` 
      WHERE `list_usage`.`user` = ".$id." AND `list_usage`.`list` = `list`.`id` AND `list`.`active` = 1
      GROUP BY `list_usage`.`list`
      ORDER BY MAX(`list_usage`.`time`) DESC
      LIMIT 0 , ".$limit.";";
    $query = mysqli_query($con, $sql);
    while ($row = mysqli_fetch_assoc($query)) {
      array_push($lists, BasicWordList::get_by_id($row['list']));
    }
    return $lists;
  }
  
  
  // feed
  static function get_feed($id, $since) {
    if ($since == -1) {
      $next_to_last_login = self::get_next_to_last_login_of_user($id);
      if (is_null($next_to_last_login)) {
        $since = 0;
      } else {
        $since = $next_to_last_login->date;
      }
    }
    return new Feed($id, $since);
  }


  // settings

  // get user settings
  static function get_user_settings($id) {
    return UserSettings::get_by_id($id);
  }

  // set ads enabled
  static function set_ads_enabled($id, $ads_enabled) {
    global $con;
    $sql = "UPDATE `user_settings` SET `ads_enabled` = ".(($ads_enabled == 'false') ? 0 : 1)." WHERE `user` = ".$id.";";
    $query = mysqli_query($con, $sql);
    return ($ads_enabled == 'true') ? TRUE : FALSE;
  }
}

class Feed {
  public $user;
  public $events = array();
  
  function __construct($id, $since) {
    // $id is the user id for whom the feed is being created.
    // $since is a UNIX-timestamp which limits the feed content. If e.g. a list has been shared before this time it will not appear in the feed.
    $this->user = intval($id);
    
    global $con;
    
    // shared lists
    $sql = "
      SELECT `share`.`id`, `share`.`list`, `share`.`permissions`, `list`.`creator`, `share`.`time`
      FROM `share`, `list` 
      WHERE `list`.`id` = `share`.`list` AND `share`.`user` = ".$id." AND `share`.`permissions` <> 0 AND `share`.`time` >= ".$since."
      ORDER BY `share`.`time` DESC;";
    $query = mysqli_query($con, $sql);
    while ($row = mysqli_fetch_assoc($query)) {
      array_push(
        $this->events, 
        new FeedItem(
          FeedItemType::ListShared,
          intval($row['time']),
          new SharingInformation($row['id'], SimpleUser::get_by_id($row['creator']), $row['list'], $row['permissions'])
        )
      );
    }
    
    // another user has added a word to a list which is shared with you
    $sql = "
      SELECT `list`.`creator`, `word`.`user`, COUNT(`word`.`id`) AS 'amount', MIN(`word`.`time`) AS 'time', `list`.`id`
      FROM `list`, `share`, `word` 
      WHERE `share`.`user` = ".$id." AND `word`.`user` <> ".$id." AND 
        `word`.`list` = `list`.`id` AND `share`.`list` = `list`.`id` AND
        `list`.`active` <> 0 AND `share`.`permissions` <> 0 AND `word`.`status` <> 0 AND
        `word`.`time` > ".$since."
      GROUP BY `word`.`list`, `word`.`user`
      ORDER BY `word`.`time` ASC;
    ";
    $query = mysqli_query($con, $sql);

    while ($row = mysqli_fetch_assoc($query)) {
      if ($row['user'] == 0) continue;
      
      array_push(
        $this->events, 
        new FeedItem(
          FeedItemType::WordAdded,
          intval($row['time']),
          new WordsAddedFeedItem(
            $row['amount'], 
            BasicWordList::get_by_id($row['id']), 
            SimpleUser::get_by_id($row['creator']), 
            SimpleUser::get_by_id($row['user']))
        )
      );
    }
    
    // another user has added a word to your word list
    $sql = "
      SELECT `list`.`creator`, `word`.`user`, COUNT(`word`.`id`) AS 'amount', MIN(`word`.`time`) AS 'time', `list`.`id`
      FROM `list`, `word` 
      WHERE `word`.`list` = `list`.`id` AND `word`.`user` <> ".$id." AND `list`.`creator` = ".$id." AND
        `list`.`active` <> 0 AND `word`.`status` <> 0 AND
        `word`.`time` > ".$since."
      GROUP BY `word`.`list`, `word`.`user`
      ORDER BY `word`.`time` ASC;
    ";
    $query = mysqli_query($con, $sql);

    while ($row = mysqli_fetch_assoc($query)) {
      if (intval($row['user']) === 0) {
        continue;
      }
      array_push(
        $this->events, 
        new FeedItem(
          FeedItemType::WordAdded,
          intval($row['time']),
          new WordsAddedFeedItem($row['amount'], BasicWordList::get_by_id($row['id']), SimpleUser::get_by_id($row['creator']), SimpleUser::get_by_id($row['user']))
        )
      );
    }
    
    // users added
    $sql = "
      SELECT `user1`, `time`
      FROM `relationship` 
      WHERE `type` <> 0 AND `user2` = ".$id." AND `time` >= ".$since."
      ORDER BY `time` DESC;";
    $query = mysqli_query($con, $sql);
    while ($row = mysqli_fetch_assoc($query)) {
      array_push(
        $this->events, 
        new FeedItem(
          FeedItemType::UserAdded,
          intval($row['time']),
          SimpleUser::get_by_id($row['user1'])
        )
      );
    }
  }
}

class FeedItem {
  public $type;
  public $time;
  public $info;
  
  function __construct($type, $time, $info) {
    $this->type = $type;
    $this->time = $time;
    $this->info = $info;
  }
}

abstract class FeedItemType {
  const UserAdded = 0;
  const ListShared = 1;
  const WordAdded = 2;
  const WordDeleted = 3;
}

class WordsAddedFeedItem {
  public $amount;
  public $list;
  public $list_creator;
  public $user;
  
  function __construct($amount, BasicWordList $list, SimpleUser $list_creator, SimpleUser $user) {
    $this->amount = intval($amount);
    $this->list = $list;
    $this->list_creator = $list_creator;
    $this->user = $user;
  }
}

class Answer {
  public $id;
  public $user;
  public $word;
  public $correct;
  public $time;

  function __construct($id, $user, $word, $correct, $time) {
    $this->id = intval($id);
    $this->user = intval($user);
    $this->word = intval($word);
    $this->correct = intval($correct);
    $this->time = intval($time);
  }

  static function get_by_id($id) {
    global $con;
    $sql = "SELECT * FROM `answer` WHERE `id` = ".$id.";";
    $query = mysqli_query($con, $sql);
    while ($row = mysqli_fetch_assoc($query)) {
      return new Answer($row['id'], $row['user'], $row['word'], $row['correct'], $row['time']);
    }
  }
}

class LabelAttachment {
  public $id;
  public $list;
  public $label;
  public $active;

  function __construct($id, $list, $label, $active) {
    $this->id = intval($id);
    $this->list = intval($list);
    $this->label = intval($label);
    $this->active = intval($active);
  }
}


class Label {
  public $id;
  public $name;
  public $user;
  public $parent_label;
  public $active;

  public function __construct($id, $name, $user, $parent_label, $active) {
    $this->id = intval($id);
    $this->name = $name;
    $this->user = intval($user);
    $this->parent_label = intval($parent_label);
    $this->active = intval($active);
  }

  // second constructor (by id)
  public function get_by_id($id) {
    global $con;

    $sql = "SELECT * FROM `label` WHERE `id` = ".$id.";";
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
    $this->id =intval($id);
    $this->firstname = $firstname;
    $this->lastname = $lastname;
    $this->email = $email;
  }

  // second constructor (by id)
  static function get_by_id($id) {
    global $con;

    $sql = "SELECT `firstname`, `lastname`, `email` FROM `user` WHERE `id` = ".$id.";";
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

  public function get_settings() {
    return UserSettings::get_by_id($this->id);
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

    $sql = "SELECT * FROM `user` WHERE `id` = ".$id.";";
    $query = mysqli_query($con, $sql);
    while ($row = mysqli_fetch_assoc($query)) {
      $this->id = intval($id);
      $this->firstname = $row['firstname'];
      $this->lastname = $row['lastname'];
      $this->email = $row['email'];
      $this->password = $row['password'];
      $this->salt = $row['salt'];
      $this->reg_time = intval($row['reg_time']);
      $this->email_confirmed = intval($row['email_confirmed']);
      $this->email_confirmation_key = $row['email_confirmation_key'];
    }
  }
}

class UserSettings {
  public $user;
  public $ads_enabled;

  public function __construct($user, $ads_enabled) {
    $this->user = $user;
    $this->ads_enabled = $ads_enabled;
  }

  public function get_by_id($id) {
    global $con;

    $sql = "SELECT * FROM `user_settings` WHERE `user` = ".$id.";";
    $query = mysqli_query($con, $sql);
    while ($row = mysqli_fetch_assoc($query)) {
      return new UserSettings($id, ($row['ads_enabled'] == 0) ? FALSE : TRUE);
    }
    return NULL;
  }

}

class Login {
  public $id;
  public $user_id;
  public $date;
  public $ip;

  public function __construct($id, $user_id, $date, $ip) {
    $this->id = intval($id);
    $this->user_id = intval($user_id);
    $this->date = intval($date);
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
  public $words;

  public function __construct($id, $name, $creator, $comment, $language1, $language2, $creation_time) {
    $this->id = intval($id);
    $this->name = $name;

    // creator can be SimpleUser or Integer
    if ($creator instanceof SimpleUser)
      $this->creator = $creator;
    else 
      $this->creator = intval($creator);

    $this->comment = $comment;
    $this->language1 = $language1;
    $this->language2 = $language2;
    $this->creation_time = intval($creation_time);

    if ($this->language1 == null) $this->language1 = "First language";
    if ($this->language2 == null) $this->language2 = "Second language";
  }

  public function get_by_id($id) {
    global $con;

    $sql = "SELECT * FROM `list` WHERE `id` = ".$id.";";
    $query = mysqli_query($con, $sql);
    while ($row = mysqli_fetch_assoc($query)) {
      return new BasicWordList($id, $row['name'], $row['creator'], $row['comment'], $row['language1'], $row['language2'], $row['creation_time']);
    }
    return null;
  }

  public function load_words($loadAnswers, $user_id) {
    global $con;

    $sql = "SELECT * FROM `word` WHERE `list` = ".$this->id." AND `status` = 1;";
    $query = mysqli_query($con, $sql);
    $this->words = array();
    while ($row = mysqli_fetch_assoc($query)) {
      $word = new Word($row['id'], $row['list'], $row['language1'], $row['language2']);
      if ($loadAnswers) {
        $word->load_answers($user_id);
      }
      array_push($this->words, $word);
    }
  }
  
  // get editing permissions for user
  public function get_editing_permissions_for_user($id) {
    // returns true if the user given via $id has permissions to edit the list
    // returns false if the user has no permissions to edit but permissions to view or no permissions at all
    
    global $con;
    
    $sql = "SELECT COUNT(`id`) AS 'count' FROM `share` WHERE `list` = ".$this->id." AND `user` = ".$id." AND `permissions` = 2;";
    $query = mysqli_query($con, $sql);
    $count = mysqli_fetch_object($query)->count;
    
    return ($count == 0);
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
		WHERE `label_attachment`.`label` = `label`.`id` AND `label`.`user` = ".$user." AND
		`label`.`active` = 1 AND `label_attachment`.`active` = 1 AND
		`label_attachment`.`list` = ".$id."
		GROUP BY `label`.`id`;";
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
  public $answers;

  public function __construct($id, $list, $language1, $language2) {
    $this->id = intval($id);
    $this->list = intval($list);
    $this->language1 = $language1;
    $this->language2 = $language2;
  }

  function load_answers($user_id) {
    $this->answers = array();
    global $con;
    $sql = "SELECT * FROM `answer` WHERE `user` = ".$user_id." AND `word` = ".$this->id.";";
    $query = mysqli_query($con, $sql);
    while ($row = mysqli_fetch_assoc($query)) {
      array_push($this->answers, new Answer($row['id'], $row['user'], $row['word'], $row['correct'], $row['time']));
    }
  }

  static function get_by_id($id) {
    global $con;
    $sql = "SELECT * FROM `word` WHERE `id` = ".$id." ORDER BY `id` DESC;";
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
  public $list;

  public function __construct($id, SimpleUser $user, $list, $permissions) {
    $this->id = intval($id);
    $this->user = $user;
    $this->permissions = intval($permissions);
    $this->list = BasicWordList::get_by_id($list);
  }
}
?>
