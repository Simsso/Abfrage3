<?php

require('database.class.php');

session_start();

if (!isset($_SESSION['id']) && isset($_COOKIE['stay_logged_in_hash']) && isset($_COOKIE['stay_logged_in_id'])) {
  if (Database::check_stay_logged_in($_COOKIE['stay_logged_in_id'], $_COOKIE['stay_logged_in_hash'])) {
    $_SESSION['id'] = $_COOKIE['stay_logged_in_id'];
  }
}

// include the files depending on the session status
if (isset($_SESSION['id'])) { // user logged in
  require('home.inc.php');
} else { // user not logged in
  require('login.inc.php');
}
?>
