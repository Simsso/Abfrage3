<?php
require('dbconnect.inc.php'); // include data base connection
require('validation.class.php'); // include validation class to verify correctness of strings in general


// database classes
require('database/answer.class.php');
require('database/feed.class.php');
require('database/label.class.php');
require('database/user.class.php');
require('database/word.class.php');
require('database/wordlist.class.php');


// Database
//
// class with a lot of static methods to read and write the Abfrag3 data base
// mostly called from the file server.php
class Database {
  const STAY_LOGGED_IN_DURATION = 32678400;
  
  // log server request
  //
  // inserts a new entry into the server requst table 
  //
  // @param unsigned int user: the id of the user
  // @param string page: the requested page
  static function log_server_request($user, $page) {
    // inserts a new server request into the server_request table
    global $con;
    $sql = "INSERT INTO `server_request` (`user`, `page`, `time`, `ip`) VALUES (".$user.", '".$page."', ".time().", '".$_SERVER['REMOTE_ADDR']."');";
    $query = mysqli_query($con, $sql);
  }
  

  // register user
  //
  // throws exceptions if the passed data is now valid or not accepted for some reason
  //
  // @param string firstname, lastname, email, password, confirmpassword: the data of the new user
  //
  // @return bool: TRUE if everything worked out
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


  // email available
  //
  // check if an email-address is available
  // 
  // @param string email: the email address to check
  //
  // @return bool: TRUE of the email is available; FALSE if it isn't
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


