<?php

// include database class
require('database.class.php');

// start session 
session_start();

if (!isset($_SESSION['id']) && isset($_COOKIE['stay_logged_in_hash']) && isset($_COOKIE['stay_logged_in_id'])) {
  // no session running but user has cookies indicating that he stayed logged in
  
  // confirm if the user stayed logged in
  if (Database::check_stay_logged_in($_COOKIE['stay_logged_in_id'], $_COOKIE['stay_logged_in_hash'])) {
    // the user stayed logged in
    // set the session cookie with the users id
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
