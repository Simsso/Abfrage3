<?php

class SimpleUser {
  public $id;
  public $firstname;
  public $lastname;
  public $email;

  public function __construct($id, $firstname, $lastname, $email) {
    $this->id =intval($id);
    $this->firstname = htmlspecialchars_decode($firstname);
    $this->lastname = htmlspecialchars_decode($lastname);
    $this->email = htmlspecialchars_decode($email);
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
  public $hash;

  // constructor (by id)
  public function __construct($id) {
    global $con;

    $sql = "SELECT * FROM `user` WHERE `id` = ".$id.";";
    $query = mysqli_query($con, $sql);
    while ($row = mysqli_fetch_assoc($query)) {
      $this->id = intval($id);
      $this->firstname = htmlspecialchars_decode($row['firstname']);
      $this->lastname = htmlspecialchars_decode($row['lastname']);
      $this->email = htmlspecialchars_decode($row['email']);
      $this->password = $row['password'];
      $this->salt = $row['salt'];
      $this->reg_time = intval($row['reg_time']);
      $this->email_confirmed = intval($row['email_confirmed']);
      $this->hash = $row['hash'];
    }
  }
}

class UserSettings {
  public $user;
  public $ads_enabled;
  public $newsletter_enabled;

  public function __construct($user, $ads_enabled, $newsletter_enabled) {
    $this->user = $user;
    $this->ads_enabled = $ads_enabled;
    $this->newsletter_enabled = $newsletter_enabled;
  }

  public function get_by_id($id) {
    global $con;

    $sql = "SELECT * FROM `user_settings` WHERE `user` = ".$id.";";
    $query = mysqli_query($con, $sql);
    while ($row = mysqli_fetch_assoc($query)) {
      return new UserSettings($id, ($row['ads_enabled'] == 0) ? FALSE : TRUE, ($row['newsletter_enabled'] == 0) ? FALSE : TRUE);
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

?>