  // check login data
  //
  // checks an email password combination
  //
  // @param string email: email address
  // @param string password: password
  //
  // @return byte: 
  //  - 0: wrong password
  //  - 1: right combination
  //  - 2: right combination but email has not been confirmed yet
  //  - 3: wrong email
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
    if ($count == 0) { 
      $sql = "SELECT COUNT(`id`) AS 'count' FROM `user` WHERE `email` LIKE '".$email."' AND `active` = 1;";
      $query = mysqli_query($con, $sql);
      $count = mysqli_fetch_object($query)->count;
      if ($count == 0) {
        return 3;
      }
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
  //
  // the login table stores every single login
  // users can stay logged in to do not require a login every time they load the page when the session has expired
  // 
  // @param unsigned int login_id: id of a table row in the table login
  // @param unsigned int user: the id of the user
  static function stay_logged_in($login_id, $id) {
    
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
  //
  // checks if the user is staying logged in
  // the user is verified by a hash, stored in a cookie and in the data base
  //
  // @param unsigned int user: the id of the user
  // @param string hash: hash to verify the user
  //
  // @return bool: true if the user is stayed logged in; false if not
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
  

  // refresh session if staying logged in
  //
  // in case a session has expired but the user wants to stay logged in the session cookie has to be set again
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
  //
  // reads the salt from the user table
  //
  // @param string email: email address
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
  //
  // converts an email address to the corresponding id
  //
  // @param string email: email address
  //
  // @return unsigned int | NULL: the id or NULL if the email address doesn't exist
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


  // id to email
  //
  // converts a user id to the email of the user
  //
  // @param unsigned int id: the id of the user
  // 
  // @return string | NULL: the email or NULL if the id doesn't exist
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
  //
  // after a user has registered a email confirmation is required
  // this function checks the key and email address for validity
  // 
  // @param string email: email address
  // @param string key: the key to verify the idendity of the user
  //
  // @return bool: TRUE if the email has been confirmed; false if not
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
  //
  // @param unsigned int id: the id of the user
  // @param bool stay logged in: if the user wants to stay logged in
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


  // first login of user
  //
  // @param unsigned int id: the id of the user
  //
  // @return bool: true if the given user logs in for the first time
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


  // get last login of user
  //
  // @param unsigned int id: the id of the user
  //
  // @return Login | NULL: login object of the last login of a user
  static function get_last_login_of_user($id) {
    global $con;
    $sql = "SELECT `id`, `user`, `time`, `ip` FROM `login` WHERE `user` = ".$id." ORDER BY `time` DESC LIMIT 1;";
    $query = mysqli_query($con, $sql);
    while ($row = mysqli_fetch_assoc($query)) {
      return new Login($row['id'], $row['user'], $row['time'], $row['ip']);
    }
    return NULL;
  }


  // get next to last login of user
  //
  // @param unsigned int id: the id of the user
  //
  // @return Login | NULL: login object of the next to last login of a user
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
  //
  // @param unsigned int id: the id of the user
  //
  // @return SimpleUser[]: array of SimpleUser objects with the additional attribute (bool) bidirectional telling whether both users have added them
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
  //
  // @param unsigned int id: the id of the user
  //
  // @return SimpleUser[]: array of SimpleUser objects with the additional attribute (bool) bidirectional telling whether both users have added them
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

  // users have added them both
  //
  // @param unsigned int user1: the id of the first user
  // @param unsigned int user2: the id of the second user
  //
  // @return bool: true if users have added them both (bidirectional)
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
  //
  // @param unsigned int user1: the id of the first user
  // @param unsigned int user2: the id of the to check if the first user has added them
  //
  // @return bool: true if user1 has added user 2
  static function user_has_added_user($user1, $user2) {
    global $con;
    $sql = "SELECT COUNT(`id`) AS 'count' 
      FROM `relationship` 
      WHERE `user1` = ".$user1." AND `user2` = ".$user2." AND `relationship`.`type` = 1;";
    $query = mysqli_query($con, $sql);
    return (mysqli_fetch_object($query)->count == 1);
  }


  // get number of registered users
  //
  // @return unsigned int: total number of registered users in Abfrage3
  static function get_number_of_registered_users() {
    global $con;
    $sql = "SELECT COUNT(`id`) AS 'count' FROM `user` WHERE `active` <> 0;";
    $query = mysqli_query($con, $sql);
    return mysqli_fetch_object($query)->count;
  }


  // get number of logins during last time given in seconds
  //
  // @param int time_in_seconds: time span
  //
  // @return: number of logins during the last time passed
  static function get_number_of_logins_during_last_time($time_in_seconds) {
    $time_min = time() - $time_in_seconds;
    global $con;
    $sql = "SELECT COUNT(`id`) AS 'count' FROM `login` WHERE `time` > ".$time_min.";";
    $query = mysqli_query($con, $sql);
    return mysqli_fetch_object($query)->count;
  }


  // add user
  // 
  // add user means not register user but some user adds another user
  //
  // @param unsigned int id: the id of the user who adds the other guy
  // @param string email: the email address of the user to add
  // 
  // @return int:
  //  - -1: the user with the passed email address doesn't exist
  //  - -2: the id which belongs to the passed email equals the passed id (user wants to add themself)
  //  - 0: unknown error
  //  - 1: information in the data base updated (users have already added them before and removed afterwards or so)
  //  - 2: user added
  static function add_user($id, $email) {
    global $con;
    $added_user_id = self::email2id($email);

    if (is_null($added_user_id))
      return -1;

    if ($added_user_id == $id)
      return -2;

    $time = time();
    // check if the user is already added
    if (!self::user_already_has_a_relationship_with($id, $added_user_id)) {
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
  //
  // removes a user from another user
  // 
  // @return int: 1 if everything went fine
  static function remove_user($user1, $user2) {
    global $con;

    $sql = "UPDATE `relationship` SET `type` = 0 WHERE `user1` = ".$user1." AND `user2` = ".$user2.";";
    $query = mysqli_query($con, $sql);
    return 1;
  }

  // user already has a relationship with
  //
  // @param unsigned int user1: the id of user A
  // @param unsigned int user2: the id of the user to check if user A has a relationship with
  //
  // @return bool: true if user1 already has a relationship entry in the data base with user2
  static function user_already_has_a_relationship_with($user1, $user2) {
    global $con;

    $sql = "SELECT COUNT(`id`) AS 'count' FROM `relationship` WHERE `user1` = ".$user1." AND `user2` = ".$user2.";";
    $query = mysqli_query($con, $sql);
    if (mysqli_fetch_object($query)->count == 0) {
      return false;
    }
    return true;
  }


  // add word list
  // 
  // @param unsigned int id: the id of the user who wants to create a new list
  // @param string name: the name of the new list
  //
  // @return object
  // @return byte object->state: 1 if everything went fine
  // @return unsigned int object->id: id of the new entry in the data base table
  static function add_word_list($id, $name) {
    // returns id and state
    global $con;
    $time = time();
    $sql = "INSERT INTO `list` (`name`, `creator`, `creation_time`) VALUES ('".$name."', ".$id.", ".$time.");";
    $query = mysqli_query($con, $sql);

    $result = new stdClass();
    $result->state = 1;
    $result->id = mysqli_insert_id($con);
    return $result;
  }


  // get list of word lists of a user
  //
  // all word lists a user has created which are not deleted
  // but not those which are shared with the user
  //
  // @param unsigned int id: the id of the user
  //
  // @return BasicWordList[]: lists of the passed user
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
  //
  // includes self created lists and lists shared with the user
  // 
  // @param unsigned int id: the id of the user
  static function get_query_lists_of_user($id) {
    $lists = array_merge(self::get_word_lists_of_user($id), self::get_list_of_shared_word_lists_with_user($id));
    for ($i = 0; $i < count($lists); $i++) {
      $lists[$i]->load_words(true, $id, "DESC");
    }
    return $lists;
  }

  // get specific word list
  //
  // @param unsigned int user_id: the id of the user (required to verify that the user has rights to get the list data)
  // @param unsigned int word_list_id: id of the requested word list
  // @param bool log: if set to true the function will log that the word list has been used (for the recently used box)
  //
  // @return WordList | NULL: WordList object or NULL if the user has insufficient rights or the list doesn't exist
  static function get_word_list($user_id, $word_list_id, $log) {
    global $con;
    
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
        $row['creation_time'], null);
      $list->load_words(true, $user_id, "DESC");
      $list->set_labels(WordList::get_labels_of_list($user_id, $word_list_id));


      // log the list access inside the loop to make sure it is only logged if the user has actually access to the list
      if ($log === true) {
        $sql = "INSERT INTO `list_usage` (`user`, `list`, `time`)
            VALUES (".$user_id.", ".$word_list_id.", ".time().");";
        $query = mysqli_query($con, $sql);
      }

      return $list;
    }
    return NULL;
  }
  

  // rename word list
  // 
  // @param unsigned int user_id: the id of the user
  // @param unsigned int word_list_id: the id of the word list to rename
  // @param string list_name: new name of the list
  //
  // @return byte: 1 if everything went right
  static function rename_word_list($user_id, $word_list_id, $list_name) {
    global $con;

    $sql = "UPDATE `list` SET `name` = '".$list_name."' WHERE `id` = ".$word_list_id." AND `creator` = ".$user_id.";";
    $query = mysqli_query($con, $sql);
    return 1;
  }


  // get words of list
  //
  // @param unsigned int list_id: the id of the word list
  //
  // @return Word[]: array of the lists words
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
  //
  // @param unsigned int user_id: id of the user (to check permissions before deleting)
  // @param unsigned int word_list_id: id of the list to delete
  //
  // @return byte: 1 if everything went right
  static function delete_word_list($user_id, $word_list_id) {
    global $con;

    $sql = "UPDATE `list` SET `active` = 0 WHERE `id` = ".$word_list_id." AND `creator` = ".$user_id.";";
    $query = mysqli_query($con, $sql);
    return 1;
  }


  // add word
  //
  // add a new word to a word list
  //
  // @param unsigned int user_id: id of the user
  // @param unsigned int word_list_id: id of the list to add the new word
  // @param string lang1: meaning of the word in the first language
  // @param string lang2: meaning of the word in the second language
  //
  // @return unsigned int: the id of the newly added word
  static function add_word($user_id, $word_list_id, $lang1, $lang2) {
    global $con;
    // TODO check owner
    $sql = "INSERT INTO `word` (`list`, `language1`, `language2`, `time`, `user`)
		VALUES (".$word_list_id.", '".$lang1."', '".$lang2."', ".time().", ".$user_id.");";
    $query = mysqli_query($con, $sql);
    return mysqli_insert_id($con);
  }


  // update word
  //
  // updates the meaning of a word
  //
  // @param unsigned int user_id: id of the user
  // @param unsigned int word_id: id of the word
  // @param string lang1: meaning of the word in the first language
  // @param string lang2: meaning of the word in the second language
  //
  // @return byte: 1 if everything went right
  static function update_word($user_id, $word_id, $lang1, $lang2) {
    global $con;
    // TODO: add check if word is owned by $user_id
    $sql = "UPDATE `word` SET `language1` = '".$lang1."', `language2` = '".$lang2."' WHERE `id` = ".$word_id.";";
    $query = mysqli_query($con, $sql);
    return 1;
  }


  // remove word
  //
  // @param unsigned int user_id: id of the user
  // @param unsigned int word_id: id of the word
  //
  // @return byte: 1 if everything went right
  static function remove_word($user_id, $word_id) {
    global $con;
    // TODO: add check if word is owned by $user_id
    $sql = "UPDATE `word` SET `status` = 0, `time` = ".time().", `user` = ".$user_id." WHERE `id` = ".$word_id.";";
    $query = mysqli_query($con, $sql);
    return 1;
  }


  // get list of shared word lists of user
  //
  // list of word lists the given user has shared with other users
  //
  // @param unsigned int id: id of the user
  //
  // @return BasicWordList[]: array of the word lists
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
  //
  // list of word lists which other users have shared with the given user
  //
  // @param unsigned int id: id of the user
  //
  // @return BasicWordList[]: array of the word lists
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
  //
  // used to share a list with users or to unshare
  //
  // @param unsigned int user_id: id of the user
  // @param unsigned int word_list_id: id of the word list
  // @param string email: email address of the other user
  // @param byte permissions: permissions for the other user (don't see anything, view or edit)
  //
  // @return byte:
  //  - -1: email belongs to user_id
  //  - 1: inserted data (success)
  //  - 2: updated data (success)
  //  - 0: unknown error
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
    return 0;
  }


  // set sharing permissions of word list with user
  // 
  // @param unsigned int user_id: id of the user who wants to set the sharing permissions
  // @param unsigned int id: id of the other user
  // @param byte permissions: permissions for the other user (don't see anything, view or edit)
  //
  // @return byte: 1 if everything worked out
  static function set_sharing_permissions_by_sharing_id($user_id, $id, $permissions) {
    global $con;

    $sql = "
		UPDATE `share`, `list`
		SET `share`.`permissions` = ".$permissions.", `share`.`time` = ".time()."
		WHERE `share`.`id` = ".$id." AND (`list`.`id` = `share`.`list` AND `list`.`creator` = ".$user_id." OR `share`.`user` = ".$user_id.");";
    $query = mysqli_query($con, $sql);
    return 1;
  }


  // get sharing permissions of list with user
  // 
  // @param unsigned int list_owner: id of the list owner
  // @param unsigned int word_list_id: id of the list
  // @param string email: email of the other user to check permissions
  //
  // @return SharingInformation: object with the permissions the user passed via email has
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
  //
  // @param unsigned int user_id: id of the user
  // @param unsigned int word_list_id: id of the word list
  //
  // @return SharingInformation[]: sharing information of the list
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
  //
  // a word list can have information about it's languages
  // the functions changes them
  //
  // @param unsigned int user_id: id of the user
  // @param unsigned int list_id: id of the list to change the languages
  // @param string language1: first language of the list
  // @param string language2: second language of the list
  static function set_word_list_languages($user_id, $list_id, $language1, $language2) {
    global $con;

    $sql = "
		UPDATE `list`, `share`
		SET `list`.`language1` = '".$language1."', `list`.`language2` = '".$language2."'
		WHERE `list`.`id` = ".$list_id." AND (`list`.`creator` = ".$user_id." OR (`list`.`id` = `share`.`list` AND `share`.`user` = ".$user_id." AND `share`.`permissions` = 1));";
    $query = mysqli_query($con, $sql);
    return 1;
  }


  // add label 
  //
  // @param unsigned int user_id: id of the user
  // @param string label_name: name of the new label
  // @param unsigned int parent_label_id: id of the parent label or 0 if the level is no sub-label
  static function add_label($user_id, $label_name, $parent_label_id) {
    global $con;

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
  // 
  // deletes a label if 0 is passed as the status
  //
  // @param unsigned int user_id: id of the user
  // @param unsigned int id: id of the label
  // @param byte status: deleted (0); every other value than 0 will have no effect
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
  }


  // set label list attachment
  //
  // @param unsigned int user_id: id of the user
  // @param unsigned int label_id: id of the label
  // @param unsigned int list_id: id of the list
  // @param byte attachment: active or not (1 or 0)
  // 
  // @return unsigned int:
  //  - 0: the label information has been updated
  //  - !0: the label information is a new table row; id of the row
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
      return 0;
    }
  }


  // get labels of user
  //
  // @param unsigned int user_id: id of the user
  //
  // @return Label[]: array of labels
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
  //
  // @param unsigned int user_id: id of the user
  // @param unsigned int label_id: id of the label
  // @param string label_name: new name of the label
  //
  // @return byte: 1
  static function rename_label($user_id, $label_id, $label_name) {
    global $con;
    $sql = "UPDATE `label` SET `name` = '".$label_name."' WHERE `id` = ".$label_id." AND `user` = ".$user_id.";";
    $query = mysqli_query($con, $sql);
    return 1;
  }


  // get label list attachments of user
  //
  // @param unsigned int id: id of the user
  //
  // @return LabelAttachment[]: array of label list attachments
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


  // add query results
  //
  // @param unsigned int user: id of the user
  // @param object[] data: array of answer objects
  // @param data[]->word, correct, direction, type, time: information about the answer
  //
  // @return unsigned int: number of added answers
  static function add_query_results($user, $data) {
    global $con;
    // add the whole array
    for ($i = 0; $i < count($data); $i++) {
      $sql = "INSERT INTO `answer` (`user`, `word`, `correct`, `direction`, `type`, `time`)
        VALUES (
          ".$user.", 
          ".$data[$i]['word'].", 
          ".$data[$i]['correct'].", 
          ".$data[$i]['direction'].", 
          ".$data[$i]['type'].", 
          ".$data[$i]['time'].");";
      $query = mysqli_query($con, $sql);
    }
    return count($data);
  }


  // get query results
  //
  // @param unsigned int user: id of the user
  // @param unsigned int[]: array of word ids for which the query answers are requested
  //
  // @return Answer[]: answers given to the passed words
  static function get_query_results($user, $wordIds) {
    $answers = array();
    for ($i = 0; $i < count($wordIds); $i++) {
      array_merge($answers, Answer::get_by_word_id($wordIds[$i]));
    }
    return $answers;
  }
  
  
  // settings set name
  //
  // @param unsigned int id: id of the user
  // @param string firstname: new firstname of the user
  // @param string lastname: new lastname of the user
  //
  // @return byte:
  //  - 0: invalid first- or lastname
  //  - 1: success
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
  

  // set password
  //
  // @param unsigned int id: id of the user
  // @param string old_pw: old password
  // @param string new_pw: new password
  // @param string new_pw_confirm: new password confirmation
  //
  // @return byte:
  //  - 1: success
  //  - 2: passwords not equal
  //  - 3: wrong old password given
  //  - 4: email not confirmed
  //  - 5: invalid new password
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
  

  // delete account
  //
  // @param unsigned int id: id of the user
  // @param string password: password to confirm deletion
  //
  // @return byte:
  //  - 0: error
  //  - 1: success
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
  
  
  // get last used n lists of user
  //
  // recently used lists
  //
  // @param unsigned int id: id of the user
  // @param int: limit (max number of lists)
  // 
  // @return BasicWordList[]: lists
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
  
  
  // get feed
  //
  // @param unsigned int id: id of the user
  // @param int since: all (-1) or UNIX timestamp
  //
  // @return Feed: feed object
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


  // get user settings
  //
  // @param unsigned int id: id of the user
  // 
  // @return UserSettings: settings
  static function get_user_settings($id) {
    return UserSettings::get_by_id($id);
  }

  // set ads enabled
  //
  // @param unsigned int id: id of the user
  // @param bool ads_enabled: ads enabled or not
  //
  // @return bool: true if the user has ads enabled now
  static function set_ads_enabled($id, $ads_enabled) {
    global $con;
    $sql = "UPDATE `user_settings` SET `ads_enabled` = ".(($ads_enabled == 'false') ? 0 : 1)." WHERE `user` = ".$id.";";
    $query = mysqli_query($con, $sql);
    return ($ads_enabled == 'true') ? TRUE : FALSE;
  }
}

?>